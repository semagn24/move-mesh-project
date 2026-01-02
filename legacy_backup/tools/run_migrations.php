<?php
// Simple migration runner for the workspace migrations/*.sql files
// Usage: php tools/run_migrations.php [--dry-run|-n] [--yes|-y] [--preview]
//  --dry-run / -n   : show what would run without applying
//  --yes / -y       : assume yes for all confirmations (non-interactive)
//  --preview        : show SQL for pending migrations and exit
// NOTE: ensure your DB credentials in config/db.php are correct and you have a backup before running.

require_once __DIR__ . '/../config/db.php';

// Parse flags
$flags = $argv;
array_shift($flags); // remove script name
$dryRun = in_array('--dry-run', $flags) || in_array('-n', $flags);
$assumeYes = in_array('--yes', $flags) || in_array('-y', $flags);
$previewOnly = in_array('--preview', $flags);

function prompt_yes_no($msg, $default = false) {
    if (function_exists('readline')) {
        $rv = readline($msg);
    } else {
        echo $msg;
        $rv = fgets(STDIN);
    }
    $rv = trim(strtolower($rv));
    if ($rv === '') return $default;
    return in_array($rv, ['y', 'yes']);
}

try {
    // Ensure migrations table exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS migrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        filename VARCHAR(255) NOT NULL UNIQUE,
        applied_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    $migrationsDir = __DIR__ . '/../migrations';
    $files = glob($migrationsDir . '/*.sql');
    sort($files, SORT_STRING);

    $appliedStmt = $pdo->prepare('SELECT filename FROM migrations WHERE filename = ?');
    $insertStmt = $pdo->prepare('INSERT INTO migrations (filename) VALUES (?)');

    $pending = [];
    foreach ($files as $f) {
        $filename = basename($f);
        $appliedStmt->execute([$filename]);
        if ($appliedStmt->fetchColumn()) {
            continue;
        }
        $sql = file_get_contents($f);
        if (!trim($sql)) continue;
        $pending[] = ['path' => $f, 'filename' => $filename, 'sql' => $sql];
    }

    if (empty($pending)) {
        echo "No pending migrations.\n";
        exit(0);
    }

    if ($previewOnly || $dryRun) {
        echo "Pending migrations (preview):\n";
        foreach ($pending as $p) {
            echo "---- {$p['filename']} ----\n";
            // show first 2000 chars to avoid flooding
            echo substr($p['sql'], 0, 2000) . (strlen($p['sql']) > 2000 ? "\n... (truncated)\n" : "\n");
        }
        if ($dryRun) {
            echo "(Dry run) No migrations applied.\n";
            exit(0);
        }
    }

    foreach ($pending as $p) {
        $filename = $p['filename'];
        $sql = $p['sql'];

        echo "\n---- $filename ----\n";
        echo "Preview (first 10 lines):\n";
        $lines = explode("\n", $sql);
        echo implode("\n", array_slice($lines, 0, 10)) . (count($lines) > 10 ? "\n...\n" : "\n");

        $apply = $assumeYes ? true : prompt_yes_no("Apply migration $filename? [y/N]: ", false);
        if (!$apply) {
            echo "Skipping: $filename\n";
            continue;
        }

        echo "Applying: $filename ... ";
        try {
            $pdo->beginTransaction();
            $pdo->exec($sql);
            $insertStmt->execute([$filename]);
            $pdo->commit();
            echo "OK\n";
        } catch (PDOException $e) {
            $pdo->rollBack();
            echo "FAILED - " . $e->getMessage() . "\n";
            echo "Stopping further migrations.\n";
            exit(1);
        }
    }

    echo "\nMigrations complete.\n";
} catch (PDOException $e) {
    echo "Connection or setup error: " . $e->getMessage() . "\n";
    exit(1);
}

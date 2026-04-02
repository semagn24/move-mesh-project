<?php
require_once __DIR__ . "/../config/db.php";
$mid = (int)$_GET['movie'];
$stmt=$pdo->prepare("SELECT c.*,u.username FROM comments c JOIN users u ON u.id=c.user_id WHERE movie_id=? ORDER BY created_at DESC");
$stmt->execute([$mid]);
foreach($stmt as $c){
 echo "<div class='comment'><div style='display:flex;justify-content:space-between;'>
 <span class='user'>".htmlspecialchars($c['username'])."</span>
 <span class='stars'>".str_repeat("★",$c['rating']).str_repeat("☆",5-$c['rating'])."</span>
 </div><div class='text'>".htmlspecialchars($c['comment'])."</div></div>";
}

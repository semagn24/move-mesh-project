P2P Integration (WebTorrent) — Setup Guide

Overview
- We added minimal server-side support for WebTorrent-based P2P streaming.
- The server acts as a tracker/seed controller and can act as an initial web-seed when a Node daemon is running.

Steps to enable full P2P operation:

1) Database migration
- Run the SQL in `migrations/2025_add_magnet.sql` to add the `magnet_link` column to `movies`.
  Example (MySQL):
    mysql -u root -p movie_stream < migrations/2025_add_magnet.sql

2) Configure seed token
- Edit `config/seed_config.php` and set a strong value for `'token'` and set `'uploads_base_url'` to your public uploads path.

3) Run the seed daemon (Node.js)
- Install Node and run:
    npm install webtorrent-hybrid chokidar axios minimist
- Start the daemon:
    node tools/seed_daemon.js --uploads="/absolute/path/to/uploads/videos" --taskdir="/absolute/path/to/uploads/to_seed" --callback="http://yourdomain/movie_stream/admin/seed_callback.php" --token=YOUR_TOKEN --baseurl="https://yourdomain/movie_stream/uploads/videos"

Notes:
- When an admin uploads a movie in `admin_add_movie.php`, it will write a task file to `uploads/to_seed/<movie_id>.json` containing the absolute path and movie id.
- The seed daemon will pick up tasks, seed the file using webtorrent-hybrid (adds a webseed URL using your public upload path), and POST the generated magnet link to `admin/seed_callback.php`.
- `admin/seed_callback.php` updates the `movies.magnet_link` column.- To seed your existing library, use `php tools/seed_db_queue.php` to queue all movies that have a `video` file but no `magnet_link` (it writes tasks to `uploads/to_seed/`). You can also run `tools/seed_db_queue.php` periodically (cron) or use the admin button **Queue Missing Magnets** on `admin/seeding.php` to run it via the web UI.
4) Client (watch.php)
- `movies/watch.php` now uses WebTorrent in the browser (via CDN) if `magnet_link` is available. It prioritizes the beginning of the file so playback starts quickly.
- If no magnet link is present, it falls back to HTTP streaming from `uploads/videos`.

Security considerations
- Keep the seed token secret (store in `config/seed_config.php` and do not commit it).
- Run the seed daemon on a secure host and consider running it behind a process manager (pm2, systemd).

Limitations & Recommendations
- This is a prototype scaffold. Production systems need HTTPS, TURN servers (for WebRTC connectivity), proper CORS and rate limiting, monitoring, and stronger authentication for seeding callbacks.
- For a resilient initial seeder, run multiple seeder instances across regions.

If you want, I can also:
- Add a small admin page that shows `magnet_link` and seeding status for each movie.
- Provide a Dockerfile for running the seed daemon. 

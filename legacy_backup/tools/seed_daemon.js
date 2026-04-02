#!/usr/bin/env node
/*
Seed daemon (Node.js) - watches uploads/to_seed for tasks, seeds video files using webtorrent-hybrid
and POSTs the magnet link to the app's seed callback endpoint.

Install:
  npm install webtorrent-hybrid chokidar axios

Run:
  node seed_daemon.js --uploads="C:/xampp/htdocs/movie_stream (2)/movie_stream/uploads/videos" --taskdir="C:/xampp/htdocs/movie_stream (2)/movie_stream/uploads/to_seed" --callback="http://localhost/movie_stream/admin/seed_callback.php" --token=CHANGE_ME

This script is a sample starter - run it as a service/PM2 for production.
*/

const WebTorrent = require('webtorrent-hybrid');
const chokidar = require('chokidar');
const axios = require('axios');
const fs = require('fs');
const path = require('path');

const argv = require('minimist')(process.argv.slice(2));
const uploads = argv.uploads || argv.u || './uploads/videos';
const taskdir = argv.taskdir || argv.t || './uploads/to_seed';
const callback = argv.callback || argv.c || 'http://localhost/movie_stream/admin/seed_callback.php';
const token = argv.token || process.env.SEED_TOKEN || 'CHANGE_ME';
const baseUrl = argv.baseurl || argv.b || 'http://localhost/movie_stream/uploads/videos';

if (!fs.existsSync(taskdir)) fs.mkdirSync(taskdir, { recursive: true });

const client = new WebTorrent();

async function handleTask(filePath) {
  try {
    const raw = fs.readFileSync(filePath, 'utf8');
    const task = JSON.parse(raw);
    const videoPath = task.video_path || task.videoPath;
    const movieId = task.movie_id || task.movieId;
    if (!videoPath || !movieId) {
      console.error('Invalid task', filePath);
      return;
    }

    console.log('Seeding', videoPath);
    const options = {
      // Add webseed so browsers can fallback to server
      urlList: [ baseUrl + '/' + path.basename(videoPath) ]
    };

    client.seed(videoPath, options, async (torrent) => {
      console.log('Seeding complete. Magnet URI:', torrent.magnetURI);
      try {
        await axios.post(callback, {
          token: token,
          movie_id: movieId,
          magnet_link: torrent.magnetURI
        }, { timeout: 10000 });

        console.log('Posted magnet to callback for movie', movieId);
        // remove the task file
        fs.unlinkSync(filePath);
      } catch (err) {
        console.error('Failed to post magnet to callback', err.message);
      }
    });
  } catch (err) {
    console.error('Error processing task', err.message);
  }
}

// On startup, process existing tasks
fs.readdirSync(taskdir).forEach(f => {
  if (f.endsWith('.json')) handleTask(path.join(taskdir, f));
});

// Watch for new tasks
chokidar.watch(taskdir, { ignoreInitial: true }).on('add', (p) => {
  console.log('New task added', p);
  handleTask(p);
});

console.log('Seed daemon started. Watching', taskdir);

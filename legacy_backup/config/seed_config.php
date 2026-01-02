<?php
// Simple seed configuration. Set a strong token before running the seed daemon.
return [
    // Enable or disable the P2P seeding system (set to false to disable)
    'enabled' => false,

    // A shared secret between the seed daemon and this app for authorization
    'token' => 'CHANGE_ME_TO_A_RANDOM_SECRET',

    // Optional: public base URL for uploaded videos (used by seeder to add webseeds)
    'uploads_base_url' => 'http://localhost/movie_stream/uploads/videos'
];
<?php
/**
 * Payment configuration (Chapa)
 * Location: movie_stream/users/payment_config.php
 */

return [
    // Your Chapa Test Secret Key
    'chapa_secret' => 'CHASECK_TEST-mOK1mVlfwORHemxVMC1004YmFbc9ptYi',

    // Callback and return URLs (Ensure these paths exist in your project)
    'callback_url' => 'http://localhost/movie_stream/users/verify_payment.php',
    'return_url'   => 'http://localhost/movie_stream/users/profile.php',

    // Plan / pricing
    'plan_price'   => 150, // Amount in ETB
    'plan_days'    => 30,  // Subscription duration
];
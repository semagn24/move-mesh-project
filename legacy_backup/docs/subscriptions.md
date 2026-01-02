# Subscriptions & Payments

Quick steps to enable Premium subscriptions:

1. Run the DB migration:

   mysql -u root -p movie_stream < migrations/2025_add_subscription.sql

2. Configure payment keys in `config/payment_config.php`:
   - Set `'chapa_secret'` to your Chapa secret key
   - Update `'callback_url'` and `'return_url'` if needed

3. Optional: run any app scripts or cron jobs if required.

Notes:
- Premium protection is enforced in `movies/watch.php`. It prevents the player from loading for premium content unless the user has an active subscription.
- Files under `uploads/videos` are still directly accessible by URL; consider moving served files behind an access-controlled route if you need strict DRM.

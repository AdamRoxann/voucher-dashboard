# Voucher Claim Dashboard (Laravel + MySQL 8)

This is a Laravel application overlay/starter containing the app-specific code for a voucher claim dashboard. It is not a copy of Laravel's framework/vendor directory.

## Requirements
- PHP 8.2+ and Composer
- Laravel 12
- MySQL 8
- Node.js/npm (optional; the included dashboard CSS works without a frontend build)

## Install
1. Create a fresh Laravel 12 project: `composer create-project laravel/laravel voucher-claims "^12.0"`
2. Copy the contents of this package into the Laravel project, merging the folders.
3. Install Excel export dependency: `composer require maatwebsite/excel:^3.1`
4. Configure `.env`:
   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=voucher_claims
   DB_USERNAME=your_user
   DB_PASSWORD=your_password
   ```
5. Create the database in MySQL 8, then run:
   ```bash
   php artisan key:generate
   php artisan migrate --seed
   php artisan storage:link
   php artisan serve
   ```
6. Visit `/`.

## Image storage
Voucher proof images are stored under `storage/app/public/voucher-proofs/` using Laravel's `public` disk. The database stores only the relative path. `php artisan storage:link` exposes them through `/storage/...`. Validate uploads and back up `storage/app/public` as part of normal server backups.

## Notes before production
- Add authentication/authorization before exposing this dashboard publicly. The included routes are an internal-tool starter and do not add login by default.
- Configure HTTPS, CSRF protection (already provided by Laravel web routes), database backups, upload limits, and web-server restrictions.
- Use a private disk and signed/authorized image routes if proof images must not be publicly accessible.
- Excel export uses `maatwebsite/excel` and exports all voucher-code rows for each matching claim.
- Claim amount is stored in decimal format; voucher counts are calculated from the entered code lists and stored for convenient reporting.

## Update existing local database
After copying these updated files into an existing project, run:

```powershell
php artisan db:seed
```

The seeder adds all campaign store names using `firstOrCreate`, so existing stores and claims are preserved. The dashboard claim table now has a **Details** action that opens a full claim detail page with merchant/contact information, bank details, transfer status, voucher codes by category, and proof image.


## Authentication and admin roles

This overlay adds session-based login/logout and role protection. Public registration is intentionally disabled. Existing dashboard, claim, store, export, and admin routes require login; `/admin` additionally requires `users.role = admin`.

### Install
1. Merge these files into the existing Laravel project; do not replace `.env` or `vendor`.
2. Run `php artisan migrate`. Existing users receive role `user` by default.
3. Create an admin account using Tinker (commands below).
4. Visit `/login`.

Create an admin account (replace email and password with your own):
```php
php artisan tinker
$user = \App\Models\User::create(['name' => 'Dashboard Admin', 'email' => 'admin@example.com', 'password' => \Illuminate\Support\Facades\Hash::make('ChangeThis-Strong-Password!')]);
$user->role = 'admin';
$user->save();
exit
```
If the account already exists, promote it instead:
```php
$user = \App\Models\User::where('email', 'admin@example.com')->firstOrFail();
$user->role = 'admin';
$user->save();
```
Use a unique strong password and change the example email/password. Do not share admin credentials. No registration endpoint is provided.

### Create regular users
Use Tinker and set `role` to `user`, or create accounts through a future admin-only user-management page. Non-admin authenticated users receive HTTP 403 on `/admin`.

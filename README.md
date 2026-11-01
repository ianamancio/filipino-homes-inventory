# Real Estate Property Management System

PHP 8+, MySQL/MariaDB, XAMPP, Bootstrap 5 and Meta Graph API.

## 1. Requirements
- XAMPP with Apache, MySQL and PHP 8+
- PHP extensions: `pdo_mysql`, `curl`, `fileinfo`, `openssl`
- A Meta Developer app with Facebook Login and Pages permissions

## 2. Install locally
1. Extract this folder into `C:\xampp\htdocs\real-estate-inventory`.
2. Start Apache and MySQL in XAMPP.
3. Open `http://localhost/phpmyadmin` and create database `real_estate_inventory` (or let installer create tables after DB exists).
4. Copy `.env.example` to `.env`; set DB credentials. Default XAMPP is usually DB user `root` and blank password.
5. Open `http://localhost/real-estate-inventory/install.php`.
6. Create your admin account. Delete or rename `install.php` after setup.
7. Sign in at `http://localhost/real-estate-inventory/login.php`.

## 3. Meta/Facebook setup
1. Create an app at https://developers.facebook.com/apps/
2. Add Facebook Login (Web) and configure the Valid OAuth Redirect URI to:
   `http://localhost/real-estate-inventory/facebook-callback.php`
3. In app settings, add your App ID and App Secret to `.env`.
4. Request `pages_show_list`, `pages_read_engagement`, and `pages_manage_posts`.
5. Ensure the Facebook user has Page access with permission to create content.
6. For local testing, add your Facebook account as an app role (Admin/Developer/Tester) and use Development mode. For other users, request required Advanced Access/App Review as Meta requires.
7. In the app dashboard, open **Facebook Integration** and connect your Page.

The app uses Graph API v26.0 as its configurable default. Check Meta's current supported version before production deployment.

## 4. Automatic publishing
When Facebook is connected and the checkbox "Publish to Facebook after saving" is enabled on Add Property, the system saves the property first, then uploads its images and publishes a Page post. A failed Facebook post does NOT delete the property; it is recorded as Failed in Facebook History.

For image posts, the server uploads each image to `/{page-id}/photos` with `published=false`, then creates one `/{page-id}/feed` post with `attached_media`. Images must be accessible locally to PHP, valid JPEG/PNG/GIF/WEBP (Meta may not accept every format; JPEG/PNG recommended), and within Meta's upload limits. This starter implementation converts no formats; use JPEG or PNG for best compatibility.

## 5. Security before going online
- Use HTTPS and a real domain; localhost OAuth is for development only.
- Set strong unique DB credentials and keep `.env` outside public web root where possible.
- Never commit `.env` to Git.
- Keep App Secret and Page token server-side only.
- Use a dedicated production database, backups, and least-privilege DB account.
- Add rate limiting, audit logs, and token refresh/reconnection monitoring before multi-agent/public deployment.
- Remove `install.php` after creating the first admin.
- Do not expose this app publicly until security review is complete.

## Notes
This is a runnable starter project, not a hosted/live deployment. Facebook connection requires your own Meta App ID/Secret, Page authorization, and Meta approval/access where applicable.

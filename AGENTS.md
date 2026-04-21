# Repository Notes

- Stack: plain PHP app with MySQL, Bootstrap 5 via CDN, and vanilla JavaScript. There is no framework, package manager, or build step.
- Entry point: `/index.php` with Apache rewrite rules in `/.htaccess`.
- Base URL is hardcoded in `app/config/config.php` as `/form2`; update it if the folder name or mount path changes.
- Database setup is SQL-first: use `database/schema.sql` and `database/seed.sql` instead of migrations.
- Uploaded files are stored under `public/uploads/blocks` and `public/uploads/submissions`; avoid changing those paths without updating `storage_url()` and upload handling in `CampaignModel`.
- Password reset uses PHPMailer via `vendor/autoload.php` and the SMTP settings in `app/config/config.php`.
- Excel export is implemented as an Excel-compatible HTML table response in `AdminCampaignController::export()`, not via PhpSpreadsheet.
- Public QR rendering depends on the client-side `qrcodejs` CDN script loaded in `app/views/layouts/main.php`.
- There is no test suite yet; the fastest verification is `php -l` across `index.php` and `app/**/*.php`, then manual checks of login, campaign create/edit, public submit, results, and export.

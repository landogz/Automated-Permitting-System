# Deployment Runbook

1. `composer install --no-dev -o`
2. `npm ci && npm run build`
3. Configure `.env` (MySQL, Sanctum, S3, APP_DEBUG=false)
4. `php artisan migrate --force`
5. `php artisan db:seed --class=RoleAndAdminSeeder` (first deploy only)
6. `php artisan storage:link`
7. Queue worker + scheduler
8. Staging UAT → Production

Health: `GET /up`

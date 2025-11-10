# Setup Checklist

Use this checklist to ensure your Star Recruiting application is properly configured.

## Initial Setup

- [ ] Install PHP 8.3+ and Composer
- [ ] Install MySQL 8
- [ ] Install Node.js and npm
- [ ] Clone/download the project
- [ ] Run `composer install`
- [ ] Run `npm install`

## Configuration

- [ ] Copy `.env.example` to `.env` (if not exists)
- [ ] Generate application key: `php artisan key:generate`
- [ ] Create MySQL database: `star_recruiting`
- [ ] Update `.env` with database credentials:
  ```
  DB_CONNECTION=mysql
  DB_HOST=127.0.0.1
  DB_PORT=3306
  DB_DATABASE=star_recruiting
  DB_USERNAME=root
  DB_PASSWORD=your_password
  ```

## Database Setup

- [ ] Run migrations: `php artisan migrate`
- [ ] Install Passport: `php artisan passport:install`
- [ ] Copy Passport client credentials to `.env`:
  ```
  PASSPORT_CLIENT_ID=...
  PASSPORT_CLIENT_SECRET=...
  ```
- [ ] Seed database: `php artisan db:seed`

## Storage Permissions

- [ ] Ensure storage directories are writable:
  ```bash
  chmod -R 775 storage bootstrap/cache
  ```

## Testing

- [ ] Start server: `php artisan serve`
- [ ] Test registration endpoint
- [ ] Test login endpoint
- [ ] Test protected endpoints with token

## Verification

- [ ] Database tables created (12 tables)
- [ ] Assessment types seeded (Behavioral, Aptitude)
- [ ] Assessment questions seeded (20 behavioral, 10 aptitude)
- [ ] API endpoints accessible
- [ ] Frontend views load correctly

## Troubleshooting

If you encounter issues:

1. **Passport not working**: Run `php artisan passport:keys`
2. **Database errors**: Verify credentials and database exists
3. **Permission errors**: Check storage directory permissions
4. **Route not found**: Run `php artisan route:clear`

## Next Steps

- [ ] Review API documentation
- [ ] Test all endpoints
- [ ] Customize assessment questions if needed
- [ ] Set up frontend authentication
- [ ] Deploy to production (see DEPLOYMENT.md)


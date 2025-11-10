# Installation Guide

## Quick Start

1. **Install Dependencies**
   ```bash
   composer install
   npm install
   ```

2. **Configure Environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

3. **Set Up Database**
   - Create MySQL database: `star_recruiting`
   - Update `.env` with your database credentials:
     ```
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=star_recruiting
     DB_USERNAME=root
     DB_PASSWORD=your_password
     ```

4. **Run Migrations**
   ```bash
   php artisan migrate
   ```

5. **Install Passport**
   ```bash
   php artisan passport:install
   ```
   
   Copy the generated client ID and secret to your `.env` file:
   ```
   PASSPORT_CLIENT_ID=...
   PASSPORT_CLIENT_SECRET=...
   ```

6. **Seed Database (Optional)**
   ```bash
   php artisan db:seed
   ```
   
   This will create:
   - Assessment types (Behavioral, Aptitude)
   - Sample questions for both assessment types

7. **Start Development Server**
   ```bash
   php artisan serve
   ```

   The application will be available at `http://localhost:8000`

## Testing the API

### Register a Candidate
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "candidate1",
    "password": "password123",
    "role": "candidate",
    "first_name": "John",
    "last_name": "Doe",
    "role_title": "Software Engineer",
    "years_exp": 5
  }'
```

### Register a Recruiter
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "username": "recruiter1",
    "password": "password123",
    "role": "recruiter",
    "first_name": "Jane",
    "last_name": "Smith",
    "email": "jane@company.com",
    "company_name": "Tech Recruiters Inc"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "username": "candidate1",
    "password": "password123"
  }'
```

Save the token from the response for authenticated requests.

### Access Protected Endpoints
```bash
curl -X GET http://localhost:8000/api/candidate/home \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -H "Accept: application/json"
```

## Troubleshooting

### Passport Installation Issues
If you encounter issues with Passport:
```bash
php artisan passport:keys
php artisan passport:client --personal
```

### Database Connection Issues
- Verify MySQL is running
- Check database credentials in `.env`
- Ensure database exists: `CREATE DATABASE star_recruiting;`

### Permission Issues
Ensure storage directories are writable:
```bash
chmod -R 775 storage bootstrap/cache
```

## Next Steps

- Review `API_DOCUMENTATION.md` for complete API reference
- Check `DEPLOYMENT.md` for production deployment instructions
- Customize assessment questions in the seeder
- Add frontend authentication if needed


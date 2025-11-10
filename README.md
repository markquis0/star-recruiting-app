# Star Recruiting Application

A web application connecting candidates and recruiters built with Laravel 11 and MySQL.

## Features

- **Candidate Management**: Registration, profile creation, and assessment forms
- **Recruiter Management**: Search, review, and save candidate profiles
- **Assessment System**: Behavioral and aptitude assessments with automated scoring
- **API Authentication**: Laravel Passport for secure API access

## Technology Stack

- **Backend**: Laravel 11 (PHP 8.3+)
- **Database**: MySQL 8
- **Authentication**: Laravel Passport
- **Frontend**: Blade Templates

## Installation

1. Clone the repository
2. Install dependencies: `composer install`
3. Copy `.env.example` to `.env`: `cp .env.example .env`
4. Generate application key: `php artisan key:generate`
5. Configure database in `.env`
6. Run migrations: `php artisan migrate`
7. Install Passport: `php artisan passport:install`
8. Seed database (optional): `php artisan db:seed`
9. Start server: `php artisan serve`

## Database Setup

Create a MySQL database named `star_recruiting` and update your `.env` file:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=star_recruiting
DB_USERNAME=root
DB_PASSWORD=your_password
```

## API Endpoints

### Authentication
- `POST /api/register` - Register new user (candidate or recruiter)
- `POST /api/login` - Login and receive JWT token

### Candidate
- `GET /api/candidate/home` - Get all forms
- `POST /api/candidate/form` - Create/update form
- `GET /api/candidate/form/{id}` - Get form by ID
- `POST /api/candidate/assessment/{form_id}` - Submit assessment
- `GET /api/candidate/results` - Get all assessment results

### Recruiter
- `GET /api/recruiter/home` - Get saved candidates
- `GET /api/recruiter/search` - Search candidates
- `POST /api/recruiter/save` - Save candidate
- `DELETE /api/recruiter/save/{id}` - Remove saved candidate
- `GET /api/recruiter/candidate/{id}` - View candidate profile

## License

MIT


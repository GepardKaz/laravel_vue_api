# Laravel-Vue SPA 

## Features

- Laravel 8
- Vue + VueRouter + Vuex + VueI18n + ESlint
- Pages with dynamic import and custom layouts
- Login, register, email verification and password reset
- Authentication with JWT
- Bootstrap 4 + Font Awesome 5

## Installation

- git clone https://github.com/GepardKaz/laravel_vue_api.git
- composer update
- Edit `.env` and set your database connection details
- (When installed via git clone or download, run `php artisan key:generate` and `php artisan jwt:secret`)
- `php artisan migrate`
- `npm install`
```bash
#### via docker
- docker-compose up --build
- docker-compose exec ecp composer install(or composer update)
- docker-compose exec ecp php artisan config:cache
- docker-compose exec ecp php artisan migrate
- docker-compose exec ecp php artisan key:generate
- docker-compose exec ecp npm install
- docker-compose exec ecp npm run dev
```
## Usage

#### Development

```bash
# Build and watch
npm run watch

# Serve with hot reloading (not working)
npm run hot
```

#### Production

```bash
npm run production
```

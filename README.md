# Laravel-Vue SPA 

## Features

- Laravel 8
- Vue + VueRouter + Vuex + VueI18n + ESlint
- Pages with dynamic import and custom layouts
- Login, register, email verification and password reset
- Authentication with JWT
- Bootstrap 4 + Font Awesome 5

## Installation

- git clone http://192.168.0.137:63424/kadyl/ecp.recycle.kz.git
- composer update
- Edit `.env` and set your database connection details
- (When installed via git clone or download, run `php artisan key:generate` and `php artisan jwt:secret`)
- `php artisan migrate`
- `npm install`

#### if via docker
- docker-compose up --build
- docker-compose exec ecp_server composer install(or composer update)
- docker-compose exec ecp_server php artisan config:cache
- docker-compose exec ecp_server php artisan migrate
- docker-compose exec ecp_server npm install
- docker-compose exec ecp_server npm run dev
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

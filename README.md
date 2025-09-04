## About Laravel API Starter Kit

Laravel is an API base for building any laravel api first application. It comes with built-in features including:

-   [Spatie's roles and permissions package](https://spatie.be/docs/laravel-permission/v6/introduction).
-   Laravel's Sanctum.
-   [Spatie's activity logger](https://spatie.be/docs/laravel-activitylog/v4/introduction)

## License

This API Starter Kit is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## added composer commands
```
    "db-reset": "php artisan migrate:fresh --force --ansi",
    "clear-system-cache": "php artisan config:clear && php artisan cache:clear && php artisan optimize:clear",
```

## added packages
```
    "php": "^8.4",
    "cloudinary-labs/cloudinary-laravel": "^3.0",
    "laravel/framework": "^12.0",
    "laravel/sanctum": "^4.1",
    "laravel/tinker": "^2.10.1",
    "spatie/laravel-activitylog": "^4.10",
    "spatie/laravel-permission": "^6.19"
```

# Webapp Admin Files

A Laravel web application for managing files inside an organization. Users sign in through **AWS Cognito**, upload files to **AWS S3**, and see or manage files depending on their role and area. The interface is in Spanish.

## Features

- **Authentication with AWS Cognito**: login, first-login password change, forgot/reset password and logout. Sessions are validated against Cognito JWTs.
- **File management**: upload (up to 10 MB per file), download, search by unique identifier or original name, and copy a file's unique identifier to the clipboard.
- **Recycle bin**: deleting a file is a soft delete. Files can be restored or permanently deleted from the recycle bin.
- **Automatic cleanup**: the `files:delete-expired` command permanently removes files (and their S3 objects) that have been in the recycle bin for more than 30 days.
- **History**: every file action is logged and can be searched by admins and managers.
- **User management**: create, edit and delete users, plus an organization chart view.
- **Area management**: full CRUD for areas (admin only).
- **File metrics**: total files, files uploaded today and this month, and total size, scoped to what the user can see.

## Roles and permissions

Each user has one role and, optionally, an area. What a user can see and delete depends on the role:

| Role | Files they can access | Files they can delete |
| --- | --- | --- |
| `estandar` | Their own files | Their own files |
| `jefe_area` | Files of users in their area | Their own files |
| `gerente` | Files of the areas they manage | Files of the areas they manage |
| `admin` | All files | All files |

Other permissions:

- `/users`: `admin`, `gerente` and `jefe_area`.
- `/history`: `admin` and `gerente`.
- `/areas`: `admin` only.

The rules live in [app/Policies](app/Policies) and are also enforced on the server.

## Tech stack

- PHP 8.3+ and Laravel 13
- SQLite by default (any database supported by Laravel works)
- AWS SDK for PHP: Cognito for authentication, S3 for file storage
- Blade views with Bootstrap 5 and Bootstrap Icons, built with Vite
- PHPUnit for tests and Laravel Pint for code style

## Getting started

### Requirements

- PHP 8.3 or higher and Composer
- Node.js and npm
- An AWS account with a Cognito user pool (and app client) and an S3 bucket

### Installation

```bash
composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite   # only if you use SQLite
php artisan migrate
npm install
npm run build
```

`composer setup` runs most of these steps for you.



```bash
composer dev
```

This starts the development processes (`php artisan dev`). To run only the PHP server and the asset watcher, use `php artisan serve` and `npm run dev` in separate terminals. The app is served at `http://localhost:8000` by default.

### Running the tests

```bash
composer test
```

## Scheduled cleanup

Files stay in the recycle bin for 30 days. To remove expired ones, run:

```bash
php artisan files:delete-expired
```

The command is not registered in the scheduler yet. To run it automatically, add it to `routes/console.php`, for example `Schedule::command('files:delete-expired')->daily();`, and make sure the scheduler runs (`php artisan schedule:work` locally, or a `* * * * * php artisan schedule:run` cron entry in production).

## Project structure

```
app/
  Console/Commands/   files:delete-expired command
  Helpers/            JWT helper
  Http/
    Controllers/      Auth, Home, File, History, User and Area controllers
    Middleware/       CognitoAuth (session/JWT check) and role-based admin middleware
    Requests/         Form request validation
  Models/             User, Area, File, History
  Policies/           File, User and Area authorization rules
  Services/           Cognito (auth, register, reset password), S3 and file metrics
database/migrations/  Schema (users, areas, files, history, manager-area pivot)
resources/views/      Blade templates
routes/web.php        All application routes
docs/                 Entity relationship diagram (erd-diagram.html)
archify_out/          Entity relationship map in Mermaid (erd.md)
```

## Data model

The main entities are `users`, `areas`, `files` and the `gerente_areas` pivot table (which areas each manager oversees), plus a `historials` table for the action log. See [archify_out/erd.md](archify_out/erd.md) or open [docs/erd-diagram.html](docs/erd-diagram.html) for the diagram.

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

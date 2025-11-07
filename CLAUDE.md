# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a Laravel 12 HR Management System built with Livewire 3 for real-time, reactive UI components. The application manages employee information, payroll, attendance, leaves, promotions, contracts, and more.

## Development Commands

### Starting the Development Environment
```bash
# Start all services (server, queue, logs, vite)
composer dev
```
This runs 4 concurrent processes:
- `php artisan serve` - Development server
- `php artisan queue:listen` - Queue worker
- `php artisan pail` - Real-time log viewer
- `npm run dev` - Vite dev server with HMR

### Individual Services
```bash
# Run development server only
php artisan serve

# Watch frontend assets
npm run dev

# Build for production
npm run build

# View logs
php artisan pail
```

### Testing
```bash
# Run all tests
composer test
# or
php artisan test

# Run specific test
php artisan test --filter TestName

# Run tests with Pest
./vendor/bin/pest
```

### Code Quality
```bash
# Format code with Laravel Pint
./vendor/bin/pint

# Run Pint on specific file
./vendor/bin/pint path/to/file.php
```

### Database
```bash
# Run migrations
php artisan migrate

# Fresh migration with seeding
php artisan migrate:fresh --seed

# Create new migration
php artisan make:migration create_table_name
```

### IDE Helper (for better autocomplete)
```bash
# Generate IDE helper files
php artisan ide-helper:generate
php artisan ide-helper:models
php artisan ide-helper:meta
```

## Architecture

### Frontend Layer
- **Framework**: Livewire 3 (reactive components without writing JavaScript)
- **Styling**: Bootstrap 5 + Tailwind CSS 4
- **Build Tool**: Vite with Laravel plugin
- **Key Libraries**: ApexCharts, Quill, Flatpickr, Choices.js, Dragula, FullCalendar

### Component Structure
Livewire components are organized by domain:
- `app/Livewire/Hr/` - HR management (staffs, attendance, leaves, salary)
- `app/Livewire/Setup/` - System configuration (finance, locations)
- `app/Livewire/Acl/` - Access control (roles, permissions)
- `app/Livewire/Auth/` - Authentication (login, 2FA, password reset)
- `app/Livewire/Users/` - User profile management

Each Livewire component has a corresponding Blade view in `resources/views/livewire/`.

### Backend Architecture
- **Models**: Located in `app/Models/` - Eloquent ORM models with relationships
- **Routes**: Defined in `routes/web.php` - all routes use Livewire components
- **Services**: `app/Services/` - business logic abstraction
- **Migrations**: 41 migrations defining HR system schema

### Database Schema
Key entities managed:
- Employees (personal info, contracts, salary, attendance)
- Departments, Designations, Job Titles
- Locations (countries, regions, districts, wards, villages, streets)
- Finance (allowances, deductions, salary scales, denominations)
- HR Records (leaves, promotions, qualifications, disciplinary actions, dependants)
- Workstations and shifts
- Permissions and roles (Spatie Laravel Permission)
- Activity logs (Spatie Laravel Activity Log)

### Key Packages
- **livewire/livewire**: Full-stack reactive components
- **spatie/laravel-permission**: Role and permission management
- **spatie/laravel-activitylog**: User activity tracking
- **devrabiul/laravel-toaster-magic**: Toast notifications
- **jenssegers/agent**: User agent detection

## Conventions

### Database
- Use UUIDs for primary keys where applicable
- Enum fields preferred over booleans for status fields (e.g., `'active'`/`'inactive'` instead of true/false)
- Soft deletes not widely used - handle cascading with `->cascadeOnDelete()` on foreign keys
- Foreign key convention: `foreignUuid('field_name')->constrained('table_name')`

### Livewire Components
- Component class: `app/Livewire/Module/ComponentName.php`
- View: `resources/views/livewire/module/component-name.blade.php`
- Naming: Use descriptive names (e.g., `Stafflist`, `Addstaff`, `Staffdetails`)
- Route binding: Components are registered directly in routes

### File Organization
- HR staff-related features are in `Hr/Staffs/` namespace
- Setup/configuration features are in `Setup/` with subfolders for `Finance/` and `Location/`
- Shared components are in `Common/`

### Testing
- Uses PHPUnit/Pest for testing
- Test database: SQLite in-memory (`:memory:`)
- Tests located in `tests/Unit/` and `tests/Feature/`

## Environment Setup

1. Copy `.env.example` to `.env`
2. Default DB: SQLite (`DB_CONNECTION=sqlite`)
3. Run `php artisan key:generate`
4. Run `php artisan migrate`
5. Install dependencies:
   ```bash
   composer install
   npm install
   ```

## Notes

- The application uses a custom theme in `dashertheme/` directory
- Session driver defaults to database
- Queue connection uses database queue driver in production
- Authentication includes 2FA support
- Activity logging is enabled system-wide for audit trails

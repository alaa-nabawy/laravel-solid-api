<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Laravel API SOLID

A Laravel-based API project implementing SOLID principles with enhanced CRUD generation capabilities, repository pattern, and service layer architecture.

## 🚀 Enhanced CRUD Generation Commands

This project includes powerful Artisan commands for rapid development with clean architecture patterns.

### Available Commands

#### `make:structure` - Enhanced CRUD Structure Generator

Generate complete CRUD structures with repositories, services, and optional resource folders.

```bash
# Generate full CRUD structure
php artisan make:structure Post

# Generate specific CRUD methods only
php artisan make:structure Post --only=create,read,update

# Skip resource folder generation
php artisan make:structure Post --no-resource

# Combine options
php artisan make:structure Post --only=read,update --no-resource
```

**Generated Structure:**
```
app/
├── Http/
│   └── Resources/
│       └── Post/
│           ├── Admin/
│           └── Api/
├── Services/
│   └── Post/
│       ├── PostCreateService.php
│       ├── PostReadService.php
│       ├── PostUpdateService.php
│       └── PostDeleteService.php
└── Repositories/
    └── PostRepository.php
```

#### `generate:crud` - Legacy CRUD Generator

Original CRUD generation command with resource folder support.

```bash
php artisan generate:crud Post
```

### Features

- **🏗️ SOLID Architecture**: Repository pattern with service layer separation
- **🎯 Selective Generation**: Choose specific CRUD methods to generate
- **📁 Optional Resources**: Skip resource folder creation when not needed
- **🔧 Modern PHP**: Constructor property promotion (PHP 8.0+)
- **📝 Clean Code**: Consistent naming conventions and structure
- **⚡ Rapid Development**: Generate complete CRUD structures in seconds

## 📋 Version History

### Version 2.1.0 (Latest)
**Release Date:** January 2025

#### 🆕 New Features
- Added `--no-resource` flag to skip resource folder generation
- Added `--only` option for selective CRUD method generation
- Enhanced command descriptions and help text
- Dynamic output messages showing exactly what was created

#### 🔧 Improvements
- Updated service stub templates to use constructor property promotion
- Improved code organization and maintainability
- Case-insensitive method matching for `--only` option
- Better error handling and validation

#### 🏗️ Architecture Enhancements
- Modern PHP 8.0+ syntax adoption
- Cleaner service class structure
- Reduced boilerplate code in generated files
- Enhanced stub template system

### Version 2.0.0
**Release Date:** January 2025

#### 🆕 Major Features
- Enhanced `make:structure` command with optional flags
- Resource folder generation (Admin/Api subfolders)
- Repository pattern implementation
- Service layer architecture

#### 🔧 Core Improvements
- CRUD service separation (Create, Read, Update, Delete)
- Automated directory structure creation
- Stub-based file generation system
- Laravel Passport integration

### Version 1.0.0
**Release Date:** January 2025

#### 🎉 Initial Release
- Basic Laravel API setup
- User authentication with Passport
- Initial CRUD generation command
- Repository and service foundation

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

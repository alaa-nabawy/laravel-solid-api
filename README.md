# Laravel API SOLID

<p align="center">
<a href="https://github.com/laravel/framework"><img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat&logo=php&logoColor=white" alt="PHP Version"></a>
<a href="https://laravel.com"><img src="https://img.shields.io/badge/Laravel-12.0+-FF2D20?style=flat&logo=laravel&logoColor=white" alt="Laravel Version"></a>
<a href="https://www.docker.com"><img src="https://img.shields.io/badge/Docker-Ready-2496ED?style=flat&logo=docker&logoColor=white" alt="Docker Ready"></a>
<a href="https://github.com/features/actions"><img src="https://img.shields.io/badge/CI-GitHub%20Actions-2088FF?style=flat&logo=github-actions&logoColor=white" alt="CI"></a>
<a href="LICENSE"><img src="https://img.shields.io/badge/License-MIT-green.svg" alt="License"></a>
</p>

## About

Enterprise-ready Laravel API boilerplate with SOLID architecture, automated CRUD generation, Passport authentication, Docker containerization, and comprehensive pre-commit quality tools.

## Table of Contents

- [Features](#features)
- [Quick Start](#quick-start)
- [Enhanced CRUD Generation](#enhanced-crud-generation)
- [Code Quality & Pre-commit Hooks](#code-quality--pre-commit-hooks)
- [Docker Environment](#docker-environment)
- [Authentication](#authentication)
- [Documentation](#documentation)
- [Version History](#version-history)

## Features

**🏗️ SOLID Architecture**

- Repository pattern with service layer separation
- Dependency injection and interface-based design
- Clean, maintainable, and testable code structure

**⚡ Enhanced CRUD Generation**

- `make:structure` command with selective generation (`--only`, `--no-resource`)
- `make:route-group` for organized API endpoints
- Modern PHP 8.2+ syntax with constructor property promotion

**🔐 Laravel Passport Integration**

- OAuth2 authentication out of the box
- Automated setup scripts
- API token management

**🐳 Professional Docker Setup**

- Separate development and production environments
- PostgreSQL, Redis, MailHog integration
- Optimized for cloud deployment

**🔧 Comprehensive Code Quality**

- Pre-commit hooks: PHPStan, Pint, PHPUnit, security audit
- Automated testing with coverage reports
- Markdown linting and secret detection

**🚀 Workflow Automation**

- Complete Makefile with 30+ commands
- One-command setup and deployment
- Integrated development tools

## Quick Start

### Development Setup

```bash
# Clone and setup
git clone <repository-url>
cd laravel-api-solid

# One-command setup (includes Docker, dependencies, migrations, Passport)
make setup

# Start development environment
make dev

# Access workspace for Laravel commands
make shell
```

Your API will be available at `http://localhost:8000` with MailHog at `http://localhost:8025`.

### Production Deployment

```bash
# Configure environment
cp .env.prod .env
nano .env  # Edit production settings

# Deploy
make prod-build
```

### Essential Commands

```bash
# Development
make dev              # Start development environment
make shell            # Access workspace container
make migrate          # Run database migrations
make test             # Run test suite
make code-quality     # Run all quality checks

# Code Quality
make phpstan          # Static analysis
make code-style       # Fix code formatting
make test-coverage    # Tests with coverage
make security-audit   # Security scan

# Passport
make passport-setup   # Complete Passport setup
make passport-client  # Create OAuth client
```

## Enhanced CRUD Generation

Powerful Artisan commands for rapid API development with SOLID architecture.

### Commands

```bash
# Generate complete CRUD structure
php artisan make:structure User

# Selective generation (only specific methods)
php artisan make:structure Post --only=index,show,store

# Skip resource folder creation
php artisan make:structure Category --no-resource

# Generate organized route groups
php artisan make:route-group Api/V1/Users
```

### What Gets Generated

- **Repository & Interface**: Data access layer with contracts
- **Service Classes**: Business logic separation (Create, Read, Update, Delete)
- **Controller**: Clean, dependency-injected API controller
- **Resources**: API response transformers (optional)
- **Routes**: Organized route groups with automatic registration

**📖 [Complete Documentation](./docs/general/route-generation.md)** - Detailed examples and advanced usage

## Code Quality & Pre-commit Hooks

Comprehensive automated code quality enforcement with pre-commit hooks.

### Tools Included

- **PHPStan**: Static analysis and type checking
- **Laravel Pint**: Code style formatting
- **PHPUnit**: Automated testing with coverage
- **Composer Audit**: Security vulnerability scanning
- **Secret Detection**: Prevent credential commits
- **Markdown Linting**: Documentation quality

### Setup

```bash
# Setup pre-commit hooks (included in make setup)
make setup-pre-commit

# Manual quality checks
make full-quality-check  # Complete analysis
make phpstan            # Static analysis only
make code-style         # Fix formatting
make test-coverage      # Tests with coverage
make security-audit     # Security scan
```

Pre-commit hooks automatically run on every commit, ensuring consistent code quality across the team.

**📖 [Pre-commit Setup Guide](./docs/general/pre-commit-setup.md)** | **📖 [Code Quality Tools](./docs/general/code-quality-tools.md)**

## Docker Environment

Professional Docker setup with separate development and production configurations.

### Development Stack

- **PostgreSQL**: Primary database
- **Redis**: Caching and sessions
- **MailHog**: Email testing
- **Workspace**: Full development environment with tools

### Production Stack

- **Nginx**: Web server
- **PHP-FPM**: Optimized PHP processing
- **PostgreSQL**: Production database
- **Redis**: Production caching

```bash
# Development
make dev              # Start development stack
make shell            # Access workspace
make db-shell         # Access PostgreSQL

# Production
make prod-build       # Build and deploy production
make prod-logs        # View production logs
```

**📖 [Complete Docker Documentation](./docker/README.md)**

## Authentication

Laravel Passport OAuth2 implementation with automated setup.

### Features

- OAuth2 server with personal access tokens
- API authentication middleware
- Automated client and key generation
- Production-ready configuration

### Setup

```bash
# Automatic setup (included in make setup)
make passport-setup

# Manual setup
make passport-install
make passport-keys
make passport-client
```

**📖 [Passport Setup Guide](./docs/general/passport-setup.md)**

## Documentation

Comprehensive guides available in the `docs/` directory:

- **[Architecture Guide](./docs/general/architecture.md)** - SOLID principles and project structure
- **[Service Repository Pattern](./docs/general/service-repository-pattern.md)** - Implementation details
- **[Route Generation](./docs/general/route-generation.md)** - CRUD command reference
- **[DevOps Guide](./docs/general/devops.md)** - Docker and deployment
- **[Code Quality Tools](./docs/general/code-quality-tools.md)** - Quality automation
- **[Pre-commit Setup](./docs/general/pre-commit-setup.md)** - Hook configuration
- **[Passport Setup](./docs/general/passport-setup.md)** - OAuth2 authentication

**📖 [Documentation Index](./docs/README.md)**

## Version History

### v2.1.0 (Current)

_January 2025_

- Enhanced CRUD generation with `--only` and `--no-resource` flags
- Modern PHP 8.2+ syntax with constructor property promotion
- Improved command validation and error handling
- Dynamic output messaging

### v2.0.0

_January 2025_

- Complete `make:structure` command with selective generation
- Repository pattern with service layer architecture
- Laravel Passport OAuth2 integration
- Comprehensive Docker development environment

### v1.0.0

_January 2025_

- Initial Laravel API boilerplate
- Basic CRUD generation foundation
- User authentication setup
- Core repository and service structure

---

**License:** MIT | **PHP:** 8.2+ | **Laravel:** 12.0+

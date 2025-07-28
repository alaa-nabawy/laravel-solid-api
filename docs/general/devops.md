# Laravel API SOLID - DevOps Documentation

## 🚀 Development Operations Guide

This document covers development operations, deployment strategies, and environment management for the Laravel API SOLID project.

## 🛠️ Development Environment Setup

### Prerequisites

- PHP 8.2 or higher
- Composer 2.x
- Node.js 18+ and npm
- SQLite (default) or MySQL/PostgreSQL
- Git

### Initial Setup

1. **Clone the repository**

    ```bash
    git clone <repository-url>
    cd laravel-api-solid
    ```

2. **Install PHP dependencies**

    ```bash
    composer install
    ```

3. **Install Node.js dependencies**

    ```bash
    npm install
    ```

4. **Environment configuration**

    ```bash
    cp .env.example .env
    php artisan key:generate
    ```

5. **Database setup**

    ```bash
    touch database/database.sqlite  # For SQLite
    php artisan migrate
    php artisan db:seed
    ```

6. **Passport setup**

    ```bash
    php artisan passport:install
    ```

## 🔧 Development Tools

### Available Scripts

#### Composer Scripts

- `composer dev` - Start development environment with concurrent processes
- `composer test` - Run the test suite
- `composer pint` - Fix code style issues

#### NPM Scripts

- `npm run dev` - Start Vite development server
- `npm run build` - Build assets for production

#### Artisan Commands

- `php artisan serve` - Start development server
- `php artisan queue:work` - Process queue jobs
- `php artisan pail` - Real-time log monitoring

### Development Workflow

1. **Start development environment**

    ```bash
    composer dev
    ```

    This starts:
    - Laravel development server (port 8000)
    - Queue worker
    - Log monitoring (Pail)
    - Vite development server

2. **Code style and quality**

    ```bash
    composer pint        # Fix code style
    composer test        # Run tests
    ```

## 🐳 Professional Docker Architecture

This project includes a comprehensive Docker setup with separate development and production environments, designed for professional deployment with managed cloud services.

### Architecture Overview

- **Development Environment** (`compose.dev.yaml`) - Full local development stack
- **Production Environment** (`compose.prod.yaml`) - Optimized for deployment with external managed services
- **Multi-stage Dockerfile** - Optimized builds for both environments
- **Managed Database Strategy** - External PostgreSQL for production scalability

### Development Environment

#### Services

| Service   | Container             | Port      | Purpose                      |
| --------- | --------------------- | --------- | ---------------------------- |
| app       | laravel-app-dev       | 9000      | PHP-FPM application server   |
| nginx     | laravel-nginx-dev     | 8000      | Web server and reverse proxy |
| postgres  | laravel-postgres-dev  | 5432      | PostgreSQL database          |
| workspace | laravel-workspace-dev | -         | Development tools and CLI    |
| redis     | laravel-redis-dev     | 6379      | Cache and session storage    |
| mailhog   | laravel-mailhog-dev   | 1025/8025 | Email testing                |

#### Quick Start

```bash
# Initial setup
make setup          # Complete setup with dependencies
make dev            # Start development environment
make shell          # Access workspace shell

# Development workflow
make artisan cmd="migrate"     # Run Laravel commands
make npm-dev                   # Start Vite dev server
make test                      # Run tests
```

#### Development Features

- **Live Code Editing**: Volume mounts for real-time development
- **Xdebug**: Debugging support for PHP
- **Node.js & npm**: Frontend development tools
- **PostgreSQL**: Primary database with test database
- **SQLite**: Alternative lightweight database
- **MailHog**: Email testing at <http://localhost:8025>
- **Application**: Available at <http://localhost:8000>

### Production Environment

#### Services

| Service   | Container              | Purpose                   |
| --------- | ---------------------- | ------------------------- |
| app       | laravel-app-prod       | Optimized PHP-FPM server  |
| nginx     | laravel-nginx-prod     | Production web server     |
| queue     | laravel-queue-prod     | Background job processing |
| scheduler | laravel-scheduler-prod | Laravel cron jobs         |

#### Production Features

- **Multi-stage Build**: Optimized production images
- **External Database**: Connects to managed PostgreSQL (RDS, etc.)
- **Health Checks**: Container health monitoring
- **Security**: Non-root users, minimal attack surface
- **Performance**: OPcache, optimized configurations
- **Scalability**: Horizontal scaling ready

#### Production Deployment

```bash
# Configure environment
cp .env.prod .env
# Edit .env with your production values

# Deploy
./scripts/docker-prod.sh deploy

# Management commands
./scripts/docker-prod.sh status
./scripts/docker-prod.sh scale queue 3
./scripts/docker-prod.sh logs app 100
```

### Managed Database Strategy

#### Why External Managed PostgreSQL?

The production environment **deliberately excludes** a PostgreSQL container and connects to external managed database services (AWS RDS, Google Cloud SQL, Azure Database).

**Benefits:**

1. **Disaster Recovery**: Automated backups, cross-region replication, point-in-time recovery
2. **Automatic Backups**: Continuous transaction logs, daily snapshots, backup encryption
3. **Vertical Scaling**: CPU/memory scaling without downtime, read replicas, performance insights
4. **Security & Compliance**: Encryption at rest/transit, VPC isolation, SOC/PCI/HIPAA compliance
5. **Operational Benefits**: Managed maintenance, 24/7 support, 99.99% uptime SLA

#### Required Environment Variables

```env
# Database (Managed PostgreSQL)
DB_HOST=your-rds-endpoint.amazonaws.com
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password
DB_SSLMODE=require

# Cache (Managed Redis)
REDIS_HOST=your-redis-endpoint.amazonaws.com
REDIS_PASSWORD=your_redis_password
REDIS_TLS=true

# Storage (AWS S3)
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_BUCKET=your_s3_bucket
```

### Docker Configuration Files

```
docker/
├── common/
│   └── php-fpm.Dockerfile     # Multi-stage PHP-FPM container
├── dev/
│   ├── nginx.conf             # Development web server config
│   ├── php.ini                # Development PHP settings
│   ├── php-fpm.conf           # Development PHP-FPM pool
│   ├── xdebug.ini             # Xdebug configuration
│   ├── workspace.Dockerfile   # Development workspace
│   └── postgres-init.sql      # PostgreSQL initialization
└── prod/
    ├── nginx.conf             # Production web server config
    ├── php.ini                # Production PHP settings
    └── php-fpm.conf           # Production PHP-FPM pool
```

### Available Commands

The `Makefile` provides 30+ automation commands:

```bash
# Development
make dev                    # Start development environment
make shell                  # Access workspace shell
make artisan cmd="migrate" # Run artisan commands
make composer-install       # Install PHP dependencies
make npm-install           # Install Node dependencies
make test                  # Run tests

# Production
make prod-build            # Build production environment
make setup-prod            # Initial production setup

# Database
make migrate               # Run migrations
make db-shell              # Access PostgreSQL shell
make db-dump               # Create database dump

# Monitoring
make status                # Show container status
make logs                  # Show logs
```

### Security Features

- **Non-root container execution**
- **Security headers and rate limiting**
- **Secrets management via environment variables**
- **Minimal production images (no dev tools)**
- **SSL/TLS ready configuration**
- **Network isolation and proper firewall rules**

### Performance Optimizations

- **Multi-stage builds** for minimal image size
- **OPcache enabled** in production
- **Gzip compression** and HTTP/2 ready
- **Asset compilation** and optimization
- **Redis caching** for sessions and application cache
- **Database connection pooling** ready

### Monitoring & Observability

- **Health check endpoints** (/health, /ping, /status)
- **Structured JSON logging** for production
- **Container resource monitoring**
- **Application performance monitoring** ready
- **Log aggregation and alerting** ready

📖 **[Complete Docker Documentation](../../docker/README.md)** - Detailed setup and usage guide

## 🗄️ Database Management

### Supported Databases

- **SQLite** (default for development)
- **MySQL** (recommended for production)
- **PostgreSQL** (alternative for production)

### Migration Strategy

```bash
# Create new migration
php artisan make:migration create_posts_table

# Run migrations
php artisan migrate

# Rollback migrations
php artisan migrate:rollback

# Fresh migration with seeding
php artisan migrate:fresh --seed
```

### Database Seeding

```bash
# Create seeder
php artisan make:seeder PostSeeder

# Run specific seeder
php artisan db:seed --class=PostSeeder

# Run all seeders
php artisan db:seed
```

## 🔐 Security Configuration

### Environment Variables

```env
# Application
APP_KEY=base64:...
APP_ENV=production
APP_DEBUG=false

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=laravel_api
DB_USERNAME=root
DB_PASSWORD=

# Passport
PASSPORT_PRIVATE_KEY="-----BEGIN RSA PRIVATE KEY-----..."
PASSPORT_PUBLIC_KEY="-----BEGIN PUBLIC KEY-----..."
```

### Security Best Practices

1. **Never commit `.env` files**
2. **Use strong, unique APP_KEY**
3. **Secure database credentials**
4. **Configure HTTPS in production**
5. **Regular security updates**

## 🚀 Deployment Strategies

### Production Deployment Checklist

1. **Environment Setup**
    - [ ] Configure production `.env`
    - [ ] Set `APP_ENV=production`
    - [ ] Set `APP_DEBUG=false`
    - [ ] Configure database credentials
    - [ ] Set up SSL certificates

2. **Application Deployment**

    ```bash
    # Install dependencies
    composer install --no-dev --optimize-autoloader

    # Build assets
    npm ci
    npm run build

    # Cache configuration
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache

    # Run migrations
    php artisan migrate --force

    # Install Passport keys
    php artisan passport:keys
    ```

3. **Server Configuration**
    - Configure web server (Nginx/Apache)
    - Set up process manager (Supervisor)
    - Configure queue workers
    - Set up log rotation

### Zero-Downtime Deployment

1. **Blue-Green Deployment**
    - Deploy to staging environment
    - Run tests and validation
    - Switch traffic to new version
    - Keep old version as backup

2. **Rolling Deployment**
    - Deploy to subset of servers
    - Gradually roll out to all servers
    - Monitor for issues during rollout

## 📊 Monitoring and Logging

### Application Monitoring

- **Laravel Telescope**: Development debugging
- **Laravel Horizon**: Queue monitoring
- **Laravel Pulse**: Application performance

### Log Management

```bash
# Real-time log monitoring
php artisan pail

# Log channels configuration in config/logging.php
# - single: Single file
# - daily: Daily rotation
# - slack: Slack notifications
# - stack: Multiple channels
```

### Performance Monitoring

- **Application Performance Monitoring (APM)**
- **Database query monitoring**
- **Cache hit/miss ratios**
- **Queue job processing times**

## 🔄 CI/CD Pipeline

### GitHub Actions Example

```yaml
name: Laravel CI/CD

on: [push, pull_request]

jobs:
    test:
        runs-on: ubuntu-latest
        steps:
            - uses: actions/checkout@v3
            - name: Setup PHP
              uses: shivammathur/setup-php@v2
              with:
                  php-version: 8.2
            - name: Install dependencies
              run: composer install
            - name: Run tests
              run: php artisan test
            - name: Code style check
              run: ./vendor/bin/pint --test
```

### Deployment Automation

1. **Automated testing** on pull requests
2. **Code quality checks** (Pint, PHPStan)
3. **Security scanning** (Composer audit)
4. **Automated deployment** to staging
5. **Manual approval** for production

## 🛡️ Backup and Recovery

### Database Backups

```bash
# MySQL backup
mysqldump -u username -p database_name > backup.sql

# PostgreSQL backup
pg_dump -U username database_name > backup.sql

# Automated backup script
php artisan backup:run
```

### File System Backups

- **Application files**: Version controlled in Git
- **Storage files**: Regular backup to cloud storage
- **Configuration files**: Secure backup of `.env` files

### Disaster Recovery

1. **Recovery Time Objective (RTO)**: < 1 hour
2. **Recovery Point Objective (RPO)**: < 15 minutes
3. **Backup testing**: Monthly restore tests
4. **Documentation**: Detailed recovery procedures

## 📈 Scaling Strategies

### Horizontal Scaling

- **Load balancers**: Distribute traffic across multiple servers
- **Database clustering**: Master-slave replication
- **Cache layers**: Redis/Memcached clusters
- **CDN**: Static asset distribution

### Vertical Scaling

- **Server resources**: CPU, RAM, storage upgrades
- **Database optimization**: Query optimization, indexing
- **Application optimization**: Code profiling, caching

### Microservices Migration

- **Service decomposition**: Split by domain boundaries
- **API gateway**: Centralized routing and authentication
- **Service discovery**: Dynamic service registration
- **Data consistency**: Event-driven architecture

## 🔧 Troubleshooting

### Common Issues

1. **Permission Issues**

    ```bash
    sudo chown -R www-data:www-data storage bootstrap/cache
    sudo chmod -R 775 storage bootstrap/cache
    ```

2. **Cache Issues**

    ```bash
    php artisan cache:clear
    php artisan config:clear
    php artisan route:clear
    php artisan view:clear
    ```

3. **Queue Issues**

    ```bash
    php artisan queue:restart
    php artisan queue:work --tries=3
    ```

### Debug Tools

- **Laravel Telescope**: Request/response debugging
- **Laravel Debugbar**: Development debugging
- **Xdebug**: Step-through debugging
- **Laravel Pail**: Real-time log monitoring

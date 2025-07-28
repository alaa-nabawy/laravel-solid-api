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

## 🐳 Docker Support (Laravel Sail)

### Setup with Sail
```bash
# Install Sail
composer require laravel/sail --dev

# Publish Sail configuration
php artisan sail:install

# Start containers
./vendor/bin/sail up -d

# Run commands through Sail
./vendor/bin/sail artisan migrate
./vendor/bin/sail composer install
./vendor/bin/sail npm install
```

### Sail Services
- **Laravel Application**: Main application container
- **MySQL/PostgreSQL**: Database service
- **Redis**: Caching and session storage
- **Mailhog**: Email testing
- **Selenium**: Browser testing

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
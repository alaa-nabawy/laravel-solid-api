# Docker Setup for Laravel 12 with PostgreSQL

This directory contains the Docker configuration for running Laravel 12 in both development and production environments with PostgreSQL as the primary database.

## Quick Start

### Development Environment

```bash
# Initial setup
make setup

# Or manually:
cp .env.dev .env
make dev
make composer-install
make npm-install
make artisan cmd="key:generate"
make migrate
make passport-install
```

### Production Environment

```bash
# Configure environment
cp .env.prod .env
# Edit .env with your production values

# Deploy
make prod-build
make setup-prod
```

## Directory Structure

```
docker/
├── README.md                  # This file
├── common/
│   └── php-fpm.Dockerfile     # Multi-stage PHP-FPM Dockerfile
├── dev/
│   ├── nginx.conf             # Development Nginx configuration
│   ├── php.ini                # Development PHP settings
│   ├── php-fpm.conf           # Development PHP-FPM pool
│   ├── xdebug.ini             # Xdebug configuration
│   ├── workspace.Dockerfile   # Development workspace container
│   ├── workspace-php.ini      # Workspace PHP settings
│   └── postgres-init.sql      # PostgreSQL initialization
└── prod/
    ├── nginx.conf             # Production Nginx configuration
    ├── php.ini                # Production PHP settings
    └── php-fpm.conf           # Production PHP-FPM pool
```

## Development Environment

### Services

| Service   | Container Name        | Port      | Purpose                      |
| --------- | --------------------- | --------- | ---------------------------- |
| app       | laravel-app-dev       | 9000      | PHP-FPM application server   |
| nginx     | laravel-nginx-dev     | 8000      | Web server and reverse proxy |
| postgres  | laravel-postgres-dev  | 5432      | PostgreSQL database          |
| workspace | laravel-workspace-dev | -         | Development tools and CLI    |
| redis     | laravel-redis-dev     | 6379      | Cache and session storage    |
| mailhog   | laravel-mailhog-dev   | 1025/8025 | Email testing                |

### Features

- **Live Code Editing**: Volume mounts for real-time development
- **Xdebug**: Debugging support for PHP
- **Node.js & npm**: Frontend development tools
- **PostgreSQL**: Primary database with test database
- **SQLite**: Alternative lightweight database
- **Redis**: Caching and session storage
- **MailHog**: Email testing and debugging

### Development URLs

- **Laravel Application**: <http://localhost:8000>
- **MailHog Web Interface**: <http://localhost:8025>
- **PostgreSQL**: localhost:5432 (user: laravel, password: secret)
- **Redis**: localhost:6379

### Common Development Commands

```bash
# Start development environment
make dev

# Access workspace shell
make shell

# Run Laravel commands
make artisan cmd="migrate"
make artisan cmd="tinker"

# Frontend development
make npm cmd="install"
make npm-dev

# Run tests
make test

# View logs
make dev-logs

# Stop environment
make down
```

## Production Environment

### Services

| Service   | Container Name         | Port   | Purpose                   |
| --------- | ---------------------- | ------ | ------------------------- |
| app       | laravel-app-prod       | 9000   | Optimized PHP-FPM server  |
| nginx     | laravel-nginx-prod     | 80/443 | Production web server     |
| queue     | laravel-queue-prod     | -      | Background job processing |
| scheduler | laravel-scheduler-prod | -      | Laravel cron jobs         |

### Features

- **Multi-stage Build**: Optimized production images
- **External Database**: Connects to managed PostgreSQL (RDS, etc.)
- **Health Checks**: Container health monitoring
- **Security**: Non-root users, minimal attack surface
- **Performance**: OPcache, optimized configurations
- **Scalability**: Horizontal scaling ready
- **Monitoring**: Structured logging and metrics

### Production Requirements

#### Environment Variables

Required environment variables for production:

```bash
# Application
APP_KEY=base64:...
APP_URL=https://your-domain.com

# Database (Managed PostgreSQL)
DB_HOST=your-rds-endpoint.amazonaws.com
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password

# Cache (Managed Redis)
REDIS_HOST=your-redis-endpoint.amazonaws.com
REDIS_PASSWORD=your_redis_password

# Mail
MAIL_HOST=your-smtp-host
MAIL_USERNAME=your_smtp_username
MAIL_PASSWORD=your_smtp_password

# Storage
AWS_ACCESS_KEY_ID=your_access_key
AWS_SECRET_ACCESS_KEY=your_secret_key
AWS_BUCKET=your_s3_bucket
```

#### Managed Services

The production environment is designed to work with managed cloud services:

- **Database**: AWS RDS, Google Cloud SQL, Azure Database
- **Cache**: AWS ElastiCache, Google Memorystore, Azure Cache
- **Storage**: AWS S3, Google Cloud Storage, Azure Blob
- **Email**: AWS SES, SendGrid, Mailgun

### Production Commands

```bash
# Deploy production
./scripts/docker-prod.sh deploy

# Update production
./scripts/docker-prod.sh update

# Scale services
./scripts/docker-prod.sh scale queue 3

# Run artisan commands
./scripts/docker-prod.sh artisan "cache:clear"

# View logs
./scripts/docker-prod.sh logs app 100

# Check status
./scripts/docker-prod.sh status

# Create backup
./scripts/docker-prod.sh backup
```

## Configuration Files

### PHP Configuration

#### Development (`dev/php.ini`)

- Error reporting enabled
- Xdebug enabled
- Higher memory limits
- Development-friendly settings

#### Production (`prod/php.ini`)

- Error reporting disabled
- OPcache enabled
- Optimized memory limits
- Security-hardened settings

### Nginx Configuration

#### Development (`dev/nginx.conf`)

- Basic security headers
- Development-friendly timeouts
- Static file serving
- Health check endpoint

#### Production (`prod/nginx.conf`)

- Comprehensive security headers
- Rate limiting
- Gzip compression
- SSL/TLS ready
- Performance optimizations

### PHP-FPM Configuration

#### Development (`dev/php-fpm.conf`)

- Lower process limits
- Development logging
- Relaxed timeouts

#### Production (`prod/php-fpm.conf`)

- Optimized process management
- Production logging
- Performance tuning

## Multi-stage Dockerfile

The `common/php-fpm.Dockerfile` uses multi-stage builds:

1. **Base Stage**: Common PHP extensions and dependencies
2. **Development Stage**: Adds Xdebug and development tools
3. **Builder Stage**: Installs dependencies and builds assets
4. **Production Stage**: Minimal, optimized final image

### Build Targets

```bash
# Development build
docker build --target development -t laravel-app:dev .

# Production build
docker build --target production -t laravel-app:prod .
```

## Database Strategy

### Development

- **PostgreSQL Container**: Full-featured local database
- **SQLite Option**: Lightweight alternative for simple development
- **Test Database**: Separate database for testing
- **Extensions**: UUID, trigram, unaccent support

### Production

- **Managed PostgreSQL**: External managed database service
- **High Availability**: Multi-AZ deployments
- **Automated Backups**: Point-in-time recovery
- **Security**: Encryption, VPC isolation, IAM integration

### Why Managed Database?

1. **Disaster Recovery**: Automated backups and cross-region replication
2. **Scalability**: Vertical and horizontal scaling without downtime
3. **Security**: Enterprise-grade security and compliance
4. **Maintenance**: Automated updates and maintenance windows
5. **Monitoring**: Built-in performance insights and alerting

## Security Considerations

### Development

- Isolated Docker network
- Non-root users where possible
- Debug tools available for development
- Local-only access

### Production

- Minimal attack surface
- No development tools in production images
- Security headers and rate limiting
- Secrets management integration
- Regular security updates
- Non-root container execution

## Performance Optimizations

### Development

- Volume caching for better performance
- Delegated volumes for storage directories
- Composer and npm cache volumes

### Production

- OPcache enabled
- Optimized autoloader
- Asset compilation and minification
- Gzip compression
- HTTP/2 ready
- Connection pooling

## Monitoring and Logging

### Development

- Container logs via Docker Compose
- Xdebug for debugging
- MailHog for email testing
- Real-time log streaming

### Production

- Structured JSON logging
- Health check endpoints
- Resource usage monitoring
- External monitoring integration
- Log aggregation ready
- Performance metrics

## Troubleshooting

### Common Issues

#### Permission Issues

```bash
# Fix storage permissions
make artisan cmd="storage:link"
sudo chown -R $USER:$USER storage bootstrap/cache
```

#### Database Connection

```bash
# Check PostgreSQL status
make artisan cmd="migrate:status"
docker compose -f compose.dev.yaml exec postgres pg_isready -U laravel
```

#### Xdebug Not Working

```bash
# Check Xdebug configuration
make artisan cmd="--version"
docker compose -f compose.dev.yaml exec workspace php -m | grep xdebug
```

#### Asset Build Issues

```bash
# Clear npm cache and reinstall
make npm cmd="cache clean --force"
make npm cmd="install"
```

### Debug Commands

```bash
# Check container logs
docker compose -f compose.dev.yaml logs app

# Access container shell
docker compose -f compose.dev.yaml exec app sh

# Check PHP configuration
docker compose -f compose.dev.yaml exec app php -i

# Test database connection
docker compose -f compose.dev.yaml exec app php artisan tinker
```

## Best Practices

1. **Use .dockerignore**: Exclude unnecessary files from build context
2. **Multi-stage builds**: Optimize production image size
3. **Non-root users**: Run containers as non-root for security
4. **Health checks**: Implement proper health check endpoints
5. **Secrets management**: Use environment variables for sensitive data
6. **Resource limits**: Set appropriate CPU and memory limits
7. **Logging**: Use structured logging for better observability
8. **Monitoring**: Implement comprehensive monitoring and alerting

## Contributing

When modifying Docker configurations:

1. Test changes in development environment first
2. Update documentation for any configuration changes
3. Ensure production builds still work
4. Test with clean Docker environment
5. Update version tags appropriately

For more information, see the main project documentation.

# CI Docker Setup - Production Parity

This document explains the Docker-based CI/CD pipeline that ensures your tests run in an environment identical to production.

## Overview

The CI pipeline now uses Docker containers to run all tests and quality checks, providing true production parity. This approach catches environment-specific issues that traditional CI setups miss.

## Architecture

### Docker Compose Files

1. **`compose.dev.yaml`** - Base development configuration
2. **`compose.ci.yaml`** - CI-specific overrides for testing environment

### Key Services

- **workspace**: PHP CLI container with all development tools
- **postgres**: PostgreSQL 16 database
- **redis**: Redis for caching and sessions
- **app**: PHP-FPM application container (built but not used in CI)
- **nginx**: Web server (not started in CI)

## CI Pipeline Flow

### 1. Environment Setup

```yaml
- Checkout code
- Set up Docker Buildx
- Copy .env.example to .env
```

### 2. Container Orchestration

```yaml
- Build all Docker images in parallel
- Start PostgreSQL and Redis services
- Wait for services to be healthy
- Start workspace container
- Verify workspace is ready
```

### 3. Application Setup

```yaml
- Install Composer dependencies
- Generate application key
- Install NPM dependencies
- Build frontend assets
- Run database migrations
- Setup Laravel Passport
```

### 4. Quality Assurance

```yaml
- Run PHPStan static analysis
- Run Laravel Pint code formatting checks
- Run PHPUnit tests with coverage
- Run Composer security audit
```

### 5. Cleanup

```yaml
- Stop and remove all containers
- Clean up volumes
```

## Benefits of Docker-Based CI

### Production Parity

- **Identical Environment**: Same PHP version, extensions, and system packages
- **Service Integration**: Tests run against the same PostgreSQL and Redis versions
- **Container Networking**: Services communicate exactly as in production
- **Volume Mounting**: File permissions and storage behavior match production

### Reliability

- **Isolation**: No conflicts with GitHub Actions runner environment
- **Reproducibility**: Same results locally and in CI
- **Dependency Management**: All tools and versions are containerized

### Debugging

- **Local Reproduction**: Run the exact same commands locally
- **Service Logs**: Access to all service logs for troubleshooting
- **Environment Consistency**: Issues found in CI will reproduce locally

## Configuration Files

### compose.ci.yaml

Overrides development settings for CI:

```yaml
services:
    postgres:
        environment:
            POSTGRES_DB: laravel_test # Use test database

    workspace:
        environment:
            - APP_ENV=testing
            - DB_DATABASE=laravel_test
            - CACHE_DRIVER=redis
            - SESSION_DRIVER=redis
```

### Health Checks

The pipeline includes robust health checks:

```bash
# PostgreSQL readiness
timeout 60 bash -c 'until docker-compose exec -T postgres pg_isready -U laravel; do sleep 2; done'

# Redis readiness
timeout 60 bash -c 'until docker-compose exec -T redis redis-cli ping; do sleep 2; done'

# Workspace readiness
timeout 60 bash -c 'until docker-compose exec -T workspace php --version; do sleep 2; done'
```

## Script Compatibility

All existing scripts work seamlessly:

- **`setup-passport.sh`**: Automatically detects Docker environment
- **`code-quality.sh`**: Runs in containerized environment
- **Development scripts**: Continue to work in local Docker setup

## Local Development

Developers can run the exact same CI pipeline locally:

```bash
# Start the full CI pipeline locally
docker-compose -f compose.dev.yaml -f compose.ci.yaml up -d

# Run any CI command
docker-compose -f compose.dev.yaml -f compose.ci.yaml exec -T workspace php artisan test

# Cleanup
docker-compose -f compose.dev.yaml -f compose.ci.yaml down -v
```

## Troubleshooting

### Common Issues

1. **Service Not Ready**: Increase timeout values in health checks
2. **Permission Issues**: Ensure workspace user has correct permissions
3. **Memory Limits**: Adjust container memory limits if needed

### Debugging Commands

```bash
# Check service logs
docker-compose -f compose.dev.yaml -f compose.ci.yaml logs postgres
docker-compose -f compose.dev.yaml -f compose.ci.yaml logs workspace

# Access workspace container
docker-compose -f compose.dev.yaml -f compose.ci.yaml exec workspace bash

# Check database connectivity
docker-compose -f compose.dev.yaml -f compose.ci.yaml exec workspace php artisan migrate:status
```

## Performance Optimizations

- **Parallel Building**: Images build simultaneously
- **Layer Caching**: Docker layer caching reduces build times
- **Optimized Dependencies**: Composer autoloader optimization
- **Minimal Services**: Only necessary services run in CI

## Security

- **Isolated Environment**: Each CI run uses fresh containers
- **No Persistent Data**: Volumes are cleaned up after each run
- **Secure Secrets**: Database passwords are marked as allowlist secrets
- **Minimal Attack Surface**: Only required ports are exposed

This setup ensures that "if it works in CI, it works in production" with the highest degree of confidence.

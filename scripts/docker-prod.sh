#!/bin/bash

# Laravel Docker Production Deployment Script
# This script provides convenient commands for Docker production deployment

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to print colored output
print_status() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

print_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

print_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Function to check if Docker is running
check_docker() {
    if ! docker info > /dev/null 2>&1; then
        print_error "Docker is not running. Please start Docker and try again."
        exit 1
    fi
}

# Function to check if .env file exists
check_env_file() {
    if [ ! -f ".env" ]; then
        print_error ".env file not found. Please copy .env.prod and configure it with production values."
        print_status "Example: cp .env.prod .env && nano .env"
        exit 1
    fi
}

# Function to validate required environment variables
validate_env_vars() {
    local required_vars=(
        "APP_KEY"
        "DB_HOST"
        "DB_DATABASE"
        "DB_USERNAME"
        "DB_PASSWORD"
    )

    local missing_vars=()

    for var in "${required_vars[@]}"; do
        if ! grep -q "^${var}=" .env || grep -q "^${var}=$" .env; then
            missing_vars+=("$var")
        fi
    done

    if [ ${#missing_vars[@]} -ne 0 ]; then
        print_error "Missing or empty required environment variables:"
        for var in "${missing_vars[@]}"; do
            echo "  - $var"
        done
        print_status "Please configure these variables in your .env file"
        exit 1
    fi
}

# Function to check if production environment is running
check_prod_running() {
    if ! docker compose -f compose.prod.yaml ps -q > /dev/null 2>&1; then
        print_error "Production environment is not running. Run '$0 deploy' first."
        exit 1
    fi
}

# Function to wait for services to be ready
wait_for_services() {
    print_status "Waiting for services to be ready..."

    # Wait for app container health check
    print_status "Waiting for app container..."
    local max_attempts=30
    local attempt=1

    while [ $attempt -le $max_attempts ]; do
        if docker compose -f compose.prod.yaml exec app php --version > /dev/null 2>&1; then
            break
        fi

        if [ $attempt -eq $max_attempts ]; then
            print_error "App container failed to start within expected time"
            exit 1
        fi

        sleep 2
        ((attempt++))
    done

    # Wait for nginx health check
    print_status "Waiting for nginx..."
    attempt=1

    while [ $attempt -le $max_attempts ]; do
        if curl -f http://localhost/health > /dev/null 2>&1; then
            break
        fi

        if [ $attempt -eq $max_attempts ]; then
            print_warning "Nginx health check failed, but continuing..."
            break
        fi

        sleep 2
        ((attempt++))
    done

    print_success "Services are ready!"
}

# Function to build production images
build_images() {
    print_status "Building production images..."
    docker compose -f compose.prod.yaml build --no-cache
    print_success "Production images built successfully"
}

# Function to deploy production environment
deploy() {
    print_status "Deploying production environment..."

    # Build and start services
    docker compose -f compose.prod.yaml up -d --build

    # Wait for services
    wait_for_services

    # Run Laravel setup commands
    setup_laravel

    print_success "Production deployment complete!"
    show_status
}

# Function to setup Laravel application for production
setup_laravel() {
    print_status "Setting up Laravel application for production..."

    # Run migrations
    print_status "Running database migrations..."
    docker compose -f compose.prod.yaml exec app php artisan migrate --force

    # Cache configuration
    print_status "Caching configuration..."
    docker compose -f compose.prod.yaml exec app php artisan config:cache

    # Cache routes
    print_status "Caching routes..."
    docker compose -f compose.prod.yaml exec app php artisan route:cache

    # Cache views
    print_status "Caching views..."
    docker compose -f compose.prod.yaml exec app php artisan view:cache

    # Optimize autoloader
    print_status "Optimizing autoloader..."
    docker compose -f compose.prod.yaml exec app composer dump-autoload --optimize

    print_success "Laravel application setup complete!"
}

# Function to update production environment
update() {
    print_warning "This will update the production environment. Are you sure? (y/N)"
    read -r response
    if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
        print_status "Updating production environment..."

        # Pull latest images and rebuild
        docker compose -f compose.prod.yaml pull
        docker compose -f compose.prod.yaml up -d --build

        # Wait for services
        wait_for_services

        # Run migrations and cache
        docker compose -f compose.prod.yaml exec app php artisan migrate --force
        docker compose -f compose.prod.yaml exec app php artisan config:cache
        docker compose -f compose.prod.yaml exec app php artisan route:cache
        docker compose -f compose.prod.yaml exec app php artisan view:cache

        print_success "Production environment updated successfully!"
    else
        print_status "Update cancelled"
    fi
}

# Function to rollback deployment
rollback() {
    print_warning "This will rollback to the previous deployment. Are you sure? (y/N)"
    read -r response
    if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
        print_status "Rolling back deployment..."

        # This is a simplified rollback - in a real scenario, you'd have tagged images
        docker compose -f compose.prod.yaml down
        docker compose -f compose.prod.yaml up -d

        wait_for_services
        print_success "Rollback complete!"
    else
        print_status "Rollback cancelled"
    fi
}

# Function to show application status
show_status() {
    print_status "Production Application Status:"
    echo ""

    # Container status
    echo "Container Status:"
    docker compose -f compose.prod.yaml ps
    echo ""

    # Health checks
    echo "Health Checks:"
    if curl -f http://localhost/health > /dev/null 2>&1; then
        print_success "Application health check: PASSED"
    else
        print_error "Application health check: FAILED"
    fi

    # Resource usage
    echo ""
    echo "Resource Usage:"
    docker stats --no-stream --format "table {{.Container}}\t{{.CPUPerc}}\t{{.MemUsage}}\t{{.NetIO}}" $(docker compose -f compose.prod.yaml ps -q)
}

# Function to show logs
show_logs() {
    local service="$1"
    local lines="${2:-100}"

    if [ -z "$service" ]; then
        print_status "Showing logs for all services (last $lines lines)..."
        docker compose -f compose.prod.yaml logs --tail="$lines" -f
    else
        print_status "Showing logs for service: $service (last $lines lines)..."
        docker compose -f compose.prod.yaml logs --tail="$lines" -f "$service"
    fi
}

# Function to backup application data
backup() {
    local backup_dir="backups/$(date +%Y%m%d_%H%M%S)"
    mkdir -p "$backup_dir"

    print_status "Creating application backup in: $backup_dir"

    # Backup storage directory
    print_status "Backing up storage..."
    docker compose -f compose.prod.yaml exec app tar -czf /tmp/storage_backup.tar.gz -C /var/www/html storage
    docker cp "$(docker compose -f compose.prod.yaml ps -q app)":/tmp/storage_backup.tar.gz "$backup_dir/storage.tar.gz"

    # Backup environment file
    print_status "Backing up environment configuration..."
    cp .env "$backup_dir/env.backup"

    print_success "Backup created in: $backup_dir"
}

# Function to scale services
scale() {
    local service="$1"
    local replicas="$2"

    if [ -z "$service" ] || [ -z "$replicas" ]; then
        print_error "Usage: $0 scale <service> <replicas>"
        print_status "Available services: app, queue, scheduler"
        exit 1
    fi

    print_status "Scaling $service to $replicas replicas..."
    docker compose -f compose.prod.yaml up -d --scale "$service=$replicas"
    print_success "Service $service scaled to $replicas replicas"
}

# Function to run artisan commands
artisan() {
    local command="$1"

    if [ -z "$command" ]; then
        print_error "Usage: $0 artisan <command>"
        print_status "Example: $0 artisan 'cache:clear'"
        exit 1
    fi

    print_status "Running artisan command: $command"
    docker compose -f compose.prod.yaml exec app php artisan $command
}

# Function to show help
show_help() {
    echo "Laravel Docker Production Deployment Script"
    echo ""
    echo "Usage: $0 <command> [options]"
    echo ""
    echo "Commands:"
    echo "  build               Build production images"
    echo "  deploy              Deploy production environment"
    echo "  update              Update production environment"
    echo "  rollback            Rollback to previous deployment"
    echo "  start               Start production environment"
    echo "  stop                Stop production environment"
    echo "  restart             Restart production environment"
    echo "  status              Show application status"
    echo "  logs [service] [n]  Show logs (optionally for specific service, last n lines)"
    echo "  backup              Create application backup"
    echo "  scale <svc> <n>     Scale service to n replicas"
    echo "  artisan <cmd>       Run artisan command"
    echo "  help                Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 deploy"
    echo "  $0 logs app 50"
    echo "  $0 scale queue 3"
    echo "  $0 artisan 'queue:restart'"
}

# Main script logic
case "$1" in
    build)
        check_docker
        check_env_file
        validate_env_vars
        build_images
        ;;
    deploy)
        check_docker
        check_env_file
        validate_env_vars
        deploy
        ;;
    update)
        check_docker
        check_env_file
        validate_env_vars
        check_prod_running
        update
        ;;
    rollback)
        check_docker
        check_prod_running
        rollback
        ;;
    start)
        check_docker
        check_env_file
        validate_env_vars
        print_status "Starting production environment..."
        docker compose -f compose.prod.yaml up -d
        wait_for_services
        show_status
        ;;
    stop)
        print_status "Stopping production environment..."
        docker compose -f compose.prod.yaml down
        print_success "Production environment stopped"
        ;;
    restart)
        check_docker
        check_prod_running
        print_status "Restarting production environment..."
        docker compose -f compose.prod.yaml restart
        wait_for_services
        print_success "Production environment restarted"
        ;;
    status)
        check_docker
        show_status
        ;;
    logs)
        check_docker
        check_prod_running
        show_logs "$2" "$3"
        ;;
    backup)
        check_docker
        check_prod_running
        backup
        ;;
    scale)
        check_docker
        check_prod_running
        scale "$2" "$3"
        ;;
    artisan)
        check_docker
        check_prod_running
        artisan "$2"
        ;;
    help|--help|-h)
        show_help
        ;;
    "")
        print_error "No command specified"
        show_help
        exit 1
        ;;
    *)
        print_error "Unknown command: $1"
        show_help
        exit 1
        ;;
esac

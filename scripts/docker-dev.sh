#!/bin/bash

# Laravel Docker Development Helper Script
# This script provides convenient commands for Docker development workflow

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

# Function to check if development environment is running
check_dev_running() {
    if ! docker compose -f compose.dev.yaml ps -q > /dev/null 2>&1; then
        print_error "Development environment is not running. Run 'make dev' first."
        exit 1
    fi
}

# Function to wait for services to be ready
wait_for_services() {
    print_status "Waiting for services to be ready..."

    # Wait for PostgreSQL
    print_status "Waiting for PostgreSQL..."
    until docker compose -f compose.dev.yaml exec postgres pg_isready -U laravel > /dev/null 2>&1; do
        sleep 1
    done

    # Wait for app container
    print_status "Waiting for app container..."
    until docker compose -f compose.dev.yaml exec app php --version > /dev/null 2>&1; do
        sleep 1
    done

    print_success "All services are ready!"
}

# Function to setup Laravel application
setup_laravel() {
    print_status "Setting up Laravel application..."

    # Generate application key if not exists
    if ! grep -q "APP_KEY=base64:" .env 2>/dev/null; then
        print_status "Generating application key..."
        docker compose -f compose.dev.yaml exec workspace php artisan key:generate
    fi

    # Run migrations
    print_status "Running database migrations..."
    docker compose -f compose.dev.yaml exec workspace php artisan migrate --force

    # Install Passport if not already installed
    print_status "Setting up Laravel Passport..."
    docker compose -f compose.dev.yaml exec workspace php artisan passport:install --force

    # Clear and cache config
    print_status "Optimizing application..."
    docker compose -f compose.dev.yaml exec workspace php artisan config:clear
    docker compose -f compose.dev.yaml exec workspace php artisan route:clear
    docker compose -f compose.dev.yaml exec workspace php artisan view:clear

    print_success "Laravel application setup complete!"
}

# Function to run tests
run_tests() {
    print_status "Running Laravel tests..."
    docker compose -f compose.dev.yaml exec workspace php artisan test
}

# Function to show application status
show_status() {
    print_status "Application Status:"
    echo ""

    # Container status
    echo "Container Status:"
    docker compose -f compose.dev.yaml ps
    echo ""

    # Application URLs
    echo "Application URLs:"
    echo "  - Laravel App: http://localhost:8000"
    echo "  - MailHog: http://localhost:8025"
    echo "  - PostgreSQL: localhost:5432"
    echo "  - Redis: localhost:6379"
    echo ""

    # Database status
    echo "Database Status:"
    if docker compose -f compose.dev.yaml exec postgres pg_isready -U laravel > /dev/null 2>&1; then
        print_success "PostgreSQL is ready"
    else
        print_error "PostgreSQL is not ready"
    fi
}

# Function to backup database
backup_database() {
    local backup_file="backup_$(date +%Y%m%d_%H%M%S).sql"
    print_status "Creating database backup: $backup_file"

    docker compose -f compose.dev.yaml exec postgres pg_dump -U laravel laravel_dev > "$backup_file"
    print_success "Database backup created: $backup_file"
}

# Function to restore database
restore_database() {
    local backup_file="$1"

    if [ -z "$backup_file" ]; then
        print_error "Please provide backup file path"
        echo "Usage: $0 restore <backup_file.sql>"
        exit 1
    fi

    if [ ! -f "$backup_file" ]; then
        print_error "Backup file not found: $backup_file"
        exit 1
    fi

    print_warning "This will replace the current database. Are you sure? (y/N)"
    read -r response
    if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
        print_status "Restoring database from: $backup_file"
        docker compose -f compose.dev.yaml exec -T postgres psql -U laravel -d laravel_dev < "$backup_file"
        print_success "Database restored successfully"
    else
        print_status "Database restore cancelled"
    fi
}

# Function to clean up development environment
cleanup() {
    print_warning "This will stop containers and remove volumes. Are you sure? (y/N)"
    read -r response
    if [[ "$response" =~ ^([yY][eE][sS]|[yY])$ ]]; then
        print_status "Cleaning up development environment..."
        docker compose -f compose.dev.yaml down -v
        docker system prune -f
        print_success "Cleanup complete"
    else
        print_status "Cleanup cancelled"
    fi
}

# Function to show logs
show_logs() {
    local service="$1"

    if [ -z "$service" ]; then
        print_status "Showing logs for all services..."
        docker compose -f compose.dev.yaml logs -f
    else
        print_status "Showing logs for service: $service"
        docker compose -f compose.dev.yaml logs -f "$service"
    fi
}

# Function to show help
show_help() {
    echo "Laravel Docker Development Helper"
    echo ""
    echo "Usage: $0 <command> [options]"
    echo ""
    echo "Commands:"
    echo "  start           Start development environment"
    echo "  stop            Stop development environment"
    echo "  restart         Restart development environment"
    echo "  setup           Setup Laravel application"
    echo "  test            Run Laravel tests"
    echo "  status          Show application status"
    echo "  logs [service]  Show logs (optionally for specific service)"
    echo "  shell           Access workspace shell"
    echo "  backup          Create database backup"
    echo "  restore <file>  Restore database from backup"
    echo "  cleanup         Clean up containers and volumes"
    echo "  help            Show this help message"
    echo ""
    echo "Examples:"
    echo "  $0 start"
    echo "  $0 logs app"
    echo "  $0 restore backup_20240101_120000.sql"
}

# Main script logic
case "$1" in
    start)
        check_docker
        print_status "Starting development environment..."
        cp .env.dev .env 2>/dev/null || true
        docker compose -f compose.dev.yaml up -d
        wait_for_services
        show_status
        ;;
    stop)
        print_status "Stopping development environment..."
        docker compose -f compose.dev.yaml down
        print_success "Development environment stopped"
        ;;
    restart)
        print_status "Restarting development environment..."
        docker compose -f compose.dev.yaml restart
        wait_for_services
        print_success "Development environment restarted"
        ;;
    setup)
        check_docker
        check_dev_running
        setup_laravel
        ;;
    test)
        check_docker
        check_dev_running
        run_tests
        ;;
    status)
        check_docker
        show_status
        ;;
    logs)
        check_docker
        check_dev_running
        show_logs "$2"
        ;;
    shell)
        check_docker
        check_dev_running
        print_status "Accessing workspace shell..."
        docker compose -f compose.dev.yaml exec workspace bash
        ;;
    backup)
        check_docker
        check_dev_running
        backup_database
        ;;
    restore)
        check_docker
        check_dev_running
        restore_database "$2"
        ;;
    cleanup)
        check_docker
        cleanup
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

#!/bin/bash

# Docker Architecture Summary Script
# This script provides an overview of the Docker setup

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
MAGENTA='\033[0;35m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

# Function to print colored output
print_header() {
    echo -e "${CYAN}========================================${NC}"
    echo -e "${CYAN}$1${NC}"
    echo -e "${CYAN}========================================${NC}"
}

print_section() {
    echo -e "\n${BLUE}$1${NC}"
    echo -e "${BLUE}$(printf '%.0s-' {1..40})${NC}"
}

print_item() {
    echo -e "${GREEN}✓${NC} $1"
}

print_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

print_info() {
    echo -e "${MAGENTA}ℹ${NC} $1"
}

# Main summary function
show_summary() {
    clear
    print_header "Laravel 12 Docker Architecture Summary"

    print_section "🏗️ Architecture Overview"
    print_item "Multi-environment Docker setup (Development & Production)"
    print_item "PostgreSQL as primary database with managed DB strategy"
    print_item "Multi-stage Dockerfile for optimized builds"
    print_item "Professional Nginx configuration with security headers"
    print_item "Laravel Sail-like development experience"

    print_section "🔧 Development Environment"
    print_item "Services: app (PHP-FPM), nginx, postgres, workspace, redis, mailhog"
    print_item "Live code editing with volume mounts"
    print_item "Xdebug enabled for debugging"
    print_item "Node.js and npm for frontend development"
    print_item "PostgreSQL with test database and extensions"
    print_item "MailHog for email testing (http://localhost:8025)"
    print_item "Application available at http://localhost:8000"

    print_section "🚀 Production Environment"
    print_item "Services: app, nginx, queue worker, scheduler"
    print_item "Multi-stage build with optimized final image"
    print_item "External managed PostgreSQL (RDS, Cloud SQL, etc.)"
    print_item "Health checks and monitoring ready"
    print_item "Security hardened (non-root users, minimal attack surface)"
    print_item "Horizontal scaling ready"
    print_item "SSL/TLS and rate limiting configured"

    print_section "📁 File Structure Created"
    echo "docker/"
    echo "├── common/"
    echo "│   └── php-fpm.Dockerfile     # Multi-stage PHP-FPM container"
    echo "├── dev/"
    echo "│   ├── nginx.conf             # Development web server config"
    echo "│   ├── php.ini                # Development PHP settings"
    echo "│   ├── php-fpm.conf           # Development PHP-FPM pool"
    echo "│   ├── xdebug.ini             # Xdebug configuration"
    echo "│   ├── workspace.Dockerfile   # Development workspace"
    echo "│   ├── workspace-php.ini      # Workspace PHP settings"
    echo "│   └── postgres-init.sql      # PostgreSQL initialization"
    echo "├── prod/"
    echo "│   ├── nginx.conf             # Production web server config"
    echo "│   ├── php.ini                # Production PHP settings"
    echo "│   └── php-fpm.conf           # Production PHP-FPM pool"
    echo "└── README.md                  # Comprehensive Docker guide"
    echo ""
    echo "Root files:"
    echo "├── compose.dev.yaml           # Development Docker Compose"
    echo "├── compose.prod.yaml          # Production Docker Compose"
    echo "├── .dockerignore              # Build context optimization"
    echo "├── .env.dev                   # Development environment"
    echo "├── .env.prod                  # Production environment template"
    echo "├── Makefile                   # Automation commands"
    echo "└── scripts/"
    echo "    ├── docker-dev.sh          # Development helper script"
    echo "    ├── docker-prod.sh         # Production deployment script"
    echo "    └── docker-summary.sh      # This summary script"

    print_section "🎯 Key Features Implemented"
    print_item "SOLID architecture with repository pattern"
    print_item "Service layer separation"
    print_item "Laravel Passport integration"
    print_item "Vite for modern frontend builds"
    print_item "Comprehensive error handling and logging"
    print_item "Security best practices (headers, rate limiting, etc.)"
    print_item "Performance optimizations (OPcache, compression, etc.)"
    print_item "Development tools (Xdebug, MailHog, workspace)"

    print_section "🗄️ Database Strategy"
    print_item "Development: PostgreSQL container with full features"
    print_item "Production: External managed PostgreSQL (AWS RDS, etc.)"
    print_warning "Managed DB provides: automated backups, disaster recovery"
    print_warning "Managed DB provides: vertical scaling, security compliance"
    print_warning "Managed DB provides: maintenance, monitoring, high availability"

    print_section "🚀 Quick Start Commands"
    echo -e "${GREEN}Development:${NC}"
    echo "  make setup          # Complete initial setup"
    echo "  make dev            # Start development environment"
    echo "  make shell          # Access workspace shell"
    echo "  make artisan cmd=\"migrate\"  # Run Laravel commands"
    echo "  make test           # Run tests"
    echo "  make npm-dev        # Start Vite dev server"
    echo ""
    echo -e "${GREEN}Production:${NC}"
    echo "  cp .env.prod .env && nano .env  # Configure production"
    echo "  make prod-build     # Build and deploy production"
    echo "  ./scripts/docker-prod.sh status  # Check production status"
    echo "  ./scripts/docker-prod.sh scale queue 3  # Scale services"

    print_section "📚 Documentation"
    print_item "Complete Docker guide: ./docker/README.md"
    print_item "DevOps guide with Docker architecture: ./docs/general/devops.md"
    print_item "Main project README updated with Docker info"
    print_item "Makefile with 30+ automation commands"
    print_item "Helper scripts for development and production"

    print_section "🔒 Security & Compliance"
    print_item "Non-root container execution"
    print_item "Secrets management via environment variables"
    print_item "Security headers and rate limiting"
    print_item "Minimal production images (no dev tools)"
    print_item "Network isolation and proper firewall rules"
    print_item "SSL/TLS ready configuration"

    print_section "📊 Monitoring & Observability"
    print_item "Health check endpoints (/health, /ping, /status)"
    print_item "Structured JSON logging for production"
    print_item "Container resource monitoring"
    print_item "Application performance monitoring ready"
    print_item "Log aggregation and alerting ready"

    print_section "⚡ Performance Optimizations"
    print_item "Multi-stage builds for minimal image size"
    print_item "OPcache enabled in production"
    print_item "Gzip compression and HTTP/2 ready"
    print_item "Asset compilation and optimization"
    print_item "Database connection pooling ready"
    print_item "Redis caching for sessions and application cache"

    print_section "🎉 What's Next?"
    print_info "1. Run 'make setup' to start development"
    print_info "2. Configure .env.prod for your production environment"
    print_info "3. Set up your managed PostgreSQL database"
    print_info "4. Configure your CI/CD pipeline for automated deployments"
    print_info "5. Set up monitoring and alerting for production"

    echo ""
    print_header "Docker Architecture Setup Complete! 🎉"
    echo ""
}

# Show help
show_help() {
    echo "Docker Architecture Summary"
    echo ""
    echo "Usage: $0 [command]"
    echo ""
    echo "Commands:"
    echo "  summary    Show complete architecture summary (default)"
    echo "  files      List all created files"
    echo "  commands   Show available make commands"
    echo "  help       Show this help message"
}

# Show created files
show_files() {
    print_header "Created Docker Files"

    echo "Docker Configuration Files:"
    find docker/ -type f | sort

    echo ""
    echo "Compose Files:"
    ls -la compose*.yaml

    echo ""
    echo "Environment Files:"
    ls -la .env* .dockerignore

    echo ""
    echo "Scripts:"
    ls -la scripts/docker-*.sh Makefile

    echo ""
    echo "Documentation:"
    ls -la docs/general/devops.md docker/README.md
}

# Show available commands
show_commands() {
    print_header "Available Make Commands"

    if [ -f "Makefile" ]; then
        make help
    else
        echo "Makefile not found in current directory"
    fi
}

# Main script logic
case "${1:-summary}" in
    summary)
        show_summary
        ;;
    files)
        show_files
        ;;
    commands)
        show_commands
        ;;
    help|--help|-h)
        show_help
        ;;
    *)
        echo "Unknown command: $1"
        show_help
        exit 1
        ;;
esac

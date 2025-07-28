#!/bin/bash

# Docker Troubleshooting Script for Laravel Project
# This script helps diagnose and fix common Docker build issues

set -e

echo "🔧 Docker Troubleshooting Script"
echo "================================"

# Function to check Docker status
check_docker() {
    echo "📋 Checking Docker status..."
    if ! docker --version > /dev/null 2>&1; then
        echo "❌ Docker is not installed or not running"
        exit 1
    fi
    echo "✅ Docker is running"
}

# Function to clean up Docker resources
cleanup_docker() {
    echo "🧹 Cleaning up Docker resources..."

    # Stop and remove containers
    echo "Stopping containers..."
    docker-compose -f compose.dev.yaml down --remove-orphans 2>/dev/null || true

    # Remove dangling images
    echo "Removing dangling images..."
    docker image prune -f

    # Remove build cache
    echo "Removing build cache..."
    docker builder prune -f

    # Remove unused volumes (be careful with this)
    read -p "Do you want to remove unused volumes? This will delete database data (y/N): " -n 1 -r
    echo
    if [[ $REPLY =~ ^[Yy]$ ]]; then
        docker volume prune -f
    fi

    echo "✅ Docker cleanup completed"
}

# Function to rebuild images
rebuild_images() {
    echo "🔨 Rebuilding Docker images..."

    # Build with no cache to ensure fresh build
    echo "Building app image..."
    docker-compose -f compose.dev.yaml build --no-cache app

    echo "Building workspace image..."
    docker-compose -f compose.dev.yaml build --no-cache workspace

    echo "✅ Images rebuilt successfully"
}

# Function to check system resources
check_resources() {
    echo "📊 Checking system resources..."

    # Check available disk space
    echo "Disk space:"
    df -h | grep -E '(Filesystem|/dev/)'

    # Check available memory
    echo "\nMemory usage:"
    if command -v free > /dev/null; then
        free -h
    else
        # macOS alternative
        echo "$(vm_stat | grep 'Pages free' | awk '{print $3}' | sed 's/\.//')" "pages free"
    fi

    # Check Docker resource limits
    echo "\nDocker system info:"
    docker system df
}

# Function to show build logs
show_build_logs() {
    echo "📝 Recent Docker build logs:"
    echo "You can check detailed build logs with:"
    echo "  docker-compose -f compose.dev.yaml build --progress=plain app"
    echo "  docker-compose -f compose.dev.yaml build --progress=plain workspace"
}

# Function to show common solutions
show_solutions() {
    echo "💡 Common Solutions:"
    echo "==================="
    echo "1. Build timeout issues:"
    echo "   - Increase Docker build timeout: export DOCKER_BUILDKIT_TIMEOUT=600"
    echo "   - Use BuildKit for better caching: export DOCKER_BUILDKIT=1"
    echo ""
    echo "2. Memory issues:"
    echo "   - Increase Docker memory limit in Docker Desktop settings"
    echo "   - Close other applications to free up memory"
    echo ""
    echo "3. Network issues:"
    echo "   - Check internet connection for package downloads"
    echo "   - Try building during off-peak hours"
    echo ""
    echo "4. PHP extension compilation issues:"
    echo "   - The Dockerfiles have been optimized to build extensions separately"
    echo "   - Try building individual services: docker-compose build app"
    echo ""
    echo "5. Cache issues:"
    echo "   - Clear Docker build cache: docker builder prune -a"
    echo "   - Use --no-cache flag: docker-compose build --no-cache"
}

# Main menu
show_menu() {
    echo ""
    echo "Select an option:"
    echo "1) Check Docker status"
    echo "2) Clean up Docker resources"
    echo "3) Rebuild images (no cache)"
    echo "4) Check system resources"
    echo "5) Show build logs info"
    echo "6) Show common solutions"
    echo "7) Full troubleshoot (all steps)"
    echo "8) Exit"
    echo ""
    read -p "Enter your choice (1-8): " choice
}

# Full troubleshoot function
full_troubleshoot() {
    echo "🔍 Running full troubleshoot..."
    check_docker
    check_resources
    cleanup_docker
    echo ""
    echo "Now try running: make setup"
    echo "If issues persist, try: docker-compose -f compose.dev.yaml build --no-cache"
}

# Main script logic
if [ "$1" = "--auto" ]; then
    full_troubleshoot
    exit 0
fi

while true; do
    show_menu
    case $choice in
        1) check_docker ;;
        2) cleanup_docker ;;
        3) rebuild_images ;;
        4) check_resources ;;
        5) show_build_logs ;;
        6) show_solutions ;;
        7) full_troubleshoot ;;
        8) echo "👋 Goodbye!"; exit 0 ;;
        *) echo "❌ Invalid option. Please try again." ;;
    esac
    echo ""
    read -p "Press Enter to continue..."
done

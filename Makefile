.PHONY: help dev prod down clean build logs shell artisan npm test migrate seed passport-install

# Default target
help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

# Development Environment
dev: ## Start development environment
	@echo "Starting development environment..."
	@cp .env.dev .env 2>/dev/null || true
	docker compose -f compose.dev.yaml up -d
	@echo "Development environment started at http://localhost:8000"
	@echo "MailHog available at http://localhost:8025"

dev-build: ## Build and start development environment
	@echo "Building and starting development environment..."
	@cp .env.dev .env 2>/dev/null || true
	docker compose -f compose.dev.yaml up -d --build

dev-logs: ## Show development logs
	docker compose -f compose.dev.yaml logs -f

# Production Environment
prod: ## Start production environment
	@echo "Starting production environment..."
	@if [ ! -f .env ]; then echo "Error: .env file not found. Copy .env.prod and configure it."; exit 1; fi
	docker compose -f compose.prod.yaml up -d
	@echo "Production environment started"

prod-build: ## Build and start production environment
	@echo "Building and starting production environment..."
	@if [ ! -f .env ]; then echo "Error: .env file not found. Copy .env.prod and configure it."; exit 1; fi
	docker compose -f compose.prod.yaml up -d --build

prod-logs: ## Show production logs
	docker compose -f compose.prod.yaml logs -f

# General Commands
down: ## Stop all containers
	docker compose -f compose.dev.yaml down 2>/dev/null || true
	docker compose -f compose.prod.yaml down 2>/dev/null || true

clean: ## Stop containers and remove volumes
	docker compose -f compose.dev.yaml down -v 2>/dev/null || true
	docker compose -f compose.prod.yaml down -v 2>/dev/null || true
	docker system prune -f

build: ## Build all images
	docker compose -f compose.dev.yaml build
	docker compose -f compose.prod.yaml build

# Container Access
shell: ## Access workspace container shell
	docker compose -f compose.dev.yaml exec workspace bash

app-shell: ## Access app container shell
	docker compose -f compose.dev.yaml exec app sh

nginx-shell: ## Access nginx container shell
	docker compose -f compose.dev.yaml exec nginx sh

# Laravel Commands
artisan: ## Run artisan command (usage: make artisan cmd="migrate")
	@if [ -z "$(cmd)" ]; then \
		echo "Usage: make artisan cmd=\"your-command\""; \
		echo "Example: make artisan cmd=\"migrate\""; \
	else \
		docker compose -f compose.dev.yaml exec workspace php artisan $(cmd); \
	fi

migrate: ## Run database migrations
	docker compose -f compose.dev.yaml exec workspace php artisan migrate

migrate-fresh: ## Fresh migration with seeding
	docker compose -f compose.dev.yaml exec workspace php artisan migrate:fresh --seed

seed: ## Run database seeders
	docker compose -f compose.dev.yaml exec workspace php artisan db:seed

test: ## Run tests
	docker compose -f compose.dev.yaml exec workspace php artisan test

test-coverage: ## Run tests with coverage
	docker compose -f compose.dev.yaml exec workspace php artisan test --coverage

# Frontend Commands
npm: ## Run npm command (usage: make npm cmd="install")
	@if [ -z "$(cmd)" ]; then \
		echo "Usage: make npm cmd=\"your-command\""; \
		echo "Example: make npm cmd=\"install\""; \
	else \
		docker compose -f compose.dev.yaml exec workspace npm $(cmd); \
	fi

npm-install: ## Install npm dependencies
	docker compose -f compose.dev.yaml exec workspace sh -c "sudo chown -R sail:sail /home/sail/.npm 2>/dev/null || true && npm install"

npm-dev: ## Run Vite development server
	docker compose -f compose.dev.yaml exec workspace npm run dev

npm-build: ## Build assets for production
	docker compose -f compose.dev.yaml exec workspace npm run build

# Composer Commands
composer: ## Run composer command (usage: make composer cmd="install")
	@if [ -z "$(cmd)" ]; then \
		echo "Usage: make composer cmd=\"your-command\""; \
		echo "Example: make composer cmd=\"install\""; \
	else \
		docker compose -f compose.dev.yaml exec workspace composer $(cmd); \
	fi

composer-install: ## Install composer dependencies
	docker compose -f compose.dev.yaml exec workspace composer install

composer-update: ## Update composer dependencies
	docker compose -f compose.dev.yaml exec workspace composer update

# Laravel Passport
passport-setup: ## Setup Laravel Passport
	docker compose -f compose.dev.yaml exec workspace ./scripts/setup-passport.sh

passport-install: ## Install Laravel Passport
	docker compose -f compose.dev.yaml exec workspace php artisan passport:install

passport-keys: ## Generate Passport keys
	docker compose -f compose.dev.yaml exec workspace php artisan passport:keys

passport-client: ## Create Passport client
	docker compose -f compose.dev.yaml exec workspace php artisan passport:client

# Database Commands
db-shell: ## Access PostgreSQL shell
	docker compose -f compose.dev.yaml exec postgres psql -U laravel -d laravel_dev

db-dump: ## Create database dump
	docker compose -f compose.dev.yaml exec postgres pg_dump -U laravel laravel_dev > dump.sql

db-restore: ## Restore database from dump (usage: make db-restore file="dump.sql")
	@if [ -z "$(file)" ]; then \
		echo "Usage: make db-restore file=\"dump.sql\""; \
	else \
		docker compose -f compose.dev.yaml exec -T postgres psql -U laravel -d laravel_dev < $(file); \
	fi

# Setup Commands
setup: ## Initial setup for development
	@echo "Setting up Laravel development environment..."
	@cp .env.dev .env 2>/dev/null || true
	make dev-build
	@echo "Waiting for services to start..."
	sleep 10
	make composer-install
	make npm-install
	make artisan cmd="key:generate"
	make migrate
	docker compose -f compose.dev.yaml exec workspace ./scripts/setup-passport.sh
	@echo "Setting up pre-commit hooks..."
	make setup-pre-commit
	@echo "Setup complete! Visit http://localhost:8000"
	@echo "Pre-commit hooks are now active for code quality checks."

setup-prod: ## Initial setup for production
	@echo "Setting up Laravel production environment..."
	@if [ ! -f .env ]; then echo "Error: .env file not found. Copy .env.prod and configure it."; exit 1; fi
	make prod-build
	@echo "Waiting for services to start..."
	sleep 15
	make artisan-prod cmd="migrate --force"
	docker compose -f compose.prod.yaml exec app ./scripts/setup-passport.sh
	make artisan-prod cmd="config:cache"
	make artisan-prod cmd="route:cache"
	make artisan-prod cmd="view:cache"
	@echo "Production setup complete!"

# Production artisan commands
artisan-prod: ## Run artisan command in production (usage: make artisan-prod cmd="migrate")
	@if [ -z "$(cmd)" ]; then \
		echo "Usage: make artisan-prod cmd=\"your-command\""; \
	else \
		docker compose -f compose.prod.yaml exec app php artisan $(cmd); \
	fi

# Code Quality Commands
code-quality: ## Run code quality script (usage: make code-quality cmd="full-check")
	@if [ -z "$(cmd)" ]; then \
		echo "Usage: make code-quality cmd=\"command\""; \
		echo "Available commands: full-check, style, test, phpstan, security"; \
	else \
		./scripts/code-quality.sh $(cmd); \
	fi

phpstan: ## Run PHPStan static analysis
	./scripts/code-quality.sh phpstan

code-style: ## Fix code style with Laravel Pint
	./scripts/code-quality.sh style

test-coverage: ## Run tests with coverage
	./scripts/code-quality.sh test

security-audit: ## Run security audit
	./scripts/code-quality.sh security

full-quality-check: ## Run complete code quality analysis
	./scripts/code-quality.sh full-check

ide-helpers: ## Generate IDE helper files
	./scripts/code-quality.sh ide-helpers

# Pre-commit Commands
setup-pre-commit: ## Setup pre-commit hooks
	./scripts/setup-pre-commit.sh setup

install-pre-commit: ## Install pre-commit hooks
	./scripts/setup-pre-commit.sh install

test-pre-commit: ## Test pre-commit hooks
	./scripts/setup-pre-commit.sh test

update-pre-commit: ## Update pre-commit hooks
	./scripts/setup-pre-commit.sh update

pre-commit-run: ## Run pre-commit on all files
	pre-commit run --all-files

pre-commit-run-staged: ## Run pre-commit on staged files
	pre-commit run

# Monitoring
status: ## Show container status
	@echo "Development containers:"
	docker compose -f compose.dev.yaml ps 2>/dev/null || echo "Development environment not running"
	@echo "\nProduction containers:"
	docker compose -f compose.prod.yaml ps 2>/dev/null || echo "Production environment not running"

logs: ## Show logs for current environment
	@if docker compose -f compose.dev.yaml ps -q > /dev/null 2>&1; then \
		make dev-logs; \
	elif docker compose -f compose.prod.yaml ps -q > /dev/null 2>&1; then \
		make prod-logs; \
	else \
		echo "No containers running"; \
	fi

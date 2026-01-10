.PHONY: help build up down restart logs shell migrate seed clean

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}'

build: ## Build Docker images
	docker-compose build

up: ## Start all containers
	docker-compose up -d

down: ## Stop all containers
	docker-compose down

restart: ## Restart all containers
	docker-compose restart

logs: ## Show container logs
	docker-compose logs -f

shell: ## Access app container shell
	docker-compose exec app sh

migrate: ## Run database migrations
	docker-compose exec app php artisan migrate

migrate-fresh: ## Fresh migration (⚠️ drops all tables)
	docker-compose exec app php artisan migrate:fresh

seed: ## Run database seeders
	docker-compose exec app php artisan db:seed

setup: ## Initial setup (migrate + seed)
	docker-compose exec app php artisan migrate --force
	docker-compose exec app php artisan db:seed --force

cache-clear: ## Clear all caches
	docker-compose exec app php artisan optimize:clear

cache: ## Cache configuration (production)
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

key: ## Generate application key
	docker-compose exec app php artisan key:generate

storage-link: ## Create storage symlink
	docker-compose exec app php artisan storage:link

mysql: ## Access MySQL shell
	docker-compose exec mysql mysql -u radiix_user -p radiix_tracker

clean: ## Remove containers, volumes, and images
	docker-compose down -v
	docker-compose rm -f

rebuild: clean build up ## Clean, rebuild, and start containers

start: build up setup ## Build, start, and setup application

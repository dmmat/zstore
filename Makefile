#!/usr/bin/make

help: ## Show this help
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

up:  ## Start all containers (in background) for development
	@docker-compose up --no-recreate -d

init-db: ## INIT DATABASE
	@docker-compose exec -T mysql mysql -u root -proot zstore < ./db/db.sql
	@docker-compose exec -T mysql mysql -u root -proot zstore < ./db/initdata.sql

init-composer: ## composer install
	@docker-compose exec php-fpm composer install
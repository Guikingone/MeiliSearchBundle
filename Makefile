DOCKER 		   = @docker
DOCKER_COMPOSE = @docker compose
PHP            = $(DOCKER_COMPOSE) run --rm php

.DEFAULT_GOAL := help

help:
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

##
## Project
##---------------------------------------------------------------------------

.PHONY: boot up shell down vendor

boot: ## Launch the project
boot: up vendor

up: ## Up the containers
up: docker-compose.yml
	$(DOCKER_COMPOSE) up -d --build --remove-orphans

down: ## Down the containers
down: docker-compose.yml
	$(DOCKER_COMPOSE) down

shell: ## Get in container shell
shell: docker-compose.yml
	$(PHP) /bin/bash

vendor: ## Install the dependencies
vendor: composer.json
	$(PHP) composer install

##
## Tools
##---------------------------------------------------------------------------

.PHONY: php-cs-fixer php-cs-fixer-dry phpstan phpstan-baseline rector-dry rector

phpcs: ## Run PHP-CS-FIXER and fix the errors
phpcs:
	$(PHP) vendor/bin/php-cs-fixer -v fix

phpcs-dry: ## Run PHP-CS-FIXER in --dry-run mode
phpcs-dry:
	$(PHP) vendor/bin/php-cs-fixer -v --dry-run --diff fix

phpstan: ## Run PHPStan (the configuration must be defined in phpstan.neon)
phpstan: phpstan.neon.dist
	$(PHP) vendor/bin/phpstan analyse --memory-limit=-1

phpstan-baseline: ## Run PHPStan generate baseline
phpstan-baseline: phpstan.neon.dist
	$(PHP) vendor/bin/phpstan analyse --generate-baseline --allow-empty-baseline --memory-limit=-1

rector: ## Run Rector
rector: rector.php
	$(PHP) vendor/bin/rector process

rector-dry: ## Run Rector in --dry-run mode
rector-dry: rector.php
	$(PHP) vendor/bin/rector process --dry-run

##
## Tests
##---------------------------------------------------------------------------

.PHONY: tests infection

tests: ## Launch the PHPUnit tests
tests: phpunit.xml.dist
	$(PHP) vendor/bin/phpunit tests

infection: ## Launch Infection
infection: infection.json5.dist
	$(PHP) vendor/bin/infection --threads=max

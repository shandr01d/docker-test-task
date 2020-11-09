DOCKER_COMPOSE = docker-compose
RUN_PHP = $(DOCKER_COMPOSE) run --rm --no-deps php-fpm
RUN_COMPOSER = $(RUN_PHP) composer
EXECUTE_APP ?= $(DOCKER_COMPOSE) exec php-fpm

all: setup up
.PHONY: all

#
# Setup
#
setup: composer-install
.PHONY: setup

composer-install:
	$(RUN_COMPOSER) install
.PHONY: composer-install

ssh:
	$(EXECUTE_APP) bash
.PHONY: ssh

#
# Docker Compose
#
ps:
	$(DOCKER_COMPOSE) ps
.PHONY: ps

restart:
	$(DOCKER_COMPOSE) restart
.PHONY: restart

logs:
	$(DOCKER_COMPOSE) logs -f
.PHONY: logs

up:
	$(DOCKER_COMPOSE) up --remove-orphans -d
.PHONY: up

down:
	$(DOCKER_COMPOSE) down --remove-orphans
.PHONY: down

#
# Doctrine
#
db-migrate:
	$(RUN_PHP) bin/console doctrine:migrations:migrate
.PHONY: db-migrate

db-diff:
	$(RUN_PHP) bin/console doctrine:migrations:diff
.PHONY: db-diff

db-status:
	$(RUN_PHP) bin/console doctrine:migrations:status
.PHONY: db-diff

db-make-entity:
	$(RUN_PHP) bin/console make:entity
.PHONY: db-make-entity

#
# Other
#

cc:
	$(RUN_PHP) bin/console cache:clear
.PHONY: cc

#
# Tests
#
test:
#	$(RUN_PHP) vendor/bin/codecept run --steps
	$(RUN_PHP) vendor/bin/codecept run unit
#	$(RUN_PHP) vendor/bin/codecept run functional
	$(RUN_PHP) vendor/bin/codecept run api
#	$(RUN_PHP) vendor/bin/codecept run acceptance
.PHONY: test

test-unit:
	$(RUN_PHP) vendor/bin/codecept run unit
.PHONY: test-unit

test-acceptance:
	$(RUN_PHP) vendor/bin/codecept run acceptance
.PHONY: test-acceptance

test-api:
	$(RUN_PHP) vendor/bin/codecept run api
.PHONY: test-api

test-functional:
	$(RUN_PHP) vendor/bin/codecept run functional
.PHONY: test-functional
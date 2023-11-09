SHELL=/bin/bash

.DEFAULT_GOAL := help

PHP_RUN := docker compose run --rm php-cli
ARGS ?= $(shell read -p "Additional arguments ([enter] for none): " args; echo $$args)

export HOST_UID := $(shell id -u)
export HOST_GID := $(shell id -g)

##
##--------------
## Building
##--------------

## build:			Build the docker containers
.PHONY: build
build:
	docker compose build --pull

## setup:			Sets up the project for you
.PHONY: setup
setup:
	${PHP_RUN} composer install --ansi

##
##--------------
## Debugging
##--------------

## bash:			Go into the container for running things manually
.PHONY: bash
bash:
	${PHP_RUN} bash

## php:			PHP
.PHONY: php
php:
	${PHP_RUN} $(ARGS)

## composer:		Runs composer through docker
.PHONY: composer
composer:
	${PHP_RUN} composer $(ARGS) --ansi

##
##--------------
## Git
##--------------

## branch:		Checkout main, get most recent version, create new branch based on main
.PHONY: branch
branch:
	git checkout main
	git pull
	read -p "Enter branch name: " branch_name; \
    git checkout -b $$branch_name

## clear-local-branches:	Removes all local branches
.PHONY: clear-local-branches
clear-local-branches:
	git for-each-ref --format '%(refname:short)' refs/heads | grep -v main | xargs git branch -D

##
##--------------
## Tests
##--------------

## build-codeception:	Builds stuff needed for Codeception
.PHONY: build-codeception
build-codeception:
	${PHP_RUN} php vendor/bin/codecept bootstrap
	${PHP_RUN} php vendor/bin/codecept build
	${PHP_RUN} composer dump-autoload

## clear-codeception:	Resets stuff needed for Codeception
.PHONY: clear-codeception
clear-codeception:
	${PHP_RUN} php vendor/bin/codecept build
	${PHP_RUN} composer dump-autoload

## tests:			Runs tests
.PHONY: test tests
test: tests
tests: unit

## failed-tests:		Runs failed tests
.PHONY: failed-tests
failed-tests:
	${PHP_RUN} php vendor/bin/codecept run -g failed

## clear-failed:		Removes the failed tests memory because if a failed test was (re)moved it would get stuck on trying to access it first.
.PHONY: clear-failed
clear-failed:
	rm -f tests/_output/failed

## unit:			Runs unit tests
.PHONY: unit
unit: clear-failed
	${PHP_RUN} php vendor/bin/codecept run Unit

##
##--------------
## Extra
##--------------

## help:			Print this message
.PHONY: help
help: Makefile
	sed -n 's/^##//p' $<

## tab-makefile:		Tabs the makefile descriptions
.PHONY: tab-makefile
tab-makefile:
	sed -i 's/:	/:		/g' Makefile

# End it with a blank line
##

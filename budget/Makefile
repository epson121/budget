SHELL := /bin/bash

tests:
	bin/console doctrine:database:drop --force --env=test || true
	bin/console doctrine:database:create --env=test
	bin/console doctrine:migrations:migrate -n --env=test
	bin/console doctrine:fixtures:load -n --env=test
	bin/phpunit $@

.PHONY: tests
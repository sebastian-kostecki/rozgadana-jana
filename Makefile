.PHONY: setup up down logs shell wp reset config

setup:
	./scripts/setup.sh

up: setup
	docker compose up -d

down:
	docker compose down

logs:
	docker compose logs -f wordpress

shell:
	docker compose exec wordpress bash

wp:
	docker compose run --rm wpcli wp $(ARGS)

reset:
	docker compose down -v
	@echo "Local database volume removed. Run 'make up' to start fresh."

config:
	docker compose config

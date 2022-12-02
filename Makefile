help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

bash: ## [host] Ouvre un bash dans le conteneur web (en tant que root)
	docker compose exec web bash

permissions-dev: ## [host] Configure les permissions de dev
	sudo setfacl -R  -m u:$(USER):rwX ./
	sudo setfacl -dR -m u:$(USER):rwX ./

install: ## [host] Installe les dépendances
	docker compose exec web composer install
	docker compose exec web yarn install
	docker compose exec web yarn encore prod

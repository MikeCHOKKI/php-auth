# Makefile — php-auth
# Commandes standardisées pour le développement PHP

.PHONY: dev test lint audit clean install help docker-up docker-down docker-build db-migrate db-seed keys generate-test-keys

## Aide
help: ## Afficher l'aide
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "  \033[36m%-20s\033[0m %s\n", $$1, $$2}'

## Développement
dev: docker-up ## Démarrer l'environnement de développement
	@echo "Application disponible sur http://localhost:$(shell grep APP_PORT .env 2>/dev/null | cut -d= -f2 || echo 8080)"

install: ## Installer les dépendances Composer
	composer install

update: ## Mettre à jour les dépendances Composer
	composer update

## Build
build: ## Build Docker
	docker compose build

## Tests
test: ## Lancer tous les tests PHPUnit
	./vendor/bin/phpunit

test-unit: ## Tests unitaires uniquement
	./vendor/bin/phpunit --testsuite Unit

test-integration: ## Tests d'intégration uniquement
	./vendor/bin/phpunit --testsuite Integration

test-functional: ## Tests fonctionnels uniquement
	./vendor/bin/phpunit --testsuite Functional

coverage: ## Rapport de couverture
	./vendor/bin/phpunit --coverage-html coverage

## Qualité
lint: ## Vérification PSR-12
	./vendor/bin/phpcs --standard=PSR12 src tests

lint-fix: ## Correction automatique PSR-12
	./vendor/bin/phpcbf --standard=PSR12 src tests

static-analysis: ## Analyse statique avec PHPStan
	./vendor/bin/phpstan analyse src tests --level=8

audit: ## Audit de sécurité des dépendances
	composer audit

## Docker
docker-up: ## Démarrer Docker (dev)
	docker compose up -d

docker-down: ## Arrêter Docker
	docker compose down

docker-logs: ## Afficher les logs
	docker compose logs -f

docker-clean: ## Nettoyer les conteneurs et volumes
	docker compose down -v
	docker system prune -f

## Base de données
db-migrate: ## Lancer les migrations
	docker compose exec postgres psql -U postgres -d php_auth -f /docker-entrypoint-initdb.d/init.sql

db-seed: ## Insérer les données de test
	docker compose exec app php scripts/seed.php

db-reset: ## Réinitialiser la base de données
	docker compose down -v
	docker compose up -d postgres

db-shell: ## Shell PostgreSQL
	docker compose exec postgres psql -U postgres -d php_auth

## Sécurité — Clés JWT
keys: ## Générer les clés RSA pour JWT
	mkdir -p keys
	openssl genrsa -out keys/private.pem 2048
	openssl rsa -in keys/private.pem -pubout -out keys/public.pem
	chmod 600 keys/private.pem
	chmod 644 keys/public.pem
	@echo "Clés JWT générées dans keys/"

generate-test-keys: ## Générer clés de test pour CI
	mkdir -p keys
	openssl genrsa -out keys/private.pem 2048
	openssl rsa -in keys/private.pem -pubout -out keys/public.pem

## Nettoyage
clean: ## Nettoyer les artefacts
	rm -rf vendor/
	rm -rf coverage/
	rm -rf .phpunit.result.cache
	rm -rf keys/*.pem

## CI
ci: lint audit test ## Pipeline CI complète

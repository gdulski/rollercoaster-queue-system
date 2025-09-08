# System Kolejek Górskich - Makefile
# Ułatwienia do zarządzania projektem

.PHONY: help start stop restart clean build rebuild logs status test test-unit test-coverage test-watch health redis coaster-status coaster-status-json coaster-status-refresh coaster-monitor coaster-monitor-fast coaster-monitor-clear

# Domyślna komenda
help: ## Wyświetl dostępne komendy
	@echo "🎢 System Kolejek Górskich - Dostępne komendy:"
	@echo "=============================================="
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

start: ## Uruchom całe środowisko (jedna komenda)
	@echo "🚀 Uruchamianie Systemu Kolejek Górskich..."
	@echo "🛑 Zatrzymywanie istniejących kontenerów..."
	@docker-compose down 2>/dev/null || true
	@echo "🔨 Budowanie i uruchamianie kontenerów..."
	@docker-compose up -d --build
	@echo "⏳ Oczekiwanie na uruchomienie kontenerów..."
	@sleep 10
	@echo "📊 Status kontenerów:"
	@docker-compose ps
	@echo ""
	@echo "🎉 System został uruchomiony pomyślnie!"
	@echo "🌐 Aplikacja dostępna pod adresem: http://localhost:8080"
	@echo "🧪 Test: curl http://localhost:8080/api/health"

stop: ## Zatrzymaj wszystkie kontenery
	@echo "🛑 Zatrzymywanie kontenerów..."
	@docker-compose down

restart: ## Restart kontenerów
	@echo "🔄 Restart kontenerów..."
	@docker-compose restart

clean: ## Usuń wszystkie kontenery, obrazy i wolumeny
	@echo "🧹 Czyszczenie środowiska..."
	@docker-compose down -v --rmi all
	@docker system prune -f

build: ## Zbuduj obrazy bez uruchamiania
	@echo "🔨 Budowanie obrazów..."
	@docker-compose build

rebuild: ## Przebuduj obrazy od nowa
	@echo "🔄 Przebudowywanie obrazów od nowa..."
	@docker-compose build --no-cache

logs: ## Wyświetl logi wszystkich kontenerów
	@echo "📋 Logi kontenerów:"
	@docker-compose logs -f

logs-nginx: ## Wyświetl logi nginx
	@docker-compose logs -f nginx

logs-php: ## Wyświetl logi PHP
	@docker-compose logs -f php

logs-redis: ## Wyświetl logi Redis
	@docker-compose logs -f redis

status: ## Sprawdź status kontenerów
	@echo "📊 Status kontenerów:"
	@docker-compose ps

test: ## Uruchom testy API (health check)
	@echo "🧪 Testowanie API..."
	@echo "Health Check:"
	@curl -s http://localhost:8080/api/health | jq . 2>/dev/null || curl -s http://localhost:8080/api/health
	@echo ""
	@echo "Redis Test:"
	@curl -s http://localhost:8080/api/health/redis | jq . 2>/dev/null || curl -s http://localhost:8080/api/health/redis
	@echo ""
	@echo "🎉 Testy zakończone!"

test-unit: ## Uruchom testy jednostkowe PHPUnit
	@echo "🧪 Uruchamianie testów jednostkowych..."
	@docker-compose exec php ./vendor/bin/phpunit --colors=always
	@echo "🎉 Testy jednostkowe zakończone!"

test-coverage: ## Uruchom testy z pokryciem kodu
	@echo "📊 Uruchamianie testów z pokryciem kodu..."
	@docker-compose exec php ./vendor/bin/phpunit --coverage-html=build/logs/html --coverage-text --colors=always
	@echo "📈 Raport pokrycia dostępny w: build/logs/html/index.html"

test-watch: ## Uruchom testy w trybie watch (automatyczne powtarzanie)
	@echo "👀 Uruchamianie testów w trybie watch..."
	@docker-compose exec php ./vendor/bin/phpunit --watch --colors=always

health: ## Sprawdź health check
	@echo "🏥 Health Check:"
	@curl -s http://localhost:8080/api/health | jq . 2>/dev/null || curl -s http://localhost:8080/api/health

redis: ## Sprawdź połączenie z Redis
	@echo "🔴 Redis Test:"
	@curl -s http://localhost:8080/api/health/redis | jq . 2>/dev/null || curl -s http://localhost:8080/api/health/redis

shell-php: ## Otwórz shell w kontenerze PHP
	@echo "🐚 Otwieranie shell w kontenerze PHP..."
	@docker-compose exec php bash

shell-nginx: ## Otwórz shell w kontenerze nginx
	@echo "🐚 Otwieranie shell w kontenerze nginx..."
	@docker-compose exec nginx sh

shell-redis: ## Otwórz Redis CLI
	@echo "🔴 Otwieranie Redis CLI..."
	@docker-compose exec redis redis-cli

coaster-status: ## Wyświetl status kolejek górskich
	@echo "🎢 Status kolejek górskich:"
	@docker-compose exec php php spark coaster:status

coaster-status-json: ## Wyświetl status kolejek w formacie JSON
	@echo "🎢 Status kolejek górskich (JSON):"
	@docker-compose exec php php spark coaster:status --json

coaster-status-refresh: ## Odśwież i wyświetl status kolejek
	@echo "🔄 Odświeżanie statusu kolejek górskich:"
	@docker-compose exec php php spark coaster:status --refresh

coaster-monitor: ## Monitor kolejek w czasie rzeczywistym
	@echo "🎢 Uruchamianie monitora w czasie rzeczywistym:"
	@docker-compose exec php php spark coaster:monitor

coaster-monitor-fast: ## Monitor z szybkim odświeżaniem (2s)
	@echo "⚡ Uruchamianie szybkiego monitora:"
	@docker-compose exec php php spark coaster:monitor --interval=2

coaster-monitor-clear: ## Monitor z czyszczeniem ekranu
	@echo "🧹 Uruchamianie monitora z czyszczeniem ekranu:"
	@docker-compose exec php php spark coaster:monitor --clear

install: ## Zainstaluj zależności PHP
	@echo "📦 Instalowanie zależności PHP..."
	@docker-compose exec php composer install

update: ## Aktualizuj zależności PHP
	@echo "🔄 Aktualizowanie zależności PHP..."
	@docker-compose exec php composer update

dev: ## Uruchom w trybie deweloperskim
	@echo "🛠️  Uruchamianie w trybie deweloperskim..."
	@docker-compose -f docker-compose.yml -f docker-compose.dev.yml up -d

prod: ## Uruchom w trybie produkcyjnym
	@echo "🏭 Uruchamianie w trybie produkcyjnym..."
	@docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d

# Szybkie komendy
up: start ## Alias dla start
down: stop ## Alias dla stop
ps: status ## Alias dla status


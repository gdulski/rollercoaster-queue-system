# System Kolejek Górskich - Makefile
# Ułatwienia do zarządzania projektem

.PHONY: help start stop restart clean build logs status test health redis

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

test: ## Uruchom testy API
	@echo "🧪 Testowanie API..."
	@echo "Health Check:"
	@curl -s http://localhost:8080/api/health | jq . 2>/dev/null || curl -s http://localhost:8080/api/health
	@echo ""
	@echo "Redis Test:"
	@curl -s http://localhost:8080/api/health/redis | jq . 2>/dev/null || curl -s http://localhost:8080/api/health/redis
	@echo ""
	@echo "🎉 Testy zakończone!"

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


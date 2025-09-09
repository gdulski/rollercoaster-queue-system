# System Kolejek Górskich

System zarządzania kolejkami górskimi zbudowany w PHP z użyciem CodeIgniter 4, nginx i Redis.

## Wymagania

- Docker
- Docker Compose
- Make (opcjonalnie, dla ułatwień)

## 🚀 Szybki Start (Jedna Komenda)

```bash
make start
```

**To wszystko!** Aplikacja będzie dostępna pod adresem: `http://localhost:8080`

## 📚 Dokumentacja API

Kompletna dokumentacja wszystkich endpointów API dostępna jest w pliku:
**[API_DOCUMENTATION.md](./API_DOCUMENTATION.md)**

Dokumentacja zawiera:
- Opis wszystkich endpointów
- Przykłady requestów i odpowiedzi
- Kody błędów i ich znaczenie
- Przykłady użycia z różnymi narzędziami
- Rozwiązywanie problemów

### 🚀 Szybki dostęp do endpointów

| Metoda | Endpoint | Opis |
|--------|----------|------|
| GET | `/api/health` | Sprawdzenie stanu aplikacji |
| GET | `/api/health/redis` | Sprawdzenie połączenia z Redis |
| GET | `/api/coasters` | Pobranie wszystkich kolejek |
| POST | `/api/coasters` | Utworzenie nowej kolejki |
| GET | `/api/coasters/{id}` | Pobranie konkretnej kolejki |
| PUT | `/api/coasters/{id}` | Aktualizacja kolejki |
| GET | `/api/coasters/{id}/wagons` | Pobranie wagonów kolejki |
| POST | `/api/coasters/{id}/wagons` | Utworzenie nowego wagonu |
| DELETE | `/api/coasters/{id}/wagons/{wagonId}` | Usunięcie wagonu |
| GET | `/api/statistics` | Ogólne statystyki systemu |
| GET | `/api/statistics/coaster/{id}` | Statystyki konkretnej kolejki |

## 🧪 Testowanie

```bash
# Uruchom testy API
make test
```

### Ręczne testy

Po uruchomieniu kontenerów, aplikacja będzie dostępna pod adresem: `http://localhost:8080`

#### 1. Health Check
```bash
curl http://localhost:8080/api/health
```

Odpowiedź:
```json
{
    "status": "OK",
    "message": "System kolejek górskich działa poprawnie",
    "timestamp": "2024-01-01 12:00:00",
    "version": "1.0.0",
    "environment": "development"
}
```

#### 2. Test połączenia z Redis
```bash
curl http://localhost:8080/api/health/redis
```

Odpowiedź:
```json
{
    "status": "OK",
    "message": "Połączenie z Redis działa poprawnie",
    "timestamp": "2024-01-01 12:00:00"
}
```

## 🛠️ Zarządzanie Projektem

### Dostępne komendy Makefile

#### 🚀 Podstawowe operacje
```bash
make help          # Wyświetl wszystkie dostępne komendy
make start         # Uruchom całe środowisko (jedna komenda)
make stop          # Zatrzymaj wszystkie kontenery
make restart       # Restart kontenerów
make clean         # Usuń wszystkie kontenery, obrazy i wolumeny
make status        # Sprawdź status kontenerów
```

#### 🔨 Budowanie i konfiguracja
```bash
make build         # Zbuduj obrazy bez uruchamiania
make rebuild       # Przebuduj obrazy od nowa
make install       # Zainstaluj zależności PHP
make update        # Aktualizuj zależności PHP
make dev           # Uruchom w trybie deweloperskim
make prod          # Uruchom w trybie produkcyjnym
```

#### 📋 Logi i monitoring
```bash
make logs          # Wyświetl logi wszystkich kontenerów
make logs-nginx    # Wyświetl logi nginx
make logs-php      # Wyświetl logi PHP
make logs-redis    # Wyświetl logi Redis
```

#### 🧪 Testowanie
```bash
make test          # Uruchom testy API (health check)
make test-unit     # Uruchom testy jednostkowe PHPUnit
make test-coverage # Uruchom testy z pokryciem kodu
make test-watch    # Uruchom testy w trybie watch
```

#### 🏥 Health Check
```bash
make health        # Sprawdź health check aplikacji
make redis         # Sprawdź połączenie z Redis
```

#### 🎢 Monitorowanie kolejek (CodeIgniter 4 CLI + Redis React)
```bash
make coaster-status        # Wyświetl status kolejek górskich
make coaster-status-json   # Wyświetl status w formacie JSON
make coaster-status-refresh # Odśwież i wyświetl status
make coaster-monitor       # Monitor w czasie rzeczywistym
make coaster-monitor-fast  # Monitor z szybkim odświeżaniem (2s)
make coaster-monitor-clear # Monitor z czyszczeniem ekranu
```

#### 🐚 Dostęp do kontenerów
```bash
make shell-php     # Otwórz shell w kontenerze PHP
make shell-nginx   # Otwórz shell w kontenerze nginx
make shell-redis   # Otwórz Redis CLI
```

#### ⚡ Szybkie aliasy
```bash
make up            # Alias dla start
make down          # Alias dla stop
make ps            # Alias dla status
```

### 📖 Szczegółowa pomoc
```bash
make help          # Wyświetl wszystkie komendy z opisami
```



## 📁 Struktura projektu

- `app/Controllers/Api/` - Kontrolery API
- `app/Config/` - Konfiguracja aplikacji
- `docker/` - Konfiguracja Docker
- `public/` - Pliki publiczne (punkt wejścia)

## 🐳 Kontenery

- **nginx** - Serwer web (port 8080)
- **php** - PHP-FPM z CodeIgniter 4
- **redis** - Baza danych Redis (port 6379)

## 🛑 Zatrzymanie

```bash
make stop
# lub
docker-compose down
```

## 🧹 Usunięcie danych

```bash
make clean
# lub
docker-compose down -v
```
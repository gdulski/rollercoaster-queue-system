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
```bash
make help          # Wyświetl wszystkie dostępne komendy
make start         # Uruchom środowisko
make stop          # Zatrzymaj kontenery
make restart       # Restart kontenerów
make clean         # Usuń wszystkie kontenery i obrazy
make logs          # Wyświetl logi
make status        # Sprawdź status kontenerów
make test          # Uruchom testy API
make health        # Sprawdź health check
make redis         # Sprawdź Redis
make shell-php     # Otwórz shell w kontenerze PHP
make shell-redis   # Otwórz Redis CLI
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
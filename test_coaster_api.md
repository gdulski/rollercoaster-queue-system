# Test API dla kolejek górskich

## Uruchomienie testów

### Testy jednostkowe (PHPUnit)

**Uwaga:** Testy wymagają uruchomionej aplikacji z Redis.

```bash
# 1. Uruchom aplikację
make start
# lub
docker-compose up -d

# 2. Uruchom wszystkie testy
./vendor/bin/phpunit

# 3. Uruchom tylko testy coastera
./vendor/bin/phpunit tests/unit/CoasterApiTest.php

# 4. Uruchom z pokryciem kodu
./vendor/bin/phpunit --coverage-html tests/coverage/
```

### Testy integracyjne (curl)

## 🎢 Endpointy Kolejek (Coasters)

### Test 1: Utworzenie nowej kolejki górskiej

**Request:**
```bash
curl -X POST http://localhost:8080/api/coasters \
  -H "Content-Type: application/json" \
  -d '{
    "staff_count": 16,
    "daily_customers": 60000,
    "track_length": 1800,
    "opening_time": "8:00",
    "closing_time": "16:00"
  }'
```

**Oczekiwana odpowiedź:**
```json
{
  "success": true,
  "message": "Kolejka górska została pomyślnie utworzona",
  "timestamp": "2024-01-01 12:00:00",
  "data": {
    "id": "coaster_1234567890_1234",
    "staff_count": 16,
    "daily_customers": 60000,
    "track_length": 1800,
    "opening_time": "8:00",
    "closing_time": "16:00",
    "created_at": "2024-01-01 12:00:00",
    "updated_at": "2024-01-01 12:00:00"
  }
}
```

### Test 2: Pobranie wszystkich kolejek

**Request:**
```bash
curl -X GET http://localhost:8080/api/coasters
```

**Oczekiwana odpowiedź:**
```json
{
  "success": true,
  "timestamp": "2024-01-01 12:00:00",
  "data": [
    {
      "id": "coaster_1234567890_1234",
      "staff_count": 16,
      "daily_customers": 60000,
      "track_length": 1800,
      "opening_time": "8:00",
      "closing_time": "16:00",
      "created_at": "2024-01-01 12:00:00",
      "updated_at": "2024-01-01 12:00:00"
    }
  ]
}
```

### Test 3: Pobranie konkretnej kolejki

**Request:**
```bash
curl -X GET http://localhost:8080/api/coasters/coaster_1234567890_1234
```

**Oczekiwana odpowiedź:**
```json
{
  "success": true,
  "timestamp": "2024-01-01 12:00:00",
  "data": {
    "id": "coaster_1234567890_1234",
    "staff_count": 16,
    "daily_customers": 60000,
    "track_length": 1800,
    "opening_time": "8:00",
    "closing_time": "16:00",
    "created_at": "2024-01-01 12:00:00",
    "updated_at": "2024-01-01 12:00:00"
  }
}
```

### Test 4: Aktualizacja kolejki

**Request:**
```bash
curl -X PUT http://localhost:8080/api/coasters/coaster_1234567890_1234 \
  -H "Content-Type: application/json" \
  -d '{
    "staff_count": 20,
    "daily_customers": 70000,
    "opening_time": "7:00",
    "closing_time": "17:00"
  }'
```

**Oczekiwana odpowiedź:**
```json
{
  "success": true,
  "message": "Kolejka górska została pomyślnie zaktualizowana",
  "timestamp": "2024-01-01 12:00:00",
  "data": {
    "id": "coaster_1234567890_1234",
    "staff_count": 20,
    "daily_customers": 70000,
    "track_length": 1800,
    "opening_time": "7:00",
    "closing_time": "17:00",
    "created_at": "2024-01-01 12:00:00",
    "updated_at": "2024-01-01 12:05:00"
  }
}
```


## Testy walidacji

### Test 5: Błędne dane - brak wymaganych pól

**Request:**
```bash
curl -X POST http://localhost:8080/api/coasters \
  -H "Content-Type: application/json" \
  -d '{
    "staff_count": 16
  }'
```

**Oczekiwana odpowiedź:**
```json
{
  "success": false,
  "message": "Dane wejściowe są nieprawidłowe",
  "timestamp": "2024-01-01 12:00:00",
  "errors": {
    "daily_customers": "Pole liczba klientów jest wymagane",
    "track_length": "Pole długość trasy jest wymagane",
    "opening_time": "Pole godziny od jest wymagane",
    "closing_time": "Pole godziny do jest wymagane"
  }
}
```

### Test 6: Błędne dane - nieprawidłowy format czasu

**Request:**
```bash
curl -X POST http://localhost:8080/api/coasters \
  -H "Content-Type: application/json" \
  -d '{
    "staff_count": 16,
    "daily_customers": 60000,
    "track_length": 1800,
    "opening_time": "25:00",
    "closing_time": "16:00"
  }'
```

**Oczekiwana odpowiedź:**
```json
{
  "success": false,
  "message": "Dane wejściowe są nieprawidłowe",
  "timestamp": "2024-01-01 12:00:00",
  "errors": {
    "opening_time": "Godziny od muszą być w formacie HH:MM"
  }
}
```

### Test 7: Błędne dane - godzina rozpoczęcia po godzinie zakończenia

**Request:**
```bash
curl -X POST http://localhost:8080/api/coasters \
  -H "Content-Type: application/json" \
  -d '{
    "staff_count": 16,
    "daily_customers": 60000,
    "track_length": 1800,
    "opening_time": "18:00",
    "closing_time": "16:00"
  }'
```

**Oczekiwana odpowiedź:**
```json
{
  "success": false,
  "message": "Dane wejściowe są nieprawidłowe",
  "timestamp": "2024-01-01 12:00:00",
  "errors": {
    "closing_time": "Godzina zakończenia musi być późniejsza niż godzina rozpoczęcia"
  }
}
```

## 🚂 Endpointy Wagonów (Wagons)

### Test 8: Utworzenie nowego wagonu

**Request:**
```bash
curl -X POST http://localhost:8080/api/coasters/coaster_1234567890_1234/wagons \
  -H "Content-Type: application/json" \
  -d '{
    "seat_count": 32,
    "wagon_speed": 1.2
  }'
```

**Oczekiwana odpowiedź:**
```json
{
  "success": true,
  "message": "Wagon został pomyślnie utworzony",
  "timestamp": "2024-01-01 12:00:00",
  "data": {
    "coaster_id": "coaster_1234567890_1234",
    "seat_count": 32,
    "wagon_speed": 1.2
  }
}
```

### Test 9: Pobranie wszystkich wagonów kolejki

**Request:**
```bash
curl -X GET http://localhost:8080/api/coasters/coaster_1234567890_1234/wagons
```

**Oczekiwana odpowiedź:**
```json
{
  "success": true,
  "message": "Lista wagonów została pomyślnie pobrana",
  "timestamp": "2024-01-01 12:00:00",
  "data": {
    "wagons": [
      {
        "coaster_id": "coaster_1234567890_1234",
        "seat_count": 32,
        "wagon_speed": 1.2
      }
    ],
    "summary": {
      "total_seats": 32,
      "average_speed": 1.2,
      "wagon_count": 1
    }
  }
}
```

### Test 10: Usunięcie wagonu

**Request:**
```bash
curl -X DELETE http://localhost:8080/api/coasters/coaster_1234567890_1234/wagons/wagon_456
```

**Oczekiwana odpowiedź:**
```json
{
  "success": true,
  "message": "Wagon został pomyślnie usunięty",
  "timestamp": "2024-01-01 12:00:00",
  "data": []
}
```

## 📊 Endpointy Statystyk (Statistics)

### Test 11: Ogólne statystyki systemu

**Request:**
```bash
curl -X GET http://localhost:8080/api/statistics
```

### Test 12: Statystyki konkretnej kolejki

**Request:**
```bash
curl -X GET http://localhost:8080/api/statistics/coaster/coaster_1234567890_1234
```

### Test 13: Sprawdzenie stanu systemu

**Request:**
```bash
curl -X GET http://localhost:8080/api/statistics/health
```

### Test 14: Statystyki do wyświetlenia (format konsoli)

**Request:**
```bash
curl -X GET http://localhost:8080/api/statistics/display
```

## 🏥 Health Check

### Test 15: Sprawdzenie stanu aplikacji

**Request:**
```bash
curl -X GET http://localhost:8080/api/health
```

**Oczekiwana odpowiedź:**
```json
{
  "status": "OK",
  "message": "System kolejek górskich działa poprawnie",
  "timestamp": "2024-01-01 12:00:00",
  "version": "1.0.0",
  "environment": "development"
}
```

### Test 16: Sprawdzenie połączenia z Redis

**Request:**
```bash
curl -X GET http://localhost:8080/api/health/redis
```

**Oczekiwana odpowiedź:**
```json
{
  "status": "OK",
  "message": "Połączenie z Redis działa poprawnie",
  "timestamp": "2024-01-01 12:00:00"
}
```

## 🚀 Uruchomienie aplikacji

### Szybki start (Makefile)
```bash
# Uruchom aplikację
make start

# Sprawdź status
make status

# Zatrzymaj aplikację
make stop

# Wyświetl logi
make logs
```

### Ręczne uruchomienie
```bash
# 1. Uruchom kontenery
docker-compose up -d

# 2. Sprawdź status aplikacji
curl http://localhost:8080/api/health

# 3. Sprawdź połączenie z Redis
curl http://localhost:8080/api/health/redis
```

## 🧪 Testy z różnymi narzędziami

### Testy z HTTPie

```bash
# Instalacja
pip install httpie

# Test utworzenia kolejki
http POST localhost:8080/api/coasters \
  staff_count:=16 \
  daily_customers:=60000 \
  track_length:=1800 \
  opening_time="8:00" \
  closing_time="16:00"

# Test pobrania kolejek
http GET localhost:8080/api/coasters

# Test utworzenia wagonu
http POST localhost:8080/api/coasters/coaster_1234567890_1234/wagons \
  seat_count:=32 \
  wagon_speed:=1.2

# Test usunięcia wagonu
http DELETE localhost:8080/api/coasters/coaster_1234567890_1234/wagons/wagon_456
```

### Testy z Postman/Insomnia

Importuj kolekcję z pliku `postman_collection.json` do Postman lub Insomnia.

### Testy Redis

```bash
# Połącz się z Redis
docker-compose exec redis redis-cli

# W Redis CLI:
KEYS coaster:*
SMEMBERS coasters:index
GET coaster:coaster_1234567890_1234

# Sprawdź wagony
KEYS wagon:*
SMEMBERS wagons:coaster_1234567890_1234
```

## 📋 Podsumowanie endpointów

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
| GET | `/api/coasters/{id}/wagons/{wagonId}` | Pobranie konkretnego wagonu |
| DELETE | `/api/coasters/{id}/wagons/{wagonId}` | Usunięcie wagonu |
| GET | `/api/statistics` | Ogólne statystyki systemu |
| GET | `/api/statistics/coaster/{id}` | Statystyki konkretnej kolejki |
| GET | `/api/statistics/health` | Sprawdzenie stanu systemu |
| GET | `/api/statistics/display` | Statystyki do wyświetlenia |
| GET | `/api/statistics/monitor` | Dane monitorowania w czasie rzeczywistym |

## 🔧 Rozwiązywanie problemów

### Aplikacja nie odpowiada
```bash
# Sprawdź status kontenerów
docker-compose ps

# Sprawdź logi
docker-compose logs

# Restart aplikacji
make restart
```

### Błędy Redis
```bash
# Sprawdź połączenie z Redis
curl http://localhost:8080/api/health/redis

# Sprawdź logi Redis
docker-compose logs redis
```

### Błędy walidacji
Sprawdź czy wszystkie wymagane pola są podane i mają poprawny format zgodnie z regułami walidacji.

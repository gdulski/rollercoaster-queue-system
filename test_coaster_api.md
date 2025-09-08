# Test API dla kolejek górskich

## Uruchomienie testów

### Testy jednostkowe (PHPUnit)

**Uwaga:** Testy wymagają uruchomionej aplikacji z Redis.

```bash
# 1. Uruchom aplikację
docker-compose up -d

# 2. Uruchom wszystkie testy
./vendor/bin/phpunit

# 3. Uruchom tylko testy coastera
./vendor/bin/phpunit tests/unit/CoasterApiTest.php

# 4. Uruchom z pokryciem kodu
./vendor/bin/phpunit --coverage-html tests/coverage/
```

### Testy integracyjne (curl)

## Endpoint: POST /api/coasters

### Test 1: Utworzenie nowej kolejki górskiej

**Request:**
```bash



```

**Oczekiwana odpowiedź:**
```json
{
  "success": true,
  "message": "Kolejka górska została pomyślnie utworzona",
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

### Test 3: Pobranie konkretnej kolejki

**Request:**
```bash
curl -X GET http://localhost:8080/api/coasters/coaster_1234567890_1234
```

### Test 4: Aktualizacja kolejki

**Request:**
```bash
curl -X PUT http://localhost:8080/api/coasters/coaster_1234567890_1234 \
  -H "Content-Type: application/json" \
  -d '{
    "staff_count": 20,
    "daily_customers": 70000,
    "track_length": 1800,
    "opening_time": "7:00",
    "closing_time": "17:00"
  }'
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
  "status": 400,
  "error": "Pole liczba klientów jest wymagane"
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
  "status": 400,
  "error": "Godziny od muszą być w formacie HH:MM"
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
  "status": 400,
  "error": "Godzina rozpoczęcia musi być wcześniejsza niż godzina zakończenia"
}
```

## Uruchomienie aplikacji

1. Uruchom kontenery:
   ```bash
   docker-compose up -d
   ```

2. Sprawdź status:
   ```bash
   curl http://localhost:8080/api/health
   ```

3. Sprawdź połączenie z Redis:
   ```bash
   curl http://localhost:8080/api/health/redis
   ```

## Testy z Postman/Insomnia

Importuj kolekcję z pliku `postman_collection.json` do Postman lub Insomnia.

## Testy z HTTPie

```bash
# Instalacja
pip install httpie

# Test POST
http POST localhost:8080/api/coasters \
  staff_count:=16 \
  daily_customers:=60000 \
  track_length:=1800 \
  opening_time="8:00" \
  closing_time="16:00"

# Test GET
http GET localhost:8080/api/coasters
```

## Testy Redis

```bash
# Połącz się z Redis
docker-compose exec redis redis-cli

# W Redis CLI:
KEYS coaster:*
SMEMBERS coasters:index
GET coaster:coaster_1234567890_1234
```

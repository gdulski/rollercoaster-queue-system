# 🎢 API Documentation - System Kolejek Górskich

Kompletna dokumentacja API dla systemu zarządzania kolejkami górskimi.

## 📋 Spis treści

- [Podstawowe informacje](#podstawowe-informacje)
- [Uwierzytelnianie](#uwierzytelnianie)
- [Format odpowiedzi](#format-odpowiedzi)
- [Kody błędów](#kody-błędów)
- [Endpointy Health Check](#endpointy-health-check)
- [Endpointy Kolejek (Coasters)](#endpointy-kolejek-coasters)
- [Endpointy Wagonów (Wagons)](#endpointy-wagonów-wagons)
- [Endpointy Statystyk (Statistics)](#endpointy-statystyk-statistics)
- [Przykłady użycia](#przykłady-użycia)
- [Rozwiązywanie problemów](#rozwiązywanie-problemów)

## Podstawowe informacje

**Base URL:** `http://localhost:8080/api`  
**Wersja API:** 1.0.0  
**Format danych:** JSON  
**Kodowanie:** UTF-8

## Uwierzytelnianie

Obecnie API nie wymaga uwierzytelniania. Wszystkie endpointy są publiczne.

## Format odpowiedzi

### Sukces
```json
{
  "success": true,
  "message": "Operacja zakończona pomyślnie",
  "timestamp": "2024-01-01 12:00:00",
  "data": {
    // Dane odpowiedzi
  }
}
```

### Błąd
```json
{
  "success": false,
  "message": "Opis błędu",
  "timestamp": "2024-01-01 12:00:00",
  "errors": {
    "field_name": "Szczegółowy opis błędu"
  }
}
```

## Kody błędów

| Kod | Opis |
|-----|------|
| 200 | OK - Sukces |
| 201 | Created - Zasób utworzony |
| 400 | Bad Request - Błędne dane wejściowe |
| 404 | Not Found - Zasób nie znaleziony |
| 500 | Internal Server Error - Błąd serwera |
| 207 | Multi-Status - Częściowy sukces z problemami |

---

## 🏥 Endpointy Health Check

### GET /api/health

Sprawdza stan aplikacji.

**Odpowiedź:**
```json
{
  "status": "OK",
  "message": "System kolejek górskich działa poprawnie",
  "timestamp": "2024-01-01 12:00:00",
  "version": "1.0.0",
  "environment": "development"
}
```

### GET /api/health/redis

Sprawdza połączenie z Redis.

**Odpowiedź:**
```json
{
  "status": "OK",
  "message": "Połączenie z Redis działa poprawnie",
  "timestamp": "2024-01-01 12:00:00"
}
```

**Błąd:**
```json
{
  "status": "ERROR",
  "message": "Błąd połączenia z Redis: [szczegóły błędu]",
  "timestamp": "2024-01-01 12:00:00"
}
```

---

## 🎢 Endpointy Kolejek (Coasters)

### POST /api/coasters

Tworzy nową kolejkę górską.

**Request Body:**
```json
{
  "staff_count": 16,
  "daily_customers": 60000,
  "track_length": 1800,
  "opening_time": "8:00",
  "closing_time": "16:00"
}
```

**Parametry:**
- `staff_count` (integer, wymagane) - Liczba pracowników
- `daily_customers` (integer, wymagane) - Liczba klientów dziennie
- `track_length` (integer, wymagane) - Długość trasy w metrach
- `opening_time` (string, wymagane) - Godzina otwarcia (format HH:MM)
- `closing_time` (string, wymagane) - Godzina zamknięcia (format HH:MM)

**Odpowiedź (201):**
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

### GET /api/coasters

Pobiera wszystkie kolejki górskie.

**Odpowiedź (200):**
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

### GET /api/coasters/{id}

Pobiera konkretną kolejkę górską.

**Parametry URL:**
- `id` (string, wymagane) - ID kolejki górskiej

**Odpowiedź (200):**
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

**Błąd (404):**
```json
{
  "success": false,
  "message": "Kolejka górska o podanym ID nie została znaleziona",
  "timestamp": "2024-01-01 12:00:00"
}
```

### PUT /api/coasters/{id}

Aktualizuje kolejkę górską.

**Parametry URL:**
- `id` (string, wymagane) - ID kolejki górskiej

**Request Body:**
```json
{
  "staff_count": 20,
  "daily_customers": 70000,
  "opening_time": "7:00",
  "closing_time": "17:00"
}
```

**Odpowiedź (200):**
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

---

## 🚂 Endpointy Wagonów (Wagons)

### POST /api/coasters/{coasterId}/wagons

Tworzy nowy wagon dla konkretnej kolejki górskiej.

**Parametry URL:**
- `coasterId` (string, wymagane) - ID kolejki górskiej

**Request Body:**
```json
{
  "seat_count": 32,
  "wagon_speed": 1.2
}
```

**Parametry:**
- `seat_count` (integer, wymagane) - Liczba miejsc w wagonie
- `wagon_speed` (float, wymagane) - Prędkość wagonu (m/s)

**Odpowiedź (201):**
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

### GET /api/coasters/{coasterId}/wagons

Pobiera wszystkie wagony dla konkretnej kolejki górskiej.

**Parametry URL:**
- `coasterId` (string, wymagane) - ID kolejki górskiej

**Odpowiedź (200):**
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

### GET /api/coasters/{coasterId}/wagons/{wagonId}

Pobiera konkretny wagon.

**Parametry URL:**
- `coasterId` (string, wymagane) - ID kolejki górskiej
- `wagonId` (string, wymagane) - ID wagonu

**Odpowiedź (200):**
```json
{
  "success": true,
  "message": "Dane wagonu zostały pomyślnie pobrane",
  "timestamp": "2024-01-01 12:00:00",
  "data": {
    "coaster_id": "coaster_1234567890_1234",
    "seat_count": 32,
    "wagon_speed": 1.2
  }
}
```

### DELETE /api/coasters/{coasterId}/wagons/{wagonId}

Usuwa wagon.

**Parametry URL:**
- `coasterId` (string, wymagane) - ID kolejki górskiej
- `wagonId` (string, wymagane) - ID wagonu

**Odpowiedź (200):**
```json
{
  "success": true,
  "message": "Wagon został pomyślnie usunięty",
  "timestamp": "2024-01-01 12:00:00",
  "data": []
}
```

---

## 📊 Endpointy Statystyk (Statistics)

### GET /api/statistics

Pobiera ogólne statystyki systemu.

**Odpowiedź (200):**
```json
{
  "success": true,
  "message": "Statystyki systemu wygenerowane pomyślnie",
  "timestamp": "2024-01-01 12:00:00",
  "data": {
    "timestamp": "2024-01-01 12:00:00",
    "date": "2024-01-01",
    "coasters": [
      {
        "name": "coaster_1234567890_1234",
        "operating_hours": "8:00-16:00",
        "wagon_count": 3,
        "available_personnel": 16,
        "required_personnel": 20,
        "daily_customers": 60000,
        "status": "OK",
        "problems": []
      }
    ],
    "summary": {
      "total_coasters": 1,
      "total_wagons": 3,
      "total_available_personnel": 16,
      "total_required_personnel": 20,
      "system_status": "OK",
      "coasters_with_problems": 0
    }
  }
}
```

### GET /api/statistics/coaster/{id}

Pobiera statystyki dla konkretnej kolejki górskiej.

**Parametry URL:**
- `id` (string, wymagane) - ID kolejki górskiej

**Odpowiedź (200):**
```json
{
  "success": true,
  "message": "Statystyki kolejki górskiej pobrane pomyślnie",
  "timestamp": "2024-01-01 12:00:00",
  "data": {
    "coaster_id": "coaster_1234567890_1234",
    "operating_hours": "8:00-16:00",
    "wagon_count": 3,
    "available_personnel": 16,
    "required_personnel": 20,
    "daily_customers": 60000,
    "status": "OK",
    "problems": []
  }
}
```

### GET /api/statistics/health

Sprawdza stan systemu z identyfikacją problemów.

**Odpowiedź (200):**
```json
{
  "overall_status": "HEALTHY",
  "timestamp": "2024-01-01 12:00:00",
  "issues": [],
  "coasters": [
    {
      "id": "coaster_1234567890_1234",
      "status": "OK",
      "problems": []
    }
  ]
}
```

**Odpowiedź z problemami (207):**
```json
{
  "overall_status": "CRITICAL",
  "timestamp": "2024-01-01 12:00:00",
  "issues": [
    "Niedobór personelu w kolejce coaster_1234567890_1234"
  ],
  "coasters": [
    {
      "id": "coaster_1234567890_1234",
      "status": "CRITICAL",
      "problems": [
        "Niedobór personelu: 16/20"
      ]
    }
  ]
}
```

### GET /api/statistics/display

Pobiera statystyki w formacie do wyświetlenia (konsola).

**Odpowiedź (200):**
```json
{
  "success": true,
  "message": "Statystyki sformatowane do wyświetlenia",
  "timestamp": "2024-01-01 12:00:00",
  "data": {
    "header": "[Godzina 2024-01-01 12:00:00]",
    "date": "2024-01-01",
    "coasters": [
      {
        "name": "[coaster_1234567890_1234]",
        "details": [
          "1. Godziny działania: 8:00-16:00",
          "2. Liczba wagonów: 3",
          "3. Dostępny personel: 16/20",
          "4. Klienci dziennie: 60000",
          "5. Status: OK"
        ]
      }
    ],
    "summary": {
      "total_coasters": 1,
      "total_wagons": 3,
      "total_personnel": "16/20",
      "system_status": "OK",
      "problematic_coasters": 0
    }
  }
}
```

### GET /api/statistics/monitor

Pobiera dane monitorowania w czasie rzeczywistym.

**Odpowiedź (200):**
```json
{
  "success": true,
  "message": "Dane monitorowania pobrane pomyślnie",
  "timestamp": "2024-01-01 12:00:00",
  "data": {
    "monitoring": true,
    "real_time": true,
    "timestamp": "2024-01-01 12:00:00",
    "data": {
      // Pełne dane statystyk systemu
    }
  }
}
```

---

## Przykłady użycia

### cURL

```bash
# Utworzenie kolejki górskiej
curl -X POST http://localhost:8080/api/coasters \
  -H "Content-Type: application/json" \
  -d '{
    "staff_count": 16,
    "daily_customers": 60000,
    "track_length": 1800,
    "opening_time": "8:00",
    "closing_time": "16:00"
  }'

# Pobranie wszystkich kolejek
curl -X GET http://localhost:8080/api/coasters

# Utworzenie wagonu
curl -X POST http://localhost:8080/api/coasters/coaster_1234567890_1234/wagons \
  -H "Content-Type: application/json" \
  -d '{
    "seat_count": 32,
    "wagon_speed": 1.2
  }'

# Pobranie statystyk
curl -X GET http://localhost:8080/api/statistics
```

### HTTPie

```bash
# Instalacja
pip install httpie

# Utworzenie kolejki
http POST localhost:8080/api/coasters \
  staff_count:=16 \
  daily_customers:=60000 \
  track_length:=1800 \
  opening_time="8:00" \
  closing_time="16:00"

# Pobranie kolejek
http GET localhost:8080/api/coasters
```

### JavaScript (Fetch API)

```javascript
// Utworzenie kolejki górskiej
const createCoaster = async () => {
  const response = await fetch('http://localhost:8080/api/coasters', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      staff_count: 16,
      daily_customers: 60000,
      track_length: 1800,
      opening_time: "8:00",
      closing_time: "16:00"
    })
  });
  
  const data = await response.json();
  console.log(data);
};

// Pobranie statystyk
const getStatistics = async () => {
  const response = await fetch('http://localhost:8080/api/statistics');
  const data = await response.json();
  console.log(data);
};
```

---

## Rozwiązywanie problemów

### Częste błędy

#### 400 Bad Request
- **Przyczyna:** Błędne dane wejściowe
- **Rozwiązanie:** Sprawdź format danych i wymagane pola

#### 404 Not Found
- **Przyczyna:** Zasób nie istnieje
- **Rozwiązanie:** Sprawdź czy ID jest poprawne

#### 500 Internal Server Error
- **Przyczyna:** Błąd serwera
- **Rozwiązanie:** Sprawdź logi aplikacji

### Walidacja danych

#### Kolejki górskie
- `staff_count`: Liczba całkowita > 0
- `daily_customers`: Liczba całkowita > 0
- `track_length`: Liczba całkowita > 0
- `opening_time`: Format HH:MM (24h)
- `closing_time`: Format HH:MM (24h), musi być późniejsza niż opening_time

#### Wagony
- `seat_count`: Liczba całkowita > 0
- `wagon_speed`: Liczba zmiennoprzecinkowa > 0

### Sprawdzanie stanu systemu

```bash
# Sprawdź stan aplikacji
curl http://localhost:8080/api/health

# Sprawdź połączenie z Redis
curl http://localhost:8080/api/health/redis

# Sprawdź statystyki systemu
curl http://localhost:8080/api/statistics/health
```

### Logi

```bash
# Wyświetl logi wszystkich kontenerów
docker-compose logs

# Wyświetl logi konkretnego kontenera
docker-compose logs php
docker-compose logs nginx
docker-compose logs redis
```

---

## 📝 Changelog

### v1.0.0 (2024-01-01)
- Pierwsza wersja API
- Podstawowe endpointy dla kolejek górskich
- Zarządzanie wagonami
- System statystyk i monitorowania
- Health check endpoints

---

## 🤝 Wsparcie

W przypadku problemów lub pytań:
1. Sprawdź logi aplikacji
2. Upewnij się, że wszystkie kontenery działają
3. Sprawdź połączenie z Redis
4. Sprawdź format danych wejściowych

**Aplikacja dostępna pod:** `http://localhost:8080`

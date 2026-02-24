# Devadhwani API

A comprehensive temple management system API built with Laravel 9.

## Features

- **Temple Management**: Register and manage multiple temples
- **OTP Authentication**: Secure phone-based authentication via WhatsApp (Twilio)
- **Devotee Management**: Track devotees with nakshatra and address details
- **Pooja Booking System**: Book poojas with daily, monthly, or yearly schedules
- **Payment Tracking**: Track partial and full payments for bookings
- **Panchang Integration**: Hindu calendar data via Prokerala API
- **Inventory Management**: Track items, suppliers, purchases, and usage
- **Role-based Access**: Member roles with granular permissions
- **Multi-tenant Support**: Database-per-temple architecture

## Requirements

- PHP 8.0.2+
- MySQL 5.7+ / MariaDB 10.3+
- Composer
- Node.js (for development)

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd devadhwani_api
   ```

2. **Install dependencies**
   ```bash
   composer install
   ```

3. **Configure environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Update `.env` with your settings**
   ```env
   DB_DATABASE=devadhwani
   DB_USERNAME=your_username
   DB_PASSWORD=your_password

   TWILIO_SID=your_twilio_sid
   TWILIO_AUTH_TOKEN=your_twilio_auth_token
   TWILIO_WHATSAPP_FROM=whatsapp:+1234567890
   TWILIO_ON=true

   PROKERALA_CLIENT_ID=your_prokerala_client_id
   PROKERALA_CLIENT_SECRET=your_prokerala_client_secret
   ```

5. **Run migrations**
   ```bash
   php artisan migrate
   ```

6. **Start the server**
   ```bash
   php artisan serve
   ```

## API Documentation

Full API documentation is available at [docs/API.md](docs/API.md).

### Quick Start

1. **Send OTP**
   ```bash
   curl -X POST http://localhost:8000/api/send-otp \
     -H "Content-Type: application/json" \
     -d '{"phone": "+919876543210"}'
   ```

2. **Verify OTP & Register**
   ```bash
   curl -X POST http://localhost:8000/api/verify-otp \
     -H "Content-Type: application/json" \
     -d '{
       "phone": "+919876543210",
       "otp": "123456",
       "temple_name": "Sri Krishna Temple",
       "temple_address": "123 Temple Street"
     }'
   ```

3. **Use the returned token for authenticated requests**
   ```bash
   curl -X GET http://localhost:8000/api/devotees \
     -H "Authorization: Bearer <token>"
   ```

## Testing

Run the test suite:

```bash
# Run all tests
php artisan test

# Run with coverage
php artisan test --coverage

# Run specific test file
php artisan test tests/Feature/DevoteeTest.php

# Run specific test method
php artisan test --filter=it_can_create_a_devotee
```

### Test Coverage

- **Feature Tests**: Authentication, Devotee CRUD, Temple management, Panchang
- **Unit Tests**: Models, ApiResponse trait

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/     # API controllers
│   ├── Middleware/          # Custom middleware
│   └── Requests/            # Form request validation
├── Models/                  # Eloquent models
├── Services/                # External API services
├── Traits/                  # Reusable traits (ApiResponse)
└── Helpers/                 # Helper classes
database/
├── factories/               # Model factories for testing
└── migrations/              # Database migrations
docs/
└── API.md                   # API documentation
tests/
├── Feature/                 # Feature/integration tests
└── Unit/                    # Unit tests
```

## Key Endpoints

| Endpoint | Method | Description |
|----------|--------|-------------|
| `/api/send-otp` | POST | Send OTP to phone |
| `/api/verify-otp` | POST | Verify OTP & register temple |
| `/api/login` | POST | Login with OTP |
| `/api/temples` | GET | List temples |
| `/api/devotees` | GET/POST | List/Create devotees |
| `/api/temple-poojas` | GET/POST | List/Create poojas |
| `/api/bookings` | GET/POST | List/Create bookings |
| `/api/panchang` | GET | Get today's panchang |

## Security Features

- **Rate Limiting**: OTP endpoints are rate-limited (5 sends/min, 10 verifies/min)
- **Sanctum Authentication**: Token-based API authentication
- **Input Validation**: Form request classes for input validation
- **Soft Deletes**: Records are soft-deleted by default

## Configuration

### Twilio (WhatsApp OTP)
Configure in `config/services.php`:
```php
'twilio' => [
    'sid' => env('TWILIO_SID'),
    'auth_token' => env('TWILIO_AUTH_TOKEN'),
    'whatsapp_from' => env('TWILIO_WHATSAPP_FROM'),
    'enabled' => env('TWILIO_ON', false),
],
```

### Prokerala (Panchang API)
Configure in `config/services.php`:
```php
'prokerala' => [
    'client_id' => env('PROKERALA_CLIENT_ID'),
    'client_secret' => env('PROKERALA_CLIENT_SECRET'),
],
```

## Scheduled Tasks

The following commands run on schedule:

```bash
# Fetch panchang data for next 4 days
php artisan panchang:fetch-four-days

# Complete temple pooja bookings
php artisan bookings:complete
```

Add to crontab:
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## Contributing

1. Create a feature branch
2. Write tests for new features
3. Ensure all tests pass
4. Submit a pull request

## License

This project is proprietary software.

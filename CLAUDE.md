# Devadhwani API

A Laravel-based temple management system API for managing temples, devotees, pooja bookings, inventory, and more.

## Project Structure

```
app/
├── Http/
│   ├── Controllers/Api/     # API controllers
│   ├── Middleware/          # Custom middleware
│   └── Requests/            # Form request validation classes
├── Models/                  # Eloquent models
├── Services/                # Business logic services
├── Traits/                  # Reusable traits
└── Helpers/                 # Helper classes
```

## Key Conventions

### API Response Format
Use the `ApiResponse` trait for consistent responses:
```php
use App\Traits\ApiResponse;

class MyController extends Controller
{
    use ApiResponse;

    public function index()
    {
        return $this->success($data, 'Message');
        return $this->successWithPagination($paginator);
        return $this->error('Error message', 400);
        return $this->notFound('Resource not found');
    }
}
```

### Form Requests
Use Form Request classes for validation instead of inline validation:
- `app/Http/Requests/StoreDevoteeRequest.php`
- `app/Http/Requests/UpdateDevoteeRequest.php`

### Configuration
- Never use `env()` directly in code, use `config()` instead
- Third-party service credentials go in `config/services.php`
- All credentials must be stored in `.env` (never commit actual values)

### Authentication
- OTP-based authentication via Twilio WhatsApp
- Uses Laravel Sanctum for API tokens
- Rate limiting applied to OTP endpoints

### Multi-tenancy
- Each temple can have its own database
- `TempleDatabaseMiddleware` handles database switching
- Tenant models are in `app/Models/Tenant/`

## Running Tests

```bash
php artisan test
```

## Common Commands

```bash
# Clear all caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Run migrations
php artisan migrate

# Run Panchang cron
php artisan panchang:fetch
```

## External Services

- **Prokerala API**: Panchang/calendar data
- **Twilio**: WhatsApp OTP delivery

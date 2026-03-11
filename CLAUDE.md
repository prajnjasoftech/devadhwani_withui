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

## Key Models & Relationships

### Deities, Poojas & Bookings
- **TempleDeity**: Master table for temple deities (e.g., Lord Ganesha, Lord Shiva)
- **TemplePooja**: Poojas can optionally have a default deity (`deity_id`)
- **TemplePoojaBooking**: Bookings can have a deity (`deity_id`)
  - When a pooja is selected, the deity auto-populates from the pooja's default
  - User can override the deity in the booking

```
TempleDeity (1) ──────< TemplePooja (many)
     │
     └──────────────< TemplePoojaBooking (many)
```

### Receipts & Payments
Payments are tracked at the **receipt level**, not per-booking. This prevents rounding issues when multiple bookings share a payment.

- **Receipt**: Groups one or more bookings, tracks total/paid/balance
- **ReceiptPayment**: Individual payments against a receipt
- **TemplePoojaBooking**: Links to receipt via `receipt_id`

```
Receipt (1) ─────────< TemplePoojaBooking (many)
    │
    └────────────────< ReceiptPayment (many)
```

**Usage:**
```php
// Create receipt
$receipt = Receipt::create([
    'temple_id' => $templeId,
    'receipt_number' => Receipt::generateReceiptNumber($templeId),
    'receipt_date' => now(),
    'total_amount' => 2100,
    'net_amount' => 2100,
]);

// Add bookings to receipt
$booking->update(['receipt_id' => $receipt->id]);
$receipt->recalculateTotals();

// Record payment (auto-updates receipt totals)
ReceiptPayment::create([
    'receipt_id' => $receipt->id,
    'amount' => 2000,
    'payment_date' => now(),
    'payment_mode' => 'cash',
]);
// receipt->balance_due is now 100, payment_status is 'partial'
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

## Frontend Utilities

### Malayalam Localization
The app displays nakshatras and Malayalam calendar months in Malayalam script (മലയാളം).

**Utility file:** `resources/js/utils/malayalam.js`
```javascript
import { toMalayalamNakshatra, nakshatraList, malayalamMonths } from '@/utils/malayalam';

// Convert English nakshatra to Malayalam
toMalayalamNakshatra('Ashwini');  // Returns 'അശ്വതി'

// Get dropdown options with both scripts
nakshatraList;  // [{ value: 'Ashwini', label: 'അശ്വതി (Ashwini)' }, ...]

// Malayalam months for Panchang calendar
malayalamMonths;  // { 'Chingam': 'ചിങ്ങം', 'Kanni': 'കന്നി', ... }
```

**Used in:**
- Panchang calendar (month names, nakshatra)
- Devotee pages (nakshatra display and dropdowns)
- Booking pages (nakshatra display and dropdowns)

## External Services

- **Prokerala API**: Panchang/calendar data
- **Twilio**: WhatsApp OTP delivery

# Devadhwani API - Complete Project Documentation

## Project Overview

**Devadhwani API** is a comprehensive Laravel-based temple management system designed for managing temples, devotees, pooja bookings, payments, inventory, and related operations. It supports multiple temples through a multi-tenant architecture with OTP-based authentication.

### Key Technologies
- **Backend**: Laravel 10+ (PHP 8.2)
- **Authentication**: Laravel Sanctum (API tokens)
- **Frontend**: Vue.js with Inertia.js
- **Database**: MySQL
- **External APIs**: Prokerala (Panchang), Twilio (WhatsApp OTP)

---

## System Architecture

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                           DEVADHWANI ARCHITECTURE                            │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│   ┌──────────────┐     ┌──────────────┐     ┌──────────────┐               │
│   │  Mobile App  │     │   Web UI     │     │  External    │               │
│   │  (Flutter)   │     │  (Vue.js)    │     │  Services    │               │
│   └──────┬───────┘     └──────┬───────┘     └──────┬───────┘               │
│          │                    │                    │                        │
│          │    HTTP/REST       │    Inertia.js      │                        │
│          ▼                    ▼                    ▼                        │
│   ┌─────────────────────────────────────────────────────────┐              │
│   │                    LARAVEL API                           │              │
│   │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐     │              │
│   │  │    Auth     │  │ Controllers │  │  Services   │     │              │
│   │  │  (Sanctum)  │  │  (API/Web)  │  │  (Business) │     │              │
│   │  └─────────────┘  └─────────────┘  └─────────────┘     │              │
│   │                          │                              │              │
│   │                          ▼                              │              │
│   │  ┌─────────────────────────────────────────────────┐   │              │
│   │  │              ELOQUENT MODELS                     │   │              │
│   │  │  Temple │ Devotee │ Pooja │ Booking │ Receipt   │   │              │
│   │  └─────────────────────────────────────────────────┘   │              │
│   └─────────────────────────────────────────────────────────┘              │
│                              │                                              │
│                              ▼                                              │
│   ┌─────────────────────────────────────────────────────────┐              │
│   │                     MYSQL DATABASE                       │              │
│   │  ┌─────────────┐  ┌─────────────┐  ┌─────────────┐     │              │
│   │  │ Main DB     │  │ Tenant DB 1 │  │ Tenant DB 2 │     │              │
│   │  │ (temples,   │  │ (temple 1   │  │ (temple 2   │     │              │
│   │  │  shared)    │  │  specific)  │  │  specific)  │     │              │
│   │  └─────────────┘  └─────────────┘  └─────────────┘     │              │
│   └─────────────────────────────────────────────────────────┘              │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Module Overview

### 1. Authentication Module
- OTP-based login via Twilio WhatsApp
- Temple registration with OTP verification
- Member (staff) authentication
- Sanctum token management

### 2. Temple Management
- Temple profile (name, address, logo)
- Multi-tenant database support
- Temple-scoped data isolation

### 3. Devotee Management
- Devotee registration (name, phone, nakshatra, address)
- Search and filtering
- Soft delete support

### 4. Deity Management
- Master list of temple deities
- Deity-pooja associations
- Image support

### 5. Pooja Management
- Pooja service definitions
- Pricing and scheduling (once/daily/monthly/yearly)
- Deity assignment

### 6. Booking System
- Pooja booking with devotee selection
- Date range support for recurring bookings
- Payment tracking per booking
- Status management (pending/completed/cancelled)

### 7. Payment System (Receipt-Based)
- Receipt generation for grouped bookings
- Partial payment support
- Payment mode tracking (cash/card/online/UPI)
- Automatic balance calculation

### 8. Inventory Management
- Categories and items
- Stock tracking (current_quantity)
- Suppliers and purchases
- Usage/consumption tracking

### 9. Panchang Integration
- Hindu calendar data from Prokerala API
- Nakshatra-based date calculations
- Malayalam month support
- Data caching

### 10. Reporting
- Transaction summaries
- Pending pooja reports
- Revenue tracking

---

## Data Flow Diagrams

### Authentication Flow

```
┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐
│  User   │───▶│ Send OTP│───▶│ Twilio  │───▶│WhatsApp │───▶│  User   │
│ (Phone) │    │  API    │    │   API   │    │ Message │    │ Receives│
└─────────┘    └─────────┘    └─────────┘    └─────────┘    └────┬────┘
                                                                 │
    ┌────────────────────────────────────────────────────────────┘
    │
    ▼
┌─────────┐    ┌─────────┐    ┌─────────┐    ┌─────────┐
│  Enter  │───▶│ Verify  │───▶│ Create/ │───▶│ Return  │
│   OTP   │    │  OTP    │    │  Login  │    │  Token  │
└─────────┘    └─────────┘    └─────────┘    └─────────┘
```

### Booking Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           BOOKING DATA FLOW                              │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  1. SELECT POOJA                                                        │
│     └── GET /temple-poojas ──► Returns: id, name, amount, deity, period │
│                                                                          │
│  2. SELECT/CREATE DEVOTEES                                              │
│     ├── Existing: GET /devotees?search=name                             │
│     └── New: Created during booking                                     │
│                                                                          │
│  3. SELECT DATE RANGE                                                   │
│     ├── once: single date                                               │
│     ├── daily: start → end (every day)                                  │
│     ├── monthly: start → end (nakshatra-based dates)                    │
│     └── yearly: start → end (anniversary dates)                         │
│                                                                          │
│  4. SUBMIT BOOKING                                                      │
│     POST /bookings ──►                                                  │
│     │                                                                    │
│     ├── Create TemplePoojaBooking                                       │
│     │   └── booking_number: "BKG-XXXXXXXX"                              │
│     │                                                                    │
│     ├── Resolve Devotees                                                │
│     │   ├── Find existing by ID                                         │
│     │   └── Create new if not found                                     │
│     │                                                                    │
│     ├── Create Trackings (per devotee × date)                          │
│     │   └── TemplePoojaBookingTracking                                  │
│     │       - pooja_date                                                │
│     │       - paid_amount: 0                                            │
│     │       - due_amount: pooja_amount                                  │
│     │       - payment_status: pending                                   │
│     │                                                                    │
│     └── Apply Payment (if any)                                          │
│         └── Mark trackings as paid (oldest first)                       │
│                                                                          │
│  5. RESPONSE                                                            │
│     └── Return booking with trackings                                   │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘
```

### Payment Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                           PAYMENT DATA FLOW                              │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  OPTION A: Booking-Level Payment (Current)                              │
│  ─────────────────────────────────────────                              │
│                                                                          │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐              │
│  │   Booking    │───▶│ PaymentDetail│───▶│  Tracking    │              │
│  │ (amount_rcpt)│    │  (credit)    │    │  (paid/due)  │              │
│  └──────────────┘    └──────────────┘    └──────────────┘              │
│                                                                          │
│  OPTION B: Receipt-Based Payment (Recommended)                          │
│  ─────────────────────────────────────────────                          │
│                                                                          │
│  ┌──────────────┐         ┌──────────────┐                              │
│  │   Booking 1  │────┐    │              │                              │
│  └──────────────┘    │    │              │    ┌──────────────┐          │
│                      ├───▶│   Receipt    │◀───│ReceiptPayment│          │
│  ┌──────────────┐    │    │ (aggregated) │    │ (individual) │          │
│  │   Booking 2  │────┘    │              │    └──────────────┘          │
│  └──────────────┘         └──────────────┘                              │
│                                                                          │
│  Receipt Benefits:                                                      │
│  - Prevents rounding issues                                             │
│  - Tracks multiple bookings together                                    │
│  - Auto-calculates totals                                               │
│  - Supports partial payments                                            │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘
```

### Inventory Flow

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         INVENTORY DATA FLOW                              │
├─────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ┌──────────────┐    ┌──────────────┐    ┌──────────────┐              │
│  │   Category   │◀───│     Item     │───▶│   Supplier   │              │
│  │ (Grocery,    │    │ (Rice, Oil)  │    │ (ABC Store)  │              │
│  │  Pooja Items)│    │              │    │              │              │
│  └──────────────┘    └──────┬───────┘    └──────────────┘              │
│                             │                                           │
│              ┌──────────────┼──────────────┐                           │
│              │              │              │                           │
│              ▼              ▼              ▼                           │
│       ┌──────────────┐ ┌──────────────┐ ┌──────────────┐              │
│       │   Purchase   │ │    Usage     │ │    Stock     │              │
│       │   (+qty)     │ │   (-qty)     │ │  (current)   │              │
│       └──────────────┘ └──────────────┘ └──────────────┘              │
│                                                                          │
│  Stock Calculation:                                                     │
│  current_quantity = SUM(purchases) - SUM(usages)                       │
│                                                                          │
└─────────────────────────────────────────────────────────────────────────┘
```

---

## Database Schema

### Entity Relationship Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              DATABASE SCHEMA                                 │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  ┌─────────────┐                                                            │
│  │   TEMPLE    │──────────────────────────────────────────┐                 │
│  │─────────────│                                          │                 │
│  │ id          │◀──┐                                      │                 │
│  │ temple_name │   │                                      │                 │
│  │ phone       │   │                                      │                 │
│  │ temple_logo │   │                                      │                 │
│  └─────────────┘   │                                      │                 │
│        │           │                                      │                 │
│        │ 1:N       │                                      │                 │
│        ▼           │                                      │                 │
│  ┌─────────────┐   │    ┌─────────────┐    ┌─────────────┐                 │
│  │   MEMBER    │   │    │    ROLE     │    │   OTP_LOG   │                 │
│  │─────────────│   │    │─────────────│    │─────────────│                 │
│  │ temple_id   │───┘    │ temple_id   │    │ phone       │                 │
│  │ role_id     │───────▶│ role_name   │    │ otp         │                 │
│  │ name, phone │        │ role (JSON) │    │ is_verified │                 │
│  └─────────────┘        └─────────────┘    └─────────────┘                 │
│                                                                              │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐                     │
│  │  DEVOTEE    │    │TEMPLE_DEITY │    │TEMPLE_POOJA │                     │
│  │─────────────│    │─────────────│    │─────────────│                     │
│  │ temple_id   │    │ temple_id   │◀───│ temple_id   │                     │
│  │ devotee_name│    │ name        │    │ deity_id    │──┐                  │
│  │ phone       │    │ image       │    │ pooja_name  │  │                  │
│  │ nakshatra   │    │ is_active   │    │ amount      │  │                  │
│  └──────┬──────┘    └──────┬──────┘    │ period      │  │                  │
│         │                  │           └──────┬──────┘  │                  │
│         │                  │                  │         │                  │
│         │                  └──────────────────┼─────────┘                  │
│         │                                     │                            │
│         │              ┌──────────────────────┘                            │
│         │              │                                                   │
│         │              ▼                                                   │
│         │    ┌─────────────────────┐                                       │
│         │    │TEMPLE_POOJA_BOOKING │                                       │
│         │    │─────────────────────│                                       │
│         │    │ temple_id           │                                       │
│         │    │ pooja_id            │                                       │
│         │    │ deity_id            │                                       │
│         │    │ booking_number      │                                       │
│         │    │ booking_date        │                                       │
│         │    │ period              │                                       │
│         │    │ pooja_amount        │                                       │
│         │    │ receipt_id          │───────────┐                           │
│         │    └──────────┬──────────┘           │                           │
│         │               │                      │                           │
│         │               │ 1:N                  │                           │
│         │               ▼                      ▼                           │
│         │    ┌─────────────────────┐  ┌─────────────┐                     │
│         │    │BOOKING_TRACKING     │  │   RECEIPT   │                     │
│         │    │─────────────────────│  │─────────────│                     │
│         └───▶│ booking_id          │  │ temple_id   │                     │
│              │ devotee_id          │  │ receipt_no  │                     │
│              │ pooja_date          │  │ total_amount│                     │
│              │ paid_amount         │  │ amount_paid │                     │
│              │ due_amount          │  │ balance_due │                     │
│              │ payment_status      │  └──────┬──────┘                     │
│              │ booking_status      │         │                            │
│              └─────────────────────┘         │ 1:N                        │
│                                              ▼                            │
│                                     ┌─────────────────┐                   │
│                                     │RECEIPT_PAYMENT  │                   │
│                                     │─────────────────│                   │
│                                     │ receipt_id      │                   │
│                                     │ amount          │                   │
│                                     │ payment_mode    │                   │
│                                     │ payment_date    │                   │
│                                     └─────────────────┘                   │
│                                                                              │
│  INVENTORY TABLES                                                           │
│  ─────────────────                                                          │
│                                                                              │
│  ┌─────────────┐    ┌─────────────┐    ┌─────────────┐                     │
│  │  CATEGORY   │◀───│    ITEM     │───▶│  SUPPLIER   │                     │
│  │─────────────│    │─────────────│    │─────────────│                     │
│  │ temple_id   │    │ temple_id   │    │ temple_id   │                     │
│  │ name        │    │ category_id │    │ name        │                     │
│  └─────────────┘    │ item_name   │    │ contact     │                     │
│                     │ current_qty │    └─────────────┘                     │
│                     └──────┬──────┘                                        │
│                            │                                               │
│              ┌─────────────┼─────────────┐                                 │
│              │             │             │                                 │
│              ▼             ▼             ▼                                 │
│       ┌─────────────┐ ┌─────────────┐ ┌─────────────┐                     │
│       │  PURCHASE   │ │    USAGE    │ │PAYMENT_DETAIL│                    │
│       │─────────────│ │─────────────│ │─────────────│                     │
│       │ item_id     │ │ item_id     │ │ temple_id   │                     │
│       │ supplier_id │ │ used_by     │ │ source      │                     │
│       │ quantity    │ │ quantity    │ │ source_id   │                     │
│       │ total_price │ │ date        │ │ payment     │                     │
│       └─────────────┘ └─────────────┘ └─────────────┘                     │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## API Endpoints Reference

### Authentication

| Method | Endpoint | Description | Rate Limit |
|--------|----------|-------------|------------|
| POST | `/api/send-otp` | Send OTP to phone | 5/min |
| POST | `/api/verify-otp` | Verify OTP & register | 10/min |
| POST | `/api/login` | Login with OTP | 10/min |
| POST | `/api/logout` | Logout (revoke token) | - |

### Temple & Devotee

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/temples` | List temples |
| PUT | `/api/temples/{id}` | Update temple |
| GET | `/api/devotees` | List devotees |
| POST | `/api/devotees` | Create devotee |
| GET | `/api/devotees/{id}` | Get devotee |
| PUT | `/api/devotees/{id}` | Update devotee |
| DELETE | `/api/devotees/{id}` | Delete devotee |

### Deity & Pooja

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/temple-deities` | List deities |
| POST | `/api/temple-deities` | Create deity |
| GET | `/api/temple-poojas` | List poojas |
| POST | `/api/temple-poojas` | Create pooja |
| GET | `/api/bookings` | List bookings |
| POST | `/api/bookings` | Create booking |
| PUT | `/api/bookings/{id}` | Update booking |
| DELETE | `/api/bookings/{id}` | Delete booking |

### Inventory

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/categories` | List categories |
| GET | `/api/items` | List items |
| GET | `/api/suppliers` | List suppliers |
| GET | `/api/purchases` | List purchases |
| POST | `/api/purchases` | Create purchase |
| GET | `/api/usages` | List usages |
| POST | `/api/usages` | Create usage |

### Reports

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/transaction-summary` | Transaction report |
| GET | `/api/pending-pooja-summary` | Pending poojas |
| GET | `/api/panchang` | Panchang data |

---

## File Structure

```
devadhwani_api/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/                    # REST API Controllers
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── DevoteeController.php
│   │   │   │   ├── TempleDeityController.php
│   │   │   │   ├── TemplePoojaController.php
│   │   │   │   ├── TemplePoojaBookingController.php
│   │   │   │   ├── ItemController.php
│   │   │   │   ├── PurchaseController.php
│   │   │   │   └── ... (21 controllers)
│   │   │   └── Web/                    # Inertia/Web Controllers
│   │   │       ├── DashboardController.php
│   │   │       ├── BookingController.php
│   │   │       └── ... (13 controllers)
│   │   ├── Middleware/
│   │   │   ├── TempleDatabaseMiddleware.php
│   │   │   └── ...
│   │   └── Requests/
│   │       ├── StoreDevoteeRequest.php
│   │       └── UpdateDevoteeRequest.php
│   ├── Models/
│   │   ├── Temple.php
│   │   ├── Devotee.php
│   │   ├── TempleDeity.php
│   │   ├── TemplePooja.php
│   │   ├── TemplePoojaBooking.php
│   │   ├── TemplePoojaBookingTracking.php
│   │   ├── Receipt.php
│   │   ├── ReceiptPayment.php
│   │   ├── Item.php
│   │   ├── Category.php
│   │   ├── Purchase.php
│   │   ├── Panchang.php
│   │   └── ... (18 models)
│   ├── Services/
│   │   ├── ProkeralaService.php       # Panchang API
│   │   └── PanchangService.php        # Panchang caching
│   ├── Traits/
│   │   └── ApiResponse.php            # Standardized responses
│   └── Helpers/
│       └── TenantHelper.php           # Multi-tenancy
├── database/
│   └── migrations/                     # 40+ migrations
├── routes/
│   ├── api.php                        # API routes
│   └── web.php                        # Web routes
├── resources/
│   └── js/
│       ├── Pages/                     # Vue.js pages
│       └── utils/
│           └── malayalam.js           # Localization
├── config/
│   └── services.php                   # Twilio, Prokerala config
└── docs/
    ├── booking.md                     # Booking documentation
    └── devadhwani.md                  # This file
```

---

## Configuration

### Environment Variables (.env)

```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=devadhwani
DB_USERNAME=root
DB_PASSWORD=

# Twilio (WhatsApp OTP)
TWILIO_SID=your_twilio_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_WHATSAPP_FROM=whatsapp:+14155238886
TWILIO_ON=0                            # 0=disabled, 1=enabled
TEST_OTP=123456                        # Test OTP when Twilio disabled

# Prokerala (Panchang API)
PROKERALA_CLIENT_ID=your_client_id
PROKERALA_CLIENT_SECRET=your_secret
```

### Service Configuration (config/services.php)

```php
'twilio' => [
    'sid' => env('TWILIO_SID'),
    'auth_token' => env('TWILIO_AUTH_TOKEN'),
    'whatsapp_from' => env('TWILIO_WHATSAPP_FROM'),
    'enabled' => env('TWILIO_ON', false),
],

'prokerala' => [
    'client_id' => env('PROKERALA_CLIENT_ID'),
    'client_secret' => env('PROKERALA_CLIENT_SECRET'),
],
```

---

## API Response Format

### Success Response
```json
{
  "status": true,
  "message": "Success message",
  "data": { /* resource data */ }
}
```

### Paginated Response
```json
{
  "status": true,
  "data": [ /* items */ ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 100,
    "last_page": 10
  }
}
```

### Error Response
```json
{
  "status": false,
  "error": "Error message",
  "errors": { /* validation errors */ }
}
```

---

## Common Commands

```bash
# Clear caches
php artisan config:clear
php artisan cache:clear
php artisan route:clear

# Run migrations
php artisan migrate

# Fetch Panchang data
php artisan panchang:fetch

# Run tests
php artisan test
```

---

## Security Features

1. **Rate Limiting** - OTP endpoints protected (5-10 requests/minute)
2. **Input Validation** - Form request classes for all inputs
3. **Soft Deletes** - Safe deletion with recovery
4. **Sanctum Auth** - Secure API token management
5. **CSRF Protection** - Web forms protected
6. **Cookie Encryption** - Automatic encryption

---

## External Integrations

### Twilio (WhatsApp OTP)
- Sends OTP via WhatsApp
- Template-based messages
- Test mode for development

### Prokerala (Panchang)
- Hindu calendar/astrology data
- Nakshatra information
- Malayalam calendar support
- OAuth2 authentication

---

## Recent Updates (March 2026)

1. **Receipt-Based Payment System** - Payment aggregation
2. **Temple Logo Base64** - Mobile app support
3. **Item Category Display** - "Item - Category" format
4. **Deity Management** - Full deity system
5. **Migration Fixes** - Index rollback fixes

---

## Support

- **Issues**: https://github.com/anthropics/claude-code/issues
- **Documentation**: `/docs` folder in project

# Booking Process Documentation

## Overview Diagram

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                              BOOKING FLOW                                    │
├─────────────────────────────────────────────────────────────────────────────┤
│                                                                              │
│  UI (Mobile/Web)                         API (Backend)                       │
│  ───────────────                         ────────────                        │
│                                                                              │
│  1. Select Pooja ──────────────────────► GET /temple-poojas                 │
│     (with amount, period, deity)                                            │
│                                                                              │
│  2. Select/Add Devotees ───────────────► (optional - creates if new)        │
│     (name, phone, nakshatra)                                                │
│                                                                              │
│  3. Select Date Range ─────────────────► booking_date, booking_end_date     │
│     (based on period: once/daily/                                           │
│      monthly/yearly)                                                        │
│                                                                              │
│  4. Enter Payment ─────────────────────► pooja_amount_receipt               │
│     (partial or full)                                                       │
│                                                                              │
│  5. Submit Booking ────────────────────► POST /bookings                     │
│                                              │                              │
│                                              ▼                              │
│                                    ┌─────────────────────┐                  │
│                                    │  Create Booking     │                  │
│                                    │  (TemplePoojaBooking)│                  │
│                                    └──────────┬──────────┘                  │
│                                               │                              │
│                                               ▼                              │
│                                    ┌─────────────────────┐                  │
│                                    │  Resolve Devotees   │                  │
│                                    │  (create if new)    │                  │
│                                    └──────────┬──────────┘                  │
│                                               │                              │
│                                               ▼                              │
│                                    ┌─────────────────────┐                  │
│                                    │  Create Trackings   │                  │
│                                    │  (per devotee,      │                  │
│                                    │   per pooja date)   │                  │
│                                    └──────────┬──────────┘                  │
│                                               │                              │
│                                               ▼                              │
│                                    ┌─────────────────────┐                  │
│                                    │  Apply Payment      │                  │
│                                    │  (mark trackings    │                  │
│                                    │   as paid)          │                  │
│                                    └─────────────────────┘                  │
│                                                                              │
└─────────────────────────────────────────────────────────────────────────────┘
```

---

## Step-by-Step Process

### 1. UI: User Selects Pooja
- User picks a pooja from dropdown
- Pooja has: `name`, `amount`, `period` (once/daily/monthly/yearly), `deity_id`

### 2. UI: User Adds Devotees (if pooja requires)
- Can select existing devotee OR create new one
- Devotee data: `name`, `phone`, `nakshatra`, `address`

### 3. UI: User Selects Date Range

| Period | Date Selection |
|--------|----------------|
| `once` | Single date |
| `daily` | Start date → End date (every day) |
| `monthly` | Start date → End date (one per month, based on nakshatra) |
| `yearly` | Start date → End date (one per year) |

### 4. UI: User Enters Payment
- `pooja_amount_receipt` = amount paid now
- Can be partial or full payment

### 5. API: POST /bookings

**Request Payload:**
```json
{
  "temple_id": 1,
  "pooja_id": 5,
  "deity_id": 2,
  "booking_date": "2026-03-30",
  "booking_end_date": "2026-06-30",
  "period": "monthly",
  "pooja_amount": 100,
  "pooja_amount_receipt": 200,
  "payment_mode": "cash",
  "devotees": [
    { "devotee_name": "Ram", "nakshatra": "Ashwini", "devotee_phone": "9876543210" }
  ]
}
```

---

## Backend Processing (store method)

```
┌──────────────────────────────────────────────────────────────┐
│                    STORE BOOKING FLOW                         │
├──────────────────────────────────────────────────────────────┤
│                                                               │
│  1. Validate Input                                            │
│     └── Check temple, pooja, deity exist                     │
│                                                               │
│  2. Create TemplePoojaBooking                                │
│     └── Generate booking_number: "BKG-XXXXXXXX"              │
│                                                               │
│  3. Resolve Devotees                                          │
│     ├── If devotee_id provided → fetch existing              │
│     └── If new devotee → create in Devotee table             │
│                                                               │
│  4. Calculate Pooja Dates (getValidDates)                    │
│     ├── once   → single date                                 │
│     ├── daily  → every day from start to end                 │
│     ├── monthly→ nakshatra-based OR next_pooja_perform_date  │
│     └── yearly → once per year                               │
│                                                               │
│  5. Create Trackings (TemplePoojaBookingTracking)            │
│     └── One row per (devotee × pooja_date)                   │
│         {                                                     │
│           booking_id, devotee_id, pooja_date,                │
│           paid_amount: 0, due_amount: pooja_amount,          │
│           payment_status: 'pending', booking_status: 'pending'│
│         }                                                     │
│                                                               │
│  6. Record Payment (PaymentDetail)                           │
│     └── If pooja_amount_receipt > 0                          │
│                                                               │
│  7. Apply Payment to Trackings                               │
│     └── Mark trackings as 'done' or 'partial'                │
│         (oldest dates first)                                 │
│                                                               │
└──────────────────────────────────────────────────────────────┘
```

---

## Database Tables Involved

```
┌─────────────────────┐     ┌─────────────────────────────┐
│  TemplePoojaBooking │     │  TemplePoojaBookingTracking │
├─────────────────────┤     ├─────────────────────────────┤
│ id                  │◄────│ booking_id                  │
│ temple_id           │     │ devotee_id                  │
│ pooja_id            │     │ pooja_date                  │
│ deity_id            │     │ paid_amount                 │
│ booking_number      │     │ due_amount                  │
│ booking_date        │     │ payment_status (pending/    │
│ booking_end_date    │     │                partial/done)│
│ period              │     │ booking_status (pending/    │
│ pooja_amount        │     │                completed)   │
│ payment_status      │     └─────────────────────────────┘
│ booking_status      │
└─────────────────────┘
         │
         │ 1:N
         ▼
┌─────────────────────┐
│    PaymentDetail    │
├─────────────────────┤
│ booking_id          │
│ payment             │
│ payment_mode        │
│ type (credit)       │
│ source (pooja)      │
└─────────────────────┘
```

---

## Table Relationships

### TemplePoojaBooking (Main Booking Record)

| Field | Type | Description |
|-------|------|-------------|
| id | int | Primary key |
| temple_id | int | FK to temples |
| pooja_id | int | FK to temple_poojas |
| deity_id | int | FK to temple_deities (optional) |
| member_id | int | FK to members (who created booking) |
| booking_number | string | Unique ID like "BKG-ABC12345" |
| booking_date | date | Start date |
| booking_end_date | date | End date (for recurring) |
| period | enum | once, daily, monthly, yearly |
| pooja_amount | decimal | Amount per pooja |
| payment_status | string | pending, partial, done |
| booking_status | string | pending, completed, cancelled |

### TemplePoojaBookingTracking (Individual Pooja Dates)

| Field | Type | Description |
|-------|------|-------------|
| id | int | Primary key |
| booking_id | int | FK to temple_pooja_bookings |
| devotee_id | int | FK to devotees (nullable for guest) |
| pooja_date | date | Specific date for this pooja |
| paid_amount | decimal | Amount paid for this date |
| due_amount | decimal | Amount remaining |
| payment_status | enum | pending, partial, done |
| booking_status | enum | pending, completed, cancelled |

---

## Example: Monthly Booking for 3 Months

### Input:
- **Pooja:** Ganapathi Homam (₹100)
- **Period:** monthly
- **Dates:** March 2026 → May 2026
- **Devotee:** Ram (Nakshatra: Ashwini)
- **Payment:** ₹200

### Result:

| Table | Data |
|-------|------|
| **TemplePoojaBooking** | 1 row: booking_number=BKG-ABC123, pooja_amount=100 |
| **TemplePoojaBookingTracking** | 3 rows (one per month): |
| | - March 15: paid=100, due=0, status=done |
| | - April 12: paid=100, due=0, status=done |
| | - May 10: paid=0, due=100, status=pending |
| **PaymentDetail** | 1 row: payment=200, type=credit |

---

## Key Concepts

### 1. Trackings = Individual Pooja Dates
Each tracking row represents ONE pooja performance for ONE devotee on ONE date.

### 2. Payment Applies Oldest First
When payment is received, it fills tracking rows chronologically (oldest dates get paid first).

### 3. Nakshatra-Based Date Calculation
For monthly bookings, the system finds the date when the devotee's nakshatra occurs that month using the Panchang table.

### 4. Partial Payment Supported
- A tracking can be `partial` with split amounts
- Example: ₹100 pooja, ₹50 paid → paid_amount=50, due_amount=50, status=partial

### 5. Devotee Resolution
- If `devotee_id` provided → use existing devotee
- If new devotee data provided → create new devotee record
- If no devotee → tracking has `devotee_id = NULL` (guest)

---

## API Endpoints

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | /api/bookings | List bookings (with date filter) |
| POST | /api/bookings | Create new booking |
| PUT | /api/bookings/{id} | Update booking |
| DELETE | /api/bookings/{id} | Soft delete booking |
| GET | /api/bookings/trashed | List deleted bookings |
| POST | /api/bookings/{id}/restore | Restore deleted booking |

---

## Files Reference

| File | Purpose |
|------|---------|
| `app/Http/Controllers/Api/TemplePoojaBookingController.php` | API controller |
| `app/Models/TemplePoojaBooking.php` | Booking model |
| `app/Models/TemplePoojaBookingTracking.php` | Tracking model |
| `app/Models/Devotee.php` | Devotee model |
| `app/Models/PaymentDetail.php` | Payment model |
| `app/Models/Panchang.php` | Nakshatra calendar data |

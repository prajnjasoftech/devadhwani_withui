# Devadhwani API Documentation

Base URL: `/api`

## Authentication

All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer <token>
```

### Send OTP
Send OTP to phone number via WhatsApp.

**Endpoint:** `POST /send-otp`
**Rate Limit:** 5 requests per minute

**Request Body:**
```json
{
  "phone": "+919876543210"
}
```

**Response:**
```json
{
  "message": "OTP sent successfully"
}
```

---

### Verify OTP & Register
Verify OTP and register a new temple.

**Endpoint:** `POST /verify-otp`
**Rate Limit:** 10 requests per minute

**Request Body:**
```json
{
  "phone": "+919876543210",
  "otp": "123456",
  "temple_name": "Sri Krishna Temple",
  "temple_address": "123 Temple Street, City",
  "temple_logo": "<file>" // optional, image file
}
```

**Response:**
```json
{
  "message": "Temple registered successfully",
  "temple": {
    "id": 1,
    "temple_name": "Sri Krishna Temple",
    "temple_address": "123 Temple Street, City",
    "phone": "+919876543210"
  },
  "token": "1|abc123...",
  "role": {
    "User": "4",
    "Role": "4",
    "Pooja": "4",
    "Settings": "4",
    "Events": "4",
    "Donations": "4",
    "Members": "4"
  }
}
```

---

### Login
Login with phone and OTP.

**Endpoint:** `POST /login`
**Rate Limit:** 10 requests per minute

**Request Body:**
```json
{
  "phone": "+919876543210",
  "otp": "123456"
}
```

**Response (Temple):**
```json
{
  "message": "Login successful",
  "temple": { ... },
  "token": "1|abc123...",
  "role": { ... }
}
```

**Response (Member):**
```json
{
  "message": "Member login successful",
  "login_as": "member",
  "member": { ... },
  "token": "1|abc123...",
  "role": { ... }
}
```

---

### Logout
Logout and invalidate all tokens.

**Endpoint:** `POST /logout`
**Auth Required:** Yes

**Response:**
```json
{
  "message": "Logged out"
}
```

---

## Temples

### List Temples
**Endpoint:** `GET /temples`
**Auth Required:** Yes

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| temple_id | int | Filter by temple ID |
| search | string | Search by name, phone, or database name |
| per_page | int | Items per page (default: 10) |

**Response:**
```json
{
  "status": true,
  "message": "Temple list fetched successfully",
  "data": {
    "current_page": 1,
    "data": [
      {
        "id": 1,
        "temple_name": "Sri Krishna Temple",
        "temple_address": "123 Temple Street",
        "phone": "+919876543210",
        "temple_logo": "temple_123.jpg",
        "temple_logo_base64": "..."
      }
    ],
    "per_page": 10,
    "total": 1
  }
}
```

---

### Update Temple
**Endpoint:** `POST /temples/{id}`
**Auth Required:** Yes

**Request Body (form-data):**
| Field | Type | Description |
|-------|------|-------------|
| temple_name | string | Temple name |
| temple_address | string | Temple address |
| phone | string | Phone number |
| temple_logo | file | Logo image (jpg, jpeg, png, webp) |

**Response:**
```json
{
  "status": true,
  "message": "Temple updated successfully",
  "data": { ... }
}
```

---

## Devotees

### List Devotees
**Endpoint:** `GET /devotees`
**Auth Required:** Yes

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| temple_id | int | Filter by temple ID |
| search | string | Search by name or phone |
| with_trashed | bool | Include soft-deleted records |
| per_page | int | Items per page (default: 10) |

**Response:**
```json
{
  "status": true,
  "message": "Devotee list fetched successfully.",
  "data": [
    {
      "id": 1,
      "temple_id": 1,
      "devotee_name": "Ravi Kumar",
      "devotee_phone": "+919876543210",
      "nakshatra": "Ashwini",
      "address": "123 Street"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 10,
    "total": 50,
    "last_page": 5
  }
}
```

---

### Create Devotee
**Endpoint:** `POST /devotees`
**Auth Required:** Yes

**Request Body:**
```json
{
  "temple_id": 1,
  "devotee_name": "Ravi Kumar",
  "devotee_phone": "+919876543210",
  "nakshatra": "Ashwini",
  "address": "123 Street"
}
```

**Response:**
```json
{
  "status": true,
  "message": "Devotee created successfully.",
  "data": { ... }
}
```

---

### Get Devotee
**Endpoint:** `GET /devotees/{id}`
**Auth Required:** Yes

**Response:**
```json
{
  "status": true,
  "data": {
    "id": 1,
    "temple_id": 1,
    "devotee_name": "Ravi Kumar",
    ...
  }
}
```

---

### Update Devotee
**Endpoint:** `PUT /devotees/{id}`
**Auth Required:** Yes

**Request Body:**
```json
{
  "devotee_name": "Updated Name",
  "nakshatra": "Rohini"
}
```

---

### Delete Devotee (Soft Delete)
**Endpoint:** `DELETE /devotees/{id}`
**Auth Required:** Yes

---

### Restore Devotee
**Endpoint:** `POST /devotees/{id}/restore`
**Auth Required:** Yes

---

### Force Delete Devotee
**Endpoint:** `DELETE /devotees/{id}/force`
**Auth Required:** Yes

---

## Temple Poojas

### List Poojas
**Endpoint:** `GET /temple-poojas`
**Auth Required:** Yes

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| temple_id | int | Filter by temple ID |
| member_id | int | Filter by member ID |
| search | string | Search by pooja name |
| with_trashed | bool | Include soft-deleted records |
| per_page | int | Items per page (default: 10) |

**Response:**
```json
{
  "status": true,
  "data": {
    "data": [
      {
        "id": 1,
        "temple_id": 1,
        "pooja_name": "Ganapathi Homam",
        "period": "monthly",
        "amount": "500.00",
        "details": "Monthly pooja",
        "devotees_required": 1,
        "next_pooja_perform_date": "2026-03-15"
      }
    ]
  },
  "meta": { ... }
}
```

---

### Create Pooja
**Endpoint:** `POST /temple-poojas`
**Auth Required:** Yes

**Request Body:**
```json
{
  "temple_id": 1,
  "pooja_name": "Ganapathi Homam",
  "period": "monthly",
  "amount": 500.00,
  "details": "Monthly pooja for Lord Ganesha",
  "devotees_required": 1,
  "next_pooja_perform_date": "2026-03-15"
}
```

**Period Options:** `once`, `daily`, `monthly`, `yearly`

---

### Update Pooja
**Endpoint:** `PUT /temple-poojas/{id}`
**Auth Required:** Yes

**Request Body:**
```json
{
  "pooja_name": "Updated Pooja Name",
  "amount": 600.00,
  "next_pooja_perform_date": "2026-03-28"
}
```

#### Automatic Tracking Update Behavior

When `next_pooja_perform_date` is updated, the system automatically updates all active booking tracking records:

1. **Finds the next pending tracking** - The earliest tracking record that is not yet completed
2. **Updates the pooja date** - Sets the tracking's `pooja_date` to the new `next_pooja_perform_date`
3. **Extends booking if needed** - If the new date exceeds the booking's `booking_end_date`, automatically extends it

**Example Scenario:**
- Pooja was performed on March 5th (tracking marked completed)
- Admin updates `next_pooja_perform_date` to March 28th
- The next pending tracking (originally April 15th) is updated to March 28th
- This allows flexibility when poojas cannot be performed on the scheduled date

---

### Delete Pooja (Soft Delete)
**Endpoint:** `DELETE /temple-poojas/{id}`
**Auth Required:** Yes

---

### Restore Pooja
**Endpoint:** `POST /temple-poojas/{id}/restore`
**Auth Required:** Yes

---

## Pooja Bookings

### List Bookings
**Endpoint:** `GET /bookings`
**Auth Required:** Yes

**Query Parameters:**
| Parameter | Type | Description |
|-----------|------|-------------|
| temple_id | int | Filter by temple ID |
| pooja_id | int | Filter by pooja ID |
| status | string | Filter by booking status |
| start_date | date | Start date filter |
| end_date | date | End date filter |
| per_page | int | Items per page |

---

### Create Booking
**Endpoint:** `POST /bookings`
**Auth Required:** Yes

**Request Body:**
```json
{
  "temple_id": 1,
  "pooja_id": 1,
  "member_id": null,
  "booking_date": "2025-02-01",
  "booking_end_date": "2025-12-31",
  "period": "monthly",
  "pooja_amount": 500.00,
  "pooja_amount_receipt": 1000.00,
  "payment_mode": "cash",
  "devotees": [
    {
      "devotee_id": 1
    },
    {
      "devotee_name": "New Devotee",
      "devotee_phone": "+919876543210",
      "nakshatra": "Ashwini"
    }
  ]
}
```

---

### Update Booking
**Endpoint:** `PUT /bookings/{id}`
**Auth Required:** Yes

---

### Delete Booking
**Endpoint:** `DELETE /bookings/{id}`
**Auth Required:** Yes

---

## Panchang

### Get Today's Panchang
**Endpoint:** `GET /panchang`
**Auth Required:** No

---

### Get Panchang by Date
**Endpoint:** `GET /panchang/listDate/{date}`
**Auth Required:** No

**Example:** `GET /panchang/listDate/2025-02-22`

---

### List Panchang Records
**Endpoint:** `GET /panchang/list`
**Auth Required:** No

---

### Get Yearly Panchang
**Endpoint:** `GET /panchangYear`
**Auth Required:** No

---

## Members

### List Members
**Endpoint:** `GET /members`
**Auth Required:** Yes

---

### Create Member
**Endpoint:** `POST /members`
**Auth Required:** Yes

---

### Update Member
**Endpoint:** `PUT /members/{id}`
**Auth Required:** Yes

---

### Delete Member
**Endpoint:** `DELETE /members/{id}`
**Auth Required:** Yes

---

## Roles

### List Roles
**Endpoint:** `GET /roles`
**Auth Required:** Yes

---

### Create Role
**Endpoint:** `POST /roles`
**Auth Required:** Yes

---

## Inventory Management

### Categories
- `GET /categories` - List categories
- `POST /categories` - Create category
- `GET /categories/{id}` - Get category
- `PUT /categories/{id}` - Update category
- `DELETE /categories/{id}` - Delete category

### Items
- `GET /items` - List items
- `POST /items` - Create item
- `GET /items/{id}` - Get item
- `PUT /items/{id}` - Update item
- `DELETE /items/{id}` - Delete item

### Suppliers
- `GET /suppliers` - List suppliers
- `POST /suppliers` - Create supplier
- `PUT /suppliers/{id}` - Update supplier
- `DELETE /suppliers/{id}` - Delete supplier

### Purchases
- `GET /purchases` - List purchases
- `POST /purchases` - Create purchase
- `PUT /purchases/{id}` - Update purchase
- `DELETE /purchases/{id}` - Delete purchase

### Usages
- `GET /usages` - List usages
- `POST /usages` - Create usage record
- `PUT /usages/{id}` - Update usage
- `DELETE /usages/{id}` - Delete usage

---

## Reports

### Transaction Summary
**Endpoint:** `GET /transaction-summary`
**Auth Required:** Yes

---

### Pending Pooja Summary
**Endpoint:** `GET /pending-pooja-summary`
**Auth Required:** Yes

---

## Error Responses

### Validation Error (422)
```json
{
  "status": false,
  "error": "The temple id field is required.",
  "errors": {
    "temple_id": ["The temple id field is required."]
  }
}
```

### Not Found (404)
```json
{
  "status": false,
  "error": "Resource not found"
}
```

### Unauthorized (401)
```json
{
  "message": "Unauthenticated. Please provide a valid token."
}
```

### Rate Limited (429)
```json
{
  "message": "Too Many Attempts."
}
```

### Server Error (500)
```json
{
  "status": false,
  "error": "Internal server error message"
}
```

---

## Nakshatra Reference

Valid nakshatra values for devotee records:
- Ashwini, Bharani, Krittika, Rohini, Mrigashira
- Ardra, Punarvasu, Pushya, Ashlesha, Magha
- Purva Phalguni, Uttara Phalguni, Hasta, Chitra
- Swati, Vishakha, Anuradha, Jyeshtha, Mula
- Purva Ashadha, Uttara Ashadha, Shravana, Dhanishta
- Shatabhisha, Purva Bhadrapada, Uttara Bhadrapada, Revati

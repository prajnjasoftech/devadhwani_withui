# Changelog

## [2026-03-30] - API & UI Updates

### Migration Fix
- **Fixed**: `devotees_temple_deleted_index` migration rollback error
  - Drop foreign key before dropping indexes to avoid MySQL constraint error
  - File: `database/migrations/2026_02_24_100001_add_indexes_to_devotees_table.php`

### Temple Logo Base64
- **Added**: Return `temple_logo_base64` in login/register API responses
  - Mobile app can now display temple logo without separate image fetch
  - Files:
    - `app/Models/Temple.php` - Fixed storage path in accessor
    - `app/Http/Controllers/Api/AuthController.php` - Added append for `temple_logo_base64`

### Item with Category Display
- **Added**: `item_with_category` accessor to Item model
  - Format: "Item Name - Category Name" (e.g., "Rice - Grocery")
  - File: `app/Models/Item.php`

### Purchase Module Updates

#### API Controllers
- `app/Http/Controllers/Api/PurchaseController.php`
  - Load `item.category` relationship in all queries
- `app/Http/Controllers/Web/PurchaseController.php`
  - Load `item.category` relationship for web UI

#### Web UI (Vue.js)
- `resources/js/Pages/Purchase/Index.vue`
  - Display `item_with_category` in purchase list
- `resources/js/Pages/Purchase/Create.vue`
  - Show "Item - Category (Unit)" in dropdown
- `resources/js/Pages/Purchase/Edit.vue`
  - Show "Item - Category (Unit)" in dropdown

---

## Mobile App Changes (Local - Not Committed)

### Models Updated
- `lib/modules/purchases/model/purchase_model.dart`
  - Added `itemWithCategory` field to Item class
  - Added `Category` class for nested category data
- `lib/modules/items/model/item_model.dart`
  - Added `itemWithCategory` field

### Views Updated
- `lib/modules/purchases/view/purchases_screen.dart`
  - Display `itemWithCategory` in purchase list
- `lib/modules/purchases/view/purchase_detail_screen.dart`
  - Display `itemWithCategory` in detail view
- `lib/modules/purchases/view/add_edit_purchase_screen.dart`
  - Show "Item - Category" in item dropdown

### Required Action
Run build_runner to regenerate freezed files:
```bash
cd C:\wamp64\www\devadhwani\devadhwani-main
flutter pub run build_runner build --delete-conflicting-outputs
```

---

## Database

### Sample Poojas Inserted
- 50 poojas inserted into `temple_poojas` table for temple_id=1
- Source: `C:\wamp64\www\kshethram\mundolikshethram.in\assets\vazhipadu.txt`

---

## Git Commits

| Commit | Description |
|--------|-------------|
| `12e6520` | Fix migration rollback for devotees table indexes |
| `6521408` | Add temple logo base64 and item category display |
| `b82f7e0` | Show item with category in web UI purchases |

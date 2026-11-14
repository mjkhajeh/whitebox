# WC Utility

Provides WooCommerce helper methods for account endpoints, cart counts, coupons, Iranian currency conversion, gallery metadata, and product/order lookups.

---

## Methods

### get_account_endpoint
Determines the active WooCommerce account endpoint slug from the current query vars or a supplied item list.

**Signature:**
```php
public static function get_account_endpoint($items = [])
```

**Parameters:**
- `$items` (array): Optional custom account menu items. Keys are endpoint slugs.

**Returns:**
- (string) Active endpoint slug. Falls back to `'dashboard'`.

**Notes:**
- Automatically appends `'view-order'` to the candidate list before matching.
- Uses `wc_get_account_menu_items()` when no custom list is provided.

---

### get_cart_count
Gets the current WooCommerce cart item count.

**Signature:**
```php
public static function get_cart_count(): int
```

**Returns:**
- (int) Number of cart items, or `0` if WooCommerce/cart is unavailable.

**Notes:**
- Uses a static local cache for the request lifetime.

---

### get_active_coupons_for_user
Returns active WooCommerce coupons that are valid for the currently logged-in user.

**Signature:**
```php
public static function get_active_coupons_for_user()
```

**Returns:**
- (array) Array of active `WC_Coupon` objects.

**Validation checks:**
- User must be logged in.
- Coupon expiry date must be valid or missing.
- Coupon email restrictions must include the current user email.
- Coupon usage limit must not be reached.

---

### ir_currencies
Returns the Iranian currency conversion map to the rial.

**Signature:**
```php
public static function ir_currencies()
```

**Returns:**
- (array) Associative array with:
  - `IRR => 1`
  - `IRT => 0.1`
  - `IRHR => 0.001`
  - `IRHT => 0.0001`

---

### ir_currencies_label
Returns translated labels for the Iranian currency codes.

**Signature:**
```php
public static function ir_currencies_label()
```

**Returns:**
- (array) Associative array of currency codes to localized strings.

---

### get_gallery_ids
Collects all relevant product image attachment IDs, including the featured image and variation images.

**Signature:**
```php
public static function get_gallery_ids($product)
```

**Parameters:**
- `$product` (WC_Product): WooCommerce product instance.

**Returns:**
- (array) Unique attachment IDs for the product and any variable-product variations.

**Notes:**
- Handles both standard and variable products.
- Uses `ProductType::VARIABLE` when available and falls back to the older `WC_Product_Factory` style checks.

---

### get_product_type_by_id
Looks up the product type for a product ID with request-scoped caching.

**Signature:**
```php
public static function get_product_type_by_id($product_id)
```

**Parameters:**
- `$product_id` (int): Product ID.

**Returns:**
- (string) Product type such as `'simple'`, `'variable'`, `'grouped'`, or an empty string if no product is found.

**Notes:**
- Caches results in `self::$products_type_cache`.
- Attempts `WC_Product_Factory::get_product_type()` first, then falls back to `wc_get_product()`.

---

### get_order_ids_by_product_ids
Finds WooCommerce order IDs that contain any of the provided product or variation IDs.

**Signature:**
```php
public function get_order_ids_by_product_ids(array $product_ids): array
```

**Parameters:**
- `$product_ids` (array): List of product or variation IDs.

**Returns:**
- (array) Matching order IDs as integers, or an empty array when input is empty or no matches are found.

**Behavior:**
- Normalizes IDs with `absint()` and removes duplicates.
- Searches both `'_product_id'` and `'_variation_id'` WooCommerce order item meta keys.
- Works with both legacy order storage and HPOS by querying the WooCommerce order item tables directly.

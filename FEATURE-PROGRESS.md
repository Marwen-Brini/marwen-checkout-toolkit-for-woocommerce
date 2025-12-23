# Checkout Toolkit Feature Expansion Progress

## Overview

Comprehensive expansion of the Checkout Toolkit for WooCommerce plugin with 10 new features organized into 4 phases. This document tracks implementation progress, technical details, and serves as a reference for continuing development.

**Total Features:** 10
**Completed:** 2
**Progress:** 20%

---

## Architecture Decisions

### Settings Storage
Each feature uses its own WordPress option for clean separation:
- Pattern: `checkout_toolkit_{feature}_settings`
- All options registered via `Settings::register_settings()`
- Sanitization callbacks handle null input (for tab-based saving)

### Order Meta Storage
Custom order data stored with `_wct_` prefix:
- Pattern: `_wct_{field_name}`
- Compatible with WooCommerce HPOS (High-Performance Order Storage)
- Accessed via `$order->get_meta()` / `$order->update_meta_data()`

### Dual Checkout Support
All features must work in both checkout types:
- **Classic Checkout:** PHP hooks + jQuery (via `public/js/checkout.js`)
- **Blocks Checkout:** React components + Store API (via `public/js/blocks-checkout.js`)

### Admin Interface
- Separate tabs per feature in WooCommerce > Checkout Toolkit
- Settings page: `admin/views/settings-page.php`
- Individual tab views: `admin/views/settings-{feature}.php`

---

## Phase 1: Simple Standalone Features

### Feature 1: Order Notes Placeholder Customization

**Status:** ✅ Completed

**Description:**
Customize the default WooCommerce order notes field (order_comments) placeholder text and label. This allows store owners to provide context-specific instructions to customers.

**Settings:**
| Setting | Type | Default |
|---------|------|---------|
| `enabled` | boolean | `false` |
| `custom_label` | string | `''` (uses WooCommerce default) |
| `custom_placeholder` | string | `''` (uses WooCommerce default) |

**Implementation Details:**

1. **Class:** `src/WooCheckoutToolkit/Communication/OrderNotesCustomizer.php`
   - Hooks into `woocommerce_checkout_fields` filter at priority 20
   - Modifies `$fields['order']['order_comments']` array
   - Only modifies if feature is enabled and values are set

2. **Admin View:** `admin/views/settings-order-notes.php`
   - Enable/disable toggle
   - Custom label input
   - Custom placeholder textarea
   - Live preview section showing how field will appear

3. **Settings Registration:** `src/WooCheckoutToolkit/Admin/Settings.php`
   - Option name: `checkout_toolkit_order_notes_settings`
   - Sanitization: `sanitize_order_notes_settings()`
   - Default getter: `get_default_order_notes_settings()`

4. **Initialization:** `src/WooCheckoutToolkit/Main.php`
   - Property: `private ?OrderNotesCustomizer $order_notes_customizer`
   - Initialized in `init_frontend()` method

**Files Created:**
```
src/WooCheckoutToolkit/Communication/OrderNotesCustomizer.php
admin/views/settings-order-notes.php
```

**Files Modified:**
```
src/WooCheckoutToolkit/Main.php
  - Added: use WooCheckoutToolkit\Communication\OrderNotesCustomizer;
  - Added: private ?OrderNotesCustomizer $order_notes_customizer = null;
  - Added: Initialization in init_frontend()

src/WooCheckoutToolkit/Admin/Settings.php
  - Added: register_setting() for order_notes_settings
  - Added: get_default_order_notes_settings()
  - Added: sanitize_order_notes_settings()

admin/views/settings-page.php
  - Added: $order_notes_settings variable
  - Added: Order Notes tab in navigation
  - Added: Include for settings-order-notes.php
```

**Usage Example:**
```php
// Settings are automatically applied via filter
// No action needed by store owner other than enabling in admin

// Developers can access settings:
$settings = get_option('checkout_toolkit_order_notes_settings', []);
```

---

### Feature 2: Second Custom Field Support

**Status:** ✅ Completed

**Description:**
Adds a second configurable custom field to checkout, allowing stores to collect two pieces of custom information from customers. Each field has independent settings for label, placeholder, type, position, and display options.

**Settings (Field 2):**
| Setting | Type | Default |
|---------|------|---------|
| `enabled` | boolean | `false` |
| `required` | boolean | `false` |
| `field_type` | string | `'text'` |
| `field_label` | string | `'Additional Information'` |
| `field_placeholder` | string | `''` |
| `field_position` | string | `'woocommerce_after_order_notes'` |
| `max_length` | integer | `200` |
| `show_in_emails` | boolean | `true` |
| `show_in_admin` | boolean | `true` |

**Implementation Details:**

1. **Class:** `src/WooCheckoutToolkit/Fields/OrderFields2.php`
   - Mirrors structure of existing `OrderFields.php`
   - Registers at configured position with priority 15 (after field 1)
   - Handles validation, sanitization, and saving
   - Meta key: `_wct_custom_field_2`
   - Skips rendering for blocks checkout (handled by JS)

2. **Admin View:** `admin/views/settings-fields.php` (Redesigned)
   - Two distinct sections: "Custom Field 1" and "Custom Field 2"
   - Visual separator between sections
   - Each field has full configuration options
   - Consistent UI with existing field settings

3. **Settings Registration:** `src/WooCheckoutToolkit/Admin/Settings.php`
   - Option name: `checkout_toolkit_field_2_settings`
   - Sanitization: `sanitize_field_2_settings()`
   - Default getter: `get_default_field_2_settings()`

4. **Main Class Updates:** `src/WooCheckoutToolkit/Main.php`
   - Added property and initialization for `OrderFields2`
   - Added `get_default_field_2_settings()` method
   - Added `get_field_2_settings()` method
   - Updated `get_blocks_script_data()` to include `customField2`

5. **Blocks Integration:** `src/WooCheckoutToolkit/Blocks/BlocksIntegration.php`
   - Added `custom_field_2` to Store API schema
   - Added `customField2` to script data
   - Added save logic in `save_order_data()`
   - Added `get_field_2_settings()` helper method

6. **Frontend JS:** `public/js/blocks-checkout.js`
   - Added `CustomField2Component` React component
   - Updated `CheckoutToolkitFields` to render field 2
   - Added initialization for `custom_field_2` extension data

7. **Display Components:**
   - `OrderDisplay.php` - Shows field 2 in admin order view and meta box
   - `EmailDisplay.php` - Includes field 2 in order emails (HTML and plain text)
   - `AccountDisplay.php` - Shows field 2 in My Account order details

**Files Created:**
```
src/WooCheckoutToolkit/Fields/OrderFields2.php
```

**Files Modified:**
```
src/WooCheckoutToolkit/Main.php
  - Added: use WooCheckoutToolkit\Fields\OrderFields2;
  - Added: private ?OrderFields2 $order_fields_2 = null;
  - Added: Initialization in init_frontend()
  - Added: get_default_field_2_settings()
  - Added: get_field_2_settings()
  - Updated: get_blocks_script_data() with customField2

src/WooCheckoutToolkit/Admin/Settings.php
  - Added: register_setting() for field_2_settings
  - Added: get_default_field_2_settings()
  - Added: sanitize_field_2_settings()

admin/views/settings-page.php
  - Added: $field_2_settings variable

admin/views/settings-fields.php
  - Complete rewrite with two field sections

src/WooCheckoutToolkit/Blocks/BlocksIntegration.php
  - Added: use WooCheckoutToolkit\Admin\Settings;
  - Added: custom_field_2 to schema
  - Added: customField2 to script data
  - Added: Save logic for custom_field_2
  - Added: get_field_2_settings() method

public/js/blocks-checkout.js
  - Added: customField2 to destructured settings
  - Added: CustomField2Component
  - Updated: CheckoutToolkitFields to render field 2
  - Updated: Extension data initialization

src/WooCheckoutToolkit/Display/OrderDisplay.php
  - Added: $field_2_settings and $custom_field_2 variables
  - Added: Display logic for field 2 in display_in_admin()
  - Added: Display logic for field 2 in render_meta_box()

src/WooCheckoutToolkit/Display/EmailDisplay.php
  - Complete rewrite to support field 2
  - Added: $field_2_settings, $custom_field_2, $show_field_2
  - Updated: render_html() and render_plain_text() signatures

src/WooCheckoutToolkit/Display/AccountDisplay.php
  - Added: $field_2_settings and $custom_field_2 variables
  - Added: Display logic for field 2
```

**Data Flow (Blocks Checkout):**
```
1. User fills field on checkout
2. CustomField2Component calls setExtensionData('checkout-toolkit', 'custom_field_2', value)
3. WooCommerce dispatches to store: __internalSetExtensionData()
4. On submit, Store API receives extensions['checkout-toolkit']['custom_field_2']
5. BlocksIntegration::save_order_data() saves to _wct_custom_field_2
6. Display components read via $order->get_meta('_wct_custom_field_2')
```

**Data Flow (Classic Checkout):**
```
1. User fills field on checkout
2. Form submits with checkout_toolkit_custom_field_2 in POST
3. OrderFields2::validate_field() validates input
4. OrderFields2::save_field() saves to _wct_custom_field_2
5. Display components read via $order->get_meta('_wct_custom_field_2')
```

**Usage Example:**
```php
// Get field 2 value from order
$order = wc_get_order($order_id);
$field_2_value = $order->get_meta('_wct_custom_field_2');

// Get field 2 settings
$settings = Main::get_instance()->get_field_2_settings();

// Filter field 2 value before save
add_filter('checkout_toolkit_sanitize_field_2_value', function($value) {
    return sanitize_text_field($value);
});

// Action after field 2 saved
add_action('checkout_toolkit_custom_field_2_saved', function($order_id, $value) {
    // Custom logic
}, 10, 2);
```

---

### Feature 3: Field Type Options

**Status:** ⏳ Pending

**Description:**
Extend custom fields to support multiple input types: text, textarea, checkbox, and select (dropdown). This provides more flexibility in the type of information collected.

**Planned Settings Addition:**
| Setting | Type | Options |
|---------|------|---------|
| `field_type` | string | `'text'`, `'textarea'`, `'checkbox'`, `'select'` |
| `select_options` | array | For select type: `[['value' => '', 'label' => '']]` |
| `checkbox_label` | string | Label shown next to checkbox |

**Planned Implementation:**

1. **Update Settings:**
   - Add radio buttons for field type selection
   - Add conditional UI for select options (repeater field)
   - Add checkbox label input

2. **Update OrderFields.php & OrderFields2.php:**
   - Handle different field types in render methods
   - Adjust validation per type (checkbox = boolean, select = in_array)
   - Adjust sanitization per type

3. **Update blocks-checkout.js:**
   - Add checkbox component rendering
   - Add select/dropdown component rendering
   - Handle different value types

4. **Update Display Components:**
   - Format checkbox as Yes/No
   - Format select as selected label

**Files to Modify:**
```
admin/views/settings-fields.php
src/WooCheckoutToolkit/Fields/OrderFields.php
src/WooCheckoutToolkit/Fields/OrderFields2.php
src/WooCheckoutToolkit/Blocks/BlocksIntegration.php
public/js/blocks-checkout.js
src/WooCheckoutToolkit/Display/OrderDisplay.php
src/WooCheckoutToolkit/Display/EmailDisplay.php
src/WooCheckoutToolkit/Display/AccountDisplay.php
```

---

## Phase 2: Delivery Method Core

### Feature 4: Pickup vs Delivery Toggle

**Status:** ⏳ Pending

**Description:**
Allow customers to choose between pickup and delivery at checkout. This choice affects which other fields are shown (delivery shows date/time/instructions, pickup shows location selector).

**Planned Settings:**
| Setting | Type | Default |
|---------|------|---------|
| `enabled` | boolean | `false` |
| `default_method` | string | `'delivery'` |
| `delivery_label` | string | `'Delivery'` |
| `pickup_label` | string | `'Pickup'` |
| `show_as` | string | `'toggle'` or `'radio'` |

**Planned Meta Key:** `_wct_delivery_method` (values: `'delivery'` or `'pickup'`)

**Planned Files:**
```
src/WooCheckoutToolkit/Delivery/DeliveryMethod.php
admin/views/settings-delivery-method.php
templates/checkout/delivery-method-toggle.php
```

**Frontend Behavior:**
- When PICKUP selected: Hide delivery date, time window, instructions; Show location selector
- When DELIVERY selected: Show delivery date, time window, instructions; Hide location selector

---

### Feature 5: Delivery Instructions Field

**Status:** ⏳ Pending

**Description:**
Add a dedicated field for delivery instructions such as "Leave at door", "Ring doorbell", "Call on arrival", etc. Can include preset options or free text.

**Planned Settings:**
| Setting | Type | Default |
|---------|------|---------|
| `enabled` | boolean | `false` |
| `required` | boolean | `false` |
| `field_label` | string | `'Delivery Instructions'` |
| `field_type` | string | `'textarea'` or `'select'` or `'both'` |
| `preset_options` | array | Common delivery instructions |
| `allow_custom` | boolean | `true` |
| `max_length` | integer | `500` |

**Planned Meta Key:** `_wct_delivery_instructions`

**Planned Files:**
```
src/WooCheckoutToolkit/Delivery/DeliveryInstructions.php
admin/views/settings-delivery-instructions.php
```

---

### Feature 6: Time Window Selection

**Status:** ⏳ Pending

**Description:**
Allow customers to select a preferred delivery time window (Morning, Afternoon, Evening) or custom time slots defined by the store.

**Planned Settings:**
| Setting | Type | Default |
|---------|------|---------|
| `enabled` | boolean | `false` |
| `required` | boolean | `false` |
| `field_label` | string | `'Preferred Time'` |
| `time_slots` | array | `[['id' => 'morning', 'label' => 'Morning (9am-12pm)'], ...]` |
| `show_only_with_delivery` | boolean | `true` |

**Planned Meta Key:** `_wct_time_window`

**Planned Files:**
```
src/WooCheckoutToolkit/Delivery/TimeWindow.php
admin/views/settings-time-windows.php
```

---

## Phase 3: Dependent Features

### Feature 7: Store Location Selector

**Status:** ⏳ Pending

**Description:**
For pickup orders, allow customers to select which store location to pick up from. Free version limited to 3 locations.

**Planned Settings:**
| Setting | Type | Default |
|---------|------|---------|
| `enabled` | boolean | `false` |
| `required` | boolean | `true` (when pickup selected) |
| `field_label` | string | `'Pickup Location'` |

**Planned Locations Array:** `checkout_toolkit_store_locations`
```php
[
    [
        'id' => 'loc_1',
        'name' => 'Downtown Store',
        'address' => '123 Main St',
        'hours' => 'Mon-Fri 9am-6pm',
        'enabled' => true
    ],
    // ... max 3 in free version
]
```

**Planned Meta Key:** `_wct_pickup_location`

**Planned Files:**
```
src/WooCheckoutToolkit/Pickup/LocationManager.php  // CRUD for locations
src/WooCheckoutToolkit/Pickup/LocationSelector.php // Frontend selector
admin/views/settings-locations.php
```

**Constraint:** `const MAX_FREE_LOCATIONS = 3;`

---

### Feature 8: Estimated Delivery Display

**Status:** ⏳ Pending

**Description:**
Calculate and display estimated delivery date on checkout based on lead time, disabled days, and selected delivery date.

**Planned Settings:**
| Setting | Type | Default |
|---------|------|---------|
| `enabled` | boolean | `false` |
| `display_format` | string | `'Estimated delivery: {date}'` |
| `calculation_method` | string | `'from_order'` or `'from_selected'` |
| `additional_days` | integer | `0` |

**Planned Files:**
```
src/WooCheckoutToolkit/Delivery/EstimatedDelivery.php
admin/views/settings-estimated-delivery.php
```

**Display Location:** Checkout page only (per user requirement)

---

## Phase 4: Advanced Features

### Feature 9: Product/Category Field Visibility

**Status:** ⏳ Pending

**Description:**
Show custom fields only when specific products or product categories are in the cart. Useful for conditional information collection.

**Planned Settings Addition to Field Settings:**
| Setting | Type | Default |
|---------|------|---------|
| `visibility_type` | string | `'all'`, `'products'`, `'categories'` |
| `visibility_products` | array | Product IDs |
| `visibility_categories` | array | Category IDs/slugs |
| `visibility_mode` | string | `'show'` or `'hide'` |

**Planned Files:**
```
src/WooCheckoutToolkit/Fields/FieldVisibility.php
```

**Logic:**
```php
// In cart check
foreach (WC()->cart->get_cart() as $item) {
    $product_id = $item['product_id'];
    $categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'ids']);

    if ($visibility_type === 'products' && in_array($product_id, $visibility_products)) {
        return true;
    }
    if ($visibility_type === 'categories' && array_intersect($categories, $visibility_categories)) {
        return true;
    }
}
```

---

### Feature 10: Gift Message Option

**Status:** ⏳ Pending

**Description:**
Add a "This is a gift" checkbox with optional gift message field. When checked, order can be marked for gift wrapping/special handling.

**Planned Settings:**
| Setting | Type | Default |
|---------|------|---------|
| `enabled` | boolean | `false` |
| `checkbox_label` | string | `'This is a gift'` |
| `show_message_field` | boolean | `true` |
| `message_label` | string | `'Gift Message'` |
| `message_placeholder` | string | `'Enter your gift message...'` |
| `message_max_length` | integer | `500` |
| `show_in_emails` | boolean | `true` |
| `show_in_admin` | boolean | `true` |

**Planned Meta Keys:**
- `_wct_is_gift` (boolean)
- `_wct_gift_message` (string)

**Planned Files:**
```
src/WooCheckoutToolkit/Communication/GiftOptions.php
admin/views/settings-gift-options.php
templates/checkout/gift-options.php
```

---

## Summary Table

| # | Phase | Feature | Status | Priority |
|---|-------|---------|--------|----------|
| 1 | 1 | Order notes placeholder | ✅ Completed | Low |
| 2 | 1 | Second custom field | ✅ Completed | Medium |
| 3 | 1 | Field type options | ⏳ Pending | Medium |
| 4 | 2 | Pickup vs Delivery toggle | ⏳ Pending | High |
| 5 | 2 | Delivery instructions | ⏳ Pending | Medium |
| 6 | 2 | Time window selection | ⏳ Pending | Medium |
| 7 | 3 | Store location selector | ⏳ Pending | High |
| 8 | 3 | Estimated delivery display | ⏳ Pending | Low |
| 9 | 4 | Product/category visibility | ⏳ Pending | Medium |
| 10 | 4 | Gift message option | ⏳ Pending | Low |

---

## Technical Reference

### Available Hook Positions
```php
'woocommerce_before_checkout_billing_form'
'woocommerce_after_checkout_billing_form'
'woocommerce_before_checkout_shipping_form'
'woocommerce_after_checkout_shipping_form'
'woocommerce_before_order_notes'
'woocommerce_after_order_notes'
'woocommerce_review_order_before_cart_contents'
'woocommerce_review_order_after_cart_contents'
'woocommerce_review_order_before_shipping'
'woocommerce_review_order_after_shipping'
'woocommerce_review_order_before_order_total'
'woocommerce_review_order_before_submit'
```

### Store API Extension Pattern
```php
// Schema registration
woocommerce_store_api_register_endpoint_data([
    'endpoint' => 'checkout',
    'namespace' => 'checkout-toolkit',
    'schema_callback' => [$this, 'get_store_api_schema'],
    'schema_type' => ARRAY_A,
]);

// Schema definition
'field_name' => [
    'description' => __('Description', 'checkout-toolkit-for-woo'),
    'type' => ['string', 'null'],  // Allow null for optional
    'context' => ['view', 'edit'],
    'default' => '',
]

// Save handler
add_action('woocommerce_store_api_checkout_update_order_from_request', [$this, 'save_order_data'], 10, 2);
```

### Blocks JS Component Pattern
```javascript
const MyFieldComponent = ({ cart, extensions, setExtensionData }) => {
    const [value, setValue] = useState('');

    const handleChange = (e) => {
        const newValue = e.target.value;
        setValue(newValue);
        setExtensionData('checkout-toolkit', 'field_key', newValue);
    };

    if (!settings.myField?.enabled) return null;

    return el('div', { className: 'wc-block-components-checkout-step' },
        // ... field markup
    );
};
```

---

## Changelog

### 2024-12-23
- Completed Feature 1: Order Notes Placeholder Customization
- Completed Feature 2: Second Custom Field Support
- Created FEATURE-PROGRESS.md for tracking

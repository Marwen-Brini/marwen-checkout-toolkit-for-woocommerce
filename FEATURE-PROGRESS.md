# Checkout Toolkit Feature Expansion Progress

## Overview

Comprehensive expansion of the Checkout Toolkit for WooCommerce plugin with 10 new features organized into 4 phases. This document tracks implementation progress, technical details, and serves as a reference for continuing development.

**Total Features:** 10
**Completed:** 4
**Progress:** 40%

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

**Status:** ✅ Completed

**Description:**
Extend custom fields to support multiple input types: text, textarea, checkbox, and select (dropdown). This provides more flexibility in the type of information collected.

**Settings Addition:**
| Setting | Type | Options |
|---------|------|---------|
| `field_type` | string | `'text'`, `'textarea'`, `'checkbox'`, `'select'` |
| `select_options` | array | For select type: `[['value' => '', 'label' => '']]` |
| `checkbox_label` | string | Label shown next to checkbox |

**Implementation Details:**

1. **Settings Updates:** `admin/views/settings-fields.php`
   - Radio buttons for field type selection (text, textarea, checkbox, select)
   - Conditional UI showing checkbox label input when checkbox selected
   - JavaScript-powered repeater for select options (add/remove options dynamically)
   - Real-time UI updates when field type changes

2. **OrderFields.php & OrderFields2.php Updates:**
   - Added `get_select_options()` method for parsing select options
   - Updated `render_custom_field()` with switch statement for different types
   - Checkbox renders as labeled checkbox input
   - Select renders as dropdown with configured options
   - Validation adjusted per type (checkbox accepts '1' or empty, select validates against options)
   - Added `get_posted_value()` method to centralize $_POST access

3. **Blocks Integration:**
   - `BlocksIntegration.php` passes `checkboxLabel` and `selectOptions` to frontend
   - Save logic handles checkbox type (stores '1' or '0')
   - Select options validated server-side

4. **blocks-checkout.js Updates:**
   - Added checkbox rendering with label in both `CustomFieldComponent` and `CustomField2Component`
   - Added select/dropdown rendering with placeholder option
   - Handle different value types (checkbox = '1'/'' toggle)

5. **Display Components Updates:**
   - `OrderDisplay.php`: Added `format_field_value()` method
   - `EmailDisplay.php`: Added `format_field_value()` method
   - Checkbox displays as "Yes" / "No"
   - Select displays the option label instead of value

**Files Modified:**
```
admin/views/settings-fields.php
  - Complete rewrite with field type selector
  - Added checkbox label input
  - Added select options repeater with JavaScript

src/WooCheckoutToolkit/Fields/OrderFields.php
  - Added: get_select_options() method
  - Added: get_posted_value() method
  - Updated: render_custom_field() with switch for types
  - Updated: validate_field() for checkbox/select validation
  - Updated: save_field() for checkbox handling

src/WooCheckoutToolkit/Fields/OrderFields2.php
  - Same updates as OrderFields.php

src/WooCheckoutToolkit/Admin/Settings.php
  - Added: sanitize_select_options() method
  - Updated: sanitize_field_settings() for checkbox_label and select_options
  - Updated: sanitize_field_2_settings() similarly

src/WooCheckoutToolkit/Blocks/BlocksIntegration.php
  - Added: checkboxLabel and selectOptions to script data
  - Updated: save_order_data() for checkbox type handling

public/js/blocks-checkout.js
  - Added: checkbox rendering in renderField()
  - Added: select rendering in renderField()
  - Updated: handleChange for checkbox toggle

src/WooCheckoutToolkit/Display/OrderDisplay.php
  - Added: format_field_value() method for Yes/No and label display

src/WooCheckoutToolkit/Display/EmailDisplay.php
  - Added: format_field_value() method for Yes/No and label display

src/WooCheckoutToolkit/Main.php
  - Added: checkbox_label and select_options to default field settings
```

**Usage Example:**
```php
// Checkbox field - stored value is '1' or '0'
$is_checked = $order->get_meta('_wct_custom_field') === '1';

// Select field - stored value is the option value
$selected_option = $order->get_meta('_wct_custom_field');
$settings = Main::get_instance()->get_field_settings();
foreach ($settings['select_options'] as $option) {
    if ($option['value'] === $selected_option) {
        $label = $option['label'];
        break;
    }
}
```

---

## Phase 2: Delivery Method Core

### Feature 4: Pickup vs Delivery Toggle

**Status:** ✅ Completed

**Description:**
Allow customers to choose between pickup and delivery at checkout. This choice affects which other fields are shown (delivery shows date/time/instructions, pickup shows location selector).

**Settings:**
| Setting | Type | Default |
|---------|------|---------|
| `enabled` | boolean | `false` |
| `default_method` | string | `'delivery'` |
| `field_label` | string | `'Fulfillment Method'` |
| `delivery_label` | string | `'Delivery'` |
| `pickup_label` | string | `'Pickup'` |
| `show_as` | string | `'toggle'` or `'radio'` |
| `show_in_admin` | boolean | `true` |
| `show_in_emails` | boolean | `true` |

**Meta Key:** `_wct_delivery_method` (values: `'delivery'` or `'pickup'`)

**Implementation Details:**

1. **Class:** `src/WooCheckoutToolkit/Delivery/DeliveryMethod.php`
   - Renders toggle or radio buttons on checkout (classic checkout)
   - Hooks into `woocommerce_before_order_notes` at priority 5
   - Validates that a valid method is selected
   - Saves to `_wct_delivery_method` order meta
   - Inline CSS for toggle button styling
   - jQuery for toggle button active state management
   - Fires `wct_delivery_method_changed` event for other components

2. **Admin View:** `admin/views/settings-delivery-method.php`
   - Enable/disable toggle
   - Default method selection (radio)
   - Field label, delivery label, pickup label inputs
   - Display style selection (toggle buttons vs radio)
   - Show in admin/emails checkboxes
   - Live preview section with working toggle/radio demo
   - JavaScript for real-time preview updates

3. **Settings Registration:** `src/WooCheckoutToolkit/Admin/Settings.php`
   - Option name: `checkout_toolkit_delivery_method_settings`
   - Sanitization: `sanitize_delivery_method_settings()`
   - Default getter: `get_default_delivery_method_settings()`

4. **Settings Page:** `admin/views/settings-page.php`
   - Added "Pickup/Delivery" as the first tab (now default tab)
   - Default tab changed from 'delivery' to 'delivery-method'

5. **Blocks Integration:** `src/WooCheckoutToolkit/Blocks/BlocksIntegration.php`
   - Added `delivery_method` to Store API schema
   - Added `deliveryMethod` settings to `get_script_data()`
   - Added `get_delivery_method_settings()` helper method
   - Saves delivery method in `save_order_data()`

6. **Frontend JS:** `public/js/blocks-checkout.js`
   - Added `DeliveryMethodComponent` React component
   - Renders toggle buttons or radio buttons based on settings
   - Dispatches extension data on change
   - Updated `CheckoutToolkitFields` to include delivery method

7. **Display Components:**
   - `OrderDisplay.php`: Shows delivery method in admin order view and meta box
   - `EmailDisplay.php`: Includes delivery method in order emails (HTML and plain text)
   - Both display the configured label (Delivery/Pickup) instead of raw value

8. **Main Class:** `src/WooCheckoutToolkit/Main.php`
   - Added `get_default_delivery_method_settings()` method
   - Added `get_delivery_method_settings()` method
   - Updated `get_blocks_script_data()` to include deliveryMethod
   - Initializes DeliveryMethod in `init_frontend()`

**Files Created:**
```
src/WooCheckoutToolkit/Delivery/DeliveryMethod.php
admin/views/settings-delivery-method.php
```

**Files Modified:**
```
src/WooCheckoutToolkit/Main.php
  - Added: use WooCheckoutToolkit\Delivery\DeliveryMethod;
  - Added: private ?DeliveryMethod $delivery_method = null;
  - Added: Initialization in init_frontend()
  - Added: get_default_delivery_method_settings()
  - Added: get_delivery_method_settings()
  - Updated: get_blocks_script_data() with deliveryMethod

src/WooCheckoutToolkit/Admin/Settings.php
  - Added: register_setting() for delivery_method_settings
  - Added: get_default_delivery_method_settings()
  - Added: sanitize_delivery_method_settings()
  - Updated: default tab to 'delivery-method'

admin/views/settings-page.php
  - Added: $delivery_method_settings variable
  - Added: Pickup/Delivery tab (first position)
  - Added: Include for settings-delivery-method.php

src/WooCheckoutToolkit/Blocks/BlocksIntegration.php
  - Added: delivery_method to Store API schema
  - Added: deliveryMethod to script data
  - Added: get_delivery_method_settings() helper
  - Updated: save_order_data() for delivery_method

public/js/blocks-checkout.js
  - Added: deliveryMethod to destructured settings
  - Added: DeliveryMethodComponent with toggle/radio rendering
  - Updated: CheckoutToolkitFields to render delivery method
  - Updated: Extension data initialization

src/WooCheckoutToolkit/Display/OrderDisplay.php
  - Added: $delivery_method_settings and $delivery_method variables
  - Added: Display logic for delivery method in display_in_admin()
  - Added: Display logic for delivery method in render_meta_box()

src/WooCheckoutToolkit/Display/EmailDisplay.php
  - Added: $delivery_method_settings and $delivery_method variables
  - Updated: Method signatures for render_html() and render_plain_text()
  - Added: Display logic for delivery method
```

**Frontend Behavior:**
- When PICKUP selected: Future features will hide delivery date, time window, instructions; Show location selector
- When DELIVERY selected: Future features will show delivery date, time window, instructions; Hide location selector
- Event `wct_delivery_method_changed` fired on change for other components to react

**Usage Example:**
```php
// Get delivery method from order
$order = wc_get_order($order_id);
$method = $order->get_meta('_wct_delivery_method');

if ($method === 'pickup') {
    // Handle pickup order
} else {
    // Handle delivery order (default)
}

// Get settings
$settings = Main::get_instance()->get_delivery_method_settings();
$delivery_label = $settings['delivery_label'];
$pickup_label = $settings['pickup_label'];

// Hook after delivery method saved
add_action('checkout_toolkit_delivery_method_saved', function($order_id, $method) {
    // Custom logic based on method
}, 10, 2);
```

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
| 3 | 1 | Field type options | ✅ Completed | Medium |
| 4 | 2 | Pickup vs Delivery toggle | ✅ Completed | High |
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

### 2024-12-24
- Completed Feature 3: Field Type Options (text, textarea, checkbox, select)
- Completed Feature 4: Pickup vs Delivery Toggle
- Progress: 40% (4/10 features complete)

### 2024-12-23
- Completed Feature 1: Order Notes Placeholder Customization
- Completed Feature 2: Second Custom Field Support
- Created FEATURE-PROGRESS.md for tracking

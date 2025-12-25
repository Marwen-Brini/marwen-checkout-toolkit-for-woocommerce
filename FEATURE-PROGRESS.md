# Checkout Toolkit Feature Expansion Progress

## Overview

Comprehensive expansion of the Checkout Toolkit for WooCommerce plugin with 10 new features organized into 4 phases. This document tracks implementation progress, technical details, and serves as a reference for continuing development.

**Total Features:** 10
**Completed:** 8
**Progress:** 80%

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

**Status:** ✅ Completed

**Description:**
Add a dedicated field for delivery instructions with preset dropdown options plus custom textarea. Only visible when "Delivery" is selected (hidden for Pickup orders).

**Settings:**
| Setting | Type | Default |
|---------|------|---------|
| `enabled` | boolean | `false` |
| `required` | boolean | `false` |
| `field_label` | string | `'Delivery Instructions'` |
| `preset_label` | string | `'Common Instructions'` |
| `preset_options` | array | `[['value' => 'leave_door', 'label' => 'Leave at door'], ...]` |
| `custom_label` | string | `'Additional Instructions'` |
| `custom_placeholder` | string | `'Any other delivery instructions...'` |
| `max_length` | integer | `500` |
| `show_in_emails` | boolean | `true` |
| `show_in_admin` | boolean | `true` |

**Meta Keys:**
- `_wct_delivery_instructions_preset` - Selected preset option value
- `_wct_delivery_instructions_custom` - Custom text instructions

**Implementation Details:**

1. **Class:** `src/WooCheckoutToolkit/Delivery/DeliveryInstructions.php`
   - Renders preset dropdown + custom textarea on checkout (classic)
   - Conditional visibility based on delivery method selection
   - Listens for `wct_delivery_method_changed` event to show/hide
   - Validates that at least one field is filled if required
   - Saves both preset and custom values to separate meta keys
   - Character counter for custom textarea

2. **Admin View:** `admin/views/settings-delivery-instructions.php`
   - Enable/disable toggle with note about Delivery-only visibility
   - Required checkbox
   - Section label, preset dropdown label configuration
   - Preset options repeater (add/remove options dynamically)
   - Custom text label and placeholder configuration
   - Max length setting with character counter
   - Show in admin/emails checkboxes
   - Live preview section

3. **Settings Registration:** `src/WooCheckoutToolkit/Admin/Settings.php`
   - Option name: `checkout_toolkit_delivery_instructions_settings`
   - Sanitization: `sanitize_delivery_instructions_settings()`
   - Default getter: `get_default_delivery_instructions_settings()`
   - Uses existing `sanitize_select_options()` for preset options

4. **Settings Page:** `admin/views/settings-page.php`
   - Added "Delivery Instructions" tab after Pickup/Delivery
   - Added include for settings-delivery-instructions.php

5. **Blocks Integration:** `src/WooCheckoutToolkit/Blocks/BlocksIntegration.php`
   - Added `delivery_instructions_preset` and `delivery_instructions_custom` to Store API schema
   - Added `deliveryInstructions` settings to `get_script_data()`
   - Added `get_delivery_instructions_settings()` helper method
   - Saves both fields in `save_order_data()` only if not pickup

6. **Frontend JS (Classic):** Inline JS in DeliveryInstructions.php
   - Listens for `wct_delivery_method_changed` event
   - Shows/hides with slideUp/slideDown animation
   - Character counter updates on input

7. **Frontend JS (Blocks):** `public/js/blocks-checkout.js`
   - Added `DeliveryInstructionsComponent` React component
   - Preset dropdown + custom textarea with consistent styling
   - Watches `extensionDataState.delivery_method` for visibility
   - Dispatches both preset and custom values to extension data
   - Renders at `woocommerce_before_order_notes` position

8. **Display Components:**
   - `OrderDisplay.php`: Shows preset label + custom text in admin order view and meta box
   - `EmailDisplay.php`: Includes delivery instructions in order emails (HTML and plain text)
   - `AccountDisplay.php`: Shows delivery instructions in My Account order details
   - All use `get_preset_label()` to display the label instead of value

**Files Created:**
```
src/WooCheckoutToolkit/Delivery/DeliveryInstructions.php
admin/views/settings-delivery-instructions.php
```

**Files Modified:**
```
src/WooCheckoutToolkit/Main.php
  - Added: use WooCheckoutToolkit\Delivery\DeliveryInstructions;
  - Added: private ?DeliveryInstructions $delivery_instructions = null;
  - Added: Initialization in init_frontend()
  - Added: get_default_delivery_instructions_settings()
  - Added: get_delivery_instructions_settings()
  - Updated: get_blocks_script_data() with deliveryInstructions
  - Updated: enqueue checks to include delivery_instructions_settings

src/WooCheckoutToolkit/Admin/Settings.php
  - Added: register_setting() for delivery_instructions_settings
  - Added: get_default_delivery_instructions_settings()
  - Added: sanitize_delivery_instructions_settings()

admin/views/settings-page.php
  - Added: $delivery_instructions_settings variable
  - Added: Delivery Instructions tab in navigation
  - Added: Include for settings-delivery-instructions.php

src/WooCheckoutToolkit/Blocks/BlocksIntegration.php
  - Added: delivery_instructions_preset and delivery_instructions_custom to schema
  - Added: deliveryInstructions to script data
  - Added: get_delivery_instructions_settings() helper
  - Updated: save_order_data() for delivery instructions (respects pickup)

public/js/blocks-checkout.js
  - Added: deliveryInstructions to destructured settings
  - Added: DeliveryInstructionsComponent with visibility based on delivery method
  - Updated: initPositionedFields() to render delivery instructions
  - Updated: initExtensionData() with delivery instructions

src/WooCheckoutToolkit/Display/OrderDisplay.php
  - Added: delivery_instructions_preset/custom variables and settings
  - Added: Display logic in display_in_admin() and render_meta_box()
  - Added: get_preset_label() helper method

src/WooCheckoutToolkit/Display/EmailDisplay.php
  - Added: delivery_instructions_preset/custom variables and settings
  - Updated: render_html() and render_plain_text() signatures and content
  - Added: get_preset_label() helper method

src/WooCheckoutToolkit/Display/AccountDisplay.php
  - Added: delivery_instructions_preset/custom variables and settings
  - Added: Display logic for delivery instructions
  - Added: get_preset_label() helper method
```

**Data Flow (Blocks Checkout):**
```
1. User selects preset option or types custom text
2. DeliveryInstructionsComponent calls setExtensionData() for each
3. On submit, Store API receives extensions['checkout-toolkit']['delivery_instructions_preset/custom']
4. BlocksIntegration::save_order_data() checks delivery method
5. If not pickup, saves to _wct_delivery_instructions_preset and _wct_delivery_instructions_custom
6. Display components read via $order->get_meta()
```

**Data Flow (Classic Checkout):**
```
1. User selects preset option or types custom text
2. Form submits with checkout_toolkit_delivery_instructions_preset/custom in POST
3. DeliveryInstructions::validate_field() validates if required
4. DeliveryInstructions::save_field() checks delivery method and saves
5. Display components read via $order->get_meta()
```

**Usage Example:**
```php
// Get delivery instructions from order
$order = wc_get_order($order_id);
$preset = $order->get_meta('_wct_delivery_instructions_preset');
$custom = $order->get_meta('_wct_delivery_instructions_custom');

// Get preset label from settings
$settings = Main::get_instance()->get_delivery_instructions_settings();
foreach ($settings['preset_options'] as $option) {
    if ($option['value'] === $preset) {
        $preset_label = $option['label'];
        break;
    }
}

// Combine for display
$instructions = '';
if (!empty($preset_label)) {
    $instructions .= $preset_label;
}
if (!empty($custom)) {
    $instructions .= ' - ' . $custom;
}

// Hook after delivery instructions saved
add_action('checkout_toolkit_delivery_instructions_preset_saved', function($order_id, $value) {
    // Custom logic
}, 10, 2);

add_action('checkout_toolkit_delivery_instructions_custom_saved', function($order_id, $value) {
    // Custom logic
}, 10, 2);
```

---

### Feature 6: Time Window Selection

**Status:** ✅ Completed

**Description:**
Allow customers to select a preferred delivery time window (Morning, Afternoon, Evening) or custom time slots defined by the store. Only visible when "Delivery" is selected (hidden for Pickup orders).

**Settings:**
| Setting | Type | Default |
|---------|------|---------|
| `enabled` | boolean | `false` |
| `required` | boolean | `false` |
| `field_label` | string | `'Preferred Time'` |
| `time_slots` | array | `[['value' => 'morning', 'label' => 'Morning (9am-12pm)'], ...]` |
| `show_only_with_delivery` | boolean | `true` |
| `show_in_emails` | boolean | `true` |
| `show_in_admin` | boolean | `true` |

**Meta Key:** `_wct_time_window`

**Implementation Details:**

1. **Class:** `src/WooCheckoutToolkit/Delivery/TimeWindow.php`
   - Renders dropdown on checkout (classic checkout)
   - Conditional visibility based on delivery method selection
   - Listens for `wct_delivery_method_changed` event to show/hide
   - Validates selected option is valid if required
   - Saves selected time slot to meta

2. **Admin View:** `admin/views/settings-time-windows.php`
   - Enable/disable toggle with note about Delivery-only visibility
   - Required checkbox
   - Field label configuration
   - Time slots repeater (add/remove options dynamically)
   - Show only with delivery checkbox
   - Show in admin/emails checkboxes
   - Live preview section

3. **Settings Registration:** `src/WooCheckoutToolkit/Admin/Settings.php`
   - Option name: `checkout_toolkit_time_window_settings`
   - Sanitization: `sanitize_time_window_settings()`
   - Default getter: `get_default_time_window_settings()`

4. **Settings Page:** `admin/views/settings-page.php`
   - Added "Time Windows" tab after Delivery Instructions

5. **Blocks Integration:** `src/WooCheckoutToolkit/Blocks/BlocksIntegration.php`
   - Added `time_window` to Store API schema
   - Added `timeWindow` settings to `get_script_data()`
   - Added `get_time_window_settings()` helper method
   - Validates selected option and saves in `save_order_data()`

6. **Frontend JS (Classic):** Inline JS in TimeWindow.php
   - Listens for `wct_delivery_method_changed` event
   - Shows/hides based on delivery method selection

7. **Frontend JS (Blocks):** `public/js/blocks-checkout.js`
   - Added `TimeWindowComponent` React component
   - Dropdown select with time slot options
   - Watches delivery method for visibility
   - Dispatches selected value to extension data
   - Renders at `woocommerce_before_order_notes` position

8. **Display Components:**
   - `OrderDisplay.php`: Shows time window in admin order view and meta box
   - `EmailDisplay.php`: Includes time window in order emails (HTML and plain text)
   - `AccountDisplay.php`: Shows time window in My Account order details
   - All use `get_time_slot_label()` to display the label instead of value

**Files Created:**
```
src/WooCheckoutToolkit/Delivery/TimeWindow.php
admin/views/settings-time-windows.php
```

**Files Modified:**
```
src/WooCheckoutToolkit/Main.php
  - Added: use WooCheckoutToolkit\Delivery\TimeWindow;
  - Added: private ?TimeWindow $time_window = null;
  - Added: Initialization in init_frontend()
  - Added: get_default_time_window_settings()
  - Added: get_time_window_settings()
  - Updated: get_blocks_script_data() with timeWindow
  - Updated: enqueue checks to include time_window_settings

src/WooCheckoutToolkit/Admin/Settings.php
  - Added: register_setting() for time_window_settings
  - Added: get_default_time_window_settings()
  - Added: sanitize_time_window_settings()

admin/views/settings-page.php
  - Added: $time_window_settings variable
  - Added: Time Windows tab in navigation
  - Added: Include for settings-time-windows.php

src/WooCheckoutToolkit/Blocks/BlocksIntegration.php
  - Added: time_window to Store API schema
  - Added: timeWindow to script data
  - Added: get_time_window_settings() helper
  - Updated: save_order_data() for time_window with validation

public/js/blocks-checkout.js
  - Added: timeWindow to destructured settings
  - Added: TimeWindowComponent with visibility based on delivery method
  - Updated: initPositionedFields() to render time window
  - Updated: initExtensionData() with time_window

src/WooCheckoutToolkit/Display/OrderDisplay.php
  - Added: time_window variable and settings
  - Added: Display logic in display_in_admin() and render_meta_box()
  - Added: get_time_slot_label() helper method

src/WooCheckoutToolkit/Display/EmailDisplay.php
  - Added: time_window variable and settings
  - Updated: render_html() and render_plain_text() signatures and content
  - Added: get_time_slot_label() helper method

src/WooCheckoutToolkit/Display/AccountDisplay.php
  - Added: time_window variable and settings
  - Added: Display logic for time window
  - Added: get_time_slot_label() helper method
```

**Data Flow (Blocks Checkout):**
```
1. User selects time window from dropdown
2. TimeWindowComponent calls setExtensionData('checkout-toolkit', 'time_window', value)
3. On submit, Store API receives extensions['checkout-toolkit']['time_window']
4. BlocksIntegration::save_order_data() validates against available options
5. If valid, saves to _wct_time_window
6. Display components read via $order->get_meta()
```

**Data Flow (Classic Checkout):**
```
1. User selects time window from dropdown
2. Form submits with checkout_toolkit_time_window in POST
3. TimeWindow::validate_time_window() validates selection
4. TimeWindow::save_time_window() saves to _wct_time_window
5. Display components read via $order->get_meta()
```

**Usage Example:**
```php
// Get time window from order
$order = wc_get_order($order_id);
$time_window = $order->get_meta('_wct_time_window');

// Get time slot label from settings
$settings = Main::get_instance()->get_time_window_settings();
foreach ($settings['time_slots'] as $slot) {
    if ($slot['value'] === $time_window) {
        $time_label = $slot['label'];
        break;
    }
}

// Hook after time window saved
add_action('checkout_toolkit_time_window_saved', function($order_id, $value) {
    // Custom logic based on selected time
}, 10, 2);
```

---

## Phase 3: Dependent Features

### Feature 7: Store Location Selector

**Status:** ✅ Completed

**Description:**
For pickup orders, allow customers to select which store location to pick up from. Displays store details including name, address, phone, and hours. Only visible when "Pickup" is selected (hidden for Delivery orders).

**Settings:**
| Setting | Type | Default |
|---------|------|---------|
| `enabled` | boolean | `false` |
| `required` | boolean | `true` |
| `field_label` | string | `'Pickup Location'` |
| `locations` | array | `[]` |
| `show_in_emails` | boolean | `true` |
| `show_in_admin` | boolean | `true` |

**Locations Array Structure:**
```php
[
    [
        'id' => 'main-store',
        'name' => 'Main Store',
        'address' => '123 Main Street, City, State 12345',
        'phone' => '(555) 123-4567',
        'hours' => 'Mon-Fri: 9am-6pm, Sat: 10am-4pm',
    ],
    // ... additional locations
]
```

**Meta Key:** `_wct_store_location` (stores location ID)

**Implementation Details:**

1. **Class:** `src/WooCheckoutToolkit/Pickup/StoreLocationSelector.php`
   - Renders dropdown with configured store locations on checkout (classic)
   - **OPPOSITE visibility** from delivery fields: Shows ONLY when Pickup selected, hidden for Delivery
   - Listens for `wct_delivery_method_changed` event to show/hide
   - Shows location details (address, phone, hours) when location is selected
   - Validates that a location is selected when required
   - Only saves to meta when Pickup is selected

2. **Admin View:** `admin/views/settings-store-locations.php`
   - Enable/disable toggle with note about Pickup-only visibility
   - Required checkbox
   - Field label configuration
   - **Store locations repeater** (add/remove functionality):
     - ID (auto-generated from name if empty)
     - Name (required)
     - Address
     - Phone
     - Hours
   - Show in admin/emails checkboxes
   - Live preview section

3. **Settings Registration:** `src/WooCheckoutToolkit/Admin/Settings.php`
   - Option name: `checkout_toolkit_store_locations_settings`
   - Sanitization: `sanitize_store_locations_settings()`
   - Default getter: `get_default_store_locations_settings()`
   - Uses `sanitize_store_locations()` helper for repeater

4. **Settings Page:** `admin/views/settings-page.php`
   - Added "Store Locations" tab after Pickup/Delivery

5. **Blocks Integration:** `src/WooCheckoutToolkit/Blocks/BlocksIntegration.php`
   - Added `store_location` to Store API schema
   - Added `storeLocations` settings to `get_script_data()`
   - Added `get_store_locations_settings()` helper method
   - Validates against available locations and saves in `save_order_data()`
   - Only saves when delivery method is 'pickup'

6. **Frontend JS (Classic):** Inline JS in StoreLocationSelector.php
   - Listens for `wct_delivery_method_changed` event
   - Shows for pickup, hides for delivery (opposite of delivery fields)
   - Shows location details when a location is selected

7. **Frontend JS (Blocks):** `public/js/blocks-checkout.js`
   - Added `StoreLocationComponent` React component
   - Dropdown with configured locations
   - Shows location details (address, phone, hours) when selected
   - **OPPOSITE visibility**: `isVisible = (method === 'pickup')`
   - Dispatches selected value to extension data
   - Renders at `woocommerce_before_order_notes` position

8. **Display Components:**
   - `OrderDisplay.php`: Shows full location details in admin order view and meta box
   - `EmailDisplay.php`: Includes location details in order emails (HTML and plain text)
   - `AccountDisplay.php`: Shows location details in My Account order details
   - All use `get_store_location_by_id()` to display full location data

**Files Created:**
```
src/WooCheckoutToolkit/Pickup/StoreLocationSelector.php
admin/views/settings-store-locations.php
```

**Files Modified:**
```
src/WooCheckoutToolkit/Main.php
  - Added: use WooCheckoutToolkit\Pickup\StoreLocationSelector;
  - Added: private ?StoreLocationSelector $store_location_selector = null;
  - Added: Initialization in init_frontend()
  - Added: get_default_store_locations_settings()
  - Added: get_store_locations_settings()
  - Updated: get_blocks_script_data() with storeLocations
  - Updated: enqueue checks to include store_locations_settings

src/WooCheckoutToolkit/Admin/Settings.php
  - Added: register_setting() for store_locations_settings
  - Added: get_default_store_locations_settings()
  - Added: sanitize_store_locations_settings()
  - Added: sanitize_store_locations() helper

admin/views/settings-page.php
  - Added: $checkout_toolkit_store_locations_settings variable
  - Added: Store Locations tab in navigation
  - Added: Include for settings-store-locations.php

src/WooCheckoutToolkit/Blocks/BlocksIntegration.php
  - Added: store_location to Store API schema
  - Added: storeLocations to script data
  - Added: get_store_locations_settings() helper
  - Updated: save_order_data() for store_location (validates and saves only for pickup)

public/js/blocks-checkout.js
  - Added: storeLocations to destructured settings
  - Added: StoreLocationComponent with opposite visibility logic
  - Updated: initPositionedFields() to render store location
  - Updated: initExtensionData() with store_location

src/WooCheckoutToolkit/Display/OrderDisplay.php
  - Added: store_location variable and settings
  - Added: Display logic in display_in_admin() and render_meta_box()
  - Added: get_store_location_by_id() helper method

src/WooCheckoutToolkit/Display/EmailDisplay.php
  - Added: store_location variable and settings
  - Updated: render_html() and render_plain_text() signatures and content
  - Added: get_store_location_by_id() helper method

src/WooCheckoutToolkit/Display/AccountDisplay.php
  - Added: store_location variable and settings
  - Added: Display logic for store location
  - Added: get_store_location_by_id() helper method
```

**Key Implementation Note - Opposite Visibility:**
Unlike delivery-related fields (Delivery Date, Time Window, Delivery Instructions) which show when Delivery is selected and hide for Pickup, the Store Location Selector has **opposite visibility**:
- Shows when Pickup is selected
- Hides when Delivery is selected

```javascript
// Classic checkout
$(document.body).on('wct_delivery_method_changed', function(e, method) {
    if (method === 'pickup') {
        $('#wct-store-location-wrapper').slideDown(200);
    } else {
        $('#wct-store-location-wrapper').slideUp(200);
    }
});

// Blocks checkout
const [isVisible, setIsVisible] = useState(initialMethod === 'pickup');
// On method change: setIsVisible(method === 'pickup');
```

**Data Flow (Blocks Checkout):**
```
1. User selects Pickup as delivery method
2. StoreLocationComponent becomes visible
3. User selects store from dropdown
4. StoreLocationComponent calls setExtensionData('checkout-toolkit', 'store_location', locationId)
5. On submit, Store API receives extensions['checkout-toolkit']['store_location']
6. BlocksIntegration::save_order_data() validates delivery method is pickup
7. If pickup, validates location ID and saves to _wct_store_location
8. Display components read via $order->get_meta() and look up full location data
```

**Data Flow (Classic Checkout):**
```
1. User selects Pickup as delivery method
2. Store location wrapper becomes visible via jQuery
3. User selects store from dropdown
4. Form submits with checkout_toolkit_store_location in POST
5. StoreLocationSelector::validate_field() checks if pickup and validates selection
6. StoreLocationSelector::save_field() checks if pickup and saves to _wct_store_location
7. Display components read via $order->get_meta() and look up full location data
```

**Display Format:**
```
Admin Order View / Emails / My Account:

Pickup Location:
Main Store
123 Main Street, City, State 12345
Phone: (555) 123-4567
Hours: Mon-Fri: 9am-6pm, Sat: 10am-4pm
```

**Usage Example:**
```php
// Get store location from order
$order = wc_get_order($order_id);
$location_id = $order->get_meta('_wct_store_location');

// Get full location data from settings
$settings = Main::get_instance()->get_store_locations_settings();
foreach ($settings['locations'] as $location) {
    if ($location['id'] === $location_id) {
        $store_name = $location['name'];
        $store_address = $location['address'];
        $store_phone = $location['phone'];
        $store_hours = $location['hours'];
        break;
    }
}

// Hook after store location saved
add_action('checkout_toolkit_store_location_saved', function($order_id, $location_id) {
    // Custom logic based on selected store
}, 10, 2);
```

---

### Feature 8: Estimated Delivery Display

**Status:** ✅ Completed

**Description:**
Calculate and display estimated delivery date message on the checkout page based on lead time, disabled days, cutoff time, and order timing. Shows a dynamic message like "Order by 2pm for delivery as early as Thursday, December 26".

**Settings (Added to existing `checkout_toolkit_delivery_settings`):**
| Setting | Type | Default |
|---------|------|---------|
| `show_estimated_delivery` | boolean | `false` |
| `estimated_delivery_message` | string | `'Order now for delivery as early as {date}'` |
| `cutoff_time` | string | `'14:00'` |
| `cutoff_message` | string | `'Order by {time} for delivery as early as {date}'` |

**Implementation Details:**

1. **AvailabilityChecker Enhancement:** `src/WooCheckoutToolkit/Delivery/AvailabilityChecker.php`
   - Added `get_earliest_available_date(bool $after_cutoff = false)` method
   - Uses existing date availability logic (disabled weekdays, blocked dates)
   - If `$after_cutoff` is true or current time >= cutoff, adds +1 to lead days
   - Returns earliest available date in Y-m-d format

2. **DeliveryDate Update:** `src/WooCheckoutToolkit/Delivery/DeliveryDate.php`
   - Added `render_estimated_delivery_message()` method
   - Compares current time to cutoff time
   - Shows appropriate message template with `{date}` and `{time}` replaced
   - Rendered above the date picker field

3. **Admin UI:** `admin/views/settings-delivery.php`
   - New "Estimated Delivery Message" section added
   - Enable/disable toggle
   - Cutoff time input (time picker)
   - Before cutoff message template with `{time}` and `{date}` placeholders
   - After cutoff message template with `{date}` placeholder

4. **Settings:** `src/WooCheckoutToolkit/Admin/Settings.php`
   - Added sanitization for new delivery settings
   - Added `sanitize_time()` helper for HH:MM format validation

5. **Main Class:** `src/WooCheckoutToolkit/Main.php`
   - Added defaults for new estimated delivery settings
   - Added `get_estimated_delivery_data()` helper method
   - Updated `get_blocks_script_data()` to include estimatedDelivery

6. **Blocks Integration:** `public/js/blocks-checkout.js`
   - Added `EstimatedDeliveryMessage` React component
   - Calculates if current time is past cutoff
   - Displays appropriate message with formatted date
   - Rendered inside DeliveryDateField component above the date picker

**Files Modified:**
```
src/WooCheckoutToolkit/Admin/Settings.php
  - Added: sanitize_time() helper method
  - Updated: sanitize_delivery_settings() with new fields

src/WooCheckoutToolkit/Main.php
  - Updated: get_default_delivery_settings() with new settings
  - Added: get_estimated_delivery_data() method
  - Updated: get_blocks_script_data() with estimatedDelivery

src/WooCheckoutToolkit/Delivery/AvailabilityChecker.php
  - Added: get_earliest_available_date() method
  - Added: find_next_available_date() helper method

src/WooCheckoutToolkit/Delivery/DeliveryDate.php
  - Added: render_estimated_delivery_message() method
  - Updated: render_delivery_date_field() to call message renderer

admin/views/settings-delivery.php
  - Added: Estimated Delivery Message settings section

public/js/blocks-checkout.js
  - Added: estimatedDelivery to destructured settings
  - Added: EstimatedDeliveryMessage component
  - Updated: DeliveryDateField to render EstimatedDeliveryMessage
```

**Cutoff Time Logic:**
```
Before Cutoff (e.g., 10am when cutoff is 2pm):
- Uses min_lead_days as-is
- Shows: "Order by 2:00pm for delivery as early as Thursday, December 26"

After Cutoff (e.g., 3pm when cutoff is 2pm):
- Adds +1 to min_lead_days
- Shows: "Order now for delivery as early as Friday, December 27"
```

**Display Location:** Checkout page only, above the delivery date picker

**Usage Example:**
```php
// Get earliest available date
$checker = new AvailabilityChecker();
$earliest = $checker->get_earliest_available_date(); // Uses current time
$earliest_tomorrow = $checker->get_earliest_available_date(true); // Forces after-cutoff

// Settings include:
$settings = Main::get_instance()->get_delivery_settings();
$cutoff = $settings['cutoff_time']; // e.g., '14:00'
$message = $settings['cutoff_message']; // e.g., 'Order by {time} for delivery as early as {date}'
```

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
| 5 | 2 | Delivery instructions | ✅ Completed | Medium |
| 6 | 2 | Time window selection | ✅ Completed | Medium |
| 7 | 3 | Store location selector | ✅ Completed | High |
| 8 | 3 | Estimated delivery display | ✅ Completed | Low |
| 9 | 4 | Product/category visibility | ⏳ Pending | Medium |
| 10 | 4 | Gift message option | ⏳ Pending | Low |

---

## Technical Reference

### Coding Standards

All template variables in `admin/views/*.php` files must use the `$checkout_toolkit_` prefix to comply with WordPress Plugin Check requirements.

Example:
```php
// Correct - uses plugin prefix
$checkout_toolkit_settings = get_option('checkout_toolkit_delivery_settings');
foreach ($checkout_toolkit_positions as $checkout_toolkit_hook => $checkout_toolkit_label) {
    // ...
}

// Incorrect - will fail plugin check
$settings = get_option('checkout_toolkit_delivery_settings');
foreach ($positions as $hook => $label) {
    // ...
}
```

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

### 2025-12-25 (Session 5)
- Completed Feature 8: Estimated Delivery Display
  - Shows estimated delivery date message on checkout (above date picker)
  - Cutoff time logic: orders before cutoff get earlier delivery date
  - Customizable message templates with {date} and {time} placeholders
  - Added `get_earliest_available_date()` method to AvailabilityChecker
  - Works in both Classic and Blocks checkout
- Progress: 80% (8/10 features complete)

### 2025-12-25 (Session 4)
- Completed Feature 7: Store Location Selector
  - Admin can configure multiple store locations (name, address, phone, hours)
  - Dropdown appears on checkout ONLY when Pickup is selected (hidden for Delivery)
  - **OPPOSITE visibility** from delivery-related fields
  - Shows location details when selected
  - Selected location saved to order meta `_wct_store_location`
  - Displayed in admin orders, emails, and My Account with full location details
  - Works in both Classic and Blocks checkout
- Progress: 70% (7/10 features complete)

### 2025-12-25 (Session 3)
- Completed Feature 6: Time Window Selection
  - Dropdown field for selecting preferred delivery time window
  - Default time slots: Morning (9am-12pm), Afternoon (12pm-5pm), Evening (5pm-8pm)
  - Configurable time slots with add/remove repeater in admin
  - Conditional visibility: only shown when Delivery is selected, hidden for Pickup
  - Works in both Classic and Blocks checkout
  - Displayed in admin orders, emails, and My Account
- Progress: 60% (6/10 features complete)

### 2025-12-25 (Session 2)
- Completed Feature 5: Delivery Instructions Field
  - Preset dropdown with configurable options (Leave at door, Ring bell, etc.)
  - Custom textarea for additional instructions
  - Conditional visibility: only shown when Delivery is selected, hidden for Pickup
  - Works in both Classic and Blocks checkout
  - Displayed in admin orders, emails, and My Account
- Added conditional visibility to Delivery Date field (hide for Pickup)
- Progress: 50% (5/10 features complete)

---

## Future Refactoring Notes

### Pickup/Delivery Field Visibility Review
Once all Pickup/Delivery features are complete, conduct a full review of field visibility:
- **Issue:** Some fields (e.g., custom field checkbox) still show when Pickup is selected
- **Action:** Review all checkout fields and ensure proper conditional visibility based on delivery method
- **Scope:**
  - Custom Field 1 and 2 - add option for "Show only for Delivery" / "Show only for Pickup" / "Show for both"
  - Any other delivery-specific fields should respect the Pickup/Delivery toggle
- **Priority:** After Phase 2 features complete

### 2025-12-25
- Fixed dropdown label/value order in settings-fields.php
- Added UX improvement: field options disabled until field is enabled
- Implemented position-based rendering for Blocks checkout (full position support)
- Fixed `__internalSetExtensionData` deprecation (using new `setExtensionData` API with fallback)
- Added debouncing to reduce `useSelect` re-render warnings
- Fixed settings defaults merging in DeliveryDate.php and AvailabilityChecker.php
- Security hardening: Added proper nonce verification to OrderFields, OrderFields2, DeliveryMethod, DeliveryList
- Plugin passes WordPress Plugin Check (only hidden files and unavoidable slow DB query warnings remain)

### 2024-12-24
- Completed Feature 3: Field Type Options (text, textarea, checkbox, select)
- Completed Feature 4: Pickup vs Delivery Toggle
- Progress: 40% (4/10 features complete)

### 2024-12-23
- Completed Feature 1: Order Notes Placeholder Customization
- Completed Feature 2: Second Custom Field Support
- Created FEATURE-PROGRESS.md for tracking

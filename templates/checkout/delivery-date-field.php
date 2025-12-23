<?php
/**
 * Delivery date field template
 *
 * This template can be overridden by copying it to:
 * yourtheme/woocommerce/checkout-toolkit-for-woo/checkout/delivery-date-field.php
 *
 * @package CheckoutToolkitForWoo
 * @version 1.0.0
 *
 * @var array  $settings     Delivery settings
 * @var string $field_value  Current field value
 */

defined('ABSPATH') || exit;

do_action('checkout_toolkit_before_delivery_date_field');
?>

<div class="checkout-toolkit-delivery-date-wrapper">
    <?php
    woocommerce_form_field(
        'checkout_toolkit_delivery_date',
        apply_filters('checkout_toolkit_delivery_date_field_args', [
            'type' => 'text',
            'label' => $settings['field_label'],
            'required' => $settings['required'],
            'class' => ['form-row-wide', 'wct-delivery-date-field'],
            'input_class' => ['wct-datepicker'],
            'custom_attributes' => [
                'readonly' => 'readonly',
                'data-wct-datepicker' => 'true',
            ],
        ]),
        $field_value
    );
    ?>
    <input type="hidden" name="checkout_toolkit_delivery_date_value" id="checkout_toolkit_delivery_date_value" value="" />
</div>

<?php
do_action('checkout_toolkit_after_delivery_date_field');

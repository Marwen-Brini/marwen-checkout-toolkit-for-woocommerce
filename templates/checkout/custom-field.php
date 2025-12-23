<?php
/**
 * Custom field template
 *
 * This template can be overridden by copying it to:
 * yourtheme/woocommerce/checkout-toolkit-for-woo/checkout/custom-field.php
 *
 * @package CheckoutToolkitForWoo
 * @version 1.0.0
 *
 * @var array  $settings     Field settings
 * @var array  $field_args   Field arguments
 * @var string $field_value  Current field value
 */

defined('ABSPATH') || exit;

do_action('checkout_toolkit_before_custom_field');
?>

<div class="checkout-toolkit-custom-field-wrapper">
    <?php
    woocommerce_form_field(
        'checkout_toolkit_custom_field',
        apply_filters('checkout_toolkit_custom_field_args', $field_args),
        $field_value
    );
    ?>
</div>

<?php
do_action('checkout_toolkit_after_custom_field');

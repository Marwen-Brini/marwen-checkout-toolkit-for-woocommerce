<?php
/**
 * Delivery date field template
 *
 * This template can be overridden by copying it to:
 * yourtheme/woocommerce/marwen-marwchto-for-woocommerce/checkout/delivery-date-field.php
 *
 * @package CheckoutToolkitForWoo
 * @version 1.0.0
 *
 * @var array  $settings     Delivery settings
 * @var string $field_value  Current field value
 */

defined('ABSPATH') || exit;

do_action('marwchto_before_delivery_date_field');
?>

<div class="marwchto-delivery-date-wrapper">
    <?php
    woocommerce_form_field(
        'marwchto_delivery_date',
        apply_filters('marwchto_delivery_date_field_args', [
            'type' => 'text',
            'label' => $settings['field_label'],
            'required' => $settings['required'],
            'class' => ['form-row-wide', 'marwchto-delivery-date-field'],
            'input_class' => ['marwchto-datepicker'],
            'custom_attributes' => [
                'readonly' => 'readonly',
                'data-marwchto-datepicker' => 'true',
            ],
        ]),
        $field_value
    );
    ?>
    <input type="hidden" name="marwchto_delivery_date_value" id="marwchto_delivery_date_value" value="" />
</div>

<?php
do_action('marwchto_after_delivery_date_field');

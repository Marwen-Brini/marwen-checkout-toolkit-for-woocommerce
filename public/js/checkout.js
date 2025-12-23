/**
 * Checkout Toolkit for WooCommerce - Checkout JavaScript
 *
 * @package CheckoutToolkitForWoo
 */

(function($) {
    'use strict';

    const WCTCheckout = {
        /**
         * Initialize checkout functionality
         */
        init: function() {
            if (typeof wctConfig === 'undefined') {
                return;
            }

            this.initDeliveryDatePicker();
            this.initCharacterCounter();
        },

        /**
         * Initialize Flatpickr date picker
         */
        initDeliveryDatePicker: function() {
            if (!wctConfig.delivery.enabled) {
                return;
            }

            const $datepicker = $('[data-wct-datepicker="true"]');

            if (!$datepicker.length || typeof flatpickr === 'undefined') {
                return;
            }

            const self = this;

            flatpickr($datepicker[0], {
                minDate: wctConfig.delivery.minDate,
                maxDate: wctConfig.delivery.maxDate,
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: wctConfig.delivery.dateFormat || 'F j, Y',
                firstDayOfWeek: wctConfig.delivery.firstDayOfWeek || 1,
                disableMobile: false,
                locale: {
                    firstDayOfWeek: wctConfig.delivery.firstDayOfWeek || 1
                },
                disable: [
                    // Disable specific dates
                    ...self.getBlockedDates(),
                    // Disable days of week
                    function(date) {
                        return self.isDayDisabled(date);
                    }
                ],
                onChange: function(selectedDates, dateStr) {
                    // Update hidden field with Y-m-d format
                    $('#checkout_toolkit_delivery_date_value').val(dateStr);

                    // Trigger WooCommerce update if needed
                    $(document.body).trigger('update_checkout');
                },
                onReady: function(selectedDates, dateStr, instance) {
                    // Add custom class to calendar
                    $(instance.calendarContainer).addClass('wct-calendar');
                }
            });
        },

        /**
         * Get blocked dates array
         */
        getBlockedDates: function() {
            if (!wctConfig.delivery.disabledDates) {
                return [];
            }

            return wctConfig.delivery.disabledDates.map(function(date) {
                return date;
            });
        },

        /**
         * Check if a day of week is disabled
         */
        isDayDisabled: function(date) {
            if (!wctConfig.delivery.disabledDays || !wctConfig.delivery.disabledDays.length) {
                return false;
            }

            const dayOfWeek = date.getDay();
            return wctConfig.delivery.disabledDays.includes(dayOfWeek);
        },

        /**
         * Initialize character counter for custom field
         */
        initCharacterCounter: function() {
            if (!wctConfig.field.enabled) {
                return;
            }

            const $field = $('#checkout_toolkit_custom_field');
            const maxLength = wctConfig.field.maxLength;

            if (!$field.length || !maxLength || maxLength <= 0) {
                return;
            }

            // Create counter element
            const $counter = $('<span class="wct-char-counter"></span>');
            $field.after($counter);

            // Update counter function
            const updateCounter = function() {
                const remaining = maxLength - $field.val().length;
                $counter.text(remaining + ' ' + (wctConfig.i18n.charactersRemaining || 'characters remaining'));

                // Add warning class when low
                if (remaining <= 20) {
                    $counter.addClass('warning');
                } else {
                    $counter.removeClass('warning');
                }
            };

            // Bind events
            $field.on('input keyup', updateCounter);

            // Initial update
            updateCounter();
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        WCTCheckout.init();
    });

    // Re-initialize after WooCommerce updates checkout
    $(document.body).on('updated_checkout', function() {
        WCTCheckout.init();
    });

})(jQuery);

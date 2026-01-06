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
            if (typeof marwchtoConfig === 'undefined') {
                return;
            }

            this.initDeliveryDatePicker();
            this.initCharacterCounter();
            this.initDeliveryMethodToggle();
            this.initDeliveryInstructions();
            this.initStoreLocationSelector();
            this.initTimeWindow();
            this.initDeliveryDateVisibility();
        },

        /**
         * Initialize Flatpickr date picker
         */
        initDeliveryDatePicker: function() {
            if (!marwchtoConfig.delivery.enabled) {
                return;
            }

            const $datepicker = $('[data-wct-datepicker="true"]');

            if (!$datepicker.length || typeof flatpickr === 'undefined') {
                return;
            }

            const self = this;

            flatpickr($datepicker[0], {
                minDate: marwchtoConfig.delivery.minDate,
                maxDate: marwchtoConfig.delivery.maxDate,
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: marwchtoConfig.delivery.dateFormat || 'F j, Y',
                firstDayOfWeek: marwchtoConfig.delivery.firstDayOfWeek || 1,
                disableMobile: false,
                locale: {
                    firstDayOfWeek: marwchtoConfig.delivery.firstDayOfWeek || 1
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
                    $(instance.calendarContainer).addClass('marwchto-calendar');
                }
            });
        },

        /**
         * Get blocked dates array
         */
        getBlockedDates: function() {
            if (!marwchtoConfig.delivery.disabledDates) {
                return [];
            }

            return marwchtoConfig.delivery.disabledDates.map(function(date) {
                return date;
            });
        },

        /**
         * Check if a day of week is disabled
         */
        isDayDisabled: function(date) {
            if (!marwchtoConfig.delivery.disabledDays || !marwchtoConfig.delivery.disabledDays.length) {
                return false;
            }

            const dayOfWeek = date.getDay();
            return marwchtoConfig.delivery.disabledDays.includes(dayOfWeek);
        },

        /**
         * Initialize character counter for custom field
         */
        initCharacterCounter: function() {
            if (!marwchtoConfig.field.enabled) {
                return;
            }

            const $field = $('#checkout_toolkit_custom_field');
            const maxLength = marwchtoConfig.field.maxLength;

            if (!$field.length || !maxLength || maxLength <= 0) {
                return;
            }

            // Create counter element
            const $counter = $('<span class="wct-char-counter"></span>');
            $field.after($counter);

            // Update counter function
            const updateCounter = function() {
                const remaining = maxLength - $field.val().length;
                $counter.text(remaining + ' ' + (marwchtoConfig.i18n.charactersRemaining || 'characters remaining'));

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
        },

        /**
         * Initialize delivery method toggle
         */
        initDeliveryMethodToggle: function() {
            // Toggle style
            $('.marwchto-toggle-option input[type="radio"]').off('change.wct').on('change.wct', function() {
                $('.marwchto-toggle-option').removeClass('active');
                $(this).closest('.marwchto-toggle-option').addClass('active');
                $(document.body).trigger('wct_delivery_method_changed', [$(this).val()]);
            });

            // Radio style
            $('.marwchto-radio-option input[type="radio"]').off('change.wct').on('change.wct', function() {
                $(document.body).trigger('wct_delivery_method_changed', [$(this).val()]);
            });
        },

        /**
         * Initialize delivery instructions field
         */
        initDeliveryInstructions: function() {
            const self = this;

            // Handle delivery method change - show/hide instructions
            $(document.body).off('wct_delivery_method_changed.instructions').on('wct_delivery_method_changed.instructions', function(e, method) {
                if (method === 'pickup') {
                    $('#marwchto-delivery-instructions-wrapper').slideUp(200);
                } else {
                    $('#marwchto-delivery-instructions-wrapper').slideDown(200);
                }
            });

            // Character counter for custom textarea
            self.initDeliveryInstructionsCounter();
        },

        /**
         * Initialize delivery instructions character counter
         */
        initDeliveryInstructionsCounter: function() {
            var $customField = $('#checkout_toolkit_delivery_instructions_custom');
            var $counter = $('.marwchto-di-char-counter');

            if (!$customField.length || !$counter.length) {
                return;
            }

            var maxLength = parseInt($customField.attr('maxlength'), 10) || 0;

            if (maxLength <= 0) {
                return;
            }

            var updateCounter = function() {
                var remaining = maxLength - $customField.val().length;
                var text = marwchtoConfig.i18n ? marwchtoConfig.i18n.charactersRemaining : 'characters remaining';
                $counter.text(remaining + ' ' + text);

                if (remaining <= 20) {
                    $counter.addClass('warning');
                } else {
                    $counter.removeClass('warning');
                }
            };

            $customField.off('input.wct keyup.wct').on('input.wct keyup.wct', updateCounter);
            updateCounter();
        },

        /**
         * Initialize store location selector
         */
        initStoreLocationSelector: function() {
            // Handle delivery method change - show/hide store location (OPPOSITE of delivery fields)
            $(document.body).off('wct_delivery_method_changed.storelocation').on('wct_delivery_method_changed.storelocation', function(e, method) {
                if (method === 'pickup') {
                    $('#marwchto-store-location-wrapper').slideDown(200);
                } else {
                    $('#marwchto-store-location-wrapper').slideUp(200);
                }
            });

            // Show location details when a location is selected
            var $select = $('#checkout_toolkit_store_location');
            var $details = $('#marwchto-store-location-details');

            $select.off('change.wct').on('change.wct', function() {
                var $selected = $(this).find('option:selected');
                var address = $selected.data('address');
                var phone = $selected.data('phone');
                var hours = $selected.data('hours');

                if ($(this).val() && (address || phone || hours)) {
                    $('.marwchto-location-address').toggle(!!address);
                    $('.marwchto-location-address .marwchto-detail-value').text(address || '');

                    $('.marwchto-location-phone').toggle(!!phone);
                    $('.marwchto-location-phone .marwchto-detail-value').text(phone || '');

                    $('.marwchto-location-hours').toggle(!!hours);
                    $('.marwchto-location-hours .marwchto-detail-value').text(hours || '');

                    $details.slideDown(200);
                } else {
                    $details.slideUp(200);
                }
            });

            // Trigger initial update if a location is pre-selected
            if ($select.val()) {
                $select.trigger('change');
            }
        },

        /**
         * Initialize time window field
         */
        initTimeWindow: function() {
            // Handle delivery method change - show/hide time window
            $(document.body).off('wct_delivery_method_changed.timewindow').on('wct_delivery_method_changed.timewindow', function(e, method) {
                if (method === 'pickup') {
                    $('.checkout-toolkit-time-window-wrapper').slideUp(200);
                } else {
                    $('.checkout-toolkit-time-window-wrapper').slideDown(200);
                }
            });
        },

        /**
         * Initialize delivery date conditional visibility
         */
        initDeliveryDateVisibility: function() {
            // Handle delivery method change - show/hide delivery date
            $(document.body).off('wct_delivery_method_changed.deliverydate').on('wct_delivery_method_changed.deliverydate', function(e, method) {
                if (method === 'pickup') {
                    $('.checkout-toolkit-delivery-date-wrapper').slideUp(200);
                } else {
                    $('.checkout-toolkit-delivery-date-wrapper').slideDown(200);
                }
            });
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

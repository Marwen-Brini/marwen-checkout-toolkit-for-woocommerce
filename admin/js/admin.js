/**
 * Checkout Toolkit for WooCommerce - Admin JavaScript
 *
 * @package CheckoutToolkitForWoo
 */

(function($) {
    'use strict';

    const WCTAdmin = {
        /**
         * Initialize admin functionality
         */
        init: function() {
            this.initDatepicker();
            this.initBlockedDates();
            this.initFieldSettings();
            this.initDeliveryMethodSettings();
            this.initDeliveryInstructionsSettings();
            this.initStoreLocationsSettings();
            this.initTimeWindowSettings();
        },

        /**
         * Initialize Flatpickr for admin date picker
         */
        initDatepicker: function() {
            const $datepicker = $('#checkout_toolkit_add_blocked_date');

            if ($datepicker.length && typeof flatpickr !== 'undefined') {
                flatpickr($datepicker[0], {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: 'F j, Y',
                    minDate: 'today',
                    onChange: function(selectedDates, dateStr) {
                        // Store the date value
                        $datepicker.data('selected-date', dateStr);
                    }
                });
            }
        },

        /**
         * Initialize blocked dates management
         */
        initBlockedDates: function() {
            const self = this;

            // Add date button
            $('#checkout_toolkit_add_date_btn').on('click', function() {
                const $input = $('#checkout_toolkit_add_blocked_date');
                const date = $input.data('selected-date') || $input.val();

                if (!date) {
                    alert(marwchtoAdmin.i18n.selectDate || 'Please select a date');
                    return;
                }

                // Check if date already exists
                if ($('#checkout_toolkit_blocked_dates_list').find('[data-date="' + date + '"]').length) {
                    alert('This date is already blocked.');
                    return;
                }

                self.addBlockedDate(date);
                $input.val('');
                $input.data('selected-date', '');

                // Clear flatpickr
                if ($input[0]._flatpickr) {
                    $input[0]._flatpickr.clear();
                }
            });

            // Remove date button
            $('#checkout_toolkit_blocked_dates_list').on('click', '.wct-remove-date', function() {
                if (confirm(marwchtoAdmin.i18n.confirmRemove || 'Are you sure?')) {
                    $(this).closest('.wct-blocked-date-item').fadeOut(200, function() {
                        $(this).remove();
                        self.updateNoDateMessage();
                    });
                }
            });
        },

        /**
         * Add a blocked date to the list
         */
        addBlockedDate: function(date) {
            const $list = $('#checkout_toolkit_blocked_dates_list');

            // Remove "no dates" message if present
            $list.find('.wct-no-dates').remove();

            // Format date for display
            const displayDate = this.formatDateForDisplay(date);

            const $item = $(`
                <div class="wct-blocked-date-item" data-date="${date}">
                    <span class="wct-date-display">${displayDate}</span>
                    <input type="hidden" name="checkout_toolkit_delivery_settings[blocked_dates][]" value="${date}">
                    <button type="button" class="wct-remove-date button-link button-link-delete">
                        Remove
                    </button>
                </div>
            `);

            $list.append($item);
            $item.hide().fadeIn(200);
        },

        /**
         * Format date for display
         */
        formatDateForDisplay: function(dateStr) {
            const date = new Date(dateStr + 'T00:00:00');
            const options = { year: 'numeric', month: 'long', day: 'numeric' };
            return date.toLocaleDateString(undefined, options);
        },

        /**
         * Update "no dates" message visibility
         */
        updateNoDateMessage: function() {
            const $list = $('#checkout_toolkit_blocked_dates_list');

            if ($list.find('.wct-blocked-date-item').length === 0) {
                $list.html('<p class="wct-no-dates">No dates blocked.</p>');
            }
        },

        /**
         * Initialize custom field settings (Field 1 & 2)
         */
        initFieldSettings: function() {
            const self = this;

            // Enable/disable field options based on enabled checkbox
            $('#wct-field-1-enabled').on('change', function() {
                self.toggleFieldOptions(1, $(this).is(':checked'));
            });

            $('#wct-field-2-enabled').on('change', function() {
                self.toggleFieldOptions(2, $(this).is(':checked'));
            });

            // Field type change handler
            $('.wct-field-type-radio').on('change', function() {
                var fieldNum = $(this).data('field');
                var fieldType = $(this).val();

                // Hide all conditional fields for this field number
                $('.wct-field-' + fieldNum + '-text, .wct-field-' + fieldNum + '-textarea, .wct-field-' + fieldNum + '-checkbox, .wct-field-' + fieldNum + '-select').removeClass('active');

                // Show relevant conditional fields
                $('.wct-field-' + fieldNum + '-' + fieldType).addClass('active');
            });

            // Add option button handler
            $('.wct-add-option').on('click', function(e) {
                e.preventDefault();
                var fieldNum = $(this).data('field');
                var wrapper = $(this).closest('.wct-select-options-wrapper').find('.wct-select-options-list');
                var optionName = fieldNum === 1 ? 'checkout_toolkit_field_settings' : 'checkout_toolkit_field_2_settings';
                var index = wrapper.find('.wct-select-option-row').length;

                var newRow = '<div class="wct-select-option-row">' +
                    '<input type="text" name="' + optionName + '[select_options][' + index + '][label]" value="" placeholder="' + (marwchtoAdmin.i18n.label || 'Label') + '" class="regular-text">' +
                    '<input type="text" name="' + optionName + '[select_options][' + index + '][value]" value="" placeholder="' + (marwchtoAdmin.i18n.value || 'Value') + '" class="regular-text">' +
                    '<a href="#" class="button-link-delete wct-remove-option" title="' + (marwchtoAdmin.i18n.remove || 'Remove') + '">&times;</a>' +
                    '</div>';

                wrapper.append(newRow);
            });

            // Remove option button handler
            $(document).on('click', '.wct-remove-option', function(e) {
                e.preventDefault();
                var wrapper = $(this).closest('.wct-select-options-list');

                // Keep at least one option row
                if (wrapper.find('.wct-select-option-row').length > 1) {
                    $(this).closest('.wct-select-option-row').remove();
                }
            });

            // Visibility type change handler
            $('.wct-visibility-type-radio').on('change', function() {
                var fieldNum = $(this).data('field');
                var visibilityType = $(this).val();

                // Hide all visibility options for this field
                $('.wct-visibility-' + fieldNum + '-products').removeClass('active');
                $('.wct-visibility-' + fieldNum + '-categories').removeClass('active');

                // Show/hide visibility mode section
                if (visibilityType === 'always') {
                    $('.wct-visibility-' + fieldNum + '-mode').hide();
                } else {
                    $('.wct-visibility-' + fieldNum + '-mode').show();
                    // Show the relevant visibility option
                    $('.wct-visibility-' + fieldNum + '-' + visibilityType).addClass('active');
                }
            });

            // Initialize WooCommerce enhanced select (product search)
            self.initProductSearch();
        },

        /**
         * Toggle field options enabled/disabled state
         */
        toggleFieldOptions: function(fieldNum, isEnabled) {
            var optionsWrapper = $('#wct-field-' + fieldNum + '-options');

            if (isEnabled) {
                optionsWrapper.removeClass('wct-field-options-disabled');
            } else {
                optionsWrapper.addClass('wct-field-options-disabled');
            }
        },

        /**
         * Initialize WooCommerce product search
         */
        initProductSearch: function() {
            if (typeof $.fn.selectWoo !== 'undefined') {
                $('select.wc-product-search').each(function() {
                    var $el = $(this);
                    // Only initialize if not already done
                    if (!$el.hasClass('select2-hidden-accessible')) {
                        $el.selectWoo({
                            minimumInputLength: 3,
                            allowClear: true,
                            placeholder: $el.data('placeholder'),
                            ajax: {
                                url: ajaxurl,
                                dataType: 'json',
                                delay: 250,
                                data: function(params) {
                                    return {
                                        term: params.term,
                                        action: $el.data('action'),
                                        security: marwchtoAdmin.nonces.searchProducts || ''
                                    };
                                },
                                processResults: function(data) {
                                    var terms = [];
                                    if (data) {
                                        $.each(data, function(id, text) {
                                            terms.push({
                                                id: id,
                                                text: text
                                            });
                                        });
                                    }
                                    return {
                                        results: terms
                                    };
                                },
                                cache: true
                            }
                        });
                    }
                });
            }
        },

        /**
         * Initialize delivery method settings
         */
        initDeliveryMethodSettings: function() {
            // Update preview when labels change
            $('#checkout_toolkit_dm_field_label').on('input', function() {
                $('#wct-preview-label').text($(this).val() || 'Fulfillment Method');
            });

            $('#checkout_toolkit_dm_delivery_label').on('input', function() {
                var label = $(this).val() || 'Delivery';
                $('#wct-preview-delivery-toggle').text(label);
                $('#wct-preview-delivery-radio').text(label);
            });

            $('#checkout_toolkit_dm_pickup_label').on('input', function() {
                var label = $(this).val() || 'Pickup';
                $('#wct-preview-pickup-toggle').text(label);
                $('#wct-preview-pickup-radio').text(label);
            });

            // Toggle between toggle/radio preview
            $('input[name="checkout_toolkit_delivery_method_settings[show_as]"]').on('change', function() {
                if ($(this).val() === 'toggle') {
                    $('#wct-preview-toggle').show();
                    $('#wct-preview-radio').hide();
                } else {
                    $('#wct-preview-toggle').hide();
                    $('#wct-preview-radio').show();
                }
            });
        },

        /**
         * Initialize delivery instructions settings
         */
        initDeliveryInstructionsSettings: function() {
            // Enable/disable options based on enabled checkbox
            $('#wct-di-enabled').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#wct-di-options').removeClass('wct-field-options-disabled');
                } else {
                    $('#wct-di-options').addClass('wct-field-options-disabled');
                }
            });

            // Update preview when labels change
            $('#wct_di_field_label').on('input', function() {
                $('#wct-preview-field-label').text($(this).val() || 'Delivery Instructions');
            });

            $('#wct_di_preset_label').on('input', function() {
                $('#wct-preview-preset-label').text($(this).val() || 'Common Instructions');
            });

            $('#wct_di_custom_label').on('input', function() {
                $('#wct-preview-custom-label').text($(this).val() || 'Additional Instructions');
            });

            $('#wct_di_custom_placeholder').on('input', function() {
                $('#wct-preview-custom-textarea').attr('placeholder', $(this).val() || 'Any other delivery instructions...');
            });

            // Add preset option
            $('#wct-add-preset-option').on('click', function(e) {
                e.preventDefault();
                var wrapper = $('.wct-preset-options-list');
                var index = wrapper.find('.wct-preset-option-row').length;

                var newRow = '<div class="wct-preset-option-row">' +
                    '<input type="text" name="checkout_toolkit_delivery_instructions_settings[preset_options][' + index + '][label]" value="" placeholder="' + (marwchtoAdmin.i18n.labelShownToCustomer || 'Label (shown to customer)') + '" class="regular-text">' +
                    '<input type="text" name="checkout_toolkit_delivery_instructions_settings[preset_options][' + index + '][value]" value="" placeholder="' + (marwchtoAdmin.i18n.valueStored || 'Value (stored)') + '" class="regular-text">' +
                    '<a href="#" class="button-link-delete wct-remove-preset-option" title="' + (marwchtoAdmin.i18n.remove || 'Remove') + '">&times;</a>' +
                    '</div>';

                wrapper.append(newRow);
            });

            // Remove preset option
            $(document).on('click', '.wct-remove-preset-option', function(e) {
                e.preventDefault();
                var wrapper = $(this).closest('.wct-preset-options-list');

                // Keep at least one option row
                if (wrapper.find('.wct-preset-option-row').length > 1) {
                    $(this).closest('.wct-preset-option-row').remove();
                }
            });
        },

        /**
         * Initialize store locations settings
         */
        initStoreLocationsSettings: function() {
            const self = this;

            // Enable/disable options based on enabled checkbox
            $('#wct-sl-enabled').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#wct-sl-options').removeClass('wct-field-options-disabled');
                } else {
                    $('#wct-sl-options').addClass('wct-field-options-disabled');
                }
            });

            // Update preview when field label changes
            $('#wct_sl_field_label').on('input', function() {
                $('#wct-preview-field-label').text($(this).val() || 'Pickup Location');
            });

            // Update preview dropdown when location names change
            $(document).on('input', '.wct-location-name', function() {
                self.updateLocationPreview();
            });

            // Add location
            $('#wct-add-location').on('click', function(e) {
                e.preventDefault();
                var wrapper = $('.wct-locations-list');
                var index = wrapper.find('.wct-location-row').length;

                var newRow = '<div class="wct-location-row">' +
                    '<div class="wct-location-header">' +
                        '<h4>' + (marwchtoAdmin.i18n.location || 'Location') + ' <span class="wct-location-number">' + (index + 1) + '</span></h4>' +
                        '<a href="#" class="button-link-delete wct-remove-location" title="' + (marwchtoAdmin.i18n.removeLocation || 'Remove location') + '">' +
                            (marwchtoAdmin.i18n.remove || 'Remove') +
                        '</a>' +
                    '</div>' +
                    '<div class="wct-location-fields">' +
                        '<div class="wct-location-field">' +
                            '<label>' + (marwchtoAdmin.i18n.locationId || 'Location ID') + '</label>' +
                            '<input type="text" name="checkout_toolkit_store_locations_settings[locations][' + index + '][id]" value="" placeholder="' + (marwchtoAdmin.i18n.locationIdPlaceholder || 'e.g., main-store (auto-generated if empty)') + '">' +
                        '</div>' +
                        '<div class="wct-location-field">' +
                            '<label>' + (marwchtoAdmin.i18n.storeName || 'Store Name') + ' <span style="color: #d63638;">*</span></label>' +
                            '<input type="text" name="checkout_toolkit_store_locations_settings[locations][' + index + '][name]" value="" placeholder="' + (marwchtoAdmin.i18n.storeNamePlaceholder || 'Store name (required)') + '" class="wct-location-name">' +
                        '</div>' +
                        '<div class="wct-location-field full-width">' +
                            '<label>' + (marwchtoAdmin.i18n.address || 'Address') + '</label>' +
                            '<input type="text" name="checkout_toolkit_store_locations_settings[locations][' + index + '][address]" value="" placeholder="' + (marwchtoAdmin.i18n.fullAddress || 'Full address') + '">' +
                        '</div>' +
                        '<div class="wct-location-field">' +
                            '<label>' + (marwchtoAdmin.i18n.phone || 'Phone') + '</label>' +
                            '<input type="text" name="checkout_toolkit_store_locations_settings[locations][' + index + '][phone]" value="" placeholder="' + (marwchtoAdmin.i18n.phoneNumber || 'Phone number') + '">' +
                        '</div>' +
                        '<div class="wct-location-field">' +
                            '<label>' + (marwchtoAdmin.i18n.hours || 'Hours') + '</label>' +
                            '<input type="text" name="checkout_toolkit_store_locations_settings[locations][' + index + '][hours]" value="" placeholder="' + (marwchtoAdmin.i18n.hoursPlaceholder || 'e.g., Mon-Fri: 9am-6pm') + '">' +
                        '</div>' +
                    '</div>' +
                '</div>';

                wrapper.append(newRow);
                self.updateLocationNumbers();
            });

            // Remove location
            $(document).on('click', '.wct-remove-location', function(e) {
                e.preventDefault();
                var wrapper = $(this).closest('.wct-locations-list');

                // Keep at least one location row
                if (wrapper.find('.wct-location-row').length > 1) {
                    $(this).closest('.wct-location-row').remove();
                    self.updateLocationNumbers();
                    self.updateLocationPreview();
                }
            });
        },

        /**
         * Update location preview dropdown
         */
        updateLocationPreview: function() {
            var $select = $('#wct-preview-location-select');
            if (!$select.length) return;

            var currentValue = $select.val();
            $select.find('option:not(:first)').remove();

            $('.wct-location-row').each(function() {
                var name = $(this).find('.wct-location-name').val();
                var id = $(this).find('input[name*="[id]"]').val();
                if (name) {
                    $select.append($('<option>', {
                        value: id || name.toLowerCase().replace(/\s+/g, '-'),
                        text: name
                    }));
                }
            });

            if (currentValue) {
                $select.val(currentValue);
            }
        },

        /**
         * Update location numbers
         */
        updateLocationNumbers: function() {
            $('.wct-location-row').each(function(index) {
                $(this).find('.wct-location-number').text(index + 1);
            });
        },

        /**
         * Initialize time window settings
         */
        initTimeWindowSettings: function() {
            const self = this;
            var slotIndex = $('.time-slot-row').length || 0;

            // Toggle form fields based on enabled state
            $('#time_window_enabled').on('change', function() {
                var enabled = $(this).is(':checked');
                var $fields = $(this).closest('.wct-settings-section').find('input:not(#time_window_enabled), select, button:not([type="submit"])');

                if (enabled) {
                    $fields.prop('disabled', false);
                } else {
                    $fields.prop('disabled', true);
                }
            });

            // Add new time slot
            $('#add-time-slot').on('click', function() {
                var html = '<div class="time-slot-row" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">' +
                    '<input type="text" name="checkout_toolkit_time_window_settings[time_slots][' + slotIndex + '][value]" ' +
                    'placeholder="' + (marwchtoAdmin.i18n.timeSlotValuePlaceholder || 'Value (e.g., morning)') + '" class="regular-text" style="width: 200px;">' +
                    '<input type="text" name="checkout_toolkit_time_window_settings[time_slots][' + slotIndex + '][label]" ' +
                    'placeholder="' + (marwchtoAdmin.i18n.timeSlotLabelPlaceholder || 'Label (e.g., Morning 9am-12pm)') + '" class="regular-text" style="width: 300px;">' +
                    '<button type="button" class="button remove-time-slot">' + (marwchtoAdmin.i18n.remove || 'Remove') + '</button>' +
                    '</div>';
                $('#time-slots-container').append(html);
                slotIndex++;
                self.updateTimeWindowPreview();
            });

            // Remove time slot
            $(document).on('click', '.remove-time-slot', function() {
                if ($('.time-slot-row').length > 1) {
                    $(this).closest('.time-slot-row').remove();
                    self.updateTimeWindowPreview();
                }
            });

            // Update preview on input changes
            $('#time_window_field_label').on('input', function() {
                $('#preview-time-window-label').contents().first().replaceWith($(this).val() + ' ');
            });

            $('#time_window_required').on('change', function() {
                var $label = $('#preview-time-window-label');
                var $asterisk = $label.find('span');
                if ($(this).is(':checked')) {
                    if ($asterisk.length === 0) {
                        $label.append('<span style="color: #cc0000;">*</span>');
                    }
                } else {
                    $asterisk.remove();
                }
            });

            $(document).on('input', '.time-slot-row input', function() {
                self.updateTimeWindowPreview();
            });
        },

        /**
         * Update time window preview select options
         */
        updateTimeWindowPreview: function() {
            var $select = $('#preview-time-window-select');
            if (!$select.length) return;

            $select.find('option:not(:first)').remove();

            $('.time-slot-row').each(function() {
                var value = $(this).find('input:first').val();
                var label = $(this).find('input:eq(1)').val();
                if (label) {
                    $select.append('<option value="' + value + '">' + label + '</option>');
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        WCTAdmin.init();
    });

})(jQuery);

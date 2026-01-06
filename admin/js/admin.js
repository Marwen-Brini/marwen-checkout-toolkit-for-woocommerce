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
            const $datepicker = $('#marwchto_add_blocked_date');

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
            $('#marwchto_add_date_btn').on('click', function() {
                const $input = $('#marwchto_add_blocked_date');
                const date = $input.data('selected-date') || $input.val();

                if (!date) {
                    alert(marwchtoAdmin.i18n.selectDate || 'Please select a date');
                    return;
                }

                // Check if date already exists
                if ($('#marwchto_blocked_dates_list').find('[data-date="' + date + '"]').length) {
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
            $('#marwchto_blocked_dates_list').on('click', '.marwchto-remove-date', function() {
                if (confirm(marwchtoAdmin.i18n.confirmRemove || 'Are you sure?')) {
                    $(this).closest('.marwchto-blocked-date-item').fadeOut(200, function() {
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
            const $list = $('#marwchto_blocked_dates_list');

            // Remove "no dates" message if present
            $list.find('.marwchto-no-dates').remove();

            // Format date for display
            const displayDate = this.formatDateForDisplay(date);

            const $item = $(`
                <div class="marwchto-blocked-date-item" data-date="${date}">
                    <span class="marwchto-date-display">${displayDate}</span>
                    <input type="hidden" name="marwchto_delivery_settings[blocked_dates][]" value="${date}">
                    <button type="button" class="marwchto-remove-date button-link button-link-delete">
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
            const $list = $('#marwchto_blocked_dates_list');

            if ($list.find('.marwchto-blocked-date-item').length === 0) {
                $list.html('<p class="marwchto-no-dates">No dates blocked.</p>');
            }
        },

        /**
         * Initialize custom field settings (Field 1 & 2)
         */
        initFieldSettings: function() {
            const self = this;

            // Enable/disable field options based on enabled checkbox
            $('#marwchto-field-1-enabled').on('change', function() {
                self.toggleFieldOptions(1, $(this).is(':checked'));
            });

            $('#marwchto-field-2-enabled').on('change', function() {
                self.toggleFieldOptions(2, $(this).is(':checked'));
            });

            // Field type change handler
            $('.marwchto-field-type-radio').on('change', function() {
                var fieldNum = $(this).data('field');
                var fieldType = $(this).val();

                // Hide all conditional fields for this field number
                $('.marwchto-field-' + fieldNum + '-text, .marwchto-field-' + fieldNum + '-textarea, .marwchto-field-' + fieldNum + '-checkbox, .marwchto-field-' + fieldNum + '-select').removeClass('active');

                // Show relevant conditional fields
                $('.marwchto-field-' + fieldNum + '-' + fieldType).addClass('active');
            });

            // Add option button handler
            $('.marwchto-add-option').on('click', function(e) {
                e.preventDefault();
                var fieldNum = $(this).data('field');
                var wrapper = $(this).closest('.marwchto-select-options-wrapper').find('.marwchto-select-options-list');
                var optionName = fieldNum === 1 ? 'marwchto_field_settings' : 'marwchto_field_2_settings';
                var index = wrapper.find('.marwchto-select-option-row').length;

                var newRow = '<div class="marwchto-select-option-row">' +
                    '<input type="text" name="' + optionName + '[select_options][' + index + '][label]" value="" placeholder="' + (marwchtoAdmin.i18n.label || 'Label') + '" class="regular-text">' +
                    '<input type="text" name="' + optionName + '[select_options][' + index + '][value]" value="" placeholder="' + (marwchtoAdmin.i18n.value || 'Value') + '" class="regular-text">' +
                    '<a href="#" class="button-link-delete marwchto-remove-option" title="' + (marwchtoAdmin.i18n.remove || 'Remove') + '">&times;</a>' +
                    '</div>';

                wrapper.append(newRow);
            });

            // Remove option button handler
            $(document).on('click', '.marwchto-remove-option', function(e) {
                e.preventDefault();
                var wrapper = $(this).closest('.marwchto-select-options-list');

                // Keep at least one option row
                if (wrapper.find('.marwchto-select-option-row').length > 1) {
                    $(this).closest('.marwchto-select-option-row').remove();
                }
            });

            // Visibility type change handler
            $('.marwchto-visibility-type-radio').on('change', function() {
                var fieldNum = $(this).data('field');
                var visibilityType = $(this).val();

                // Hide all visibility options for this field
                $('.marwchto-visibility-' + fieldNum + '-products').removeClass('active');
                $('.marwchto-visibility-' + fieldNum + '-categories').removeClass('active');

                // Show/hide visibility mode section
                if (visibilityType === 'always') {
                    $('.marwchto-visibility-' + fieldNum + '-mode').hide();
                } else {
                    $('.marwchto-visibility-' + fieldNum + '-mode').show();
                    // Show the relevant visibility option
                    $('.marwchto-visibility-' + fieldNum + '-' + visibilityType).addClass('active');
                }
            });

            // Initialize WooCommerce enhanced select (product search)
            self.initProductSearch();
        },

        /**
         * Toggle field options enabled/disabled state
         */
        toggleFieldOptions: function(fieldNum, isEnabled) {
            var optionsWrapper = $('#marwchto-field-' + fieldNum + '-options');

            if (isEnabled) {
                optionsWrapper.removeClass('marwchto-field-options-disabled');
            } else {
                optionsWrapper.addClass('marwchto-field-options-disabled');
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
            $('#marwchto_dm_field_label').on('input', function() {
                $('#marwchto-preview-label').text($(this).val() || 'Fulfillment Method');
            });

            $('#marwchto_dm_delivery_label').on('input', function() {
                var label = $(this).val() || 'Delivery';
                $('#marwchto-preview-delivery-toggle').text(label);
                $('#marwchto-preview-delivery-radio').text(label);
            });

            $('#marwchto_dm_pickup_label').on('input', function() {
                var label = $(this).val() || 'Pickup';
                $('#marwchto-preview-pickup-toggle').text(label);
                $('#marwchto-preview-pickup-radio').text(label);
            });

            // Toggle between toggle/radio preview
            $('input[name="marwchto_delivery_method_settings[show_as]"]').on('change', function() {
                if ($(this).val() === 'toggle') {
                    $('#marwchto-preview-toggle').show();
                    $('#marwchto-preview-radio').hide();
                } else {
                    $('#marwchto-preview-toggle').hide();
                    $('#marwchto-preview-radio').show();
                }
            });
        },

        /**
         * Initialize delivery instructions settings
         */
        initDeliveryInstructionsSettings: function() {
            // Enable/disable options based on enabled checkbox
            $('#marwchto-di-enabled').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#marwchto-di-options').removeClass('marwchto-field-options-disabled');
                } else {
                    $('#marwchto-di-options').addClass('marwchto-field-options-disabled');
                }
            });

            // Update preview when labels change
            $('#marwchto_di_field_label').on('input', function() {
                $('#marwchto-preview-field-label').text($(this).val() || 'Delivery Instructions');
            });

            $('#marwchto_di_preset_label').on('input', function() {
                $('#marwchto-preview-preset-label').text($(this).val() || 'Common Instructions');
            });

            $('#marwchto_di_custom_label').on('input', function() {
                $('#marwchto-preview-custom-label').text($(this).val() || 'Additional Instructions');
            });

            $('#marwchto_di_custom_placeholder').on('input', function() {
                $('#marwchto-preview-custom-textarea').attr('placeholder', $(this).val() || 'Any other delivery instructions...');
            });

            // Add preset option
            $('#marwchto-add-preset-option').on('click', function(e) {
                e.preventDefault();
                var wrapper = $('.marwchto-preset-options-list');
                var index = wrapper.find('.marwchto-preset-option-row').length;

                var newRow = '<div class="marwchto-preset-option-row">' +
                    '<input type="text" name="marwchto_delivery_instructions_settings[preset_options][' + index + '][label]" value="" placeholder="' + (marwchtoAdmin.i18n.labelShownToCustomer || 'Label (shown to customer)') + '" class="regular-text">' +
                    '<input type="text" name="marwchto_delivery_instructions_settings[preset_options][' + index + '][value]" value="" placeholder="' + (marwchtoAdmin.i18n.valueStored || 'Value (stored)') + '" class="regular-text">' +
                    '<a href="#" class="button-link-delete marwchto-remove-preset-option" title="' + (marwchtoAdmin.i18n.remove || 'Remove') + '">&times;</a>' +
                    '</div>';

                wrapper.append(newRow);
            });

            // Remove preset option
            $(document).on('click', '.marwchto-remove-preset-option', function(e) {
                e.preventDefault();
                var wrapper = $(this).closest('.marwchto-preset-options-list');

                // Keep at least one option row
                if (wrapper.find('.marwchto-preset-option-row').length > 1) {
                    $(this).closest('.marwchto-preset-option-row').remove();
                }
            });
        },

        /**
         * Initialize store locations settings
         */
        initStoreLocationsSettings: function() {
            const self = this;

            // Enable/disable options based on enabled checkbox
            $('#marwchto-sl-enabled').on('change', function() {
                if ($(this).is(':checked')) {
                    $('#marwchto-sl-options').removeClass('marwchto-field-options-disabled');
                } else {
                    $('#marwchto-sl-options').addClass('marwchto-field-options-disabled');
                }
            });

            // Update preview when field label changes
            $('#marwchto_sl_field_label').on('input', function() {
                $('#marwchto-preview-field-label').text($(this).val() || 'Pickup Location');
            });

            // Update preview dropdown when location names change
            $(document).on('input', '.marwchto-location-name', function() {
                self.updateLocationPreview();
            });

            // Add location
            $('#marwchto-add-location').on('click', function(e) {
                e.preventDefault();
                var wrapper = $('.marwchto-locations-list');
                var index = wrapper.find('.marwchto-location-row').length;

                var newRow = '<div class="marwchto-location-row">' +
                    '<div class="marwchto-location-header">' +
                        '<h4>' + (marwchtoAdmin.i18n.location || 'Location') + ' <span class="marwchto-location-number">' + (index + 1) + '</span></h4>' +
                        '<a href="#" class="button-link-delete marwchto-remove-location" title="' + (marwchtoAdmin.i18n.removeLocation || 'Remove location') + '">' +
                            (marwchtoAdmin.i18n.remove || 'Remove') +
                        '</a>' +
                    '</div>' +
                    '<div class="marwchto-location-fields">' +
                        '<div class="marwchto-location-field">' +
                            '<label>' + (marwchtoAdmin.i18n.locationId || 'Location ID') + '</label>' +
                            '<input type="text" name="marwchto_store_locations_settings[locations][' + index + '][id]" value="" placeholder="' + (marwchtoAdmin.i18n.locationIdPlaceholder || 'e.g., main-store (auto-generated if empty)') + '">' +
                        '</div>' +
                        '<div class="marwchto-location-field">' +
                            '<label>' + (marwchtoAdmin.i18n.storeName || 'Store Name') + ' <span style="color: #d63638;">*</span></label>' +
                            '<input type="text" name="marwchto_store_locations_settings[locations][' + index + '][name]" value="" placeholder="' + (marwchtoAdmin.i18n.storeNamePlaceholder || 'Store name (required)') + '" class="marwchto-location-name">' +
                        '</div>' +
                        '<div class="marwchto-location-field full-width">' +
                            '<label>' + (marwchtoAdmin.i18n.address || 'Address') + '</label>' +
                            '<input type="text" name="marwchto_store_locations_settings[locations][' + index + '][address]" value="" placeholder="' + (marwchtoAdmin.i18n.fullAddress || 'Full address') + '">' +
                        '</div>' +
                        '<div class="marwchto-location-field">' +
                            '<label>' + (marwchtoAdmin.i18n.phone || 'Phone') + '</label>' +
                            '<input type="text" name="marwchto_store_locations_settings[locations][' + index + '][phone]" value="" placeholder="' + (marwchtoAdmin.i18n.phoneNumber || 'Phone number') + '">' +
                        '</div>' +
                        '<div class="marwchto-location-field">' +
                            '<label>' + (marwchtoAdmin.i18n.hours || 'Hours') + '</label>' +
                            '<input type="text" name="marwchto_store_locations_settings[locations][' + index + '][hours]" value="" placeholder="' + (marwchtoAdmin.i18n.hoursPlaceholder || 'e.g., Mon-Fri: 9am-6pm') + '">' +
                        '</div>' +
                    '</div>' +
                '</div>';

                wrapper.append(newRow);
                self.updateLocationNumbers();
            });

            // Remove location
            $(document).on('click', '.marwchto-remove-location', function(e) {
                e.preventDefault();
                var wrapper = $(this).closest('.marwchto-locations-list');

                // Keep at least one location row
                if (wrapper.find('.marwchto-location-row').length > 1) {
                    $(this).closest('.marwchto-location-row').remove();
                    self.updateLocationNumbers();
                    self.updateLocationPreview();
                }
            });
        },

        /**
         * Update location preview dropdown
         */
        updateLocationPreview: function() {
            var $select = $('#marwchto-preview-location-select');
            if (!$select.length) return;

            var currentValue = $select.val();
            $select.find('option:not(:first)').remove();

            $('.marwchto-location-row').each(function() {
                var name = $(this).find('.marwchto-location-name').val();
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
            $('.marwchto-location-row').each(function(index) {
                $(this).find('.marwchto-location-number').text(index + 1);
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
                var $fields = $(this).closest('.marwchto-settings-section').find('input:not(#time_window_enabled), select, button:not([type="submit"])');

                if (enabled) {
                    $fields.prop('disabled', false);
                } else {
                    $fields.prop('disabled', true);
                }
            });

            // Add new time slot
            $('#add-time-slot').on('click', function() {
                var html = '<div class="time-slot-row" style="display: flex; gap: 10px; margin-bottom: 10px; align-items: center;">' +
                    '<input type="text" name="marwchto_time_window_settings[time_slots][' + slotIndex + '][value]" ' +
                    'placeholder="' + (marwchtoAdmin.i18n.timeSlotValuePlaceholder || 'Value (e.g., morning)') + '" class="regular-text" style="width: 200px;">' +
                    '<input type="text" name="marwchto_time_window_settings[time_slots][' + slotIndex + '][label]" ' +
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

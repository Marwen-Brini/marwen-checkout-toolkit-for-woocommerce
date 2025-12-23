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
                    alert(wctAdmin.i18n.selectDate || 'Please select a date');
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
                if (confirm(wctAdmin.i18n.confirmRemove || 'Are you sure?')) {
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
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        WCTAdmin.init();
    });

})(jQuery);

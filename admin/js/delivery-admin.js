/**
 * Delivery Admin JavaScript
 *
 * @package WooCheckoutToolkit
 */

(function($) {
    'use strict';

    /**
     * Delivery Status Manager
     */
    var WCTDelivery = {
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Quick status change from dropdown
            $(document).on('change', '.marwchto-delivery-status-select', this.handleStatusChange.bind(this));

            // Status change from order meta box
            $(document).on('change', '#marwchto_delivery_status', this.handleMetaBoxStatusChange.bind(this));
        },

        /**
         * Handle status change from list dropdown
         *
         * @param {Event} e Change event.
         */
        handleStatusChange: function(e) {
            var $select = $(e.target);
            var $wrapper = $select.closest('.marwchto-status-wrapper');
            var orderId = $wrapper.data('order-id');
            var newStatus = $select.val();

            if (!orderId || !newStatus) {
                return;
            }

            this.updateStatus(orderId, newStatus, $wrapper);
        },

        /**
         * Handle status change from order meta box
         *
         * @param {Event} e Change event.
         */
        handleMetaBoxStatusChange: function(e) {
            var $select = $(e.target);
            var orderId = $select.data('order-id');
            var newStatus = $select.val();

            if (!orderId || !newStatus) {
                return;
            }

            var $wrapper = $select.closest('.marwchto-order-delivery-meta');
            this.updateStatus(orderId, newStatus, $wrapper);
        },

        /**
         * Update delivery status via AJAX
         *
         * @param {number} orderId Order ID.
         * @param {string} status New status.
         * @param {jQuery} $wrapper Container element.
         */
        updateStatus: function(orderId, status, $wrapper) {
            var self = this;

            // Add loading state
            $wrapper.addClass('marwchto-quick-status-updating');

            $.ajax({
                url: marwchtoDelivery.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'marwchto_update_delivery_status',
                    nonce: marwchtoDelivery.nonce,
                    order_id: orderId,
                    status: status
                },
                success: function(response) {
                    $wrapper.removeClass('marwchto-quick-status-updating');

                    if (response.success) {
                        // Update badge if present
                        var $badge = $wrapper.find('.marwchto-delivery-status-badge');
                        if ($badge.length && response.data.badge) {
                            $badge.replaceWith(response.data.badge);
                        }

                        // Show success message
                        self.showNotice('success', response.data.message || marwchtoDelivery.i18n.statusUpdated);
                    } else {
                        self.showNotice('error', response.data.message || marwchtoDelivery.i18n.error);
                    }
                },
                error: function() {
                    $wrapper.removeClass('marwchto-quick-status-updating');
                    self.showNotice('error', marwchtoDelivery.i18n.error);
                }
            });
        },

        /**
         * Show admin notice
         *
         * @param {string} type Notice type (success, error).
         * @param {string} message Notice message.
         */
        showNotice: function(type, message) {
            var $notice = $('<div class="notice notice-' + type + ' is-dismissible"><p>' + message + '</p></div>');

            // Remove existing notices
            $('.marwchto-delivery-dashboard .notice, .woocommerce-layout__notice-list .notice').remove();

            // Add new notice
            if ($('.marwchto-delivery-dashboard').length) {
                $('.marwchto-delivery-dashboard h1').after($notice);
            } else {
                // For order page
                $('.wrap h1, .woocommerce-layout__header').first().after($notice);
            }

            // Auto dismiss after 3 seconds
            setTimeout(function() {
                $notice.fadeOut(function() {
                    $(this).remove();
                });
            }, 3000);
        }
    };

    /**
     * Calendar functionality
     */
    var WCTCalendar = {
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Click on calendar day with deliveries
            $(document).on('click', '.marwchto-calendar td.has-deliveries', function(e) {
                // Don't trigger if clicking on the link
                if ($(e.target).closest('a').length) {
                    return;
                }

                var date = $(this).data('date');
                if (date) {
                    window.location.href = marwchtoDelivery.ajaxUrl.replace('admin-ajax.php', 'admin.php') +
                        '?page=marwchto-deliveries&tab=list&filter_date=' + date;
                }
            });
        }
    };

    /**
     * Bulk actions
     */
    var WCTBulkActions = {
        /**
         * Initialize
         */
        init: function() {
            this.bindEvents();
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Confirm bulk status change
            $(document).on('submit', '.marwchto-delivery-dashboard form', function(e) {
                var action = $(this).find('select[name="action"]').val();

                if (action && action.indexOf('set_status_') === 0) {
                    var checked = $(this).find('input[name="order_ids[]"]:checked').length;

                    if (checked === 0) {
                        e.preventDefault();
                        alert('Please select at least one order.');
                        return false;
                    }
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        WCTDelivery.init();
        WCTCalendar.init();
        WCTBulkActions.init();
    });

})(jQuery);

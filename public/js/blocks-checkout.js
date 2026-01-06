/**
 * Checkout Toolkit - WooCommerce Blocks Integration
 *
 * Supports position-based field rendering in blocks checkout.
 *
 * @package WooCheckoutToolkit
 */

(function () {
    'use strict';

    // Defensive checks for required globals
    if (typeof window.wp === 'undefined' || typeof window.wp.plugins === 'undefined') {
        console.error('Checkout Toolkit: wp.plugins not available');
        return;
    }

    if (typeof window.wc === 'undefined' || typeof window.wc.blocksCheckout === 'undefined') {
        console.error('Checkout Toolkit: wc.blocksCheckout not available');
        return;
    }

    const { registerPlugin } = window.wp.plugins;
    const { ExperimentalOrderMeta } = window.wc.blocksCheckout;
    const { useEffect, useState, useRef, createElement: el } = window.wp.element;

    // Verify ExperimentalOrderMeta is available
    if (typeof ExperimentalOrderMeta === 'undefined') {
        console.error('Checkout Toolkit: ExperimentalOrderMeta slot not available');
        return;
    }

    // Get settings from WooCommerce settings API or fallback to wp_localize_script
    let settings = {};

    if (typeof window.wc !== 'undefined' && typeof window.wc.wcSettings !== 'undefined' && typeof window.wc.wcSettings.getSetting === 'function') {
        settings = window.wc.wcSettings.getSetting('checkout-toolkit_data', {});
    }

    if (!settings || Object.keys(settings).length === 0) {
        settings = window.marwchtoData || {};
    }

    if (!settings || Object.keys(settings).length === 0) {
        return;
    }

    const { orderNotes, deliveryMethod, deliveryInstructions, timeWindow, storeLocations, delivery, estimatedDelivery, customField, customField2, cart, i18n } = settings;

    /**
     * Check if a custom field should be shown based on visibility settings
     *
     * @param {Object} fieldConfig - The field configuration with visibility settings
     * @returns {boolean} Whether the field should be visible
     */
    const shouldShowFieldByVisibility = (fieldConfig) => {
        const { visibilityType, visibilityProducts, visibilityCategories, visibilityMode } = fieldConfig || {};

        // Default to always visible
        if (!visibilityType || visibilityType === 'always') {
            return true;
        }

        // Get cart data
        const cartProductIds = (cart?.productIds || []).map(id => parseInt(id, 10));
        const cartCategoryIds = (cart?.categoryIds || []).map(id => parseInt(id, 10));

        // Empty cart handling
        if (cartProductIds.length === 0) {
            return visibilityMode !== 'show';
        }

        let hasMatch = false;

        if (visibilityType === 'products') {
            const targetIds = (visibilityProducts || []).map(id => parseInt(id, 10));
            hasMatch = cartProductIds.some(id => targetIds.includes(id));
        } else if (visibilityType === 'categories') {
            const targetIds = (visibilityCategories || []).map(id => parseInt(id, 10));
            hasMatch = cartCategoryIds.some(id => targetIds.includes(id));
        }

        // Apply visibility mode
        if (visibilityMode === 'hide') {
            return !hasMatch; // Hide when match = show when no match
        }

        return hasMatch; // Show when match
    };

    /**
     * Position mapping: PHP hooks to DOM selectors
     * Each entry: { selector, insertPosition: 'before' | 'after' | 'prepend' | 'append' }
     */
    const positionMap = {
        'woocommerce_before_checkout_billing_form': {
            selector: '.wp-block-woocommerce-checkout-billing-address-block',
            insertPosition: 'before'
        },
        'woocommerce_after_checkout_billing_form': {
            selector: '.wp-block-woocommerce-checkout-billing-address-block',
            insertPosition: 'after'
        },
        'woocommerce_before_checkout_shipping_form': {
            selector: '.wp-block-woocommerce-checkout-shipping-address-block',
            insertPosition: 'before'
        },
        'woocommerce_after_checkout_shipping_form': {
            selector: '.wp-block-woocommerce-checkout-shipping-address-block',
            insertPosition: 'after'
        },
        'woocommerce_before_order_notes': {
            selector: '.wp-block-woocommerce-checkout-order-note-block',
            insertPosition: 'before'
        },
        'woocommerce_after_order_notes': {
            selector: '.wp-block-woocommerce-checkout-order-note-block',
            insertPosition: 'after'
        },
        'woocommerce_review_order_before_cart_contents': {
            selector: '.wc-block-components-order-summary',
            insertPosition: 'before'
        },
        'woocommerce_review_order_after_cart_contents': {
            selector: '.wc-block-components-order-summary',
            insertPosition: 'after'
        },
        'woocommerce_review_order_before_shipping': {
            selector: '.wc-block-components-totals-shipping',
            insertPosition: 'before'
        },
        'woocommerce_review_order_after_shipping': {
            selector: '.wc-block-components-totals-shipping',
            insertPosition: 'after'
        },
        'woocommerce_review_order_before_order_total': {
            selector: '.wc-block-components-totals-footer-item',
            insertPosition: 'before'
        },
        'woocommerce_review_order_before_submit': {
            selector: '.wp-block-woocommerce-checkout-actions-block',
            insertPosition: 'before'
        }
    };

    /**
     * Insert element at position relative to target
     */
    const insertAtPosition = (container, targetSelector, position) => {
        const target = document.querySelector(targetSelector);
        if (!target) return false;

        switch (position) {
            case 'before':
                target.parentNode.insertBefore(container, target);
                break;
            case 'after':
                target.parentNode.insertBefore(container, target.nextSibling);
                break;
            case 'prepend':
                target.insertBefore(container, target.firstChild);
                break;
            case 'append':
                target.appendChild(container);
                break;
            default:
                target.parentNode.insertBefore(container, target.nextSibling);
        }
        return true;
    };

    /**
     * Shared extension data state management with debouncing to reduce re-renders
     */
    const extensionDataState = {};
    let extensionDataTimeout = null;
    let pendingExtensionData = {};

    const flushExtensionData = () => {
        if (Object.keys(pendingExtensionData).length === 0) return;

        const { dispatch } = wp.data;
        const checkoutStore = dispatch('wc/store/checkout');
        if (checkoutStore) {
            const dataToSend = { ...pendingExtensionData };
            pendingExtensionData = {};

            if (typeof checkoutStore.setExtensionData === 'function') {
                checkoutStore.setExtensionData('marwchto', dataToSend);
            } else if (typeof checkoutStore.__internalSetExtensionData === 'function') {
                checkoutStore.__internalSetExtensionData('marwchto', dataToSend);
            }
        }
    };

    const setExtensionData = (namespace, key, value) => {
        const stringValue = value === null || value === undefined ? '' : String(value);

        // Skip if value hasn't changed
        if (extensionDataState[key] === stringValue) return;

        extensionDataState[key] = stringValue;
        pendingExtensionData[key] = stringValue;

        // Debounce updates to reduce re-renders
        if (extensionDataTimeout) {
            clearTimeout(extensionDataTimeout);
        }
        extensionDataTimeout = setTimeout(flushExtensionData, 50);
    };

    /**
     * Customize Order Notes field for blocks checkout
     */
    const customizeOrderNotes = () => {
        if (!orderNotes || !orderNotes.enabled) return;

        const customizeElements = () => {
            const orderNotesSection = document.querySelector('.wp-block-woocommerce-checkout-order-note-block');
            if (!orderNotesSection) return;

            if (orderNotes.customLabel) {
                const labels = orderNotesSection.querySelectorAll('label, .wc-block-components-checkbox__label');
                labels.forEach(label => {
                    if (!label.dataset.wctCustomized) {
                        const span = label.querySelector('span');
                        if (span && !span.querySelector('input')) {
                            span.textContent = orderNotes.customLabel;
                        } else if (label.childNodes.length > 0) {
                            label.childNodes.forEach(node => {
                                if (node.nodeType === Node.TEXT_NODE && node.textContent.trim()) {
                                    node.textContent = orderNotes.customLabel;
                                }
                            });
                        }
                        label.dataset.wctCustomized = 'true';
                    }
                });
            }

            if (orderNotes.customPlaceholder) {
                const textareas = orderNotesSection.querySelectorAll('textarea');
                textareas.forEach(textarea => {
                    if (!textarea.dataset.wctCustomized) {
                        textarea.placeholder = orderNotes.customPlaceholder;
                        textarea.dataset.wctCustomized = 'true';
                    }
                });
            }
        };

        customizeElements();
        const observer = new MutationObserver(customizeElements);
        observer.observe(document.body, { childList: true, subtree: true, attributes: true });
        setTimeout(customizeElements, 100);
        setTimeout(customizeElements, 500);
    };

    // Initialize order notes customization
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', customizeOrderNotes);
    } else {
        customizeOrderNotes();
    }

    /**
     * Delivery Method Toggle Component
     */
    const DeliveryMethodComponent = () => {
        const [selectedMethod, setSelectedMethod] = useState(deliveryMethod?.defaultMethod || 'delivery');

        const handleChange = (method) => {
            setSelectedMethod(method);
            setExtensionData('marwchto', 'delivery_method', method);

            // Dispatch custom event for other components to listen to
            document.dispatchEvent(new CustomEvent('wct_delivery_method_changed', {
                detail: { method }
            }));
        };

        if (!deliveryMethod || !deliveryMethod.enabled) {
            return null;
        }

        const toggleStyles = {
            display: 'flex',
            border: '1px solid #ddd',
            borderRadius: '4px',
            overflow: 'hidden'
        };

        const optionStyles = (isActive) => ({
            flex: 1,
            textAlign: 'center',
            padding: '12px 20px',
            cursor: 'pointer',
            background: isActive ? '#2271b1' : '#f9f9f9',
            color: isActive ? '#fff' : 'inherit',
            fontWeight: '500',
            border: 'none',
            borderRight: '1px solid #ddd',
            transition: 'all 0.2s ease'
        });

        const renderToggle = () => {
            if (deliveryMethod.showAs === 'radio') {
                return el('div', { className: 'marwchto-delivery-method-radio' },
                    el('label', { style: { marginBottom: '8px', display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer' } },
                        el('input', {
                            type: 'radio',
                            name: 'marwchto_delivery_method',
                            value: 'delivery',
                            checked: selectedMethod === 'delivery',
                            onChange: () => handleChange('delivery')
                        }),
                        deliveryMethod.deliveryLabel || 'Delivery'
                    ),
                    el('label', { style: { display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer' } },
                        el('input', {
                            type: 'radio',
                            name: 'marwchto_delivery_method',
                            value: 'pickup',
                            checked: selectedMethod === 'pickup',
                            onChange: () => handleChange('pickup')
                        }),
                        deliveryMethod.pickupLabel || 'Pickup'
                    )
                );
            }

            return el('div', { className: 'marwchto-delivery-method-toggle', style: toggleStyles },
                el('button', {
                    type: 'button',
                    style: optionStyles(selectedMethod === 'delivery'),
                    onClick: () => handleChange('delivery')
                }, deliveryMethod.deliveryLabel || 'Delivery'),
                el('button', {
                    type: 'button',
                    style: { ...optionStyles(selectedMethod === 'pickup'), borderRight: 'none' },
                    onClick: () => handleChange('pickup')
                }, deliveryMethod.pickupLabel || 'Pickup')
            );
        };

        return el('div', { className: 'marwchto-delivery-method-block wc-block-components-checkout-step', style: { marginTop: '16px', marginBottom: '16px' } },
            el('div', { className: 'wc-block-components-checkout-step__heading' },
                el('h2', { className: 'wc-block-components-title wc-block-components-checkout-step__title' },
                    deliveryMethod.fieldLabel || 'Fulfillment Method'
                )
            ),
            el('div', { className: 'wc-block-components-checkout-step__container' },
                el('div', { className: 'wc-block-components-checkout-step__content' }, renderToggle())
            )
        );
    };

    /**
     * Delivery Instructions Component
     * Shows preset dropdown + custom textarea
     * Only visible when delivery is selected (hidden for pickup)
     */
    const DeliveryInstructionsComponent = () => {
        const [presetValue, setPresetValue] = useState('');
        const [customValue, setCustomValue] = useState('');
        const [charCount, setCharCount] = useState(0);
        const initialMethod = extensionDataState.delivery_method || (deliveryMethod?.defaultMethod || 'delivery');
        const [isVisible, setIsVisible] = useState(initialMethod !== 'pickup');

        // Listen for delivery method changes via custom event
        useEffect(() => {
            const handleMethodChange = (e) => {
                const method = e.detail?.method || 'delivery';
                setIsVisible(method !== 'pickup');
            };

            document.addEventListener('wct_delivery_method_changed', handleMethodChange);
            return () => document.removeEventListener('wct_delivery_method_changed', handleMethodChange);
        }, []);

        const handlePresetChange = (e) => {
            const val = e.target.value;
            setPresetValue(val);
            setExtensionData('marwchto', 'delivery_instructions_preset', val);
        };

        const handleCustomChange = (e) => {
            let val = e.target.value;
            const maxLen = deliveryInstructions.maxLength || 0;

            if (maxLen > 0 && val.length > maxLen) {
                val = val.substring(0, maxLen);
            }

            setCustomValue(val);
            setCharCount(val.length);
            setExtensionData('marwchto', 'delivery_instructions_custom', val);
        };

        if (!deliveryInstructions || !deliveryInstructions.enabled) return null;
        if (!isVisible) return null;

        const inputStyles = {
            width: '100%',
            padding: '12px 16px',
            border: '1px solid #8c8f94',
            borderRadius: '4px',
            fontSize: '16px',
            fontFamily: 'inherit'
        };

        const presetOptions = deliveryInstructions.presetOptions || [];

        return el('div', {
            className: 'marwchto-delivery-instructions-block wc-block-components-checkout-step',
            style: { marginTop: '16px', marginBottom: '16px' }
        },
            el('div', { className: 'wc-block-components-checkout-step__heading' },
                el('h2', { className: 'wc-block-components-title wc-block-components-checkout-step__title' },
                    deliveryInstructions.fieldLabel || 'Delivery Instructions',
                    deliveryInstructions.required && el('span', { style: { color: '#cc0000' } }, ' *')
                )
            ),
            el('div', { className: 'wc-block-components-checkout-step__container' },
                el('div', { className: 'wc-block-components-checkout-step__content' },
                    // Preset dropdown
                    el('div', { style: { marginBottom: '15px' } },
                        el('label', {
                            htmlFor: 'marwchto-delivery-instructions-preset',
                            style: { display: 'block', marginBottom: '5px', fontWeight: '500' }
                        }, deliveryInstructions.presetLabel || 'Common Instructions'),
                        el('select', {
                            id: 'marwchto-delivery-instructions-preset',
                            value: presetValue,
                            onChange: handlePresetChange,
                            required: deliveryInstructions.required,
                            style: inputStyles
                        },
                            el('option', { value: '' }, i18n?.selectOption || 'Select an option...'),
                            presetOptions.map(opt =>
                                opt.label ? el('option', { key: opt.value, value: opt.value }, opt.label) : null
                            )
                        )
                    ),
                    // Custom textarea
                    el('div', null,
                        el('label', {
                            htmlFor: 'marwchto-delivery-instructions-custom',
                            style: { display: 'block', marginBottom: '5px', fontWeight: '500' }
                        }, deliveryInstructions.customLabel || 'Additional Instructions'),
                        el('textarea', {
                            id: 'marwchto-delivery-instructions-custom',
                            placeholder: deliveryInstructions.customPlaceholder || '',
                            value: customValue,
                            onChange: handleCustomChange,
                            rows: 3,
                            maxLength: deliveryInstructions.maxLength > 0 ? deliveryInstructions.maxLength : undefined,
                            style: { ...inputStyles, resize: 'vertical', minHeight: '80px' }
                        }),
                        deliveryInstructions.maxLength > 0 && el('div', {
                            style: { marginTop: '8px', fontSize: '14px', color: '#757575', textAlign: 'right' }
                        }, (deliveryInstructions.maxLength - charCount) + ' ' + (i18n?.charactersRemaining || 'characters remaining'))
                    )
                )
            )
        );
    };

    /**
     * Time Window Selection Component
     * Shows a dropdown for selecting preferred delivery time
     * Only visible when delivery is selected (hidden for pickup) if showOnlyWithDelivery is true
     */
    const TimeWindowComponent = () => {
        const [selectedTime, setSelectedTime] = useState('');
        const initialMethod = extensionDataState.delivery_method || (deliveryMethod?.defaultMethod || 'delivery');
        const showOnlyDelivery = timeWindow?.showOnlyWithDelivery ?? true;
        const [isVisible, setIsVisible] = useState(!showOnlyDelivery || initialMethod !== 'pickup');

        // Listen for delivery method changes
        useEffect(() => {
            if (!showOnlyDelivery) return;

            const handleMethodChange = (e) => {
                const method = e.detail?.method || 'delivery';
                setIsVisible(method !== 'pickup');
            };

            document.addEventListener('wct_delivery_method_changed', handleMethodChange);
            return () => document.removeEventListener('wct_delivery_method_changed', handleMethodChange);
        }, [showOnlyDelivery]);

        const handleChange = (e) => {
            const val = e.target.value;
            setSelectedTime(val);
            setExtensionData('marwchto', 'time_window', val);
        };

        if (!timeWindow || !timeWindow.enabled) return null;
        if (!isVisible) return null;

        const timeSlots = timeWindow.timeSlots || [];
        if (timeSlots.length === 0) return null;

        const selectStyles = {
            width: '100%',
            padding: '12px 16px',
            border: '1px solid #8c8f94',
            borderRadius: '4px',
            fontSize: '16px',
            backgroundColor: '#fff',
            cursor: 'pointer',
            appearance: 'none',
            backgroundImage: 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'%3E%3Cpath fill=\'%23333\' d=\'M6 8L1 3h10z\'/%3E%3C/svg%3E")',
            backgroundRepeat: 'no-repeat',
            backgroundPosition: 'right 16px center',
            paddingRight: '40px'
        };

        return el('div', {
            className: 'marwchto-time-window-block wc-block-components-checkout-step',
            style: { marginTop: '16px', marginBottom: '16px' }
        },
            el('div', { className: 'wc-block-components-checkout-step__heading' },
                el('h2', { className: 'wc-block-components-title wc-block-components-checkout-step__title' },
                    timeWindow.fieldLabel || 'Preferred Time',
                    timeWindow.required && el('span', { style: { color: '#cc0000' } }, ' *')
                )
            ),
            el('div', { className: 'wc-block-components-checkout-step__container' },
                el('div', { className: 'wc-block-components-checkout-step__content' },
                    el('select', {
                        value: selectedTime,
                        onChange: handleChange,
                        required: timeWindow.required,
                        style: selectStyles
                    },
                        el('option', { value: '' }, i18n?.selectTime || 'Select a time...'),
                        timeSlots.map(slot =>
                            el('option', { key: slot.value, value: slot.value }, slot.label)
                        )
                    )
                )
            )
        );
    };

    /**
     * Store Location Selector Component
     * OPPOSITE visibility: Only visible when PICKUP is selected (hidden for delivery)
     */
    const StoreLocationComponent = () => {
        const [selectedLocation, setSelectedLocation] = useState('');
        const initialMethod = extensionDataState.delivery_method || (deliveryMethod?.defaultMethod || 'delivery');
        // OPPOSITE visibility: Show when pickup, hide when delivery
        const [isVisible, setIsVisible] = useState(initialMethod === 'pickup');

        // Listen for delivery method changes
        useEffect(() => {
            const handleMethodChange = (e) => {
                const method = e.detail?.method || 'delivery';
                setIsVisible(method === 'pickup'); // Show ONLY for pickup
            };

            document.addEventListener('wct_delivery_method_changed', handleMethodChange);
            return () => document.removeEventListener('wct_delivery_method_changed', handleMethodChange);
        }, []);

        const handleChange = (e) => {
            const val = e.target.value;
            setSelectedLocation(val);
            setExtensionData('marwchto', 'store_location', val);
        };

        if (!storeLocations || !storeLocations.enabled) return null;
        if (!isVisible) return null;

        const locations = storeLocations.locations || [];
        if (locations.length === 0) return null;

        const selectStyles = {
            width: '100%',
            padding: '12px 16px',
            border: '1px solid #8c8f94',
            borderRadius: '4px',
            fontSize: '16px',
            backgroundColor: '#fff',
            cursor: 'pointer',
            appearance: 'none',
            backgroundImage: 'url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'12\' height=\'12\' viewBox=\'0 0 12 12\'%3E%3Cpath fill=\'%23333\' d=\'M6 8L1 3h10z\'/%3E%3C/svg%3E")',
            backgroundRepeat: 'no-repeat',
            backgroundPosition: 'right 16px center',
            paddingRight: '40px'
        };

        // Find selected location details
        const selectedLocationData = locations.find(loc => loc.id === selectedLocation);

        return el('div', {
            className: 'marwchto-store-location-block wc-block-components-checkout-step',
            style: { marginTop: '16px', marginBottom: '16px' }
        },
            el('div', { className: 'wc-block-components-checkout-step__heading' },
                el('h2', { className: 'wc-block-components-title wc-block-components-checkout-step__title' },
                    storeLocations.fieldLabel || 'Pickup Location',
                    storeLocations.required && el('span', { style: { color: '#cc0000' } }, ' *')
                )
            ),
            el('div', { className: 'wc-block-components-checkout-step__container' },
                el('div', { className: 'wc-block-components-checkout-step__content' },
                    el('select', {
                        value: selectedLocation,
                        onChange: handleChange,
                        required: storeLocations.required,
                        style: selectStyles
                    },
                        el('option', { value: '' }, i18n?.selectLocation || 'Select a location...'),
                        locations.map(loc =>
                            el('option', { key: loc.id, value: loc.id }, loc.name)
                        )
                    ),
                    // Show location details when selected
                    selectedLocationData && (selectedLocationData.address || selectedLocationData.phone || selectedLocationData.hours) &&
                    el('div', {
                        style: {
                            marginTop: '15px',
                            padding: '15px',
                            background: '#f9f9f9',
                            border: '1px solid #e0e0e0',
                            borderRadius: '4px'
                        }
                    },
                        selectedLocationData.address && el('div', { style: { marginBottom: '8px' } },
                            el('strong', null, i18n?.address || 'Address:'), ' ', selectedLocationData.address
                        ),
                        selectedLocationData.phone && el('div', { style: { marginBottom: '8px' } },
                            el('strong', null, i18n?.phone || 'Phone:'), ' ', selectedLocationData.phone
                        ),
                        selectedLocationData.hours && el('div', null,
                            el('strong', null, i18n?.hours || 'Hours:'), ' ', selectedLocationData.hours
                        )
                    )
                )
            )
        );
    };

    /**
     * Estimated Delivery Message Component
     * Shows earliest available delivery date based on cutoff time
     */
    const EstimatedDeliveryMessage = () => {
        const [message, setMessage] = useState('');

        useEffect(() => {
            if (!estimatedDelivery || !estimatedDelivery.enabled || !delivery?.enabled) {
                return;
            }

            const now = new Date();
            const cutoffParts = (estimatedDelivery.cutoffTime || '14:00').split(':');
            const cutoffDate = new Date();
            cutoffDate.setHours(parseInt(cutoffParts[0], 10), parseInt(cutoffParts[1], 10), 0, 0);

            const isPastCutoff = now >= cutoffDate;
            const earliestDate = isPastCutoff
                ? estimatedDelivery.earliestDateAfterCutoff
                : estimatedDelivery.earliestDate;

            if (!earliestDate) {
                setMessage('');
                return;
            }

            // Format the date for display
            const dateObj = new Date(earliestDate + 'T00:00:00');
            const formattedDate = dateObj.toLocaleDateString(undefined, {
                weekday: 'long',
                month: 'long',
                day: 'numeric'
            });

            let msg = isPastCutoff
                ? (estimatedDelivery.message || 'Order now for delivery as early as {date}')
                : (estimatedDelivery.cutoffMessage || 'Order by {time} for delivery as early as {date}');

            msg = msg.replace('{date}', formattedDate);

            if (!isPastCutoff) {
                const formattedTime = cutoffDate.toLocaleTimeString(undefined, {
                    hour: 'numeric',
                    minute: '2-digit'
                });
                msg = msg.replace('{time}', formattedTime);
            }

            setMessage(msg);
        }, []);

        if (!estimatedDelivery || !estimatedDelivery.enabled || !delivery?.enabled || !message) {
            return null;
        }

        return el('p', {
            className: 'marwchto-estimated-delivery-message',
            style: {
                margin: '0 0 16px 0',
                padding: '12px 16px',
                background: '#f0f7ff',
                border: '1px solid #c3d9f0',
                borderRadius: '4px',
                color: '#1e4a7f',
                fontSize: '14px',
                lineHeight: '1.4'
            }
        }, message);
    };

    /**
     * Delivery Date Field Component
     * Only visible when delivery is selected (hidden for pickup)
     */
    const DeliveryDateField = () => {
        const [selectedDate, setSelectedDate] = useState('');
        const inputRef = useRef(null);
        const flatpickrRef = useRef(null);
        const initialMethod = extensionDataState.delivery_method || (deliveryMethod?.defaultMethod || 'delivery');
        const [isVisible, setIsVisible] = useState(initialMethod !== 'pickup');

        // Listen for delivery method changes
        useEffect(() => {
            const handleMethodChange = (e) => {
                const method = e.detail?.method || 'delivery';
                setIsVisible(method !== 'pickup');
            };

            document.addEventListener('wct_delivery_method_changed', handleMethodChange);
            return () => document.removeEventListener('wct_delivery_method_changed', handleMethodChange);
        }, []);

        useEffect(() => {
            const initPicker = () => {
                if (!inputRef.current || !window.flatpickr || flatpickrRef.current) return;

                const today = new Date();
                const minDate = new Date(today);
                minDate.setDate(minDate.getDate() + (delivery.minLeadDays || 0));
                const maxDate = new Date(today);
                maxDate.setDate(maxDate.getDate() + (delivery.maxFutureDays || 30));

                const disableWeekdays = (date) => {
                    if (delivery.disabledWeekdays && delivery.disabledWeekdays.length > 0) {
                        return delivery.disabledWeekdays.includes(date.getDay());
                    }
                    return false;
                };

                const disabledDates = delivery.blockedDates || [];

                flatpickrRef.current = flatpickr(inputRef.current, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: delivery.dateFormat || 'F j, Y',
                    minDate: minDate,
                    maxDate: maxDate,
                    disable: [disableWeekdays, ...disabledDates],
                    locale: { firstDayOfWeek: delivery.firstDayOfWeek || 0 },
                    onChange: (selectedDates, dateStr) => {
                        setSelectedDate(dateStr);
                        setExtensionData('marwchto', 'delivery_date', dateStr);
                    }
                });
            };

            if (window.flatpickr) {
                initPicker();
            } else {
                const checkInterval = setInterval(() => {
                    if (window.flatpickr) {
                        clearInterval(checkInterval);
                        initPicker();
                    }
                }, 100);
                setTimeout(() => clearInterval(checkInterval), 10000);
            }

            return () => {
                if (flatpickrRef.current) {
                    flatpickrRef.current.destroy();
                    flatpickrRef.current = null;
                }
            };
        }, []);

        if (!delivery || !delivery.enabled) return null;
        if (!isVisible) return null;

        return el('div', { className: 'marwchto-delivery-date-block wc-block-components-checkout-step', style: { marginTop: '16px', marginBottom: '16px' } },
            el('div', { className: 'wc-block-components-checkout-step__heading' },
                el('h2', { className: 'wc-block-components-title wc-block-components-checkout-step__title' },
                    delivery.label || 'Preferred Delivery Date',
                    delivery.required && el('span', { style: { color: '#cc0000' } }, ' *')
                )
            ),
            el('div', { className: 'wc-block-components-checkout-step__container' },
                el('div', { className: 'wc-block-components-checkout-step__content' },
                    // Estimated delivery message above the date picker
                    el(EstimatedDeliveryMessage, null),
                    el('input', {
                        ref: inputRef,
                        type: 'text',
                        className: 'marwchto-datepicker',
                        placeholder: i18n?.selectDate || 'Select a date',
                        required: delivery.required,
                        readOnly: true,
                        style: { width: '100%', padding: '12px 16px', border: '1px solid #8c8f94', borderRadius: '4px', fontSize: '16px', cursor: 'pointer' }
                    })
                )
            )
        );
    };

    /**
     * Generic Custom Field Component Factory
     */
    const createCustomFieldComponent = (fieldConfig, fieldKey, fieldId) => {
        return () => {
            const [value, setValue] = useState('');
            const [charCount, setCharCount] = useState(0);

            const handleChange = (e) => {
                let newValue = fieldConfig.type === 'checkbox' ? (e.target.checked ? '1' : '') : e.target.value;

                if (['text', 'textarea'].includes(fieldConfig.type) && fieldConfig.maxLength > 0 && newValue.length > fieldConfig.maxLength) {
                    newValue = newValue.substring(0, fieldConfig.maxLength);
                }

                setValue(newValue);
                setCharCount(newValue.length);
                setExtensionData('marwchto', fieldKey, newValue);
            };

            if (!fieldConfig || !fieldConfig.enabled) return null;

            // Check product/category visibility rules
            if (!shouldShowFieldByVisibility(fieldConfig)) return null;

            const inputStyles = {
                width: '100%',
                padding: '12px 16px',
                border: '1px solid #8c8f94',
                borderRadius: '4px',
                fontSize: '16px',
                fontFamily: 'inherit',
                resize: 'vertical'
            };

            const renderField = () => {
                switch (fieldConfig.type) {
                    case 'checkbox':
                        return el('label', { style: { display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer' } },
                            el('input', {
                                type: 'checkbox',
                                id: fieldId,
                                checked: value === '1',
                                onChange: handleChange,
                                required: fieldConfig.required,
                                style: { width: '20px', height: '20px' }
                            }),
                            el('span', null, fieldConfig.checkboxLabel || fieldConfig.label)
                        );

                    case 'select':
                        return el('select', {
                            id: fieldId,
                            value: value,
                            onChange: handleChange,
                            required: fieldConfig.required,
                            style: inputStyles
                        },
                            el('option', { value: '' }, fieldConfig.placeholder || i18n?.selectOption || 'Select an option...'),
                            (fieldConfig.selectOptions || []).map(opt =>
                                el('option', { key: opt.value, value: opt.value }, opt.label)
                            )
                        );

                    case 'textarea':
                        return el('textarea', {
                            id: fieldId,
                            placeholder: fieldConfig.placeholder || '',
                            value: value,
                            onChange: handleChange,
                            required: fieldConfig.required,
                            rows: 4,
                            maxLength: fieldConfig.maxLength > 0 ? fieldConfig.maxLength : undefined,
                            style: inputStyles
                        });

                    default:
                        return el('input', {
                            type: 'text',
                            id: fieldId,
                            placeholder: fieldConfig.placeholder || '',
                            value: value,
                            onChange: handleChange,
                            required: fieldConfig.required,
                            maxLength: fieldConfig.maxLength > 0 ? fieldConfig.maxLength : undefined,
                            style: inputStyles
                        });
                }
            };

            return el('div', { className: `marwchto-${fieldId}-block wc-block-components-checkout-step`, style: { marginTop: '16px', marginBottom: '16px' } },
                el('div', { className: 'wc-block-components-checkout-step__heading' },
                    el('h2', { className: 'wc-block-components-title wc-block-components-checkout-step__title' },
                        fieldConfig.label,
                        fieldConfig.required && el('span', { style: { color: '#cc0000' } }, ' *')
                    )
                ),
                el('div', { className: 'wc-block-components-checkout-step__container' },
                    el('div', { className: 'wc-block-components-checkout-step__content' },
                        renderField(),
                        ['text', 'textarea'].includes(fieldConfig.type) && fieldConfig.maxLength > 0 && el('div', {
                            style: { marginTop: '8px', fontSize: '14px', color: '#757575', textAlign: 'right' }
                        }, (fieldConfig.maxLength - charCount) + ' ' + (i18n?.charactersRemaining || 'characters remaining'))
                    )
                )
            );
        };
    };

    // Create field components
    const CustomFieldComponent = createCustomFieldComponent(customField, 'custom_field', 'marwchto-custom-field');
    const CustomField2Component = createCustomFieldComponent(customField2, 'custom_field_2', 'marwchto-custom-field-2');

    /**
     * Render a field at its configured position
     */
    const renderFieldAtPosition = (Component, containerId, position) => {
        // Check if already rendered with content
        const existingContainer = document.getElementById(containerId);
        if (existingContainer) {
            if (existingContainer.hasChildNodes()) {
                return;
            }
            // Container exists but is empty - remove it and recreate
            existingContainer.remove();
        }

        const posConfig = positionMap[position];
        if (!posConfig) {
            return;
        }

        const container = document.createElement('div');
        container.id = containerId;
        container.className = 'marwchto-positioned-field';

        const inserted = insertAtPosition(container, posConfig.selector, posConfig.insertPosition);
        if (!inserted) {
            return;
        }

        // Render React component into container
        try {
            const { createRoot, render: legacyRender } = wp.element;
            if (createRoot) {
                const root = createRoot(container);
                root.render(el(Component, null));
            } else if (legacyRender) {
                legacyRender(el(Component, null), container);
            }
        } catch (error) {
            console.error('Checkout Toolkit: Error rendering component:', error);
        }
    };

    /**
     * Initialize position-based field rendering
     */
    const initPositionedFields = () => {
        // Render delivery method
        if (deliveryMethod && deliveryMethod.enabled) {
            renderFieldAtPosition(DeliveryMethodComponent, 'marwchto-delivery-method-container', 'woocommerce_before_order_notes');
        }

        // Render delivery instructions (after delivery method, before order notes)
        if (deliveryInstructions && deliveryInstructions.enabled) {
            renderFieldAtPosition(DeliveryInstructionsComponent, 'marwchto-delivery-instructions-container', 'woocommerce_before_order_notes');
        }

        // Render time window (after delivery instructions, before order notes)
        if (timeWindow && timeWindow.enabled) {
            renderFieldAtPosition(TimeWindowComponent, 'marwchto-time-window-container', 'woocommerce_before_order_notes');
        }

        // Render store location selector (after delivery method, only shows when pickup is selected)
        if (storeLocations && storeLocations.enabled) {
            renderFieldAtPosition(StoreLocationComponent, 'marwchto-store-location-container', 'woocommerce_before_order_notes');
        }

        // Render delivery date at its position
        if (delivery && delivery.enabled) {
            renderFieldAtPosition(DeliveryDateField, 'marwchto-delivery-date-container', delivery.position || 'woocommerce_after_order_notes');
        }

        // Render custom field 1 at its position
        if (customField && customField.enabled) {
            renderFieldAtPosition(CustomFieldComponent, 'marwchto-custom-field-container', customField.position || 'woocommerce_after_order_notes');
        }

        // Render custom field 2 at its position
        if (customField2 && customField2.enabled) {
            renderFieldAtPosition(CustomField2Component, 'marwchto-custom-field-2-container', customField2.position || 'woocommerce_after_order_notes');
        }
    };

    /**
     * Initialize with MutationObserver for dynamic content
     */
    const initWithObserver = () => {
        // Try immediately
        initPositionedFields();

        // Use MutationObserver to handle dynamic content
        let initAttempts = 0;
        const maxAttempts = 20;

        const observer = new MutationObserver(() => {
            initAttempts++;
            if (initAttempts <= maxAttempts) {
                initPositionedFields();
            } else {
                observer.disconnect();
            }
        });

        observer.observe(document.body, { childList: true, subtree: true });

        // Also try after delays
        setTimeout(initPositionedFields, 500);
        setTimeout(initPositionedFields, 1000);
        setTimeout(initPositionedFields, 2000);

        // Cleanup after 10 seconds
        setTimeout(() => observer.disconnect(), 10000);
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initWithObserver);
    } else {
        initWithObserver();
    }

    /**
     * Initialize extension data (runs once)
     */
    let extensionDataInitialized = false;

    const initExtensionData = () => {
        if (extensionDataInitialized) return;
        extensionDataInitialized = true;

        // Set initial values in our local state (will be batched and sent)
        if (deliveryMethod && deliveryMethod.enabled) {
            extensionDataState.delivery_method = deliveryMethod.defaultMethod || 'delivery';
            pendingExtensionData.delivery_method = deliveryMethod.defaultMethod || 'delivery';
        }
        if (deliveryInstructions && deliveryInstructions.enabled) {
            extensionDataState.delivery_instructions_preset = '';
            pendingExtensionData.delivery_instructions_preset = '';
            extensionDataState.delivery_instructions_custom = '';
            pendingExtensionData.delivery_instructions_custom = '';
        }
        if (timeWindow && timeWindow.enabled) {
            extensionDataState.time_window = '';
            pendingExtensionData.time_window = '';
        }
        if (storeLocations && storeLocations.enabled) {
            extensionDataState.store_location = '';
            pendingExtensionData.store_location = '';
        }
        if (delivery && delivery.enabled) {
            extensionDataState.delivery_date = '';
            pendingExtensionData.delivery_date = '';
        }
        if (customField && customField.enabled) {
            extensionDataState.custom_field = '';
            pendingExtensionData.custom_field = '';
        }
        if (customField2 && customField2.enabled) {
            extensionDataState.custom_field_2 = '';
            pendingExtensionData.custom_field_2 = '';
        }

        // Flush all initial data in one batch
        flushExtensionData();
    };

    // Initialize extension data after a short delay
    setTimeout(initExtensionData, 100);

    /**
     * Register empty plugin (required for WooCommerce to recognize the integration)
     */
    registerPlugin('marwchto-blocks', {
        render: () => null,
        scope: 'woocommerce-checkout'
    });

})();

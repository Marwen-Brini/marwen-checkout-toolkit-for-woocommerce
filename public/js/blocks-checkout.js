/**
 * Checkout Toolkit - WooCommerce Blocks Integration
 *
 * Uses the ExperimentalOrderMeta slot to add fields to blocks checkout.
 *
 * @package WooCheckoutToolkit
 */

(function () {
    'use strict';

    const { registerPlugin } = wp.plugins;
    const { ExperimentalOrderMeta } = wc.blocksCheckout;
    const { useEffect, useState, useRef, createElement: el } = wp.element;

    // Get settings from PHP (via wp_localize_script)
    const settings = window.checkoutToolkitData || {};

    if (!settings || Object.keys(settings).length === 0) {
        console.warn('Checkout Toolkit: No settings found');
        return;
    }

    const { delivery, customField, i18n } = settings;

    /**
     * Delivery Date Field Component
     */
    const DeliveryDateField = ({ cart, extensions, setExtensionData }) => {
        const [selectedDate, setSelectedDate] = useState('');
        const inputRef = useRef(null);
        const flatpickrRef = useRef(null);

        useEffect(() => {
            // Wait for flatpickr to be available
            const initPicker = () => {
                if (!inputRef.current || !window.flatpickr || flatpickrRef.current) {
                    return;
                }

                // Calculate min/max dates
                const today = new Date();
                const minDate = new Date(today);
                minDate.setDate(minDate.getDate() + (delivery.minLeadDays || 0));

                const maxDate = new Date(today);
                maxDate.setDate(maxDate.getDate() + (delivery.maxFutureDays || 30));

                // Disable weekdays function
                const disableWeekdays = (date) => {
                    if (delivery.disabledWeekdays && delivery.disabledWeekdays.length > 0) {
                        return delivery.disabledWeekdays.includes(date.getDay());
                    }
                    return false;
                };

                // Build disabled dates
                const disabledDates = [];
                if (delivery.blockedDates && delivery.blockedDates.length > 0) {
                    disabledDates.push(...delivery.blockedDates);
                }

                flatpickrRef.current = flatpickr(inputRef.current, {
                    dateFormat: 'Y-m-d',
                    altInput: true,
                    altFormat: delivery.dateFormat || 'F j, Y',
                    minDate: minDate,
                    maxDate: maxDate,
                    disable: [disableWeekdays, ...disabledDates],
                    locale: {
                        firstDayOfWeek: delivery.firstDayOfWeek || 0
                    },
                    onChange: (selectedDates, dateStr) => {
                        setSelectedDate(dateStr);
                        if (typeof setExtensionData === 'function') {
                            setExtensionData('checkout-toolkit', 'delivery_date', dateStr);
                        }
                    }
                });
            };

            // Try to init immediately or wait for flatpickr
            if (window.flatpickr) {
                initPicker();
            } else {
                const checkInterval = setInterval(() => {
                    if (window.flatpickr) {
                        clearInterval(checkInterval);
                        initPicker();
                    }
                }, 100);

                // Cleanup interval after 10 seconds
                setTimeout(() => clearInterval(checkInterval), 10000);
            }

            return () => {
                if (flatpickrRef.current) {
                    flatpickrRef.current.destroy();
                    flatpickrRef.current = null;
                }
            };
        }, []);

        if (!delivery || !delivery.enabled) {
            return null;
        }

        return el('div', { className: 'checkout-toolkit-delivery-date-block wc-block-components-checkout-step' },
            el('div', { className: 'wc-block-components-checkout-step__heading' },
                el('h2', { className: 'wc-block-components-title wc-block-components-checkout-step__title' },
                    delivery.label || 'Preferred Delivery Date',
                    delivery.required && el('span', { className: 'required', style: { color: '#cc0000' } }, ' *')
                )
            ),
            el('div', { className: 'wc-block-components-checkout-step__container' },
                el('div', { className: 'wc-block-components-checkout-step__content' },
                    el('input', {
                        ref: inputRef,
                        type: 'text',
                        id: 'checkout-toolkit-delivery-date',
                        name: 'checkout_toolkit_delivery_date',
                        className: 'wc-block-components-text-input__input checkout-toolkit-datepicker',
                        placeholder: i18n?.selectDate || 'Select a date',
                        required: delivery.required,
                        readOnly: true,
                        style: {
                            width: '100%',
                            padding: '12px 16px',
                            border: '1px solid #8c8f94',
                            borderRadius: '4px',
                            fontSize: '16px',
                            cursor: 'pointer'
                        }
                    })
                )
            )
        );
    };

    /**
     * Custom Field Component
     */
    const CustomFieldComponent = ({ cart, extensions, setExtensionData }) => {
        const [value, setValue] = useState('');
        const [charCount, setCharCount] = useState(0);

        const handleChange = (e) => {
            let newValue = e.target.value;

            // Enforce max length
            if (customField.maxLength > 0 && newValue.length > customField.maxLength) {
                newValue = newValue.substring(0, customField.maxLength);
            }

            setValue(newValue);
            setCharCount(newValue.length);

            if (typeof setExtensionData === 'function') {
                setExtensionData('checkout-toolkit', 'custom_field', newValue);
            }
        };

        if (!customField || !customField.enabled) {
            return null;
        }

        const isTextarea = customField.type === 'textarea';

        const inputStyles = {
            width: '100%',
            padding: '12px 16px',
            border: '1px solid #8c8f94',
            borderRadius: '4px',
            fontSize: '16px',
            fontFamily: 'inherit',
            resize: 'vertical'
        };

        return el('div', { className: 'checkout-toolkit-custom-field-block wc-block-components-checkout-step' },
            el('div', { className: 'wc-block-components-checkout-step__heading' },
                el('h2', { className: 'wc-block-components-title wc-block-components-checkout-step__title' },
                    customField.label || 'Special Instructions',
                    customField.required && el('span', { className: 'required', style: { color: '#cc0000' } }, ' *')
                )
            ),
            el('div', { className: 'wc-block-components-checkout-step__container' },
                el('div', { className: 'wc-block-components-checkout-step__content' },
                    isTextarea
                        ? el('textarea', {
                            id: 'checkout-toolkit-custom-field',
                            name: 'checkout_toolkit_custom_field',
                            className: 'checkout-toolkit-custom-field',
                            placeholder: customField.placeholder || '',
                            value: value,
                            onChange: handleChange,
                            required: customField.required,
                            rows: 4,
                            maxLength: customField.maxLength > 0 ? customField.maxLength : undefined,
                            style: inputStyles
                        })
                        : el('input', {
                            type: 'text',
                            id: 'checkout-toolkit-custom-field',
                            name: 'checkout_toolkit_custom_field',
                            className: 'checkout-toolkit-custom-field',
                            placeholder: customField.placeholder || '',
                            value: value,
                            onChange: handleChange,
                            required: customField.required,
                            maxLength: customField.maxLength > 0 ? customField.maxLength : undefined,
                            style: inputStyles
                        }),
                    customField.maxLength > 0 && el('div', {
                        className: 'checkout-toolkit-char-count',
                        style: { marginTop: '8px', fontSize: '14px', color: '#757575', textAlign: 'right' }
                    },
                        (customField.maxLength - charCount) + ' ' + (i18n?.charactersRemaining || 'characters remaining')
                    )
                )
            )
        );
    };

    /**
     * Main Render Component for ExperimentalOrderMeta slot
     */
    const CheckoutToolkitFields = ({ cart, extensions }) => {
        const [extensionData, setExtensionDataState] = useState({});

        // Function to update extension data and dispatch to store
        const setExtensionData = (namespace, key, value) => {
            setExtensionDataState(prev => ({
                ...prev,
                [key]: value
            }));

            // Dispatch to WooCommerce store
            const { dispatch } = wp.data;
            if (dispatch('wc/store/checkout')) {
                dispatch('wc/store/checkout').__internalSetExtensionData(namespace, { [key]: value });
            }
        };

        const hasDelivery = delivery && delivery.enabled;
        const hasCustomField = customField && customField.enabled;

        if (!hasDelivery && !hasCustomField) {
            return null;
        }

        return el('div', { className: 'checkout-toolkit-blocks-wrapper', style: { marginTop: '24px' } },
            hasDelivery && el(DeliveryDateField, { cart, extensions, setExtensionData }),
            hasCustomField && el(CustomFieldComponent, { cart, extensions, setExtensionData })
        );
    };

    /**
     * Register the plugin with ExperimentalOrderMeta slot
     */
    const render = () => {
        return el(ExperimentalOrderMeta, null,
            el(CheckoutToolkitFields, null)
        );
    };

    // Register the plugin
    registerPlugin('checkout-toolkit-blocks', {
        render,
        scope: 'woocommerce-checkout'
    });

})();

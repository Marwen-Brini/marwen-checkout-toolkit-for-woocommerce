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

    const { deliveryMethod, delivery, customField, customField2, i18n } = settings;

    /**
     * Delivery Method Toggle Component
     */
    const DeliveryMethodComponent = ({ cart, extensions, setExtensionData }) => {
        const [selectedMethod, setSelectedMethod] = useState(deliveryMethod?.defaultMethod || 'delivery');

        useEffect(() => {
            // Set initial value
            if (typeof setExtensionData === 'function') {
                setExtensionData('checkout-toolkit', 'delivery_method', selectedMethod);
            }
        }, []);

        const handleChange = (method) => {
            setSelectedMethod(method);
            if (typeof setExtensionData === 'function') {
                setExtensionData('checkout-toolkit', 'delivery_method', method);
            }
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

        const radioStyles = {
            marginBottom: '8px',
            display: 'flex',
            alignItems: 'center',
            gap: '8px',
            cursor: 'pointer'
        };

        const renderToggle = () => {
            if (deliveryMethod.showAs === 'radio') {
                return el('div', { className: 'wct-delivery-method-radio' },
                    el('label', { style: radioStyles },
                        el('input', {
                            type: 'radio',
                            name: 'checkout_toolkit_delivery_method',
                            value: 'delivery',
                            checked: selectedMethod === 'delivery',
                            onChange: () => handleChange('delivery')
                        }),
                        deliveryMethod.deliveryLabel || 'Delivery'
                    ),
                    el('label', { style: radioStyles },
                        el('input', {
                            type: 'radio',
                            name: 'checkout_toolkit_delivery_method',
                            value: 'pickup',
                            checked: selectedMethod === 'pickup',
                            onChange: () => handleChange('pickup')
                        }),
                        deliveryMethod.pickupLabel || 'Pickup'
                    )
                );
            }

            // Toggle buttons (default)
            return el('div', { className: 'wct-delivery-method-toggle', style: toggleStyles },
                el('button', {
                    type: 'button',
                    className: 'wct-toggle-option' + (selectedMethod === 'delivery' ? ' active' : ''),
                    style: optionStyles(selectedMethod === 'delivery'),
                    onClick: () => handleChange('delivery')
                }, deliveryMethod.deliveryLabel || 'Delivery'),
                el('button', {
                    type: 'button',
                    className: 'wct-toggle-option' + (selectedMethod === 'pickup' ? ' active' : ''),
                    style: { ...optionStyles(selectedMethod === 'pickup'), borderRight: 'none' },
                    onClick: () => handleChange('pickup')
                }, deliveryMethod.pickupLabel || 'Pickup')
            );
        };

        return el('div', { className: 'checkout-toolkit-delivery-method-block wc-block-components-checkout-step' },
            el('div', { className: 'wc-block-components-checkout-step__heading' },
                el('h2', { className: 'wc-block-components-title wc-block-components-checkout-step__title' },
                    deliveryMethod.fieldLabel || 'Fulfillment Method'
                )
            ),
            el('div', { className: 'wc-block-components-checkout-step__container' },
                el('div', { className: 'wc-block-components-checkout-step__content' },
                    renderToggle()
                )
            )
        );
    };

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
            let newValue = customField.type === 'checkbox' ? (e.target.checked ? '1' : '') : e.target.value;

            // Enforce max length for text fields
            if (['text', 'textarea'].includes(customField.type) && customField.maxLength > 0 && newValue.length > customField.maxLength) {
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
            switch (customField.type) {
                case 'checkbox':
                    return el('label', { style: { display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer' } },
                        el('input', {
                            type: 'checkbox',
                            id: 'checkout-toolkit-custom-field',
                            name: 'checkout_toolkit_custom_field',
                            className: 'checkout-toolkit-custom-field',
                            checked: value === '1',
                            onChange: handleChange,
                            required: customField.required,
                            style: { width: '20px', height: '20px' }
                        }),
                        el('span', null, customField.checkboxLabel || customField.label)
                    );

                case 'select':
                    return el('select', {
                        id: 'checkout-toolkit-custom-field',
                        name: 'checkout_toolkit_custom_field',
                        className: 'checkout-toolkit-custom-field',
                        value: value,
                        onChange: handleChange,
                        required: customField.required,
                        style: inputStyles
                    },
                        el('option', { value: '' }, customField.placeholder || i18n?.selectOption || 'Select an option...'),
                        (customField.selectOptions || []).map(opt =>
                            el('option', { key: opt.value, value: opt.value }, opt.label)
                        )
                    );

                case 'textarea':
                    return el('textarea', {
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
                    });

                default: // text
                    return el('input', {
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
                    });
            }
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
                    renderField(),
                    ['text', 'textarea'].includes(customField.type) && customField.maxLength > 0 && el('div', {
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
     * Custom Field 2 Component
     */
    const CustomField2Component = ({ cart, extensions, setExtensionData }) => {
        const [value, setValue] = useState('');
        const [charCount, setCharCount] = useState(0);

        const handleChange = (e) => {
            let newValue = customField2.type === 'checkbox' ? (e.target.checked ? '1' : '') : e.target.value;

            // Enforce max length for text fields
            if (['text', 'textarea'].includes(customField2.type) && customField2.maxLength > 0 && newValue.length > customField2.maxLength) {
                newValue = newValue.substring(0, customField2.maxLength);
            }

            setValue(newValue);
            setCharCount(newValue.length);

            if (typeof setExtensionData === 'function') {
                setExtensionData('checkout-toolkit', 'custom_field_2', newValue);
            }
        };

        if (!customField2 || !customField2.enabled) {
            return null;
        }

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
            switch (customField2.type) {
                case 'checkbox':
                    return el('label', { style: { display: 'flex', alignItems: 'center', gap: '8px', cursor: 'pointer' } },
                        el('input', {
                            type: 'checkbox',
                            id: 'checkout-toolkit-custom-field-2',
                            name: 'checkout_toolkit_custom_field_2',
                            className: 'checkout-toolkit-custom-field-2',
                            checked: value === '1',
                            onChange: handleChange,
                            required: customField2.required,
                            style: { width: '20px', height: '20px' }
                        }),
                        el('span', null, customField2.checkboxLabel || customField2.label)
                    );

                case 'select':
                    return el('select', {
                        id: 'checkout-toolkit-custom-field-2',
                        name: 'checkout_toolkit_custom_field_2',
                        className: 'checkout-toolkit-custom-field-2',
                        value: value,
                        onChange: handleChange,
                        required: customField2.required,
                        style: inputStyles
                    },
                        el('option', { value: '' }, customField2.placeholder || i18n?.selectOption || 'Select an option...'),
                        (customField2.selectOptions || []).map(opt =>
                            el('option', { key: opt.value, value: opt.value }, opt.label)
                        )
                    );

                case 'textarea':
                    return el('textarea', {
                        id: 'checkout-toolkit-custom-field-2',
                        name: 'checkout_toolkit_custom_field_2',
                        className: 'checkout-toolkit-custom-field-2',
                        placeholder: customField2.placeholder || '',
                        value: value,
                        onChange: handleChange,
                        required: customField2.required,
                        rows: 4,
                        maxLength: customField2.maxLength > 0 ? customField2.maxLength : undefined,
                        style: inputStyles
                    });

                default: // text
                    return el('input', {
                        type: 'text',
                        id: 'checkout-toolkit-custom-field-2',
                        name: 'checkout_toolkit_custom_field_2',
                        className: 'checkout-toolkit-custom-field-2',
                        placeholder: customField2.placeholder || '',
                        value: value,
                        onChange: handleChange,
                        required: customField2.required,
                        maxLength: customField2.maxLength > 0 ? customField2.maxLength : undefined,
                        style: inputStyles
                    });
            }
        };

        return el('div', { className: 'checkout-toolkit-custom-field-2-block wc-block-components-checkout-step' },
            el('div', { className: 'wc-block-components-checkout-step__heading' },
                el('h2', { className: 'wc-block-components-title wc-block-components-checkout-step__title' },
                    customField2.label || 'Additional Information',
                    customField2.required && el('span', { className: 'required', style: { color: '#cc0000' } }, ' *')
                )
            ),
            el('div', { className: 'wc-block-components-checkout-step__container' },
                el('div', { className: 'wc-block-components-checkout-step__content' },
                    renderField(),
                    ['text', 'textarea'].includes(customField2.type) && customField2.maxLength > 0 && el('div', {
                        className: 'checkout-toolkit-char-count',
                        style: { marginTop: '8px', fontSize: '14px', color: '#757575', textAlign: 'right' }
                    },
                        (customField2.maxLength - charCount) + ' ' + (i18n?.charactersRemaining || 'characters remaining')
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
        const initializedRef = useRef(false);

        // Function to update extension data and dispatch to store
        const setExtensionData = (namespace, key, value) => {
            // Ensure value is always a string (never null/undefined)
            const stringValue = value === null || value === undefined ? '' : String(value);

            setExtensionDataState(prev => ({
                ...prev,
                [key]: stringValue
            }));

            // Dispatch to WooCommerce store
            const { dispatch } = wp.data;
            if (dispatch('wc/store/checkout')) {
                dispatch('wc/store/checkout').__internalSetExtensionData(namespace, { [key]: stringValue });
            }
        };

        const hasDeliveryMethod = deliveryMethod && deliveryMethod.enabled;
        const hasDelivery = delivery && delivery.enabled;
        const hasCustomField = customField && customField.enabled;
        const hasCustomField2 = customField2 && customField2.enabled;

        // Initialize extension data with empty strings on mount
        useEffect(() => {
            if (initializedRef.current) return;
            initializedRef.current = true;

            const { dispatch } = wp.data;
            if (dispatch('wc/store/checkout')) {
                const initialData = {};
                if (hasDeliveryMethod) {
                    initialData.delivery_method = deliveryMethod.defaultMethod || 'delivery';
                }
                if (hasDelivery) {
                    initialData.delivery_date = '';
                }
                if (hasCustomField) {
                    initialData.custom_field = '';
                }
                if (hasCustomField2) {
                    initialData.custom_field_2 = '';
                }
                dispatch('wc/store/checkout').__internalSetExtensionData('checkout-toolkit', initialData);
            }
        }, [hasDeliveryMethod, hasDelivery, hasCustomField, hasCustomField2]);

        if (!hasDeliveryMethod && !hasDelivery && !hasCustomField && !hasCustomField2) {
            return null;
        }

        return el('div', { className: 'checkout-toolkit-blocks-wrapper', style: { marginTop: '24px' } },
            hasDeliveryMethod && el(DeliveryMethodComponent, { cart, extensions, setExtensionData }),
            hasDelivery && el(DeliveryDateField, { cart, extensions, setExtensionData }),
            hasCustomField && el(CustomFieldComponent, { cart, extensions, setExtensionData }),
            hasCustomField2 && el(CustomField2Component, { cart, extensions, setExtensionData })
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

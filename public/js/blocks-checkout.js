/**
 * Checkout Toolkit - WooCommerce Blocks Integration
 *
 * @package WooCheckoutToolkit
 */

(function () {
    'use strict';

    const { registerCheckoutBlock } = wc.blocksCheckout;
    const { useEffect, useState, useRef, createElement: el } = wp.element;
    const { getSetting } = wc.wcSettings;
    const { extensionCartUpdate } = wc.blocksCheckout;

    // Get settings from PHP
    const settings = getSetting('checkout-toolkit_data', {});
    const { delivery, customField, i18n } = settings;

    // Store for our field values
    let checkoutToolkitData = {
        delivery_date: '',
        custom_field: ''
    };

    /**
     * Delivery Date Field Component
     */
    const DeliveryDateField = ({ checkoutExtensionData }) => {
        const { setExtensionData } = checkoutExtensionData;
        const [selectedDate, setSelectedDate] = useState('');
        const [displayDate, setDisplayDate] = useState('');
        const inputRef = useRef(null);
        const flatpickrRef = useRef(null);

        useEffect(() => {
            if (!inputRef.current || !window.flatpickr) {
                // Load flatpickr if not available
                loadFlatpickr().then(() => initFlatpickr());
                return;
            }
            initFlatpickr();
        }, []);

        const loadFlatpickr = () => {
            return new Promise((resolve) => {
                if (window.flatpickr) {
                    resolve();
                    return;
                }

                // Flatpickr should already be loaded via wp_enqueue_script
                // This is a fallback
                const checkInterval = setInterval(() => {
                    if (window.flatpickr) {
                        clearInterval(checkInterval);
                        resolve();
                    }
                }, 100);

                // Timeout after 5 seconds
                setTimeout(() => {
                    clearInterval(checkInterval);
                    resolve();
                }, 5000);
            });
        };

        const initFlatpickr = () => {
            if (!inputRef.current || !window.flatpickr || flatpickrRef.current) {
                return;
            }

            // Calculate min/max dates
            const today = new Date();
            const minDate = new Date(today);
            minDate.setDate(minDate.getDate() + (delivery.minLeadDays || 0));

            const maxDate = new Date(today);
            maxDate.setDate(maxDate.getDate() + (delivery.maxFutureDays || 30));

            // Build disabled dates array
            const disabledDates = [];

            // Add blocked specific dates
            if (delivery.blockedDates && delivery.blockedDates.length > 0) {
                disabledDates.push(...delivery.blockedDates);
            }

            // Disable weekdays function
            const disableWeekdays = (date) => {
                if (delivery.disabledWeekdays && delivery.disabledWeekdays.length > 0) {
                    return delivery.disabledWeekdays.includes(date.getDay());
                }
                return false;
            };

            flatpickrRef.current = flatpickr(inputRef.current, {
                dateFormat: 'Y-m-d',
                altInput: true,
                altFormat: delivery.dateFormat || 'F j, Y',
                minDate: minDate,
                maxDate: maxDate,
                disable: [
                    disableWeekdays,
                    ...disabledDates
                ],
                locale: {
                    firstDayOfWeek: delivery.firstDayOfWeek || 0
                },
                onChange: (selectedDates, dateStr) => {
                    setSelectedDate(dateStr);
                    checkoutToolkitData.delivery_date = dateStr;
                    setExtensionData('checkout-toolkit', 'delivery_date', dateStr);
                }
            });
        };

        useEffect(() => {
            return () => {
                if (flatpickrRef.current) {
                    flatpickrRef.current.destroy();
                }
            };
        }, []);

        if (!delivery.enabled) {
            return null;
        }

        return el('div', { className: 'checkout-toolkit-delivery-date-block' },
            el('label', {
                htmlFor: 'checkout-toolkit-delivery-date',
                className: 'wc-block-components-text-input__label'
            },
                delivery.label || 'Preferred Delivery Date',
                delivery.required && el('span', { className: 'required' }, ' *')
            ),
            el('div', { className: 'checkout-toolkit-date-wrapper' },
                el('input', {
                    ref: inputRef,
                    type: 'text',
                    id: 'checkout-toolkit-delivery-date',
                    name: 'checkout_toolkit_delivery_date',
                    className: 'wc-block-components-text-input__input checkout-toolkit-datepicker',
                    placeholder: i18n.selectDate || 'Select a date',
                    required: delivery.required,
                    readOnly: true
                })
            )
        );
    };

    /**
     * Custom Field Component
     */
    const CustomFieldComponent = ({ checkoutExtensionData }) => {
        const { setExtensionData } = checkoutExtensionData;
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
            checkoutToolkitData.custom_field = newValue;
            setExtensionData('checkout-toolkit', 'custom_field', newValue);
        };

        if (!customField.enabled) {
            return null;
        }

        const InputComponent = customField.type === 'textarea' ? 'textarea' : 'input';
        const inputProps = {
            id: 'checkout-toolkit-custom-field',
            name: 'checkout_toolkit_custom_field',
            className: 'wc-block-components-text-input__input checkout-toolkit-custom-field',
            placeholder: customField.placeholder || '',
            value: value,
            onChange: handleChange,
            required: customField.required
        };

        if (customField.type === 'textarea') {
            inputProps.rows = 4;
        } else {
            inputProps.type = 'text';
        }

        if (customField.maxLength > 0) {
            inputProps.maxLength = customField.maxLength;
        }

        return el('div', { className: 'checkout-toolkit-custom-field-block' },
            el('label', {
                htmlFor: 'checkout-toolkit-custom-field',
                className: 'wc-block-components-text-input__label'
            },
                customField.label || 'Special Instructions',
                customField.required && el('span', { className: 'required' }, ' *')
            ),
            el(InputComponent, inputProps),
            customField.maxLength > 0 && el('div', { className: 'checkout-toolkit-char-count' },
                (customField.maxLength - charCount) + ' ' + (i18n.charactersRemaining || 'characters remaining')
            )
        );
    };

    /**
     * Main Checkout Block Component
     */
    const CheckoutToolkitBlock = ({ checkoutExtensionData }) => {
        if (!delivery.enabled && !customField.enabled) {
            return null;
        }

        return el('div', { className: 'checkout-toolkit-blocks-wrapper' },
            delivery.enabled && el(DeliveryDateField, { checkoutExtensionData }),
            customField.enabled && el(CustomFieldComponent, { checkoutExtensionData })
        );
    };

    /**
     * Block configuration
     */
    const blockConfig = {
        name: 'checkout-toolkit',
        metadata: {
            name: 'checkout-toolkit',
            parent: ['woocommerce/checkout-order-notes-block'],
        },
        component: ({ checkoutExtensionData, extensions }) => {
            return el(CheckoutToolkitBlock, { checkoutExtensionData, extensions });
        }
    };

    // Register the checkout block
    if (typeof registerCheckoutBlock === 'function') {
        registerCheckoutBlock(blockConfig);
    }

    // Load Flatpickr styles
    const loadStyles = () => {
        const styleId = 'checkout-toolkit-blocks-style';
        if (!document.getElementById(styleId)) {
            const link = document.createElement('link');
            link.id = styleId;
            link.rel = 'stylesheet';
            link.href = settings.cssUrl || '';
            document.head.appendChild(link);
        }
    };

    // Initialize when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadStyles);
    } else {
        loadStyles();
    }

})();

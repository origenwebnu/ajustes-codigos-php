(function () {
    'use strict';

    const CONTINUE_SHOPPING_URL = 'https://laforeste.com/los-guardianes/';
    const PLACE_ORDER_TEXT = 'Pago en línea Bold';

    const FIELD_LABELS = {
        billing_first_name: 'Nombres',
        billing_last_name: 'Apellidos',
        billing_identification: 'Número de Identificación',
        billing_address_1: 'Calle, Número.',
        billing_address_2: 'Casa, apartamento, etc. (Opcional)',
        billing_city: 'Ciudad',
        billing_state: 'Departamento',
        billing_postcode: 'Código postal / Zip Code (opcional)',
        billing_phone: 'Teléfono',
        billing_email: 'Correo electrónico',
        order_comments: 'Notas adicionales (opcional)'
    };

    function getCheckoutRoot() {
        return document.querySelector('.ny-compraf');
    }

    function getCheckoutForm(root) {
        return root && root.querySelector('form.checkout.woocommerce-checkout');
    }

    function buildCheckoutHeader(root) {
        if (!root || root.querySelector('.cr-checkout-header')) {
            return;
        }

        const form = getCheckoutForm(root);
        const customerDetails = root.querySelector('#customer_details');
        if (!form || !customerDetails) {
            return;
        }

        const header = document.createElement('div');
        header.className = 'cr-checkout-header';
        header.innerHTML =
            '<h2 class="cr-checkout-header__title">Detalles de facturación</h2>' +
            '<a href="' + CONTINUE_SHOPPING_URL + '" class="cr-checkout-header__continue">Seguir comprando</a>';

        customerDetails.insertBefore(header, customerDetails.firstChild);
    }

    function moveLeftColumnSections(root) {
        const col1 = root.querySelector('#customer_details .col-1');
        const col2 = root.querySelector('#customer_details .col-2');

        if (!col1 || !col2) {
            return;
        }

        const shippingFields = col2.querySelector('.woocommerce-shipping-fields');
        const additionalFields = col2.querySelector('.woocommerce-additional-fields');

        if (shippingFields && !col1.contains(shippingFields)) {
            col1.appendChild(shippingFields);
        }

        if (additionalFields && !col1.contains(additionalFields)) {
            col1.appendChild(additionalFields);
        }
    }

    function updateFieldLabels(root) {
        Object.keys(FIELD_LABELS).forEach(function (fieldId) {
            const field = root.querySelector('#' + fieldId + '_field');
            if (!field) {
                return;
            }

            const label = field.querySelector('label');
            if (!label) {
                return;
            }

            const required = label.querySelector('.required');
            const optional = label.querySelector('.optional');
            const labelHasOptional = /\(opcional\)/i.test(FIELD_LABELS[fieldId]);

            label.textContent = FIELD_LABELS[fieldId];

            if (required) {
                label.appendChild(document.createTextNode(' '));
                label.appendChild(required);
            } else if (optional && !labelHasOptional) {
                label.appendChild(document.createTextNode(' '));
                label.appendChild(optional);
            } else if (optional && labelHasOptional) {
                optional.remove();
            }
        });

        const address2Label = root.querySelector('#billing_address_2_field label');
        if (address2Label) {
            address2Label.classList.remove('screen-reader-text');
        }

        const notesField = root.querySelector('#order_comments');
        if (notesField) {
            notesField.setAttribute(
                'placeholder',
                'Notas sobre tu pedido, por ejemplo alguna indicación para la entrega.'
            );
        }
    }

    const TWO_COLUMN_FIELDS = {
        billing_first_name: 'first',
        billing_last_name: 'last',
        billing_identification: 'first',
        billing_city: 'first',
        billing_state: 'last',
        billing_postcode: 'first',
        billing_phone: 'last'
    };

    const FULL_WIDTH_FIELDS = [
        'billing_address_1',
        'billing_address_2',
        'billing_email',
        'order_comments'
    ];

    const ID_FIELD_SELECTORS = [
        '#billing_document_field',
        '#billing_cedula_field',
        '#billing_identification_field',
        '#billing_id_number_field',
        '#billing_nit_field'
    ];

    function setFieldColumn(field, column) {
        if (!field) {
            return;
        }

        field.classList.remove('form-row-first', 'form-row-last', 'form-row-wide');

        if (column === 'wide') {
            field.classList.add('form-row-wide');
            return;
        }

        field.classList.add(column === 'first' ? 'form-row-first' : 'form-row-last');
    }

    function enforceTwoColumnLayout(root) {
        Object.keys(TWO_COLUMN_FIELDS).forEach(function (fieldId) {
            setFieldColumn(
                root.querySelector('#' + fieldId + '_field'),
                TWO_COLUMN_FIELDS[fieldId]
            );
        });

        FULL_WIDTH_FIELDS.forEach(function (fieldId) {
            const field = root.querySelector('#' + fieldId + '_field');
            setFieldColumn(field, 'wide');
        });

        const billingWrapper = root.querySelector('.woocommerce-billing-fields__field-wrapper');
        const countryField = billingWrapper
            ? billingWrapper.querySelector('#billing_country_field')
            : root.querySelector('#billing_country_field');
        const hasIdField = ID_FIELD_SELECTORS.some(function (selector) {
            return root.querySelector(selector);
        });

        if (countryField) {
            setFieldColumn(countryField, hasIdField ? 'last' : 'first');
        }

        ID_FIELD_SELECTORS.forEach(function (selector) {
            setFieldColumn(root.querySelector(selector), 'first');
        });
    }

    function updateSubtotalLabel(root) {
        const subtotalRow = root.querySelector('.woocommerce-checkout-review-order-table tr.cart-subtotal th');
        if (!subtotalRow) {
            return;
        }

        const itemCount = root.querySelectorAll('.woocommerce-checkout-review-order-table tbody tr.cart_item').length;
        if (!itemCount) {
            return;
        }

        const label = itemCount === 1 ? 'producto' : 'productos';
        subtotalRow.textContent = 'Subtotal (' + itemCount + ' ' + label + ')';
    }

    function simplifyShippingDisplay(root) {
        const shippingCell = root.querySelector('.woocommerce-checkout-review-order-table tr.woocommerce-shipping-totals td');
        if (!shippingCell) {
            return;
        }

        const existing = shippingCell.querySelector('.cr-shipping-free');
        if (existing) {
            existing.textContent = 'Gratis';
            return;
        }

        const label = shippingCell.querySelector('label');
        const labelText = label ? label.textContent.trim() : '';
        const isFree = !labelText || /gratis|gratuito|free/i.test(labelText);
        const shippingText = isFree ? 'Gratis' : labelText;

        shippingCell.innerHTML = '<span class="cr-shipping-free">' + shippingText + '</span>';
    }

    function updatePlaceOrderButton(root) {
        const placeOrder = root.querySelector('#place_order');
        if (placeOrder) {
            placeOrder.textContent = PLACE_ORDER_TEXT;
            placeOrder.value = PLACE_ORDER_TEXT;
        }
    }

    function initCraftRootsCheckout() {
        const root = getCheckoutRoot();
        const form = getCheckoutForm(root);

        if (!root || !form) {
            return;
        }

        buildCheckoutHeader(root);
        moveLeftColumnSections(root);
        updateFieldLabels(root);
        enforceTwoColumnLayout(root);
        updateSubtotalLabel(root);
        simplifyShippingDisplay(root);
        updatePlaceOrderButton(root);

        form.classList.add('cr-checkout-ready');
    }

    function buildOrderReceivedHeader(root, orderWrap) {
        if (!root || root.querySelector('.cr-checkout-header')) {
            return;
        }

        const header = document.createElement('div');
        header.className = 'cr-checkout-header';
        header.innerHTML =
            '<h2 class="cr-checkout-header__title">Pedido recibido</h2>' +
            '<a href="' + CONTINUE_SHOPPING_URL + '" class="cr-checkout-header__continue">Seguir comprando</a>';

        orderWrap.parentNode.insertBefore(header, orderWrap);
    }

    function updateOrderOverviewLabels(root) {
        const labelMap = {
            order: 'Número de pedido:',
            date: 'Fecha:',
            total: 'Total:',
            email: 'Correo electrónico:',
            'payment-method': 'Método de pago:'
        };

        root.querySelectorAll('.woocommerce-order-overview li').forEach(function (item) {
            const strong = item.querySelector('strong');
            if (!strong) {
                return;
            }

            Object.keys(labelMap).forEach(function (key) {
                if (item.classList.contains(key) || item.classList.contains('woocommerce-order-overview__' + key)) {
                    strong.textContent = labelMap[key];
                }
            });
        });
    }

    function formatOrderOverviewDate(root) {
        const dateItem = root.querySelector('.woocommerce-order-overview__date');
        if (!dateItem) {
            return;
        }

        const strong = dateItem.querySelector('strong');
        if (!strong) {
            return;
        }

        const raw = dateItem.textContent.replace(strong.textContent, '').trim();
        const parsed = new Date(raw);

        if (isNaN(parsed.getTime())) {
            return;
        }

        const day = String(parsed.getDate()).padStart(2, '0');
        const month = String(parsed.getMonth() + 1).padStart(2, '0');
        const year = parsed.getFullYear();
        const formatted = day + ' / ' + month + ' / ' + year;

        Array.from(dateItem.childNodes).forEach(function (node) {
            if (node.nodeType === Node.TEXT_NODE) {
                node.textContent = '';
            }
        });

        dateItem.appendChild(document.createTextNode(' ' + formatted));
    }

    function addOrderDetailsQuantityColumn(root) {
        const table = root.querySelector('.woocommerce-table--order-details');
        if (!table || table.classList.contains('cr-order-qty-ready')) {
            return;
        }

        const headRow = table.querySelector('thead tr');
        const totalTh = headRow && headRow.querySelector('th.product-total');

        if (headRow && totalTh && !headRow.querySelector('th.cr-qty-header')) {
            const qtyTh = document.createElement('th');
            qtyTh.className = 'product-quantity cr-qty-header';
            qtyTh.textContent = 'Cantidad';
            headRow.insertBefore(qtyTh, totalTh);
        }

        table.querySelectorAll('tbody tr').forEach(function (row) {
            if (row.querySelector('td.product-quantity')) {
                return;
            }

            const nameCell = row.querySelector('td.product-name');
            const totalCell = row.querySelector('td.product-total');
            if (!nameCell || !totalCell) {
                return;
            }

            const qtyMatch = nameCell.textContent.match(/×\s*(\d+)/);
            const qty = qtyMatch ? qtyMatch[1] : '1';
            const qtyEl = nameCell.querySelector('.product-quantity');
            if (qtyEl) {
                qtyEl.remove();
            }

            const qtyCell = document.createElement('td');
            qtyCell.className = 'product-quantity';
            qtyCell.setAttribute('data-title', 'Cantidad');
            qtyCell.textContent = qty;
            row.insertBefore(qtyCell, totalCell);
        });

        table.classList.add('cr-order-qty-ready');
    }

    function updateOrderDetailsTitle(root) {
        const title = root.querySelector('.woocommerce-order-details__title');
        if (title) {
            title.textContent = 'Detalles del pedido:';
        }
    }

    function initCraftRootsOrderReceived() {
        const root = getCheckoutRoot();
        const orderWrap = root && root.querySelector('.woocommerce-order');

        if (!root || !orderWrap) {
            return;
        }

        buildOrderReceivedHeader(root, orderWrap);
        updateOrderOverviewLabels(root);
        formatOrderOverviewDate(root);
        updateOrderDetailsTitle(root);
        addOrderDetailsQuantityColumn(root);

        root.classList.add('cr-order-received-ready');
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (document.body.classList.contains('woocommerce-order-received')) {
            initCraftRootsOrderReceived();
            return;
        }

        initCraftRootsCheckout();
    });

    if (window.jQuery) {
        window.jQuery(document.body).on('updated_checkout', initCraftRootsCheckout);
    }
})();

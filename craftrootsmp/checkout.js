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

    const THANKYOU_MESSAGE = 'Gracias. Tu pedido ha sido recibido. Tu orden se está procesando.';

    function normalizeThankYouNotice(root) {
        let notice = root.querySelector('.woocommerce-thankyou-order-received');

        if (notice) {
            notice.className = 'cr-thankyou-banner';
            notice.removeAttribute('role');
            notice.textContent = THANKYOU_MESSAGE;
            return;
        }

        const orderWrap = root.querySelector('.woocommerce-order');
        if (!orderWrap || root.querySelector('.cr-thankyou-banner')) {
            return;
        }

        const banner = document.createElement('p');
        banner.className = 'cr-thankyou-banner';
        banner.textContent = THANKYOU_MESSAGE;
        orderWrap.insertBefore(banner, orderWrap.firstChild);
    }

    function getOverviewSlot(item) {
        if (item.classList.contains('woocommerce-order-overview__order')) {
            return 'order';
        }
        if (item.classList.contains('woocommerce-order-overview__date')) {
            return 'date';
        }
        if (item.classList.contains('woocommerce-order-overview__total')) {
            return 'total';
        }
        if (item.classList.contains('woocommerce-order-overview__payment-method')) {
            return 'payment';
        }
        if (item.classList.contains('payment-method') || item.classList.contains('method')) {
            const label = item.querySelector('strong');
            if (label && /m[eé]todo de pago/i.test(label.textContent)) {
                return 'payment';
            }
        }
        if (item.classList.contains('woocommerce-order-overview__email')) {
            return 'email';
        }
        return '';
    }

    function getOverviewItemValue(item) {
        const existingValue = item.querySelector('.cr-overview-value');
        if (existingValue) {
            return existingValue.innerHTML.trim();
        }

        const clone = item.cloneNode(true);
        const strong = clone.querySelector('strong');
        if (strong) {
            strong.remove();
        }

        return clone.innerHTML.trim();
    }

    function formatOverviewDateValue(value) {
        const plain = value.replace(/<[^>]*>/g, ' ').replace(/\s+/g, ' ').trim();
        const parsed = new Date(plain);

        if (isNaN(parsed.getTime())) {
            return value;
        }

        const day = String(parsed.getDate()).padStart(2, '0');
        const month = String(parsed.getMonth() + 1).padStart(2, '0');
        const year = parsed.getFullYear();

        return day + ' / ' + month + ' / ' + year;
    }

    function getPaymentOverviewValue(root, collected) {
        const payment = collected.find(function (entry) {
            return entry.slot === 'payment';
        });

        if (payment && payment.value) {
            return payment.value;
        }

        const paymentRow = root.querySelector('.woocommerce-table--order-details tfoot tr.payment_method td');
        if (paymentRow) {
            return paymentRow.innerHTML.trim();
        }

        const email = collected.find(function (entry) {
            return entry.slot === 'email';
        });

        return email ? email.value : '';
    }

    function restructureOrderOverview(root) {
        const list = root.querySelector('.woocommerce-order-overview');
        if (!list || list.classList.contains('cr-overview-ready')) {
            return;
        }

        const labels = {
            order: 'Número de pedido',
            date: 'Fecha',
            total: 'Total',
            payment: 'Método de pago'
        };

        const collected = Array.from(list.querySelectorAll('li')).map(function (item) {
            return {
                slot: getOverviewSlot(item),
                item: item,
                value: getOverviewItemValue(item)
            };
        }).filter(function (entry) {
            return entry.slot !== '';
        });

        const paymentValue = getPaymentOverviewValue(root, collected);
        const slots = ['order', 'date', 'total', 'payment'];
        const usedItems = [];

        slots.forEach(function (slot) {
            let entry = collected.find(function (row) {
                return row.slot === slot && usedItems.indexOf(row.item) === -1;
            });

            if (slot === 'payment' && !entry) {
                entry = collected.find(function (row) {
                    return row.slot === 'email' && usedItems.indexOf(row.item) === -1;
                });
            }

            if (!entry) {
                return;
            }

            usedItems.push(entry.item);

            let value = slot === 'payment' ? paymentValue : entry.value;
            if (slot === 'date') {
                value = formatOverviewDateValue(value);
            }

            entry.item.innerHTML =
                '<span class="cr-overview-label">' + labels[slot] + '</span>' +
                '<span class="cr-overview-value">' + value + '</span>';

            list.appendChild(entry.item);
        });

        collected.forEach(function (entry) {
            if (usedItems.indexOf(entry.item) === -1) {
                entry.item.remove();
            }
        });

        list.classList.add('cr-overview-ready');
    }

    function moveOrderDetailsTitleOutside(root) {
        let title = root.querySelector('.cr-order-details-title');
        const section = root.querySelector('.woocommerce-order-details');

        if (!title) {
            title = section && section.querySelector('.woocommerce-order-details__title');
        }

        if (!section || !title) {
            return;
        }

        title.classList.add('cr-order-details-title');
        title.textContent = 'Detalles del pedido:';

        if (title.parentNode !== section.parentNode || title.nextElementSibling !== section) {
            section.parentNode.insertBefore(title, section);
        }
    }

    function fixOrderDetailsTable(root) {
        const table = root.querySelector('.woocommerce-table--order-details');
        if (!table || table.classList.contains('cr-order-table-ready')) {
            return;
        }

        let headRow = table.querySelector('thead tr');
        if (!headRow) {
            const thead = document.createElement('thead');
            headRow = document.createElement('tr');
            thead.appendChild(headRow);
            table.insertBefore(thead, table.tBodies[0] || table.firstChild);
        }

        headRow.innerHTML =
            '<th class="product-name">Producto</th>' +
            '<th class="product-quantity">Cantidad</th>' +
            '<th class="product-total">Total</th>';

        table.querySelectorAll('tbody tr').forEach(function (row) {
            let nameCell = row.querySelector('td.product-name');
            let totalCell = row.querySelector('td.product-total');

            if (!nameCell && row.cells.length >= 2) {
                nameCell = row.cells[0];
                nameCell.classList.add('product-name');
            }

            if (!totalCell && row.cells.length >= 2) {
                totalCell = row.cells[row.cells.length - 1];
                totalCell.classList.add('product-total');
            }

            if (!nameCell || !totalCell) {
                return;
            }

            let qtyCell = row.querySelector('td.product-quantity');
            const qtyHolder = nameCell.querySelector('.cr-order-item-qty');
            const qtyEl = nameCell.querySelector('.product-quantity, .quantity');
            const qtyMatch = nameCell.textContent.match(/[×x]\s*(\d+)/i);
            const qty = qtyHolder
                ? qtyHolder.getAttribute('data-qty')
                : (qtyEl ? qtyEl.textContent.replace(/[^\d]/g, '') : (qtyMatch ? qtyMatch[1] : '1'));

            if (qtyHolder) {
                qtyHolder.remove();
            }

            if (qtyEl) {
                qtyEl.remove();
            }

            if (!qtyCell) {
                qtyCell = document.createElement('td');
                qtyCell.className = 'product-quantity';
            }

            qtyCell.textContent = qty;

            if (nameCell.nextSibling !== qtyCell) {
                row.insertBefore(qtyCell, totalCell);
            }

            if (nameCell.nextSibling && nameCell.nextSibling !== qtyCell) {
                row.insertBefore(nameCell, row.firstChild);
            }
        });

        table.classList.add('cr-order-table-ready');
    }

    function hideDuplicateIdentification(root) {
        root.querySelectorAll('.woocommerce-order > p, .woocommerce-customer-details ~ p').forEach(function (node) {
            if (/n[uú]mero de identificaci[oó]n/i.test(node.textContent)) {
                node.remove();
            }
        });
    }

    function initCraftRootsOrderReceived() {
        const root = getCheckoutRoot();
        const orderWrap = root && root.querySelector('.woocommerce-order');

        if (!root || !orderWrap) {
            return;
        }

        buildOrderReceivedHeader(root, orderWrap);
        normalizeThankYouNotice(root);
        restructureOrderOverview(root);
        moveOrderDetailsTitleOutside(root);
        fixOrderDetailsTable(root);
        hideDuplicateIdentification(root);

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

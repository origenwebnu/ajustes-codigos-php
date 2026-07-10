(function () {
    'use strict';

    const CONTINUE_SHOPPING_URL = 'https://laforeste.com/los-guardianes/';
    const PLACE_ORDER_TEXT = 'Pago en línea Bold';

    const FIELD_LABELS = {
        billing_first_name: 'Nombres',
        billing_last_name: 'Apellidos',
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
            label.textContent = FIELD_LABELS[fieldId] + ' ';

            if (required) {
                label.appendChild(required);
            } else if (optional) {
                label.appendChild(optional);
            }
        });

        const address2Label = root.querySelector('#billing_address_2_field label');
        if (address2Label) {
            address2Label.classList.remove('screen-reader-text');
        }

        const notesField = root.querySelector('#order_comments');
        if (notesField) {
            notesField.setAttribute('placeholder', '');
        }
    }

    function adjustFieldLayout(root) {
        const countryField = root.querySelector('#billing_country_field');
        if (!countryField) {
            return;
        }

        const idFieldSelectors = [
            '#billing_document_field',
            '#billing_cedula_field',
            '#billing_identification_field',
            '#billing_id_number_field',
            '#billing_nit_field'
        ];

        const hasIdField = idFieldSelectors.some(function (selector) {
            return root.querySelector(selector);
        });

        if (!hasIdField) {
            countryField.classList.remove('form-row-wide', 'form-row-last');
            countryField.classList.add('form-row-first');
        }
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
        if (!shippingCell || shippingCell.querySelector('.cr-shipping-free')) {
            return;
        }

        const label = shippingCell.querySelector('label');
        const isFree = label && /gratis|gratuito|free/i.test(label.textContent);

        shippingCell.innerHTML = '<span class="cr-shipping-free">' + (isFree ? 'Gratis' : (label ? label.textContent.trim() : 'Gratis')) + '</span>';
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
        adjustFieldLayout(root);
        updateSubtotalLabel(root);
        simplifyShippingDisplay(root);
        updatePlaceOrderButton(root);

        form.classList.add('cr-checkout-ready');
    }

    document.addEventListener('DOMContentLoaded', initCraftRootsCheckout);

    if (window.jQuery) {
        window.jQuery(document.body).on('updated_checkout', initCraftRootsCheckout);
    }
})();

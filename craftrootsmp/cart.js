(function () {
    'use strict';

    const SHIPPING_NOTE = 'Envío gratis a todo Colombia en compras elegibles. Los tiempos de entrega se calculan según tu ciudad al finalizar la compra.';

    function getShopUrl() {
        const backLink = document.querySelector('.ny-carrito .return-to-shop a, .ny-carrito a.wc-backward');
        if (backLink) {
            return backLink.getAttribute('href');
        }
        return '/shop/';
    }

    function removeEmptyTableColumns(container) {
        const removeHeader = container.querySelector('thead th.product-remove');
        if (removeHeader) {
            removeHeader.remove();
        }

        container.querySelectorAll('td.product-remove').forEach(function (cell) {
            cell.remove();
        });
    }

    function setSpanishTableHeaders(container) {
        const labels = {
            'product-name': 'Producto',
            'product-price': 'Precio',
            'product-quantity': 'Cantidad',
            'product-subtotal': 'Total'
        };

        Object.keys(labels).forEach(function (className) {
            const th = container.querySelector('thead th.' + className);
            if (th) {
                th.textContent = labels[className];
            }
        });
    }

    function moveRemoveIntoProductName(container) {
        container.querySelectorAll('tr.cart_item').forEach(function (row) {
            const removeLink = row.querySelector('td.product-remove a.remove');
            const nameCell = row.querySelector('td.product-name');

            if (removeLink && nameCell && !nameCell.contains(removeLink)) {
                nameCell.appendChild(removeLink);
            }
        });
    }

    function initQuantityStepper(input) {
        if (!input || input.closest('.cr-qty-stepper')) {
            return;
        }

        const min = parseFloat(input.getAttribute('min'));
        const max = parseFloat(input.getAttribute('max'));
        const step = parseFloat(input.getAttribute('step')) || 1;
        const minVal = isNaN(min) ? 1 : min;
        const maxVal = isNaN(max) ? Infinity : max;

        const wrapper = document.createElement('div');
        wrapper.className = 'cr-qty-stepper';

        const minus = document.createElement('button');
        minus.type = 'button';
        minus.className = 'cr-qty-minus';
        minus.setAttribute('aria-label', 'Disminuir cantidad');
        minus.textContent = '−';

        const plus = document.createElement('button');
        plus.type = 'button';
        plus.className = 'cr-qty-plus';
        plus.setAttribute('aria-label', 'Aumentar cantidad');
        plus.textContent = '+';

        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(minus);
        wrapper.appendChild(input);
        wrapper.appendChild(plus);

        function updateValue(direction) {
            let value = parseFloat(input.value);
            if (isNaN(value)) {
                value = minVal;
            }

            value = direction === 'up'
                ? Math.min(maxVal, value + step)
                : Math.max(minVal, value - step);

            input.value = value;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        minus.addEventListener('click', function () {
            updateValue('down');
        });

        plus.addEventListener('click', function () {
            updateValue('up');
        });
    }

    function buildCartHeader(container, itemCount, shopUrl) {
        if (container.querySelector('.cr-cart-header')) {
            return;
        }

        const form = container.querySelector('form.woocommerce-cart-form');
        if (!form) {
            return;
        }

        const header = document.createElement('div');
        header.className = 'cr-cart-header';
        header.innerHTML =
            '<div class="cr-cart-header__left">' +
                '<h1 class="cr-cart-header__title">Carrito de compras</h1>' +
                '<p class="cr-cart-header__count">(' + itemCount + ' ' + (itemCount === 1 ? 'Producto' : 'Productos') + ')</p>' +
            '</div>' +
            '<a href="' + shopUrl + '" class="cr-cart-header__continue">Seguir comprando</a>';

        container.insertBefore(header, form);
    }

    function ensureCartFormId(form) {
        if (!form) {
            return '';
        }

        if (!form.id) {
            form.id = 'cr-cart-form';
        }

        return form.id;
    }

    function linkFieldsToCartForm(form, element) {
        const formId = ensureCartFormId(form);
        if (!formId || !element) {
            return;
        }

        element.querySelectorAll('input, select, textarea, button').forEach(function (field) {
            if (!field.getAttribute('form')) {
                field.setAttribute('form', formId);
            }
        });
    }

    function moveCouponToSidebar(container, cartTotals) {
        const coupon = container.querySelector('.coupon');
        const form = container.querySelector('form.woocommerce-cart-form');

        if (!coupon || !cartTotals || cartTotals.querySelector('.cr-cart-coupon')) {
            return;
        }

        const wrapper = document.createElement('div');
        wrapper.className = 'cr-cart-coupon';
        wrapper.appendChild(coupon);

        const proceedBox = cartTotals.querySelector('.wc-proceed-to-checkout');
        if (proceedBox) {
            cartTotals.insertBefore(wrapper, proceedBox);
        } else {
            cartTotals.appendChild(wrapper);
        }

        linkFieldsToCartForm(form, coupon);

        const couponInput = coupon.querySelector('#coupon_code');
        if (couponInput) {
            couponInput.setAttribute('placeholder', 'Código de descuento o Cupón');
        }

        const applyButton = coupon.querySelector('[name="apply_coupon"]');
        if (applyButton) {
            applyButton.value = 'Aplicar';
        }
    }

    function addShippingNote(cartTotals) {
        if (!cartTotals || cartTotals.querySelector('.cr-shipping-note')) {
            return;
        }

        const orderTotalRow = cartTotals.querySelector('.order-total');
        if (!orderTotalRow) {
            return;
        }

        const note = document.createElement('div');
        note.className = 'cr-shipping-note';
        note.innerHTML = '<p>' + SHIPPING_NOTE + '</p>';
        orderTotalRow.parentNode.insertBefore(note, orderTotalRow);
    }

    function setupSidebarActions(container, cartTotals, shopUrl) {
        const form = container.querySelector('form.woocommerce-cart-form');
        const checkoutButton = cartTotals.querySelector('.checkout-button');
        const updateButton = container.querySelector('[name="update_cart"]');

        if (checkoutButton) {
            checkoutButton.textContent = 'Finalizar compra';
        }

        const totalsTitle = cartTotals.querySelector('h2');
        if (totalsTitle) {
            totalsTitle.textContent = 'Resumen de la compra';
        }

        if (checkoutButton && !cartTotals.querySelector('.cr-continue-shopping')) {
            const continueLink = document.createElement('a');
            continueLink.href = shopUrl;
            continueLink.className = 'cr-continue-shopping';
            continueLink.textContent = 'Seguir comprando';
            checkoutButton.insertAdjacentElement('afterend', continueLink);
        }

        if (updateButton && form && !cartTotals.querySelector('.cr-update-cart-wrap')) {
            updateButton.classList.add('cr-update-cart');
            updateButton.classList.remove('button', 'alt');
            updateButton.textContent = 'Actualizar carrito';
            updateButton.style.display = 'inline-block';
            linkFieldsToCartForm(form, updateButton);

            const wrap = document.createElement('div');
            wrap.className = 'cr-update-cart-wrap';
            wrap.appendChild(updateButton);

            const proceedBox = cartTotals.querySelector('.wc-proceed-to-checkout');
            if (proceedBox) {
                proceedBox.insertAdjacentElement('afterend', wrap);
            } else {
                cartTotals.appendChild(wrap);
            }
        }
    }

    function initCraftRootsCart() {
        const wrapper = document.querySelector('.ny-carrito');
        const container = wrapper && wrapper.querySelector('.woocommerce');

        if (!container || container.classList.contains('cr-cart-ready')) {
            return;
        }

        if (container.querySelector('.cart-empty')) {
            return;
        }

        const itemCount = container.querySelectorAll('tr.cart_item').length;
        if (!itemCount) {
            return;
        }

        const shopUrl = getShopUrl();
        const cartTotals = container.querySelector('.cart_totals');

        buildCartHeader(container, itemCount, shopUrl);
        setSpanishTableHeaders(container);
        moveRemoveIntoProductName(container);
        removeEmptyTableColumns(container);

        container.querySelectorAll('.quantity input.qty').forEach(initQuantityStepper);

        if (cartTotals) {
            moveCouponToSidebar(container, cartTotals);
            addShippingNote(cartTotals);
            setupSidebarActions(container, cartTotals, shopUrl);
        }

        container.classList.add('cr-cart-ready');
    }

    document.addEventListener('DOMContentLoaded', initCraftRootsCart);
    document.addEventListener('updated_wc_div', initCraftRootsCart);
})();

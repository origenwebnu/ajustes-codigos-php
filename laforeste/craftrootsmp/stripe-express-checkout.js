(function () {
    'use strict';

    function getStripeExpressElement() {
        return document.getElementById('wc-stripe-express-checkout-element')
            || document.querySelector('.wc-stripe-express-checkout-element');
    }

    function moveStripeOnCart(stripeEl) {
        const proceedBox = document.querySelector('.ny-carrito .cart_totals .wc-proceed-to-checkout');
        if (!proceedBox || proceedBox.contains(stripeEl)) {
            return false;
        }

        proceedBox.appendChild(stripeEl);
        return true;
    }

    function moveStripeOnCheckout(stripeEl) {
        const orderReview = document.querySelector('.ny-compraf #order_review');
        if (!orderReview || orderReview.contains(stripeEl)) {
            return false;
        }

        const payment = orderReview.querySelector('#payment');
        if (payment) {
            orderReview.insertBefore(stripeEl, payment);
            return true;
        }

        const orderTable = orderReview.querySelector('.woocommerce-checkout-review-order-table');
        if (orderTable) {
            orderTable.insertAdjacentElement('afterend', stripeEl);
            return true;
        }

        return false;
    }

    function relocateStripeExpressCheckout() {
        const stripeEl = getStripeExpressElement();
        if (!stripeEl) {
            return;
        }

        if (document.body.classList.contains('woocommerce-cart')) {
            moveStripeOnCart(stripeEl);
            return;
        }

        if (document.body.classList.contains('woocommerce-checkout')) {
            moveStripeOnCheckout(stripeEl);
        }
    }

    document.addEventListener('DOMContentLoaded', relocateStripeExpressCheckout);

    // Stripe inyecta el elemento de forma dinámica después del load
    const observer = new MutationObserver(function () {
        relocateStripeExpressCheckout();
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
})();

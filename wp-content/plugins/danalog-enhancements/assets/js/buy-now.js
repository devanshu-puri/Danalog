(() => {
    const FLAG_FIELD = 'danalog_buy_now';

    const isDisabled = (button) => {
        return button.classList.contains('disabled') || button.hasAttribute('disabled') || button.disabled;
    };

    const resetFlag = (hiddenInput) => {
        if (!hiddenInput) {
            return;
        }

        window.setTimeout(() => {
            hiddenInput.value = '0';
        }, 1500);
    };

    const focusFirstEmptySelect = (form) => {
        if (!form) {
            return;
        }

        const selects = form.querySelectorAll('.variations select');
        for (const select of selects) {
            if (!select.value) {
                select.focus();
                select.classList.add('danalog-buy-now-required');
                break;
            }
        }
    };

    const handleProductBuyNow = (button) => {
        const form = button.closest('form.cart');
        if (!form) {
            return;
        }

        const hidden = form.querySelector(`input[name="${FLAG_FIELD}"]`);
        if (!hidden) {
            return;
        }

        const addToCartButton = form.querySelector('.single_add_to_cart_button');
        if (addToCartButton && isDisabled(addToCartButton)) {
            focusFirstEmptySelect(form);
            return;
        }

        hidden.value = '1';

        const quantityInput = form.querySelector('input[name="quantity"]');
        if (quantityInput) {
            quantityInput.value = '1';
        }

        if (addToCartButton) {
            addToCartButton.click();
        } else {
            form.submit();
        }

        resetFlag(hidden);
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.danalog-buy-now-button');
        if (!button) {
            return;
        }

        event.preventDefault();

        if (isDisabled(button)) {
            return;
        }

        handleProductBuyNow(button);
    });

    document.addEventListener('change', (event) => {
        const target = event.target;
        if (!(target instanceof HTMLSelectElement)) {
            return;
        }

        if (!target.closest('.variations')) {
            return;
        }

        target.classList.remove('danalog-buy-now-required');
    });
})();

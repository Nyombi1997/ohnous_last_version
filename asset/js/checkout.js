(function ($) {
    var form = document.getElementById('checkout_form');
    var zoneSelect = document.getElementById('checkout_zone');
    var deliveryPriceElement = document.getElementById('checkout_delivery_price');
    var totalElement = document.getElementById('checkout_total');
    var paymentFeedback = document.getElementById('checkout_payment_feedback');
    var paymentMethodInputs = document.querySelectorAll('input[name="payment_method"]');
    var mobileMoneyFields = document.getElementById('mobile_money_fields');
    var customerNumberInput = document.getElementById('checkout_customer_number');
    var addressTextarea = document.getElementById('checkout_address');
    var checkoutConfig = window.ohnousCheckoutConfig || { subtotal: 0, mode: 'cart', visaEnabled: false };

    if (!form) {
        return;
    }

    function syncTextareaHeight(textarea) {
        if (!textarea) {
            return;
        }

        textarea.style.height = 'auto';
        textarea.style.height = Math.max(textarea.scrollHeight, 120) + 'px';
    }

    function selectedPaymentMethod() {
        var checked = document.querySelector('input[name="payment_method"]:checked');
        return checked ? checked.value : 'mobile_money';
    }

    function syncTotals() {
        var selectedOption = zoneSelect ? zoneSelect.options[zoneSelect.selectedIndex] : null;
        var deliveryPrice = selectedOption ? parseFloat(selectedOption.getAttribute('data-price') || '0') : 0;
        var total = parseFloat(checkoutConfig.subtotal || 0) + deliveryPrice;

        if (deliveryPriceElement) {
            deliveryPriceElement.innerText = deliveryPrice.toFixed(2);
        }

        if (totalElement) {
            totalElement.innerText = total.toFixed(2);
        }
    }

    function updateFeedback(message, type) {
        if (!paymentFeedback) {
            return;
        }

        paymentFeedback.classList.remove('is-success', 'is-error', 'is-pending');
        if (type) {
            paymentFeedback.classList.add(type);
        }
        paymentFeedback.querySelector('p').innerText = message;
    }

    function syncPaymentMethodUI() {
        var method = selectedPaymentMethod();

        $('.checkout-payment-method').removeClass('is-active');
        $('input[name="payment_method"]:checked').closest('.checkout-payment-method').addClass('is-active');

        if (mobileMoneyFields) {
            mobileMoneyFields.style.display = method === 'mobile_money' ? 'block' : 'none';
        }

        if (customerNumberInput) {
            customerNumberInput.required = method === 'mobile_money';
        }

        if (method === 'visa') {
            updateFeedback(
                checkoutConfig.visaEnabled
                    ? 'Le flux Visa est prêt à être branché selon la configuration FreshPay.'
                    : 'Visa est bientôt disponible sur ce checkout.',
                'is-pending'
            );
        } else {
            updateFeedback('Le paiement Mobile Money sera initié, puis confirmé de manière asynchrone par FreshPay.', 'is-pending');
        }
    }

    function buildErrorText(data) {
        if (!data) {
            return '';
        }

        if (data.shareable_error) {
            return data.shareable_error;
        }

        if (data.error_code && data.technical_error) {
            return data.error_code + ' | ' + data.technical_error;
        }

        if (data.error_code) {
            return data.error_code;
        }

        if (data.technical_error) {
            return data.technical_error;
        }

        return '';
    }

    if (zoneSelect) {
        zoneSelect.addEventListener('change', syncTotals);
    }

    if (addressTextarea) {
        addressTextarea.addEventListener('input', function () {
            syncTextareaHeight(addressTextarea);
        });
        syncTextareaHeight(addressTextarea);
    }

    paymentMethodInputs.forEach(function (input) {
        input.addEventListener('change', syncPaymentMethodUI);
    });

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        var submitButton = form.querySelector('button[type="submit"]');
        var tempText = submitButton ? submitButton.innerHTML : '';

        if (submitButton) {
            submitButton.setAttribute('disabled', '');
            submitButton.innerHTML = '<i class="fa-solid fa-circle-notch rotate"></i>';
        }

        updateFeedback('Paiement en cours de confirmation...', 'is-pending');

        $.post('/paiement-demarrer', $(form).serialize(), function (data) {
            var resultIcon = data.result === 'ok' ? 'success' : 'error';
            var resultText = data.reference ? ('Référence : ' + data.reference) : '';
            var technicalText = buildErrorText(data);

            if (data.payment_status === 'pending' || data.payment_status === 'submitted') {
                resultIcon = 'info';
                updateFeedback('Paiement initié. Nous attendons encore la confirmation finale de FreshPay.', 'is-pending');
            } else if (data.payment_status === 'success' || data.payment_status === 'paid' || data.payment_status === 'successful') {
                updateFeedback('Paiement confirmé.', 'is-success');
            } else {
                updateFeedback(data.msg || 'Le paiement a échoué.', 'is-error');
            }

            Swal.fire({
                icon: resultIcon,
                title: data.msg || 'Paiement traité.',
                html: technicalText !== ''
                    ? '<p style="font-size:16px; margin-bottom:8px;">' + (resultText || '') + '</p><p style="font-size:14px; word-break:break-word;"><strong>Détail technique :</strong><br>' + technicalText + '</p>'
                    : resultText,
                confirmButtonColor: '#6775d6'
            }).then(function () {
                if (data.redirect) {
                    window.location.href = data.redirect;
                }
            });
        }, 'json').fail(function (xhr) {
            var data = xhr.responseJSON || null;
            var technicalText = buildErrorText(data);

            updateFeedback("Le paiement n'a pas pu être initié. Veuillez réessayer.", 'is-error');
            Swal.fire({
                icon: 'error',
                title: "Le paiement n'a pas pu être initié.",
                html: technicalText !== ''
                    ? '<p style="font-size:14px; word-break:break-word;"><strong>Détail technique :</strong><br>' + technicalText + '</p>'
                    : '',
                confirmButtonColor: '#6775d6'
            });
        }).always(function () {
            if (submitButton) {
                submitButton.removeAttribute('disabled');
                submitButton.innerHTML = tempText;
            }
        });
    });

    syncTotals();
    syncPaymentMethodUI();
})(jQuery);

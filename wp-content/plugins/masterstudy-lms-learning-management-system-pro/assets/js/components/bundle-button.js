(function ($) {
    $(document).ready(function () {
        const $buy = $('.masterstudy-bundle-button_active');

        $buy.on('click', function(e) {
            const $this = $(this);

            if ( $this.attr('href') !== '#') {
                return;
            }

            e.preventDefault();

            $.ajax({
                url: stm_lms_ajaxurl,
                dataType: 'json',
                context: this,
                data: {
                    action: bundle_data.guest_checkout ? 'stm_lms_add_to_cart_guest' : 'stm_lms_add_bundle_to_cart',
                    item_id: $this.data('bundle'),
                    nonce: bundle_data.guest_checkout ? bundle_data.guest_nonce : bundle_data.nonce,
                },
                beforeSend: function () {
                    $this.addClass('masterstudy-bundle-button_loading');
                },
                complete: function (data) {
                    data = data['responseJSON'];
                    $this.removeClass('masterstudy-bundle-button_loading');
                    $this.find('.masterstudy-bundle-button__title').text(data['text']);
                    if (data['cart_url']) {
                        if (data['redirect']) window.location = data['cart_url'];
                        $this.attr('href', data['cart_url']);
                    }
                }
            });

            if (bundle_data.guest_checkout) {
                let item_id = $this.data('bundle');
                let currentCart = getCookie('stm_lms_notauth_cart');

                currentCart = (currentCart === undefined || currentCart === null) ? [] : JSON.parse(decodeURIComponent(currentCart));

                let item_id_str = item_id.toString();

                currentCart = currentCart.map(String);

                if (!currentCart.includes(item_id_str)) {
                    currentCart.push(item_id_str);
                }

                // Update cookies
                setCookie('stm_lms_notauth_cart', JSON.stringify(currentCart).replace(/"/g, ''), {path: '/'});

                // Get cookies
                function getCookie(name) {
                    const value = `; ${document.cookie}`;
                    const parts = value.split(`; ${name}=`);
                    if (parts.length === 2) return parts.pop().split(';').shift();
                }

                // Install cookies
                function setCookie(name, value, options = {}) {
                    document.cookie = `${name}=${value}; path=${options.path}`;
                }
            }
        });
    });
})(jQuery);
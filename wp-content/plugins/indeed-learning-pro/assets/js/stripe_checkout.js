/// redirect to checkout
var selector = document.getElementById( 'uap_js_stripe_settings' );
var key = selector.dataset.key;
var session_id = selector.dataset.session_id;
var stripe = Stripe( key );
stripe.redirectToCheckout({
      sessionId: session_id
}).then(function (result) {});

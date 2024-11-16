<div class="ulp-stripe-form ulp-display-none" id="ulp_stripe_payment_form_fields">
    <div class="ulp-stripe-field">
        <label><?php esc_html_e('Full name on card:', 'ulp');?></label>
        <input type="text" name="card_name" value="" />
    </div>
    <div class="ulp-stripe-field">
        <label><?php esc_html_e('Card number:', 'ulp');?></label>
        <input type="number" name="card_num" value="" />
    </div>
    <div class="ulp-stripe-field">
      <label><?php esc_html_e('Card expire Month:', 'ulp');?></label>
      <input type="number" name="card_exp_month" value="" placeholder="mm" max="12" min="1" />
    </div>
    <div class="ulp-stripe-field">
      <label><?php esc_html_e('Card expire Year:', 'ulp');?></label>
      <input type="number" name="card_exp_year" value="" placeholder="yy" min="10" max="99"/>
    </div>
    <div class="ulp-stripe-field">
      <label><?php esc_html_e('CVC:', 'ulp');?></label>
      <input type="number" name="cvc" value="" max="999" />
    </div>
</div>

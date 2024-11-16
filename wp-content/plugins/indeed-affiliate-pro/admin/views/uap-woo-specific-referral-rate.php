<div id="uap_woo_wsr" class="panel woocommerce_options_panel options_group" >

    <p><?php esc_html_e( 'Customize Referral Rate for this product. These settings will be used to calculate Referrals when this product is purchased.', 'uap');?></p>

    <p class="form-field">
        <label><?php esc_html_e('Referral Rate Type', 'uap');?></label>
          <select name="uap-woo-wsr-type" class="select short">
              <?php if ( $data['types'] ):?>
                  <?php foreach ( $data['types'] as $key => $value ):?>
                      <option value="<?php echo esc_attr($key);?>" <?php echo ( $data['uap-woo-wsr-type'] == $key ) ?  'selected' : '';?> ><?php echo esc_html($value);?></option>
                  <?php endforeach;?>
              <?php endif;?>
          </select>
    </p>

    <p class="form-field">
        <label><?php esc_html_e('Referral Rate', 'uap');?></label>
        <input type="number" step="0.01" min="0" class="short" name="uap-woo-wsr-value" value="<?php echo esc_attr($data['uap-woo-wsr-value']);?>" />
    </p>
    <p class="form-field">
    <?php
    $offerType = get_option( 'uap_referral_offer_type' );
    if ( $offerType == 'biggest' ){
    		$offerType = esc_html__( 'Biggest', 'uap' );
    } else {
    		$offerType = esc_html__( 'Lowest', 'uap' );
    }
    echo esc_html__( 'If there are multiple Amounts set for the same action, like Ranks, Offers, Product or Category rate the ', 'uap' ) . '<strong>' . $offerType . '</strong> ' . esc_html__( 'will be taken in consideration. You may change that from', 'uap' ) . ' <a href="' . admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=settings' ) . '" target="_blank">' . esc_html__( 'here.', 'uap' ) . '</a>';
    ?>
    </p>

    <p class="form-field">
        <?php $checked = (int)($data['uap-woo-excluded-prod']) === 1 ? 'checked' : '';?>
        <label><?php esc_html_e('Disable Referrals', 'uap');?></label>

        <input type="checkbox" class="checkbox" onClick="uapCheckAndH(this, '#uap-woo-excluded-prod');"  <?php echo $checked;?> />
        <input type="hidden" name="uap-woo-excluded-prod" value="<?php echo $data['uap-woo-excluded-prod'];?>" id="uap-woo-excluded-prod" />
    </p>
    <p class="form-field"><?php echo esc_html__( 'Enabling this option will prevent referrals from being generated for this product. This setting takes precedence over all other referral rate configurations.', 'uap' );?></p>

</div>

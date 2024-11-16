<tr class="form-field">
    <td colspan="2"><h3><?php esc_html_e( 'Ultimate Affiliate Pro - Custom Referral Rate', 'uap');?></h3></td>
</tr>
<tr class="form-field">
    <td><label><?php esc_html_e('Referral Rate Type', 'uap');?></label></td>
    <td>
        <select name="uap_referral_type" class="select short">
              <?php if ( $data['types'] ):?>
                  <?php foreach ( $data['types'] as $key => $value ):?>
                      <option value="<?php echo esc_attr($key);?>" <?php echo ( $data['uap_referral_type'] == $key ) ? 'selected' : '';?> ><?php echo esc_html($value);?></option>
                  <?php endforeach;?>
              <?php endif;?>
        </select>
    </td>
</tr>
<tr class="form-field">
      <td><label><?php esc_html_e('Referral Rate', 'uap');?></label></td>
      <td><input type="number" step="0.01" min="0" class="short" name="uap_referral_value" value="<?php echo esc_attr($data['uap_referral_value']);?>" /></td>
</tr>
<tr class="form-field">
    <td></td>
    <td>
      <?php
      $offerType = get_option( 'uap_referral_offer_type' );
      if ( $offerType == 'biggest' ){
      		$offerType = esc_html__( 'Biggest', 'uap' );
      } else {
      		$offerType = esc_html__( 'Lowest', 'uap' );
      }
      echo esc_html__( 'If there are multiple Amounts set for the same action, like Ranks, Offers, Product or Category rate the ', 'uap' ) . '<strong>' . $offerType . '</strong> ' . esc_html__( 'will be taken in consideration. You may change that from', 'uap' ) . ' <a href="' . admin_url( 'admin.php?page=ultimate_affiliates_pro&tab=settings' ) . '" target="_blank">' . esc_html__( 'here.', 'uap' ) . '</a>';
      ?>
    </td>
</tr>
<tr class="form-field">
    <td><?php esc_html_e( 'Disable Referrals for Category', 'uap');?></td>
    <td>
      <?php $checked = (int)($data['uap_excluded']) === 1 ? 'checked' : '';?>
      <input type="checkbox" class="checkbox" onClick="uapCheckAndH(this, '#uap_excluded');"  <?php echo $checked;?> />
      <input type="hidden" name="uap_excluded" value="<?php echo $data['uap_excluded'];?>" id="uap_excluded" />
    </td>
</tr>
<tr class="form-field">
    <td></td>
    <td>
      <?php
      echo esc_html__( 'Enabling this option will prevent referrals from being generated for any products within this category. This setting supersedes all other referral rate configurations.', 'uap' );
      ?>
    </td>
</tr>

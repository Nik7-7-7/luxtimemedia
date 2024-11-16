<!-- TAXES -->
<?php if ( isset($data['taxesData']['show']) && isset($data['taxesData']['details'])  && is_array($data['taxesData']['details']) && count($data['taxesData']['details']) > 0): ?>
    <div class="ihc-checkout-page-box-wrapper ihc-taxes-wrapper">
      <div class="ihc-checkout-page-box-title"><?php echo esc_ump_content($data['messages']['ihc_checkout_taxes_title']);?></div>
      <table  class="ihc-product-details-table ihc-subtotal-table">
         <?php foreach ($data['taxesData']['details']['items'] as $item){ ?>
        <tr>
          <td><div class="ihc-tax-label"><?php echo esc_ump_content($item['label']); ?></div></td>
          <td><div class="ihc-tax-price"><?php echo esc_ump_content($item['percentage']).'% '; ?></div></td>
        </tr>
        <?php } ?>
      </table>
    </div>
<?php endif;?>

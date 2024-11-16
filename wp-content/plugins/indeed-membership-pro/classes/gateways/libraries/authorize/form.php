<form method="post" action="<?php echo $args['target_url'];?>" id="authorize_form">
  <input type="hidden" name="x_login" value="<?php echo $args['x_login'];?>" />
  <input type="hidden" name="x_amount" value="<?php echo $args['x_amount'];?>" />
  <input type="hidden" name="x_currency_code" value="<?php echo $args['x_currency_code'];?>" />
  <input type="hidden" name="x_type" value="AUTH_CAPTURE" />
  <input type="hidden" name="x_description" value="<?php echo $args['x_description'];?>" />
  <input type="hidden" name="x_invoice_num" value="<?php echo $args['x_invoice_num'];?>" />
  <input type="hidden" name="x_fp_sequence" value="<?php echo $args['x_fp_sequence'];?>" />
  <input type="hidden" name="x_fp_timestamp" value="<?php echo $args['x_fp_timestamp'];?>" />
  <input type="hidden" name="x_fp_hash" value="<?php echo $args['x_fp_hash'];?>" />
  <input type="hidden" name="x_relay_response" value="FALSE" />
  <input type="hidden" name="x_relay_url" value="<?php echo $args['x_relay_url'];?>" />
  <input type="hidden" name="x_cust_id" value="<?php echo $args['x_cust_id'];?>" />
  <input type="hidden" name="x_po_num" value="<?php echo $args['x_po_num'];?>" />
  <input type="hidden" name="x_test_request" value="<?php echo $args['x_test_request'];?>" />
  <input type="hidden" name="x_show_form" value="PAYMENT_FORM" />
</form>
<script>
  document.forms[0].submit();
</script>

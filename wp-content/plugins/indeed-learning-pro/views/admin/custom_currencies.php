<form  method="post">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />

	<div class="ulp-stuffbox">
		<h3 class="ulp-h3"><?php esc_html_e('Add new Currency', 'ulp');?></h3>

  			<div class="inside">
  			  <h2><?php esc_html_e('Custom Currency', 'ulp');?></h2>
  		    <p><?php esc_html_e('Add new currencies beside the predefined list based on custom Symbols', 'ulp');?></p>

  				<div class="ulp-form-line">
  					<label class="ulp-labels-special"><?php esc_html_e('Code:', 'ulp');?></label>
  					<input type="test" value="" name="new_currency_code" />
  					<p><?php esc_html_e('Insert a valid Currency Code, ex: ', 'ulp');?><span><strong><?php esc_html_e('USD, EUR, CAD.', 'ulp');?></strong></span></p>
  				</div>

  				<div class="ulp-form-line">
  					<label class="ulp-labels-special"><?php esc_html_e('Name:', 'ulp');?></label>
  					<input type="text" value="" name="new_currency_name" />
  				</div>

  				<div class="ulp-submit-form">
  					<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="ulp_save" class="btn btn-primary pointer" />
  				</div>

  			</div>
	</div>
</form>
<?php if ($data ['currencies']!==FALSE && count($data ['currencies'])>0):?>
    <div class="ulp-stuffbox">
      <table class="wp-list-table widefat fixed tags ulp-special-table">
        <thead>
          <tr>
            <th class="manage-column">Code</th>
            <th class="manage-column">Name</th>
            <th class="manage-column ulp-text-aling-center ulp-table-delete-col">Delete</th>
          </tr>
        </thead>
        <tbody>
          <?php	foreach ($data ['currencies'] as $code=>$name):?>
              <tr id="<?php echo esc_attr('ulp_div_' . $code);?>">
                <td><?php echo esc_html($code);?></td>
                <td><?php echo esc_html($name);?></td>
                <td class="ulp-text-aling-center"><i class="fa-ulp fa-remove-ulp" onClick="ulpRemoveCurrency('<?php echo esc_attr($code);?>');"></i></td>
              </tr>
          <?php endforeach;	?>
        </tbody>
      </table>
    </div>
<?php endif;?>

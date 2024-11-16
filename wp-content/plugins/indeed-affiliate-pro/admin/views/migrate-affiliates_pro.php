<div class="uap-wrapper">
	<div class="uap-stuffbox">
		<h3 class="uap-h3"><?php esc_html_e('Affiliates Pro migration', 'uap');?></h3>
		<div class="inside">
		<div class="row">
		  <div class="col-xs-12">
			<div class="uap-form-line">
            <h2><?php esc_html_e('Migrate from Affiliates Pro script', 'uap');?></h2>
            <p><?php esc_html_e('Copy the Affiliate users and stored Referrals from Affiliates Pro script. You can assign a specific UAP Rank to new copied affiliate users during this process.', 'uap'); ?></p>

            <p><strong><?php esc_html_e('Note: Affiliates and Referrals will not be removed after migration for safety reasons. You will have to manually remove them.', 'uap'); ?></strong></p>
          </div>
            <div class="uap-line-break"></div>
            <div class="uap-form-line">
            <h4><?php esc_html_e('Assign rank:', 'uap');?></h4>
              <select class="uap-migrate-assign-rank">
                <?php foreach ($data['ranks_available'] as $k=>$v):?>
    							<?php $selected = ( isset( $data['rank_id'] ) && $k == $data['rank_id']) ? 'selected' : '';?>
    							<option value="<?php echo esc_attr($k);?>" <?php echo esc_attr($selected);?>><?php echo esc_html($v);?></option>
    						<?php endforeach;?>
              </select>
          </div>
          <div class="uap-line-break"></div>
            <div class="uap-form-line">
          <div>
              <div class="uap-progress-bar-wrapp"></div>
          </div>
          <span class="uap-trigger-event-migrate button button-primary button-large" data-type="affiliates-pro"><?php esc_html_e('Trigger', 'uap');?></span>
      </div>

		</div>
	</div>
    </div>
	</div>

</div>

<?php wp_enqueue_script('uap-migration-ajax', UAP_URL . 'assets/js/migration-ajax.js', array(), null);?>

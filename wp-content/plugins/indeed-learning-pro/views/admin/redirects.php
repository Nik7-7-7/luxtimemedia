<form  method="post" role = "form">
	<input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
	
	<div class="ulp-stuffbox">

    <h3 class="ulp-h3"><?php esc_html_e('Redirects', 'ulp');?></h3>

    <div class="inside">
	<div class="ulp-inside-item">
      <div class="row">
          <div class="col-xs-4">
            <h2><?php esc_html_e('Default Redirects', 'ulp');?></h2>
			<p><?php esc_html_e('Establish the official system redirects after specific stages. ', 'ulp');?></p>

        	<h4><?php esc_html_e('Default redirect', 'ulp');?></h4>
				<div class="form-group row">
						<select name="ulp_default_redirect" class="form-control m-bot15">
			        <option value="-1" <?php echo ($data['metas']['ulp_default_redirect']==-1) ? 'selected' : '';?> >...</option>
							<?php	if ($data['pages']):?>
								<?php foreach ($data['pages'] as $k => $v):?>
									<?php $selected = $data['metas']['ulp_default_redirect']==$v->ID ? 'selected' : '';?>
									<option value="<?php echo esc_attr($v->ID);?>" <?php echo esc_attr($selected);?> ><?php echo esc_ulp_content($v->post_title);?></option>
								<?php endforeach;?>
			        <?php endif;?>
						</select>
						<?php if ($data['metas']['ulp_default_redirect']>-1):?>
							<?php $permalink = get_permalink($data['metas']['ulp_default_redirect']);?>
							<div class="ulp-link-to-public"><?php echo esc_html__('View Page: ', 'ulp'); ?><a href="<?php echo esc_url($permalink);?>" target="_blank" ><?php echo esc_url($permalink);?></a></div>
						<?php endif;?>
				</div>


			<h4><?php esc_html_e('User profile redirect', 'ulp');?></h4>
              <div class="form-group row">
                      <select name="ulp_user_profile_redirect" class="form-control m-bot15">
                  <option value="-1" <?php echo ($data['metas']['ulp_user_profile_redirect']==-1) ? 'selected' : '';?> >...</option>
                          <?php	if ($data['pages']):?>
                              <?php foreach ($data['pages'] as $k => $v):?>
                                  <?php $selected = $data['metas']['ulp_user_profile_redirect']==$v->ID ? 'selected' : '';?>
                                  <option value="<?php echo esc_attr($v->ID);?>" <?php echo esc_attr($selected);?> ><?php echo esc_ulp_content($v->post_title);?></option>
                              <?php endforeach;?>
                  <?php endif;?>
                      </select>
                      <?php if ($data['metas']['ulp_user_profile_redirect']>-1):?>
													<?php $permalink = get_permalink($data['metas']['ulp_user_profile_redirect']);?>
                          <div class="ulp-link-to-public"><?php echo esc_html__('View Page: ', 'ulp'); ?><a href="<?php echo esc_url($permalink);?>" target="_blank" ><?php echo esc_url($permalink);?></a></div>
                      <?php endif;?>
              </div>

			<h4><?php esc_html_e('Unregistered user try to buy redirect', 'ulp');?></h4>
			<div class="form-group row">
							<select name="ulp_unregistered_user_try_to_buy_redirect" class="form-control m-bot15">
									<option value="-1" <?php echo ($data['metas']['ulp_unregistered_user_try_to_buy_redirect']==-1) ? 'selected' : '';?> >...</option>
									<?php	if ($data['pages']):?>
											<?php foreach ($data['pages'] as $k => $v):?>
													<?php $selected = $data['metas']['ulp_unregistered_user_try_to_buy_redirect']==$v->ID ? 'selected' : '';?>
													<option value="<?php echo esc_attr($v->ID);?>" <?php echo esc_attr($selected);?> ><?php echo esc_ulp_content($v->post_title);?></option>
											<?php endforeach;?>
									<?php endif;?>
							</select>
							<?php if ($data['metas']['ulp_unregistered_user_try_to_buy_redirect']>-1):?>
									<?php $permalink = get_permalink($data['metas']['ulp_unregistered_user_try_to_buy_redirect']);?>
									<div class="ulp-link-to-public"><?php echo esc_html__('View Page: ', 'ulp'); ?><a href="<?php echo esc_url($permalink);?>" target="_blank" ><?php echo esc_url($permalink);?></a></div>
							<?php endif;?>
			</div>



		</div>
      </div>
    </div>
  	<div class="ulp-line-break"></div>
	<div class="ulp-inside-item">
      <div class="row">
          <div class="col-xs-6">

    		<div class="form-group row">
    				<div class="col-4">
    						<input type="submit" name="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>"  class="btn btn-primary pointer" />
    			  </div>
    		</div>
		</div>
      </div>
    </div>
	</div>
 </div>
</form>

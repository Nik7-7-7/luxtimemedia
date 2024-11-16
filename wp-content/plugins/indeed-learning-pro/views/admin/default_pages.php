<form  method="post" role = "form">
    <input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
    
<div class="ulp-stuffbox">

    <h3 class="ulp-h3"><?php esc_html_e('Default Pages', 'ulp');?></h3>

    <div class="inside">
	<div class="ulp-inside-item">
      <div class="row">
          <div class="col-xs-4">
            <h2><?php esc_html_e('FrontEnd Pages', 'ulp');?></h2>
		<p><?php esc_html_e('Establish the official system pages for specific sections. ', 'ulp');?></p>

        <h4><?php esc_html_e('List Courses', 'ulp');?></h4>
		<div class="form-group row">

			<select name="ulp_default_page_list_courses"  class="form-control m-bot15">
        <option value="-1" <?php echo ($data['metas']['ulp_default_page_list_courses']==-1) ? 'selected' : '';?> >...</option>
				<?php	if ($data['pages']):?>
					<?php foreach ($data['pages'] as $k => $v):?>
						<?php $selected = $data['metas']['ulp_default_page_list_courses']==$v->ID ? 'selected' : '';?>
						<option value="<?php echo esc_attr($v->ID);?>" <?php echo esc_attr($selected);?> ><?php echo esc_attr($v->post_title);?></option>
					<?php endforeach;?>
        <?php endif;?>
				</select>
				<?php if ($data['metas']['ulp_default_page_list_courses']>-1):?>
          <?php $permalink = get_permalink($data['metas']['ulp_default_page_list_courses']);?>
				<div class="ulp-link-to-public"><?php echo esc_html__('View Page: ', 'ulp'); ?><a href="<?php echo esc_url($permalink);?>" target="_blank" ><?php echo esc_url($permalink);?></a></div>
				<?php endif;?>
			</div>

		<h4><?php esc_html_e('Student Profile', 'ulp');?></h4>
      <div class="form-group row">

  			<select name="ulp_default_page_student_profile"  class="form-control m-bot15">
          <option value="-1" <?php echo ($data['metas']['ulp_default_page_student_profile']==-1) ? 'selected' : '';?> >...</option>
  				<?php	if ($data['pages']):?>
  					<?php foreach ($data['pages'] as $k => $v):?>
  						<?php $selected = $data['metas']['ulp_default_page_student_profile']==$v->ID ? 'selected' : '';?>
  						<option value="<?php echo esc_attr($v->ID);?>" <?php echo esc_attr($selected);?> ><?php echo esc_ulp_content($v->post_title);?></option>
  					<?php endforeach;?>
          <?php endif;?>
  				</select>
					<?php if ($data['metas']['ulp_default_page_student_profile']>-1):?>
            <?php $permalink = get_permalink($data['metas']['ulp_default_page_student_profile']);?>
						<div class="ulp-link-to-public"><?php echo esc_html__('View Page: ', 'ulp'); ?><a href="<?php echo esc_url($permalink);?>" target="_blank" ><?php echo esc_url($permalink);?></a></div>
					<?php endif;?>
  			</div>


	  			<h4><?php esc_html_e('Become Instructor', 'ulp');?></h4>
				<div class="form-group row">
	  			<select name="ulp_default_page_become_instructor" class="form-control m-bot15">
	          <option value="-1" <?php echo ($data['metas']['ulp_default_page_become_instructor']==-1) ? 'selected' : '';?> >...</option>
	  				<?php	if ($data['pages']):?>
	  					<?php foreach ($data['pages'] as $k => $v):?>
	  						<?php $selected = $data['metas']['ulp_default_page_become_instructor']==$v->ID ? 'selected' : '';?>
	  						<option value="<?php echo esc_attr($v->ID);?>" <?php echo esc_attr($selected);?> ><?php echo esc_html($v->post_title);?></option>
	  					<?php endforeach;?>
	          <?php endif;?>
	  				</select>
						<?php if ($data['metas']['ulp_default_page_become_instructor']>-1):?>
              <?php $permalink = get_permalink($data['metas']['ulp_default_page_become_instructor']);?>
							<div class="ulp-link-to-public"><?php echo esc_html__('View Page: ', 'ulp'); ?><a href="<?php echo esc_url($permalink);?>" target="_blank" ><?php echo esc_url($permalink);?></a></div>
						<?php endif;?>
	  		</div>

				<?php $display = get_option('ulp_watch_list_enable') ? 'block' : 'none';?>

                <h4><?php esc_html_e('Wish List', 'ulp');?></h4>
                <div class="form-group row">

		  			<select name="ulp_default_page_list_watch_list" class="form-control m-bot15">
		          	<option value="-1" <?php echo ($data['metas']['ulp_default_page_list_watch_list']==-1) ? 'selected' : '';?> >...</option>
					  		<?php	if ($data['pages']):?>
					  				<?php foreach ($data['pages'] as $k => $v):?>
					  						<?php $selected = $data['metas']['ulp_default_page_list_watch_list']==$v->ID ? 'selected' : '';?>
					  						<option value="<?php echo esc_attr($v->ID);?>" <?php echo esc_attr($selected);?> ><?php echo esc_ulp_content($v->post_title);?></option>
					  				<?php endforeach;?>
					     <?php endif;?>
					  </select>
						<?php if ($data['metas']['ulp_default_page_list_watch_list']>-1):?>
                <?php $permalink = get_permalink($data['metas']['ulp_default_page_list_watch_list']);?>
								<div class="ulp-link-to-public"><?php echo esc_html__('View Page: ', 'ulp'); ?><a href="<?php echo esc_url($permalink);?>" target="_blank" ><?php echo esc_url($permalink);?></a></div>
						<?php endif;?>
			  </div>

				<h4><?php esc_html_e('Checkout', 'ulp');?></h4>
				<div class="form-group row">

				   	<select name="ulp_default_page_checkout" class="form-control m-bot15">
						   	<option value="-1" <?php echo ($data['metas']['ulp_default_page_checkout']==-1) ? 'selected' : '';?> >...</option>
						 		<?php	if ($data['pages']):?>
										<?php foreach ($data['pages'] as $k => $v):?>
						   					<?php $selected = $data['metas']['ulp_default_page_checkout']==$v->ID ? 'selected' : '';?>
												<option value="<?php echo esc_attr($v->ID);?>" <?php echo esc_attr($selected);?> ><?php echo esc_ulp_content($v->post_title);?></option>
										<?php endforeach;?>
								<?php endif;?>
						</select>
						<?php if ($data['metas']['ulp_default_page_checkout']>-1):?>
                <?php $permalink = get_permalink($data['metas']['ulp_default_page_checkout']);?>
								<div class="ulp-link-to-public"><?php echo esc_html__('View Page: ', 'ulp'); ?><a href="<?php echo esc_url($permalink);?>" target="_blank" ><?php echo esc_url($permalink);?></a></div>
						<?php endif;?>
				</div>

        <h4><?php esc_html_e('Instructor Dashboard', 'ulp');?></h4>
				<div class="form-group row">
				   	<select name="ulp_default_page_instructor_dashboard" class="form-control m-bot15">
						   	<option value="-1" <?php echo ($data['metas']['ulp_default_page_instructor_dashboard']==-1) ? 'selected' : '';?> >...</option>
						 		<?php	if ($data['pages']):?>
										<?php foreach ($data['pages'] as $k => $v):?>
						   					<?php $selected = $data['metas']['ulp_default_page_instructor_dashboard']==$v->ID ? 'selected' : '';?>
												<option value="<?php echo esc_attr($v->ID);?>" <?php echo esc_attr($selected);?> ><?php echo esc_ulp_content($v->post_title);?></option>
										<?php endforeach;?>
								<?php endif;?>
						</select>
						<?php if ($data['metas']['ulp_default_page_instructor_dashboard']>-1):?>
                <?php $permalink = get_permalink($data['metas']['ulp_default_page_instructor_dashboard']);?>
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

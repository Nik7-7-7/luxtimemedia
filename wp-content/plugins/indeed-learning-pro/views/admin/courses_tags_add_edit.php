
<form action="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=courses_tags');?>" method="post">
  <input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
  
  <div class="ulp-stuffbox">
  		<h3 class="ulp-h3"><?php esc_html_e('Add/Edit Course tag', 'ulp');?></h3>
    	<div class="inside">
        <div class="ulp-inside-item">
   		<div class="row">
          <div class="col-xs-8">
            <input type="hidden" name="term_id" value="<?php echo esc_attr($object->term_id);?>" />
    				<div class="input-group ulp-input-group-max ulp-input-group-space">
    					<span class="input-group-addon" ><?php esc_html_e('Slug:', 'ulp');?></span>
    					<input type="text" class="form-control" value="<?php echo esc_attr($object->slug);?>" name="slug">
    				</div>
                     <p><i><?php esc_html_e("slug must be unique and based only on lowercase characters and not additional symbols", 'ulp');?></i></p>
    				<div class="input-group ulp-input-group-max ulp-input-group-space">
    					<span class="input-group-addon"><?php esc_html_e('Label:', 'ulp');?></span>
    					<input type="text" class="form-control" value="<?php echo esc_attr($object->name);?>" name="label">
    				</div>
            <div class="input-group  ulp-input-group-space">
    					<h4><?php esc_html_e('Color:', 'ulp');?></h4>
              <p><?php esc_html_e("Choose one of the predefined colors", 'ulp');?></p>

              <div  class="ulp-user-list-wrap">
              	<ul id="colors_ul" class="colors_ul">
                  <?php
                        $color_scheme = ['#0a9fd8', '#38cbcb', '#27bebe', '#0bb586', '#94c523', '#6a3da3', '#f1505b', '#ee3733', '#f36510', '#f8ba01'];
                        $i = 0;
                        foreach ($color_scheme as $color){
                            $class = ($object->color==$color) ? 'color-scheme-item-selected' : 'color-scheme-item';
                            ?>
                            <li class="<?php echo esc_attr($class);?> ulp-background-color-<?php echo substr($color,1);?>" onClick="ulpChangeColorScheme(this, '<?php echo esc_attr($color);?>', '#color_scheme');ulpPreviewShortcode();"></li>
                            <?php
                            $i++;
                        }
                  ?>
                  <div class='ulp-clear'></div>
                  </ul>
                  <input type="hidden" id="color_scheme" name="color" value="<?php echo esc_attr($object->color);?>" />
              </div>
    				</div>
            <div class="input-group ulp-input-group-max ulp-input-group-space ulp-display-none">
    					<span  ><?php esc_html_e('Description:', 'ulp');?></span>
    					<textarea class="form-control" name="description"><?php echo (isset($object->description)) ? $object->description : '';?></textarea>
    				</div>
    				<div class="ulp-submit-form">
    					<input type="submit" value="<?php esc_html_e('Save Changes', 'ulp');?>" name="save_tag" class="btn btn-primary pointer"/>
    				</div>
    	      </div>
            </div>
          </div>
        </div>
  	</div>
</form>

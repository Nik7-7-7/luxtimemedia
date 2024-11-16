<?php
//return html meta boxes for admin section
global $post;
$meta_arr = ihc_post_metas($post->ID);
?>

<div class="ihc-class ihc-padding">
	<select class="ihc-fullwidth ihc-select" name="ihc_mb_type" id="ihc_mb_type" onChange="ihcShowHideDrip();">
		<option value="show" <?php echo ($meta_arr['ihc_mb_type']=='show') ? 'selected' : ''; ?> ><?php esc_html_e('Show Page Only', 'ihc');?></option>
		<option value="block" <?php echo ($meta_arr['ihc_mb_type']=='block') ? 'selected' : ''; ?> ><?php esc_html_e('Block Page Only', 'ihc');?></option>
	</select>
</div>


<div>
	<div  class="ihc-padding ihc-text-aling-right">
	<label class="ihc-bold">...<?php esc_html_e('for', 'ihc');?></label>
		<?php
			if (isset($meta_arr['ihc_mb_who']) && strpos($meta_arr['ihc_mb_who'], ',')!==FALSE){
				$arr = explode(',', $meta_arr['ihc_mb_who']);
			} else {
				$arr[] = $meta_arr['ihc_mb_who'];
			}
			$posible_values = array('all'=>esc_html__('All', 'ihc'), 'reg'=>esc_html__('Registered Users','ihc'), 'unreg'=>esc_html__('Unregistered Users','ihc') );

			$levels = \Indeed\Ihc\Db\Memberships::getAll();
			if ($levels){
				foreach ($levels as $id=>$level){
					$posible_values[$id] = $level['label'];
				}
			}
			?>
			<select id="ihc-change-target-user-set" onChange="ihcWriteTagValueForEditPost(this, '#ihc_mb_who-hidden', '#ihc_tags_field', 'ihc_select_tag_' );">
				<option value="-1" selected>...</option>
				<?php
					foreach ($posible_values as $k=>$v){
						?>
						<option value="<?php echo esc_attr( $k );?>"><?php echo esc_html( $v );?></option>
						<?php
					}
				?>
			</select>
	</div>
			<div id="ihc_tags_field">
            	<?php
            		if (isset($meta_arr['ihc_mb_who'])){
                    	if (strpos($meta_arr['ihc_mb_who'], ',')!==FALSE){
                    		$values = explode(',', $meta_arr['ihc_mb_who']);
                    	}
                        else {
                        	$values[] = $meta_arr['ihc_mb_who'];
                        }
                        if (count($values)){
                        	foreach ($values as $value){
                        		if (isset($posible_values[$value])){
                        			?>
		                        		<div id="ihc_select_tag_<?php echo esc_attr($value);?>" class="ihc-tag-item">
		                        	    	<?php echo esc_html($posible_values[$value]);?>
		                        	    	<div class="ihc-remove-tag" onclick="ihcremoveTagForEditPost('<?php echo esc_attr($value);?>', '#ihc_select_tag_', '#ihc_mb_who-hidden');" title="<?php esc_html_e('Removing tag', 'ihc');?>">x</div>
		                        	    </div>
		                        	<?php
                        		}
                        	}//end of foreach
                        }
                        ?>
                    <div class="ihc-clear"></div>
                    <?php }//end of if ?>

			</div>
			<div class="ihc-clear"></div>
			<input type="hidden" id="ihc_mb_who-hidden" name="ihc_mb_who" value="<?php echo esc_attr( $meta_arr['ihc_mb_who'] );?>" />
			<div class="clear"></div>

</div>
<div class="ihc-separator"></div>
<div class="ihc-class ihc-padding">
	<label><?php esc_html_e('If is not allow', 'ihc');?>...</label>
	<?php
		$select_types = array('redirect'=>esc_html__('Redirect the Page', 'ihc'), 'replace'=>esc_html__('Replace the Content', 'ihc') );
		if ($post->ID==get_option('ihc_general_redirect_default_page')){
			unset($select_types['redirect']); //unset redirect from select options
			$meta_arr['ihc_mb_block_type'] = 'replace';//force 'ihc_mb_block_type' to be replace
			update_option('ihc_mb_block_type', 'replace');//alse change the value in db
		}
	?>
	<select class="ihc-fullwidth ihc-select" name="ihc_mb_block_type" onChange="ihcRedirectReplaceDd(this.value);">
		<?php
			foreach($select_types as $value=>$label){
				?>
					<option value="<?php echo esc_attr($value);?>" <?php echo ($meta_arr['ihc_mb_block_type']==$value) ? 'selected' : ''; ?> >
						<?php echo esc_html($label);?>
					</option>
				<?php
			}
		?>
	</select>
</div>

<div class="ihc-class ihc-padding ihc-redrep">
<?php
	$class = 'ihc-display-none';
	if($meta_arr['ihc_mb_block_type']=='redirect'){
		$class = 'ihc-display-block';
	}
?>
<div class="<?php echo esc_attr($class);?> " id="ihc-meta-box-redirect">
	<label class="ihc-bold"><?php esc_html_e('To:', 'ihc');?></label>
	<select name="ihc_mb_redirect_to" class=" ihc-select ihc_mb_redirect_to">
		<option value="-1" <?php echo ($meta_arr['ihc_mb_redirect_to']==-1) ? 'selected' : ''; ?> >...</option>
		<?php
			$default_redirect = get_option('ihc_general_redirect_default_page');
			if($default_redirect && $default_redirect!=-1){
				?>
				<option value="<?php echo esc_attr($default_redirect);?>" <?php echo ($meta_arr['ihc_mb_redirect_to']==$default_redirect || $meta_arr['ihc_mb_redirect_to']==-1) ? 'selected' : ''; ?> >
					<?php
						echo esc_html__('default', 'ihc');
						$title = get_the_title($default_redirect);
						if ($title){
							echo esc_html(" ( $title )");
						} else {
							echo esc_html(" ( ") . esc_html__("Custom Link:", 'ihc') . esc_url( $default_redirect ) . " )";
						}
					?>
				</option>
				<?php
			}

			$pages = ihc_get_all_pages();
			$pages = $pages + ihc_get_redirect_links_as_arr_for_select();

			foreach ($pages as $k=>$v){
				if ($k!=$default_redirect && $k!=$post->ID){
				?>
					<option value="<?php echo esc_attr($k);?>" <?php echo ($meta_arr['ihc_mb_redirect_to']==$k) ? 'selected' : ''; ?> ><?php echo esc_html($v);?></option>
				<?php
				}
			}
		?>
	</select>
</div>

<?php
	$class = 'ihc-display-none';
	if($meta_arr['ihc_mb_block_type']=='replace'){
		$class = 'ihc-display-block';
	}
?>
<div class="<?php echo esc_attr($class);?>" id="ihc-meta-box-replace">
	<?php esc_html_e('Add the replacement content into the "Replace Content" Editor box.', 'ihc');?>
</div>

</div>
<?php wp_enqueue_script('ihc-back_end');?>

<?php
add_action( 'wp_nav_menu_item_custom_fields', 'ihc_wp_menu_custom_settings', 999, 5 );
if ( !function_exists( 'ihc_wp_menu_custom_settings' ) ):
function ihc_wp_menu_custom_settings( $item_id=0, $item=null, $depth=null, $args=[], $id=0 )
{
	?>
	<!---------------------------- INDEED CUSTOM SECTION  -------------------------------------------->
							<span class="ihc-js-custom-nav-menu-labels" data-remove="<?php esc_html_e('Removing tag', 'ihc');?>"></span>
							<?php wp_enqueue_script( 'ihc-custom-nav-menu', IHC_URL . 'admin/assets/js/custom-nav-menu.js', ['jquery'], 10.1 );?>

							<div>
								<h5>Ultimate Membership Pro - <?php esc_html_e('Menu item Restriction', 'ihc');?></h5>
								<div class="ihc-class ihc-padding">
									<select class="ihc-fullwidth ihc-select" name="ihc_menu_mb_type-<?php echo esc_attr($item_id); ?>">
									<option value="show" <?php if($item->ihc_menu_mb_type=='show'){
										echo esc_attr('selected');
									} ?> ><?php esc_html_e('Show Menu Item Only', 'ihc');?></option>
										<option value="block" <?php if($item->ihc_menu_mb_type=='block'){
											echo esc_attr('selected');
										} ?> ><?php esc_html_e('Block Menu Item Only', 'ihc');?></option>
									</select>
								</div>
								<div  class="ihc-padding ihc-text-aling-right">
									<label class="ihc-bold">...<?php esc_html_e('for', 'ihc');?></label>
									<?php
										$posible_values = array('all'=>esc_html__('All', 'ihc'), 'reg'=>esc_html__('Registered Users', 'ihc'), 'unreg'=>esc_html__('Unregistered Users', 'ihc') );

										$levels = \Indeed\Ihc\Db\Memberships::getAll();
										if($levels){
											foreach($levels as $id => $level){
												$posible_values[$id] = $level['name'];
											}
										}
										?>
										<select onChange="ihcWriteTagValue(this, '#ihc_mb_who_hidden-<?php echo esc_attr($item_id);?>', '#ihc_tags_field-<?php echo esc_attr($item_id);?>', '<?php echo esc_attr($item_id);?>_ihc_select_tag_' );">
											<option value="-1" selected>...</option>
											<?php
												foreach($posible_values as $k=>$v){
													?>
													<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
													<?php
												}
											?>
										</select>
								</div>
								<div id="ihc_tags_field-<?php echo esc_attr($item_id);?>">
					            	<?php

					                    if($item->ihc_mb_who_menu_type){
					                    	if(strpos($item->ihc_mb_who_menu_type, ',')!==FALSE){
					                    		$values = explode(',', $item->ihc_mb_who_menu_type);
					                    	}
					                        else{
					                        	$values[] = $item->ihc_mb_who_menu_type;
					                        }
					                        foreach($values as $value){ ?>
					                        	<div id="<?php echo esc_attr($item_id);?>_ihc_select_tag_<?php echo esc_attr($value);?>" class="ihc-tag-item">
					                        		<?php echo esc_html($posible_values[$value]);?>
					                        		<div class="ihc-remove-tag" onclick="ihcremoveTag('<?php echo esc_attr($value);?>', '#<?php echo esc_attr($item_id);?>_ihc_select_tag_', '#ihc_mb_who_hidden-<?php echo esc_attr($item_id);?>');" title="<?php esc_html_e('Removing tag', 'ihc');?>">x</div>
					                        	</div>
					                            <?php
					                        }//end of foreach ?>
					                    <div class="ihc-clear"></div>
					                    <?php }//end of if ?>

								</div>
								<div class="ihc-clear"></div>
								<input type="hidden" id="ihc_mb_who_hidden-<?php echo esc_attr($item_id);?>" name="ihc_mb_who_menu_type-<?php echo esc_attr($item_id); ?>" value="<?php echo esc_attr($item->ihc_mb_who_menu_type);?>" />
								<div class="clear"></div>
							</div>
	<!---------------------------- END OF INDEED CUSTOM SECTION  -------------------------------------------->
	<?php
}
endif;


add_action('wp_update_nav_menu_item', 'ihc_wp_menu_custom_settings_do_update', 999, 3);
if ( !function_exists( 'ihc_wp_menu_custom_settings_do_update' ) ):
function ihc_wp_menu_custom_settings_do_update( $menu_id, $menu_db_id, $args )
{
	if ( isset($_POST['ihc_mb_who_menu_type-'.$menu_db_id]) && isset($_POST['ihc_menu_mb_type-'.$menu_db_id]) ) {
		update_post_meta( $menu_db_id, 'ihc_mb_who_menu_type', sanitize_text_field( $_POST['ihc_mb_who_menu_type-'.$menu_db_id] ) );
		update_post_meta( $menu_db_id, 'ihc_menu_mb_type', sanitize_text_field( $_POST['ihc_menu_mb_type-'.$menu_db_id] ) );
	}
}
endif;


//add custom fields to object
add_filter( 'wp_setup_nav_menu_item','ihc_nav_items_custom' );
if ( !function_exists( 'ihc_nav_items_custom' ) ):
function ihc_nav_items_custom($obj)
{
	$obj->ihc_mb_who_menu_type = get_post_meta( $obj->ID, 'ihc_mb_who_menu_type', true );
	$obj->ihc_menu_mb_type = get_post_meta( $obj->ID, 'ihc_menu_mb_type', true );
	return $obj;
}
endif;

<div class="row ulp-module-item" id="<?php echo esc_attr('moduleid_' . $data['id']);?>">
	<div class="col-8">

		<span class="ulp-js-couse-add-new-module" data-id="<?php echo esc_attr($data['id']);?>"></span>

		<div id="<?php echo esc_attr('ulp_module_content_' . $data['id']);?>">
          <div class="ulp-course-module-top">
                <div class="ulp-course-module-order"><span class="ulp-module-order-display" id="<?php echo esc_attr('ulp_module_order_display_' . $data['id']);?>"><?php echo esc_attr($data['module_order']) ;?></span></div>
				<div class="ulp-course-module-title">
					<input type="text" class="ulp-module-name" name="module_name[<?php echo esc_attr($data['id']);?>]" value="<?php echo esc_attr($data['module_name']);?>" placeholder="<?php esc_html_e('Section name...', 'ulp');?>"/>
                    <input type="hidden" name="module_new[<?php echo esc_attr($data['id']);?>]" value="<?php echo (isset($data['new'])) ? esc_attr($data['new']) : '';?>" class="ulp_module_new" />
					<input type="hidden" name="module_id[]" value="<?php echo esc_attr($data['id']) ;?>" class="ulp_module_id" />
					<input type="hidden" name="module_order[<?php echo esc_attr($data['id']);?>]" value="<?php echo esc_attr($data['module_order']) ;?>" class="ulp-module-order" />
				</div>
				<div class="ulp-course-module-options">
					<span id="<?php echo esc_attr( 'ulp_toggle_modukle_' . $data['id']);?>">
						<i title="<?php esc_html_e('Toggle Section', 'ulp');?>" class="fa-ulp fa-toggle_up-ulp" onClick="ulpDoModuleToggle( '<?php echo esc_attr($data['id']);?>' );" ></i>
					</span>
					<span  class="ulp-move-module-box"><i title="<?php esc_html_e('Shift Section', 'ulp');?>" class="fa-ulp fa-reorder-ulp"></i></span>
					<span id="<?php echo esc_attr('ulp_remove_modukle_' . $data['id']);?>" >
							<i title="<?php esc_html_e('Remove Section', 'ulp');?>" class="fa-ulp fa-remove-ulp"  onClick="ulpDoModuleRemove( '<?php echo esc_attr($data['id']);?>' );" ></i>
					</span>
				</div>
                <div class="ulp-clear"></div>
           </div>
				<div class="ulp-course-module-list" id="<?php echo esc_attr('ulp_module_content_inside_' . $data['id']);?>">
				<select id="<?php echo esc_attr('module_item_' . $data['id']);?>" multiple="multiple" name="module_items[<?php echo esc_attr($data['id']);?>][]" class="ulp-course-module-list-multiple">
						<?php if ($data['items']):?>
								<?php foreach ($data['items'] as $array):?>
										<?php
												if (isset( $data['items_in'][$data['id']] ) && is_array( $data['items_in'][$data['id']] ) && in_array($array['ID'], $data['items_in'][$data['id']])){
														$labels [$array['ID']] = $array['post_title'];
														$post_types [$array['ID']] = $array['post_type'];
														continue;
												}
										 ?>
										 <?php
										 	$iconType = $array['post_type'];
											if ( $iconType == 'ulp_lesson' && get_post_meta( $array['ID'], 'ulp_lesson_is_video', true ) ){
													$iconType = 'ulp_lesson_video';
											}
										 ?>
										<option value="<?php echo esc_attr($array['ID']);?>" data-post_type="<?php echo esc_attr($iconType);?>"><?php echo esc_attr($array['post_title']);?></option>
								<?php endforeach;?>
								<?php if (isset($data['items_in']) && isset($data['items_in'][$data['id']])):?>
											<?php foreach ($data['items_in'][$data['id']] as $item_id):?>
													<option value="<?php echo esc_attr($item_id);?>" data-post_type="<?php echo esc_attr($post_types[$item_id]);?>"  selected><?php echo esc_attr($labels[$item_id]);?></option>
											<?php endforeach;?>
								<?php endif;?>
						<?php endif;?>
				</select>
                </div>
		</div>
	</div>

</div>

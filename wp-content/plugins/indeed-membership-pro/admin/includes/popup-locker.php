<div class="ihc-popup-wrapp" id="popup_box">
	<div class="ihc-the-popup ihc-the-popup-locker">
        <div class="ihc-popup-top">
        	<div class="title">Membership Pro Ultimate Wp - <?php esc_html_e('Locker', 'ihc');?></div>
            <div class="close-bttn" onClick="ihcClosePopup();"></div>
            <div class="clear"></div>
        </div>
        <div class="ihc-popup-content">
        	<div class="ihc-popup-left-section">
	        	<div>
	  				<select class="ihc-fullwidth ihc-select"  id="ihc_mb_type-shortcode">
	  					<option value="show" selected><?php esc_html_e('Show Content Only For', 'ihc');?></option>
	  					<option value="block"><?php esc_html_e('Hide Content Only For', 'ihc');?></option>
	  				</select>
	        	</div>
	        	<div>
		         	<div class="ihc-popup-label">
		         		<?php esc_html_e('Target Users:', 'ihc');?>
		         	</div>
		         	<?php
						if(isset($meta_arr['ihc_mb_who']) && strpos($meta_arr['ihc_mb_who'], ',')!==FALSE){
							$arr = explode(',', $meta_arr['ihc_mb_who']);
						}else{
							$arr[] = '';
						}
						$posible_values = array('all'=>'All', 'reg'=>'Registered Users', 'unreg'=>'Unregistered Users');

						$levels = \Indeed\Ihc\Db\Memberships::getAll();
						if($levels){
							foreach($levels as $id=>$level){
								$posible_values[$id] = $level['name'];
							}
						}
					?>
					<select class="ihc-fullwidth ihc-select" id="ihc-popup-select-target" onChange="ihcWriteTagValue(this, '#ihc_mb_who-shortcode', '#ihc-popup-target-user-select-view', 'ihc_select_popuptag_' );">
						<option value="-1" selected>...</option>
						<?php
							foreach($posible_values as $k=>$v){
							?>
								<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v);?></option>
							<?php
							}
							?>
					</select>
					<div id="ihc-popup-target-user-select-view"></div>
					<input type="hidden" id="ihc_mb_who-shortcode" />
	        	</div>
	        	<div class="clear"></div>
	        	<div class="ihc-popup-label">
	        		<div><?php esc_html_e('Choose Locker:', 'ihc');?></div>
	        		<?php
	        			$lockers = ihc_return_meta('ihc_lockers');
	        			if ($lockers){
							?>
	        		<select class="ihc-fullwidth ihc-select" id="ihc_mb_template-shortcode" onChange="ihcLockerPreviewWi(this.value, 0);">
	        			<option value="-1">...</option>
	        			<?php
	        				foreach ($lockers as $k=>$v){
	        						?>
	        							<option value="<?php echo esc_attr($k);?>"><?php echo esc_html($v['ihc_locker_name']);?></option>
	        						<?php
	        				}
	        			?>
	        		</select>
							<?php
	        			}else{
	        				esc_html_e('No Lockers Available.', 'ihc');
	        			}

	        		?>
	        	</div>
	        	<div class="ihc-bttn-wrap">
	        		<input type="button" class="button button-primary button-large" value="Save" onClick="tinymce.execCommand('ihc_insert_locker_shortcode');"/>
	        	</div>
        	</div>
        	<div class="ihc-popup-right-section">
    			<div><?php esc_html_e('Preview', 'ihc');?></div>
				<div id="locker-preview">
				</div>
    		</div>
    	</div>
    </div>
</div>

<div id="ulp_drip_content_meta_box" >

<div class="ulp-instructor-edit-row">
  	<div class="ulp-inst-col-12">
    <div class="ulp-form-section">
   <h3><?php esc_html_e('Lesson Drip Content', 'ulp');?></h3>
    <p><?php esc_html_e("Release content at regular intervals, by creating a release schedule for your content.", 'ulp')?></p>
	<div class="ulp-margin-top">

		<label class="ulp_label_shiwtch">
			<?php $checked = ($data['metas']['ulp_drip_content'] == 1) ? 'checked' : '';?>
			<input type="checkbox" class="checkbox-big" onClick="ulpCheckAndH(this, '#ulp_drip_content_field_hidden');" <?php echo esc_attr($checked);?> />
			<span><strong><?php esc_html_e("Activate Drip workflow", 'ulp')?></strong></span>
		</label>
		<input type="hidden" value="<?php echo esc_attr($data['metas']['ulp_drip_content']);?>" name="ulp_drip_content" id="ulp_drip_content_field_hidden" />
	</div>
  </div>
  </div>
</div>
<div class="ulp-instructor-edit-row">
  	<div class="ulp-inst-col-6">
    <h4><?php esc_html_e("Release Time", 'ulp')?></h4>
            <div class="ulp-form-section">
            <label><?php esc_html_e('Type:', 'ulp');?></label>
            <select name="ulp_drip_start_type"  class="ulp-form-control" onchange="ulpShowSelectorIf('#after_x_time', this.value, 1);ulpShowSelectorIf('#specific_date', this.value, 2);">
                    <?php foreach ([1=>esc_html__('After Enroll X time', 'ulp'), 2 => esc_html__('On specific date', 'ulp')] as $k=>$v):?>
                            <option value="<?php echo esc_attr($k);?>" <?php echo ($k==$data['metas']['ulp_drip_start_type']) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
                    <?php endforeach;?>
            </select>
        </div>
  </div>
</div>
<div class="ulp-instructor-edit-row">
  	<div class="ulp-inst-col-6">
        <div class="ulp_drip_pickperiod ulp-margin-bottom" id="after_x_time">
          <label><?php esc_html_e('After Enroll:', 'ulp');?></label>
           <div class="ulp-form-section">
          		     <input type="number" min="0"  class="ulp-form-control" value="<?php echo esc_attr($data['metas']['ulp_drip_start_numeric_value']);?>" name="ulp_drip_start_numeric_value"  />
               </div>
             <div class="ulp-form-section">
            		<select name="ulp_drip_start_numeric_type"  class="ulp-form-control ulp-drip-start-numeric-type"><?php
  				          foreach (array('days'=>'Days', 'weeks'=>'Weeks', 'months'=>'Months') as $k=>$v){
  					        ?>
  					              <option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['ulp_drip_start_numeric_type']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
  					        <?php }
  				      ?></select>
             </div>
          </div>
        </div>
  </div>
<div class="ulp-instructor-edit-row">
  	<div class="ulp-inst-col-6">
        <div class="ulp_drip_pickdate ulp-margin-bottom" id="specific_date">
        	<div class="ulp-form-section">
            <div class="ulp-input-group">
               <span class="ulp-input-group-addon"> <?php esc_html_e('On Specific Date:', 'ulp');?></span>
              <input type="text" class="ulp-form-control" value="<?php echo esc_attr($data['metas']['ulp_drip_start_certain_date']);?>" name="ulp_drip_start_certain_date" id="ulp_drip_start_certain_date"/>
            </div>
            </div>
           <div class="ulp-drip-message"><?php esc_html_e('Pick the desired date when the Page will be available', 'ulp');?></div>
          </div>
      <div class="ulp-clear"></div>
    </div>
  </div>
 </div>

<div class="ulp-clear"></div>
<span class="ulp-js-instructor-lesson-drip-content" data-start_type="<?php echo esc_attr($data['metas']['ulp_drip_start_type']);?>" ></span>

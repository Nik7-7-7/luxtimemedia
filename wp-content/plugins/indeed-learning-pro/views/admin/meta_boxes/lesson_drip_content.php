<div id="ulp_drip_content_meta_box" >
<div class="ulp-inside-item">
  <div class="row">
   <div class="col-xs-6">
   <h3><?php esc_html_e('Lesson Drip Content', 'ulp');?></h3>
	<div class="ulp-input-group-space">

		<label class="ulp_label_shiwtch ulp-switch-button-margin">
			<?php $checked = ($data['metas']['ulp_drip_content'] == 1) ? 'checked' : '';?>
			<input type="checkbox" class="ulp-switch" onClick="ulpCheckAndH(this, '#ulp_drip_content_field_hidden');" <?php echo esc_attr($checked);?> />
			<div class="switch ulp-display-inline"></div>
		</label>
		<input type="hidden" value="<?php echo esc_attr($data['metas']['ulp_drip_content']);?>" name="ulp_drip_content" id="ulp_drip_content_field_hidden" />
	</div>


  <div>
   <p><?php esc_html_e("Release content at regular intervals, by creating a release schedule for your content.", 'ulp')?></p>
  </div>


  <div class="ulp-input-group-space">
    <h4 class="ulp-meta-drip-subtitle"><?php esc_html_e("Release Time", 'ulp')?></h4>
    <div class="ulp-admin-drip-type">
        <div class="ulp-input-group-max ulp-input-group-space"
            <div class="title-select"><?php esc_html_e('Type:', 'ulp');?></div>
            <select name="ulp_drip_start_type"  class="form-control m-bot15" onchange="ulpShowSelectorIf('#after_x_time', this.value, 1);ulpShowSelectorIf('#specific_date', this.value, 2);">
                    <?php foreach ([1=>esc_html__('After Enroll X time', 'ulp'), 2 => esc_html__('On specific date', 'ulp')] as $k=>$v):?>
                            <option value="<?php echo esc_attr($k);?>" <?php echo ($k==$data['metas']['ulp_drip_start_type']) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
                    <?php endforeach;?>
            </select>
        </div>

        <div class="ulp_drip_pickperiod ulp-margin-bottom" id="after_x_time">

          <div class="title-select"><?php esc_html_e('After Enroll:', 'ulp');?></div>
           <div class="row ulp-input-group-max">
   			       <div class="col-xs-6 ulp-admin-drip-fields">
          		     <input type="number" min="0" value="<?php echo esc_attr($data['metas']['ulp_drip_start_numeric_value']);?>" name="ulp_drip_start_numeric_value" class="form-control"  />
               </div>
             <div class="col-xs-6 ulp-admin-drip-fields">
            		<select name="ulp_drip_start_numeric_type"  class="form-control m-bot15 ulp-admin-drip-numeric-type"><?php
  				          foreach (array('days'=>'Days', 'weeks'=>'Weeks', 'months'=>'Months') as $k=>$v){
  					        ?>
  					              <option value="<?php echo esc_attr($k);?>" <?php echo ($data['metas']['ulp_drip_start_numeric_type']==$k) ? 'selected' : '';?> ><?php echo esc_html($v);?></option>
  					        <?php }
  				      ?></select>
             </div>
          </div>

          </div>

          <div class="ulp_drip_pickdate ulp-margin-bottom" id="specific_date">
            <div class="input-group ulp-input-group-max">
               <span class="input-group-addon"> <?php esc_html_e('On Specific Date:', 'ulp');?></span>
              <input type="text" class="form-control" value="<?php echo esc_attr($data['metas']['ulp_drip_start_certain_date']);?>" name="ulp_drip_start_certain_date" id="ulp_drip_start_certain_date"/>
            </div>
           <div class="ulp-admin-drip-message"><?php esc_html_e('Pick the desired date when the Page will be available', 'ulp');?></div>
          </div>

      </div>
      <div class="ulp-clear"></div>
    </div>
  </div>
 </div>
 </div>


<div class="ulp-clear"></div>

<span class="ulp-js-lesson-drip-content" data-ulp_drip_start_type="<?php echo esc_attr($data['metas']['ulp_drip_start_type']);?>" ></span>

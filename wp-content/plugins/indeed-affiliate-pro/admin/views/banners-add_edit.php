<div class="uap-wrapper">
<div class="uap-stuffbox">
  <form action="<?php echo esc_url($data['form_action_url']);?>" method="post">

  <input type="hidden" name="uap_admin_forms_nonce" value="<?php echo wp_create_nonce( 'uap_admin_forms_nonce' );?>" />

	<h3 class="uap-h3"><?php esc_html_e('Manage Creative', 'uap');?></h3>
	<div class="inside">
    <div class="uap-form-line">
        <div class="row">
          <div class="col-xs-6">
              <h2><?php esc_html_e('Activate/Hold the Creative', 'uap');?></h2>
              <p><?php esc_html_e('The Creative can be turned on or off without having to be removed', 'uap');?></p>
              <label class="uap_label_shiwtch uap-switch-button-margin">
                <?php $checked = ($data['status']) ? 'checked' : '';?>
                <input type="checkbox" class="uap-switch" onClick="uapCheckAndH(this, '#status');" <?php echo esc_attr($checked);?> />
                <div class="switch uap-display-inline"></div>
              </label>
              <input type="hidden" name="status" value="<?php echo esc_attr($data['status']);?>" id="status" />
          </div>
        </div>
    </div>

    <div class="uap-form-line">
	  <div class="uap-inside-item">
		<div class="row">
			<div class="col-xs-6">
			<div class="input-group">
				<span class="input-group-addon"><?php esc_html_e('Name', 'uap');?></span>
				<input type="text" class="form-control" placeholder="..."  value="<?php echo esc_attr($data['name']);?>" name="name"  />
			</div>
      <p><?php esc_html_e("This creative's name. Give your affiliates a brief overview of the creative using this", 'uap');?></p>
			</div>
		</div>
	 </div>
 </div>

	 <div class="uap-inside-item">
     <div class="uap-form-line">
		<div class="row">
			<div class="col-xs-6">
				<h4><?php esc_html_e('Short Description', 'uap');?></h4>
          <p><?php esc_html_e('An optional description for this creative, which you may use to give your affiliates more details about the creative.', 'uap');?></p>
				<textarea name="description" class="form-control text-area" cols="30" rows="5" placeholder="<?php esc_html_e('Some details...', 'uap');?>"><?php echo esc_html($data['description']);?></textarea>
			</div>
		</div>
  </div>
	</div>

  <div class="uap-inside-item">
    <div class="uap-form-line">
       <div class="row">
           <div class="col-xs-6">
             <h2><?php esc_html_e('Creative Type', 'uap');?></h2>
             <p><?php esc_html_e('Choose the type of the Creative', 'uap');?></p>
             <select name="content_type" class="uap-js-admin-creatives-select-type form-control m-bot15">
                <option value="image" <?php if ( $data['content_type'] === 'image' ) echo 'selected';?> ><?php esc_html_e( 'Image', 'uap' );?></option>
                <option value="text" <?php if ( $data['content_type'] === 'text' ) echo 'selected';?> ><?php esc_html_e( 'Text', 'uap' );?></option>
             </select>
           </div>
       </div>
     </div>
 </div>

	<div class="uap-inside-item uap-special-line">
    <div class="uap-form-line">
		<div class="row">
			<div class="col-xs-6">
			<h2><?php esc_html_e('Creative Options', 'uap');?></h2>
			<p><?php esc_html_e('Predefined URL And Image for your Custom Creative available for Affiliates', 'uap');?></p>
    </div>
  </div>
  </div>
  <div class="uap-form-line">
  <div class="row">
    <div class="col-xs-6">
      <div class="input-group">
				<span class="input-group-addon"><?php esc_html_e('Link', 'uap');?></span>
				<input type="text" class="form-control" value="<?php echo esc_attr($data['url']);?>" name="url" />
			</div>
    </div>
  </div>
  </div>

  <div class="uap-form-line">
  <div class="row">
    <div class="col-xs-6">
      <?php $extraClass = $data['content_type'] === 'image' ? '' : 'uap-display-none';?>
      <div class="<?php echo $extraClass;?>" id="uap_js_admin_creatives_image_url">
          <h4><?php esc_html_e('Creative Image', 'uap');?></h4>
          <p><?php esc_html_e('Select your image hosted on your WordPress site', 'uap');?></p>
    			<div class="form-group">
    			      <input type="text" class="form-control uap-display-inline uap-banner-settings-image-input" onClick="openMediaUp(this);" value="<?php echo esc_attr($data['image']);?>" name="image" id="uap_the_image" />
    		        <i class="fa-uap fa-trash-uap" id="uap_js_add_edit_banners_trash" title="<?php esc_html_e('Remove Creative Image', 'uap');?>"></i>
    			</div>
          <div class="input-group">
              <span class="input-group-addon"><?php esc_html_e('Alt Text', 'uap');?></span>
              <input type="text" class="form-control uap-display-inline uap-banner-settings-image-input" value="<?php echo esc_attr($data['alt_text']);?>" name="alt_text" id="alt_text" />
          </div>
      </div>

      <?php $extraClass = $data['content_type'] === 'text' ? '' : 'uap-display-none';?>
      <div class="<?php echo $extraClass;?>" id="uap_js_admin_creatives_text_type">
          <div class="input-group">
                <span class="input-group-addon"><?php esc_html_e('Anchor text', 'uap');?></span>
                <input type="text" class="form-control" value="<?php echo esc_attr($data['text_content']);?>" name="text_content" id="uap_text_content" />
          </div>
      </div>
    </div>
  </div>
  </div>
  <div class="uap-form-line">
  <div class="row">
    <div class="col-xs-6">
      <h4><?php esc_html_e('Internal Notes', 'uap');?></h4>
      <p><?php esc_html_e('Additional Information for internal purpose only.', 'uap');?></p>
      <div class="form-group">
          <textarea name="notes" class="form-control text-area" cols="30" rows="5"><?php echo esc_attr($data['notes']);?></textarea>
      </div>


			</div>
    </div>
		</div>
	</div>
		<div id="uap_save_changes" class="uap-submit-form">
			<input type="submit" value="<?php esc_html_e('Save Changes', 'uap');?>" name="save" class="button button-primary button-large">
		</div>
	</div>
	<input type="hidden" name="id" value="<?php echo esc_attr($data['id']);?>" />
  </form>
</div>

</div>

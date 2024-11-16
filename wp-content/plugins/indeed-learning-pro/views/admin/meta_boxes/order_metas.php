<div>

<div class="ulp-inside-item">
<div class="row">
    <div class="col-xs-6">
    <div class="input-group ulp-input-group-max ulp-input-group-space">
        <span class="input-group-addon"><?php esc_html_e('Unique identificator', 'ulp');?></span>
        <input type="text" class="form-control" name="unique_identificator" value="<?php echo esc_attr($data['metas']['unique_identificator']);?>" />
    </div>
  </div>
</div>
</div>
<div class="ulp-line-break"></div>
<div class="ulp-inside-item">
<div class="row">
    <div class="col-xs-6">
    <div class="input-group ulp-input-group-max ulp-input-group-space">
        <span class="input-group-addon"><?php esc_html_e('Source', 'ulp');?></span>
        <input type="text" class="form-control" name="source" disabled value="<?php echo esc_attr($data['metas']['source']);?>" />
    </div>
    <div class="input-group ulp-input-group-max ulp-input-group-space">
        <span class="input-group-addon"><?php esc_html_e('Amount', 'ulp');?></span>
        <input type="number" class="form-control" min=0 name="amount" value="<?php echo esc_attr($data['metas']['amount']);?>" />
        <div class="input-group-addon"><?php echo esc_html($data['currency']);?></div>
    </div>
  </div>
</div>
</div>
<div class="ulp-line-break"></div>
<div class="ulp-inside-item">
<div class="row">
    <div class="col-xs-6">
    <h3><?php esc_html_e('Customer Details', 'ulp');?></h3>

    <?php if (isset($data['metas']['avatar']) && isset($data['metas']['username'])):?>
    <div class="ulp-input-group-max ulp-input-group-space">
    <img src="<?php echo esc_url($data['metas']['avatar']);?>" class="ulp-general-user-image" /><a href="<?php echo admin_url('user-edit.php?user_id=' . $data['metas']['user_id']); ?>" target="_blank"><?php echo esc_attr($data['metas']['username']);?></a>
     </div>
     <?php endif;?>
     <div class="input-group ulp-input-group-max ulp-input-group-space">
        <span class="input-group-addon"><?php esc_html_e('Buyer Username', 'ulp');?></span>
        <input type="text" class="form-control" name="username" value="<?php echo (isset($data['metas']['username'])) ? $data['metas']['username'] : '';?>" />
    </div>
  </div>
</div>
</div>
<div class="ulp-line-break"></div>
<div class="ulp-inside-item">
<div class="row">
    <div class="col-xs-6">
     <div class="ulp-input-group-max ulp-input-group-space">
        <h3><?php esc_html_e('Course', 'ulp');?></h3>
        <?php if ($data['courses']):?>
            <select name="course_id" class="form-control m-bot15">
                <?php foreach ($data['courses'] as $array):?>
                  <option value="<?php echo esc_attr($array['ID']);?>" <?php echo ($array['ID']==$data['metas']['course_id']) ? 'selected' : '';?> ><?php echo esc_html($array['post_title']);?></option>
                <?php endforeach;?>
            </select>
        <?php endif;?>
    </div>
  </div>
</div>
</div>
<div class="ulp-line-break"></div>
<div class="ulp-inside-item">
<div class="row">
    <div class="col-xs-6">
     <div class="ulp-input-group-max ulp-input-group-space">
        <h3><?php esc_html_e('Order status', 'ulp');?></h3>
        <select name="ulp_status" class="form-control m-bot15">
            <?php
            $types = [
                      'ulp_pending' => esc_html__('Pending', 'ulp'),
                      'ulp_complete' => esc_html__('Completed', 'ulp'),
                      'ulp_fail' => esc_html__('Fail', 'ulp'),
            ];
            foreach ($types as $key=>$label):?>
            <option value="<?php echo esc_attr($key);?>" <?php selected($data ['post_status'], $key);?> ><?php echo esc_html($label);?></option>
            <?php endforeach;?>
        </select>
    </div>
  </div>
</div>
</div>
<div class="ulp-inside-item">
<div class="row">
    <div class="col-xs-6">
      <div>
          <input type="submit" name="save" value="<?php esc_html_e('Save Changes', 'ulp');?>" class="button save_order button-primary" />
      </div>
  </div>
</div>
</div>

<div class="ulp-padding">
  <div><strong><?php esc_html_e('Set the Page as:', 'ulp');?></strong></div>
  <select class="ulp-fullwidth ihc-select" name="ulp_set_page_as_default_something">
    <option value="-1">...</option>
    <?php
      foreach ($data['types'] as $name=>$label):
        $selected = ($name==$data['current_page_type']) ? 'selected' : '';
      ?>
        <option <?php echo esc_attr($selected);?> value="<?php echo esc_attr($name);?>"><?php echo esc_html($label) . ' ' . esc_html__('Page', 'ulp');?></option>
      <?php
      endforeach;
    ?>
  </select>
  <input type="hidden" name="ulp_post_id" value="<?php echo (isset($data['post_id'])) ? $data['post_id'] : '';?>" />
</div>

<div class="ulp-page-metabox-wrapper">
  <?php if (!empty($data['unset_pages'])): ?>
    <?php foreach ($data['unset_pages'] as $page_name): ?>
      <div class="ulp-metabox-not-set"><?php echo esc_html__('Default ', 'ulp') . $page_name . ' ' . esc_html__('Page Not Set!', 'ulp');?></div>
    <?php endforeach;?>
  <?php else:?>
      <?php echo esc_html__('All the required pages are properly set, to change them click', 'ulp') . '<a href="' . admin_url('admin.php?page=ultimate_learning_pro&tab=settings&subtab=default_pages') . '">' . esc_html__('here', 'ulp') . '</a>';?>
  <?php endif;?>
</div>

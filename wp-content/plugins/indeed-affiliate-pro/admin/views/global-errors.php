<div class="<?php echo esc_attr($data['err_class']);?> uap-global-error-wrapper">
<div class='uap-close-notice uap-js-close-admin-dashboard-notice'>x</div>
  <?php
echo esc_uap_content('<p>') . esc_html__('This is a Trial Version of ', 'uap').'<strong>'. esc_html__(' Ultimate Affiliate Pro ', 'uap').'</strong>'. esc_html__(' plugin. Please add your purchase code into Licence section to enable the Full Ultimate Affiliate Pro Version. Check your ', 'uap') . '<a href="' . esc_url($data['url']) . '">' . esc_html__(' licence section ', 'uap') .'</a>.</p>';

?></div>

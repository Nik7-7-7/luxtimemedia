<div class="uap-ap-wrap">
<?php if (!empty($data['title'])):?>
	<h3><?php echo esc_uap_content($data['title']);?></h3>
<?php endif;?>
<?php if (!empty($data['content'])):?>
	<p><?php echo do_shortcode($data['content']);?></p>
<?php endif;?>

<form  method="post">
<div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php esc_html_e('Add New Direct Link', 'uap');?></span></div>
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
                <?php esc_html_e("Users will no longer avoid links that could benefit a certain affiliate, because they will not be able to know to which affiliate the link belongs to.", 'uap');?>
                </div>
            </div>
        </div>
        <div class="uap-profile-box-content">
            <div class="uap-row ">
            	<div class="uap-col-xs-10">
                            <div class="uap-account-title-label"><?php esc_html_e('Your Direct Link', 'uap');?></div>
                            <input type="text" name="url" class="uap-public-form-control" />
                            <div class="uap-account-notes"><?php echo esc_html__("Submit one of your website page from where traffic should be tracked and recorded as coming from you.", 'uap') . esc_html__(" You can submit up to ", "uap");?> <strong> <?php echo esc_uap_content($data['max_limit']) . esc_html__(" links ", 'uap');?></strong></div>


                        <?php if (!empty($data['err'])):?>
                            <div class="uap-warning-box">
                                <div><?php echo esc_uap_content($data['err']);?></div>
                            </div>
                        <?php endif;?>

                        <div class="uap-submit-field-wrap">
                            <input type="submit" value="<?php esc_html_e('Add New Link', 'uap');?>" name="save" class="button button-primary button-large uap-js-submit-simple-link" <?php echo (isset($data['preview'])) ? 'disabled' : ''; ?> />
                        </div>

          </div>
       </div>
 </div>
</form>

<?php if (!empty($data['items'])):?>
<div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php esc_html_e('Your own Direct Links for Tracking', 'uap');?></span></div>
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
                  <div class="uap-stuffbox">
                      <table class="uap-account-table">
                          <thead>
                              <tr>
                                  <th><?php esc_html_e('Direct Link', 'uap');?></th>
                                  <th><?php esc_html_e('Status', 'uap');?></th>
                                  <th><?php esc_html_e('Action', 'uap');?></th>
                              </tr>
                          </thead>
                          <tbody class="uap-alternate">
                              <?php foreach ($data['items'] as $item):?>
                              <tr>
                                  <td><a href="<?php echo esc_url($item['url']);?>" target="_blank"><?php echo esc_url($item['url']);?></a></td>
                                  <td>
                                      <?php if ($item['status']):?>
                                          <div class="uap-status uap-status-active"><?php esc_html_e('Approved', 'uap');?></div>
                                      <?php else:?>
                                          <div class="uap-status uap-status-inactive"><?php esc_html_e('Pending', 'uap');?></div>
                                      <?php endif;?>
                                  </td>
                                  <td>
                                      <a href="<?php echo add_query_arg('del', $item['id'], $data['url']);?>" class="uap-color-red"><?php esc_html_e('Remove', 'uap');?></a>
                                  </td>
                              </tr>
                              <?php endforeach;?>
                          </tbody>
                      </table>
                  </div>
              </div>
           </div>
        </div>
      </div>
<?php endif;?>
</div>

<span class="uap-js-simple-links-section" data-current_url="<?php echo esc_url($data['url']);?>"></span>

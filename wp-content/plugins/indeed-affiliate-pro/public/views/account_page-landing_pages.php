<div class="uap-ap-wrap">
<?php if (!empty($data['title'])):?>
	<h3><?php echo esc_uap_content($data['title']);?></h3>
<?php endif;?>
<?php if (!empty($data['content'])):?>
	<p><?php echo do_shortcode($data['content']);?></p>
<?php endif;?>


<div class="uap-profile-box-wrapper">
    	<div class="uap-profile-box-title"><span><?php esc_html_e("Your Landing Page", 'uap');?></span></div>
        <div class="uap-profile-box-content">
        	<div class="uap-row ">
            	<div class="uap-col-xs-12">
                <?php esc_html_e("Redirect traffic and leads to your Landing Page without being necessary to use your Tracking Code in order to track visitors. Users will no longer avoid links that could benefit a certain affiliate because no affiliate link will be required on this case.", 'uap');?>
                </div>
             </div>
                <?php if (empty($data['pages'])):?>
                  <div class="uap-warning-box uap-extra-margin-top"><?php esc_html_e('No Landing Page have been assigned to your account yet.', 'uap');?></div>
                <?php else :?>
                <table class="uap-account-table">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Page Title', 'uap');?></th>
                            <th><?php esc_html_e('Link', 'uap');?></th>
                        </tr>
                    </thead>
										<tbody class="uap-alternate">
                    <?php foreach ($data['pages'] as $object):?>
                      <tr>
                          <td><?php echo esc_uap_content($object->post_title);?></td>
                          <td><a href="<?php echo get_permalink($object->ID);?>" target="_blank"><?php echo get_permalink($object->ID);?></a></td>
                      </tr>
                    <?php endforeach;?>
									</tbody>
                </table>

                <?php endif;?>

    </div>
</div>
</div>

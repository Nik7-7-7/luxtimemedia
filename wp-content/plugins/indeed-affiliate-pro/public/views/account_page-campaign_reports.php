<div class="uap-ap-wrap">

<?php if (!empty($data['title'])):?>
	<h3><?php echo esc_uap_content($data['title']);?></h3>
<?php endif;?>
<?php if (!empty($data['message'])):?>
	<p><?php echo do_shortcode($data['message']);?></p>
<?php endif;?>

<?php if (!empty($data['items']) && is_array($data['items'])):?>
	<div>
		<table class="uap-account-table">
		<thead>
				<tr>
					<th><?php esc_html_e("Name", 'uap');?></th>
					<th align="center"><?php esc_html_e("Unique Clicks", 'uap');?></th>
					<th align="center"><?php esc_html_e("Total Clicks", 'uap');?></th>
					<th align="center"><?php esc_html_e("Referrals", 'uap');?></th>
				</tr>
			</thead>
			<tbody class="uap-alternate">
			<?php foreach ($data['items'] as $object) : ?>
				<tr>
					<td class="uap-special-label"><?php echo esc_uap_content($object->name);?></td>
					<td align="center"><?php echo esc_uap_content($object->unique_visits_count);?></td>
					<td align="center"><?php echo esc_uap_content($object->visit_count);?></td>
					<td align="center"><?php echo esc_uap_content($object->referrals);?></td>
				</tr>
			<?php endforeach;?>
			</tbody>
		</table>
	</div>
    <?php else: ?>
    	   <div class="uap-account-detault-message">
              <div>
			  <?php esc_html_e('You have no campaign into your Affiliate account. Please create one ', 'uap');?>
              <a href="<?php echo esc_url($data['campaign_page_url']);?>">
			  	<?php esc_html_e('here', 'uap');?>
              </a>
              </div>
          </div>
<?php endif;?>

<?php if (!empty($data['pagination'])):?>
	<?php echo esc_uap_content($data['pagination']);?>
<?php endif;?>
</div>

<div class="ihc-subtab-menu">
	<a class="ihc-subtab-menu-item" href="<?php echo admin_url('admin.php?page=ihc_manage&tab=gifts');?>"><?php esc_html_e('Settings', 'ihc');?></a>
	<a class="ihc-subtab-menu-item" href="<?php echo admin_url('admin.php?page=ihc_manage&tab=generated-gift-code');?>"><?php esc_html_e('Generated Membership Gift Codes', 'ihc');?></a>
	<div class="ihc-clear"></div>
</div>
<?php
if (isset($_GET['delete_generated_code'])){
	Ihc_Db::do_delete_generated_gift_code( sanitize_text_field($_GET['delete_generated_code']));
}
echo ihc_check_default_pages_set();//set default pages message
echo ihc_check_payment_gateways();
echo ihc_is_curl_enable();
do_action( "ihc_admin_dashboard_after_top_menu" );

$limit = 25;
$total = Ihc_Db::get_count_all_gift_codes();

$current_page = (empty($_GET['ihcdu_page'])) ? 1 : sanitize_text_field( $_GET['ihcdu_page'] );
if ($current_page>1){
	$offset = ( $current_page - 1 ) * $limit;
} else {
	$offset = 0;
}



require_once IHC_PATH . 'classes/Ihc_Pagination.class.php';
$url  = admin_url('admin.php?page=ihc_manage&tab=generated-gift-code');
$pagination_object = new Ihc_Pagination(array(
											'base_url' => $url,
											'param_name' => 'ihcdu_page',
											'total_items' => $total,
											'items_per_page' => $limit,
											'current_page' => $current_page,
));
$pagination = $pagination_object->output();
if ($offset + $limit>$total){
	$limit = $total - $offset;
}
$data = Ihc_Db::get_all_gift_codes($limit, $offset);

$currency = get_option('ihc_currency');
$levels = \Indeed\Ihc\Db\Memberships::getAll();
$levels[-1]['label'] = esc_html__('All', 'ihc');
?>
<div class="iump-wrapper">
<div class="iump-page-title">Ultimate Membership Pro -
			<span class="second-text">
				<?php esc_html_e('MemberShip Codes', 'ihc');?>
			</span>
</div>

<?php if (!empty($data)):?>
<table class="wp-list-table widefat fixed tags ihc-admin-tables">
	<thead>
		<tr>
			<th><?php esc_html_e('Username', 'ihc');?></th>
			<th><?php esc_html_e('Gift Code', 'ihc');?></th>
			<th><?php esc_html_e('Discount Value', 'ihc');?></th>
			<th><?php esc_html_e('Discount for Membership', 'ihc');?></th>
			<th><?php esc_html_e('Gift Status', 'ihc');?></th>
			<th><?php esc_html_e('Action', 'ihc');?></th>
		</tr>
	</thead>
	<?php $i = 1;
		foreach ($data as $gift_id => $gift):?>
		<tr class="<?php if($i%2==0){
			 echo esc_attr('alternate');
		}
		?>">
			<td><strong><?php echo esc_html($gift['username']);?></strong></td>
			<td><?php echo esc_html($gift['code']);?></td>
			<td><?php
				if ($gift['discount_type']=='price'){
					echo ihc_format_price_and_currency($currency, $gift['discount_value']);
				} else {
					echo esc_html($gift['discount_value']) . '%';
				}
			?></td>
			<td>
				<div class="level-type-list ">
				<?php
					$l = $gift['target_level'];
					if (isset($levels[$l]) && isset($levels[$l]['label'])){
						echo esc_html($levels[$l]['label']);
					}
				?>
				</div>
			</td>
			<td><?php
				if ($gift['is_active']):
					esc_html_e('Unused', 'ihc');
				else :
					esc_html_e('Used', 'ihc');
				endif;
			?></td>
			<td><a href="<?php echo admin_url('admin.php?page=ihc_manage&tab=generated-gift-code&delete_generated_code=' . $gift_id);?>"><?php esc_html_e('Delete', 'ihc');?></a></td>
		</tr>
	<?php
	$i++;
	endforeach;?>
</table>
<?php echo esc_ump_content($pagination);?>
<?php else : ?>
	<h3><?php esc_html_e('No Gift Codes available!', 'ihc');?></h3>
<?php endif;?>
</div>

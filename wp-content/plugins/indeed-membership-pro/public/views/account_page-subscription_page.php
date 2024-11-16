<div class="ihc-ap-wrap">
	<?php if (!empty($data['title'])):?>
		<h3><?php echo do_shortcode($data['title']);?></h3>
	<?php endif;?>
	<?php if (!empty($data['content'])):?>
		<p><?php echo do_shortcode($data['content']);?></p>
	<?php endif;?>

<?php
		if ( $data['show_table'] ){
				echo do_shortcode( '[ihc-account-page-subscriptions-table]' );
		}
		if ($data['show_subscription_plan']){
			echo do_shortcode( '[ihc-select-level]' );
		}
?>
</div>

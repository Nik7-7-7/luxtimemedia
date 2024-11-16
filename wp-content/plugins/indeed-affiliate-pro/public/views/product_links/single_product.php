<div class="uap-product-links-single-item">
    <div class="uap-single-product-image-wrapp"><img src="<?php echo esc_url($data['featureImage']);?>" class="uap-single-product-img" /></div>
    <div class="uap-single-product-name">
    <a href="<?php echo esc_url($data['permalink']);?>" target="_blank" class="uap-special-label"><?php echo esc_html($data['label']);?></a>
    <?php if ( $data['categories'] ):?>
        <?php foreach ( $data['categories'] as $object ):?>
            <div class="uap-single-product-category-name"> <?php esc_html_e( 'Category: ', 'uap');?><strong><?php echo esc_html($object->name);?></strong></div>
        <?php endforeach;?>
    <?php endif;?>
    </div>
    <div class="uap-single-product-price">
	<?php
		if(isset($data['numeric_price']) && isset($data['numeric_regular_price']) && $data['numeric_regular_price'] > $data['numeric_price']){
			echo esc_uap_content('<div class="uap-single-product-regular-price">'.$data['regular_price'].'</div> <div class="uap-single-product-sale-price">'.$data['price'].'</div>') ;
		}else{
			echo esc_uap_content($data['price']);
		}
	?></div>
    <div class="uap-single-product-rewards">
    <?php if ( $data['showReward'] ):?>
    	<div class="uap-single-product-reward-label"><?php esc_html_e( 'Rewards', 'uap' );?></div>
        <?php echo esc_uap_content($data['referral_amount']);?>
    <?php endif;?>
    </div>
    <div class="uap-single-product-link-wrapper">
    <div class="uap-single-product-link js-uap-affiliate-product-affiliate-link" data-post_id="<?php echo esc_attr($data['id']);?>" ><?php esc_html_e('Get Affiliate Link', 'uap');?></div>
    </div>
    <div class="uap-clear"></div>

</div>

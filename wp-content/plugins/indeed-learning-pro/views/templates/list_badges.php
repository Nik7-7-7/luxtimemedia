<?php if ($data['items']):?>
    <div class="ulp-badges-list-wrapp">
        <?php $badge_title = Ulp_Global_Settings::get('ulp_messages_list_badges_title');
				if($badge_title != ''){
           echo esc_ulp_content('<h4>'.$badge_title.'</h4>');
        }
		?>
        <?php foreach ($data['items'] as $object):?>
            <div class="ulp-badge-item">
                <img src="<?php echo esc_url($object->badge_image);?>" title="<?php echo esc_attr($object->badge_title).' - '.esc_attr($object->badge_content);?>"/>
                <!--div class="ulp-badge-title"><?php echo esc_ulp_content($object->badge_title);?></div-->
            </div>
        <?php endforeach;?>
    </div>
<?php else:?>x`
<?php endif;?>

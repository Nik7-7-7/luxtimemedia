<div  id='<?php echo esc_attr('ulp_public_list_users_'. rand(1,1000));?>'>
  <div class='<?php echo esc_attr($data ['color_class']);?>'>
    <div class='<?php echo esc_attr($data ['theme']) . ' ' . esc_attr($data ['extra_class']);?>' >
      <div class='ulp-wrapp-list-grid'>
        <div class='<?php echo esc_attr($data ['parent_class']);?>' id='<?php echo esc_attr($data ['div_parent_id']);?>' >
          <ul>
            <?php $i = 1; ?>
            <?php $breaker_div = 1; ?>
            <?php foreach ($data ['items_output'] as $item): ?>
                <?php if (!empty($new_div) && $new_div == 1):?>
                    <?php $div_id = $data ['ul_id'] . '_' . $breaker_div;?>
                    <ul id='<?php echo esc_attr($div_id);?>'>
                <?php endif;?>
                <li style =' width: <?php echo esc_attr($data ['li_width']);?>' >
				  <?php echo esc_ulp_content($item);?>
                </li>
                <?php if ($i % $data ['items_per_slide']==0 || $i==$data ['total_items'] || $i % $data ['columns']==0):?>
                    <?php
                        $breaker_div++;
                        $new_div = 1;
                    ?>
                    <div class="ulp-clear"></div></ul>
                <?php else:?>
                    <?php $new_div = 0;?>
                <?php endif;?>
                <?php $i++;?>
            <?php endforeach;?>
        </div>

            <div class="ulp-clear"></div>
      </div>
    </div>
  </div>
</div>

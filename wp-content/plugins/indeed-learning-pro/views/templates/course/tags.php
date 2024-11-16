<?php if (!empty($data['items'])):?>
    <div class="ulp-list-tags-wrapp">
        <?php foreach ($data['items'] as $item):?>
            <div class="ulp-course-tag"
            style= "
            <?php if (!empty($item->color)){?>
              background-color: <?php echo esc_attr($item->color);?>;
              <?php } ?>">
              <?php echo esc_html($item->name);?>
            </div>
        <?php endforeach;?>
    </div>
<?php endif;?>

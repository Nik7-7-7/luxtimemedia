<span class="ulp-js-sorting-field-type" ></span>
<?php
if ( isset( $data['value'] ) && !isset( $data['values'] ) ){
    $data['values'] = $data['value'];
}
?>
<div class="ulp-sorting-field-wrapp">
    <?php if ($data ['values']):?>
        <ul class="ulp-sortable-field-ul">
            <?php foreach ($data ['values'] as $value):?>
                <li>
                    <span class="input-group-addon"><i class="fa-ulp fa-sort-ulp"></i></span>
                    <span><?php echo esc_html($value);?></span>
                    <input type="hidden" data-field_type="sorting" name="<?php echo esc_attr($data ['name']) . '[]';?>" class='<?php echo esc_attr($data ['classes']) . ' ' . $data ['field_extra_attributes'];?>' value="<?php echo esc_html($value);?>" />
                </li>
            <?php endforeach;?>
        </ul>
    <?php endif;?>
</div>

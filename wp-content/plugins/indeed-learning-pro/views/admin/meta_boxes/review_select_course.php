<div>
    <div><?php esc_html_e('Select course', 'ulp');?></div>
    <select name="ulp_course_id">
        <?php foreach ($data['courses'] as $courseObject):?>
            <option value="<?php echo esc_attr($courseObject['ID']);?>" <?php echo ($courseObject['ID']==$data['ulp_course_id']) ? 'selected' : '';?> ><?php echo esc_html($courseObject['post_title']);?></option>
        <?php endforeach;?>
    </select>
</div>

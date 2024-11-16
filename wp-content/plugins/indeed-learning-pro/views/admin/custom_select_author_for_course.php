<div class="inside">
<label class="screen-reader-text" for="post_author_override"><?php esc_html_e('Author', 'ulp');?></label>
<select name="post_author_override" id="post_author_override" >
    <?php foreach ($data ['users'] as $inside):?>
	       <option value="<?php echo esc_attr($inside->uid);?>" <?php echo ($data ['the_value']==$inside->uid) ? 'selected' : '';?> ><?php echo esc_html($inside->user_login);?></option>
    <?php endforeach;?>
</select></div>

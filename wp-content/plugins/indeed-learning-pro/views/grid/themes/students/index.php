<div class="ulp-grid-list-courses">
    <?php if (!empty($student->feat_image)):?>
        <div>
            <img src="<?php echo esc_url($student->feat_image);?>"  />
        </div>
    <?php endif;?>
    <?php if (!empty($student->full_name)):?>
        <div>
            <?php echo esc_html($student->full_name);?>
        </div>
    <?php endif;?>
    <?php if (!empty($student->user_email)):?>
        <div>
            <?php echo esc_html($student->user_email);?>
        </div>
    <?php endif;?>
</div>

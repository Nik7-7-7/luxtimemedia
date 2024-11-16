<?php if (!empty($data['items'])):?>

    <div class="ulp-more-courses-by-wrapp">

        <?php if (empty($data['instructor_name'])):?>
            <h3><?php echo esc_html__('More Courses by this instructor', 'ulp');?></h3>
        <?php else : ?>
            <h3><?php echo esc_html__('More Courses by ', 'ulp') . esc_html($data['instructor_name']);?></h3>
        <?php endif;?>

        <div class="ulp-list-courses-wrapp ulp-wishlist-courses-wrapp">

            <?php foreach ($data['items'] as $object):?>
                <?php include ULP_PATH . 'views/templates/course/more_courses_by-single_course.php';?>
            <?php endforeach;?>

        </div>

    </div>

<?php endif;?>

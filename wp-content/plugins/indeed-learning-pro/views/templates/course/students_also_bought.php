<div class="ulp-students-also-bought-wrapp">

    <h2><?php esc_html_e('Students also Bought', 'ulp');?></h2>
    <div class="ulp-students-also-bought-list">
        <?php foreach ($items as $object):?>
            <?php include ULP_PATH . 'views/templates/course/miniatures-single_course.php';?>
        <?php endforeach;?>
    </div>

</div>

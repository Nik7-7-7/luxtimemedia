
<div class="ulp-dashboard-title">
		Ultimate Learning Pro -	<span class="second-text"><?php esc_html_e('Dashboard Overall', 'ulp');?></span>
</div>
<div>
    <div class="row">
      	<div class="col-xs-4">
      		<div class="ulp-dashboard-top-box ulp-dashboard-students-top-box">
      			<i class="fa-ulp fa-dashboard-students-ulp"></i>
      			<div class="stats">
      				<h4><?php echo esc_html($data['total_students']);?></h4>
      				<?php esc_html_e('Total Students', 'ulp');?>
      			</div>
      		</div>
      	</div>

      	<div class="col-xs-4">
      		<div class="ulp-dashboard-top-box ulp-dashboard-instructors-top-box">
      			<i class="fa-ulp fa-dashboard-instructors-ulp"></i>
      			<div class="stats">
      				<h4><?php echo esc_html($data['total_instructors']);?></h4>
      				<?php esc_html_e('Total Instructors', 'ulp');?>
            </div>
      		</div>
      	</div>

      	<div class="col-xs-4">
      		<div class="ulp-dashboard-top-box ulp-dashboard-earnings-top-box">
      			<i class="fa-ulp fa-dashboard-earnings-ulp"></i>
      			<div class="stats">
      				<h4><?php echo esc_html($data ['total_earnings']);?></h4>
      				<?php esc_html_e('Total Earnings', 'ulp');?>
            </div>
      		</div>
      	</div>

      	<!--div class="col-xs-3">
      		<div class="ulp-dashboard-top-box ulp-dashboard-course-top-box">
      			<i class="fa-ulp fa-dashboard-top-course-ulp"></i>
      			<div class="stats">
      				<h4 class="ulp-dashboard-top-course-title"><?php echo esc_html($data['top_course']);?></h4>
              <?php esc_html_e('Top course', 'ulp');?>
            </div>
      		</div>
       	</div-->
    </div>

    <div class="row-fluid ulp-row-fluid">

      <div class="col-xs-12">
        <div class="ulp-dashboard-top-box">
            <div><?php esc_html_e('Total Students per Course', 'ulp');?></div>
          <?php if ($data ['student_per_course']): ?>
            <div id="ulp-chart-1" class='ulp-flot'></div>
          <?php else :?>
            <div><h3><?php esc_html_e('Not enough data available.', 'ulp');?></h3></div>
          <?php endif;?>
        </div>
      </div>

    </div>

    <div class="row">
      <div class="col-xs-6">
        <div class="ulp-dashboard-top-box ulp-green-box">
            <h3><?php esc_html_e('Last 5 enrolled students', 'ulp');?></h3>
            <?php if ($data ['last_five_enrolled_students']):?>
                <ol>
                <?php foreach ($data ['last_five_enrolled_students'] as $object):?>
                    <li>
                        <strong><?php echo esc_html($object->user_login) . ' @'. esc_html($object->user_email) . '';?></strong>
                        <div><?php echo esc_html__('Register on ', 'ulp') . ulp_print_date_like_wp($object->user_registered);?></div>
                    </li>
                <?php endforeach;?>
                </ol>
            <?php else:?>
                <?php esc_html_e('No students yet!', 'ulp');?>
            <?php endif;?>
        </div>
      </div>
      <div class="col-xs-6">
        <div class="ulp-dashboard-top-box">
            <h3><?php esc_html_e('Last 5 orders', 'ulp');?></h3>
            <?php if ($data ['last_five_orders']):?>
                <ol>
                <?php foreach ($data ['last_five_orders'] as $object):?>
                    <li>
                        <strong><?php echo esc_html($object->user);?></strong>
                        <div><?php echo esc_html__('Payment on ', 'ulp') . ulp_print_date_like_wp($object->post_date);?></div>
                    </li>
                <?php endforeach;?>
                </ol>
            <?php else:?>
                <?php esc_html_e('No orders yet!', 'ulp');?>
            <?php endif;?>
        </div>
      </div>
    </div>

    <?php do_action( 'ulp_admin_action_dashboard_html' );?>

</div>

<?php if ($data ['student_per_course']):?>
	<?php foreach ($data ['student_per_course'] as $k => $v ):?>
		<span class="ulp-js-dashboard-students-per-course" data-k="<?php echo esc_attr($k);?>" data-v="<?php echo esc_attr($v);?>" ></span>
	<?php endforeach;?>
<?php endif;?>

<?php

<div class="ulp-instructor-edit ulp-instructor-edit-student-list">
<h2 class="ulp-instructor-edit-top-title"><?php echo esc_html__('Students for ', 'ulp') . '<span class="ulp-post-title">'. esc_html($courseName) . '</span>';?></h2>

<?php if ($students):?>
<div class="ulp-instructor-dashboard-student-list ulp-instructor-dashboard-list">
    <div class="ulp-instructor-item-list-head">
     <div class="ulp-instructor-item-list-row">
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-name"><?php esc_html_e('Name', 'ulp');?></div>
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-login"><?php esc_html_e('Username', 'ulp');?></div>
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-email"><?php esc_html_e('Email Address', 'ulp');?></div>
     </div>
    </div>
     <div class="ulp-instructor-item-list-content">
	<?php foreach ($students as $key=>$studentObject):?>
    <?php
			$row_class ='';
      if($key%2 == 0){
         $row_class = 'even';
      }else{
           $row_class = 'odd';
      }
		?>
    <div class="ulp-instructor-item-list-row <?php echo esc_attr($row_class);?>">
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-name"><?php echo esc_html(\DbUlp::get_full_name($studentObject->user_id));?></div>
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-login"><?php echo esc_html($studentObject->user_login);?></div>
        <div class="ulp-instructor-item-list-col ulp-instructor-item-list-email"><?php echo esc_html($studentObject->user_email);?></div>
	</div>
    <?php endforeach;?>
  </div>
</div>
<?php endif;?>

<?php if ($pagination):?>
    <?php echo esc_ulp_content($pagination);?>
<?php endif;?>
</div>

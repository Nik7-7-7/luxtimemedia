<?php do_action('ulp_do_action_before_student_profile_html');?>

<?php echo esc_ulp_content($Student_Profile->Header());?>

<?php echo esc_ulp_content($Student_Profile->NavBar());?>

<?php echo esc_ulp_content($Student_Profile->Content());?>

<?php echo esc_ulp_content($Student_Profile->Footer());?>

<?php do_action('ulp_do_action_after_student_profile_html');?>

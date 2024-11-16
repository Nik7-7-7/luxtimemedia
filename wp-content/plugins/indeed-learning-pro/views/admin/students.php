<?php wp_enqueue_script('UlpAdminSendEmail', ULP_URL . 'assets/js/UlpAdminSendEmail.js', ['jquery'], '3.7' );?>
<div class="ulp-wrapper">
<div class="ulp-page-title"><?php esc_html_e('Manage Students', 'ulp');?></div>
    <div class="ulp-margin-bottom">
      <form method="post" action="<?php echo admin_url('user-new.php');?>" >
        <input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
        <input type="hidden" name="createuser" value="1" />
        <div class="col-md-3">
          <button type="submit" class="ulp-add-new-bttn" ><?php esc_html_e('Add new Student', 'ulp');?></button>
        </div>
      </form>
      <div class="ulp-clear"></div>
    </div>
<?php if ($data['students']):?>
    <div class="tablenav_light top">
        <div class="ulp-admin-students-pagination-wrapper">
          <div class="col-md-4">
              <?php if (!empty($data['pagination'])):?>
                  <?php echo esc_ulp_content($data['pagination']);?>
              <?php endif;?>
          </div>
          <div class="col-md-8">
            <form method="post" >
              <input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />
              <div class="input-group col-md-12">
                  <input type="text" class="search-query form-control" name="search_q" placeholder="Search" />
                  <span class="input-group-btn ulp-admin-students-search-wrapper">
                      <button class="button" type="submit"><?php esc_html_e('Search', 'ulp');?></button>
                  </span>
              </div>
            </form>
          </div>
        </div>
        <br class="clear">
    </div>
<?php endif ?>
  <div class="inside">
  <?php if ($data['students']):?>
    <table class="ulp-admin-tables ulp-admin-students-table">
        <thead class="thead-inverse">
          <tr>
            <th class="ulp-admin-students-table-col1"><?php esc_html_e('User ID', 'ulp');?></th>
            <th><?php esc_html_e('Username', 'ulp');?></th>
            <th><?php esc_html_e('E-mail address', 'ulp');?></th>
            <th class="ulp-admin-students-table-col2"><?php esc_html_e('Full name', 'ulp');?></th>
            <th class="ulp-admin-students-table-col3"><?php esc_html_e('Courses', 'ulp');?></th>
            <?php do_action( 'ulp_admin_action_list_studets_column' );?>
            <th><?php esc_html_e('Rewarded Points', 'ulp');?></th>
            <?php if ($data ['show_badges']):?>
            <th><?php esc_html_e('Badges', 'ulp');?></th>
            <?php endif;?>
            <th><?php esc_html_e('User registered date', 'ulp');?></th>
            <th><?php esc_html_e('Details', 'ulp');?></th>
          </tr>
        </thead>
        <tfoot class="thead-inverse">
          <tr>
            <th><?php esc_html_e('User ID', 'ulp');?></th>
            <th><?php esc_html_e('Username', 'ulp');?></th>
            <th><?php esc_html_e('E-mail address', 'ulp');?></th>
            <th><?php esc_html_e('Full name', 'ulp');?></th>
            <th class="ulp-admin-students-table-col4"><?php esc_html_e('Courses', 'ulp');?></th>
            <?php do_action( 'ulp_admin_action_list_studets_column' );?>
            <th><?php esc_html_e('Rewarded Points', 'ulp');?></th>
            <?php if ($data ['show_badges']):?>
            <th><?php esc_html_e('Badges', 'ulp');?></th>
            <?php endif;?>
            <th><?php esc_html_e('User registered date', 'ulp');?></th>
            <th><?php esc_html_e('Details', 'ulp');?></th>
          </tr>
        </tfoot>
        <tbody>
            <?php   $i = 1;
				foreach ($data['students'] as $object):?>
                <tr onMouseOver="ulpDhSelector('<?php echo esc_attr('#hidden' . $object->user_id);?>', 1);" onMouseOut="ulpDhSelector('<?php echo esc_attr('#hidden' . $object->user_id);?>', 0);" class="<?php echo ($i%2==0) ? 'alternate' : '';?>">
                  <td scope="row">
                      <?php echo esc_html($object->user_id);?>
                  </td>
                  <td  class="column-instructor">

                      <?php if ($object->avatar):?>
                          <img src="<?php echo esc_url($object->avatar);?>" />
                      <?php endif;?>
                      <a href="<?php echo admin_url('user-edit.php?user_id=' . $object->user_id); ?>" target="_blank"><?php echo DbUlp::getUsernameByUID($object->user_id);?></a>
                      <div id="<?php echo esc_attr('hidden' . $object->user_id);?>" class="ulp-visibility-hidden"><a href="<?php echo admin_url('user-edit.php?user_id='.$object->user_id);?>"><?php esc_html_e('Edit', 'ulp');?></a></div>
                  </td>
                  <td><a href="mailto:<?php echo esc_url($object->user_email);?>"><?php echo esc_html($object->user_email);?></a></td>
                  <td><?php echo esc_html($object->full_name);?></td>


                  <td><?php
                      if (strpos($object->courses_entities_id, ',')!==FALSE){
                          $courses = explode(',', $object->courses_entities_id);
                      } else {
                          $courses = array($object->courses_entities_id);
                      }

                      foreach ($courses as $course_entity_id){
                          $course = DbUlp::getCourseNameByEntityId($course_entity_id);
                          $course_id = DBUlp::getCourseIdByEntityId($course_entity_id);
                          if ( !DbUlp::postDoesReallyExists($course_id) ){
                             continue;
                          }
                          $progress = $data['users_course_object']->getProgress($object->user_id, $course_id);
            						  if ($progress == 100):
            						  	$resultArr = $data['users_course_object']->GetCourseResult(0, 0, $course_entity_id);
                                      	$result = $resultArr['grade'];
            						  endif;
						              ?>
                              <div class="ulp-stundent-course-wrapper <?php echo ($progress == 100 ? 'ulp-course-completed' : ''); ?>">
							                  <span>
                                  <a href="<?php echo admin_url('post.php?post=' . $course_id . '&action=edit');?>" target="_blank" title="<?php echo esc_attr($progress);?>% completed">
                                    <?php echo esc_html($course);
                                    if(isset($result) && $result > 0){
                                       echo esc_ulp_content('<div class="ulp-grade"> ('. esc_html__('Grade: ', 'ulp').$result.')</div>');
                                    }?>
                                    </a>
                                  </span>
                                <div class="ulp-stundent-course-progress"><div class="ulp-percentage"><?php echo esc_html($progress);?>%</div></div>
                                <div class="ulp-clear"></div>
                              </div>
                          <?php
                      }
                  ?></td>
                  <?php do_action( 'ulp_admin_action_list_studets_column_value', $object->user_id );?>
                  <td><div class="ulp-admin-item-box ulp-text-aling-center ulp-admin-students-rewards-wrapper"><?php echo esc_html($object->reward_points);?><span><?php esc_html_e('Points', 'ulp');?></span></div></td>
                  <?php if ($data['show_badges']):?>
                  <td><?php if ($object->badges):?>
                      <?php foreach ($object->badges as $badge_object):?>
                          <a href="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=student_badges&action=edit&id='.$badge_object->id);?>"><img src="<?php echo esc_url($badge_object->badge_image);?>" class="ulp-micro-badge" /></a>
                      <?php endforeach;?>
                  <?php endif;?></td>
                  <?php endif;?>
                  <td><?php echo ulp_print_date_like_wp($object->user_registered);?></td>
                  <td>
                      <div class="ulp-small-button ulp-turcoaz-button"><a href="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=view_student_activity&uid=' . $object->user_id);?>"><?php esc_html_e('Activity', 'ulp');?></a></div>
                      <div class="ulp-small-button ulp-pointer ulp-light-red-button ulp-admin-students-reset-points-button"><span onclick="ulpResetPoints(<?php echo esc_attr($object->user_id);?>);"><?php esc_html_e('Reset Points', 'ulp');?></span></div>
                      <div class="ulp-small-button ulp-grey-button ulp-admin-do-send-email-via-ulp" data-uid="<?php echo esc_attr($object->user_id);?>" ><?php esc_html_e('Direct Email', 'ulp');?></div>
                  </td>
                </tr>
              <?php   $i++;
			  endforeach;?>
        </tbody>
    </table>
    <div class="ulp-clear"></div>
  <?php else:?>
      <h3><?php esc_html_e('No students yet!', 'ulp');?></h3>
  <?php endif;?>
  </div>
</div>

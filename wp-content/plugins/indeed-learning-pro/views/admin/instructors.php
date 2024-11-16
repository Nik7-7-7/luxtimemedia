<?php wp_enqueue_script('UlpAdminSendEmail', ULP_URL . 'assets/js/UlpAdminSendEmail.js', ['jquery'], '3.7' );?>
<div class="ulp-wrapper">

<div class="ulp-page-title"><?php esc_html_e('Manage Instructors', 'ulp');?></div>

    <div class="ulp-admin-instructor-add-new">

        <form method="post" action="<?php echo admin_url('user-new.php');?>">
            <input type="hidden" name="ulp_admin_nonce" value="<?php echo wp_create_nonce( 'ulp_admin_nonce' );?>" />

            <input type="hidden" name="role" value="ulp_instructor" />

            <input type="hidden" name="createuser" value="1" />

            <input type="hidden" name="is_instructor" value="1" />

            <div class="col-md-3">

                <button type="submit" class="ulp-add-new-bttn" ><?php esc_html_e('Add new User Instructor', 'ulp');?></button>

            </div>

        </form>

        <div class="ulp-clear"></div>

      </div>



      <div class="tablenav_light top">

      		<div class="ulp-admin-instructor-pagination-wrapper">

            <div class="col-md-4">

                <?php if (!empty($data['pagination'])):?>

                    <?php echo esc_ulp_content($data['pagination']);?>

                <?php endif;?>

            </div>

            <div class="col-md-8">

              <form method="post" >

                <div class="input-group col-md-12">

                    <input type="text" class="search-query form-control" name="search_q" placeholder="Search" />

                    <span class="input-group-btn">

                        <input class="button" type="submit" value="<?php esc_html_e('Search', 'ulp');?>" />

                    </span>

                </div>

              </form>

            </div>

          </div>

      		<br class="clear">

      </div>


  <div class="inside">

  <?php if ($data['instructors']):?>

    <table class="ulp-admin-tables ">

        <thead class="thead-inverse">

          <tr>

            <th><?php esc_html_e('User ID', 'ulp');?></th>

            <th><?php esc_html_e('Username', 'ulp');?></th>

            <th><?php esc_html_e('E-mail address', 'ulp');?></th>

            <th><?php esc_html_e('Full name', 'ulp');?></th>

            <th class="ulp-admin-instructor-course-col"><?php esc_html_e('Courses', 'ulp');?></th>

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

            <th><?php esc_html_e('Courses', 'ulp');?></th>

            <th><?php esc_html_e('User registered date', 'ulp');?></th>
            <th><?php esc_html_e('Details', 'ulp');?></th>

          </tr>

        </tfoot>

        <tbody>

            <?php  $i = 1;

			     foreach ($data['instructors'] as $object):?>

           <tr id="<?php echo esc_attr('row_'.$object->uid);?>" onMouseOver="ulpDhSelector('<?php echo esc_attr('#hidden' . $object->uid);?>', 1);" onMouseOut="ulpDhSelector('<?php echo esc_attr('#hidden' . $object->uid);?>', 0);" class="
             <?php echo ($i%2==0) ? 'alternate' : '';?>">

                  <td scope="row"><?php echo esc_html($object->uid);?></td>

                  <td class="column-instructor">



                      <?php if ($object->avatar):?>

                          <img src="<?php echo esc_html($object->avatar);?>" />

                      <?php endif;?>
					  <?php
					  	$pending = '';
					  	if(isset($object->roles['ulp_instructor-pending'])){
					  		$pending = ' class="ulp-admin-instructor-pending" ';
						}
					  ?>
                      <?php echo esc_ulp_content('<a href="' . admin_url('user-edit.php?user_id=' . $object->uid) . '" target="_blank" '.esc_attr($pending).'>'.esc_html($object->user_login).'</a>');?>

                      <div id="<?php echo esc_ulp_content('hidden' . $object->uid);?>" class="ulp-visibility-hidden">

                          <a href="<?php echo admin_url('user-edit.php?user_id='.$object->uid);?>"><?php esc_html_e('Edit', 'ulp');?></a> |

                          <span class="ulp-delete js-ulp-instructor-become-normal-user" data-uid="<?php echo esc_attr($object->uid);?>" ><?php esc_html_e('Remove', 'ulp');?></span>

                      </div>

                  </td>

                  <td><a href="mailto:<?php echo esc_attr($object->user_email);?>"><?php echo esc_html($object->user_email);?></a></td>

                  <td><?php echo esc_html($object->full_name);?></td>

                  <td><?php

                      if (!empty($object->courses)){

                          foreach ($object->courses as $course_object){

                              echo esc_ulp_content('<div class="ulp-property"><a href="' . admin_url('post.php?post=' . esc_attr($course_object->post_id) . '&action=edit') . '" target="_blank">' . $course_object->post_title . '</a></div>');

                          }

                      }

                  ?></td>

                  <td><?php echo ulp_print_date_like_wp($object->user_registered);?></td>
                  <td>
                    <div class="ulp-small-button ulp-grey-button ulp-admin-do-send-email-via-ulp" data-uid="<?php echo esc_attr($object->uid);?>" ><?php esc_html_e('Direct Email', 'ulp');?></div>
                  </td>

                </tr>

              <?php  $i++;

			    endforeach;?>

        </tbody>

    </table>

    <div class="ulp-clear"></div>



  <?php else:?>

      <div><?php esc_html_e('No instructors yet!', 'ulp');?></div>

  <?php endif;?>

  </div>

</div>

<?php

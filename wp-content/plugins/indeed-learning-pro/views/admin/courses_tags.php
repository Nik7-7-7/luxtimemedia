<div class="ulp-stuffbox">
<h3 class="ulp-h3"><?php esc_html_e('Courses Tags', 'ulp');?></h3>
  <div class="inside">
  <div class="ulp-inside-item">
    <div class="row">
  <div class="wrap">
      <a href="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=courses_tags_add_edit');?>" class="page-title-action ulp-add-new-post-bttn"><?php esc_html_e('Add new Tag', 'ulp');?></a>
  </div>
  <div>
  <p><?php esc_html_e('Once Tags are created and avaialable you can assign them to your courses from "Courses Tags" box when you editing a course.', 'ulp');?></p>
  </div>
  <?php if ($data ['items']!==FALSE && count($data ['items'])>0):?>
      <div class="ulp-wrapper">
        <table class="wp-list-table widefat fixed tags ulp-admin-tables striped ">
          <thead  class="thead-inverse">
            <tr>
              <th class="manage-column"><?php esc_html_e('Slug', 'ulp');?></th>
              <th class="manage-column"><?php esc_html_e('Label', 'ulp');?></th>
              <th class="manage-column ulp-text-aling-center ulp-table-delete-col"><?php esc_html_e('Edit', 'ulp');?></th>
              <th class="manage-column ulp-text-aling-center ulp-table-delete-col"><?php esc_html_e('Delete', 'ulp');?></th>
            </tr>
          </thead>
          <tbody>
            <?php	foreach ($data['items'] as $object):?>
                <tr id="<?php echo esc_attr('ulp_div_' . $object->slug);?>">
                  <td><div class="ulp-special-label-style"><?php echo esc_attr($object->slug);?></div></td>
                  <td>
                  <div class="ulp-course-tag-admin ulp-background-color-<?php echo substr($object->color,1);?>"><?php echo esc_html($object->name);?></div>
                  </td>
                  <td class="ulp-text-aling-center"><a href="<?php echo admin_url('admin.php?page=ultimate_learning_pro&tab=courses_tags_add_edit&term_id=' .$object->term_id );?>"><i class="fa-ulp fa-edit-ulp"></i></a></td>
                  <td class="ulp-text-aling-center"><i class="fa-ulp fa-remove-ulp ulp-delete-course-tag" data-term_id="<?php echo esc_attr($object->term_id);?>"></i></td>
                </tr>
            <?php endforeach;	?>
          </tbody>
        </table>
      </div>
  <?php endif;?>
  </div>
 </div>
</div>
</div>
<span class="ulp-js-course-tags"></span>

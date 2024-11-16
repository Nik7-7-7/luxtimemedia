<div class="ulp-list-notes-wrapp">
    <?php if ($data['items']):?>
    		<div class="ulp-course-notes-list">
                <?php foreach ($data['items'] as $id => $object) :?>
                   <div class="ulp-course-note-item">
                   	  <div class="ulp-course-note-title"><?php echo stripslashes($object->note_title);?></div>
                      <div class="ulp-course-note-course"><?php echo esc_ulp_content($object->course);?></div>
                      <div class="ulp-course-note-time"><?php echo ulp_print_date_like_wp($object->obtained_date);?></div>
                       <div class="ulp-course-note-text"><?php echo stripslashes($object->note_content);?></div>
                      <div class="ulp-course-note-removebutton js-ulp-remove-note" data-id="<?php echo esc_html($object->id);?>"><i class="fa-ulp fa-remove-ulp ulp-pointer " ></i><span><?php esc_html_e('Remove', 'ulp');?></span></div>
                    </div>
                <?php endforeach;?>
			</div>
    <?php else:?>
        <div class="ulp-additional-message"><?php echo Ulp_Global_Settings::get('ulp_messages_list_notes_zero');?></div>
    <?php endif;?>
</div>

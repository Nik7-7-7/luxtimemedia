<div class="ulp-become-instructor-wrapp">
    <?php if (empty($data['uid'])):?>
        <div><?php echo Ulp_Global_Settings::get('ulp_become_instructor_user_not_logged');?></div>
    <?php else:?>
        <?php if (empty($data['instructor'])):?>
      		<div class="ulp-become-instructor-the-button" id="ulp_become_instructor"><?php echo Ulp_Global_Settings::get('ulp_messages_become_instructor_button');?></div>
      	<?php elseif ($data['instructor']==1):?>
          <div class="ulp-instructor-registered"><?php echo Ulp_Global_Settings::get('ulp_messages_become_instructor_already_registered');?></div>
        <?php elseif ($data['instructor']==-1):?>
          <div class="ulp-instructor-pending-request"><?php echo Ulp_Global_Settings::get('ulp_messages_become_instructor_pending');?></div>
      	<?php endif;?>
    <?php endif;?>
</div>
<span class="ulp-js-become-instructor-message" data-value="<?php echo Ulp_Global_Settings::get('ulp_messages_become_instructor_pending');?>"></span>

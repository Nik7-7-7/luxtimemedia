<div class="ulp-instructor-dashboard-header">
	<div class="ulp-instructor-dashboard-top-section">
    	<div class="ulp-instructor-dashboard-top-section-left">
        	<div class="ulp-instructor-dashboard-avatar"><img src="<?php echo esc_url($avatar);?>" class="ulp-instructor-photo"/></div>
            <div class="ulp-instructor-dashboard-name">
             <div><?php echo esc_html($fullName);?> - <span><?php echo esc_html($role);?></span></div>
             <div class="ulp-instructor-dashboard-email"><?php echo esc_html($userEmail);?></div>
            </div>
    	</div>
        <div class="ulp-instructor-dashboard-top-section-right">
        	<div class="ulp-instructor-dashboard-top-section-counts">
            	<div class="ulp-instructor-dashboard-top-section-count-number"><?php echo esc_ulp_content($newQuestions);?></div>
                <div class="ulp-instructor-dashboard-top-section-count-label"><?php esc_html_e('new Questions', 'ulp');?></div>
            </div>
            <div class="ulp-instructor-dashboard-top-section-counts">
            	<div class="ulp-instructor-dashboard-top-section-count-number ulp-top-section-count-number-undead"><?php echo esc_ulp_content($newStudents);?></div>
                <div class="ulp-instructor-dashboard-top-section-count-label"><?php esc_html_e('new Students', 'ulp');?></div>
            </div>
        </div>
    <div class="ulp-clear"></div>
    </div>
    <div class="ulp-instructor-dashboard-menu-wrapper">
        <?php foreach ($tabs as $subArray):?>
						<?php $extraClass = $subArray['base_slug']==$currentTab ? 'ulp-instructor-dashboard-menu-item-selected' : '';?>
						<?php $link = add_query_arg(['tab' => $subArray['tab']], $baseUri);?>
						<?php if (!empty($subArray['type'])){
							 $link = add_query_arg(['type' => $subArray['type']], $link);
						}?>
            <div class="ulp-instructor-dashboard-menu-item <?php echo esc_attr($extraClass);?>"><a href="<?php echo esc_url($link);?>" ><?php echo esc_html($subArray['label']);?></a></div>
        <?php endforeach;?>
       		<div  class="ulp-instructor-dashboard-menu-item ulp-instructor-dashboard-menu-item-new-course"><i class="fa-ulp fa-instructordashboard-newcourse-ulp"></i><a href="<?php echo esc_url($addNewCource);?>" ><?php esc_html_e('Create New Course', 'ulp');?></a></div>
    	<div class="ulp-clear"></div>
    </div>
</div>

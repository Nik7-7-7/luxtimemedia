<?php

register_block_pattern(
	'mailster-workflow/scratch',
	array(
		'title'         => __( 'Start from scratch', 'mailster' ),
		'description'   => __( 'Create your own custom workflow on a blank canvas.', 'mailster' ),
		'viewportWidth' => 300,
		'postTypes'     => array( 'mailster-workflow' ),
		'categories'    => array( 'mailster-custom-category' ),
		'content'       => '<!-- wp:mailster-workflow/triggers -->
		<!-- wp:mailster-workflow/trigger /-->
		<!-- /wp:mailster-workflow/triggers -->',
	)
);

register_block_pattern(
	'mailster-workflow/simple-welcome',
	array(
		'title'         => __( 'Welcome new subscribers', 'mailster' ),
		'postTypes'     => array( 'mailster-workflow' ),
		'description'   => __(
			'This simple and efficient workflow is perfect for welcoming new subscribers. It is easy to set up and produces great outcomes. Once an individual subscribes to your form and is added to a list, the workflow will automatically send them a welcome email.',
			'mailster'
		),
		'viewportWidth' => 600,
		'categories'    => array( 'mailster-custom-category' ),
		'content'       => '<!-- wp:mailster-workflow/triggers -->
		<!-- wp:mailster-workflow/trigger {"trigger":"list_add","lists":[-1]} /-->
		<!-- /wp:mailster-workflow/triggers -->
		
		<!-- wp:mailster-workflow/comment {"comment":"' . esc_attr__( 'Send a welcome email whenever a user subscribes to your lists.', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/email {"name":"' . esc_attr__( 'Welcome Email', 'mailster' ) . '"} /-->',
	)
);


register_block_pattern(
	'mailster-workflow/enhanced-welcome',
	array(
		'title'         => __( 'Enhanced Welcome Email Series', 'mailster' ),
		'postTypes'     => array( 'mailster-workflow' ),
		'description'   => __(
			'Employ various channels such as sign up forms or landing pages to activate the identical workflow for your new subscribers. All new subscribers will be sent the same welcome campaign. Additionally, you can modify a custom field for the engaged subscribers and shift the unresponsive ones to a designated group for future targeting.',
			'mailster'
		),
		'viewportWidth' => 600,
		'categories'    => array( 'mailster-custom-category' ),
		'content'       => '<!-- wp:mailster-workflow/triggers -->
		<!-- wp:mailster-workflow/trigger {"trigger":"list_add","lists":[-1]} /-->
		<!-- /wp:mailster-workflow/triggers -->
		
		<!-- wp:mailster-workflow/email {"id":"bf5811","name":"' . esc_attr__( 'Welcome Email', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/delay {"amount":1,"unit":"days"} /-->
		
		<!-- wp:mailster-workflow/email {"id":"71804e","name":"' . esc_attr__( 'Preferences Email', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/delay {"amount":3,"unit":"days"} /-->
		
		<!-- wp:mailster-workflow/conditions {"conditions":"conditions%5B0%5D%5B0%5D%5Bfield%5D=_click\u0026conditions%5B0%5D%5B0%5D%5Boperator%5D=is\u0026conditions%5B0%5D%5B0%5D%5Bvalue%5D%5B0%5D=bf5811\u0026conditions%5B0%5D%5B0%5D%5Bvalue%5D%5B1%5D=71804e"} -->
		<!-- wp:mailster-workflow/condition-yes -->
		<!-- wp:mailster-workflow/action {"action":"add_tag"} /-->
		
		<!-- wp:mailster-workflow/email {"id":"3431f0","name":"' . esc_attr__( 'Offer #1', 'mailster' ) . '"} /-->
		<!-- /wp:mailster-workflow/condition-yes -->
		
		<!-- wp:mailster-workflow/condition-no -->
		<!-- wp:mailster-workflow/email {"id":"cf181c","name":"' . esc_attr__( 'Welcome', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/delay {"amount":3,"unit":"hours"} /-->
		
		<!-- wp:mailster-workflow/conditions {"conditions":"conditions%5B0%5D%5B0%5D%5Bfield%5D=_click\u0026conditions%5B0%5D%5B0%5D%5Boperator%5D=is\u0026conditions%5B0%5D%5B0%5D%5Bvalue%5D=cf181c"} -->
		<!-- wp:mailster-workflow/condition-yes -->
		<!-- wp:mailster-workflow/action {"action":"add_tag"} /-->
		<!-- /wp:mailster-workflow/condition-yes -->
		
		<!-- wp:mailster-workflow/condition-no -->
		<!-- wp:mailster-workflow/action {"action":"unsubscribe"} /-->
		<!-- /wp:mailster-workflow/condition-no -->
		<!-- /wp:mailster-workflow/conditions -->
		<!-- /wp:mailster-workflow/condition-no -->
		<!-- /wp:mailster-workflow/conditions -->',
	)
);



register_block_pattern(
	'mailster-workflow/pagevisit',
	array(
		'title'         => __( 'Workflow for page visits', 'mailster' ),
		'description'   => __( 'This workflow serves as a valuable tool for targeting subscribers who have visited specific pages. By leveraging this workflow, you can segment your audience based on their browsing behavior, enabling you to deliver personalized campaigns, recommend relevant content, and provide targeted offers.', 'mailster' ),
		'viewportWidth' => 600,
		'postTypes'     => array( 'mailster-workflow' ),
		'categories'    => array( 'mailster-custom-category' ),
		'content'       => '<!-- wp:mailster-workflow/triggers -->
		<!-- wp:mailster-workflow/trigger {"trigger":"page_visit","pages":["/hello-world"]} /-->
		<!-- /wp:mailster-workflow/triggers -->
		
		<!-- wp:mailster-workflow/delay {"amount":10,"unit":"minutes"} /-->
		
		<!-- wp:mailster-workflow/email {"name":"' . esc_attr__( 'Email #1', 'mailster' ) . '"} /--><!-- wp:mailster-workflow/action {"action":"add_tag","tags":["' . esc_attr__( 'Visited', 'mailster' ) . '"]}"}/-->',
	)
);


register_block_pattern(
	'mailster-workflow/win-back-subscribers',
	array(
		'title'         => __( 'Win back inactive subscribers', 'mailster' ),
		// 'article'       => 'https://example.com',
		'description'   => __( 'Re-engage subscribers who have shown a lack of engagement and remove inactive ones. You can initiate a campaign and update a custom field if they interact, or transfer them to the unsubscribed folder if they do not. This approach effectively purges your list, ensuring that your most active subscribers remain, leading to improved email deliverability.', 'mailster' ),
		'viewportWidth' => 600,
		'postTypes'     => array( 'mailster-workflow' ),
		'categories'    => array( 'mailster-custom-category' ),
		'content'       => '<!-- wp:mailster-workflow/triggers -->
		<!-- wp:mailster-workflow/trigger {"trigger":"updated_field","field":"-1"} /-->
		<!-- /wp:mailster-workflow/triggers -->

		<!-- wp:mailster-workflow/comment {"comment":"' . esc_attr__( 'Whenever a field is updated - either by the user or the admin - send a special', 'mailster' ) . ' offer."} /-->

		<!-- wp:mailster-workflow/email {"id":"9869d5","name":"' . esc_attr__( 'Special Offer', 'mailster' ) . '"} /-->

		<!-- wp:mailster-workflow/delay {"amount":3,"unit":"days"} /-->

		<!-- wp:mailster-workflow/conditions {"conditions":"conditions%5B0%5D%5B0%5D%5Bfield%5D=_click\u0026conditions%5B0%5D%5B0%5D%5Boperator%5D=is\u0026conditions%5B0%5D%5B0%5D%5Bvalue%5D=9869d5"} -->
		<!-- wp:mailster-workflow/condition-yes -->
		<!-- wp:mailster-workflow/comment {"comment":"' . esc_attr__( 'If the user clicked in our Special Offer Campaign you could add a tag.', 'mailster' ) . '"} /-->

		<!-- wp:mailster-workflow/action {"action":"add_tag"} /-->
		<!-- /wp:mailster-workflow/condition-yes -->

		<!-- wp:mailster-workflow/condition-no -->
		<!-- wp:mailster-workflow/comment {"comment":"' . esc_attr__( 'Send another campaign if the user haven\'t clicked in our previous message.', 'mailster' ) . '"} /-->

		<!-- wp:mailster-workflow/email {"id":"921df7","name":"' . esc_attr__( 'Final Offer', 'mailster' ) . '"} /-->

		<!-- wp:mailster-workflow/delay {"amount":3,"unit":"days"} /-->

		<!-- wp:mailster-workflow/conditions {"conditions":"conditions%5B0%5D%5B0%5D%5Bfield%5D=_click\u0026conditions%5B0%5D%5B0%5D%5Boperator%5D=is\u0026conditions%5B0%5D%5B0%5D%5Bvalue%5D=921df7"} -->
		<!-- wp:mailster-workflow/condition-yes -->
		<!-- wp:mailster-workflow/action {"action":"add_tag"} /-->
		<!-- /wp:mailster-workflow/condition-yes -->

		<!-- wp:mailster-workflow/condition-no -->
		<!-- wp:mailster-workflow/action {"action":"unsubscribe"} /-->
		<!-- /wp:mailster-workflow/condition-no -->
		<!-- /wp:mailster-workflow/conditions -->
		<!-- /wp:mailster-workflow/condition-no -->
		<!-- /wp:mailster-workflow/conditions -->',
	)
);


register_block_pattern(
	'mailster-workflow/webinar-invitation',
	array(
		'title'         => __( 'Webinar invitation', 'mailster' ),
		'description'   => __( 'Utilize this workflow to extend invitations to subscribers from various signup sources for your webinar event. Following the event, send them a survey email to gather their feedback.', 'mailster' ),
		'viewportWidth' => 600,
		'postTypes'     => array( 'mailster-workflow' ),
		'categories'    => array( 'mailster-custom-category' ),
		'content'       => '<!-- wp:mailster-workflow/triggers -->
		<!-- wp:mailster-workflow/trigger {"trigger":"form_conversion","forms":[]} /-->
		<!-- /wp:mailster-workflow/triggers -->
		
		<!-- wp:mailster-workflow/comment {"comment":"' . esc_attr__( 'Send an RSVP right after they sign up.', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/email {"name":"' . esc_attr__( 'RSVP Email', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/comment {"comment":"' . esc_attr__( 'Assuming your Webinar starts on the 10th every month it\'s a good practice to remind them one day upfront with a dedicate email.', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/delay {"amount":1,"unit":"month","date":"2023-05-14T07:00:00.000Z","month":9} /-->
		
		<!-- wp:mailster-workflow/email {"name":"' . esc_attr__( 'Webinar reminder', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/comment {"comment":"' . esc_attr__( 'Send the actual link to the webinar in this step.', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/delay {"amount":1,"unit":"days"} /-->
		
		<!-- wp:mailster-workflow/email {"name":"' . esc_attr__( 'Email with CTA to Webinar', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/comment {"comment":"' . esc_attr__( 'Wait some time after the webinar has finished and send a feedback request.', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/delay {"amount":3,"unit":"hours"} /-->
		
		<!-- wp:mailster-workflow/email {"name":"' . esc_attr__( 'Feedback Survey', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/action {"action":"add_tag","tags":["' . esc_attr__( 'Webinar Complete', 'mailster' ) . '"]} /-->',
	)
);
register_block_pattern(
	'mailster-workflow/online-course',
	array(
		'title'         => __( 'Online course', 'mailster' ),
		'description'   => __( 'Commence your online course on a designated date and implement an automated system to deliver lessons to your learners every week. This straightforward workflow proves to be highly productive for your course participants.', 'mailster' ),
		'viewportWidth' => 600,
		'postTypes'     => array( 'mailster-workflow' ),
		'categories'    => array( 'mailster-custom-category' ),
		'content'       => '<!-- wp:mailster-workflow/triggers -->
		<!-- wp:mailster-workflow/trigger {"trigger":"list_add"} /-->
		
		<!-- wp:mailster-workflow/trigger {"trigger":"form_conversion","forms":[]} /-->
		<!-- /wp:mailster-workflow/triggers -->
		
		<!-- wp:mailster-workflow/comment {"comment":"' . esc_attr__( 'Send a Welcome Email if user joins the list to your online course.', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/email {"name":"' . esc_attr__( 'Welcome Email', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/comment {"comment":"' . esc_attr__( 'Send the next email on the next Monday at 12:00', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/delay {"amount":1,"unit":"week","date":"2023-05-14T10:00:00.000Z","weekdays":[0]} /-->
		
		<!-- wp:mailster-workflow/email {"name":"' . esc_attr__( 'Lesson #1', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/comment {"comment":"' . esc_attr__( 'After one week send the email for the next lesson.', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/delay {"amount":1,"unit":"weeks","date":"2023-05-14T10:00:00.000Z","weekdays":[0]} /-->
		
		<!-- wp:mailster-workflow/email {"name":"' . esc_attr__( 'Lesson #2', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/comment {"comment":"' . esc_attr__( 'Final email after another week. You can of course add additional emails afte', 'mailster' ) . 'r that."} /-->
		
		<!-- wp:mailster-workflow/delay {"amount":1,"unit":"weeks","date":"2023-05-14T10:00:00.000Z","weekdays":[0]} /-->
		
		<!-- wp:mailster-workflow/email {"name":"' . esc_attr__( 'Final Lesson', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/comment {"comment":"' . esc_attr__( 'Sometimes it\'s good to remove users from a list to keep your list clean.', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/action {"action":"remove_list"} /-->
		
		<!-- wp:mailster-workflow/comment {"comment":"' . esc_attr__( 'You can also add tags to the subscriber once the workflow is finished.', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/action {"action":"add_tag","tags":["' . esc_attr__( 'Course #1 finished', 'mailster' ) . '"]} /-->',
	)
);

register_block_pattern(
	'mailster-workflow/birthday-wishes',
	array(
		'title'         => __( 'Celebrate customer birthdays', 'mailster' ),
		'description'   => __( 'Employ this workflow to pleasantly surprise your subscribers with birthday wishes on their special day!', 'mailster' ),
		'viewportWidth' => 600,
		'postTypes'     => array( 'mailster-workflow' ),
		'categories'    => array( 'mailster-custom-category' ),
		'content'       => '<!-- wp:mailster-workflow/triggers -->
		<!-- wp:mailster-workflow/trigger {"trigger":"anniversary","repeat":-1,"date":"2023-05-14T07:00:00.000Z","field":"birthday"} /-->
		<!-- /wp:mailster-workflow/triggers -->
		
		<!-- wp:mailster-workflow/comment {"comment":"' . esc_attr__( 'Send Birthday wishes to your subscribers. It\'s a common practice to offer a special discount which is only valid for a certain time frame.', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/email {"name":"' . esc_attr__( 'Birthday Wishes', 'mailster' ) . '"} /-->',
	)
);


register_block_pattern(
	'mailster-workflow/target-contacts',
	array(
		'title'         => __( 'Target engaged contacts', 'mailster' ),
		'description'   => __( 'Gain insights into the individuals who engage with the links in your emails. Utilize this information to send follow-up emails containing additional information tailored to their interests and actions. By understanding their interactions, you can deliver targeted and relevant content to further engage and nurture your audience.', 'mailster' ),
		'viewportWidth' => 600,
		'postTypes'     => array( 'mailster-workflow' ),
		'categories'    => array( 'mailster-custom-category' ),
		'content'       => '<!-- wp:mailster-workflow/triggers -->
		<!-- wp:mailster-workflow/trigger {"trigger":"link_click","links":["' . home_url() . '"]} /-->
		<!-- /wp:mailster-workflow/triggers -->
		
		<!-- wp:mailster-workflow/comment {"comment":"' . esc_attr__( 'If the user clicks on one of the links defined in the trigger add a tag and send an email after 3 days.', 'mailster' ) . '"} /-->
		
		<!-- wp:mailster-workflow/action {"action":"add_tag","tags":["' . esc_attr__( 'Clicked link', 'mailster' ) . '"]} /-->
		
		<!-- wp:mailster-workflow/delay {"amount":3,"unit":"days"} /-->
		
		<!-- wp:mailster-workflow/email {"name":"' . esc_attr__( 'Discover more', 'mailster' ) . '"} /-->',
	)
);


register_block_pattern(
	'mailster-workflow/check-for-eu-member',
	array(
		'title'         => __( 'Check for EU Member', 'mailster' ),
		'description'   => __(
			'The following process checks whether the user is a member of the European Union, and then adds or removes a corresponding tag based on the result.',
			'mailster'
		),
		'viewportWidth' => 600,
		'postTypes'     => array( 'mailster-workflow' ),
		'categories'    => array( 'mailster-custom-category' ),
		'content'       => '<!-- wp:mailster-workflow/conditions {"conditions":"conditions%5B0%5D%5B0%5D%5Bfield%5D=geo\u0026conditions%5B0%5D%5B0%5D%5Boperator%5D=is\u0026conditions%5B0%5D%5B0%5D%5Bvalue%5D=_EN"} -->
		<!-- wp:mailster-workflow/condition-yes -->
		<!-- wp:mailster-workflow/action {"action":"add_tag","tags":["EU"]} /-->
		<!-- /wp:mailster-workflow/condition-yes -->
		
		<!-- wp:mailster-workflow/condition-no -->
		<!-- wp:mailster-workflow/action {"action":"remove_tag","tags":["EU"]} /-->
		<!-- /wp:mailster-workflow/condition-no -->
		<!-- /wp:mailster-workflow/conditions -->',
	)
);

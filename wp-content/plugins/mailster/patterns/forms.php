<?php


register_block_pattern(
	'mailster-form/pattern-01',
	array(
		'title'         => __( 'Pattern 01', 'mailster' ),
		'categories'    => array( 'featured', 'mailster-forms' ),
		'viewportWidth' => 962,
		'inserter'      => false,
		'postTypes'     => array( 'mailster-form' ),
		'keywords'      => array( 'mailster-form' ),
		'content'       => '<!-- wp:mailster/form-wrapper {"css":{"general":".input, .submit-button{\n\theight:66px;\n}\n.mailster-wrapper{\n    margin:0 !important;\n}\n","tablet":"","mobile":""},"style":{"borderRadius":"0px","borderWidth":"0px"}} -->
<form method="post" novalidate class="wp-block-mailster-form-wrapper mailster-block-form"><div class="mailster-block-form-inner">
	
<!-- wp:mailster/field-email {"inline":true,"style":{"width":60}} -->
<div class="wp-block-mailster-field-email mailster-wrapper mailster-wrapper-required mailster-wrapper-type-email mailster-wrapper-inline mailster-wrapper-asterisk" style="width:60%"><input name="email" type="email" aria-required="true" aria-label="' . esc_attr__( 'Email', 'mailster' ) . '" spellcheck="false" required value="" class="input" autocomplete="email" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Email', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-email -->

<!-- wp:mailster/field-submit {"style":{"width":40,"borderRadius":"0px","borderWidth":"0px"}} -->
<div class="wp-block-mailster-field-submit mailster-wrapper mailster-wrapper-type-submit wp-block-button" style="width:40%"><input name="submit" type="submit" style="border-width:0px;border-radius:0px" value="' . esc_attr__( 'Subscribe', 'mailster' ) . '" class="wp-block-button__link submit-button"/></div>
<!-- /wp:mailster/field-submit --></div></form>
<!-- /wp:mailster/form-wrapper -->',
	)
);

register_block_pattern(
	'mailster-form/pattern-02',
	array(
		'title'         => __( 'Pattern 02', 'mailster' ),
		'categories'    => array( 'featured', 'mailster-forms' ),
		'viewportWidth' => 962,
		'inserter'      => false,
		'postTypes'     => array( 'mailster-form' ),
		'keywords'      => array( 'mailster-form' ),
		'content'       => '<!-- wp:mailster/form-wrapper {"css":{"general":".mailster-wrapper{\n    margin-bottom:1em;\n}\n"}} -->
<form method="post" novalidate class="wp-block-mailster-form-wrapper mailster-block-form"><div class="mailster-block-form-inner">

<!-- wp:mailster/field-firstname {"inline":true,"style":{"width":49}} -->
<div class="wp-block-mailster-field-firstname mailster-wrapper mailster-wrapper-type-text mailster-wrapper-inline" style="width:49%"><input name="firstname" type="text" aria-required="false" aria-label="' . esc_html__( 'First Name', 'mailster' ) . '" spellcheck="false" value="" class="input" autocomplete="given-name" placeholder=" "/><label class="mailster-label">' . esc_html__( 'First Name', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-firstname -->

<!-- wp:mailster/field-lastname {"inline":true,"style":{"width":49}} -->
<div class="wp-block-mailster-field-lastname mailster-wrapper mailster-wrapper-type-text mailster-wrapper-inline" style="width:49%"><input name="lastname" type="text" aria-required="false" aria-label="' . esc_attr__( 'Last Name', 'mailster' ) . '" spellcheck="false" value="" class="input" autocomplete="family-name" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Last Name', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-lastname -->

<!-- wp:mailster/field-email {"inline":true} -->
<div class="wp-block-mailster-field-email mailster-wrapper mailster-wrapper-required mailster-wrapper-type-email mailster-wrapper-inline mailster-wrapper-asterisk"><input name="email" type="email" aria-required="true" aria-label="' . esc_attr__( 'Email', 'mailster' ) . '" spellcheck="false" required value="" class="input" autocomplete="email" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Email', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-email -->

<!-- wp:mailster/field-submit {"align":"center"} -->
<div class="wp-block-mailster-field-submit mailster-wrapper mailster-wrapper-type-submit mailster-wrapper-align-center wp-block-button"><input name="submit" type="submit" value="' . esc_html__( 'Subscribe now!', 'mailster' ) . '" class="wp-block-button__link submit-button"/></div>
<!-- /wp:mailster/field-submit --></div></form>
<!-- /wp:mailster/form-wrapper -->',
	)
);

register_block_pattern(
	'mailster-form/pattern-03',
	array(
		'title'         => __( 'Pattern 03', 'mailster' ),
		'categories'    => array( 'featured', 'mailster-forms' ),
		'viewportWidth' => 1162,
		'inserter'      => false,
		'postTypes'     => array( 'mailster-form' ),
		'keywords'      => array( 'mailster-form' ),
		'content'       => '<!-- wp:mailster/form-wrapper {"background":{"opacity":100,"fixed":false,"repeat":false,"size":38,"image":"https://static.mailster.co/forms/girl-having-nice-day-at-the-beach-with-watermelon-and-in-fashion-swimsuit.png","position":{"x":0.81,"y":0.15}},"css":{"general":"h2{\n\tfont-size:2em;\n\tfont-family:sans-serif;\n\tmargin-top:0.6em;\n}\n.input{\n\tborder: 0;\n\tborder-bottom-width: 3px;\n\tborder-bottom-style: solid;\n}\n.submit-button{\n\tborder-style:solid;\n}\n.mailster-block-form{\n    outline:3px solid;\n\toutline-offset:-15px;\n}\n.mailster-wrapper{\n\tmargin-bottom:16px;\n}\n.mailster-wrapper-required label.mailster-label::after{\n    display:none\n}\n"},"style":{"borderColor":"#1A1B1F","borderRadius":"0px","spacing":{"padding":{"top":"2.5em","right":"2.5em","bottom":"2.5em","left":"2.5em"}},"color":{"text":"#363636","background":"#ffffff"}}} -->
    <form method="post" novalidate style="color:#363636;background-color:#ffffff;padding-top:2.5em;padding-right:2.5em;padding-bottom:2.5em;padding-left:2.5em" class="wp-block-mailster-form-wrapper mailster-block-form has-text-color has-background has-text-color has-background"><div class="mailster-block-form-inner"><!-- wp:columns {"isStackedOnMobile":false,"style":{"spacing":{"padding":{"top":"0","right":"0","bottom":"0","left":"0"}}}} -->
<div class="wp-block-columns is-not-stacked-on-mobile" style="padding-top:0;padding-right:0;padding-bottom:0;padding-left:0"><!-- wp:column {"width":"66.66%"} -->
<div class="wp-block-column" style="flex-basis:66.66%"><!-- wp:spacer {"height":"18px"} -->
<div style="height:18px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:heading {"style":{"typography":{"letterSpacing":"-0.1rem","fontSize":"45px"}}} -->
<h2 class="wp-block-heading" style="font-size:45px;letter-spacing:-0.1rem">' . esc_html__( 'Here is your 20% discount.', 'maister' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Join our email list and get a special 20% discount!', 'mailster' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:spacer {"height":"33px"} -->
<div style="height:33px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:mailster/field-email {"inline":true} -->
<div class="wp-block-mailster-field-email mailster-wrapper mailster-wrapper-required mailster-wrapper-type-email mailster-wrapper-inline mailster-wrapper-asterisk"><input name="email" type="email" aria-required="true" aria-label="' . esc_attr__( 'Your Email address', 'mailster' ) . '" spellcheck="false" required value="" class="input" autocomplete="email" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Your Email address', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-email -->

<!-- wp:mailster/field-submit {"style":{"backgroundColor":"#ffffff","borderColor":"#1A1B1F","borderWidth":"3px","color":"#1A1B1F","inputColor":"#151515","borderRadius":"0px","typography":{"textTransform":"uppercase","fontSize":"30px"},"spacing":{"padding":{"top":"1em","right":"1em","bottom":"1em","left":"1em"}}},"className":"mailster-wrapper-asterisk"} -->
<div class="wp-block-mailster-field-submit mailster-wrapper mailster-wrapper-type-submit wp-block-button mailster-wrapper-asterisk"><input name="submit" type="submit" style="padding-top:1em;padding-right:1em;padding-bottom:1em;padding-left:1em;font-size:30px;text-transform:uppercase;color:#151515;background-color:#ffffff;border-color:#1A1B1F;border-width:3px;border-radius:0px" value="' . esc_html__( 'Get 20% now', 'mailster' ) . '" class="wp-block-button__link submit-button"/></div>
<!-- /wp:mailster/field-submit -->

<!-- wp:spacer {"height":"13px"} -->
<div style="height:13px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"0.8rem"}}} -->
<p style="font-size:0.8rem">' . esc_html__( 'When you subscribe, you give us permission to send you emails about our products, exclusive promotions, and special events. However, you have the option to withdraw your consent at any point by clicking on the unsubscribe link provided in the emails.', 'mailster' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></form>
<!-- /wp:mailster/form-wrapper -->',
	)
);

register_block_pattern(
	'mailster-form/pattern-04',
	array(
		'title'         => __( 'Pattern 04', 'mailster' ),
		'categories'    => array( 'featured', 'mailster-forms' ),
		'viewportWidth' => 962,
		'inserter'      => false,
		'postTypes'     => array( 'mailster-form' ),
		'keywords'      => array( 'mailster-form' ),
		'content'       => '<!-- wp:mailster/form-wrapper {"background":{"opacity":40,"fixed":false,"repeat":false,"size":"cover","fullscreen":false,"position":{"x":0.67,"y":0.45},"image":"https://static.mailster.co/forms/roasted-coffee.jpg"},"style":{"color":{"background":"#332b2b"},"spacing":{"padding":{"top":"4em","right":"4em","bottom":"4em","left":"4em"}}}} -->
    <form method="post" novalidate style="background-color:#332b2b;padding-top:4em;padding-right:4em;padding-bottom:4em;padding-left:4em" class="wp-block-mailster-form-wrapper mailster-block-form has-background has-background"><div class="mailster-block-form-inner"><!-- wp:spacer -->
<div style="height:100px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontStyle":"normal","fontWeight":"600","fontSize":"5rem"},"color":{"text":"#f4f4f4"}}} -->
<h2 class="wp-block-heading has-text-align-center has-text-color" style="color:#f4f4f4;font-size:5rem;font-style:normal;font-weight:600">' . esc_html__( 'Take 20%', 'maister' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"color":{"text":"#f4f4f4"}}} -->
<p class="has-text-align-center has-text-color" style="color:#f4f4f4">' . esc_html__( 'Sign up for our email and save on your first order!', 'mailster' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:spacer {"height":"40px"} -->
<div style="height:40px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:mailster/field-email {"align":"center","justify":"left","labelAlign":"center","inline":true,"style":{"width":60}} -->
<div class="wp-block-mailster-field-email mailster-wrapper mailster-wrapper-required mailster-wrapper-type-email mailster-wrapper-align-center mailster-wrapper-justify-left mailster-wrapper-label-align-center mailster-wrapper-inline mailster-wrapper-asterisk" style="width:60%"><input name="email" type="email" aria-required="true" aria-label="' . esc_attr__( 'Your Email address', 'mailster' ) . '" spellcheck="false" required value="" class="input" autocomplete="email" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Your Email address', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-email -->

<!-- wp:mailster/field-submit {"style":{"width":37}} -->
<div class="wp-block-mailster-field-submit mailster-wrapper mailster-wrapper-type-submit wp-block-button" style="width:37%"><input name="submit" type="submit" value="' . esc_html__( 'Get 20% now', 'mailster' ) . '" class="wp-block-button__link submit-button"/></div>
<!-- /wp:mailster/field-submit -->

<!-- wp:spacer -->
<div style="height:100px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer --></div></form>
<!-- /wp:mailster/form-wrapper -->',
	)
);

register_block_pattern(
	'mailster-form/pattern-05',
	array(
		'title'         => __( 'Pattern 05', 'mailster' ),
		'categories'    => array( 'featured', 'mailster-forms' ),
		'viewportWidth' => 962,
		'inserter'      => false,
		'postTypes'     => array( 'mailster-form' ),
		'keywords'      => array( 'mailster-form' ),
		'content'       => '<!-- wp:mailster/form-wrapper {"background":{"opacity":100,"scale":1,"fixed":false,"repeat":true,"size":62,"position":{"x":"0.28","y":"0.26"}},"css":{"general":".mailster-block-form-inner{\n\tbackground-color:#ffffffaa;\n\tpadding:1em;\t\n}\n.mailster-wrapper{\n    margin-top:1em;\n}\n.mailster-block-form{\n   background-color: #D7C49E;\nbackground-image: url(\u0022data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'100%25\'%3E%3Cdefs%3E%3ClinearGradient id=\'a\' gradientUnits=\'userSpaceOnUse\' x1=\'0\' x2=\'0\' y1=\'0\' y2=\'100%25\' gradientTransform=\'rotate(172,799,620)\'%3E%3Cstop offset=\'0\' stop-color=\'%23D7C49E\'/%3E%3Cstop offset=\'1\' stop-color=\'%23343148\'/%3E%3C/linearGradient%3E%3Cpattern patternUnits=\'userSpaceOnUse\' id=\'b\' width=\'351\' height=\'292.5\' x=\'0\' y=\'0\' viewBox=\'0 0 1080 900\'%3E%3Cg fill-opacity=\'0.1\'%3E%3Cpolygon fill=\'%23444\' points=\'90 150 0 300 180 300\'/%3E%3Cpolygon points=\'90 150 180 0 0 0\'/%3E%3Cpolygon fill=\'%23AAA\' points=\'270 150 360 0 180 0\'/%3E%3Cpolygon fill=\'%23DDD\' points=\'450 150 360 300 540 300\'/%3E%3Cpolygon fill=\'%23999\' points=\'450 150 540 0 360 0\'/%3E%3Cpolygon points=\'630 150 540 300 720 300\'/%3E%3Cpolygon fill=\'%23DDD\' points=\'630 150 720 0 540 0\'/%3E%3Cpolygon fill=\'%23444\' points=\'810 150 720 300 900 300\'/%3E%3Cpolygon fill=\'%23FFF\' points=\'810 150 900 0 720 0\'/%3E%3Cpolygon fill=\'%23DDD\' points=\'990 150 900 300 1080 300\'/%3E%3Cpolygon fill=\'%23444\' points=\'990 150 1080 0 900 0\'/%3E%3Cpolygon fill=\'%23DDD\' points=\'90 450 0 600 180 600\'/%3E%3Cpolygon points=\'90 450 180 300 0 300\'/%3E%3Cpolygon fill=\'%23666\' points=\'270 450 180 600 360 600\'/%3E%3Cpolygon fill=\'%23AAA\' points=\'270 450 360 300 180 300\'/%3E%3Cpolygon fill=\'%23DDD\' points=\'450 450 360 600 540 600\'/%3E%3Cpolygon fill=\'%23999\' points=\'450 450 540 300 360 300\'/%3E%3Cpolygon fill=\'%23999\' points=\'630 450 540 600 720 600\'/%3E%3Cpolygon fill=\'%23FFF\' points=\'630 450 720 300 540 300\'/%3E%3Cpolygon points=\'810 450 720 600 900 600\'/%3E%3Cpolygon fill=\'%23DDD\' points=\'810 450 900 300 720 300\'/%3E%3Cpolygon fill=\'%23AAA\' points=\'990 450 900 600 1080 600\'/%3E%3Cpolygon fill=\'%23444\' points=\'990 450 1080 300 900 300\'/%3E%3Cpolygon fill=\'%23222\' points=\'90 750 0 900 180 900\'/%3E%3Cpolygon points=\'270 750 180 900 360 900\'/%3E%3Cpolygon fill=\'%23DDD\' points=\'270 750 360 600 180 600\'/%3E%3Cpolygon points=\'450 750 540 600 360 600\'/%3E%3Cpolygon points=\'630 750 540 900 720 900\'/%3E%3Cpolygon fill=\'%23444\' points=\'630 750 720 600 540 600\'/%3E%3Cpolygon fill=\'%23AAA\' points=\'810 750 720 900 900 900\'/%3E%3Cpolygon fill=\'%23666\' points=\'810 750 900 600 720 600\'/%3E%3Cpolygon fill=\'%23999\' points=\'990 750 900 900 1080 900\'/%3E%3Cpolygon fill=\'%23999\' points=\'180 0 90 150 270 150\'/%3E%3Cpolygon fill=\'%23444\' points=\'360 0 270 150 450 150\'/%3E%3Cpolygon fill=\'%23FFF\' points=\'540 0 450 150 630 150\'/%3E%3Cpolygon points=\'900 0 810 150 990 150\'/%3E%3Cpolygon fill=\'%23222\' points=\'0 300 -90 450 90 450\'/%3E%3Cpolygon fill=\'%23FFF\' points=\'0 300 90 150 -90 150\'/%3E%3Cpolygon fill=\'%23FFF\' points=\'180 300 90 450 270 450\'/%3E%3Cpolygon fill=\'%23666\' points=\'180 300 270 150 90 150\'/%3E%3Cpolygon fill=\'%23222\' points=\'360 300 270 450 450 450\'/%3E%3Cpolygon fill=\'%23FFF\' points=\'360 300 450 150 270 150\'/%3E%3Cpolygon fill=\'%23444\' points=\'540 300 450 450 630 450\'/%3E%3Cpolygon fill=\'%23222\' points=\'540 300 630 150 450 150\'/%3E%3Cpolygon fill=\'%23AAA\' points=\'720 300 630 450 810 450\'/%3E%3Cpolygon fill=\'%23666\' points=\'720 300 810 150 630 150\'/%3E%3Cpolygon fill=\'%23FFF\' points=\'900 300 810 450 990 450\'/%3E%3Cpolygon fill=\'%23999\' points=\'900 300 990 150 810 150\'/%3E%3Cpolygon points=\'0 600 -90 750 90 750\'/%3E%3Cpolygon fill=\'%23666\' points=\'0 600 90 450 -90 450\'/%3E%3Cpolygon fill=\'%23AAA\' points=\'180 600 90 750 270 750\'/%3E%3Cpolygon fill=\'%23444\' points=\'180 600 270 450 90 450\'/%3E%3Cpolygon fill=\'%23444\' points=\'360 600 270 750 450 750\'/%3E%3Cpolygon fill=\'%23999\' points=\'360 600 450 450 270 450\'/%3E%3Cpolygon fill=\'%23666\' points=\'540 600 630 450 450 450\'/%3E%3Cpolygon fill=\'%23222\' points=\'720 600 630 750 810 750\'/%3E%3Cpolygon fill=\'%23FFF\' points=\'900 600 810 750 990 750\'/%3E%3Cpolygon fill=\'%23222\' points=\'900 600 990 450 810 450\'/%3E%3Cpolygon fill=\'%23DDD\' points=\'0 900 90 750 -90 750\'/%3E%3Cpolygon fill=\'%23444\' points=\'180 900 270 750 90 750\'/%3E%3Cpolygon fill=\'%23FFF\' points=\'360 900 450 750 270 750\'/%3E%3Cpolygon fill=\'%23AAA\' points=\'540 900 630 750 450 750\'/%3E%3Cpolygon fill=\'%23FFF\' points=\'720 900 810 750 630 750\'/%3E%3Cpolygon fill=\'%23222\' points=\'900 900 990 750 810 750\'/%3E%3Cpolygon fill=\'%23222\' points=\'1080 300 990 450 1170 450\'/%3E%3Cpolygon fill=\'%23FFF\' points=\'1080 300 1170 150 990 150\'/%3E%3Cpolygon points=\'1080 600 990 750 1170 750\'/%3E%3Cpolygon fill=\'%23666\' points=\'1080 600 1170 450 990 450\'/%3E%3Cpolygon fill=\'%23DDD\' points=\'1080 900 1170 750 990 750\'/%3E%3C/g%3E%3C/pattern%3E%3C/defs%3E%3Crect x=\'0\' y=\'0\' fill=\'url(%23a)\' width=\'100%25\' height=\'100%25\'/%3E%3Crect x=\'0\' y=\'0\' fill=\'url(%23b)\' width=\'100%25\' height=\'100%25\'/%3E%3C/svg%3E\u0022);\nbackground-attachment: fixed;\nbackground-size: cover;\n}\n","tablet":"","mobile":""},"style":{"spacing":{"padding":{"top":"2em","right":"2em","bottom":"2em","left":"2em"}}},"className":"has-background"} -->
<form method="post" novalidate style="padding-top:2em;padding-right:2em;padding-bottom:2em;padding-left:2em" class="wp-block-mailster-form-wrapper mailster-block-form has-background"><div class="mailster-block-form-inner"><!-- wp:heading {"textAlign":"center","align":"full","style":{"typography":{"fontStyle":"normal","fontWeight":"900"}}} -->
<h2 class="wp-block-heading alignfull has-text-align-center" style="font-style:normal;font-weight:900">' . esc_html__( 'Hi and Welcome!', 'maister' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Enter your email address to join our newsletter.', 'mailster' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:mailster/field-email {"style":{"width":100}} -->
<div class="wp-block-mailster-field-email mailster-wrapper mailster-wrapper-required mailster-wrapper-type-email mailster-wrapper-asterisk" style="width:100%"><label class="mailster-label">' . esc_html__( 'Email', 'mailster' ) . '</label><input name="email" type="email" aria-required="true" aria-label="' . esc_attr__( 'Email', 'mailster' ) . '" spellcheck="false" required value="" class="input" autocomplete="email" placeholder=" "/></div>
<!-- /wp:mailster/field-email -->

<!-- wp:mailster/field-firstname {"style":{"width":49},"className":"mailster-wrapper-asterisk"} -->
<div class="wp-block-mailster-field-firstname mailster-wrapper mailster-wrapper-type-text mailster-wrapper-asterisk" style="width:49%"><label class="mailster-label">' . esc_html__( 'First Name', 'mailster' ) . '</label><input name="firstname" type="text" aria-required="false" aria-label="' . esc_html__( 'First Name', 'mailster' ) . '" spellcheck="false" value="" class="input" autocomplete="given-name" placeholder=" "/></div>
<!-- /wp:mailster/field-firstname -->

<!-- wp:mailster/field-lastname {"style":{"width":49}} -->
<div class="wp-block-mailster-field-lastname mailster-wrapper mailster-wrapper-type-text" style="width:49%"><label class="mailster-label">' . esc_html__( 'Last Name', 'mailster' ) . '</label><input name="lastname" type="text" aria-required="false" aria-label="' . esc_attr__( 'Last Name', 'mailster' ) . '" spellcheck="false" value="" class="input" autocomplete="family-name" placeholder=" "/></div>
<!-- /wp:mailster/field-lastname -->

<!-- wp:mailster/field-submit {"style":{"width":100},"className":"mailster-wrapper-asterisk"} -->
<div class="wp-block-mailster-field-submit mailster-wrapper mailster-wrapper-type-submit wp-block-button mailster-wrapper-asterisk" style="width:100%"><input name="submit" type="submit" value="' . esc_attr__( 'Subscribe', 'mailster' ) . '" class="wp-block-button__link submit-button"/></div>
<!-- /wp:mailster/field-submit --></div></form>
<!-- /wp:mailster/form-wrapper -->',
	)
);

register_block_pattern(
	'mailster-form/pattern-06',
	array(
		'title'         => __( 'Pattern 06', 'mailster' ),
		'categories'    => array( 'featured', 'mailster-forms' ),
		'viewportWidth' => 962,
		'inserter'      => false,
		'postTypes'     => array( 'mailster-form' ),
		'keywords'      => array( 'mailster-form' ),
		'content'       => '<!-- wp:mailster/form-wrapper {"background":{"opacity":20,"fixed":false,"repeat":false,"size":"cover","image":"https://static.mailster.co/forms/forest-in-winter.jpg","position":{"x":0.5,"y":0.5},"fullscreen":false},"css":{"general":".mailster-wrapper{\n    margin-top:1em;\n\tmargin-bottom:0.5em;\n}\n","tablet":"","mobile":""},"style":{"spacing":{"padding":{"top":"2em","right":"2em","bottom":"2em","left":"2em"}},"color":{"gradient":"radial-gradient( circle farthest-corner at 10% 20%,  rgba(255,94,247,1) 17.8%, rgba(2,245,255,1) 100.2% )"},"labelColor":"#000000","backgroundColor":"#ffffff"},"textColor":"white"} -->
    <form method="post" novalidate style="background:radial-gradient( circle farthest-corner at 10% 20%,  rgba(255,94,247,1) 17.8%, rgba(2,245,255,1) 100.2% );padding-top:2em;padding-right:2em;padding-bottom:2em;padding-left:2em" class="wp-block-mailster-form-wrapper mailster-block-form has-white-color has-text-color has-background has-white-color has-text-color has-background"><div class="mailster-block-form-inner"><!-- wp:heading {"textAlign":"left","align":"full","style":{"typography":{"fontStyle":"normal","fontWeight":"900"},"color":{"text":"#ffffff"}}} -->
<h2 class="wp-block-heading alignfull has-text-align-left has-text-color" style="color:#ffffff;font-style:normal;font-weight:900">' . esc_html__( 'Subscribe to our Newsletter!', 'maister' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"color":{"text":"#ffffff"}}} -->
<p class="has-text-color" style="color:#ffffff">' . esc_html__( 'Stay up to date with the latest news and relevant updates from us.', 'mailster' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:mailster/field-email {"inline":true} -->
<div class="wp-block-mailster-field-email mailster-wrapper mailster-wrapper-required mailster-wrapper-type-email mailster-wrapper-inline mailster-wrapper-asterisk"><input name="email" type="email" aria-required="true" aria-label="Enter your email address" spellcheck="false" required value="" class="input" autocomplete="email" placeholder=" "/><label class="mailster-label">Enter your email address</label></div>
<!-- /wp:mailster/field-email -->

<!-- wp:mailster/field-submit {"style":{"width":33}} -->
<div class="wp-block-mailster-field-submit mailster-wrapper mailster-wrapper-type-submit wp-block-button" style="width:33%"><input name="submit" type="submit" value="' . esc_attr__( 'Subscribe', 'mailster' ) . '" class="wp-block-button__link submit-button"/></div>
<!-- /wp:mailster/field-submit --></div></form>
<!-- /wp:mailster/form-wrapper -->',
	)
);

register_block_pattern(
	'mailster-form/pattern-07',
	array(
		'title'         => __( 'Pattern 07', 'mailster' ),
		'categories'    => array( 'featured', 'mailster-forms' ),
		'viewportWidth' => 962,
		'inserter'      => false,
		'postTypes'     => array( 'mailster-form' ),
		'keywords'      => array( 'mailster-form' ),
		'content'       => '<!-- wp:mailster/form-wrapper {"css":{"general":".mailster-wrapper{\n\tmargin-top:1em;\n}","tablet":"","mobile":""},"style":{"color":{"background":"#ffffff"}}} -->
<form method="post" novalidate style="background-color:#ffffff" class="wp-block-mailster-form-wrapper mailster-block-form has-background has-background"><div class="mailster-block-form-inner"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"><!-- wp:cover {"url":"https://static.mailster.co/forms/young-african-american-woman-sitting-on-steps.jpg","dimRatio":0} -->
<div class="wp-block-cover is-light"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="https://static.mailster.co/forms/young-african-american-woman-sitting-on-steps.jpg" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","placeholder":"Write title…","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size"></p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column -->

<!-- wp:column {"verticalAlignment":"center","width":"66.67%","style":{"spacing":{"padding":{"top":"2rem","right":"2rem","bottom":"2rem","left":"2rem"}}}} -->
<div class="wp-block-column is-vertically-aligned-center" style="padding-top:2rem;padding-right:2rem;padding-bottom:2rem;padding-left:2rem;flex-basis:66.67%"><!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"900"}}} -->
<h2 class="wp-block-heading" style="font-style:normal;font-weight:900">' . esc_html__( 'Subscribe!', 'maister' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . sprintf( esc_html__( 'Enter your email address to join %s others.', 'mailster' ), '[newsletter_subscribers round=100]+' ) . '</p>
<!-- /wp:paragraph -->


<!-- wp:mailster/field-firstname {"inline":true} -->
<div class="wp-block-mailster-field-firstname mailster-wrapper mailster-wrapper-type-text mailster-wrapper-inline"><input name="firstname" type="text" aria-required="false" aria-label="' . esc_attr__( 'Name', 'mailster' ) . '" spellcheck="false" value="" class="input" autocomplete="given-name" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Name', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-firstname -->

<!-- wp:mailster/field-email {"inline":true} -->
<div class="wp-block-mailster-field-email mailster-wrapper mailster-wrapper-required mailster-wrapper-type-email mailster-wrapper-inline mailster-wrapper-asterisk"><input name="email" type="email" aria-required="true" aria-label="' . esc_attr__( 'Email', 'mailster' ) . '" spellcheck="false" required value="" class="input" autocomplete="email" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Email', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-email -->

<!-- wp:mailster/field-submit -->
<div class="wp-block-mailster-field-submit mailster-wrapper mailster-wrapper-type-submit wp-block-button"><input name="submit" type="submit" value="' . esc_attr__( 'Subscribe', 'mailster' ) . '" class="wp-block-button__link submit-button"/></div>
<!-- /wp:mailster/field-submit -->

<!-- wp:mailster/close {"align":"center","style":{"typography":{"fontSize":"15px"}}} -->
<div class="wp-block-mailster-close mailster-wrapper mailster-wrapper-label-align-center" style="font-size:15px"><a href="" class="mailster-block-form-inner-close" aria-label="I\'m not interested" tabindex="0">I\'m not interested</a></div>
<!-- /wp:mailster/close --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></form>
<!-- /wp:mailster/form-wrapper -->',
	)
);

register_block_pattern(
	'mailster-form/pattern-08',
	array(
		'title'         => __( 'Pattern 08', 'mailster' ),
		'categories'    => array( 'featured', 'mailster-forms' ),
		'viewportWidth' => 962,
		'inserter'      => false,
		'postTypes'     => array( 'mailster-form' ),
		'keywords'      => array( 'mailster-form' ),
		'content'       => '<!-- wp:mailster/form-wrapper {"background":{"opacity":100,"fixed":false,"repeat":false,"size":"contain","image":"https://static.mailster.co/forms/dazzled-shocked-individual-dressed-in-casual-shirt.png","position":{"x":"0.24","y":"0.50"}},"css":{"general":".mailster-wrapper{\n    margin-top:1em\n}\n","tablet":"","mobile":""},"style":{"color":{"background":"#edc1c1","text":"#222222"}}} -->
    <form method="post" novalidate style="color:#222222;background-color:#edc1c1" class="wp-block-mailster-form-wrapper mailster-block-form has-text-color has-background has-text-color has-background"><div class="mailster-block-form-inner"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"></div>
<!-- /wp:column -->

<!-- wp:column {"width":"66.67%","style":{"spacing":{"padding":{"top":"2rem","right":"2rem","bottom":"2rem","left":"2rem"}}}} -->
<div class="wp-block-column" style="padding-top:2rem;padding-right:2rem;padding-bottom:2rem;padding-left:2rem;flex-basis:66.67%"><!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"900"}}} -->
<h2 class="wp-block-heading" style="font-style:normal;font-weight:900">' . esc_html__( 'Subscribe!', 'maister' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . sprintf( esc_html__( 'Enter your email address to join %s others.', 'mailster' ), '[newsletter_subscribers round=100]+' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:mailster/field-firstname {"inline":true,"style":{"width":49}} -->
<div class="wp-block-mailster-field-firstname mailster-wrapper mailster-wrapper-type-text mailster-wrapper-inline" style="width:49%"><input name="firstname" type="text" aria-required="false" aria-label="' . esc_attr__( 'Name', 'mailster' ) . '" spellcheck="false" value="" class="input" autocomplete="given-name" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Name', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-firstname -->

<!-- wp:mailster/field-lastname {"inline":true,"style":{"width":49}} -->
<div class="wp-block-mailster-field-lastname mailster-wrapper mailster-wrapper-type-text mailster-wrapper-inline" style="width:49%"><input name="lastname" type="text" aria-required="false" aria-label="' . esc_attr__( 'Last Name', 'mailster' ) . '" spellcheck="false" value="" class="input" autocomplete="family-name" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Last Name', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-lastname -->

<!-- wp:mailster/field-email {"inline":true} -->
<div class="wp-block-mailster-field-email mailster-wrapper mailster-wrapper-required mailster-wrapper-type-email mailster-wrapper-inline mailster-wrapper-asterisk"><input name="email" type="email" aria-required="true" aria-label="' . esc_attr__( 'Email', 'mailster' ) . '" spellcheck="false" required value="" class="input" autocomplete="email" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Email', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-email -->

<!-- wp:mailster/field-submit -->
<div class="wp-block-mailster-field-submit mailster-wrapper mailster-wrapper-type-submit wp-block-button"><input name="submit" type="submit" value="' . esc_attr__( 'Subscribe', 'mailster' ) . '" class="wp-block-button__link submit-button"/></div>
<!-- /wp:mailster/field-submit --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></form>
<!-- /wp:mailster/form-wrapper -->',
	)
);

register_block_pattern(
	'mailster-form/pattern-09',
	array(
		'title'         => __( 'Pattern 09', 'mailster' ),
		'categories'    => array( 'featured', 'mailster-forms' ),
		'viewportWidth' => 962,
		'inserter'      => false,
		'postTypes'     => array( 'mailster-form' ),
		'keywords'      => array( 'mailster-form' ),
		'content'       => '<!-- wp:mailster/form-wrapper {"background":{"opacity":100,"fixed":false,"repeat":false,"size":"contain","image":"https://static.mailster.co/forms/futuristic-portrait-of-topless-african-american.png","position":{"x":"0.25","y":"0.49"}},"css":{"general":".mailster-wrapper .input, .mailster-wrapper .submit-button{\n    min-height:50px;\n\tbox-sizing: border-box;\n}\n.mailster-wrapper{\n    margin-top:1em;\n}\n","tablet":"","mobile":""},"style":{"color":{"text":"#ededed","gradient":"linear-gradient(135deg,rgb(186,53,119) 0%,rgb(22,9,26) 49%,rgb(56,134,172) 94%)"},"backgroundColor":"#252424","inputColor":"#eaeaea","labelColor":"#ebebeb","spacing":{"padding":{"top":"2vw","right":"2vw","bottom":"2vw","left":"2vw"}},"borderWidth":"0px","borderRadius":"0px"}} -->
    <form method="post" novalidate style="color:#ededed;background:linear-gradient(135deg,rgb(186,53,119) 0%,rgb(22,9,26) 49%,rgb(56,134,172) 94%);padding-top:2vw;padding-right:2vw;padding-bottom:2vw;padding-left:2vw" class="wp-block-mailster-form-wrapper mailster-block-form has-text-color has-background has-text-color has-background"><div class="mailster-block-form-inner"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"width":"33.33%"} -->
<div class="wp-block-column" style="flex-basis:33.33%"></div>
<!-- /wp:column -->

<!-- wp:column {"width":"66.67%","style":{"spacing":{"padding":{"top":"2rem","right":"2rem","bottom":"2rem","left":"2rem"}}}} -->
<div class="wp-block-column" style="padding-top:2rem;padding-right:2rem;padding-bottom:2rem;padding-left:2rem;flex-basis:66.67%"><!-- wp:heading {"style":{"color":{"text":"#fefefe"},"typography":{"fontStyle":"normal","fontWeight":"900"}}} -->
<h2 class="wp-block-heading has-text-color" style="color:#fefefe;font-style:normal;font-weight:900">' . esc_html__( 'Subscribe!', 'maister' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"color":{"text":"#fdfdfd"}}} -->
<p class="has-text-color" style="color:#fdfdfd">' . sprintf( esc_html__( 'Enter your email address to join %s others.', 'mailster' ), '[newsletter_subscribers round=100]+' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:spacer {"height":"22px"} -->
<div style="height:22px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:mailster/field-firstname {"inline":true,"style":{"width":49}} -->
<div class="wp-block-mailster-field-firstname mailster-wrapper mailster-wrapper-type-text mailster-wrapper-inline" style="width:49%"><input name="firstname" type="text" aria-required="false" aria-label="' . esc_attr__( 'Name', 'mailster' ) . '" spellcheck="false" value="" class="input" autocomplete="given-name" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Name', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-firstname -->

<!-- wp:mailster/field-lastname {"inline":true,"style":{"width":49}} -->
<div class="wp-block-mailster-field-lastname mailster-wrapper mailster-wrapper-type-text mailster-wrapper-inline" style="width:49%"><input name="lastname" type="text" aria-required="false" aria-label="' . esc_attr__( 'Last Name', 'mailster' ) . '" spellcheck="false" value="" class="input" autocomplete="family-name" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Last Name', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-lastname -->

<!-- wp:mailster/field-email {"inline":true} -->
<div class="wp-block-mailster-field-email mailster-wrapper mailster-wrapper-required mailster-wrapper-type-email mailster-wrapper-inline mailster-wrapper-asterisk"><input name="email" type="email" aria-required="true" aria-label="' . esc_attr__( 'Email', 'mailster' ) . '" spellcheck="false" required value="" class="input" autocomplete="email" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Email', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-email -->

<!-- wp:mailster/field-submit {"style":{"backgroundColor":"#ce19b3","inputColor":"#f8f8f8","borderWidth":"0px","borderRadius":"0px"}} -->
<div class="wp-block-mailster-field-submit mailster-wrapper mailster-wrapper-type-submit wp-block-button"><input name="submit" type="submit" style="color:#f8f8f8;background-color:#ce19b3;border-width:0px;border-radius:0px" value="' . esc_attr__( 'Subscribe', 'mailster' ) . '" class="wp-block-button__link submit-button"/></div>
<!-- /wp:mailster/field-submit --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></form>
<!-- /wp:mailster/form-wrapper -->',
	)
);


register_block_pattern(
	'mailster-form/pattern-10',
	array(
		'title'         => __( 'Pattern 10', 'mailster' ),
		'categories'    => array( 'featured', 'mailster-forms' ),
		'viewportWidth' => 962,
		'inserter'      => false,
		'postTypes'     => array( 'mailster-form' ),
		'keywords'      => array( 'mailster-form' ),
		'content'       => '<!-- wp:mailster/form-wrapper {"css":{"general":"footer.wp-block-group{\n\tpadding:1em;\n}\nfooter .input{\t\n\theight:70px\n}\nfooter .submit-button{\t\n\theight:70px\n}","tablet":"","mobile":".mailster-wrapper{\n    margin-top:1em;\n}\n"},"style":{"spacing":{"padding":{}},"color":{"gradient":"linear-gradient(160deg,rgb(235,51,73) 0%,rgb(244,92,67) 100%)","text":"#242b35"},"borderWidth":"0px"}} -->
<form method="post" novalidate style="color:#242b35;background:linear-gradient(160deg,rgb(235,51,73) 0%,rgb(244,92,67) 100%)" class="wp-block-mailster-form-wrapper mailster-block-form has-text-color has-background has-text-color has-background"><div class="mailster-block-form-inner"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"verticalAlignment":"center","width":"60%","style":{"spacing":{"padding":{"top":"2em","right":"2em","bottom":"2em","left":"2em"}}}} -->
<div class="wp-block-column is-vertically-aligned-center" style="padding-top:2em;padding-right:2em;padding-bottom:2em;padding-left:2em;flex-basis:60%"><!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"900"},"color":{"text":"#ffffff"}}} -->
<h2 class="wp-block-heading has-text-color" style="color:#ffffff;font-style:normal;font-weight:900">' . esc_html__( 'Subscribe!', 'maister' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"style":{"color":{"text":"#ffffff"}}} -->
<p class="has-text-color" style="color:#ffffff">' . esc_html__( 'Enter your email address to join our newsletter.', 'mailster' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"40%"} -->
<div class="wp-block-column" style="flex-basis:40%"><!-- wp:cover {"url":"https://static.mailster.co/forms/women-technology-girl.jpg","id":268,"dimRatio":0,"focalPoint":{"x":"0.49","y":"0.53"},"minHeight":248,"minHeightUnit":"px","isDark":false} -->
<div class="wp-block-cover is-light" style="min-height:248px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><img class="wp-block-cover__image-background wp-image-268" alt="" src="https://static.mailster.co/forms/women-technology-girl.jpg" style="object-position:49% 53%" data-object-fit="cover" data-object-position="49% 53%"/><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","placeholder":"Write title…","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size"></p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column --></div>
<!-- /wp:columns -->

<!-- wp:group {"tagName":"footer","style":{"color":{"background":"#24a878"}}} -->
<footer class="wp-block-group has-background" style="background-color:#24a878">

<!-- wp:mailster/field-firstname {"inline":true,"style":{"width":35}} -->
<div class="wp-block-mailster-field-firstname mailster-wrapper mailster-wrapper-type-text mailster-wrapper-inline" style="width:35%"><input name="firstname" type="text" aria-required="false" aria-label="' . esc_attr__( 'Name', 'mailster' ) . '" spellcheck="false" value="" class="input" autocomplete="given-name" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Name', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-firstname -->

<!-- wp:mailster/field-email {"inline":true,"style":{"width":35}} -->
<div class="wp-block-mailster-field-email mailster-wrapper mailster-wrapper-required mailster-wrapper-type-email mailster-wrapper-inline mailster-wrapper-asterisk" style="width:35%"><input name="email" type="email" aria-required="true" aria-label="' . esc_attr__( 'Email', 'mailster' ) . '" spellcheck="false" required value="" class="input" autocomplete="email" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Email', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-email -->

<!-- wp:mailster/field-submit {"style":{"width":28,"borderWidth":"0px"}} -->
<div class="wp-block-mailster-field-submit mailster-wrapper mailster-wrapper-type-submit wp-block-button" style="width:28%"><input name="submit" type="submit" style="border-width:0px" value="' . esc_attr__( 'Subscribe', 'mailster' ) . '" class="wp-block-button__link submit-button"/></div>
<!-- /wp:mailster/field-submit --></footer>
<!-- /wp:group --></div></form>
<!-- /wp:mailster/form-wrapper -->',
	)
);

register_block_pattern(
	'mailster-form/pattern-11',
	array(
		'title'         => __( 'Pattern 11', 'mailster' ),
		'categories'    => array( 'featured', 'mailster-forms' ),
		'viewportWidth' => 1262,
		'inserter'      => false,
		'postTypes'     => array( 'mailster-form' ),
		'keywords'      => array( 'mailster-form' ),
		'content'       => '<!-- wp:mailster/form-wrapper {"css":{"general":".mailster-wrapper{\n\tmargin-top:1em;\n}","tablet":"","mobile":""},"style":{"color":{"background":"#ffffff"}}} -->
<form method="post" novalidate style="background-color:#ffffff" class="wp-block-mailster-form-wrapper mailster-block-form has-background has-background"><div class="mailster-block-form-inner"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"verticalAlignment":"center","width":"50%","style":{"spacing":{"padding":{"top":"2rem","right":"2rem","bottom":"2rem","left":"2rem"}}}} -->
<div class="wp-block-column is-vertically-aligned-center" style="padding-top:2rem;padding-right:2rem;padding-bottom:2rem;padding-left:2rem;flex-basis:50%"><!-- wp:site-logo {"width":100,"isLink":false} /-->

<!-- wp:spacer {"height":"26px"} -->
<div style="height:26px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"400"}}} -->
<h2 class="wp-block-heading" style="font-style:normal;font-weight:400">' . esc_html__( 'Sign up for our newsletter and receive 10% off your first order!', 'maister' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:spacer {"height":"50px"} -->
<div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:mailster/field-firstname -->
<div class="wp-block-mailster-field-firstname mailster-wrapper mailster-wrapper-required mailster-wrapper-type-text mailster-wrapper-asterisk"><label class="mailster-label">' . esc_html__( 'Name', 'mailster' ) . '</label><input name="firstname" type="text" aria-required="true" aria-label="' . esc_attr__( 'Name', 'mailster' ) . '" spellcheck="false" required value="" class="input" autocomplete="given-name" placeholder=" "/></div>
<!-- /wp:mailster/field-firstname -->

<!-- wp:mailster/field-email -->
<div class="wp-block-mailster-field-email mailster-wrapper mailster-wrapper-required mailster-wrapper-type-email mailster-wrapper-asterisk"><label class="mailster-label">' . esc_html__( 'Email', 'mailster' ) . '</label><input name="email" type="email" aria-required="true" aria-label="' . esc_attr__( 'Email', 'mailster' ) . '" spellcheck="false" required value="" class="input" autocomplete="email" placeholder=" "/></div>
<!-- /wp:mailster/field-email -->

<!-- wp:mailster/field-submit -->
<div class="wp-block-mailster-field-submit mailster-wrapper mailster-wrapper-type-submit wp-block-button"><input name="submit" type="submit" value="Sign up" class="wp-block-button__link submit-button"/></div>
<!-- /wp:mailster/field-submit -->

<!-- wp:spacer {"height":"50px"} -->
<div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"0.8rem"}}} -->
<p style="font-size:0.8rem">' . esc_html__( 'When you subscribe, you give us permission to send you emails about our products, exclusive promotions, and special events. However, you have the option to withdraw your consent at any point by clicking on the unsubscribe link provided in the emails.', 'mailster' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%","style":{"color":{"background":"#FFD4EF"}}} -->
<div class="wp-block-column has-background" style="background-color:#FFD4EF;flex-basis:50%"><!-- wp:cover {"url":"https://static.mailster.co/forms/fashion-lady.png","dimRatio":0,"contentPosition":"center center","isDark":false} -->
<div class="wp-block-cover is-light"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="https://static.mailster.co/forms/fashion-lady.png" data-object-fit="cover"/><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","placeholder":"Write title…","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size"></p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></form>
<!-- /wp:mailster/form-wrapper -->',
	)
);

register_block_pattern(
	'mailster-form/pattern-12',
	array(
		'title'         => __( 'Pattern 12', 'mailster' ),
		'categories'    => array( 'featured', 'mailster-forms' ),
		'viewportWidth' => 1262,
		'inserter'      => false,
		'postTypes'     => array( 'mailster-form' ),
		'keywords'      => array( 'mailster-form' ),
		'content'       => '<!-- wp:mailster/form-wrapper {"css":{"general":".mailster-wrapper{\n\tmargin-top:1em;\n}","tablet":"","mobile":""},"style":{"color":{"background":"#ffffff"}}} -->
<form method="post" novalidate style="background-color:#ffffff" class="wp-block-mailster-form-wrapper mailster-block-form has-background has-background"><div class="mailster-block-form-inner"><!-- wp:columns -->
<div class="wp-block-columns"><!-- wp:column {"verticalAlignment":"center","width":"50%","style":{"spacing":{"padding":{"top":"2rem","right":"2rem","bottom":"2rem","left":"2rem"}}}} -->
<div class="wp-block-column is-vertically-aligned-center" style="padding-top:2rem;padding-right:2rem;padding-bottom:2rem;padding-left:2rem;flex-basis:50%"><!-- wp:site-logo {"align":"center"} /-->

<!-- wp:spacer {"height":"37px"} -->
<div style="height:37px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:heading {"textAlign":"center","style":{"typography":{"fontStyle":"normal","fontWeight":"700"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="font-style:normal;font-weight:700">' . esc_html__( 'Get $10 off with free shipping', 'maister' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:spacer {"height":"50px"} -->
<div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:mailster/field-firstname {"inline":true} -->
<div class="wp-block-mailster-field-firstname mailster-wrapper mailster-wrapper-type-text mailster-wrapper-inline"><input name="firstname" type="text" aria-required="false" aria-label="' . esc_attr__( 'Name', 'mailster' ) . '" spellcheck="false" value="" class="input" autocomplete="given-name" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Name', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-firstname -->

<!-- wp:mailster/field-email {"inline":true} -->
<div class="wp-block-mailster-field-email mailster-wrapper mailster-wrapper-required mailster-wrapper-type-email mailster-wrapper-inline mailster-wrapper-asterisk"><input name="email" type="email" aria-required="true" aria-label="' . esc_attr__( 'Email', 'mailster' ) . '" spellcheck="false" required value="" class="input" autocomplete="email" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Email', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-email -->

<!-- wp:mailster/field-submit {"style":{"typography":{"textTransform":"uppercase"},"spacing":{"padding":{"top":"var:preset|spacing|40","right":"var:preset|spacing|small","bottom":"var:preset|spacing|40","left":"var:preset|spacing|small"}}},"className":"mailster-wrapper-asterisk","fontSize":"large"} -->
<div class="wp-block-mailster-field-submit mailster-wrapper mailster-wrapper-type-submit wp-block-button mailster-wrapper-asterisk has-large-font-size"><input name="submit" type="submit" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--small);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--small);text-transform:uppercase" value="Sign up" class="wp-block-button__link submit-button"/></div>
<!-- /wp:mailster/field-submit -->

<!-- wp:spacer {"height":"50px"} -->
<div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:paragraph {"style":{"typography":{"fontSize":"0.8rem"},"color":{"text":"#444444"}}} -->
<p class="has-text-color" style="color:#444444;font-size:0.8rem">When you subscribe, you give us permission to send you emails about our products, exclusive promotions, and special events. However, you have the option to withdraw your consent at any point by clicking on the unsubscribe link provided in the emails.</p>
<!-- /wp:paragraph --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"50%"} -->
<div class="wp-block-column" style="flex-basis:50%"><!-- wp:cover {"url":"https://static.mailster.co/forms/tasty-food-vegetable-galette.jpg","id":287,"dimRatio":0,"focalPoint":{"x":0,"y":0.44}} -->
<div class="wp-block-cover"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><img class="wp-block-cover__image-background wp-image-287" alt="" src="https://static.mailster.co/forms/tasty-food-vegetable-galette.jpg" style="object-position:0% 44%" data-object-fit="cover" data-object-position="0% 44%"/><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","placeholder":"Write title…","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size"></p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></form>
<!-- /wp:mailster/form-wrapper -->',
	)
);

register_block_pattern(
	'mailster-form/pattern-13',
	array(
		'title'         => __( 'Pattern 13', 'mailster' ),
		'categories'    => array( 'featured', 'mailster-forms' ),
		'viewportWidth' => 962,
		'inserter'      => false,
		'postTypes'     => array( 'mailster-form' ),
		'keywords'      => array( 'mailster-form' ),
		'content'       => '<!-- wp:mailster/form-wrapper {"background":{"opacity":30,"fixed":false,"repeat":true,"size":48,"image":"https://static.mailster.co/forms/beautiful-faces.png","position":{"x":0.47,"y":0.33}},"borderRadius":"50px","css":{"general":".mailster-block-form-inner{\n\toverflow:visible;\n}\n.mailster-wrapper{\n\tmargin-top:1em;\n}\n.float-image{\n    border: 5px solid white;\n    border-radius: 50px;\n    overflow: hidden;\n    left: -5rem;\n\tmin-width: 25rem;\n\taspect-ratio: 1;\n\tbox-shadow: rgba(0, 0, 0, 0.4) 0px 2px 4px, rgba(0, 0, 0, 0.3) 0px 7px 13px -3px, rgba(0, 0, 0, 0.2) 0px -3px 0px inset;\n}","tablet":"","mobile":".float-image{\n\tmin-width: auto;\n    left: auto;\n}"},"style":{"color":{"background":"#C0DCED","text":"#222222"}}} -->
    <form method="post" novalidate style="color:#222222;background-color:#C0DCED" class="wp-block-mailster-form-wrapper mailster-block-form has-text-color has-background has-text-color has-background"><div class="mailster-block-form-inner"><!-- wp:columns {"style":{"spacing":{"padding":{"top":"2rem","right":"2rem","bottom":"2rem","left":"2rem"}}}} -->
<div class="wp-block-columns" style="padding-top:2rem;padding-right:2rem;padding-bottom:2rem;padding-left:2rem"><!-- wp:column {"width":"33.33%","className":"float-image","layout":{"type":"default"}} -->
<div class="wp-block-column float-image" style="flex-basis:33.33%"><!-- wp:cover {"url":"https://static.mailster.co/forms/skin-care.jpg","dimRatio":0,"focalPoint":{"x":0.5,"y":0.5},"isDark":false} -->
<div class="wp-block-cover is-light"><span aria-hidden="true" class="wp-block-cover__background has-background-dim-0 has-background-dim"></span><img class="wp-block-cover__image-background" alt="" src="https://static.mailster.co/forms/skin-care.jpg" style="object-position:50% 50%" data-object-fit="cover" data-object-position="50% 50%"/><div class="wp-block-cover__inner-container"><!-- wp:paragraph {"align":"center","placeholder":" ","fontSize":"large"} -->
<p class="has-text-align-center has-large-font-size"></p>
<!-- /wp:paragraph --></div></div>
<!-- /wp:cover --></div>
<!-- /wp:column -->

<!-- wp:column {"width":"66.67%"} -->
<div class="wp-block-column" style="flex-basis:66.67%"><!-- wp:spacer {"height":"50px"} -->
<div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:heading {"style":{"typography":{"fontStyle":"normal","fontWeight":"900"}}} -->
<h2 class="wp-block-heading" style="font-style:normal;font-weight:900">' . esc_html__( 'Hey There! ', 'maister' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'You\'ve unlocked a 10% discount on your next purchase!', 'mailster' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:mailster/field-firstname {"inline":true} -->
<div class="wp-block-mailster-field-firstname mailster-wrapper mailster-wrapper-type-text mailster-wrapper-inline"><input name="firstname" type="text" aria-required="false" aria-label="' . esc_attr__( 'Name', 'mailster' ) . '" spellcheck="false" value="" class="input" autocomplete="given-name" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Name', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-firstname -->

<!-- wp:mailster/field-email {"inline":true} -->
<div class="wp-block-mailster-field-email mailster-wrapper mailster-wrapper-required mailster-wrapper-type-email mailster-wrapper-inline mailster-wrapper-asterisk"><input name="email" type="email" aria-required="true" aria-label="' . esc_attr__( 'Email', 'mailster' ) . '" spellcheck="false" required value="" class="input" autocomplete="email" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Email', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-email -->

<!-- wp:mailster/field-submit -->
<div class="wp-block-mailster-field-submit mailster-wrapper mailster-wrapper-type-submit wp-block-button"><input name="submit" type="submit" value="' . esc_attr__( 'Subscribe', 'mailster' ) . '" class="wp-block-button__link submit-button"/></div>
<!-- /wp:mailster/field-submit -->

<!-- wp:spacer {"height":"50px"} -->
<div style="height:50px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer --></div>
<!-- /wp:column --></div>
<!-- /wp:columns --></div></form>
<!-- /wp:mailster/form-wrapper -->',
	)
);

register_block_pattern(
	'mailster-form/pattern-14',
	array(
		'title'         => __( 'Pattern 14', 'mailster' ),
		'categories'    => array( 'featured', 'mailster-forms' ),
		'viewportWidth' => 962,
		'inserter'      => false,
		'postTypes'     => array( 'mailster-form' ),
		'keywords'      => array( 'mailster-form' ),
		'content'       => '<!-- wp:mailster/form-wrapper {"background":{"opacity":100,"fixed":false,"repeat":false,"size":"cover","image":"https://static.mailster.co/forms/tropical-leaves.jpg","fullscreen":true,"position":{"x":0.51,"y":0.35}},"css":{"general":".mailster-block-form{\n    max-width:500px;\n}\n.mailster-wrapper{\n\tmargin-top:0.5em\n}\n.mailster-wrapper-required label.mailster-label::after{\n    display:none\n}\n\n","tablet":"","mobile":""}} -->
    <form method="post" novalidate class="wp-block-mailster-form-wrapper mailster-block-form"><div class="mailster-block-form-inner"><!-- wp:group {"style":{"color":{"background":"#fefefe"},"spacing":{"padding":{"top":"3rem","right":"4rem","bottom":"3rem","left":"4rem"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group has-background" style="background-color:#fefefe;padding-top:3rem;padding-right:4rem;padding-bottom:3rem;padding-left:4rem"><!-- wp:heading {"style":{"typography":{"letterSpacing":"-0.1rem","fontSize":"45px","fontStyle":"normal","fontWeight":"700"}}} -->
<h2 class="wp-block-heading" style="font-size:45px;font-style:normal;font-weight:700;letter-spacing:-0.1rem">' . esc_html__( 'Here is your 20% discount.', 'maister' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph -->
<p>' . esc_html__( 'Join our email list and get a special 20% discount!', 'mailster' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:spacer {"height":"40px"} -->
<div style="height:40px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:mailster/field-email {"inline":true} -->
<div class="wp-block-mailster-field-email mailster-wrapper mailster-wrapper-required mailster-wrapper-type-email mailster-wrapper-inline mailster-wrapper-asterisk"><input name="email" type="email" aria-required="true" aria-label="' . esc_attr__( 'Your Email address', 'mailster' ) . '" spellcheck="false" required value="" class="input" autocomplete="email" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Your Email address', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-email -->

<!-- wp:mailster/field-submit -->
<div class="wp-block-mailster-field-submit mailster-wrapper mailster-wrapper-type-submit wp-block-button"><input name="submit" type="submit" value="' . esc_html__( 'Get 20% now', 'mailster' ) . '" class="wp-block-button__link submit-button"/></div>
<!-- /wp:mailster/field-submit --></div>
<!-- /wp:group --></div></form>
<!-- /wp:mailster/form-wrapper -->',
	)
);

register_block_pattern(
	'mailster-form/pattern-15',
	array(
		'title'         => __( 'Pattern 15', 'mailster' ),
		'categories'    => array( 'featured', 'mailster-forms' ),
		'viewportWidth' => 962,
		'inserter'      => false,
		'postTypes'     => array( 'mailster-form' ),
		'keywords'      => array( 'mailster-form' ),
		'content'       => '<!-- wp:mailster/form-wrapper {"background":{"opacity":100,"fixed":false,"repeat":false,"size":"cover","fullscreen":true,"position":{"x":0.51,"y":0.35},"image":"https://static.mailster.co/forms/adventure-ready.jpg"},"css":{"general":".mailster-block-form{\n    max-width:500px;\n}\n.mailster-wrapper{\n\tmargin-top:0.5em\n}\n.mailster-wrapper-required label.mailster-label::after{\n    display:none\n}\n\n","tablet":"","mobile":""}} -->
    <form method="post" novalidate class="wp-block-mailster-form-wrapper mailster-block-form"><div class="mailster-block-form-inner"><!-- wp:group {"style":{"color":{"gradient":"linear-gradient(180deg,rgba(97,66,10,0.82) 18%,rgba(64,43,3,0.17) 100%)"},"spacing":{"padding":{"top":"3rem","right":"4rem","bottom":"3rem","left":"4rem"}}},"layout":{"type":"default"}} -->
<div class="wp-block-group has-background" style="background:linear-gradient(180deg,rgba(97,66,10,0.82) 18%,rgba(64,43,3,0.17) 100%);padding-top:3rem;padding-right:4rem;padding-bottom:3rem;padding-left:4rem"><!-- wp:heading {"textAlign":"center","style":{"typography":{"letterSpacing":"-0.1rem","fontSize":"71px","fontStyle":"normal","fontWeight":"700","textTransform":"uppercase"},"color":{"text":"#ececec"}}} -->
<h2 class="wp-block-heading has-text-align-center has-text-color" style="color:#ececec;font-size:71px;font-style:normal;font-weight:700;letter-spacing:-0.1rem;text-transform:uppercase">' . esc_html__( 'Ready for your next adventure?', 'maister' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center","style":{"color":{"text":"#f4f4f4"}}} -->
<p class="has-text-align-center has-text-color" style="color:#f4f4f4">' . sprintf( esc_html__( 'Join %1$s others and get a special %2$s discount!', 'mailster' ), '[newsletter_subscribers round=100]+', '20%' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:spacer {"height":"40px"} -->
<div style="height:40px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:mailster/field-email {"inline":true} -->
<div class="wp-block-mailster-field-email mailster-wrapper mailster-wrapper-required mailster-wrapper-type-email mailster-wrapper-inline mailster-wrapper-asterisk"><input name="email" type="email" aria-required="true" aria-label="' . esc_attr__( 'Your Email address', 'mailster' ) . '" spellcheck="false" required value="" class="input" autocomplete="email" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Your Email address', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-email -->

<!-- wp:mailster/field-submit -->
<div class="wp-block-mailster-field-submit mailster-wrapper mailster-wrapper-type-submit wp-block-button"><input name="submit" type="submit" value="' . esc_html__( 'Get 20% now', 'mailster' ) . '" class="wp-block-button__link submit-button"/></div>
<!-- /wp:mailster/field-submit --></div>
<!-- /wp:group --></div></form>
<!-- /wp:mailster/form-wrapper -->',
	)
);

register_block_pattern(
	'mailster-form/pattern-16',
	array(
		'title'         => __( 'Pattern 16', 'mailster' ),
		'categories'    => array( 'featured', 'mailster-forms' ),
		'viewportWidth' => 962,
		'inserter'      => false,
		'postTypes'     => array( 'mailster-form' ),
		'keywords'      => array( 'mailster-form' ),
		'content'       => '<!-- wp:mailster/form-wrapper {"borderRadius":"20px","style":{"color":{"text":"#fbfbfb","gradient":"linear-gradient(135deg,rgb(97,205,131) 0%,rgb(27,88,163) 100%)"},"spacing":{"padding":{"top":"2em","right":"2em","bottom":"2em","left":"2em"}},"inputColor":"#212427","labelColor":"#212427","borderRadius":"42px"}} -->
<form method="post" novalidate style="color:#fbfbfb;background:linear-gradient(135deg,rgb(97,205,131) 0%,rgb(27,88,163) 100%);padding-top:2em;padding-right:2em;padding-bottom:2em;padding-left:2em" class="wp-block-mailster-form-wrapper mailster-block-form has-text-color has-background has-text-color has-background"><div class="mailster-block-form-inner"><!-- wp:group {"style":{"spacing":{"padding":{"top":"0","right":"var:preset|spacing|large","bottom":"var:preset|spacing|medium","left":"var:preset|spacing|large"}}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group" style="padding-top:0;padding-right:var(--wp--preset--spacing--large);padding-bottom:var(--wp--preset--spacing--medium);padding-left:var(--wp--preset--spacing--large)"><!-- wp:heading {"textAlign":"center","style":{"typography":{"textTransform":"capitalize","fontStyle":"normal","fontWeight":"700"}}} -->
<h2 class="wp-block-heading has-text-align-center" style="font-style:normal;font-weight:700;text-transform:capitalize">' . esc_html__( 'Sign up for free content', 'maister' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Many people receive my posts in their inbox regularly, and they enjoy this service free of charge.', 'mailster' ) . '</p>
<!-- /wp:paragraph --></div>
<!-- /wp:group -->

<!-- wp:mailster/field-email {"align":"center","labelAlign":"center","asterisk":false,"inline":true,"style":{"width":100}} -->
<div class="wp-block-mailster-field-email mailster-wrapper mailster-wrapper-required mailster-wrapper-type-email mailster-wrapper-align-center mailster-wrapper-label-align-center mailster-wrapper-inline" style="width:100%"><input name="email" type="email" aria-required="true" aria-label="' . esc_attr__( 'Enter your email address and press enter', 'mailster' ) . '" spellcheck="false" required value="" class="input" autocomplete="email" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Enter your email address and press enter', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-email -->

<!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"13px"},"spacing":{"margin":{"top":"var:preset|spacing|small"}}}} -->
<p class="has-text-align-center" style="margin-top:var(--wp--preset--spacing--small);font-size:13px">' . esc_html__( 'No spam, you can cancel at any time.', 'mailster' ) . '</p>
<!-- /wp:paragraph --></div></form>
<!-- /wp:mailster/form-wrapper -->',
	)
);

register_block_pattern(
	'mailster-form/pattern-17',
	array(
		'title'         => __( 'Pattern 17', 'mailster' ),
		'categories'    => array( 'featured', 'mailster-forms' ),
		'viewportWidth' => 1262,
		'inserter'      => false,
		'postTypes'     => array( 'mailster-form' ),
		'keywords'      => array( 'mailster-form' ),
		'content'       => '<!-- wp:mailster/form-wrapper {"css":{"general":".mailster-wrapper{\n    margin-bottom:1em;\n}\n.mailster-form-ebook{\n\tflex-wrap: nowrap;\n\tgap:0 !important;\n}\n.mailster-form-ebook figure{\n\tbox-shadow: rgba(0, 0, 0, 0.07) 0px 1px 2px, rgba(0, 0, 0, 0.07) 0px 2px 4px, rgba(0, 0, 0, 0.07) 0px 4px 8px, rgba(0, 0, 0, 0.07) 0px 8px 16px, rgba(0, 0, 0, 0.07) 0px 16px 32px, rgba(0, 0, 0, 0.07) 0px 32px 64px;\n\tborder:1px solid #ccc;\n\tmax-width:33% !important;\n\t\n}\n.mailster-form-ebook figure:nth-child(1){\n\ttranslate: 40%;\n\tscale: 0.75;\n}\n.mailster-form-ebook figure:nth-child(2){\n\tz-index:2\n}\n.mailster-form-ebook figure:nth-child(3){\n\ttranslate: -40%;\n\tscale: 0.75;\n\t\n}"}} -->
<form method="post" novalidate class="wp-block-mailster-form-wrapper mailster-block-form"><div class="mailster-block-form-inner">

<!-- wp:gallery {"columns":3,"imageCrop":false,"linkTo":"none","className":"mailster-form-ebook"} -->
<figure class="wp-block-gallery has-nested-images columns-3 mailster-form-ebook"><!-- wp:image -->
<figure class="wp-block-image"><img src="https://static.mailster.co/forms/ebook-02.jpg" alt="Ebook page"/></figure>
<!-- /wp:image -->

<!-- wp:image -->
<figure class="wp-block-image"><img src="https://static.mailster.co/forms/ebook-01.jpg" alt="Ebook cover"/></figure>
<!-- /wp:image -->

<!-- wp:image -->
<figure class="wp-block-image"><img src="https://static.mailster.co/forms/ebook-03.jpg" alt="Ebook page"/></figure>
<!-- /wp:image --></figure>
<!-- /wp:gallery -->

<!-- wp:spacer {"height":"30px"} -->
<div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:heading {"textAlign":"center"} -->
<h2 class="wp-block-heading has-text-align-center">' . esc_html__( 'Free download', 'maister' ) . '</h2>
<!-- /wp:heading -->

<!-- wp:paragraph {"align":"center"} -->
<p class="has-text-align-center">' . esc_html__( 'Your ebook one click away', 'mailster' ) . '</p>
<!-- /wp:paragraph -->

<!-- wp:spacer {"height":"30px"} -->
<div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
<!-- /wp:spacer -->

<!-- wp:mailster/field-firstname {"inline":true} -->
<div class="wp-block-mailster-field-firstname mailster-wrapper mailster-wrapper-type-text mailster-wrapper-inline"><input name="firstname" type="text" aria-required="false" aria-label="' . esc_html__( 'First Name', 'mailster' ) . '" spellcheck="false" value="" class="input" autocomplete="given-name" placeholder=" "/><label class="mailster-label">' . esc_html__( 'First Name', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-firstname -->

<!-- wp:mailster/field-email {"inline":true} -->
<div class="wp-block-mailster-field-email mailster-wrapper mailster-wrapper-required mailster-wrapper-type-email mailster-wrapper-inline mailster-wrapper-asterisk"><input name="email" type="email" aria-required="true" aria-label="' . esc_attr__( 'Email', 'mailster' ) . '" spellcheck="false" required value="" class="input" autocomplete="email" placeholder=" "/><label class="mailster-label">' . esc_html__( 'Email', 'mailster' ) . '</label></div>
<!-- /wp:mailster/field-email -->

<!-- wp:mailster/field-submit {"align":"center"} -->
<div class="wp-block-mailster-field-submit mailster-wrapper mailster-wrapper-type-submit mailster-wrapper-align-center wp-block-button"><input name="submit" type="submit" value="' . esc_html__( 'Get now!', 'mailster' ) . '" class="wp-block-button__link submit-button"/></div>
<!-- /wp:mailster/field-submit --></div></form>
<!-- /wp:mailster/form-wrapper -->',
	)
);

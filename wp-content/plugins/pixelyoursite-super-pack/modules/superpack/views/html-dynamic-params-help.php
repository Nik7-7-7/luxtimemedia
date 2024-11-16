<?php

namespace PixelYourSite\SuperPack;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
$serverUrl = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
?>

<div class="card card-static">
	<div class="card-header">
		Dynamic Parameters Help
	</div>
	<div class="card-body">
		<div class="row">
			<div class="col">
                <p class="mb-3"><strong>Important:</strong> Don't use the Dynamic Parameters to send users' personal data with your events because it can trigger warnings or other similar actions.</p>
				<ul>
					<li><code>[id]</code> - it will pull the WordPress post ID</li>
					<li><code>[title]</code> - it will pull the content title</li>
					<li><code>[content_type]</code> - it will pull the post type (post, product, page and so on)</li>
					<li><code>[categories]</code> - it will pull the content categories</li>
					<li><code>[tags]</code> - it will pull the content tags</li>
                    <li><code>[total]</code> - it will pull WooCommerce or EDD order's total when it exists</li>
                    <li><code>[subtotal]</code> - it will pull WooCommerce or EDD order's subtotal when it exists</li>
				</ul>
                <p><strong>Track URL parameters:</strong></p>
                <p> Use <code>[url_ParameterName]</code> where ParameterName = the name of the parameter. <br/>
                    Example:<br/>
                    This is your URL: <?=$serverUrl?>?ParameterName=123<br/>
                    The parameter value will be 123.<br/>
                </p>
				<p class="mb-0"><strong>Note:</strong> if a parameter is missing from a particular page, the event won't
					include it.</p>
                <p class="mt-3"><strong>Track form parameters:</strong></p>
                <p> Use <code>[field_FieldName]</code> where FieldName = the name of the field. <br/>
                    Example:<br/>
                    This is your field name: filed-name<br/>
                    The value of the dynamic parameter will be: [field_field-name]<br/>
                    The parameter value will be the value of the field.<br/>
                </p>
                <p class="mt-3"><strong>Track MemberPress plugin parameters:</strong></p>
                <p> These parameters only work on a "thankyou page" with shortcode <code>[mepr-ecommerce-tracking]Message with %%variables%% in here[/mepr-ecommerce-tracking]</code><br/>
                <p> Available parameters are described <a href="https://docs.memberpress.com/article/112-available-shortcodes" target="_blank">here</a>.</p>
                <p> All variables must have the prefix "mp_".<br/>
                    Example:<br/>
                    This is your MemberPress variable: total. <br/>
                    The parameter value will be: [mp_total].<br/>
                </p>
			</div>
		</div>
	</div>
</div>
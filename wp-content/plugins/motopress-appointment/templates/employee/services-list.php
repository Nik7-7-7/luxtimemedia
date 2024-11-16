<?php

/**
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$services = mpapp()->repositories()->service()->findAllByEmployee( get_the_ID() );

if ( empty( $services ) ) {
	return;
}

?>
<ul class="mpa-employee-price-list">
	<?php foreach ( $services as $service ) { ?>
		<li class="mpa-price-list-item">
			<span class="mpa-price-list-title"><?php echo esc_html( $service->getTitle() ); ?></span>
			<span class="mpa-price-list-delimiter"> &#8212; </span>
			<span class="mpa-price-list-price"><?php echo mpa_tmpl_price( $service->getPrice() ); ?></span>
		</li>
	<?php } ?>
</ul>

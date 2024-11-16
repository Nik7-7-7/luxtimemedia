<?php

/**
 * @param string|int[] $thumbnail_size Optional. 'thumbnail' by default.
 * @param bool $add_link Optional. Wrap image with the link. False by default.
 * @param int $max_count Optional. 5 by default.
 *
 * @since 1.2
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$service = mpa_get_service();

if ( is_null( $service ) ) {
	return;
}

$employeeIds = $service->getEmployeeIds();

if ( ! count( $employeeIds ) ) {
	return;
}

// Initialize args
extract(
	array(
		'thumbnail_size' => 'thumbnail',
		'add_link'       => false,
		'max_count'      => 5,
	),
	EXTR_SKIP
);

?>
<p class="mpa-service-employees">
	<?php
	$thumbnailArgs = array( 'add_link' => $add_link );

	// Show single image + name
	if ( count( $employeeIds ) == 1 ) {
		$employeeId = reset( $employeeIds );

		echo mpa_tmpl_post_thumbnail( $employeeId, $thumbnail_size, $thumbnailArgs );

		echo '<span class="employee-name">';
		echo esc_html( get_the_title( $employeeId ) );
		echo '</span>';

		// Show multiple images with "+N" text, if there are more than $max_count employees
	} else {
		$count     = 0; // The number of images displayed
		$moreCount = count( $employeeIds ); // Number for text "+N"

		// Display images
		foreach ( $employeeIds as $employeeId ) {
			$image = mpa_tmpl_post_thumbnail( $employeeId, $thumbnail_size, $thumbnailArgs );

			if ( ! empty( $image ) ) {
				echo $image;

				$count ++;
				$moreCount --;

				if ( $count == $max_count ) {
					break;
				}
			}
		}

		// Show "+N" text
		if ( $moreCount > 0 ) {
			echo '<span class="more-employees">';
			if ( $count > 0 ) {
				// Translators: %d: Just a number.
				printf( esc_html__( '+%d', 'motopress-appointment' ), $moreCount );
			} else {
				echo mpa_tmpl_employees_number( $moreCount );
			}
			echo '</span>';
		}
	} // If employees count > 1
	?>
</p>

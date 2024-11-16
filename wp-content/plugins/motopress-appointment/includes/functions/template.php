<?php

use MotoPress\Appointment\Fields\Basic\PageSelectField;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param array $args
 *
 * @since 1.0
 */
function mpa_display_template( ...$args ) {
	echo mpa_render_template( ...$args );
}

/**
 * @param array $args
 * @return string
 *
 * @since 1.0
 */
function mpa_render_template( ...$args ) {
	$templates    = $args;
	$templateArgs = array();

	// Pull the $templateArgs from the function arguments list
	if ( is_array( $args[ count( $args ) - 1 ] ) ) {
		$templateArgs = array_pop( $templates );
	}

	// Try to find the proper template - from the more specific template
	// to more general
	$templateFile = mpa_locate_template( $templates );

	// Get the template content
	if ( ! empty( $templateFile ) ) {
		return mpa_load_template( $templateFile, $templateArgs );
	} else {
		// Nothing found
		return '';
	}
}

/**
 * @param string|array $templates
 * @return string The template file if one is located.
 *
 * @since 1.0
 */
function mpa_locate_template( $templates ) {
	$templateDir  = mpa_template_dir();
	$templateFile = '';

	foreach ( (array) $templates as $template ) {
		// Try to find a template in the theme, but only if it's not a
		// private template
		if ( ! mpa_str_starts_with( $template, 'private/' ) ) {
			$templateFile = locate_template( "{$templateDir}{$template}" );
		}

		// If still is nothing found then get it from the plugin templates
		if ( empty( $templateFile ) ) {
			$pluginTemplate = mpa_path_to( "templates/{$template}" );

			if ( file_exists( $pluginTemplate ) ) {
				$templateFile = $pluginTemplate;
			}
		}

		// Stop searching if found one
		if ( ! empty( $templateFile ) ) {
			break;
		}
	}

	/**
	 * Allow addons and 3rd party plugins to filter the template file.
	 *
	 * @since 1.0
	 */
	$templateFile = apply_filters( 'mpa_locate_template', $templateFile, $templates );

	return $templateFile;
}

/**
 * @param string $template_file
 * @param array $template_args Optional.
 * @return string
 *
 * @since 1.0
 */
function mpa_load_template( $template_file, $template_args = array() ) {
	// Notice: many templates (like post templates) use $template_name variable.
	// Don't use it here, or it will be bad.
	//
	// Also some templates use $template_args to get the full list of arguments.

	ob_start();

	if ( ! empty( $template_args ) ) {
		extract( $template_args );
	}

	require $template_file;

	return ob_get_clean();
}

/**
 * @return string
 *
 * @since 1.0
 */
function mpa_template_dir() {
	/** @since 1.0 */
	return apply_filters( 'mpa_template_dir', 'motopress/appointment/' );
}

/**
 * @param array $atts
 * @param bool $allowEmpty Optional. True by default.
 * @return string
 *
 * @since 1.0
 * @since 1.2 added optional parameter $allowEmpty.
 */
function mpa_tmpl_atts( $atts, $allowEmpty = true ) {
	$output = '';

	foreach ( $atts as $name => $value ) {
		$output .= mpa_tmpl_attr( $name, $value, $allowEmpty );
	}

	return $output;
}

/**
 * @param string $label
 * @param array  $atts Optional.
 *     @param string $atts['class'] 'button' by default.
 *     @param string $atts['type'] 'button' by default.
 * @return string
 *
 * @since 1.0
 */
function mpa_tmpl_button( $label, $atts = array() ) {
	$buttonAtts = array_merge(
		array(
			'type'  => 'button',
			'class' => 'button',
		),
		$atts
	);

	return '<button' . mpa_tmpl_atts( $buttonAtts ) . '>' . esc_html( $label ) . '</button>';
}

/**
 * @param string $name
 * @param string $class Optional. Additional class to set. '' by default.
 * @return string
 *
 * @since 1.0
 */
function mpa_tmpl_dashicon( $name, $class = '' ) {
	$class = rtrim( "dashicons dashicons-{$name} {$class}" );

	return '<span class="' . esc_attr( $class ) . '"></span>';
}

/**
 * @param int $postId
 * @param string $title Optional.
 * @return string
 *
 * @since 1.0
 */
function mpa_tmpl_edit_post_link( $postId, $title = '' ) {
	if ( empty( $title ) ) {
		$title = get_the_title( $postId );
	}

	$editUrl = get_edit_post_link( $postId, 'raw' );

	if ( ! empty( $editUrl ) ) {
		return mpa_tmpl_link( $editUrl, $title );
	} else {
		return $title;
	}
}

/**
 * @param int $postId
 * @return string
 *
 * @since 1.15.0
 */
function mpa_tmpl_edit_post_link_no_role_checks( int $postId ) {
	$post = get_post( $postId );

	if ( ! $post ) {
		return '';
	}

	$post_type_object = get_post_type_object( $post->post_type );

	if ( ! $post_type_object ) {
		return '';
	}

	if ( $post_type_object->_edit_link ) {
		$action = '&action=edit';
		$link   = admin_url( sprintf( $post_type_object->_edit_link . $action, $post->ID ) );
	} else {
		$link = '';
	}

	return mpa_tmpl_link( $link, $post->post_title );
}

/**
 * @param array $postsList [Post ID => Post title]
 * @return array [Post ID => Edit post link]
 *
 * @since 1.0
 */
function mpa_tmpl_edit_post_links( $postsList ) {
	$links = array();

	foreach ( $postsList as $id => $title ) {
		$links[ $id ] = mpa_tmpl_edit_post_link( $id, $title );
	}

	return $links;
}

/**
 * @param array $fields Array of [label, field].
 * @param array $args Not to be confused with 'atts'. Optional.
 * @return string
 *
 * @since 1.0
 */
function mpa_tmpl_form_table( $fields, $args = array() ) {
	$class = 'form-table' . ( isset( $args['class'] ) ? ' ' . $args['class'] : '' );

	$output      = '<table class="' . esc_attr( $class ) . '">';
		$output .= '<tbody>';

	foreach ( $fields as $row ) {
		$fieldHtml = $row['field'];
		$labelHtml = isset( $row['label'] ) ? $row['label'] : '';

		$hasLabel = ! empty( $labelHtml );

		$output .= '<tr>';
		if ( $hasLabel ) {
			$output .= '<th>' . $labelHtml . '</th>';
		}

			$output .= '<td colspan="' . ( $hasLabel ? 1 : 2 ) . '">' . $fieldHtml . '</td>';
			$output .= '</tr>';
	}

		$output .= '</tbody>';
	$output     .= '</table>';

	return $output;
}

/**
 * @param string $name
 * @param mixed $value
 * @param array $atts Optional.
 * @return string
 *
 * @since 1.0
 */
function mpa_tmpl_hidden( $name, $value, $atts = array() ) {
	$inputAtts = array_merge(
		array(
			'type'  => 'hidden',
			'name'  => $name,
			'value' => $value,
		),
		$atts
	);

	return '<input' . mpa_tmpl_atts( $inputAtts ) . '>';
}

/**
 * @param string $href
 * @param string $title Optional.
 * @param array $atts Optional.
 * @return string
 *
 * @since 1.0
 */
function mpa_tmpl_link( $href, $title = '', $atts = array() ) {
	$linkAtts = array(
		'href' => $href,
	);

	if ( ! empty( $title ) ) {
		$linkAtts['title'] = $title;
	}

	$linkAtts = array_merge( $linkAtts, $atts );

	return '<a' . mpa_tmpl_atts( $linkAtts ) . '>' . wp_kses( $title, 'entities' ) . '</a>';
}

/**
 * @param string $type success|info|warning|error|none
 * @param string $messageHtml
 * @param bool $isDismissible Optional. True by default.
 * @return string
 *
 * @since 1.0
 */
function mpa_tmpl_notice( $type, $messageHtml, $isDismissible = true ) {

	$class = 'notice';

	if ( 'notice' !== $type ) {
		$class .= ' notice-' . $type;
	}

	if ( $isDismissible ) {
		$class .= ' is-dismissible';
	}

	// Build output
	$output          = '<div class="' . $class . '">';
		$output     .= '<p>';
			$output .= $messageHtml;
		$output     .= '</p>';

	if ( $isDismissible ) {
		$output     .= '<button type="button" class="notice-dismiss">';
			$output .= '<span class="screen-reader-text">' . esc_html__( 'Dismiss this notice.', 'motopress-appointment' ) . '</span>';
		$output     .= '</button>';
	}
	$output .= '</div>';

	return $output;
}

/**
 * @param array $options
 * @param mixed|array $selected
 * @param array $atts Optional.
 * @return string
 *
 * @since 1.0
 */
function mpa_tmpl_select( $options, $selected, $atts = array() ) {
	$output      = '<select' . mpa_tmpl_atts( $atts ) . '>';
		$output .= mpa_tmpl_select_options( $options, $selected );
	$output     .= '</select>';

	return $output;
}

/**
 * @param array $options
 * @param mixed|array $selected
 * @return string
 *
 * @since 1.0
 */
function mpa_tmpl_select_options( $options, $selected ) {

	$output = '';

	foreach ( $options as $value => $label ) {

		$isSelected = ! is_array( $selected )
			? $value == $selected
			: in_array( $value, $selected );

		$output     .= '<option value="' . esc_attr( $value ) . '"' . selected( $isSelected, true, false ) . '>';
			$output .= esc_html( $label );
		$output     .= '</option>';
	}

	return $output;
}

/**
 * @since 1.5.0
 * @see wp_dropdown_pages()
 * @see PageSelectField::renderInput()
 *
 * @param array $args Optional.
 * @return string HTML dropdown list of pages.
 */
function mpa_tmpl_page_select( $args = array() ) {
	$args += array(
		'echo'              => false,
		'show_option_none'  => esc_html__( '— Select —', 'motopress-appointment' ),
		'option_none_value' => '',
	);

	/**
	 * @since 1.5.0
	 *
	 * @param array $args
	 */
	$args = apply_filters( 'mpa_tmpl_page_select_args', $args );

	/**
	 * @since 1.5.0
	 *
	 * @param array $args
	 */
	do_action( 'mpa_tmpl_before_page_select', $args );

	$selectHtml = wp_dropdown_pages( $args );

	/**
	 * @since 1.5.0
	 *
	 * @param array $args
	 */
	do_action( 'mpa_tmpl_after_page_select', $args );

	return $selectHtml;
}

/**
 * @param array $args Optional.
 *
 * @return string HTML dropdown list of users.
 *
 * @see wp_dropdown_users()
 *
 * @since 1.18.0
 */
function mpa_tmpl_user_select( $args = array() ) {
	$args += [
		'echo'              => false,
		'show'              => 'display_name_with_login',
		'show_option_none'  => esc_html__( '— Select —', 'motopress-appointment' ),
		'option_none_value' => '',
	];

	/**
	 * @param array $args
	 *
	 * @since 1.18.0
	 */
	$args = apply_filters( 'mpa_tmpl_user_select_args', $args );

	/**
	 * @param array $args
	 *
	 * @since 1.18.0
	 */
	do_action( 'mpa_tmpl_before_user_select', $args );

	$selectHtml = wp_dropdown_users( $args );

	/**
	 * @param array $args
	 *
	 * @since 1.18.0
	 *
	 */
	do_action( 'mpa_tmpl_after_user_select', $args );

	return $selectHtml;
}

/**
 * @return string
 *
 * @since 1.0
 */
function mpa_tmpl_placeholder() {
	return '&#8212;';
}

/**
 * @return string
 *
 * @since 1.1.0
 */
function mpa_tmpl_aria_placeholder() {
	return '<span aria-hidden="true">' . mpa_tmpl_placeholder() . '</span>';
}

/**
 * @return string
 *
 * @since 1.0
 */
function mpa_tmpl_required() {
	return '<strong><abbr title="' . esc_html__( 'Required', 'motopress-appointment' ) . '">*</abbr></strong>';
}

/**
 * @return string
 *
 * @since 1.0
 */
function mpa_tmpl_required_tip() {
	$output = '<small>';
		// Translators: %s: HTML tag.
		$output .= sprintf( esc_html__( 'Required fields are followed by %s.', 'motopress-appointment' ), mpa_tmpl_required() );
	$output     .= '</small>';

	return $output;
}

/**
 * @param float $price
 * @param array $args Optional.
 * @return string
 *
 * @since 1.0
 */
function mpa_tmpl_price( $price, $args = array() ) {

	return \MotoPress\Appointment\Helpers\PriceCalculationHelper::formatPriceAsHTML( $price, $args );
}

/**
 * @since 1.4.0
 *
 * @param float $price
 * @param array $args Optional. See mpa_tmpl_price() for details.
 * @return string
 */
function mpa_tmpl_price_number( $price, $args = array() ) {
	// Force number for all results
	$args['literal_free'] = false;

	return mpa_tmpl_price( $price, $args );
}

/**
 * @param string $name
 * @param mixed $value
 * @param bool $allowEmpty Optional. False by default.
 * @return string Empty string if the value is empty ('') and $allowEmpty is false.
 *
 * @since 1.2
 */
function mpa_tmpl_attr( $name, $value, $allowEmpty = false ) {

	if ( (string) '' !== $value || $allowEmpty ) {
		return ' ' . $name . '="' . esc_attr( $value ) . '"';
	} else {
		return '';
	}
}

/**
 * Based on Bootstrap 4.
 *
 * @see https://getbootstrap.com/docs/4.0/components/dropdowns/#single-button-dropdowns
 *
 * @param string $label
 * @param array $actions
 * @param array $args Optional.
 *     @param bool $args['inline'] False by default.
 *     @param string $args['class'] '' by default.
 *     @param string $args['button_class'] 'button button-secondary' by default.
 * @return string
 *
 * @since 1.2
 */
function mpa_tmpl_dropdown( $label, $actions, $args = array() ) {

	$args += array(
		'inline'       => false,
		'class'        => '',
		'button_class' => 'button button-secondary',
	);

	extract( $args );

	if ( $inline ) {
		$class = ltrim( $class . ' page-title-dropdown' );
	}

	ob_start();

	?>
	<div class="mpa-dropdown <?php echo esc_attr( $class ); ?>">
		<button class="dropdown-toggle <?php echo esc_attr( $button_class ); ?>" type="button">
			<?php echo esc_html( $label ); ?>
		</button>

		<div class="dropdown-menu">
			<?php foreach ( $actions as $actionLabel => $actionUrl ) { ?>
				<a class="dropdown-item" href="<?php echo esc_url( $actionUrl ); ?>">
					<?php echo esc_html( $actionLabel ); ?>
				</a>
			<?php } ?>
		</div>
	</div>
	<?php

	return ob_get_clean();
}

/**
 * @param MotoPress\Appointment\Entities\Schedule $schedule
 * @return array [Days period => Time period], like:
 *     [
 *         'Mon-Tue'   => '09:00 - 18:00',
 *         'Wednesday' => '09:00 - 17:00',
 *         ...
 *     ]
 *
 * @since 1.2
 */
function mpa_tmpl_schedule( $schedule ) {
	// [
	//     1 => '09:00 - 18:00', // Monday
	//     2 => '09:00 - 18:00',
	//     3 => '09:00 - 17:00',
	//     ...
	// ]
	$workingWeek = array_map(
		function ( $period ) {
			return $period->toString();
		},
		$schedule->getWorkingWeek( 1 )
	);

	// Group by time:
	//     [
	//         'Mon-Tue'   => '09:00 - 18:00',
	//         'Wednesday' => '09:00 - 17:00',
	//         ...
	//     ]
	$periods = array();

	$dayStart = -1;

	$lastTime   = '';
	$lastPeriod = '';

	foreach ( $workingWeek as $day => $time ) {

		// Reset period start day
		if ( $time != $lastTime ) {
			// Save previous period
			if ( -1 != $dayStart ) {
				$periods[ $lastPeriod ] = $lastTime;
			}

			$dayStart = $day;
		}

		$lastTime = $time;

		// Modify period string
		if ( $day == $dayStart ) {
			$lastPeriod = mpa_weekday( $day );
		} else {
			$lastPeriod = mpa_weekday_abbr( $dayStart ) . '-' . mpa_weekday_abbr( $day );
		}
	}

	// Save the last period
	if ( '' != $lastTime ) {
		$periods[ $lastPeriod ] = $lastTime;
	}

	return $periods;
}

/**
 * @param WP_Term $term
 * @param array $args Optional.
 *     @param bool $args['show_count'] True by default.
 * @return string
 *
 * @since 1.2
 */
function mpa_tmpl_term_title( $term, $args = array() ) {
	$defaultArgs = array(
		'show_count' => true,
		'count'      => $term->count,
	);

	$args += $defaultArgs;

	if ( $args['show_count'] ) {
		return $term->name . ' (' . number_format_i18n( $args['count'] ) . ')';
	} else {
		return $term->name;
	}
}

/**
 * @param int|WP_Post $post
 * @param string|int[] $size Optional. Image size. Accepts any registered image
 *     size name, or an array of width and height values in pixels (in that
 *     order). 'post-thumbnail' by default.
 * @param array $args Optional.
 *     @param bool $args['add_link'] Wrap image with the link. True by default.
 * @return string
 *
 * @since 1.2
 */
function mpa_tmpl_post_thumbnail( $post, $size = 'post-thumbnail', $args = array() ) {

	$addLink = ! isset( $args['add_link'] ) || $args['add_link'];

	if ( $addLink ) {
		return get_the_post_thumbnail( $post, $size );
	} else {
		$thumbnailId = get_post_thumbnail_id( $post );

		if ( false != $thumbnailId ) {
			return wp_get_attachment_image( $thumbnailId, $size );
		} else {
			return '';
		}
	}
}

/**
 * @param int $number
 * @return string
 *
 * @since 1.2
 */
function mpa_tmpl_employees_number( $number ) {
	// Translators: %d: The count of the associated employees.
	$text = esc_html( _n( '%d employee', '%d employees', $number, 'motopress-appointment' ) );

	/**
	 * @param string Default text.
	 * @param int Employees number.
	 *
	 * @since 1.2
	 */
	$text = apply_filters( 'mpa_tmpl_employees_number', $text, $number );

	$text = sprintf( $text, $number );

	return $text;
}

/**
 * @param MotoPress\Appointment\Entities\Service $service
 * @return string
 *
 * @see mpa_tmpl_service_capacity() in assets/dev/functions/entity.js.
 *
 * @since 1.2
 */
function mpa_tmpl_service_capacity( $service ) {
	if ( $service->getMinCapacity() != $service->getMaxCapacity() ) {
		return "{$service->getMinCapacity()} - {$service->getMaxCapacity()}";
	} else {
		return (string) $service->getMaxCapacity();
	}
}

/**
 * @param string $string Any string.
 * @param bool $allowUnderscore Optional. False by default.
 * @return string HTML ID (or single class), like: 'sample-id' (or 'sample-class').
 *
 * @since 1.2.1
 */
function mpa_tmpl_id( $string, $allowUnderscore = false ) {
	if ( $allowUnderscore ) {
		$pattern = '/\\W+/';
	} else {
		$pattern = '/[\\W_]+/';
	}

	// 'Sample-Text'
	$id = preg_replace( $pattern, '-', $string );
	$id = trim( $id, '-' );

	// 'sample-text'
	$id = strtolower( $id );

	return $id;
}

/**
 * @since 1.11.0
 */
function mpa_tmpl_preloader(): string {
	return '<span class="mpa-preloader"></span>';
}

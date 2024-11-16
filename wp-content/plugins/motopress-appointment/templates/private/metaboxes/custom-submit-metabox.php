<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param \WP_Post $post
 * @param bool $is_new_post Is Add New page.
 * @param array $statuses [Status => Label]
 * @param string $default_status
 *
 * @since 1.0
 * @since 1.5.0 added the <code>$default_status</code> argument.
 */

$postTypeObject = get_post_type_object( $post->post_type );
$canPublish     = current_user_can( $postTypeObject->cap->publish_posts );

if ( $is_new_post ) {
	$postStatus = $canPublish ? $default_status : 'draft';
} elseif ( 'auto-draft' === $post->post_status ) {
	$postStatus = 'draft';
} else {
	$postStatus = $post->post_status;
}

$statuses          = mpapp()->postTypes()->getPostType( $post->post_type )->statuses();
$availableStatuses = $statuses->getManualStatuses();

if ( ! array_key_exists( $postStatus, $availableStatuses ) &&
     $statuses->hasStatus( $postStatus )
) {
	$availableStatuses[ $postStatus ] = $statuses->getLabel( $postStatus );
}
?>
<div class="submitbox" id="submitpost">
	<div id="minor-publishing">
		<div id="misc-publishing-actions">
			<div class="misc-pub-section">
				<label for="mpa_post_status"><?php esc_html_e( 'Status:' ); /* Core text - no textdomain required */ ?></label>
				<?php
				echo mpa_tmpl_select(
					$availableStatuses,
					$postStatus,
					array(
						'id'   => 'mpa_post_status',
						'name' => 'mpa_post_status',
					)
				);
				?>
			</div>
			<div class="misc-pub-section">
				<span><?php esc_html_e( 'Created on:', 'motopress-appointment' ); ?></span>
				<strong><?php echo date_i18n( mpapp()->settings()->getPostDateTimeFormat(), strtotime( $post->post_date ) ); ?></strong>
			</div>
		</div>
	</div>
	<div id="major-publishing-actions">
		<div id="delete-action">
			<?php
			if ( current_user_can( 'delete_post', $post->ID ) ) {
				if ( ! EMPTY_TRASH_DAYS ) {
					$deleteText = esc_html__( 'Delete Permanently', 'motopress-appointment' );
				} else {
					$deleteText = esc_html__( 'Move to Trash', 'motopress-appointment' );
				}
				?>
				<a class="submitdelete deletion" href="<?php echo get_delete_post_link( $post->ID ); ?>"><?php echo $deleteText; ?></a>
			<?php } ?>
		</div>
		<div id="publishing-action">
			<span class="spinner"></span>
			<input id="original_publish" name="original_publish" type="hidden" value="<?php esc_attr_e( 'Update', 'motopress-appointment' ); ?>">
			<input id="publish" name="save" type="submit" class="button button-primary button-large" value="<?php in_array( $post->post_status, array( 'new', 'auto-draft' ) ) ? esc_attr_e( 'Create', 'motopress-appointment' ) : esc_attr_e( 'Update', 'motopress-appointment' ); ?>">
		</div>
		<div class="clear"></div>
	</div>
</div>

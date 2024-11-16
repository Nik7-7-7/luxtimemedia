<?php

namespace MotoPress\Appointment\AdminPages\Manage;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
class ManagePostsPage {

	/**
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $postType;

	/**
	 * @param string $postType
	 *
	 * @since 1.0
	 */
	public function __construct( $postType ) {

		$this->postType = $postType;

		add_action(
			'admin_init',
			function() {
				if ( $this->isCurrentPage() ) {
					$this->addActions();
				}
			}
		);
	}

	/**
	 * @since 1.0
	 */
	protected function addActions() {

		add_action(
			'admin_enqueue_scripts',
			function() {
				if ( $this->isCurrentPage() ) {
					$this->enqueueScripts();
				}
			}
		);

		add_action(
			'admin_footer',
			function() {
				$this->displayDescription();
			}
		);

		add_filter(
			"manage_{$this->postType}_posts_columns",
			function( $columns ) {
				return $this->filterColumns( $columns );
			}
		);

		add_filter(
			"manage_edit-{$this->postType}_sortable_columns",
			function( $columns ) {
				return $this->filterSortableColumns( $columns );
			}
		);

		add_action(
			"manage_{$this->postType}_posts_custom_column",
			function( $columnName, $postId ) {
				$this->manageCustomColumn( $columnName, $postId );
			},
			10,
			2
		);
	}

	/**
	 * @since 1.0
	 */
	protected function enqueueScripts() {}

	/**
	 *
	 * @since 1.13.0
	 */
	protected function displayDescription() {

		$description = $this->getDescription();

		if ( ! empty( $description ) ) {
			?>
			<script type="text/javascript">
				jQuery(function () {
					jQuery('#posts-filter > .wp-list-table').first().before(<?php echo json_encode( $description ); ?>);
				});
			</script>
			<?php
		}
	}

	/**
	 * @since 1.13.0
	 */
	protected function getDescription(): string {
		return '';
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function customColumns() {
		return array();
	}

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function customSortableColumns() {
		return array();
	}

	/**
	 * @param array $columns
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function filterColumns( $columns ) {

		$customColumns = $this->customColumns();

		// Add custom columns
		if ( ! empty( $customColumns ) ) {

			$columns = array_merge( $columns, $customColumns );

			// Make 'Date' columns last
			if ( isset( $columns['date'] ) ) {

				$dateLabel = $columns['date'];
				unset( $columns['date'] );
				$columns['date'] = $dateLabel;
			}
		}

		return $columns;
	}

	/**
	 * @param array $columns
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function filterSortableColumns( $columns ) {
		return array_merge( $columns, $this->customSortableColumns() );
	}

	/**
	 * Fires only for custom columns.
	 *
	 * @param string $columnName
	 * @param int $postId
	 *
	 * @since 1.0
	 */
	protected function manageCustomColumn( $columnName, $postId ) {

		$entity = mpapp()->repositories()->getByPostType( $this->postType )->findById( $postId );

		if ( ! is_null( $entity ) ) {

			$this->displayValue( $columnName, $entity );

		} else {
			// phpcs:ignore
			echo mpa_tmpl_placeholder();
		}
	}

	/**
	 * @param string $columnName Only custom columns.
	 * @param \MotoPress\Appointment\Entities\* $entity
	 *
	 * @since 1.0
	 */
	protected function displayValue( $columnName, $entity ) {
		// phpcs:ignore
		echo mpa_tmpl_placeholder();
	}

	/**
	 * @return bool
	 *
	 * @global string $pagenow
	 * @global string|null $typenow Is null if called too early.
	 *
	 * @since 1.0
	 */
	public function isCurrentPage() {

		global $pagenow, $typenow;

		return is_admin() && 'edit.php' === $pagenow && $typenow === $this->postType;
	}

	/**
	 * @return bool
	 *
	 * @since 1.0
	 */
	public function isCurrentTrashPage() {
		// phpcs:ignore
		return $this->isCurrentPage() && isset( $_GET['post_status'] ) && 'trash' === $_GET['post_status'];
	}

	/**
	 * @param array $additionalArgs Optional.
	 * @return string
	 *
	 * @since 1.0
	 */
	public function getUrl( $additionalArgs = array() ) {

		$args = array_merge(
			array(
				'post_type' => $this->postType,
			),
			$additionalArgs
		);

		$url = add_query_arg( $args, admin_url( 'edit.php' ) );

		return $url;
	}

	/**
	 * @param \WP_Query $query
	 *
	 * @return bool
	 * @since 1.18.0
	 *
	 */
	protected function isCurrentPageDBQuery( \WP_Query $query ): bool {

		return isset( $query->query['post_type'] ) &&
			$query->query['post_type'] === $this->postType;
	}

	/**
	 * @param string $postMetaKey
	 * @param string $postMetaValue
	 * @param string $filterNotice
	 *
	 * @return void
	 * @since 1.18.0
	 *
	 * Filtering outputting items by meta field.
	 *
	 */
	protected function addFilterByPostMeta( string $postMetaKey, string $postMetaValue, string $filterNotice = '' ) {

		if ( ! empty( $filterNotice ) ) {

			add_action(
				'admin_notices',
				function () use ( $filterNotice ) {
					?>
					<div class="notice notice-info">
						<p><?php echo wp_kses( $filterNotice, array( 'a' => array( 'href' => array(), 'target' => array() ) ) ); ?></p>
					</div>
					<?php
				}
			);
		}

		add_filter(
			'posts_join',
			function ( $join, $query ) {
				global $wpdb;

				if ( ! $this->isCurrentPageDBQuery( $query ) ) {
					return $join;
				}

				$join .= " LEFT JOIN $wpdb->postmeta ON $wpdb->posts.ID = $wpdb->postmeta.post_id ";

				return $join;
			},
			10,
			2
		);

		add_filter(
			'posts_where',
			function ( $where, $query ) use ( $postMetaKey, $postMetaValue ) {
				global $wpdb;

				if ( ! $this->isCurrentPageDBQuery( $query ) ) {
					return $where;
				}

				$where .= $wpdb->prepare(
					" AND ({$wpdb->postmeta}.meta_key = %s 
			AND {$wpdb->postmeta}.meta_value = %s)",
					$postMetaKey,
					$postMetaValue
				);

				return $where;
			},
			10,
			2
		);

		add_filter(
			'posts_groupby',
			function ( $groupby, $query ) {
				global $wpdb;

				if ( ! $this->isCurrentPageDBQuery( $query ) ) {
					return $groupby;
				}

				$groupby = "$wpdb->posts.ID";

				return $groupby;
			},
			10,
			2
		);
	}
}

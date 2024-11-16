<?php

namespace MotoPress\Appointment\Repositories;

use MotoPress\Appointment\Entities\AbstractEntity;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.0
 */
abstract class AbstractRepository {

	/**
	 * @var string
	 *
	 * @since 1.0
	 */
	protected $postType;

	/**
	 * @var array [Post ID => Entity object]
	 *
	 * @since 1.0
	 */
	protected $entitiesCache = array();

	/**
	 * @param string $postType
	 *
	 * @since 1.0
	 */
	public function __construct( $postType ) {
		$this->postType = $postType;

		$this->addActions();
	}

	/**
	 * @since 1.0
	 */
	protected function addActions() {}

	/**
	 * @return array <pre>[
	 *         'post' => ['ID', 'post_title', etc.], // Which fields we want to get
	 *         'postmeta' => [
	 *             // Name of the postmeta => Is it a single value field or not
	 *             'mpa_postmeta1'  => true,
	 *             '_mpa_postmeta2' => false,
	 *             ...
	 *         ]
	 *     ]</pre> where 'post' and 'postmeta' both optional.
	 *
	 * @since 1.0
	 */
	abstract protected function entitySchema();

	/**
	 * @param int $id Optional. Current post by default.
	 * @param bool $forceReload Optional. False by default.
	 * @return mixed - AbstractEntity|null
	 *
	 * @since 1.0
	 * @since 1.2 $id is optional.
	 */
	public function findById( $id = 0, $forceReload = false ) {

		if ( ! $id ) {
			$id = get_the_ID();
		}

		if ( ! $forceReload && ! empty( $this->entitiesCache[ $id ] ) ) {
			return $this->entitiesCache[ $id ];
		}

		$post   = $this->getPost( $id );
		$entity = $this->mapPostToEntity( $post );

		// Save the most actual result, even if $entity == null
		$this->entitiesCache[ $id ] = $entity;

		return $entity;
	}

	/**
	 * @param string $metaKey
	 * @param mixed $metaValue
	 * @return int Post ID or 0.
	 *
	 * @global \wpdb $wpdb
	 */
	public function findIdByMeta( string $metaKey, $metaValue ): int {

		global $wpdb;

		if ( ! is_serialized( $metaValue ) ) {
			$metaValue = maybe_serialize( $metaValue );
		}

		$postId = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT `post_id` FROM {$wpdb->postmeta} WHERE `meta_key` = %s AND `meta_value` = %s",
				mpa_prefix( $metaKey, 'metabox' ),
				$metaValue
			)
		);

		return ! is_null( $postId ) ? absint( $postId ) : 0;
	}

	/**
	 * Usage:
	 * <pre>
	 *     findByMeta($metaKey, $metaValue)
	 *     findByMeta($metaKey, $metaValue, $operator)
	 *     findByMeta($metaKey, $operator)
	 * </pre>
	 *
	 * @param string $metaKey
	 * @param mixed $metaValue
	 * @param string $operator Optional.
	 * @return AbstractEntity|null
	 *
	 * @since 1.0
	 */
	public function findByMeta( $metaKey, $metaValue, $operator = null ) {

		$args = array(
			'fields'         => 'ids',
			'meta_query'     => $this->metaQueryArgs( $metaKey, $metaValue, $operator ),
			'posts_per_page' => 1,
		);

		$posts = $this->findAll( $args );

		if ( empty( $posts ) ) {
			return null;
		}

		$postId = reset( $posts );

		return $this->findById( $postId );
	}

	/**
	 * @param array $args Optional.
	 *     @param string|array $args['fields'] 'all', 'ids' or [Key field => Value field],
	 *         for example: ['id' => 'name'].
	 * @return array Entities, IDs or mixed values (when "fields" argument is an
	 *     array).
	 *
	 * @since 1.0
	 */
	public function findAll( $args = array() ) {

		// Set defaults
		$args = array_merge( $this->defaultQueryArgs(), $args );

		// Force current post type
		$args['post_type'] = $this->postType;

		/** @since 1.0 */
		$args = apply_filters( "{$this->postType}_repository_get_posts_query_args", $args );
		/** @since 1.0 */
		$args = apply_filters( 'mpa_repository_get_posts_query_args', $args, $this->postType );

		$posts = $this->getPosts( $args );

		// Map posts
		if ( 'ids' === $args['fields'] ) {
			return $posts;
		} else {
			$entities = $this->mapPostsToEntities( $posts );

			// Add all entities we found to the cache
			foreach ( $entities as $entity ) {
				$this->entitiesCache[ $entity->getId() ] = $entity;
			}

			// Filter required columns
			if ( is_array( $args['fields'] ) && ! empty( $entities ) ) {

				$fields = mpa_first_pair( $args['fields'] );

				$filteredData = array();

				foreach ( $entities as $entity ) {

					$key        = null;
					$fieldValue = '';

					$keyGetter = 'get' . $fields[0];

					if ( method_exists( $entity, $keyGetter ) ) {

						$key = $entity->$keyGetter();

					} else {
						$key = $entity->{$fields[0]};
					}

					$fieldGetter = 'get' . $fields[1];

					if ( method_exists( $entity, $fieldGetter ) ) {

						$fieldValue = $entity->$fieldGetter();

					} else {
						$fieldValue = $entity->{$fields[1]};
					}

					if ( null !== $key ) {

						$filteredData[ $key ] = $fieldValue;
					}
				}

				$entities = $filteredData;
			}

			return $entities;
		}
	}

	/**
	 * Usage:
	 * <pre>
	 *     findAllByMeta($metaKey, $metaValue)
	 *     findAllByMeta($metaKey, $metaValue, $operator)
	 *     findAllByMeta($metaKey, $operator)
	 * </pre>
	 *
	 * @param string $metaKey
	 * @param mixed  $metaValue
	 * @param string $operator Optional.
	 * @param array  $args Optional.
	 * @return array Entities, IDs or mixed values.
	 *
	 * @since 1.0
	 */
	public function findAllByMeta( $metaKey, $metaValue, $operator = null, $args = array() ) {

		$metaQuery = $this->metaQueryArgs( $metaKey, $metaValue, $operator );

		if ( isset( $args['meta_query'] ) ) {
			$args['meta_query'] = array_merge( $args['meta_query'], $metaQuery );
		} else {
			$args['meta_query'] = $metaQuery;
		}

		return $this->findAll( $args );
	}

	/**
	 * @param string $metaKey
	 * @param mixed $value Value to search in the arrays of postmetas.
	 * @param array $args Optional.
	 * @return array Entities, IDs or mixed values.
	 *
	 * @since 1.0
	 */
	public function findAllByValueInMeta( $metaKey, $value, $args = array() ) {

		$preSearchArgs = array(
			'fields'     => 'ids',
			'meta_query' => $this->metaQueryArgs( $metaKey, null, 'EXISTS' ),
		);

		$postsWithMetaKey = $this->findAll( $preSearchArgs );
		$postsWithValue   = array();

		foreach ( $postsWithMetaKey as $postId ) {
			$metaValue = get_post_meta( $postId, $metaKey, true );

			if ( is_array( $metaValue ) && in_array( $value, $metaValue, true ) ) {
				$postsWithValue[] = $postId;
			}
		}

		if ( ( isset( $args['fields'] ) && 'ids' === $args['fields'] )
			|| empty( $postsWithValue ) // Otherwise we'll find all available posts
		) {
			return $postsWithValue;
		} else {
			return $this->findAll(
				array_merge(
					array(
						'include' => $postsWithValue,
					),
					$args
				)
			);
		}
	}

	/**
	 * @param int $id
	 * @return \WP_Post|null
	 *
	 * @since 1.0
	 */
	protected function getPost( $id ) {

		/** @since 1.0 */
		do_action( 'mpa_repository_before_get_post', $this->postType );

		$post = get_post( $id );

		if ( ! is_null( $post ) && $post->post_type !== $this->postType ) {
			$post = null; // Use the proper repository class
		}

		/** @since 1.0 */
		do_action( 'mpa_repository_after_get_post', $this->postType );

		return $post;
	}

	/**
	 * @param array $args
	 * @return \WP_Post[]|int[]
	 *
	 * @since 1.0
	 */
	protected function getPosts( $args ) {

		/** @since 1.0 */
		do_action( 'mpa_repository_before_get_posts', $this->postType );

		$queryArgs = $args;

		if ( is_array( $args['fields'] ) ) {
			// We'll apply this filter after we get the entities
			$queryArgs['fields'] = 'all';
		}

		$posts = get_posts( $queryArgs );

		/** @since 1.0 */
		do_action( 'mpa_repository_after_get_posts', $this->postType );

		return $posts;
	}

	/**
	 * @param string $taxonomy
	 * @param array $args
	 *     @param int $args['post_id'] Optional. Custom argument. Retrieve the terms
	 *         of the taxonomy that are attached to the post.
	 * @return array [Term ID => \WP_Term]
	 *
	 * @since 1.0
	 */
	protected function getCategories( $taxonomy, $args ) {

		// Force taxonomy name
		$args['taxonomy'] = $taxonomy;

		/** @since 1.0 */
		$args = apply_filters( "{$this->postType}_repository_get_categories_query_args", $args );
		/** @since 1.0 */
		$args = apply_filters( 'mpa_repository_get_categories_query_args', $args, $this->postType );

		// Get terms
		if ( ! isset( $args['post_id'] ) ) {
			// Get all taxonomies
			$terms = get_terms( $args );

		} else {
			// Filter taxonomies by post ID
			$postId = $args['post_id'];
			unset( $args['post_id'] );

			$terms = get_the_terms( $postId, $taxonomy );
		}

		// Convert to single preferred format
		if ( is_array( $terms ) ) {
			$ids        = wp_list_pluck( $terms, 'term_id' );
			$categories = array_combine( $ids, $terms );
		} else {
			$categories = array();
		}

		return $categories;
	}

	/**
	 * @param \WP_Post|null $post
	 * @return AbstractEntity|null
	 *
	 * @since 1.0
	 */
	public function mapPostToEntity( $post ) {

		if ( is_null( $post ) ) {
			return null;
		}

		$postData = $this->mapPostToPostData( $post );
		$entity   = $this->mapPostDataToEntity( $postData );

		return $entity;
	}

	/**
	 * @param \WP_Post[] $posts
	 * @return AbstractEntity[]
	 *
	 * @since 1.0
	 */
	public function mapPostsToEntities( $posts ) {
		return mpa_array_map_reset( array( $this, 'mapPostToEntity' ), $posts );
	}

	/**
	 * @param \WP_Post $post
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function mapPostToPostData( $post ) {

		$schema   = $this->entitySchema();
		$postData = array();

		if ( isset( $schema['post'] ) ) {
			foreach ( $schema['post'] as $postField ) {
				$postData[ $postField ] = $post->$postField;
			}
		}

		if ( isset( $schema['postmeta'] ) ) {
			foreach ( $schema['postmeta'] as $postmeta => $isSingle ) {
				$fieldName              = mpa_unprefix( $postmeta );
				$postData[ $fieldName ] = get_post_meta( $post->ID, $postmeta, $isSingle );
			}
		}

		return $postData;
	}

	/**
	 * @param array $postData
	 * @return AbstractEntity
	 *
	 * @since 1.0
	 */
	abstract protected function mapPostDataToEntity( $postData);

	/**
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function defaultQueryArgs() {
		return array(
			'fields'           => 'all',
			'order'            => 'ASC',
			'orderby'          => 'ID',
			'post_status'      => 'any', // Or ['publish', ...]
			'post_type'        => $this->postType,
			'posts_per_page'   => -1,
			'suppress_filters' => false,
		);
	}

	/**
	 * @param string $metaKey
	 * @param mixed $metaValue
	 * @param string|null $operator
	 * @return array
	 *
	 * @since 1.0
	 */
	protected function metaQueryArgs( $metaKey, $metaValue, $operator ) {

		$value = $metaValue;

		// The short variant of getByMeta() or getAllByMeta() allows to search
		// without the value. So maybe $operator = $metaValue
		if ( is_null( $operator ) ) {
			if ( mpa_is_operator( $value ) ) {
				$operator = $value;
				$value    = null;
			} else {
				$operator = is_array( $value ) ? 'IN' : '=';
			}
		}

		// Don't replace the existing "meta_query" parameter, if exists
		$metaQuery = array(
			array(
				'key'     => $metaKey,
				'compare' => $operator,
			),
		);

		if ( ! is_null( $value ) ) {
			$metaQuery[0]['value'] = $value;
		}

		return $metaQuery;
	}
}

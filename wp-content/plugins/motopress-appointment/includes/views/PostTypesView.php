<?php

namespace MotoPress\Appointment\Views;

use MotoPress\Appointment\Entities\Service;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @since 1.2
 */
class PostTypesView {

	/**
	 * @var static
	 *
	 * @since 1.2
	 */
	protected static $instance = null;

	/**
	 * @since 1.2
	 */
	public function addEmployeeActions() {
		$postType = mpa_employee()->getPostType();

		// Single post template (see PostTypePseudoTemplate::displayTemplate())
		add_action( "{$postType}_single_post", array( $this, 'employeeSinglePost' ) );

		// Single post items
		add_action( "{$postType}_single_post_attributes", array( $this, 'employeeSinglePostContacts' ), 10 );
		add_action( "{$postType}_single_post_attributes", array( $this, 'employeeSinglePostSocialNetworks' ), 20 );
		add_action( "{$postType}_single_post_attributes", array( $this, 'employeeSinglePostAdditionalInfo' ), 30 );
	}

	/**
	 * @since 1.2
	 */
	public function addServiceActions() {
		$postType = mpa_service()->getPostType();

		// Single post template (see PostTypePseudoTemplate::displayTemplate())
		add_action( "{$postType}_single_post", array( $this, 'serviceSinglePost' ) );

		// Single post items
		add_action( "{$postType}_single_post_attributes", array( $this, 'serviceSinglePostAttributes' ) );
	}

	/**
	 * @since 1.2
	 */
	public function employeeSinglePost() {
		$postType = mpa_employee()->getPostType();

		$templateArgs = array(
			'show_contacts'        => true,
			'show_social_networks' => true,
			'show_additional_info' => true,
			'attributes_separator' => '',
		);

		/**
		 * @param array Template args.
		 *
		 * @since 1.2
		 */
		$templateArgs = apply_filters( "{$postType}_single_post_args", $templateArgs );

		mpa_display_template(
			'employee/single-post.php',
			'post/single-post.php',
			$templateArgs
		);
	}

	/**
	 * @param array $templateArgs
	 *
	 * @since 1.2
	 */
	public function employeeSinglePostContacts( $templateArgs ) {
		if ( isset( $templateArgs['show_contacts'] ) && ! $templateArgs['show_contacts'] ) {
			return;
		}

		$postType = mpa_employee()->getPostType();

		/**
		 * @param string
		 *
		 * @since 1.2
		 */
		$attributesTitle = apply_filters( "{$postType}_single_post_contacts_title", esc_html__( 'Contact Information', 'motopress-appointment' ) );

		$templateArgs =
			array(
				'attributes' => 'contacts',
				'title'      => $attributesTitle,
				'class'      => 'mpa-single-post-attributes mpa-single-employee-attributes mpa-employee-contacts',
			)
			+ $templateArgs;

		mpa_display_template(
			'employee/attributes.php',
			'post/attributes.php',
			$templateArgs
		);
	}

	/**
	 * @param array $templateArgs
	 *
	 * @since 1.2
	 */
	public function employeeSinglePostSocialNetworks( $templateArgs ) {
		if ( isset( $templateArgs['show_social_networks'] ) && ! $templateArgs['show_social_networks'] ) {
			return;
		}

		$postType = mpa_employee()->getPostType();

		/**
		 * @param string
		 *
		 * @since 1.2
		 */
		$attributesTitle = apply_filters( "{$postType}_single_post_social_networks_title", esc_html__( 'Social Networks', 'motopress-appointment' ) );

		$templateArgs =
			array(
				'attributes' => 'socialNetworks',
				'title'      => $attributesTitle,
				'class'      => 'mpa-single-post-attributes mpa-single-employee-attributes mpa-employee-social-networks',
			)
			+ $templateArgs;

		mpa_display_template(
			'employee/attributes.php',
			'post/attributes.php',
			$templateArgs
		);
	}

	/**
	 * @param array $templateArgs
	 *
	 * @since 1.2
	 */
	public function employeeSinglePostAdditionalInfo( $templateArgs ) {
		if ( isset( $templateArgs['show_additional_info'] ) && ! $templateArgs['show_additional_info'] ) {
			return;
		}

		$postType = mpa_employee()->getPostType();

		/**
		 * @param string
		 *
		 * @since 1.2
		 */
		$attributesTitle = apply_filters( "{$postType}_single_post_additional_info_title", esc_html__( 'Additional Information', 'motopress-appointment' ) );

		$templateArgs =
			array(
				'attributes' => 'additionalInfo',
				'title'      => $attributesTitle,
				'class'      => 'mpa-single-post-attributes mpa-single-employee-attributes mpa-employee-additional-info',
			)
			+ $templateArgs;

		mpa_display_template(
			'employee/attributes.php',
			'post/attributes.php',
			$templateArgs
		);
	}

	/**
	 * @since 1.2
	 */
	public function serviceSinglePost() {
		$postType = mpa_service()->getPostType();

		$templateArgs = array(
			'show_attributes' => true,
		);

		/**
		 * @param array Template args.
		 *
		 * @since 1.2
		 */
		$templateArgs = apply_filters( "{$postType}_single_post_args", $templateArgs );

		mpa_display_template(
			'service/single-post.php',
			'post/single-post.php',
			$templateArgs
		);
	}

	/**
	 * @param array $templateArgs
	 *
	 * @since 1.2
	 */
	public function serviceSinglePostAttributes( $templateArgs ) {
		$postType = mpa_service()->getPostType();
		$service  = mpa_get_service();

		/**
		 * @param string
		 *
		 * @since 1.2
		 */
		$attributesTitle = apply_filters( "{$postType}_single_post_attributes_title", esc_html__( 'Details', 'motopress-appointment' ) );

		/**
		 * @since 1.3.1
		 *
		 * @param array $args See mpa_get_service_attributes() for details.
		 * @param Service $service
		 */
		$attributesArgs = apply_filters( "{$postType}_single_post_attributes_args", array(), $service );

		$templateArgs =
			array(
				'attributes' => mpa_get_service_attributes( $service, $attributesArgs ),
				'title'      => $attributesTitle,
				'class'      => 'mpa-single-post-attributes mpa-single-service-attributes',
			)
			+ $templateArgs;

		mpa_display_template(
			'service/attributes.php',
			'post/attributes.php',
			$templateArgs
		);
	}

	/**
	 * @return static
	 *
	 * @since 1.2
	 */
	public static function getInstance() {
		if ( is_null( static::$instance ) ) {
			static::createInstance();
		}

		return static::$instance;
	}

	/**
	 * @since 1.2
	 */
	protected static function createInstance() {
		static::$instance = new static();
	}
}

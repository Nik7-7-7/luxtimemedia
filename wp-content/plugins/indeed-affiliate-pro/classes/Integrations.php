<?php
// Since version8.9
/*
How to use:
\Indeed\Uap\Integrations::isWooActive()
\Indeed\Uap\Integrations::isUmpActive()
\Indeed\Uap\Integrations::isEddActive()
\Indeed\Uap\Integrations::isUlpActive()
\Indeed\Uap\Integrations::isWpFormsActive()
\Indeed\Uap\Integrations::isNinjaActive()
\Indeed\Uap\Integrations::isCf7Active()
*/
namespace Indeed\Uap;
class Integrations
{
    /**
     * @param none
     * @return none
     */
    public function __construct(){}

    /**
      * @param none
      * @return array
      */
    public static function getSystems()
    {
        $systems = array(
             			 	'woo' => array(
             			 			'label' => esc_html__('WooCommerce', 'uap'),
             			 			'author' => 'Ultimate Affiliate Pro',
             			 			'author-link' => 'https://ultimateaffiliate.pro',
             			 			'description' => esc_html__('This integration ensures accurate tracking and seamless commission management for all your WooCommerce transactions', 'uap'),
             			 			'status' => self::isWooActive() && uap_is_woo_active() ? 1 : 0,
             			 			'extra-details-link' => 'https://ultimateaffiliate.pro/integrations/affiliate-program-for-woocommerce/',
             			 			'extra-settings-link' => '',
             						'installed' => uap_is_woo_active() ? 1 : 0,
             			 	),
             			 	'ump' => array(
             			 			'label' => esc_html__('Ultimate Membership Pro', 'uap'),
             			 			'author' => 'Ultimate Affiliate Pro',
             			 			'author-link' => 'https://ultimateaffiliate.pro',
             			 			'description' => esc_html__('Reward affiliates for referring new members and selling memberships via your membership site', 'uap'),
             			 			'status' => self::isUmpActive() && uap_is_ump_active() ? 1 : 0,
             			 			'extra-details-link' => '',
             			 			'extra-settings-link' => '',
             						'installed' => uap_is_ump_active() ? 1 : 0,
             			 	),
             			 	'edd' => array(
             			 			'label' => esc_html__('Easy Digital Downloads', 'uap'),
             			 			'author' => 'Ultimate Affiliate Pro',
             			 			'author-link' => 'https://ultimateaffiliate.pro',
             			 			'description' => esc_html__('Enhance your affiliate program with precise tracking and efficient commission management for your EDD store', 'uap'),
             			 			'status' => self::isEddActive() && uap_is_edd_active() ? 1 : 0,
             			 			'extra-details-link' => 'https://ultimateaffiliate.pro/integrations/easy-digital-downloads/',
             			 			'extra-settings-link' => '',
             						'installed' => uap_is_edd_active() ? 1 : 0,
             			 	),
             			 	'ulp' => array(
             			 			'label' => esc_html__('Ultimate Learning Pro', 'uap'),
             			 			'author' => 'Ultimate Affiliate Pro',
             			 			'author-link' => 'https://ultimateaffiliate.pro',
             			 			'description' => esc_html__('Easily integrate Ultimate Learning Pro to seamlessly track and reward your affiliates for promoting and selling your online courses', 'uap'),
             			 			'status' => self::isUlpActive() && uap_is_ulp_active() ? 1 : 0,
             			 			'extra-details-link' => '',
             			 			'extra-settings-link' => '',
             						'installed' => uap_is_ulp_active() ? 1 : 0,
             			 	),
             				'cf7' => array(
             			 			'label' => esc_html__('Contact Form 7', 'uap'),
             			 			'author' => 'Ultimate Affiliate Pro',
             			 			'author-link' => 'https://ultimateaffiliate.pro',
             			 			'description' => esc_html__('Effectively reward your affiliates for every Contact Form 7 submitted by referred users', 'uap'),
             			 			'status' => self::isCf7Active() && (uap_is_cf7_active() && self::cf7AddonActive() ) ? 1 : 0,
             			 			'extra-details-link' => '',
             			 			'extra-settings-link' => admin_url('admin.php?page=ultimate_affiliates_pro&tab=uap_cf7t'),
             						'installed' => (uap_is_cf7_active() && self::cf7AddonActive() ) ? 1 : 0,
             			 	),
             				'wpforms' => array(
             			 			'label' => esc_html__('WPForms', 'uap'),
             			 			'author' => 'Ultimate Affiliate Pro',
             			 			'author-link' => 'https://ultimateaffiliate.pro',
             			 			'description' => esc_html__('Allocate commissions to your affiliates for each WPForms submission', 'uap'),
             			 			'status' => self::isWpFormsActive() && (uap_is_wpforms_active() && self::wpformsAddonActive()) ? 1 : 0,
             			 			'extra-details-link' => 'https://ultimateaffiliate.pro/integrations/wpforms/',
             			 			'extra-settings-link' => admin_url('admin.php?page=ultimate_affiliates_pro&tab=uap_wp_forms'),
             						'installed' => (uap_is_wpforms_active() && self::wpformsAddonActive()) ? 1 : 0,
             			 	),
             				'ninjaforms' => array(
             			 			'label' => esc_html__('Ninja Forms', 'uap'),
             			 			'author' => 'Ultimate Affiliate Pro',
             			 			'author-link' => 'https://ultimateaffiliate.pro',
             			 			'description' => esc_html__('Pay off affiliates for every Ninja Form submission', 'uap'),
             			 			'status' => self::isNinjaActive() && ( ( uap_is_ninjaforms_active() || uap_is_ninjaformsdemo_active() ) && self::ninjaAddonActive()) ? 1 : 0,
             			 			'extra-details-link' => 'https://ultimateaffiliate.pro/integrations/ninja-forms/',
             			 			'extra-settings-link' => admin_url('admin.php?page=ultimate_affiliates_pro&tab=uap_nft'),
             						'installed' => ( ( uap_is_ninjaforms_active() || uap_is_ninjaformsdemo_active() ) && self::ninjaAddonActive()) ? 1 : 0,
             			 	),
 			 );
       $systems = apply_filters( 'uap_filter_integrations_systems', $systems );
       return $systems;
    }

    /**
      * @param none
      * @return int
      */
    public static function isWooActive()
    {
        $option = get_option( 'uap-integrations-woo', 1 );
        if ( $option === null || $option === '' || $option === false ){
            return 1;
        }
        return (int)$option;
    }

    /**
      * @param none
      * @return int
      */
    public static function isUmpActive()
    {
        $option = get_option( 'uap-integrations-ump', 1 );
        if ( $option === null || $option === '' || $option === false ){
            return 1;
        }
        return (int)$option;
    }

    /**
      * @param none
      * @return int
      */
    public static function isUlpActive()
    {
        $option = get_option( 'uap-integrations-ulp', 1 );
        if ( $option === null || $option === '' || $option === false ){
            return 1;
        }
        return (int)$option;
    }

    /**
      * @param none
      * @return int
      */
    public static function isEddActive()
    {
        $option = get_option( 'uap-integrations-edd', 1 );
        if ( $option === null || $option === '' || $option === false ){
            return 1;
        }
        return (int)$option;
    }

    /**
      * @param none
      * @return int
      */
    public static function isNinjaActive()
    {
        $option = get_option( 'uap-integrations-ninjaforms', 1 );
        if ( $option === null || $option === '' || $option === false ){
            return 1;
        }
        return (int)$option;
    }

    /**
      * @param none
      * @return int
      */
    public static function isCf7Active()
    {
        $option = get_option( 'uap-integrations-cf7', 1 );
        if ( $option === null || $option === '' || $option === false ){
            return 1;
        }
        return (int)$option;
    }

    /**
      * @param none
      * @return int
      */
    public static function isWpFormsActive()
    {
        $option = get_option( 'uap-integrations-wpforms', 1 );
        if ( $option === null || $option === '' || $option === false ){
            return 1;
        }
        return (int)$option;
    }

    /**
      * @param none
      * @return int
      */
    public static function ninjaAddonActive()
    {
        if ( !function_exists( 'is_plugin_active' ) ){
      			include_once ABSPATH . 'wp-admin/includes/plugin.php';
      	}
      	if ( is_plugin_active( 'uap-ninja-forms-tracking/uap-ninja-forms-tracking.php' ) ){
      			return true;
      	}
      	return false;
    }

    /**
      * @param none
      * @return int
      */
    public static function cf7AddonActive()
    {
        if ( !function_exists( 'is_plugin_active' ) ){
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if ( is_plugin_active( 'uap-cf7-tracking/uap-cf7-tracking.php' ) ){
            return true;
        }
        return false;
    }

    /**
      * @param none
      * @return int
      */
    public static function wpformsAddonActive()
    {
        if ( !function_exists( 'is_plugin_active' ) ){
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }
        if ( is_plugin_active( 'uap-wp-forms/uap-wp-forms.php' ) ){
            return true;
        }
        return false;
    }

    public static function update( $type='', $value='' )
    {
        update_option( 'uap-integrations-' . $type, $value );
    }

}

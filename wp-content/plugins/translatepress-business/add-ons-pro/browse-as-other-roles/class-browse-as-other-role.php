<?php

class TRP_IN_Browse_as_other_Role{

    protected $loader;
    protected $slug_manager;
    protected $settings;

    public function __construct() {

        define( 'TRP_IN_BOR_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
        define( 'TRP_IN_BOR_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

        $trp = TRP_Translate_Press::get_trp_instance();
        $this->loader = $trp->get_component( 'loader' );
        $trp_settings = $trp->get_component( 'settings' );
        $this->settings = $trp_settings->get_settings();


        $this->loader->add_filter( 'trp_view_as_values', $this, 'trp_bor_view_as_values' );
        $this->loader->add_filter( 'trp_editor_nonces', $this, 'trp_bor_nonces' );
        $this->loader->add_filter( 'trp_temporary_change_current_user_role', $this, 'trp_bor_temporary_change_current_user_role', 10, 2 );


    }


    /**
     * Function that replaces the dummy values for the roles in the view as dropdown from the free version with the actual proper values.
     * @param $trp_view_as_values
     * @return mixed
     */
    public function trp_bor_view_as_values( $trp_view_as_values ){

        $trp_all_roles = wp_roles()->roles;
        if( !empty( $trp_all_roles ) ){
            foreach( $trp_all_roles as $trp_all_role_slug => $trp_all_role ){
                $trp_view_as_values[$trp_all_role['name']] = $trp_all_role_slug;
            }
        }

        return $trp_view_as_values;
    }

    /**
     * Function that adds the nonces for the View As Values
     * @param $nonces
     * @return array
     */
    public function trp_bor_nonces( $nonces ){

        $roles = wp_roles()->roles;
        if( !empty( $roles ) ){
            foreach( $roles as $slug => $role )
                $nonces[$slug] = wp_create_nonce( 'trp_view_as'.$slug.get_current_user_id() );
        }

        return $nonces;
    }


    /**
     * Changes the $current_user global with the role from the $view_as variable
     * @param $current_user - global current user role
     * @param $view_as the slug of the role we want to change the current user object
     * @return mixed
     */
    public function trp_bor_temporary_change_current_user_role( $current_user, $view_as ){

        $trp_all_roles = wp_roles()->roles;
        if( !empty( $trp_all_roles ) ){
            foreach( $trp_all_roles as $trp_all_role_slug => $trp_all_role ){
                if( $view_as === $trp_all_role_slug ){
                    $current_user->roles = array( $trp_all_role_slug );
                    $current_user->caps = array( $trp_all_role_slug => true );
                    if( !empty( $trp_all_role['capabilities'] ) ) {
                        $current_user->allcaps = $trp_all_role['capabilities'];
                    }
                }
            }
        }

        return $current_user;
    }


}

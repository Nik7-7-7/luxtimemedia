<?php

namespace Indeed\Uap;

class GeneralActions
{

    public function __construct()
    {
        add_action( 'uap_before_user_save_custom_field', array( $this, 'changeAvatar' ), 999, 3 );
    }

    public function changeAvatar( $uid='', $metaKey='', $metaValue='' )
    {
        if ( $metaKey != 'uap_avatar' || $metaValue == '' ){
            return;
        }
        $oldValue = get_user_meta( $uid, 'uap_avatar', true );
        if ( !$oldValue || $oldValue == $metaValue ){
            return;
        }
        if ( strpos( $oldValue, "http" ) === 0 ){
            return;
        }
        /// delete old avatar
        wp_delete_attachment( $oldValue, true );

    }


}

<?php
namespace Indeed\Ihc\Admin;

class HandleDeleteMedia
{
    /**
     * @var array
     */
    private static $targetFieldsNames    = [];

    /**
     * @param none
     * @return none
     */
  	public function __construct()
  	{
  		  add_action( 'ihc_delete_user_action', [ $this, 'doDelete' ], 1, 1 );
  	}

    /**
     * @param int
     * @return bool
     */
  	public function doDelete( $uid=0 )
  	{
    		if ( empty( self::$targetFieldsNames ) ){
      			$this->setTargetFields();
    		}
        if ( empty( self::$targetFieldsNames ) ){
            return false;
        }
    		foreach ( self::$targetFieldsNames as $target ){
      			$mediaId = get_user_meta( $uid, $target, true );
      			if ( $mediaId ){
      				  wp_delete_attachment( $mediaId );
      			}
    		}
        // does it have a custom banner
        $customBannerUrl = get_user_meta( $uid, 'ihc_user_custom_banner_src', true );
        if ( empty( $customBannerUrl ) ){
            return false;
        }
        $mediaId = attachment_url_to_postid( $customBannerUrl );
        if ( !$mediaId ){
            return false;
        }
        wp_delete_attachment( $mediaId );
  	}

    /**
     * @param none
     * @return bool
     */
    private function setTargetFields()
    {
        $registerFields = ihc_get_user_reg_fields();
        if ( !$registerFields ){
            return false;
        }
        foreach ( $registerFields as $registerField ){
            if ( $registerField['type'] == 'file' || $registerField['type'] == 'upload_image' ){
                self::$targetFieldsNames[] = $registerField['name'];
            }
        }
        return true;
    }

}

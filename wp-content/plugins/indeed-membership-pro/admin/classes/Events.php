<?php
namespace Indeed\Ihc\Admin;

class Events
{
    /**
     * @param none
     * @return none
     */
    public function __construct()
    {
        add_action( 'ihc_action_after_delete_membership', [ $this, 'onDeleteMembership' ], 1, 2 );
        add_action( 'ihc_action_admin_save_membership', [ $this, 'saveStripeConnectProduct' ], 1, 1 );
        add_action( 'ihc_action_admin_write_new_log', [ $this, 'writeLog'], 99, 2 );
    }

    /**
     * It will remove the post restrictions for this level.
     * @param int
     * @param bool
     * @return none
     */
    public function onDeleteMembership( $membershipId=null, $processDone=true )
    {
        global $wpdb;
        if ( !$processDone ){
            return;
        }
        $query = "
        SELECT DISTINCT(a.post_id) as ID
        	FROM {$wpdb->postmeta} a
        	INNER JOIN {$wpdb->posts} b
        	ON a.post_id=b.ID
        	INNER JOIN {$wpdb->postmeta} c
        	ON c.post_id=a.post_id
        	WHERE 1=1
        	AND
        	(
        			(
        					( a.meta_key='ihc_mb_type' AND a.meta_value='show' )
        					AND
        					( c.meta_key='ihc_mb_who' AND FIND_IN_SET($membershipId, c.meta_value) )
        			)
        			OR
        			(
        				( a.meta_key='ihc_mb_type' AND a.meta_value='block' )
        				AND
        				( c.meta_key='ihc_mb_who' AND ( FIND_IN_SET($membershipId, c.meta_value)  ) )
        			)
        	)
        ";

        $posts = $wpdb->get_results( $query );

        if ( !$posts ){
            return;
        }

        foreach ( $posts as $postData ){
        		$postSettings = get_post_meta( $postData->ID, 'ihc_mb_who', true );
        		$levelIds = explode( ',', $postSettings );
        		$key = array_search ( $membershipId , $levelIds );
        		if ( $key !== false ){
        				unset( $levelIds[ $key ] );
        		}
        		$levelIds = implode( ',', $levelIds );
        		update_post_meta( $postData->ID, 'ihc_mb_who', $levelIds );
        }
    }

    public function saveStripeConnectProduct( $data=[] )
    {
        $enabled = get_option('ihc_stripe_connect_status');
        if ( $enabled === false || $enabled === null || $enabled == 0 ){
            return;
        }
        if ( !class_exists('\Stripe\StripeClient') ){
            require_once IHC_PATH . 'classes/gateways/libraries/stripe-sdk/init.php';
        }
        try {
            if ( get_option( 'ihc_stripe_connect_live_mode' ) ){
                $key = get_option( 'ihc_stripe_connect_client_secret' );
                if ( $key === '' || $key === false || $key === null ){
                    return;
                }
                $stripe = new \Stripe\StripeClient( $key );
                $productId = \Indeed\Ihc\Db\Memberships::getOneMeta( $data['level_id'], 'ihc_stripe_product_id' );
                $metaName = 'ihc_stripe_product_id';
            } else {
                $key = get_option( 'ihc_stripe_connect_test_client_secret' );
                if ( $key === '' || $key === false || $key === null ){
                    return;
                }
                $stripe = new \Stripe\StripeClient( $key );
                $productId = \Indeed\Ihc\Db\Memberships::getOneMeta( $data['level_id'], 'ihc_stripe_product_id-test' );
                $metaName = 'ihc_stripe_product_id-test';
            }
            $statementDescriptor = get_option( 'ihc_stripe_connect_descriptor' );
            if ( $statementDescriptor === false ){
                $statementDescriptor = get_option( 'blogname' );
            }
            $statementDescriptor = substr( $statementDescriptor, 0, 21 );

            if ( $productId === null ){
                // create
                $productParams = [
                  'name'                  => $data['label']
                ];
                if ( $data['short_description'] !== '' ){
                    $productParams['description'] = $data['short_description'];
                }
                if ( $statementDescriptor !== '' ){
                    $productParams['statement_descriptor'] = $statementDescriptor;
                }
                $product = $stripe->products->create( $productParams );

                $productId = isset( $product->id ) ? $product->id : '';
                \Indeed\Ihc\Db\Memberships::saveMeta( $data['level_id'], $metaName, $productId );
            } else {
                // update if it's case
                $product = $stripe->products->retrieve( $productId );
                if ( $product->name !== $data['label'] ){
                    // update name
                    $stripe->products->update(
                      $productId,
                      [ 'name' => $data['label'] ]
                    );
                }
                if ( $product->description !== $data['description'] ){
                    // update description
                    $stripe->products->update(
                      $productId,
                      [ 'description' => $data['description'] ]
                    );
                }
                if ( $product->statement_descriptor !== $statementDescriptor ){
                    // update statement_descriptor
                    $stripe->products->update(
                      $productId,
                      [ 'statement_descriptor' => $statementDescriptor ]
                    );
                }
            }
        } catch ( \Exception $e ){

        }


    }

    public function writeLog( $data=[] )
    {
    		global $wp_version;
        if ( (int)(get_option('ump_last_time_log_levels')) + ( 2*24*60*60 ) > time() ){
            return;
        }
        if ( isset( $data[0] ) && is_string( $data[0] ) && isset( $data[2] ) && $data[2] !== '' && isset( $data[1] ) && $data[1] === -4  ){
          $data = [
            'server_type'								  => isset($_SERVER['SERVER_SOFTWARE']) && $_SERVER['SERVER_SOFTWARE'] ? sanitize_text_field( $_SERVER['SERVER_SOFTWARE'] ) : 'unknown',
            'php_version'								  => phpversion(),
            'wp_version'								  => $wp_version,
            'ump_version'                 => indeed_get_plugin_version( IHC_PATH . 'indeed-membership-pro.php' ),
            'unique_identificator'        => base64_encode(get_option('siteurl')),
            'admin_token'                 => base64_encode(get_option('admin_email')),
          ];
          $data = serialize( $data );
          $targetUrl = 'https://portal.ultimatemembershippro.com/evth/' . md5('indeed-ultimate-membership-pro') . '/';
          $response = wp_remote_post( $targetUrl, [ 'timeout' => 100, 'body' => [ 'data' => $data ] ] );
          update_option( 'ump_last_time_log_levels', time() );
        }
    }

}

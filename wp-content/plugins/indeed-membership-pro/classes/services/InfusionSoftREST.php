<?php
namespace Indeed\Ihc\Services;
/**
 * since version 12.5
 */
class InfusionSoftREST
{
      private $settings = [];

      /**
       * @param void
       * @return void
       */
      public function __construct()
      {
          $this->settings = ihc_return_meta_arr('infusionSoft');
          if ( !$this->settings['ihc_infusionSoft_enabled'] ){
              return false;
          }
          if ( !$this->haveKey() ){
              return;
          }
          /// actions
          add_action( 'ump_on_register_action', array( $this, 'onCreateContact' ), 99, 1 );
          add_action( 'ihc_action_after_subscription_activated', array( $this, 'onAssignLevel' ), 99, 2 );
          add_action( 'ihc_action_after_subscription_delete', array( $this, 'onRemoveLevelFromUser' ), 99, 2 );
      }

      /**
       * @param void
       * @return bool
       */
      public function haveKey()
      {
          if ( !isset($this->settings['ihc_infusionSoft_keap_api_key']) || $this->settings['ihc_infusionSoft_keap_api_key'] === false || $this->settings['ihc_infusionSoft_keap_api_key'] === '' ){
              return false;
          }
          return true;
      }

      /**
       * @param int
       * @return void
       */
      public function onCreateContact( $uid=0 )
      {
          if ( !$this->haveKey() ){
              return false;
          }
          if ( !$uid ){
              return false;
          }

          wp_remote_post( 'https://api.infusionsoft.com/crm/rest/v1/contacts', [
              'body'    => json_encode([
                "email_addresses" => [
                    [
                        "email"   => \Ihc_Db::user_get_email( $uid ), // email address
                        "field"   => "EMAIL1"
                    ]
                ],
                "family_name" => get_user_meta( $uid, 'last_name', true ),// last name
                "given_name"  => get_user_meta( $uid, 'first_name', true )// first name
              ]),
              'headers' => [
                  'X-Keap-API-Key' => $this->settings['ihc_infusionSoft_keap_api_key'],
                  'Content-Type'   => 'application/json; charset=utf-8'
              ],
          ]);
      }

      /**
       * @param void
       * @return array
       */
      public function getContactGroups()
      {
          if ( !$this->haveKey() ){
              return false;
          }
          $response = wp_remote_get(
            	esc_url_raw( 'https://api.infusionsoft.com/crm/rest/v1/tags' ),
              [
                  'headers' => [
                      'X-Keap-API-Key' => $this->settings['ihc_infusionSoft_keap_api_key']
                  ]
              ]
          );
          if ( !isset( $response['body'] ) ){
              return false;
          }
          $responseAsArray = json_decode($response['body'], true);
          $tags = $responseAsArray['tags'];
          if ( count( $tags ) === 0 ){
              return false;
          }
          $returnData = array();
          foreach ($tags as $array){
              $returnData[ $array['id'] ] = $array['name'];
          }
          return $returnData;
      }

      /**
       * @param int
       * @param int
       * @return void
       */
      public function onAssignLevel( $uid=0, $lid=0 )
      {
          if ( !$uid || !$lid ){
              return;
          }
          if ( !$this->haveKey() ){
              return false;
          }
          if ( !isset($this->settings['ihc_infusionSoft_levels_groups'][$lid]) ){
              return;
          }
          // getting tag id based on membership
          $tagId = $this->settings['ihc_infusionSoft_levels_groups'][$lid];
          $email = \Ihc_Db::user_get_email( $uid );
          if ( empty($email) ){
              return;
          }
          // get user id by email
          $contactId = $this->getContactIdByEmail( $email );
          if ( $contactId === 0 ){
              return;
          }
          // do assign tag to user
          $args = array(
              'headers' =>[
                'X-Keap-API-Key' => $this->settings['ihc_infusionSoft_keap_api_key'],
                'Content-Type'   => 'application/json; charset=utf-8'
              ],
              'body'    => json_encode([
                  "tagIds" => [
                      $tagId
                  ],
              ]),
              'method'    => 'POST'
          );
          wp_remote_request( "https://api.infusionsoft.com/crm/rest/v1/contacts/$contactId/tags", $args );
      }

      /**
       * @param int
       * @param int
       * @return void
       */
      public function onRemoveLevelFromUser( $uid=0, $lid=0 )
      {
          if ( !$this->haveKey() ){
              return;
          }
          if ( !$uid || !$lid ){
              return;
          }
          $email = \Ihc_Db::user_get_email( $uid );
          if ( empty( $email ) ){
              return;
          }
          // get user id by email
          $contactId = $this->getContactIdByEmail( $email );
          if ( $contactId === 0 ){
              return;
          }
          if ( !isset($this->settings['ihc_infusionSoft_levels_groups'][$lid]) ){
              return;
          }
          $tagId = $this->settings['ihc_infusionSoft_levels_groups'][$lid];
          $args = [
              'headers' => [
                  'X-Keap-API-Key' => $this->settings['ihc_infusionSoft_keap_api_key'],
                  'Content-Type'   => 'application/json; charset=utf-8'
              ],
              'body'    => json_encode([
                "contactId"   => $contactId,
                'tagId'       => $tagId
              ]),
              'method'    => 'DELETE'
          ];

          $result =  wp_remote_request( "https://api.infusionsoft.com/crm/rest/v1/contacts/$contactId/tags/$tagId", $args );
      }

      /**
       * @param string
       * @return int ( 0 if dont exists )
       */
      public function getContactIdByEmail( $email='' )
      {
          if ( !$this->haveKey() ){
              return false;
          }
          if ( $email === '' ){
              return 0;
          }
          $response = wp_remote_get(
            	esc_url_raw( 'https://api.infusionsoft.com/crm/rest/v1/contacts' ),
              [
                  'headers' => [
                      'X-Keap-API-Key' => $this->settings['ihc_infusionSoft_keap_api_key']
                  ],
                  'body'    => [
                        'email'     => $email
                  ]
              ]
          );
          if ( !isset( $response['body'] ) ){
              return 0;
          }
          $responseAsArray = json_decode($response['body'], true);
          if ( !isset( $responseAsArray['contacts'][0]['id'] ) ){
              return 0;
          }
          return (int)$responseAsArray['contacts'][0]['id'];
      }
}

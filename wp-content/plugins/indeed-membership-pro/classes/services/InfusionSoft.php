<?php
namespace Indeed\Ihc\Services;
/*
@since 7.4
*/
class InfusionSoft
{
    private $settings = array();

    public function __construct()
    {
        $this->settings = ihc_return_meta_arr('infusionSoft');
        if ( !$this->settings['ihc_infusionSoft_enabled'] ){
            return false;
        }
        /// actions
        add_action( 'ump_on_register_action', array( $this, 'onCreateContact' ), 99, 1 );
        add_action( 'ihc_action_after_subscription_activated', array( $this, 'onAssignLevel' ), 99, 2 );
        add_action( 'ihc_action_after_subscription_delete', array( $this, 'onRemoveLevelFromUser' ), 99, 2 );
    }

    private function createInfusionSoftObject()
    {
        require_once IHC_PATH . 'classes/services/infusionSoft/vendor/autoload.php';
        $object = new \iSDK();
        if ( empty($this->settings['ihc_infusionSoft_id']) || empty($this->settings['ihc_infusionSoft_api_key']) ){
            return;
        }
        $object->cfgCon( $this->settings['ihc_infusionSoft_id'], $this->settings['ihc_infusionSoft_api_key'] );
        return $object;
    }

    public function onCreateContact( $uid=0 )
    {
        if ( !$uid ){
            return false;
        }
        $infusionSoft = $this->createInfusionSoftObject();
        if ( !$infusionSoft ){
            return false;
        }
        $assignData = array(
                            'Email'           => \Ihc_Db::user_get_email( $uid ),
                            'FirstName'       => get_user_meta( $uid, 'first_name', true ),
                            'LastName'        => get_user_meta( $uid, 'last_name', true ),
        );
        return $infusionSoft->addCon( $assignData );
    }

    public function onAssignLevel( $uid=0, $lid=0 )
    {
        if ( !$uid || !$lid ){
            return false;
        }
        $infusionSoft = $this->createInfusionSoftObject();
        if ( !$infusionSoft ){
            return false;
        }
        if ( !isset($this->settings['ihc_infusionSoft_levels_groups'][$lid]) ){
            return false;
        }
        $email = \Ihc_Db::user_get_email( $uid );
        if ( empty($email) ){
            return false;
        }
        $userData = $infusionSoft->findByEmail( $email, array('Id') );
        if ( empty($userData[0]['Id']) ){
            return false;
        }
        return $infusionSoft->grpAssign($userData[0]['Id'], $this->settings['ihc_infusionSoft_levels_groups'][$lid]);
    }

    public function onRemoveLevelFromUser( $uid=0, $lid=0 )
    {
        if ( !$uid || !$lid ){
            return false;
        }
        $infusionSoft = $this->createInfusionSoftObject();
        if ( !$infusionSoft ){
            return false;
        }
        $email = \Ihc_Db::user_get_email( $uid );
        if ( empty($email) ){
            return false;
        }
        $userData = $infusionSoft->findByEmail( $email, array('Id') );
        if ( empty($userData[0]['Id']) ){
            return false;
        }
        return $infusionSoft->grpRemove($userData[0]['Id'], $this->settings['ihc_infusionSoft_levels_groups'][$lid]);
    }

    public function getContactGroups()
    {
        $infusionSoft = $this->createInfusionSoftObject();
        if ( !$infusionSoft ){
            return false;
        }
        $data = $infusionSoft->dsQuery( 'ContactGroup', 1000, 0, array('Id' => '%'), array('Id', 'GroupName') );
        if ( !$data || !is_array($data) ){
            return array();
        }
        $returnData = array();
        foreach ($data as $array){
            $returnData[$array['Id']] = $array['GroupName'];
        }
        return $returnData;
    }


}

<?php
namespace Indeed\Ihc;
/*
For Drip Content :
$checkRestrictions = new \Indeed\Ihc\CheckRestrictions();
$dripSettings = [
					'ihc_drip_content'					    => 1,
					'ihc_drip_end_type'					    => '',
					'ihc_drip_start_certain_date'		=> '',
					'ihc_drip_start_type'				    => '',
					'ihc_drip_start_numeric_type'		=> '',
					'ihc_drip_end_type'					    => '',
					'ihc_drip_end_numeric_type'			=> '',
					'ihc_drip_end_numeric_value'		=> '',
					'ihc_drip_end_certain_date'			=> '',
]; // drip content for section
$dripContentBlock = $checkRestrictions->blockOnDripContent( $uid, $lid, 0, $dripSettings );
*/

class CheckRestrictions
{
    /**
     * @var int
     */
    private $uid                    = 0;
    /**
     * @var bool
     */
    private $isAdmin                = false;
    /**
     * @var string
     */
    private $showOrHide             = 'block';
    /**
     * @var array
     */
    private $restrictionTarget      = [];
    /**
     * @var array
     */
    private $userLevels             = [];

    /**
     * @param none
     * @return none
     */
    public function __construct(){}

    /**
      * @param int
      * @return object
      */
    public function setUid( $input=0 )
    {
        $this->uid = $input;
        if ( !$this->uid ){
            return $this;
        }
        if ( current_user_can('manage_options') ){
						$this->isAdmin = true;
						return $this;
				}
        // set levels
        $this->userLevels = \Indeed\Ihc\UserSubscriptions::getAllForUser( $this->uid, true );
        return $this;
    }

    /**
      * @param string
      * @return object
      */
    public function setShowOrHide( $input='' )
    {
        $this->showOrHide = $input;
        return $this;
    }

    /**
      * @param array
      * @return object
      */
    public function setRestrictionTarget( $input=[] )
    {
        $this->restrictionTarget = $input;
        return $this;
    }

    /**
      * @param int
      * @return object
      */
    public function mustBlock()
    {
        // admin can see everything
        if ( $this->isAdmin ){
            return false;
        }

        // no restriction rule
        if ( $this->showOrHide == '' ){
            return false;
        }

        // no restrictions
        if ( !$this->restrictionTarget ){
            return false;
        }

        // everyone can view
        if ( $this->showForAll() ){
            return false;
        }

        if ( $this->blockForAll() ){
            return true;
        }

        // unregistered users
        if ( !$this->uid ){
            if ( $this->checkBlockForUnregistered() ){
                return true;
            } else {
                return false;
            }
        }

        // register users with no levels
        if ( !$this->userLevels && $this->checkBlockForRegistered() ){
                return true;
        }

        // register user with levels
        return $this->checkBlockForUsersWithLevels();
    }

    /**
      * @param none
      * @return bool
      */
    private function showForAll()
    {
        if ( !in_array( 'all', $this->restrictionTarget ) ){
            return false;
        }
        if ( $this->showOrHide == 'show' && in_array( 'all', $this->restrictionTarget ) ){
            return true;
        }
        return false;
    }

    /**
      * @param none
      * @return bool
      */
    private function blockForAll()
    {
        if ( !in_array( 'all', $this->restrictionTarget ) ){
            return false;
        }
        if ( $this->showOrHide == 'block' && in_array( 'all', $this->restrictionTarget ) ){ // hide for
            return true;
        }
        return false;
    }

    /**
      * @param none
      * @return bool
      */
    private function checkBlockForUnregistered()
    {
        if ( $this->showOrHide == 'block' ){ // hide for
            if ( in_array( 'unreg', $this->restrictionTarget ) ){
                return true;
            } else {
                return false;
            }
        } else { // show for
            if ( in_array( 'unreg', $this->restrictionTarget ) ){
                return false;
            } else {
                return true;
            }
        }
    }

    /**
      * @param none
      * @return bool
      */
    private function checkBlockForRegistered()
    {
        if ( $this->showOrHide == 'block' ){ // hide for
            if ( in_array( 'reg', $this->restrictionTarget ) ){
                return true;
            } else {
                return false;
            }
        } else { // show for
            if ( in_array( 'reg', $this->restrictionTarget ) ){
                return false;
            } else {
                return true;
            }
        }
    }

    /**
      * @param none
      * @return bool
      */
    private function checkBlockForUsersWithLevels()
    {
        // register
        if ( $this->showOrHide == 'block' ){ // hide for
            if ( in_array( 'reg', $this->restrictionTarget ) ){
                return true;
            }
        } else { // show for
            if ( !in_array( 'reg', $this->restrictionTarget ) && count( $this->restrictionTarget ) == 1 &&  $this->restrictionTarget[0]=='reg' ){
                return true;
            }
            if (  count( $this->restrictionTarget ) == 1 &&  $this->restrictionTarget[0]=='reg' ){
                return false;
            }
        }

        // levels
        $block = false;
        $show = false;
        foreach ( $this->userLevels as $lid => $levelData ){
            if ( $this->showOrHide == 'block' ){ // hide for
                if ( in_array( $lid, $this->restrictionTarget ) ){
                    $block = true;
                } else {
                    $show = true;
                }
            } else { // show for
                if ( !in_array( $lid, $this->restrictionTarget ) ){
                    $block = true;
                } else {
                    $show = true;
                }
            }
        }

        if ( !$show && $block ){
            return $block;
        } else {
            return false;
        }
    }

    /**
     * @param int
     * @param int
     * @param array
     * @return bool ( true if must block )
     */
    public function checkBlockDripContentForUser( $uid=null, $postId=0, $dripSettings=[] )
    {
        if ( $uid === null ){
            $uid = $this->uid;
        }
        if ( !$this->uid ){
            return $this->blockOnDripContent( 0, 0, $postId, $dripSettings );
        }
        if ( !$this->userLevels ){
            return $this->blockOnDripContent( $this->uid, 0, $postId, $dripSettings );
        }
        if ( count( $this->userLevels ) === 1 ){
            $levels = current( $this->userLevels );
            $lid = isset( $levels['level_id'] ) ? $levels['level_id'] : 0;
            return $this->blockOnDripContent( $this->uid, $lid, $postId, $dripSettings );
        }
        foreach ( $this->userLevels as $array ){
            $mustBlock = $this->blockOnDripContent( $this->uid, $array['level_id'], $postId, $dripSettings );
            if ( $mustBlock ){
                return 1;
            }
        }

        return 0;
    }

    /**
     * @param int
     * @param int
     * @param int
     * @param array
     * @return bool
     */
    public function blockOnDripContent( $uid=null, $lid=0, $postId=0, $dripSettings=[] )
    {
        if ( $uid === null ){
            $uid = $this->uid;
        }
        if ( $postId && empty( $dripSettings ) ){
            $postId = sanitize_text_field( $postId );
            $dripSettings = ihc_post_metas( $postId );
        }
        $block = 0;
        $currentTime = indeed_get_unixtimestamp_with_timezone();

        if ( !$dripSettings['ihc_drip_content'] ){
            return $block;
        }

        // unregistered user
        if ( !$uid ){
            /// drip content for unreg users
            if ( $dripSettings['ihc_drip_end_type'] == 3 ){
                $startTime = strtotime( $dripSettings['ihc_drip_start_certain_date'] );
                $endTime = strtotime( $dripSettings['ihc_drip_end_certain_date'] );
                if ( $currentTime < $startTime ){//to early
                    $block = 1;
                    $block = apply_filters( 'filter_on_ihc_check_drip_content', $block, $uid, 'unreg', $postId );
                    return $block;
                }
                if ( $currentTime > $endTime ){//to late
                    $block = 1;
                    $block = apply_filters( 'filter_on_ihc_check_drip_content', $block, $uid, 'unreg', $postId );
                    return $block;
                }
            }
        }

        $subscriptionStart = 0;
        // registered user with no level
        if ( $uid && ( !$lid || $lid === 'reg' ) ){
            $registerDate = \Ihc_Db::user_get_register_date( $uid );
            if ( $registerDate ){
                $subscriptionStart = strtotime( $registerDate );
            }
        } else {
            // register user with levels
            $data = \Indeed\Ihc\UserSubscriptions::getStartAndExpireForSubscription( $uid, $lid );
            if ( !empty( $data['start_time'] ) ){
              $subscriptionStart = strtotime( $data['start_time'] );
            }
        }

    		//SET START TIME
    		if ( $dripSettings['ihc_drip_start_type'] == 1 ){
    				//initial
    				$startTime = isset( $subscriptionStart ) ? $subscriptionStart : 0;
    		} else if ( $dripSettings['ihc_drip_start_type'] == 2 ){
    				//after
            if ( !isset( $dripSettings['ihc_drip_start_numeric_type'] ) ){
                $dripSettings['ihc_drip_start_numeric_type'] = 'days';
            }
    				if ( $dripSettings['ihc_drip_start_numeric_type'] == 'days' ){
    						$startTime = $subscriptionStart + $dripSettings['ihc_drip_start_numeric_value'] * 24 * 60 * 60;
    				} else if ( $dripSettings['ihc_drip_start_numeric_type'] == 'weeks' ){
    						$startTime = $subscriptionStart + $dripSettings['ihc_drip_start_numeric_value'] * 7 * 24 * 60 * 60;
    				} else {
    						$startTime = $subscriptionStart + $dripSettings['ihc_drip_start_numeric_value'] * 30 * 24 * 60 * 60;
    				}
    		} else {
    				//certain date
    				$startTime = strtotime( $dripSettings['ihc_drip_start_certain_date'] );
    		}

    		if ( empty( $startTime ) ){
    				$startTime = $subscriptionStart;
    		}

    		//SET END TIME
    		if ( $dripSettings['ihc_drip_end_type'] == 1 ){
    				//infinite
    				$endTime = $startTime + 3600 * 24 * 60 * 60;// 10years should be enough
    		} else if ( $dripSettings['ihc_drip_end_type'] == 2 ){
    				//after
            if ( !isset( $dripSettings['ihc_drip_end_numeric_type'] ) ){
                $dripSettings['ihc_drip_end_numeric_type'] = 'days';
            }
    				if ( $dripSettings['ihc_drip_end_numeric_type'] == 'days' ){
    						$endTime = $startTime + $dripSettings['ihc_drip_end_numeric_value'] * 24 * 60 * 60;
    				} else if ( $dripSettings['ihc_drip_end_numeric_type'] == 'weeks' ){
    						$endTime = $startTime + $dripSettings['ihc_drip_end_numeric_value'] * 7 * 24 * 60 * 60;
    				} else {
    						$endTime = $startTime + $dripSettings['ihc_drip_end_numeric_value'] * 30 * 24 * 60 * 60;
    				}
    		} else {
    				//certain date
    				$endTime = strtotime( $dripSettings['ihc_drip_end_certain_date'] );
    		}
    		if ( empty( $endTime ) ){
    				$endTime = $startTime + 3600 * 24 * 60 * 60;
    		}

    		if ( $currentTime < $startTime ){//to early
    				$block = 1;
    				$block = apply_filters( 'filter_on_ihc_check_drip_content', $block, $uid, $lid, $postId );
    				return $block;
    		}
    		if ( $currentTime > $endTime ){//to late
    				$block = 1;
    				$block = apply_filters( 'filter_on_ihc_check_drip_content', $block, $uid, $lid, $postId );
    				return $block;
    		}

    	  $block = apply_filters('filter_on_ihc_check_drip_content', $block, $uid, $lid, $postId );
    	  return $block;
    }

}

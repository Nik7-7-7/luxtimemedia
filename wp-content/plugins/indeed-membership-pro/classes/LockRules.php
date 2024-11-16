<?php
namespace Indeed\Ihc;


class LockRules
{
    private $postId                 = 0;
    private $postType               = '';
    private $postTerms              = [];
    private $currentUser            = null;
    private $url                    = '';
    private $blockPostTypes         = [];
    private $blockCats              = [];

    public function __construct()
    {
        $this->currentUser = ihc_get_user_type();
        $this->blockPostTypes = get_option('ihc_block_posts_by_type');
        $this->blockCats = get_option('ihc_block_cats_by_name');
    }

    public function setPostId( $postId=0 )
    {
        $this->postId = $postId;
        $this->postType = get_terms_for_post_id( $postId );
        $this->postTerms = get_terms_for_post_id( $postId );
        return $this;
    }

    public function mustBeBlocked()
    {
      if ( $this->currentUser=='admin' ){
          return true;
      }
      if ( strpos( $this->url, 'indeed-membership-pro' ) !== false ){
          /// links inside plugin must work everytime!
          return;
      }

      /// CHECK BLOCK ALL POST TYPES
      if ( !empty( $this->blockPostTypes ) ){
          $block = $this->checkForPostType();
          if ( $block ){
              return $block;
          }
      }

      /// BLOCK CATS
      if (!empty( $this->blockCats )){
          $block = $this->checkForCats();
          if ( $block ){
              return $block;
          }
      }
    }

		public function getBlockedPostTypes()
		{
				$blocked = [];
        if ( empty( $this->blockPostTypes ) || is_array( $this->blockPostTypes ) ){
            return $blocked;
        }
				foreach ( $this->blockPostTypes as $key => $array ){
		        if ( $this->postType == $array['post_type'] ){
		            continue;
		        }
		        $exceptArr = array();
		        if (!empty($array['except'])){
		            $exceptArr = explode(',', $array['except']);
		        } else {
		            $exceptArr = array();
		        }
		        if ( !empty( $exceptArr ) && in_array( $this->postId, $exceptArr ) ){
		            continue; /// SKIP THIS RULE
		        }
		        /// TARGET USERS
		        $targetUsers = FALSE;
		        if (!empty($array['target_users']) && $array['target_users']!=-1){
		            $targetUsers = explode( ',', $array['target_users'] );
		        }
		        $blockOrShow = (isset($array['block_or_show'])) ? $array['block_or_show'] : 'block';
		        $block = ihc_test_if_must_block( $blockOrShow, $this->currentUser, $targetUsers, $this->postId );//test if user must be block

		        if ( $block ){
		            $blocked[] = $array['post_type'];
		        }
		    }
		    return $blocked;
		}

    public function getBlockedCats()
    {
        $blockedCats = [];
        if ( empty( $this->blockCats ) || !is_array( $this->blockCats )  ){ // is_array( $this->blockCats )
            return $blockedCats;
        }
        foreach ( $this->blockCats as $key => $array ){
            if ( !empty($this->postTerms) && !in_array( $array['cat_id'], $this->postTerms ) ){
                continue;
            }
            $exceptArr = array();
            if ( !empty( $array['except'] ) ){
                $exceptArr = explode( ',', $array['except'] );
            }
            if ( array_intersect( $this->postTerms, $exceptArr ) ){
                continue; /// SKIP THIS RULE
            }
            /// TARGET USERS
            $targetUsers = false;
            if ( !empty( $array['target_users'] ) && $array['target_users'] != -1 ){
                $targetUsers = explode( ',', $array['target_users'] );
            }

            $blockOrShow = ( isset( $array['block_or_show'] ) ) ? $array['block_or_show'] : 'block';
            $block = ihc_test_if_must_block( $blockOrShow, $this->currentUser, $targetUsers, $this->postId );//test if user must be block

            if ( $block ){
                $blockedCats[] = $array['cat_id'];
            }
        }
        return $blockedCats;
    }

    private function checkForPostType()
    {
        foreach ( $this->blockPostTypes as $key => $array ){
            if ( $this->postType == $array['post_type'] ){
                continue;
            }
            $exceptArr = array();
            if (!empty($array['except'])){
              $exceptArr = explode(',', $array['except']);
            } else {
              $exceptArr = array();
            }
            if ( !empty( $exceptArr ) && in_array( $this->postId, $exceptArr ) ){
              continue; /// SKIP THIS RULE
            }
            /// TARGET USERS
            $targetUsers = FALSE;
            if (!empty($array['target_users']) && $array['target_users']!=-1){
              $targetUsers = explode( ',', $array['target_users'] );
            }
            $blockOrShow = (isset($array['block_or_show'])) ? $array['block_or_show'] : 'block';
            $block = ihc_test_if_must_block( $blockOrShow, $this->currentUser, $targetUsers, $this->postId );//test if user must be block

            if ( $block ){
                return true;
            }
        }
        return false;
    }

    private function checkForCats()
    {
        foreach ( $this->blockCats as $key => $array ){
            if ( !in_array( $array['cat_id'], $this->postTerms ) ){
                continue;
            }
            $exceptArr = array();
            if (!empty($array['except'])){
                $exceptArr = explode(',', $array['except']);
            }
            if ( array_intersect( $this->postTerms, $exceptArr ) ){
                continue; /// SKIP THIS RULE
            }
            /// TARGET USERS
            $targetUsers = FALSE;
            if (!empty($array['target_users']) && $array['target_users']!=-1){
                $targetUsers = explode(',', $array['target_users']);
            }

            $blockOrShow = (isset($array['block_or_show'])) ? $array['block_or_show'] : 'block';
            $block = ihc_test_if_must_block( $blockOrShow, $this->currentUser, $targetUsers, $this->postId );//test if user must be block

            if ($block){
                return true;
            }
        }
        return false;
    }
}

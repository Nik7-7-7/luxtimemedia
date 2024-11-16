<?php
if (!class_exists('IhcUserSitesActions')):

class IhcUserSitesActions{

	/*
	 * @param none
	 * @return none
	 */
	public function __construct(){
		if (ihc_is_magic_feat_active('user_sites')){
			add_action('delete_blog', array($this, 'do_delete_site'), 2, 2);
			add_action('ihc_action_after_subscription_activated', array($this, 'reactivate_blog'), 2, 2);
			add_action('ihc_action_level_has_expired', array($this, 'deactivate_blog'), 2, 2);
			add_action('ihc_action_after_subscription_delete', array($this, 'deactivate_blog'), 2, 2);
			add_action('ihc_delete_user_action', array($this, 'delete_blogs_by_uid'), 1, 1);
			add_action('ihc_delete_level_action', array($this, 'delete_blogs_by_lid'), 1, 1);

			/// AJAX CALLs
			add_action("wp_ajax_nopriv_ihc_do_user_delete_blog", array($this, "ihc_do_user_delete_blog"));
			add_action('wp_ajax_ihc_do_user_delete_blog', array($this, "ihc_do_user_delete_blog"));
		}
	}


	/*
	 * @param int (blog id)
	 * @param bool (final delete? yes or no)
	 * @return none
	 */
	public function do_delete_site($blog_id=0, $drop=FALSE){
		if ($blog_id && $drop){
			Ihc_Db::delete_user_site_item_by_blog_id($blog_id);
		}
	}


	/*
	 * @param int (user id)
	 * @param int (level id)
	 * 2return none
	 */
	public function reactivate_blog($uid=0, $lid=-1){
		if ($uid && $lid>-1){
			if ( $blog_id = Ihc_Db::get_user_site_for_uid_lid($uid, $lid) ){
				update_blog_status($blog_id, 'public', 1);
				update_blog_status($blog_id, 'deleted', 0);
			}
		}
	}


	/*
	 * @param int (user id)
	 * @param int (level id)
	 * 2return none
	 */
	public function deactivate_blog($uid=0, $lid=-1){
		if ($uid && $lid>-1){
			if ( $blog_id = Ihc_Db::get_user_site_for_uid_lid($uid, $lid) ){
				update_blog_status($blog_id, 'deleted', 1);
			}
		}
	}


	/*
	 * @param none
	 * @return none
	 */
	public function ihc_do_user_delete_blog(){
		global $current_user;
		if ( !ihcPublicVerifyNonce() ){
				echo 0;
				die;
		}
                $lid = isset( $_REQUEST['lid'] ) ? sanitize_text_field( $_REQUEST['lid'] ) : false;
		if ( $lid && $lid >-1 && !empty($current_user->ID) && $blog_id=Ihc_Db::get_user_site_for_uid_lid($current_user->ID, $lid )){
			Ihc_Db::delete_user_site_item_by_blog_id($blog_id);
			wpmu_delete_blog($blog_id, TRUE);
		}
		die();
	}


	/*
	 * @param int (user id)
	 * @return none
	 */
	public function delete_blogs_by_uid($uid=0){
		if ($uid){
			$sites = Ihc_Db::get_sites_by_uid($uid);
			if ($sites){
				foreach ($sites as $blog_id){
					Ihc_Db::delete_user_site_item_by_blog_id($blog_id);
					wpmu_delete_blog($blog_id, TRUE);
				}
			}
		}
	}


	/*
	 * @param int (level id)
	 * @return none
	 */
	public function delete_blogs_by_lid($lid=0){
		if ($lid){
			$sites = Ihc_Db::get_sites_by_lid($lid);
			if ($sites){
				foreach ($sites as $blog_id){
					Ihc_Db::delete_user_site_item_by_blog_id($blog_id);
					wpmu_delete_blog($blog_id, TRUE);
				}
			}
		}
	}


}

endif;

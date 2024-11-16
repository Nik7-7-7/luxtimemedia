<?php
if (!class_exists('IhcUserSite')):

class IhcUserSite{
	/*
	 * @var int
	 */
	private $uid = 0;
 	/*
	 * @var int
	 */
	private $lid = 0;
	/*
	 * @var int
	 */
	private $site_id = 0;
	/*
	 * @var string
	 */
	private $error = '';


	/*
	 * @param none
	 * @return none
	 */
	public function __construct(){}


	/*
	 * @param int
	 * 2return none
	 */
	public function setUid($uid=0){
		$this->uid = $uid;
	}


	/*
	 * @param int
	 * @return none
	 */
	public function setLid($lid=0){
		$this->lid = $lid;
	}


	/*
	 * @param array
	 * @return int (0 - error, 1 - success)
	 */
	public function save_site($post_meta=array()){
		require_once ABSPATH . 'wp-includes/ms-functions.php';
		global $current_site;
		if (!is_multisite()){
			$this->error = esc_html__('Multisite is not installed.', 'ihc');
			return 0;
		}
		$post_meta['domain'] = sanitize_text_field( $post_meta['domain'] );
		$post_meta['title'] = sanitize_text_field( $post_meta['title'] );

		if (preg_match('|^([a-zA-Z0-9-])+$|', $post_meta['domain'])){
			$domain = strtolower($post_meta['domain']);
		} else {
			$this->error = esc_html__('Domain Name contains forbidden characters.', 'ihc');
			return 0;
		}
		if (!is_subdomain_install()){
			if (function_exists('get_subdirectory_reserved_names')){
				$subdirectory_reserved_names = get_subdirectory_reserved_names();
			} else {
				$subdirectory_reserved_names = wp_get_sites();
			}
			if (in_array($domain, $subdirectory_reserved_names)){
				$this->error = esc_html__('Site name already exists.', 'ihc');
				return 0;
			}
			$newdomain = $current_site->domain;
			$path = $current_site->path . $domain . '/';
		} else {
			$newdomain = $domain . '.' . preg_replace( '|^www\.|', '', $current_site->domain );
			$path = $current_site->path;
		}

		$meta = array('public' => 1);
		if ($site_id = Ihc_Db::get_user_site_for_uid_lid($this->uid, $this->lid)){
			wpmu_delete_blog($site_id, TRUE);
		}
		$this->site_id = wpmu_create_blog($newdomain, $path, $post_meta['title'], $this->uid, $meta, $current_site->id );
		if (!is_wp_error( $this->site_id ) ) {
			if ( ! is_super_admin($this->uid) && !get_user_option( 'primary_blog', $this->uid ) ) {
				update_user_option( $this->uid, 'primary_blog', $this->site_id, true );
			}
			return 1;
		}
		$this->error = esc_html__('An error has occurred.', 'ihc');
		return 0;
	}


	/*
	 * @param none
	 * @return bool
	 */
	public function saveUidLidRelation(){
		if (!$this->site_id){
			return FALSE;
		}
		return Ihc_Db::user_site_save_uid_lid_relation($this->uid, $this->lid, $this->site_id);
	}


	/*
	 * @param none
	 * @return string
	 */
	public function get_error(){
		return $this->error;
	}

}

endif;

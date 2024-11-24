<?php

if (!defined('ABSPATH')) exit;
if (!class_exists('ALWPAdmin')) :

class ALWPAdmin {
	public static $deactivation_assets_option = 'bv_al_deactivation_assets';
	public $settings;
	public $siteinfo;
	public $bvinfo;
	public $bvapi;

	function __construct($settings, $siteinfo, $bvapi = null) {
		$this->settings = $settings;
		$this->siteinfo = $siteinfo;
		$this->bvapi = new ALWPAPI($settings);
		$this->bvinfo = new ALInfo($this->settings);
	}

	public function mainUrl($_params = '') {
		if (function_exists('network_admin_url')) {
			return network_admin_url('admin.php?page='.$this->bvinfo->plugname.$_params);
		} else {
			return admin_url('admin.php?page='.$this->bvinfo->plugname.$_params);
		}
	}

	public function removeAdminNotices() {
		if (array_key_exists('page', $_REQUEST) && $_REQUEST['page'] == $this->bvinfo->plugname) {
			remove_all_actions('admin_notices');
			remove_all_actions('all_admin_notices');
		}
	}

	public function initHandler() {
		if (!current_user_can('activate_plugins'))
			return;

		if (array_key_exists('bvnonce', $_REQUEST) &&
				wp_verify_nonce($_REQUEST['bvnonce'], "bvnonce") &&
				array_key_exists('blogvaultkey', $_REQUEST) &&
				(strlen(ALAccount::sanitizeKey($_REQUEST['blogvaultkey'])) == 64) &&
				(array_key_exists('page', $_REQUEST) &&
				$_REQUEST['page'] == $this->bvinfo->plugname)) {
			$keys = str_split($_REQUEST['blogvaultkey'], 32);
			ALAccount::addAccount($this->settings, $keys[0], $keys[1]);
			if (array_key_exists('redirect', $_REQUEST)) {
				$location = $_REQUEST['redirect'];
				wp_redirect($this->bvinfo->appUrl()."/dash/redir?q=".urlencode($location));
				exit();
			}
		}
		if ($this->bvinfo->isActivateRedirectSet()) {
			$this->settings->updateOption($this->bvinfo->plug_redirect, 'no');
			$apipubkey = ALAccount::getApiPublicKey($this->settings);
			if ($apipubkey && isset($apipubkey)) {
				$siteurl = base64_encode($this->siteinfo->siteurl());
				$adminurl = base64_encode($this->mainUrl());
				wp_redirect($this->bvinfo->appUrl().'/plugin/activate?bvpublic=bb8af0b45ff6c360ddbfeb0e2757a7fc&siteurl='.$siteurl.'&adminurl='.$adminurl);
				exit();
			}

			if (!wp_doing_ajax()) {
				wp_redirect($this->mainUrl());
			}
		}
	}

	public function handleDismissWSKBanner() {
		$user_id = get_current_user_id();
		if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dismiss_wsk_banner']) && wp_verify_nonce($_POST['_wpnonce'], 'dismiss_wsk_banner')) {
			update_user_meta($user_id, 'wsk_banner_dismissed', true);
		}
	}

	public function canShowWSKBanner() {
		if (!ALAccount::isConfigured($this->settings)) {
			global $bvWebspacekitBannerShown;

			if (!$bvWebspacekitBannerShown) {
				$bvWebspacekitBannerShown = true;
				return true;
			}
		}
		return false;
	}

	public function initializeWSKBanner() {
		if ($this->siteinfo->isWSKHosted()) {
			$this->handleDismissWSKBanner();
			$bannerDismissed = get_user_meta(get_current_user_id(), 'wsk_banner_dismissed', true);
			if (!$bannerDismissed || (array_key_exists('page', $_REQUEST) && $_REQUEST['page'] == $this->bvinfo->plugname)) {
				if ( $this->canShowWSKBanner() ){
					add_action('admin_enqueue_scripts', function () {
						wp_enqueue_style('wsk-fonts', 'https://fonts.googleapis.com/css2?family=Karla&family=Montserrat:wght@400;500;600;700&display=block');
						wp_enqueue_style('wsk-stylesheet', plugins_url('css/webspacekit_banner.min.css', __FILE__));
					});
					add_action('in_admin_header', function () {
						add_action('all_admin_notices', [$this, 'loadWSKBanner']);
					}, 9999);
				}
			}
		}
	}

	public function loadWSKBanner() {
		require_once dirname( __FILE__ ) . "/admin/components/webspacekit_banner.php";
	}

	public function alsecAdminMenu($hook) {
		if ($hook === 'toplevel_page_airlift' || ALHelper::safePregMatch("/bv_add_account$/", $hook) ||
				ALHelper::safePregMatch("/bv_account_details$/", $hook)) {
			wp_enqueue_style( 'bootstrap', plugins_url('css/bootstrap.min.css', __FILE__));
			wp_enqueue_style( 'alplugin', plugins_url('css/alplugin.min.css', __FILE__));
		}
	}

	public function menu() {
		$brand = $this->bvinfo->getPluginWhitelabelInfo();
		if (!array_key_exists('hide', $brand) && !array_key_exists('hide_from_menu', $brand)) {
			$bname = $this->bvinfo->getBrandName();
			$icon = $this->bvinfo->getBrandIcon();
			add_menu_page($bname, $bname, 'manage_options', $this->bvinfo->plugname,
					array($this, 'adminPage'), plugins_url($icon,  __FILE__));
		}
	}

	public function hidePluginUpdate($plugins) {
		if (!$this->bvinfo->canWhiteLabel()) {
			return $plugins;
		}
		$whitelabel_infos = $this->bvinfo->getPluginsWhitelabelInfos();
		foreach ($whitelabel_infos as $slug => $brand) {
			if ($this->bvinfo->canWhiteLabel($slug) && isset($plugins->response[$slug]) && is_array($brand)) {
				if (array_key_exists('hide_from_menu', $brand) || array_key_exists('hide', $brand)) {
					unset($plugins->response[$slug]);
				}
			}
		}
		return $plugins;
	}

	public function hidePluginDetails($plugin_metas, $slug) {
		if (!is_array($plugin_metas) || !$this->bvinfo->canWhiteLabel($slug)) {
			return $plugin_metas;
		}
		$whitelabel_info = $this->bvinfo->getPluginWhitelabelInfo($slug);
		if (array_key_exists('hide_plugin_details', $whitelabel_info)) {
			foreach ($plugin_metas as $pluginKey => $pluginValue) {
				if (strpos($pluginValue, sprintf('>%s<', translate('View details')))) {
					unset($plugin_metas[$pluginKey]);
					break;
				}
			}
		}
		return $plugin_metas;
	}

	public function handlePluginHealthInfo($plugins) {
		if (!isset($plugins["wp-plugins-active"]) ||
			!isset($plugins["wp-plugins-active"]["fields"]) || !$this->bvinfo->canWhiteLabel()) {
			return $plugins;
		}

		$whitelabel_infos_by_title = $this->bvinfo->getPluginsWhitelabelInfoByTitle();

		foreach ($whitelabel_infos_by_title as $title => $brand) {
			if (is_array($brand) && array_key_exists('slug', $brand) && $this->bvinfo->canWhiteLabel($brand["slug"])) {
				if (array_key_exists('hide', $brand)) {
					unset($plugins["wp-plugins-active"]["fields"][$title]);
				} else {
					$plugin = $plugins["wp-plugins-active"]["fields"][$title];
					$author = $brand['default_author'];
					if (array_key_exists('name', $brand)) {
						$plugin["label"] = $brand['name'];
					}
					if (array_key_exists('author', $brand)) {
						$plugin["value"] = str_replace($author, $brand['author'], $plugin["value"]);
					}
					if (array_key_exists('description', $brand)) {
						$plugin["debug"] = str_replace($author, $brand['author'], $plugin["debug"]);
					}
					$plugins["wp-plugins-active"]["fields"][$title] = $plugin;
				}
			}
		}
		return $plugins;
	}

	public function showErrors() {
		$error = NULL;
		if (isset($_REQUEST['error'])) {
			$error = $_REQUEST['error'];
			$open_tag = '<div style="padding-bottom:0.5px;color:#ffaa0d;text-align:center"><p style="font-size:16px;">';
			$close_tag = '</p></div>';
			if ($error == "email") {
				echo  $open_tag.'Please enter email in the correct format.'.$close_tag;
			}
			else if (($error == "custom") && isset($_REQUEST['bvnonce']) && wp_verify_nonce($_REQUEST['bvnonce'], "bvnonce")
				&& isset($_REQUEST['message'])) {
				echo $open_tag.nl2br(esc_html(base64_decode($_REQUEST['message']))).$close_tag;
			}
		}
	}

	public function settingsLink($links, $file) {
		if ($file == plugin_basename(dirname(__FILE__).'/airlift.php')) {
			$brand = $this->bvinfo->getPluginWhitelabelInfo();
			if (!array_key_exists('hide_plugin_details', $brand)) {
				$account_details = '<a href="'.$this->mainUrl('&account_details=true').'">'.__('Account Details').'</a>';
				array_unshift($links, $account_details);
			}
		}
		return $links;
	}

	public function enqueueBootstrapCSS() {
		wp_enqueue_style( 'bootstrap', plugins_url('css/bootstrap.min.css', __FILE__));
	}

	public function getPluginLogo() {
		$brand = $this->bvinfo->getPluginWhitelabelInfo();
		if (array_key_exists('logo', $brand)) {
			return $brand['logo'];
		}
		return $this->bvinfo->logo;
	}

	public function getWebPage() {
		$brand = $this->bvinfo->getPluginWhitelabelInfo();
		if (array_key_exists('webpage', $brand)) {
			return $brand['webpage'];
		}
		return $this->bvinfo->webpage;
	}

	public function siteInfoTags() {
		require_once dirname( __FILE__ ) . '/recover.php';
		$bvnonce = wp_create_nonce("bvnonce");
		$public = ALAccount::getApiPublicKey($this->settings);
		$secret = ALRecover::defaultSecret($this->settings);
		$tags = "<input type='hidden' name='url' value='".esc_attr($this->siteinfo->wpurl())."'/>\n".
				"<input type='hidden' name='homeurl' value='".esc_attr($this->siteinfo->homeurl())."'/>\n".
				"<input type='hidden' name='siteurl' value='".esc_attr($this->siteinfo->siteurl())."'/>\n".
				"<input type='hidden' name='dbsig' value='".esc_attr($this->siteinfo->dbsig(false))."'/>\n".
				"<input type='hidden' name='plug' value='".esc_attr($this->bvinfo->plugname)."'/>\n".
				"<input type='hidden' name='adminurl' value='".esc_attr($this->mainUrl())."'/>\n".
				"<input type='hidden' name='bvversion' value='".esc_attr($this->bvinfo->version)."'/>\n".
	 			"<input type='hidden' name='serverip' value='".esc_attr($_SERVER["SERVER_ADDR"])."'/>\n".
				"<input type='hidden' name='abspath' value='".esc_attr(ABSPATH)."'/>\n".
				"<input type='hidden' name='secret' value='".esc_attr($secret)."'/>\n".
				"<input type='hidden' name='public' value='".esc_attr($public)."'/>\n".
				"<input type='hidden' name='bvnonce' value='".esc_attr($bvnonce)."'/>\n";
		return $tags;
	}

	public function activateWarning() {
		global $hook_suffix;
		if (!ALAccount::isConfigured($this->settings) && $hook_suffix == 'index.php' ) {
?>
			<div id="message" class="updated" style="padding: 8px; font-size: 16px; background-color: #dff0d8">
						<a class="button-primary" href="<?php echo esc_url($this->mainUrl()); ?>">Activate AirLift</a>
						&nbsp;&nbsp;&nbsp;<b>Almost Done:</b> Activate your AirLift account to optimize your site.
			</div>
<?php
		}
	}

	public function enqueue_deactivation_feedback_assets($hook) {
		if ($hook != 'plugins.php') {
			return;
		}

		$serialized_assets = $this->settings->getOption(self::$deactivation_assets_option);
		if (empty($serialized_assets)) {
			return;
		}

		$assets = @unserialize($serialized_assets);
		if ($assets === false || !is_array($assets)) {
			return;
		}

		$script_handle = 'al-deactivation-feedback';

		// Register the script
		if (!wp_register_script($script_handle, false, array('jquery'), '1.0', true)) {
			return;
		}

		// Localize the script
		$localization_success = wp_localize_script($script_handle, 'alDeactivationVars', array(
			'pluginSlug' => $this->bvinfo->slug,
		));

		// Enqueue the script only if localization was successful
		if ($localization_success) {
			wp_enqueue_script($script_handle);
		} else {
			// If localization fails, deregister the script to clean up
			wp_deregister_script($script_handle);
			return;
		}

		if (!empty($assets['js']) && !empty($assets['css'])) {
			// Attempt to decode both
			$js_content = base64_decode($assets['js']);
			$css_content = base64_decode($assets['css']);

			// Check if both were successfully decoded
			if ($js_content !== false && $css_content !== false) {
				// Enqueue JS
				wp_add_inline_script($script_handle, $js_content, 'after');

				// Enqueue CSS
				wp_add_inline_style('wp-admin', $css_content);
			}
		}
	}

	public function add_deactivation_feedback_dialog() {
		?>
		<div id="al-deactivation-feedback-container"></div>
		<?php
	}
		
	public function showAddAccountPage() {
		$this->enqueueBootstrapCSS();
		require_once dirname( __FILE__ ) . "/admin/add_new_account.php";
	}

	public function showAccountDetailsPage() {
		require_once dirname( __FILE__ ) . "/admin/account_details.php";
	}

	public function getAdminHeaderName() {
		$admin_header_name = 'AirLift Options';
		$whitelabel_info = $this->bvinfo->getPluginWhitelabelInfo($this->bvinfo->slug);
		if (is_array($whitelabel_info) && isset($whitelabel_info['admin_header_name'])) {
			$admin_header_name = $whitelabel_info['admin_header_name'];
		}
		return $admin_header_name;
	}

	public function createAlAdminMenu() {
		if (!(current_user_can('editor') || current_user_can('administrator'))) {
			return;
		}
		global $wp_admin_bar;
		$purge_cache_url = $_SERVER['REQUEST_URI'];

		if (!strpos($purge_cache_url, 'bv_purge_cache')) {
			$purge_cache_url = add_query_arg(
				array(
					'bv_purge_cache' => 'true',
					'bv_purge_all' => 'true'
				),
				$purge_cache_url
			);
		}
		$menu_id = 'airlift_options';
		$admin_header_name = $this->getAdminHeaderName();
		$wp_admin_bar->add_menu(array('id' => $menu_id, 'title' => __($admin_header_name)));
		$wp_admin_bar->add_menu(array('parent' => $menu_id, 'id' => 'purge', 'href' => $purge_cache_url, 'title' => __('Purge Complete Cache')));
	}

	public function purgeCache() {
		if (!(array_key_exists('bv_purge_cache', $_REQUEST) && (current_user_can('editor') || current_user_can('administrator')))) {
			return;
		}
		$info = array('purge_options' => array('purge_all' => true));
		$resp = $this->bvapi->pingbv('/bvapi/purge_cache', $info);
		$message = esc_html($this->handlePurgeCacheErrors($resp));
		echo '<script>window.addEventListener("load", function(){ alert("' . $message . '");})</script>';
	}

	public function handlePurgeCacheErrors($resp) {
		$message = 'Something went wrong on our side. We were unable to clear the cache. Please retry after some time or contact us.';
		if ($resp && is_array($resp)) {
			if (isset($resp['body'])) {
				return $resp['body'];
			} elseif (isset($resp['response']) && isset($resp['response']['code']) && $resp['response']['code'] == 200) {
				return "Cache will pe purged soon!!";
			}
		}
		return $message;
	}

	public function adminPage() {
		if (isset($_REQUEST['bvnonce']) && wp_verify_nonce( $_REQUEST['bvnonce'], 'bvnonce' )) {
			$info = array();
			$this->siteinfo->basic($info);
			$this->bvapi->pingbv('/bvapi/disconnect', $info, ALAccount::sanitizeKey($_REQUEST['pubkey']));
			ALAccount::remove($this->settings, ALAccount::sanitizeKey($_REQUEST['pubkey']));
		}
		if (ALAccount::isConfigured($this->settings)) {
			if (isset($_REQUEST['account_details'])) {
				$this->showAccountDetailsPage();
			} else if (isset($_REQUEST['add_account'])) {
				$this->showAddAccountPage();
			} else {
				$this->showAccountDetailsPage();
			}
		} else {
			$this->showAddAccountPage();
		}
	}

	public function initWhitelabel($plugins) {
		if (!is_array($plugins) || !$this->bvinfo->canWhiteLabel()) {
			return $plugins;
		}
		$whitelabel_infos = $this->bvinfo->getPluginsWhitelabelInfos();
		foreach ($whitelabel_infos as $slug => $brand) {
			if (!isset($slug) || !$this->bvinfo->canWhiteLabel($slug) || !array_key_exists($slug, $plugins) || !is_array($brand)) {
				continue;
			}
			if (array_key_exists('hide', $brand)) {
				unset($plugins[$slug]);
			} else {
				if (array_key_exists('name', $brand)) {
					$plugins[$slug]['Name'] = $brand['name'];
				}
				if (array_key_exists('title', $brand)) {
					$plugins[$slug]['Title'] = $brand['title'];
				}
				if (array_key_exists('description', $brand)) {
					$plugins[$slug]['Description'] = $brand['description'];
				}
				if (array_key_exists('authoruri', $brand)) {
					$plugins[$slug]['AuthorURI'] = $brand['authoruri'];
				}
				if (array_key_exists('author', $brand)) {
					$plugins[$slug]['Author'] = $brand['author'];
				}
				if (array_key_exists('authorname', $brand)) {
					$plugins[$slug]['AuthorName'] = $brand['authorname'];
				}
				if (array_key_exists('pluginuri', $brand)) {
					$plugins[$slug]['PluginURI'] = $brand['pluginuri'];
				}
			}
		}
		return $plugins;
	}
}
endif;
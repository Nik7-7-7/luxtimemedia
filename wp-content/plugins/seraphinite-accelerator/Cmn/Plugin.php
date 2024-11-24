<?php

namespace seraph_accel;

if( !defined( 'ABSPATH' ) )
	exit;

class PluginOptions
{
	const VERPREV			= 'vPrev';

	static private $_cache = null;

	static function Get( $ver, $name, $cbNs = '', $rawExtData = null )
	{
		$data = Gen::GetArrField( self::$_cache, array( PluginOptions::GetMultiSitePrefix(), $name ) );
		if( $data )
			return( $data );

		if( is_array( $rawExtData ) )
			$data = $rawExtData;
		else
		{
			$rawExtData = null;

			$data = $dataRaw = Plugin::IsInited() ? get_option( 'seraph_accel_' . $name ) : null;
			if( gettype( $data ) == 'string' )
				$data = @json_decode( $data, true );

			if( !is_array( $data ) )
				$data = array();
		}

		$verFrom = (isset($data[ 'v' ])?$data[ 'v' ]:null);
		if( !$verFrom )
			$verFrom = 0;

		{
			$dataT = Gen::CallFunc( $cbNs . 'OnOptRead_' . $name, array( $data, $verFrom ) );
			if( is_array( $dataT ) )
				$data = $dataT;
			unset( $dataT );
		}

		$data[ 'v' ] = $ver;

		if( $ver != $verFrom )
		{
			$data[ PluginOptions::VERPREV ] = $verFrom;

			$dataT = Gen::CallFunc( $cbNs . 'OnOptGetDef_' . $name );
			if( is_array( $dataT ) )
			{
				Gen::ArrSet( $dataT, $data );
				$data = $dataT;
			}

			if( !$rawExtData )
			{
				if( $verFrom && Plugin::IsInited() )
				{
					update_option( 'seraph_accel_' . $name . 'V' . $verFrom, $dataRaw );
					for( $verFromDel = ( int )$verFrom - 5; $verFromDel; $verFromDel-- )
						delete_option( 'seraph_accel_' . $name . 'V' . $verFromDel );
				}
				self::Set( $ver, $name, $data, $cbNs );
			}
		}

		self::$_cache[ $name ] = $data;
		return( $data );
	}

	static function Set( $ver, $name, $data, $cbNs = '', $updateCacheOnly = false )
	{

		{
			$dataT = Gen::CallFunc( $cbNs . 'OnOptWritePrep_' . $name, array( $data ) );
			if( $dataT )
				$data = $dataT;
			unset( $dataT );
		}

		$data[ 'v' ] = $ver;

		Gen::SetArrField( self::$_cache, array( PluginOptions::GetMultiSitePrefix(), $name ), $data );

		if( $updateCacheOnly )
			return( Gen::S_OK );

		$dataWrite = $data;
		{
			$dataWriteT = Gen::CallFunc( $cbNs . 'OnOptWrite_' . $name, array( $dataWrite ) );
			if( $dataWriteT )
				$dataWrite = $dataWriteT;
			unset( $dataWriteT );
		}

		if( Plugin::IsInited() )
			$hr = update_option( 'seraph_accel_' . $name, @json_encode( $dataWrite ) ) ? Gen::S_OK : Gen::S_FALSE;
		else
			$hr = Gen::E_UNSUPPORTED;

		return( $hr );
	}

	static function CacheClear( $name )
	{
		Gen::SetArrField( self::$_cache, array( PluginOptions::GetMultiSitePrefix(), $name ), null );
	}

	static function Del( $name, $cbNs = '' )
	{
		self::CacheClear( $name );
		delete_option( 'seraph_accel_' . $name );
		return( Gen::CallFunc( $cbNs . 'OnOptDel_' . $name, array(), Gen::S_OK ) );
	}

	static function GetMultiSitePrefix()
	{
		if( is_multisite() )
			return( Plugin::IsInited() ? get_current_blog_id() : 0 );
		return( '' );
	}
}

class PluginFileValues
{
	static function Get( $name, $cacheTimeout = 0 )
	{
		return( PluginFileValues::GetEx( OnFileValuesGetRootDir(), $name, $cacheTimeout ) );
	}

	static function GetEx( array $dir, $name, $cacheTimeout = 0 )
	{
		$tmCur = microtime( true );

		if( $cacheTimeout !== null && ( $cacheTimeout === 0 || $tmCur - self::$_lastUpdateTime > $cacheTimeout ) )
			Gen::SetArrField( self::$_cache, array( $dir[ 1 ], $name ), null );
		else
		{
			$val = Gen::GetArrField( self::$_cache, array( $dir[ 1 ], $name ) );
			if( $val !== null )
				return( $val );
		}

		$val = Gen::FileGetContentExclusive( PluginFileValues::GetFileNameEx( $dir, $name ), false, true );
		$val = ( $val !== false ) ? @unserialize( $val ) : null;

		Gen::SetArrField( self::$_cache, array( $dir[ 1 ], $name ), $val );
		self::$_lastUpdateTime = $tmCur;
		return( $val );
	}

	static function Set( $name, $val )
	{
		return( PluginFileValues::SetEx( OnFileValuesGetRootDir(), $name, $val ) );
	}

	static function SetEx( array $dir, $name, $val )
	{
		$hr = Gen::FilePutContentExclusive( PluginFileValues::GetFileNameEx( $dir, $name ), @serialize( $val ), true );
		if( Gen::HrFail( $hr ) )
			return( $hr );

		Gen::SetArrField( self::$_cache, array( $dir[ 1 ], $name ), $val );
		self::$_lastUpdateTime = microtime( true );
		return( Gen::S_OK );
	}

	static function Del( $name )
	{
		return( PluginFileValues::DelEx( OnFileValuesGetRootDir(), $name ) );
	}

	static function DelEx( array $dir, $name )
	{
		$hr = PluginFileValues::SetEx( $dir, $name, null );
		if( Gen::HrFail( $hr ) )
			return( $hr );

		@unlink( PluginFileValues::GetFileNameEx( $dir, $name ) );
		return( $hr );
	}

	static function GetDirVar( $var )
	{
		return( OnFileValuesGetRootDir( $var ) );
	}

	static function GetDirEx( $dir )
	{
		return( $dir[ 0 ] . ( strlen( $dir[ 1 ] ) ? ( '/' . $dir[ 1 ] ) : '' ) );
	}

	static function GetFileNameEx( $dir, $name )
	{
		return( PluginFileValues::GetDirEx( $dir ) . '/' . $name );
	}

	static function GetDir()
	{
		return( PluginFileValues::GetDirEx( OnFileValuesGetRootDir() ) );
	}

	static function GetFileName( $name )
	{
		return( PluginFileValues::GetFileNameEx( OnFileValuesGetRootDir(), $name ) );
	}

	static private $_cache = null;
	static private $_lastUpdateTime;
}

class PluginPostOptions
{
	static private $_cache = NULL;

	static function Get( $postId, $ver, $name, $cbType = 'Post', $cbNs = '' )
	{
		$cachePath = array( PluginOptions::GetMultiSitePrefix(), $cbType, $postId, $name );

		$data = Gen::GetArrField( self::$_cache, $cachePath );
		if( $data )
			return( $data );

		$data = $dataRaw = get_post_meta( $postId, '_seraph_accel_' . $name, true );
		if( !is_array( $data ) )
		{
			$data = @json_decode( $data, true );
			if( !is_array( $data ) )
				$data = array();
		}

		$verFrom = (isset($data[ 'v' ])?$data[ 'v' ]:null);

		$data = Gen::CallFunc( $cbNs . 'On' . $cbType . 'OptRead_' . $name, array( $data, $verFrom ), $data );

		$data[ 'v' ] = $ver;
		if( $ver != $verFrom )
		{
			if( $verFrom )
			{
				update_post_meta( $postId, '_seraph_accel_' . $name . 'V' . $verFrom, $dataRaw );
				for( $verFromDel = ( int )$verFrom - 5; $verFromDel; $verFromDel-- )
					delete_post_meta( $postId, '_seraph_accel_' . $name . 'V' . $verFromDel );
			}
			self::Set( $postId, $ver, $name, $data, $cbType, $cbNs );
		}

		Gen::SetArrField( self::$_cache, $cachePath, $data );
		return( $data );
	}

	static function Set( $postId, $ver, $name, $data, $cbType = 'Post', $cbNs = '' )
	{
		$cachePath = array( PluginOptions::GetMultiSitePrefix(), $cbType, $postId, $name );

		$data[ 'v' ] = $ver;

		$data = Gen::CallFunc( $cbNs . 'On' . $cbType . 'OptWritePrep_' . $name, array( $data ), $data );

		Gen::SetArrField( self::$_cache, $cachePath, $data );

		$dataWrite = Gen::CallFunc( $cbNs . 'On' . $cbType . 'OptWrite_' . $name, array( $data ), $data );
		return( update_post_meta( $postId, '_seraph_accel_' . $name, @json_encode( $dataWrite ) ) );
	}

	static function Del( $postId, $name, $cbType = 'Post' )
	{
		$cachePath = array( $cbType, $postId, $name );

		Gen::SetArrField( self::$_cache, $cachePath, null );
		delete_post_meta( $postId, '_seraph_accel_' . $name );
		return( true );
	}
}

class PluginRmtCfg
{
	const STG_VER			= 1;
	const STG_ID			= 'RmtCfg';

	static private $_sessTouched = false;

	static function UpdateTestRequest()
	{
		$urlRemoteCfg = null;
		{
			$args = array();
			$args[ 'epid' ] = Wp::GetSiteId();
			$args[ 'id' ] = 'wordpress-accelerator';
			$args[ 'name' ] = 'Accelerator';
			$args[ 'v' ] = '2.22.14';
			$args[ 'pk' ] = 'Base';
			$args[ 'cfg' ] = '';
			$args[ 'loc' ] = Wp::GetLocale();

			$urlRemoteCfg = add_query_arg( $args, 'https://www.s-sols.com/data/products/wordpress/accelerator/cfg0001.json.txt' );
		}

		return( Wp::RemoteGet( $urlRemoteCfg, array( 'timeout' => 30, 'redirection' => 5 ) ) );
	}

	static function Update( $bForce = false, $bFirstTimeOnly = false )
	{

		$data = PluginOptions::Get( self::STG_VER, self::STG_ID, __CLASS__ . '::' );
		$curUpdTime = time();

		$lastCheckVer = (isset($data[ 'plgVer' ])?$data[ 'plgVer' ]:null);
		$lastCheckPackage = (isset($data[ 'plgPk' ])?$data[ 'plgPk' ]:null);
		if( $lastCheckPackage === null && $lastCheckVer !== null )
			$lastCheckPackage = 'Base';

		if( $lastCheckVer !== '2.22.14' || $lastCheckPackage !== 'Base' )
		{
			$state = Plugin::StateGet();

			if( $lastCheckVer !== '2.22.14' && !isset( $state[ 'changeVerCheck' ] ) )
			{
				$state[ 'changeVerCheck' ] = $lastCheckVer !== null ? $lastCheckVer : '';
				Plugin::StateSet( $state );
			}

			if( $lastCheckPackage !== 'Base' && !isset( $state[ 'changePkCheck' ] ) )
			{
				$state[ 'changePkCheck' ] = $lastCheckPackage !== null ? $lastCheckPackage : '';
				Plugin::StateSet( $state );
			}

			$bForce = true;
		}

		if( !$bForce )
		{
			if( $bFirstTimeOnly && $lastCheckVer == '2.22.14' )
				return( Gen::S_FALSE );

			$lastUpdTime = (isset($data[ 'updTime' ])?$data[ 'updTime' ]:null);
			if( $lastUpdTime && ( $curUpdTime - $lastUpdTime ) <= 3600 )
				return( Gen::S_FALSE );

			if( self::$_sessTouched )
				return( Gen::S_FALSE );
		}

		self::$_sessTouched = true;

		$urlRemoteCfg = null;
		{
			$args = array();
			$args[ 'epid' ] = Wp::GetSiteId();
			$args[ 'id' ] = 'wordpress-accelerator';
			$args[ 'name' ] = 'Accelerator';
			$args[ 'v' ] = '2.22.14';
			$args[ 'pk' ] = 'Base';
			$args[ 'cfg' ] = '';
			$args[ 'loc' ] = Wp::GetLocale();

			$urlRemoteCfg = add_query_arg( $args, 'https://www.s-sols.com/data/products/wordpress/accelerator/cfg0001.json.txt' );
		}

		if( !$bForce && (isset($data[ 'mdfTime' ])?$data[ 'mdfTime' ]:null) )
		{
			$requestRes = wp_remote_head( $urlRemoteCfg, array( 'timeout' => 5, 'redirection' => 5 ) );

			$timeMdf = self::_Update_GetMdfTime( $requestRes );

			if( $data[ 'mdfTime' ] >= $timeMdf )
			{
				$data[ 'updTime' ] = $curUpdTime;
				$data[ 'plgVer' ] = '2.22.14';
				$data[ 'plgPk' ] = 'Base';

				$hr = PluginOptions::Set( self::STG_VER, self::STG_ID, $data, __CLASS__ . '::' );
				if( Gen::HrFail( $hr ) )
					return( $hr );

				return( $timeMdf ? Gen::S_OK : Gen::S_FALSE );
			}
		}

		$requestRes = Wp::RemoteGet( $urlRemoteCfg, array( 'timeout' => $bForce ? 30 : 5, 'redirection' => 5 ) );

		$timeMdf = self::_Update_GetMdfTime( $requestRes );

		$data[ 'mdfTime' ] = $timeMdf;
		$data[ 'updTime' ] = $curUpdTime;
		$data[ 'plgVer' ] = '2.22.14';
		$data[ 'plgPk' ] = 'Base';

		if( $timeMdf )
		{
			$content = @json_decode( wp_remote_retrieve_body( $requestRes ), true );
			if( is_array( $content ) )
				$data[ 'data' ] = $content;
		}

		$hr = PluginOptions::Set( self::STG_VER, self::STG_ID, $data, __CLASS__ . '::' );
		if( Gen::HrFail( $hr ) )
			return( $hr );

		return( $timeMdf ? Gen::S_OK : Gen::S_FALSE );

	}

	static function Get()
	{

		PluginRmtCfg::Update();
		$data = PluginOptions::Get( self::STG_VER, self::STG_ID, __CLASS__ . '::' );
		return( $data[ 'data' ] );

	}

	static private function _Update_GetMdfTime( $requestRes )
	{
		if( Net::GetHrFromWpRemoteGet( $requestRes ) != Gen::S_OK )
			return( 0 );

		return( @strtotime( wp_remote_retrieve_header( $requestRes, 'last-modified' ) ) );
	}

	static function OnOptRead_RmtCfg( $data, $verFrom )
	{
		if( !isset( $data[ 'data' ] ) || !is_array( $data[ 'data' ] ) )
			$data[ 'data' ] = array();
		if( !isset( $data[ 'updTime' ] ) )
			$data[ 'updTime' ] = 0;
		return( $data );
	}

}

class Plugin
{
	const BASENAME			= 'seraphinite-accelerator/plugin_root.php';

	const STATE_VER			= 1;

	const DisplayContent_Str		= 0;
	const DisplayContent_SmallBlock	= 1;
	const DisplayContent_Block		= 2;

	static private $_isInited = false;
	static private $_locale = null;

	static function GetCurBaseName( $full = true )
	{
		return( basename( dirname( __DIR__ ) ) . ( $full ? '/plugin_root.php' : '' ) );
	}

	static function IsInited()
	{
		return( self::$_isInited );
	}

	static function Init()
	{
		self::$_isInited = true;

		$isAdminMode = is_admin();

		if( $isAdminMode )
		{
			PluginRmtCfg::Update( false, true );
			add_action( 'admin_notices', __CLASS__ . '::_on_admin_notices' );
			add_action( 'network_admin_notices', __CLASS__ . '::_on_admin_notices' );

		}

		add_action( 'plugins_loaded', __CLASS__ . '::_OnLoadLoc' );
		add_action( 'change_locale', __CLASS__ . '::_OnLoadLoc' );

		if( !Wp::IsInRunningCron() && isset( $_REQUEST[ 'seraph_accel_at' ] ) )
		{

			@ignore_user_abort( true );
			Gen::CloseCurRequestSessionForContinueBgWorkEx();
		}

		add_action( 'wp_loaded', function() { self::_AsyncTasksProcess(); } );

		if( $isAdminMode )
		{
			add_action( 'wp_ajax_seraph_accel_act', __CLASS__ . '::_on_admin_action_act' );

			add_filter( 'removable_query_args',
				function( $args )
				{
					$args[] = 'seraph_accel_postOpsRes';
					return( $args );
				}
			);

			add_action( 'admin_init',
				function()
				{

					$settingsOp = Wp::SanitizeTextData( (isset($_REQUEST[ 'seraph_accel_settingsOp' ])?$_REQUEST[ 'seraph_accel_settingsOp' ]:null) );
					if( $settingsOp )
					{
						if( $settingsOp == 'reset' )
							Plugin::ReloadWithPostOpRes( array( 'settingsOp' => array( 'op' => $settingsOp, 'hr' => wp_verify_nonce( (isset($_REQUEST[ '_wpnonce' ])?$_REQUEST[ '_wpnonce' ]:''), 'reset' ) ? ( current_user_can( 'manage_options' ) ? Plugin::SettReset() : Gen::E_ACCESS_DENIED ) : Gen::E_CONTEXT_EXPIRED ) ) );
						exit;
					}
				}
			);

			add_action( 'seraph_accel_postOpsRes',
				function( $res )
				{
					$settingsOp = (isset($res[ 'settingsOp' ])?$res[ 'settingsOp' ]:null);
					if( $settingsOp )
					{
						if( $settingsOp[ 'op' ] == 'reset' )
						{
							$hr = $settingsOp[ 'hr' ];
							if( $hr == Gen::S_OK )
								echo( Ui::BannerMsg( Ui::MsgSucc, esc_html_x( 'ResetSuccInfo', 'admin.Common_Settings', 'seraphinite-accelerator' ), Ui::MsgOptDismissible ) );
							else
								echo( Ui::BannerMsg( Ui::MsgErr, sprintf( esc_html_x( 'ResetErrInfo_%1$s', 'admin.Common_Settings', 'seraphinite-accelerator' ), Plugin::GetErrorDescr( $hr ) ), Ui::MsgOptDismissible ) );
						}
					}
				}
			);

			add_action( 'admin_enqueue_scripts',
				function( $hook )
				{

					Plugin::CmnStyle( 'AdminUi' );

				}
			);

		}

		$isNoFuncMode = false;

		if( $isAdminMode )
		{
			global $wp_version;
			if( version_compare( PHP_VERSION, '7.1' ) < 0 || version_compare( $wp_version, '4.5' ) < 0 )
				$isNoFuncMode = true;
		}

		if( !self::IsEulaAccepted() )
			$isNoFuncMode = true;

		if( $isNoFuncMode )
		{
			if( $isAdminMode )
				Gen::CallFunc( 'seraph_accel\\OnInitAdminModeNotAccepted' );
			return( array( 'isAdmin' => $isAdminMode ) );
		}

		{
			$state = Plugin::StateGet();
			if( !isset( $state[ 'firstUseTimeStamp' ] ) )
			{
				$state[ 'firstUseTimeStamp' ] = time();
				Plugin::StateSet( $state );
			}
		}

		add_action( 'wp_loaded',
			function()
			{
				if( isset( $_REQUEST[ 'seraph_accel_api' ] ) )
					self::_OnApiCall( '', 'seraph_accel_api' );
			}
		, -999999 );

		add_filter( 'plugins_update_check_locales', __CLASS__ . '::_on_check_plugins_updates', 10, 1 );

		add_action( 'admin_post_nopriv_seraph_accel_api', function() { unset( $_REQUEST[ 'action' ] ); self::_OnApiCall( '', 'fn' ); } );
		add_action( 'admin_post_seraph_accel_api', function() { unset( $_REQUEST[ 'action' ] ); self::_OnApiCall( '', 'fn' ); } );

		if( $isAdminMode )
		{
			add_action( 'wp_ajax_seraph_accel_api', function() { unset( $_REQUEST[ 'action' ] ); self::_OnApiCall( 'Admin', 'fn' ); } );

			add_filter( 'plugin_action_links_' . Plugin::GetCurBaseName(),
				function( $actions, $plugin_file )
				{
					if( !is_array( $actions ) )
						return( $actions );

					$rmtCfg = PluginRmtCfg::Get();

					Plugin::ActionsListAdd( $actions, 'docs', Ui::Link( esc_html_x( 'PluginDocLink', 'admin.Common', 'seraphinite-accelerator' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlProductDocs' ), true, array( 'noTextIfNoHref' => true ) ), true );
					if( !Gen::IsEmpty( $urlSettings = menu_page_url( 'seraph_accel_settings', false ) ) )
						Plugin::ActionsListAdd( $actions, 'settings', Ui::Link( esc_html( Wp::GetLocString( 'Settings' ) ), $urlSettings ), true );

					if( !Gen::IsEmpty( Gen::GetArrField( $rmtCfg, 'Prms.FullProductDownloadPath' ) ) )
						Plugin::ActionsListAdd( $actions, 'order', Ui::Link( esc_html_x( 'OrderInLockedFeatureBtn', 'admin.Common_SwitchTo', 'seraphinite-accelerator' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlProductBuy' ), true, array( 'noTextIfNoHref' => true ), array( 'style' => array( 'font-weight' => '900' ) ) ), true );

					$actDeactivate = (isset($actions[ 'deactivate' ])?$actions[ 'deactivate' ]:null);
					if( $actDeactivate )
					{
						$q = Gen::GetArrField( $rmtCfg, 'Questionnaires.Items.Deactivate' );
						if( $q )
						{

							$href = null;
							{
								$ndAct = HtmlNd::FindByTag( HtmlNd::Parse( $actDeactivate ), 'a' );
								HtmlNd::SetAttrVal( $ndAct, 'onclick', 'seraph_accel.Ui.PopupShow(\'deactivateQuestionnaire\');return(false);' );

								{
									$res = HtmlNd::DeParse( $ndAct );
									if( !empty( $res ) )
										$actions[ 'deactivate' ] = $res;
								}

								$href = HtmlNd::GetAttrVal( $ndAct, 'href' );
							}

							UiPopups::Add( 'deactivateQuestionnaire',
								array(
									'modal' => true,
									'attrs' => array( 'class' => 'metabox-holder', 'style' => array( 'max-width' => '500px' ) ),
									'q' => array( 'id' => 'Deactivate', 'params' => $q ),
									'href' => $href,
									'cbPre' => function( $prms ){ self::_admin_printscriptsstyles(); },
									'cb' => __CLASS__ . '::_OnPopup_QuestionnaireDeactivate'
								)
							);
						}
					}

					return( $actions );
				}
			, 10, 2 );

			add_action( 'admin_footer',
				function()
				{
					UiPopups::Draw();
				}
			);

			Gen::CallFunc( 'seraph_accel\\OnInitAdminMode' );
		}

		Gen::CallFunc( 'seraph_accel\\OnInit', array( $isAdminMode ) );

		return( array( 'isAdmin' => $isAdminMode ) );

		esc_html__( 'Seraphinite Accelerator', 'seraphinite-accelerator' );
		esc_html__( 'Seraphinite Accelerator (Base, cache only)', 'seraphinite-accelerator' );
		esc_html__( 'Turns on site high speed to be attractive for people and search engines.', 'seraphinite-accelerator' );
		esc_html__( 'https://www.s-sols.com', 'seraphinite-accelerator' );
		esc_html__( 'Seraphinite Solutions', 'seraphinite-accelerator' );
		esc_html__( 'http://wordpress.org/plugins/seraphinite-accelerator', 'seraphinite-accelerator' );
	}

	static function _OnLoadLoc()
	{
		$subSystemIds = array();

		if( is_admin() )
		{
			if( !array_search( '', $subSystemIds, true ) )
				$subSystemIds[] = '';
			$subSystemIds[] = 'admin';
		}

		self::$_locale = Wp::Loc_Load( $subSystemIds, 'seraphinite-accelerator', Plugin::GetCurBaseName( false ), 'languages', apply_filters( 'seraph_accel_onLocLoadAddFiles', array(), 'languages' ) );
	}

	static private function _OnApiCall( $m, $fn )
	{
		$apiFunc = Wp::SanitizeId( (isset($_REQUEST[ $fn ])?$_REQUEST[ $fn ]:null) );
		if( !$apiFunc )
		{
			wp_die( '', 400 );
			return;
		}

		unset( $_REQUEST[ $fn ] );

		self::_ApiCall_Make( 'seraph_accel\\On' . $m . 'Api_' . $apiFunc, $_REQUEST );
		exit();
	}

	static function Loc_ScriptLoad( $handle )
	{
		return( Wp::Loc_ScriptLoad( $handle, 'seraphinite-accelerator', Plugin::GetCurBaseName( false ), 'languages' ) );
	}

	static function ReloadWithPostOpResEx( $res, $redir = null )
	{
		if( $redir === null )
			$redir = wp_get_referer();
		return( add_query_arg( array( 'seraph_accel_postOpsRes' => rawurlencode( base64_encode( json_encode( $res ) ) ) ), $redir ) );
	}

	static function ReloadWithPostOpRes( $res, $redir = null )
	{
		wp_safe_redirect( Plugin::ReloadWithPostOpResEx( $res, $redir ) );
	}

	static function ActionsListAdd( &$actions, $id, $link, $first = false )
	{
		if( empty( $link ) )
			return;

		if( $first )
			$actions = array_merge( array( $id => $link ), $actions );
		else
			$actions = array_merge( $actions, array( $id => $link ) );
	}

	const ASYNCTASK_TTL_DEF			= 30;
	const ASYNCTASK_PUSH_TIMEOUT	= 5;

	const ASYNCTASK_PUSH_AUTO		= 'A';

	static private function _AsyncTasksProcess()
	{

		$taskName = Gen::SanitizeId( (isset($_REQUEST[ 'seraph_accel_at' ])?$_REQUEST[ 'seraph_accel_at' ]:null) );
		$taskRunTime = ( float )str_replace( '_', '.', (isset($_REQUEST[ 'rt' ])?$_REQUEST[ 'rt' ]:'') );

		if( $taskName != 'M' )
		{
			if( $taskName )
			{
				if( $taskName == 'M_TEST' )
				{
					@header( 'X-Seraph-Accel-test: M_TEST_' . (isset($_REQUEST[ 'rt' ])?$_REQUEST[ 'rt' ]:'') );
					echo( Gen::SanitizeId( 'M_TEST_' . (isset($_REQUEST[ 'rt' ])?$_REQUEST[ 'rt' ]:'') ) );
					exit;
				}

				$dataItem = null;
				if( Gen::FileContentExclusive_Open( $h, OnAsyncTasksGetFile(), true, 'cb+' ) == Gen::S_OK )
				{
					$data = Gen::GetArrField( @unserialize( ( string )Gen::FileContentExclusive_Get( $h ) ), array( 'data' ), array() );
					$dataItem = self::_AsyncTaskCut( $data, $taskName, $taskRunTime, $changed );
					if( $changed && !Gen::FileContentExclusive_Put( $h, $data ? @serialize( array( 'data' => $data ) ) : '' ) )
						$dataItem = null;

					Gen::FileContentExclusive_Close( $h );
					unset( $h );
				}

				if( $dataItem )
					self::_AsyncTaskRun( $dataItem );

				exit;
			}

			$lock = new Lock( OnAsyncTasksGetFile() . '.m', false );
			if( !$lock -> Acquire( false ) )
			{

				return;
			}

			$dataItem = null;
			if( Gen::FileContentExclusive_Open( $h, OnAsyncTasksGetFile(), true, 'cb+' ) == Gen::S_OK )
			{
				$data = Gen::GetArrField( @unserialize( ( string )Gen::FileContentExclusive_Get( $h ) ), array( 'data' ), array() );
				$dataItem = self::_AsyncTaskGetNextRun( $data, false, $changed );
				if( $changed && !Gen::FileContentExclusive_Put( $h, $data ? @serialize( array( 'data' => $data ) ) : '' ) )
					$dataItem = null;

				Gen::FileContentExclusive_Close( $h );
				unset( $h );
			}

			if( $dataItem )
			{

				$mrgTmCheck = time();
				$mgrTmLastRun = ( int )@filemtime( OnAsyncTasksGetFile() . '.mr' );
				if( $mrgTmCheck - $mgrTmLastRun > 15 )
				{
					@file_put_contents( OnAsyncTasksGetFile() . '.mr', '' );

				Plugin::AsyncTaskPushEx( Plugin::AsyncTaskPushGetUrl( 'M' ), 0  );

				}

			}

			$lock -> Release();
			unset( $lock );

			return;
		}

		$tmStart = time();

		$lock = new Lock( OnAsyncTasksGetFile() . '.m', false );
		if( !$lock -> Acquire( 30 ) )
		{

			exit;
		}

		for( ;; )
		{

			$dataItem = null;
			if( Gen::FileContentExclusive_Open( $h, OnAsyncTasksGetFile(), true, 'cb+' ) == Gen::S_OK )
			{
				$data = Gen::GetArrField( @unserialize( ( string )Gen::FileContentExclusive_Get( $h ) ), array( 'data' ), array() );
				$dataItem = self::_AsyncTaskGetNextRun( $data, true, $changed );

				if( $changed && !Gen::FileContentExclusive_Put( $h, $data ? @serialize( array( 'data' => $data ) ) : '' ) )
					$dataItem = null;

				Gen::FileContentExclusive_Close( $h );
				unset( $h );
			}

			if( $dataItem )
			{
				if( (isset($dataItem[ 'f' ])?$dataItem[ 'f' ]:null) )
					self::_AsyncTaskRun( $dataItem );
				else
					Plugin::AsyncTaskPushEx( Plugin::AsyncTaskPushGetUrl( $dataItem[ 'n' ], $dataItem[ 'tr' ] ), 0 );
			}
			else if( time() - $tmStart <= 60 )
				usleep( ( int )( 1000000 * 1 ) );
			else
				break;
		}

		$lock -> Release();

		exit;
	}

	static private function _AsyncTaskRun( $dataItem )
	{

		if( is_multisite() )
			switch_to_blog( $dataItem[ 'b' ] );
		Gen::CallFunc( 'seraph_accel\\OnAsyncTask_' . $dataItem[ 'n' ], array( Gen::CallFunc( 'seraph_accel\\OnAsyncTask_' . $dataItem[ 'n' ] . '_AU', array( $dataItem[ 'a' ] ), $dataItem[ 'a' ] ) ) );

	}

	static private function _AsyncTaskCut( &$data, $taskName, $taskRunTime, &$changed = null )
	{
		$changed = false;

		for( $i = 0; $i < count( $data ); $i++ )
		{
			$dataItem = $data[ $i ];

			if( !isset( $dataItem[ 'tr' ] ) )
				continue;

			if( $dataItem[ 'n' ] == $taskName && $dataItem[ 'tr' ] === $taskRunTime )
			{
				array_splice( $data, $i, 1 );
				$changed = true;
				return( $dataItem );
			}
		}

		return( null );
	}

	static private function _AsyncTaskGetNextRun( &$data, $mark, &$changed = null )
	{
		$changed = false;
		$tmCur = microtime( true );
		$push = null;

		for( $i = 0; $i < count( $data ); $i++ )
		{
			$dataItem = &$data[ $i ];

			if( isset( $dataItem[ 'tr' ] ) && ( ( $tmCur - $dataItem[ 'tr' ] ) < 30 ) )
				continue;

			if( isset( $dataItem[ 'tl' ] ) && ( $tmCur > $dataItem[ 't' ] + $dataItem[ 'tl' ] ) )
			{
				array_splice( $data, $i--, 1 );
				$changed = true;
				continue;
			}

			if( $tmCur < $dataItem[ 't' ] )
				continue;

			if( $mark )
			{
				$dataItem[ 'tr' ] = ( float )sprintf( '%.20F', $tmCur );
				$changed = true;
			}

			$markAndCut = $mark && (isset($dataItem[ 'f' ])?$dataItem[ 'f' ]:null);
			if( $i )
			{

				array_splice( $data, $i, 1 );
				if( !$markAndCut )
					array_splice( $data, 0, 0, array( $dataItem ) );
				$changed = true;
			}
			else if( $markAndCut )
			{
				array_splice( $data, 0, 1 );
				$changed = true;
			}

			return( $dataItem );
		}

		return( null );
	}

	static function AsyncTaskPost( $name, $args = null, $times = Plugin::ASYNCTASK_TTL_DEF, $push = true, $singleton = false  )
	{
		return( Plugin::AsyncTaskPostEx( $name, false, $args, $times, $push, $singleton ) );
	}

	static function AsyncFastTaskPost( $name, $args = null, $times = Plugin::ASYNCTASK_TTL_DEF, $push = true, $singleton = false  )
	{
		return( Plugin::AsyncTaskPostEx( $name, true, $args, $times, $push, $singleton ) );
	}

	static function AsyncTaskPostEx( $name, $fast = false , $args = null, $times = Plugin::ASYNCTASK_TTL_DEF, $push = true, $singleton = false  )
	{

		if( $push === Plugin::ASYNCTASK_PUSH_AUTO )
			$push = Wp::IsInRunningCron();

		$dataItemNew = array( 'n' => $name );

		{
			if( !is_array( $times ) )
				$times = array( time(), $times );

			$dataItemNew[ 't' ] = $times[ 0 ];

			if( isset( $times[ 1 ] ) )
			{
				if( $times[ 1 ] !== false )
					$dataItemNew[ 'tl' ] = $times[ 1 ];
			}
			else
				$dataItemNew[ 'tl' ] = Plugin::ASYNCTASK_TTL_DEF;
		}

		if( is_multisite() )
			$dataItemNew[ 'b' ] = get_current_blog_id();

		if( $fast )
			$dataItemNew[ 'f' ] = true;

		if( !is_array( $args ) )
			$args = array();

		$hr = Gen::FileContentExclusive_Open( $h, OnAsyncTasksGetFile(), true, 'cb+' );
		if( !$h )
			return( $hr );

		$data = Gen::GetArrField( @unserialize( Gen::FileContentExclusive_Get( $h, '' ) ), array( 'data' ), array() );
		if( $singleton )
		{
			foreach( $data as $i => $dataItem )
			{
				if( $dataItem[ 'n' ] !== $name || (isset($dataItem[ 'b' ])?$dataItem[ 'b' ]:null) !== (isset($dataItemNew[ 'b' ])?$dataItemNew[ 'b' ]:null) )
					continue;

				if( !is_bool( $singleton ) )
				{
					$argsNew = call_user_func( $singleton, $args, Gen::CallFunc( 'seraph_accel\\OnAsyncTask_' . $name . '_AU', array( $dataItem[ 'a' ] ), $dataItem[ 'a' ] ) );

					if( $argsNew === false )
					{
						Gen::FileContentExclusive_Close( $h );
						return( Gen::S_FALSE );
					}

					if( $argsNew === null )
						continue;

					$args = $argsNew;
					unset( $argsNew );
				}

				array_splice( $data, $i, 1 );

				break;
			}
		}

		$dataItemNew[ 'a' ] = $args;

			$data[] = $dataItemNew;

		if( !Gen::FileContentExclusive_Put( $h, @serialize( array( 'data' => $data ) ) ) )
		{
			Gen::FileContentExclusive_Close( $h );
			return( Gen::E_FAIL );
		}

		Gen::FileContentExclusive_Close( $h );

		if( !$push )
			return( Gen::S_OK );

		$res = Plugin::AsyncTaskPush( 0 );
		$hr = $res ? Net::GetHrFromWpRemoteGet( $res ) : Gen::S_OK;
		if( $hr == Gen::E_TIMEOUT || $hr == Net::E_TIMEOUT )
			$hr = Gen::S_TIMEOUT;

		return( $hr );
	}

	static function AsyncTaskDel( $name, $args = null, $singleton = false )
	{
		$hr = Gen::FileContentExclusive_Open( $h, OnAsyncTasksGetFile(), true, 'cb+' );
		if( !$h )
			return( $hr );

		$curBlogId = null;
		if( is_multisite() )
			$curBlogId = get_current_blog_id();

		$hr = Gen::S_FALSE;

		$data = Gen::GetArrField( @unserialize( Gen::FileContentExclusive_Get( $h, '' ) ), array( 'data' ), array() );
		foreach( $data as $i => $dataItem )
		{
			if( $dataItem[ 'n' ] !== $name || (isset($dataItem[ 'b' ])?$dataItem[ 'b' ]:null) !== $curBlogId )
				continue;

			if( !is_bool( $singleton ) )
			{
				$argsNew = call_user_func( $singleton, $args, Gen::CallFunc( 'seraph_accel\\OnAsyncTask_' . $name . '_AU', array( $dataItem[ 'a' ] ), $dataItem[ 'a' ] ) );

				if( $argsNew === null )
					continue;

				unset( $argsNew );
			}

			array_splice( $data, $i, 1 );
			$hr = Gen::S_OK;
		}

		if( $hr == Gen::S_OK && !Gen::FileContentExclusive_Put( $h, $data ? @serialize( array( 'data' => $data ) ) : '' ) )
			$hr = Gen::E_FAIL;

		Gen::FileContentExclusive_Close( $h );
		return( $hr );
	}

	static function AsyncTaskGetTime( $name, $args = null, $singleton = false )
	{
		Gen::FileContentExclusive_Open( $h, OnAsyncTasksGetFile(), true, 'cb+' );
		if( !$h )
			return( false );

		$curBlogId = null;
		if( is_multisite() )
			$curBlogId = get_current_blog_id();

		$res = null;

		$data = Gen::GetArrField( @unserialize( Gen::FileContentExclusive_Get( $h, '' ) ), array( 'data' ), array() );
		foreach( $data as $i => $dataItem )
		{
			if( $dataItem[ 'n' ] !== $name || (isset($dataItem[ 'b' ])?$dataItem[ 'b' ]:null) !== $curBlogId )
				continue;

			if( !is_bool( $singleton ) )
			{
				$argsNew = call_user_func( $singleton, $args, Gen::CallFunc( 'seraph_accel\\OnAsyncTask_' . $name . '_AU', array( $dataItem[ 'a' ] ), $dataItem[ 'a' ] ) );

				if( $argsNew === null )
					continue;

				unset( $argsNew );
			}

			$res = $dataItem[ 't' ];
			break;
		}

		Gen::FileContentExclusive_Close( $h );
		return( $res );
	}

	static function AsyncTaskPush( $timeout = Plugin::ASYNCTASK_PUSH_TIMEOUT )
	{
		$lock = new Lock( OnAsyncTasksGetFile() . '.m', false );
		if( !$lock -> Acquire( false ) )
			return( null );

		$res = Plugin::AsyncTaskPushEx( Plugin::AsyncTaskPushGetUrl( 'M' ), $timeout );

		$lock -> Release();
		unset( $lock );
		return( $res );
	}

	static function AsyncTaskPushEx( $urlPush, $timeout = Plugin::ASYNCTASK_PUSH_TIMEOUT )
	{

		$method = strpos( $urlPush, '/wp-cron.php' ) !== false ? 'GET' : 'POST';

		$asyncMode = null;

		$prms = array( 'local' => $asyncMode == 'loc', 'sslverify' => apply_filters( 'https_local_ssl_verify', false ) );
		if( $timeout > 0 )
			$prms[ 'timeout' ] = $timeout;
		else
		{

			$prms[ 'timeout' ] = 0.01;
			$prms[ 'blocking' ] = false;
		}

		$res = Wp::RemoteRequest( $method, $urlPush, $prms );

		return( $res );
	}

	static function AsyncTaskPushGetUrl( $id, $timeRun = null )
	{

		if( $timeRun === null )
			$timeRun = microtime( true );

		return( Net::UrlAddArgs( Wp::GetSiteWpRootUrl( Gen::CallFunc( 'seraph_accel\\OnAsyncTasksGetPushUrlFile', array(), 'wp-cron.php' ) ), array( 'seraph_accel_at' => $id, 'rt' => str_replace( '.', '_', sprintf( '%.20F', $timeRun ) ) ) ) );
	}

	static function AsyncTaskGetFileName()
	{
		return( OnAsyncTasksGetFile() );
	}

	static private $_IsEulaAccepted = null;

	static function IsPaidLockedContent()
	{

		return( true );
	}

	static function AdminBtnsBlock_GetPaidContent( $enable = null )
	{
		if( $enable !== null )
		{
			if( !$enable )
				return( null );
		}
		else if( !self::IsPaidLockedContent() )
			return( null );

		$rmtCfg = PluginRmtCfg::Get();
		return( array( 'type' => Ui::AdminBtn_Paid, 'href' => Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlProductBuy' ) ) );
	}

	static function IsEulaAccepted()
	{

		return( true );

	}

	static private function _IsEulaAccepted()
	{
		if( self::$_IsEulaAccepted === null )
		{
			$state = Plugin::StateGet();
			self::$_IsEulaAccepted = (isset($state[ 'eulaAcceptedVer' ])?$state[ 'eulaAcceptedVer' ]:null) == namespace\PLUGIN_EULA_VER;
		}

		return( self::$_IsEulaAccepted );
	}

	static function GetSwitchToExtTitle()
	{

		return( esc_html_x( 'ExtTitle', 'admin.Common_SwitchTo', 'seraphinite-accelerator' ) );

	}

	static function SwitchToExt( $type = Plugin::DisplayContent_Block, $text = null )
	{

		$rmtCfg = PluginRmtCfg::Get();

		$res = '';
		if( !empty( $text ) )
			$res .= $text . ' ';
		$res .= vsprintf( Wp::safe_html_x( 'ExtInfo_%1$s%2$s', 'admin.Common_SwitchTo', 'seraphinite-accelerator' ), Ui::Link( Ui::Tag( 'strong', array( '', '' ) ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlProductFeatures' ), true ) );

		return( Plugin::_GetSwitchToContent( $rmtCfg,

			$type == Plugin::DisplayContent_SmallBlock ? esc_html_x( 'ExtLinkSmallBtn', 'admin.Common_SwitchTo', 'seraphinite-accelerator' ) : esc_html_x( 'ExtLinkBtn', 'admin.Common_SwitchTo', 'seraphinite-accelerator' ),

			'ext', $res ) );

	}

	static function _GetSwitchToContent( $rmtCfg, $switchBtnName, $mode, $text = null, $isBtmPrimary = true )
	{
		$res = Ui::TagOpen( 'p' );
		if( $text )
			$res .= $text;

		if( $text )
			$res .= ' ';
		$res .= sprintf( Wp::safe_html_x( 'ContentNotInplaceInfo_%1$s', 'admin.Common_SwitchTo', 'seraphinite-accelerator' ), $switchBtnName );

		if( !self::_IsEulaAccepted() )
		{
			if( !empty( $res ) )
				$res .= ' ';
			$res .= vsprintf( esc_html_x( 'LicAcceptInfo_%1$s%2$s%3$s', 'admin.Common_SwitchTo', 'seraphinite-accelerator' ), Gen::ArrFlatten( array( $switchBtnName, Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlEula' ), true ) ) ) );
		}

		$res .= Ui::TagClose( 'p' );

		$res .= Ui::TagOpen( 'div', array( 'class' => 'ctlSpaceVBefore seraph_accel_switchto' ) );
		{

			$res .= Ui::Button( $switchBtnName, $isBtmPrimary, null, 'seraph_accel_btnok', 'button', array( 'onclick' => 'seraph_accel.PluginAdmin.SwitchTo("' . $mode . '","' . add_query_arg( array( 'action' => 'seraph_accel_act', 'fn' => 'acceptEula' ), get_admin_url( NULL, 'admin-ajax.php' ) ) . '","' . Plugin::RmtCfgFld_GetLoc( PluginRmtCfg::Get(), 'Links.UrlProductInfo' ) . '")', 'class' => 'ctlSpaceAfter ctlVaMiddle' ) );
			$res .= Ui::Spinner( false, array( 'class' => 'ctlVaMiddle', 'style' => array( 'display' => 'none' ) ) );

		}
		$res .= Ui::TagClose( 'div' );

		return( $res );
	}

	static function DisplayAdminFooterRateItContent()
	{
		if( self::_RateIt_ShouldShow() !== false )
			add_filter( 'admin_footer_text', __CLASS__ . '::_OnFilter_AdminFooterText', 10, 1 );
	}

	static private function _GetRateItUrl( $rmtCfg )
	{
		$url = Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlRateIt' );
		if( !$url )
			$url = 'https://wordpress.org/support/plugin/seraphinite-accelerator/reviews?rate=5#new-post';
		return( $url );
	}

	static function GetRateItTitle()
	{
		return( esc_html_x( 'Title', 'admin.Common_RateIt', 'seraphinite-accelerator' ) );
	}

	static function GetRateItContent( $blockId, $type = Plugin::DisplayContent_Block )
	{
		if( self::_RateIt_ShouldShow() === false )
			return( null );

		$rmtCfg = PluginRmtCfg::Get();

		$res = '';

		$res .= self::_GetRateItContent( $type );

		$res .= '<br/><br/>' . Ui::Button( esc_html_x( 'RateSmallBtn', 'admin.Common_RateIt', 'seraphinite-accelerator' ), true, null, 'ctlSpaceAfter', 'button', array( 'onclick' => 'window.open(\'' . self::_GetRateItUrl( $rmtCfg ) . '\', \'_blank\')' ) );

		$res .= Ui::Tag( 'span',
			Ui::Tag( 'input', null, array( 'type' => 'button', 'class' => 'button-link', 'style' => array( 'margin-right' => '1em', 'vertical-align' => 'middle' ), 'value' => esc_html( Wp::GetLocString( 'Dismiss' ) ), 'onclick' => 'seraph_accel.PluginAdmin.RateItCont_Set(\'' . $blockId . '\',false,\'' . add_query_arg( array( 'action' => 'seraph_accel_act', 'fn' => '' ), get_admin_url( null, 'admin-ajax.php' ) ) . '\');return false;' ) ),
			array( 'class' => 'actions' )
		);

		$res .= Ui::Spinner( false, array( 'style' => array( 'display' => 'none', 'vertical-align' => 'middle' ) ) );

		return( $res );
	}

	static function _GetRateItContent( $type )
	{
		$rmtCfg = PluginRmtCfg::Get();

		$res = vsprintf( esc_html_x( 'Info_%1$s%2$s%3$s%4$s%5$s', 'admin.Common_RateIt', 'seraphinite-accelerator' ), Gen::ArrFlatten( array(
			Ui::Link( '&#9733;&#9733;&#9733;&#9733;&#9733;', self::_GetRateItUrl( $rmtCfg ), true ),
			$type == Plugin::DisplayContent_Str ? '' : ' ' . esc_html_x( 'Info_P2', 'admin.Common_RateIt', 'seraphinite-accelerator' ),
			$type == Plugin::DisplayContent_SmallBlock ? '<br/><br/>' : ' ',
			Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlRatingAboutUs' ), true )
		) ) );

		return( $res );
	}

	static function OutputNotAcceptedPageContent()
	{
		Ui::PostBoxes( Plugin::GetPluginString( 'TitleLong' ) );
	}

	static function GetNavMenuTitle()
	{
		return( Plugin::GetPluginString( 'Title' ) );
	}

	static function GetSettingsTitle()
	{
		return( Plugin::GetSubjectTitle( esc_html( Wp::GetLocString( 'Settings' ) ) ) );
	}

	static function GetSubjectTitle( $name )
	{
		return( sprintf( esc_html_x( 'PluginSubjectTitle_%1$s%2$s', 'admin.Common', 'seraphinite-accelerator' ), Plugin::GetPluginString( 'TitleLong' ), $name ) );
	}

	static function GetPluginString( $id )
	{
		$id = 'Plugin' . $id;
		return( esc_html_x( $id, 'admin.Common', 'seraphinite-accelerator' ) );

		esc_html_x( 'PluginTitle', 'admin.Common', 'seraphinite-accelerator' );
		esc_html_x( 'PluginTitleLong', 'admin.Common', 'seraphinite-accelerator' );
		esc_html_x( 'PluginTitleFull', 'admin.Common', 'seraphinite-accelerator' );

		esc_html_x( 'PluginDescription', 'admin.Common', 'seraphinite-accelerator' );
		esc_html_x( 'PluginDescriptionFull', 'admin.Common', 'seraphinite-accelerator' );

		esc_html_x( 'PluginSubjectTitle_%1$s%2$s', 'admin.Common', 'seraphinite-accelerator' );
		esc_html_x( 'PluginNameToDetails_%1$s%2$s', 'admin.Common', 'seraphinite-accelerator' );

		esc_html_x( 'Start', 'admin.Common', 'seraphinite-accelerator' );

		esc_html_x( 'FuncBlocked_%1$s', 'admin.Common_Msg', 'seraphinite-accelerator' );
		esc_html_x( 'ExecErr_%1$s', 'admin.Common_Msg', 'seraphinite-accelerator' );
		esc_html_x( 'ExecErrCode_%1$s%2$d%3$s', 'admin.Common_Msg', 'seraphinite-accelerator' );
		esc_html_x( 'TmpFileCreateErr_%1$s', 'admin.Common_Msg', 'seraphinite-accelerator' );
		esc_html_x( 'DirWriteErr_%1$s', 'admin.Common_Msg', 'seraphinite-accelerator' );
		esc_html_x( 'FileNotFound_%1$s', 'admin.Common_Msg', 'seraphinite-accelerator' );
		esc_html_x( 'FileWriteErr_%1$s', 'admin.Common_Msg', 'seraphinite-accelerator' );
		esc_html_x( 'FileReadErr_%1$s', 'admin.Common_Msg', 'seraphinite-accelerator' );
		esc_html_x( 'FileModifyErr_%1$s', 'admin.Common_Msg', 'seraphinite-accelerator' );
		esc_html_x( 'FileRenameErr_%1$s%2$s', 'admin.Common_Msg', 'seraphinite-accelerator' );
		esc_html_x( 'FileDeleteErr_%1$s', 'admin.Common_Msg', 'seraphinite-accelerator' );
		esc_html_x( 'FileMakeExecErr_%1$s', 'admin.Common_Msg', 'seraphinite-accelerator' );

		esc_html_x( 'NetDownloadErr_%1$s', 'admin.Common_Msg', 'seraphinite-accelerator' );
		esc_html_x( 'NetMimeErr_%1$s', 'admin.Common_Msg', 'seraphinite-accelerator' );

		esc_html_x( 'PhpExecMdlNotFound_%1$s', 'admin.Common_Msg', 'seraphinite-accelerator' );
		esc_html_x( 'PhpExtDirSuffix_%1$s%2$s', 'admin.Common_Msg', 'seraphinite-accelerator' );
	}

	static function GetAboutPluginTitle()
	{
		return( esc_html_x( 'Title', 'admin.Common_About', 'seraphinite-accelerator' ) );
	}

	static function GetAboutPluginContent()
	{
		$rmtCfg = PluginRmtCfg::Get();

		$urlProductInfo = Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlProductInfo' );
		$urlAboutPluginImg = file_exists( __DIR__ . '/../Images/ProductLogo.png' ) ? add_query_arg( array( 'v' => '2.22.14' ), Plugin::FileUri( '../Images/ProductLogo.png', __FILE__ ) ) : null;
		$urlAboutPluginDocs = Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlProductDocs' );
		$urlAboutPluginSupport = Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlProductSupport' );
		$url3rdPartySoft = file_exists( __DIR__ . '/../third-party-software.html' ) ? add_query_arg( array( 'v' => '2.22.14' ), Plugin::FileUri( '../third-party-software.html', __FILE__ ) ) : null;

		$urlEula = null;

		$res = '';

		$res .= Ui::Tag( 'p' );

		{
			$version = esc_html( '2.22.14' );

			$res .= Ui::TagOpen( 'div' );

			if( !empty( $urlAboutPluginImg ) )
				$res .= Ui::Link( Ui::Tag( 'img', null, array( 'class' => 'ctlSpaceAfter', 'width' => 100, 'style' => array( 'float' => 'left' ), 'src' => $urlAboutPluginImg ), true ), $urlProductInfo, true );

			$res .= '<h3 style="margin:0">' . esc_html_x( 'PluginTitleFullWithSuffix', 'admin.Common', 'seraphinite-accelerator' ) . '</h3>';

			$res .= Ui::Tag( 'div', vsprintf( esc_html( esc_html_x( 'Version_%1$s%2$s%3$s', 'admin.Common_About', 'seraphinite-accelerator' ) ), Gen::ArrFlatten( array( Ui::Tag( 'strong', array( '', '' ) ), $version ) ) ), array( 'class' => 'pluginVersion', 'style' => array( 'margin-top' => '0.3em' ) ) );

			$res .= Ui::TagClose( 'div' );
		}

		$res .= Ui::Tag( 'p', esc_html_x( 'PluginDescriptionFull', 'admin.Common', 'seraphinite-accelerator' ) );

		{
			$linksPrms = array( 'noTextIfNoHref' => true, 'linkPreContent' => Ui::TagOpen( 'span', array( 'style' => array( 'display' => 'block' ) ) ), 'linkAfterContent' => Ui::TagClose( 'span' ) );

			$resPart = '';

			$resPart .= Ui::Link( esc_html_x( 'PluginDocLink', 'admin.Common', 'seraphinite-accelerator' ), $urlAboutPluginDocs, true, $linksPrms );

			$resPart .= Ui::Link( esc_html_x( 'Link3rdPartySoft', 'admin.Common_About', 'seraphinite-accelerator' ), $url3rdPartySoft, true, $linksPrms );

			$res .= Ui::Tag( 'p', $resPart, null, false, array( 'noTagsIfNoContent' => true ) );
		}

		{
			$resPart = '';

			if( !empty( $urlAboutPluginSupport ) )
				$resPart .= Ui::Button( esc_html_x( 'LinkSupport', 'admin.Common_About', 'seraphinite-accelerator' ), false, null, 'ctlSpaceAfter', 'button', array( 'onclick' => 'window.open( \'' . $urlAboutPluginSupport . '\', \'_blank\' )' ) );

			$res .= Ui::Tag( 'p', $resPart, null, false, array( 'noTagsIfNoContent' => true ) );
		}

		return( $res );
	}

	static function GetAboutVendorTitle()
	{
		return( esc_html_x( 'Title', 'admin.Common_AboutVendor', 'seraphinite-accelerator' ) );
	}

	static function GetAboutVendorContent()
	{
		$rmtCfg = PluginRmtCfg::Get();

		$urlAboutUsLogoImg = file_exists( __DIR__ . '/../Images/VendorLogo.png' ) ? add_query_arg( array( 'v' => '2.22.14' ), Plugin::FileUri( '../Images/VendorLogo.png', __FILE__ ) ) : null;
		$urlMorePlugins = Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlMorePlugins' );
		$urlMoreInfo = Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlMain' );

		$res = '';

		if( !empty( $urlAboutUsLogoImg ) )
			$res .= Ui::Tag( 'p', Ui::Link( Ui::Tag( 'img', null, array( 'src' => $urlAboutUsLogoImg ), true ), $urlMoreInfo, true ) );

		$res .= Ui::Tag( 'p', esc_html_x( 'Info1', 'admin.Common_AboutVendor', 'seraphinite-accelerator' ) );

		$res .= Ui::Tag( 'p', vsprintf( esc_html_x( 'Info2_%1$s%2$s%3$s%4$s%5$s%6$s', 'admin.Common_AboutVendor', 'seraphinite-accelerator' ), Gen::ArrFlatten( array(
			Ui::Link( Ui::Tag( 'strong', array( '', '' ) ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlProductSupport' ), true ),
			Ui::Tag( 'strong', array( '', '' ) ),
			Ui::Tag( 'strong', array( '', '' ) )
		) ) ) );

		{
			$resPart = '';

			if( !empty( $urlMorePlugins ) )
				$resPart .= Ui::Button( esc_html_x( 'MorePluginsBtn', 'admin.Common_AboutVendor', 'seraphinite-accelerator' ), false, null, 'ctlSpaceAfter', 'button', array( 'onclick' => 'window.open( \'' . $urlMorePlugins . '\', \'_blank\' )' ) );

			if( !empty( $urlMoreInfo ) )
				$resPart .= Ui::Button( esc_html_x( 'MoreInfoBtn', 'admin.Common_AboutVendor', 'seraphinite-accelerator' ), false, null, 'ctlSpaceAfter', 'button', array( 'onclick' => 'window.open( \'' . $urlMoreInfo . '\', \'_blank\' )' ) );

			$res .= Ui::Tag( 'p', $resPart, null, false, array( 'noTagsIfNoContent' => true ) );
		}

		return( $res );
	}

	static function GetSettingsLicenseTitle()
	{
		return( esc_html_x( 'SettingsTitle', 'admin.Common_Lic', 'seraphinite-accelerator' ) );
	}

	static function GetSettingsLicenseContent()
	{

		$rmtCfg = PluginRmtCfg::Get();

		$res = '';
		$res .= vsprintf( Wp::safe_html_x( 'ExtLicenseInfo_%1$s%2$s', 'admin.Common_SwitchTo', 'seraphinite-accelerator' ), Ui::Link( Ui::Tag( 'strong', array( '', '' ) ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlProductFeatures' ), true ) );

		return( Plugin::_GetSwitchToContent( $rmtCfg,

			esc_html_x( 'ExtLinkBtn', 'admin.Common_SwitchTo', 'seraphinite-accelerator' ),

			'ext', $res ) );

	}

	static function GetSwitchToFullTitle()
	{

		return( null );

	}

	static function GetLockedFeatureLicenseContent( $type = Plugin::DisplayContent_Block, $contentBefore = '', $contentInside = '' )
	{

		return( null );

	}

	static function GetAdvertProductsTitle()
	{
		return( esc_html_x( 'Title', 'admin.Common_AdvertProducts', 'seraphinite-accelerator' ) );
	}

	const ADVERTPRODUCTS_COLS_NUM = 2;
	const ADVERTPRODUCTS_IMG_WIDTH = 100;

	static function GetAdvertProductsContent( $idBlock )
	{

		$rmtCfg = PluginRmtCfg::Get();

		$urlRequest = Gen::GetArrField( $rmtCfg, 'Prms.UrlSpecialApi' );
		if( empty( $urlRequest ) )
			return( '' );

		$urlMorePlugins = Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlMorePlugins' );

		$res = '';

		$licHtmlContent = Plugin::GetLockedFeatureLicenseContent( Plugin::DisplayContent_Block, '', vsprintf( esc_html_x( 'LockedFeatureInsideInfo_%1$s%2$s%3$s%4$s', 'admin.Common_AdvertProducts', 'seraphinite-accelerator' ), Gen::ArrFlatten( array( Ui::Tag( 'strong', array( '', '' ) ), Ui::Tag( 'strong', array( '', '' ) ) ) ) ) );
		if( !empty( $licHtmlContent ) )
			$res .= Ui::Tag( 'p', $licHtmlContent ) . Ui::SepLine( 'p' );

		$res .= Ui::Tag( 'div',
			Ui::Tag( 'div', Ui::Spinner( true, array( 'style' => array( 'vertical-align' => 'middle' ) ) ), array( 'style' => array( 'text-align' => 'center' ) ) ),
			array( 'data-oninit' => 'seraph_accel.PluginAdmin.AdvertProductsContentInit(this,"' . esc_url( $urlRequest ) . '","' . Wp::GetLocale() . '","' . esc_html_x( 'ItemMoreInfoBtn', 'admin.Common_AdvertProducts', 'seraphinite-accelerator' ) . '",' . self::ADVERTPRODUCTS_COLS_NUM . ',' . self::ADVERTPRODUCTS_IMG_WIDTH . ')' ) );

		$res .= Ui::Tag( 'p', vsprintf( esc_html_x( 'TotalInfo_%1$s%2$s', 'admin.Common_AdvertProducts', 'seraphinite-accelerator' ), Ui::Link( Ui::Tag( 'strong', array( '', '' ) ), $urlMorePlugins, true ) ), array( 'style' => array( 'text-align' => 'center' ) ) );
		if( !empty( $urlMorePlugins ) )
			$res .= Ui::Tag( 'p', Ui::Button( esc_html_x( 'MorePluginsBtn', 'admin.Common_AboutVendor', 'seraphinite-accelerator' ), false, null, 'ctlSpaceAfter', 'button', array( 'onclick' => 'window.open( \'' . $urlMorePlugins . '\', \'_blank\' )' ) ), array( 'style' => array( 'text-align' => 'center' ) ) );

		return( $res );
	}

	static function Sett_SaveBtn( $name, $full = true )
	{
		$res = Ui::Button( esc_html( Wp::GetLocString( 'Save Changes' ) ), true, $name, null, 'submit', array( 'class' => 'ctlSpaceAfter ctlSpaceVAfter', 'style' => array( 'min-width' => '7em', 'vertical-align' => 'middle' ) ) );
		if( !$full )
			return( $res );

		$urlAct = add_query_arg( array( 'action' => 'seraph_accel_act', 'fn' => '' ), get_admin_url( NULL, 'admin-ajax.php' ) );

		$res .=
			Ui::Button( esc_html( Wp::GetLocString( 'Restore' ) ), false, null, null, 'button', array( 'class' => 'ctlSpaceAfter ctlSpaceVAfter', 'style' => array( 'min-width' => '7em' ), 'onclick' => 'if(confirm("' . esc_html_x( 'RestoreConfirm', 'admin.Common_Settings', 'seraphinite-accelerator' ) . '"))window.location.href="' . add_query_arg( array( 'seraph_accel_settingsOp' => 'reset', '_' => time() , '_wpnonce' => wp_create_nonce( 'reset' ) ), $_SERVER[ 'REQUEST_URI' ] ) . '"' ) ) .
			Ui::Button( esc_html( Wp::GetLocString( 'Import' ) ), false, null, null, 'button', array( 'class' => 'ctlSpaceAfter ctlSpaceVAfter', 'style' => array( 'min-width' => '7em' ), 'onclick' => 'jQuery("#seraph_accel_settings_import_file").trigger("click")' ) ) .
			Ui::Button( esc_html( Wp::GetLocString( 'Export' ) ), false, null, null, 'button', array( 'class' => 'ctlSpaceAfter ctlSpaceVAfter', 'style' => array( 'min-width' => '7em' ), 'onclick' => 'seraph_accel.PluginAdmin.SettExport(this,"' . $urlAct . '")' ) ) .
			Ui::Spinner( false, array( 'class' => 'ctlSpaceAfter', 'style' => array( 'display' => 'none' ) ) ) .
			Ui::InputBox( 'file', 'seraph_accel_settings_import_file', null, array( 'style' => array( 'display' => 'none' ), 'accept' => '.json', 'onchange' => 'seraph_accel.PluginAdmin.SettImport(this,"' . $urlAct . '",this.files.length?this.files[0]:null,"' . wp_create_nonce( 'import' ) . '")' ) ) .
			Ui::InputBox( 'hidden', '_wpnonce', wp_create_nonce( 'save' ), null, true ) .
			'';

		return( $res );
	}

	static function Sett_WizardBtns( $name )
	{
		$res = '';
		$res .= Ui::Button( esc_html_x( 'WizPrevBtn', 'admin.Common_Settings', 'seraphinite-accelerator' ), false, null, null, 'button', array( 'class' => 'wizPrev ctlSpaceAfter ctlSpaceVAfter ctlVaMiddle', 'style' => array( 'min-width' => '7em' ), 'disabled' => true, 'onclick' => 'seraph_accel.PluginAdmin.OnSettWizNext(this,false)' ) );
		$res .= Ui::Button( esc_html_x( 'WizNextBtn', 'admin.Common_Settings', 'seraphinite-accelerator' ), false, null, null, 'button', array( 'class' => 'wizNext ctlSpaceAfter ctlSpaceVAfter ctlVaMiddle', 'style' => array( 'min-width' => '7em' ), 'disabled' => true, 'onclick' => 'seraph_accel.PluginAdmin.OnSettWizNext(this,true)' ) );
		$res .= Ui::Button( esc_html_x( 'WizFinishBtn', 'admin.Common_Settings', 'seraphinite-accelerator' ), true, $name, null, 'submit', array( 'class' => 'wizFinish ctlSpaceAfter ctlSpaceVAfter ctlVaMiddle', 'style' => array( 'min-width' => '7em', 'display' => 'none' ), 'data-oninit' => 'seraph_accel.PluginAdmin.OnSettWizNext(this,false)' ) );
		$res .= Ui::InputBox( 'hidden', '_wpnonce', wp_create_nonce( 'finish' ), null, true );
		return( $res );

		esc_html_x( 'WizStartBtn', 'admin.Common_Settings', 'seraphinite-accelerator' );
		esc_html_x( 'TitleWiz', 'admin.Common_Settings', 'seraphinite-accelerator' );
	}

	static function Sett_SaveResultBannerMsg( $hr, $opts = 0, $attrs = null, $errorCtxAlt = null )
	{
		if( $hr == Gen::S_OK || $hr == Gen::S_FALSE )
			return( Ui::BannerMsg( Ui::MsgSucc, esc_html_x( 'SaveSuccInfo', 'admin.Common_Settings', 'seraphinite-accelerator' ), $opts, $attrs ) );

		if( Gen::HrSucc( $hr ) )
			return( Ui::BannerMsg( Ui::MsgWarn, sprintf( esc_html_x( 'SaveWarnInfo_%1$s', 'admin.Common_Settings', 'seraphinite-accelerator' ), Plugin::GetErrorDescr( $hr, $errorCtxAlt, true ) ), $opts, $attrs ) );

		return( Ui::BannerMsg( Ui::MsgErr, sprintf( esc_html_x( 'SaveErrInfo_%1$s', 'admin.Common_Settings', 'seraphinite-accelerator' ), Plugin::GetErrorDescr( $hr, $errorCtxAlt ) ), $opts, $attrs ) );
	}

	static function SettGet( $settRawExt = null )
	{
		return( PluginOptions::Get( PLUGIN_SETT_VER, 'Sett', 'seraph_accel\\', $settRawExt ) );
	}

	static function SettGetGlobal( $settRawExt = null )
	{
		if( !is_multisite() )
			return( Plugin::SettGet( $settRawExt ) );

		$restoreBlog = false;
		if( get_current_blog_id() != BLOG_ID_CURRENT_SITE )
		{
			switch_to_blog( BLOG_ID_CURRENT_SITE );
			$restoreBlog = true;
		}

		$sett = Plugin::SettGet( $settRawExt );

		if( $restoreBlog )
			restore_current_blog();

		return( $sett );
	}

	static function SettSet( $data, $updateCacheOnly = false )
	{
		return( PluginOptions::Set( PLUGIN_SETT_VER, 'Sett', $data, 'seraph_accel\\', $updateCacheOnly ) );
	}

	static function SettSetGlobal( $data, $updateCacheOnly = false )
	{
		if( !is_multisite() )
			return( Plugin::SettSet( $data, $updateCacheOnly ) );

		$restoreBlog = false;
		if( get_current_blog_id() != BLOG_ID_CURRENT_SITE )
		{
			switch_to_blog( BLOG_ID_CURRENT_SITE );
			$restoreBlog = true;
		}

		$res = Plugin::SettSet( $data, $updateCacheOnly );

		if( $restoreBlog )
			restore_current_blog();

		return( $res );
	}

	static function SettCacheClear()
	{
		return( PluginOptions::CacheClear( 'Sett' ) );
	}

	static function SettReset()
	{
		return( PluginOptions::Del( 'Sett', 'seraph_accel\\' ) );
	}

	static function DataGet( $dataRawExt = null )
	{
		return( PluginOptions::Get( PLUGIN_DATA_VER, 'Data', 'seraph_accel\\', $dataRawExt ) );
	}

	static function DataSet( $data )
	{
		return( PluginOptions::Set( PLUGIN_DATA_VER, 'Data', $data, 'seraph_accel\\' ) );
	}

	static function StateGet()
	{
		return( PluginOptions::Get( self::STATE_VER, 'State', __CLASS__ . '::' ) );
	}

	static function StateSet( $data )
	{
		return( PluginOptions::Set( self::STATE_VER, 'State', $data, __CLASS__ . '::' ) );
	}

	static function StateUpdateFlds( array $fields )
	{
		$state = Plugin::StateGet();

		foreach( $fields as $id => $val )
		{
			if( $val === null )
				unset( $state[ $id ] );
			else
				$state[ $id ] = $val;
		}

		return( Plugin::StateSet( $state ) );
	}

	static function PostSettGet( $postId, $cbType = 'Post' )
	{
		$ver = constant( 'seraph_accel\\PLUGIN_' . strtoupper( $cbType ) . '_SETT_VER' );
		return( PluginPostOptions::Get( $postId, $ver, 'Sett', $cbType, 'seraph_accel\\' ) );
	}

	static function PostSettSet( $postId, $data, $cbType = 'Post' )
	{
		$ver = constant( 'seraph_accel\\PLUGIN_' . strtoupper( $cbType ) . '_SETT_VER' );
		return( PluginPostOptions::Set( $postId, $ver, 'Sett', $data, $cbType, 'seraph_accel\\' ) );
	}

	static function PostDataGet( $postId, $cbType = 'Post' )
	{
		$ver = constant( 'seraph_accel\\PLUGIN_' . strtoupper( $cbType ) . '_DATA_VER' );
		return( PluginPostOptions::Get( $postId, $ver, 'Data', $cbType, 'seraph_accel\\' ) );
	}

	static function PostDataSet( $postId, $data, $cbType = 'Post' )
	{
		$ver = constant( 'seraph_accel\\PLUGIN_' . strtoupper( $cbType ) . '_DATA_VER' );
		return( PluginPostOptions::Set( $postId, $ver, 'Data', $data, $cbType, 'seraph_accel\\' ) );
	}

	static function GetRelateRootPath()
	{
		$pluginAbsPath = Gen::ToUnixSlashes( dirname( __DIR__ ) );
		$wpAbsPath = realpath( path_join( $pluginAbsPath, '../../..' ) );
		return( substr( $pluginAbsPath, strlen( $wpAbsPath ) + 1 ) );
	}

	static function GetAbsoluteRootPath( $path = '' )
	{
		$wpAbsPath = Gen::ToUnixSlashes( realpath( path_join( dirname( __DIR__ ), '../../..' ) ) );
		if( !$path )
			return( $wpAbsPath );

		$targetAbsPath = path_join( $wpAbsPath, $path );
		$res = realpath( $targetAbsPath );
		return( $res ? $res : $targetAbsPath );
	}

	static function GetUri( $siteUrlRelative = false )
	{
		return( Plugin::FileUri( 'seraphinite-accelerator', null, $siteUrlRelative ) );
	}

	static function GetApiUri2( $funcName = '', $args = array() )
	{
		$res = add_query_arg( array_merge( array( 'action' => 'seraph_accel_api', 'fn' => $funcName ), $args ), get_admin_url( NULL, 'admin-post.php' ) );
		if( empty( $funcName ) )
			$res .= '=';
		return( $res );
	}

	static function IsApiRequest()
	{
		return( isset( $_REQUEST[ 'seraph_accel_api' ] ) );
	}

	static function GetApiUri( $funcName = '', $args = array(), $urlBase = '' )
	{
		if( $urlBase === null )
			$urlBase = Wp::GetSiteWpRootUrl( 'index.php' );

		$res = add_query_arg( array_merge( array( 'seraph_accel_api' => $funcName ), $args ), $urlBase );
		if( empty( $funcName ) )
			$res .= '=';
		return( $res );
	}

	static function GetAdminApiUri( $funcName = '', $args = array() )
	{
		$res = add_query_arg( array_merge( array( 'action' => 'seraph_accel_api', 'fn' => $funcName ), $args ), Net::Url2Uri( get_admin_url( NULL, 'admin-ajax.php' ) ) );
		if( empty( $funcName ) )
			$res .= '=';
		return( $res );
	}

	static private $g_bApiCall_Output = true;

	static function ApiCall_EnableOutput( $enable = true )
	{
		$enable = !!$enable;

		if( self::$g_bApiCall_Output == $enable )
			return;

		self::$g_bApiCall_Output = $enable;

		if( $enable )
		{
			for( $l = ob_get_level(); $l > 0; $l-- )
				ob_end_clean();
		}
		else
			ob_start( function( $data ){ return( '' ); }, 128 );
	}

	static function GetAvailablePlugins( $bActiveOnly = true )
	{
		$res = array();

		$plugins = Plugin::GetAvailablePluginsEx( $bActiveOnly );
		foreach( $plugins as $id => $data )
			$res[] = $id;

		return( $res );
	}

	static function GetAvailablePluginsEx( $bActiveOnly = false )
	{
		$res = array();

		if( !function_exists( 'get_plugins' ) )
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

		$plugins = get_plugins();
		foreach( $plugins as $id => $data )
		{
			$isActive = is_plugin_active( $id );
			if( $bActiveOnly && !$isActive )
				continue;

			$slug = null;
			$dataFile = null;
			{
				$a = explode( '/', $id );
				if( is_array( $a ) && count( $a ) > 1 )
				{
					$slug = $a[ 0 ];
					$dataFile = $a[ 1 ];
				}
			}

			if( !$slug || !$dataFile )
				continue;

			$data[ 'DataFile' ] = $dataFile;
			$data[ 'IsActive' ] = $isActive;

			$res[ $slug ] = $data;
		}

		return( $res );
	}

	static function _IsSwitchingActive()
	{
		$switchToArg = 'seraph_accel_switchto';
		return( isset( $_REQUEST[ $switchToArg ] ) );
	}

	static function _PrevVer_Confirm()
	{
		Plugin::StateUpdateFlds( array( 'warnChangeVer' => null ) );
	}

	static function _RateIt_Set( $mode )
	{
		if( !$mode )
			$v = false;
		else
		{
			$rmtCfg = PluginRmtCfg::Get();
			$curTime = intval( time() / 60 );

			$rateItSpanTimeNext = Gen::GetArrField( $rmtCfg, 'Prms.RateItSpanTimeNext', 1440 );
			$v = $curTime + $rateItSpanTimeNext;
		}

		Plugin::StateUpdateFlds( array( 'rateItRemind' => $v ) );
	}

	static private function _Sett_Import()
	{
		$data = file_get_contents( 'php://input' );
		$data = @json_decode( $data, true );
		if( !$data || (isset($data[ 't' ])?$data[ 't' ]:null) != 'mdl:seraph_accel' )
			return( false );

		unset( $data[ 't' ] );

		Plugin::SettCacheClear();
		$sett = Plugin::SettGet( $data );

		if( !isset( $sett[ PluginOptions::VERPREV ] ) )
			$sett[ PluginOptions::VERPREV ] = PLUGIN_SETT_VER;

		Plugin::SettSet( $sett );
		return( true );
	}

	static private function _Sett_Export()
	{
		$sett = Plugin::SettGet();
		unset( $sett[ PluginOptions::VERPREV ] );
		$sett[ 't' ] = 'mdl:seraph_accel';
		echo( @json_encode( $sett ) );
	}

	static private $_RateIt_ShouldShow = null;

	static function _RateIt_ShouldShow()
	{
		if( self::$_RateIt_ShouldShow !== null )
			return( self::$_RateIt_ShouldShow );
		return( self::$_RateIt_ShouldShow = self::_RateIt_ShouldShowEx() );
	}

	static private function _RateIt_ShouldShowEx()
	{
		$state = Plugin::StateGet();

		$mode = (isset($state[ 'rateItRemind' ])?$state[ 'rateItRemind' ]:null);
		if( $mode === false )
			return( false );

		$rmtCfg = PluginRmtCfg::Get();

		$rateItSpanTime = Gen::GetArrField( $rmtCfg, 'Prms.RateItSpanTime', null );
		if( $rateItSpanTime === null )
			return( false );

		if( $mode !== null )
		{
			$curTime = intval( time() / 60 );
			return( $mode < $curTime ? true : 'postponed' );
		}

		$startUseTime = intval( $state[ 'firstUseTimeStamp' ] / 60 );
		$state[ 'rateItRemind' ] = $startUseTime + $rateItSpanTime;
		Plugin::StateSet( $state );
		return( 'postponed' );
	}

	static private function _PrevVer_GetInt( $ver )
	{

		$v = 0;

		$a = explode( '.', $ver );
		if( count( $a ) >= 1 )
			$v = $v + $a[ 0 ] * 0x100 * 0x100 * 0x100;
		if( count( $a ) >= 2 )
			$v = $v + $a[ 1 ] * 0x100 * 0x100;
		if( count( $a ) >= 3 )
			$v = $v + $a[ 2 ] * 0x100;
		if( count( $a ) >= 4 )
			$v = $v + $a[ 3 ];

		return( $v );
	}

	static private function _PrevVer_Check()
	{
		$state = Plugin::StateGet();
		$warningVersionInfo = (isset($state[ 'warnChangeVer' ])?$state[ 'warnChangeVer' ]:null);

		$plgVerPrev = (isset($state[ 'changeVerCheck' ])?$state[ 'changeVerCheck' ]:null);
		$plgPkPrev = (isset($state[ 'changePkCheck' ])?$state[ 'changePkCheck' ]:null);
		if( $plgVerPrev !== null || $plgPkPrev !== null )
		{
			unset( $state[ 'changeVerCheck' ] );
			unset( $state[ 'changePkCheck' ] );
			Plugin::StateSet( $state );

			if( Gen::DoesFuncExist( 'seraph_accel\\OnChangeVer' ) )
				Gen::CallFuncArraySafe( 'seraph_accel\\OnChangeVer', array( $plgVerPrev, $plgPkPrev ) );
		}

		if( empty( $warningVersionInfo ) )
		{
			if( !$plgVerPrev )
				return( null );

			$warningChangeVersions = Gen::GetArrField( PluginRmtCfg::Get(), 'Prms.WarningChangeVersions' );
			if( !is_array( $warningChangeVersions ) )
				return( null );

			$verFrom = self::_PrevVer_GetInt( $plgVerPrev );
			$verTo = self::_PrevVer_GetInt( '2.22.14' );
			if( $verTo < $verFrom )
				list( $verTo, $verFrom ) = array( $verFrom, $verTo );

			foreach( $warningChangeVersions as $warningChangeVersion )
			{
				$verCheck = self::_PrevVer_GetInt( $warningChangeVersion );
				if( $verCheck >= $verFrom && $verCheck <= $verTo )
				{
					$warningVersionInfo = $plgVerPrev;
				}
			}

			if( empty( $warningVersionInfo ) )
				return( null );

			$state[ 'warnChangeVer' ] = $warningVersionInfo;
			Plugin::StateSet( $state );
		}

		$rmtCfg = PluginRmtCfg::Get();

		$url = add_query_arg( array( 'action' => 'seraph_accel_act', 'fn' => '' ), get_admin_url( NULL, 'admin-ajax.php' ) );

		ob_start();

?>

		<strong>
			<?php echo( esc_html_x( 'PluginTitleFull', 'admin.Common', 'seraphinite-accelerator' ) ); ?>
		</strong>
		<p>
			<?php echo( vsprintf( esc_html_x( 'Info_%1$s%2$s', 'admin.Common_ChangeVersion', 'seraphinite-accelerator' ), Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlProductLastChanges' ), true ) ) ); ?>
		</p>

		<input style="margin-right:1em;vertical-align:middle;" type="button" class="button button-primary" value="<?php echo( esc_html_x( 'ConfirmBtn', 'admin.Common_ChangeVersion', 'seraphinite-accelerator' ) ); ?>" onclick="seraph_accel.PluginAdmin.ChangeVersion_Confirm('<?php echo( esc_url( $url ) ); ?>');" />
		<span style="display:none;vertical-align:middle;" class="seraph_accel_spinner"></span>

		<?php

		$res = ob_get_clean();

		return( Ui::BannerMsg( Ui::MsgWarn, $res, 0, array( 'id' => 'seraph_accel_ChangeVersion_Msg' ) ) );
	}

	static private function _RateIt_Check()
	{
		if( self::_RateIt_ShouldShow() !== true )
		    return( null );

		$rmtCfg = PluginRmtCfg::Get();
		$url = add_query_arg( array( 'action' => 'seraph_accel_act', 'fn' => '' ), get_admin_url( NULL, 'admin-ajax.php' ) );

		ob_start();

		?>

		<strong><?php echo( esc_html_x( 'PluginTitleFull', 'admin.Common', 'seraphinite-accelerator' ) ); ?></strong>
		<p><?php echo( esc_html_x( 'InfoPrefix', 'admin.Common_RateIt', 'seraphinite-accelerator' ) ); ?> <?php echo( self::_GetRateItContent( Plugin::DisplayContent_Block ) ); ?></p>

		<input style="margin-right:1em;vertical-align:middle;" type="button" class="button button-primary" value="<?php echo( esc_html_x( 'RateBtn', 'admin.Common_RateIt', 'seraphinite-accelerator' ) ); ?>" onclick="window.open('<?php echo( self::_GetRateItUrl( $rmtCfg ) ); ?>','_blank');" />

		<span class="actions">
			<input style="margin-right:1em;vertical-align:middle;" type="button" class="button-link" value="<?php echo( esc_html_x( 'PostponeBtn', 'admin.Common_RateIt', 'seraphinite-accelerator' ) ); ?>" onclick="seraph_accel.PluginAdmin.RateIt_Set(true,'<?php echo( esc_url( $url ) ); ?>');" />
			<input style="margin-right:1em;vertical-align:middle;" type="button" class="button-link" value="<?php echo( esc_html( Wp::GetLocString( 'Dismiss' ) ) ); ?>" onclick="seraph_accel.PluginAdmin.RateIt_Set(false,'<?php echo( esc_url( $url ) ); ?>');" />
		</span>

		<span style="display:none;vertical-align:middle;" class="seraph_accel_spinner"></span>

		<?php

		$res = ob_get_clean();

		return( Ui::BannerMsg( Ui::MsgInfo, $res, 0, array( 'id' => 'seraph_accel_RateIt_Message' ) ) );
	}

	static function _AcceptEula()
	{
		Plugin::StateUpdateFlds( array( 'eulaAcceptedVer' => namespace\PLUGIN_EULA_VER ) );
	}

	static private function _Eula_Check()
	{
		if( self::IsEulaAccepted() )
			return( null );

		$rmtCfg = PluginRmtCfg::Get();

		$acceptBtnName = esc_html_x( 'AcceptBtn', 'admin.Common_Eula', 'seraphinite-accelerator' );

		$res = '';
		$res .= '<strong>' . esc_html_x( 'PluginTitleFull', 'admin.Common', 'seraphinite-accelerator' ) . '</strong>';

		$res .= '<p>' . vsprintf( esc_html_x( 'AcceptInfo_%1$s%2$s%3$s', 'admin.Common_Eula', 'seraphinite-accelerator' ), Gen::ArrFlatten( array(
			Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlEula' ), true ),
			$acceptBtnName
		) ) ) . '</p>';

		$res .= '<input type="button" class="button button-primary" value="' . $acceptBtnName . '" style="margin-right: 0.5em;" onclick="window.location.href=\'' . add_query_arg( array( 'action' => 'seraph_accel_act', 'fn' => 'acceptEula', 'redir' => rawurlencode( $_SERVER[ 'REQUEST_URI' ] ), '_' => time()  ), get_admin_url( NULL, 'admin-ajax.php' ) ) . '\'" />';

		return( Ui::BannerMsg( Ui::MsgWarn, $res ) );
	}

	static function _OnFilter_AdminFooterText( $text )
	{
		return( '<span id="footer-thankyou">' . self::_GetRateItContent( Plugin::DisplayContent_Str ) . '</span>' );
	}

	static private $g_aAlreadyIncludedObj = NULL;

	static function CmnStyle( $ids )
	{
		if( !is_array( $ids ) )
			$ids = array( $ids );

		$fileUrl = Plugin::FileUrl( '', __FILE__ );

		$res = '';

		foreach( $ids as $id )
		{

			if( (isset(self::$g_aAlreadyIncludedObj[ 'css' ][ $id ])?self::$g_aAlreadyIncludedObj[ 'css' ][ $id ]:null) )
				continue;

			wp_enqueue_style( Plugin::CmnScriptId( $id ), add_query_arg( Plugin::GetFileUrlPackageParams(), $fileUrl . '/' . $id . '.css' ), array(), '2.22.14' );

			self::$g_aAlreadyIncludedObj[ 'css' ][ $id ] = true;
		}

		return( $res );
	}

	static function ScriptId( $id, $prefix = '' )
	{
		if( !is_array( $id ) )
			return( 'seraph_accel_' . ( $prefix ? ( $prefix . '_' ) : '' ) . $id );

		$res = array();
		foreach( $id as $idItem )
			$res[] = self::ScriptId( $idItem, $prefix );
		return( $res );
	}

	static function CmnScriptId( $id )
	{
		return( Plugin::ScriptId( $id ) );
	}

	static function ScriptMinName( $filepath )
	{

		$sepPos = strrpos( $filepath, '.' );
		if( $sepPos === false )
			return( $filepath );

		return( substr( $filepath, 0, $sepPos ) . '.min' . substr( $filepath, $sepPos ) );

	}

	static function CmnScripts( $ids )
	{
		wp_enqueue_script( 'jquery' );

		if( !is_array( $ids ) )
			$ids = array( $ids );

		$fileUrl = Plugin::FileUrl( '', __FILE__ );

		$res = '';

		foreach( $ids as $id )
		{
			if( (isset(self::$g_aAlreadyIncludedObj[ 'js' ][ $id ])?self::$g_aAlreadyIncludedObj[ 'js' ][ $id ]:null) )
				continue;

			$deps = array( 'jquery' );

			if( $id == 'Ui' )
			{
				if( is_admin() )
				{
					wp_enqueue_script( 'jquery-ui-sortable' );
					$deps[] = 'jquery-ui-sortable';
				}
			}

			if( $id != 'Cmn' )
				$deps[] = Plugin::CmnScriptId( 'Cmn' );

			$scrHndId = Plugin::CmnScriptId( $id );

			wp_register_script( $scrHndId, add_query_arg( Plugin::GetFileUrlPackageParams(), $fileUrl . '/' . $id . '.js' ), $deps, '2.22.14' );
			if( $id == 'Gen' )
				Plugin::Loc_ScriptLoad( $scrHndId );
			wp_enqueue_script( $scrHndId );

			if( $id == 'Gen' )
				Wp::AddInlineScript( $scrHndId, 'seraph_accel.Plugin._int.urlRoot="' . wp_slash( Plugin::FileUri( '', __DIR__ ) ) . '";seraph_accel.Plugin._int.urlAdminApi="' . wp_slash( Plugin::GetAdminApiUri() ) . '";seraph_accel.Wp.Loc._int.lang="' . Lang::GetLangFromLocale( Wp::GetLocale() ) . '";' );

			self::$g_aAlreadyIncludedObj[ 'js' ][ $id ] = true;
		}

		return( $res );
	}

	static function GetFileUrlPackageParams( $prms = null )
	{
		if( !$prms )
			$prms = array();

		$prms[ 'pk' ] = 'Base';

		return( $prms );
	}

	static function FileUrl( $path = null, $plugin = null )
	{

		$path = plugins_url( $path, $plugin );

		return( $path );
	}

	static function FileUri( $path = null, $plugin = null, $siteUrlRelative = false )
	{
		$path = Plugin::FileUrl( $path, $plugin );
		return( Net::Url2Uri( $path, $siteUrlRelative ) );
	}

	static function GetLocale()
	{
		return( self::$_locale );
	}

	static function RmtCfgFld_GetCtx( $rmtCfg )
	{
		return( RmtCfgFldLoc::GetCtx( $rmtCfg ) );
	}

	static function RmtCfgFld_GetLocEx( $ctx, $data, $fieldPath, $sep = '.' )
	{
		return( RmtCfgFldLoc::GetEx( Plugin::GetLocale(), $ctx, $data, $fieldPath, $sep ) );
	}

	static function RmtCfgFld_GetLoc( $rmtCfg, $fieldPath, $sep = '.' )
	{
		return( Plugin::RmtCfgFld_GetLocEx( Plugin::RmtCfgFld_GetCtx( $rmtCfg ), $rmtCfg, $fieldPath, $sep ) );
	}

	static function GetErrorDescr( $hr, $ctxAlt = null, $tryFindWarnAsError = false )
	{
		$hrLocKey = sprintf( 'Err_%08X', $hr );
		$hrLocKeyWarnAsErr = $tryFindWarnAsError && Gen::HrSucc( $hr ) ? sprintf( 'Err_%08X', Gen::HrMake( Gen::SEVERITY_ERROR, Gen::HrFacility( $hr ), $hr ) ) : null;

		$keysToFind = array();

		if( !empty( $ctxAlt ) )
		{
			$keysToFind[] = array( 'id' => $hrLocKey, 'ctx' => $ctxAlt );
			if( $hrLocKeyWarnAsErr )
				$keysToFind[] = array( 'id' => $hrLocKeyWarnAsErr, 'ctx' => $ctxAlt );
		}

		{
			$keysToFind[] = array( 'id' => $hrLocKey, 'ctx' => 'admin.ErrDescr_Common' );
			if( $hrLocKeyWarnAsErr )
				$keysToFind[] = array( 'id' => $hrLocKeyWarnAsErr, 'ctx' => 'admin.ErrDescr_Common' );

			$keysToFind[] = array( 'id' => 'Def_%08X', 'ctx' => 'admin.ErrDescr_Common' );
		}

		foreach( $keysToFind as $keyToFind )
		{
			$id = $keyToFind[ 'id' ];

			$errDescr = sprintf( esc_html_x( $id, $keyToFind[ 'ctx' ], 'seraphinite-accelerator' ), $hr );
			if( $errDescr != $id )
				return( $errDescr );
		}

		return( $hrLocKey );

		esc_html_x( 'Err_80004001',		'admin.ErrDescr_Common', 'seraphinite-accelerator' );
		esc_html_x( 'Err_80004005',		'admin.ErrDescr_Common', 'seraphinite-accelerator' );
		esc_html_x( 'Err_80004021',		'admin.ErrDescr_Common', 'seraphinite-accelerator' );
		esc_html_x( 'Err_80070005',		'admin.ErrDescr_Common', 'seraphinite-accelerator' );
		esc_html_x( 'Err_80070057',		'admin.ErrDescr_Common', 'seraphinite-accelerator' );
		esc_html_x( 'Err_80070490',		'admin.ErrDescr_Common', 'seraphinite-accelerator' );
		esc_html_x( 'Err_80070570',		'admin.ErrDescr_Common', 'seraphinite-accelerator' );
		esc_html_x( 'Err_800705B4',		'admin.ErrDescr_Common', 'seraphinite-accelerator' );
		esc_html_x( 'Err_800401F7',		'admin.ErrDescr_Common', 'seraphinite-accelerator' );
		esc_html_x( 'Err_8007078B',		'admin.ErrDescr_Common', 'seraphinite-accelerator' );
		esc_html_x( 'Def_%08X', 'admin.ErrDescr_Common', 'seraphinite-accelerator' );
	}

	static function _on_admin_action_act()
	{
		$fn = Wp::SanitizeId( (isset($_REQUEST[ 'fn' ])?$_REQUEST[ 'fn' ]:null) );
		if( !$fn )
		{
			wp_die( '', 400 );
			return;
		}

		unset( $_REQUEST[ 'action' ] );
		unset( $_REQUEST[ 'fn' ] );

		$processed = true;
		$redir = Wp::SanitizeUrl( (isset($_REQUEST[ 'redir' ])?$_REQUEST[ 'redir' ]:null) );
		switch( $fn )
		{
		case 'acceptEula':
			self::_AcceptEula();

			break;

		case 'changeVersionConfirm':
			self::_PrevVer_Confirm();
			break;

		case 'rateItDisable':
			self::_RateIt_Set( false );
			break;

		case 'rateItPostpone':
			self::_RateIt_Set( true );
			break;

		case 'settImport':
			$hr = Gen::S_OK;

			if( !current_user_can( 'manage_options' ) )
				$hr = Gen::E_ACCESS_DENIED;
			else if( !wp_verify_nonce( (isset($_REQUEST[ '_wpnonce' ])?$_REQUEST[ '_wpnonce' ]:''), 'import' ) )
				$hr = Gen::E_CONTEXT_EXPIRED;
			else if( !self::_Sett_Import() )
				$hr = Gen::E_FAIL;

			echo( Plugin::ReloadWithPostOpResEx( array( 'saveSettings' => $hr ), $redir ) );
			exit();
			break;

		case 'settExport':
			self::_Sett_Export();
			break;

		default:
			$processed = false;
		}

		if( !$processed )
		{
			wp_die( '', 404 );
			return;
		}

		if( !empty( $redir ) )
			wp_safe_redirect( $redir );

		exit();
	}

	static private function _ApiCall_Make( $userFuncId, $args )
	{
		if( !Gen::DoesFuncExist( $userFuncId ) )
		{
			wp_die( '', 404 );
			return;
		}

		foreach( $args as $argKey => $arg )
			if( is_string( $arg ) )
				$args[ $argKey ] = stripslashes( $arg );

		Plugin::ApiCall_EnableOutput( false );
		$res = call_user_func( $userFuncId, $args );
		Plugin::ApiCall_EnableOutput( true );

		if( $res === null )
			return;

		echo( wp_json_encode( $res ) );
	}

	static function _on_check_plugins_updates( $locales )
	{
		$hrUpdated = PluginRmtCfg::Update();

		return( $locales );
	}

	static function _admin_printscriptsstyles()
	{
		global $seraph_accel_g_plgPrintScriptsStyles;

		if( $seraph_accel_g_plgPrintScriptsStyles )
			return;

		$seraph_accel_g_plgPrintScriptsStyles = true;

		echo( Plugin::CmnScripts( array( 'Cmn', 'Gen', 'Ui', 'Net', 'AdminUi' ) ) );
	}

	static function _on_admin_notices()
	{
		$opsRes = Wp::SanitizeTextData( (isset($_REQUEST[ 'seraph_accel_postOpsRes' ])?$_REQUEST[ 'seraph_accel_postOpsRes' ]:null) );
		if( !empty( $opsRes ) )
			$opsRes = @json_decode( base64_decode( stripslashes( $opsRes ) ), true );

		$rmtCfg = PluginRmtCfg::Get();

		{
			if( version_compare( PHP_VERSION, '7.1' ) < 0 )
			{
				self::_admin_printscriptsstyles();
				echo( Ui::BannerMsg( Ui::MsgErr,
					Ui::Tag( 'strong', esc_html_x( 'PluginTitleFull', 'admin.Common', 'seraphinite-accelerator' ) ) .
					Ui::Tag( 'p', vsprintf( Wp::safe_html_x( 'PhpMinVerErr_%1$s%2$s%3$s%4$s', 'admin.Common', 'seraphinite-accelerator' ), Gen::ArrFlatten( array(
						PHP_VERSION, '7.1',
						Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlProductReqs' ), true )
					) ) ) )
					, 0 ) );
				return;
			}

			global $wp_version;
			if( version_compare( $wp_version, '4.5' ) < 0 )
			{
				self::_admin_printscriptsstyles();
				echo( Ui::BannerMsg( Ui::MsgErr,
					Ui::Tag( 'strong', esc_html_x( 'PluginTitleFull', 'admin.Common', 'seraphinite-accelerator' ) ) .
					Ui::Tag( 'p', vsprintf( Wp::safe_html_x( 'WpMinVerErr_%1$s%2$s%3$s%4$s', 'admin.Common', 'seraphinite-accelerator' ), Gen::ArrFlatten( array(
						$wp_version, '4.5',
						Ui::Link( array( '', '' ), Plugin::RmtCfgFld_GetLoc( $rmtCfg, 'Links.UrlProductReqs' ), true )
					) ) ) )
					, 0 ) );
				return;
			}
		}

		{
			$htmlCont = self::_Eula_Check();
			if( $htmlCont )
			{
				self::_admin_printscriptsstyles();
				echo( $htmlCont );

				return;
			}
		}

		{
			$htmlCont = self::_PrevVer_Check();
			if( $htmlCont )
			{
				self::_admin_printscriptsstyles();
				echo( $htmlCont );
			}
		}

		{
			$htmlCont = self::_RateIt_Check();
			if( $htmlCont )
			{
				self::_admin_printscriptsstyles();
				echo( $htmlCont );
			}
		}

		if( !empty( $opsRes ) )
			do_action( 'seraph_accel_postOpsRes', $opsRes );
	}

	static function OnOptRead_State( $state, $verFrom )
	{
		if( $verFrom === null )
		{
			$dataEula = PluginOptions::Get( namespace\PLUGIN_EULA_VER, 'Eula', __CLASS__ . '::' );
			$state[ 'eulaAcceptedVer' ] = (isset($dataEula[ 'acceptedVer' ])?$dataEula[ 'acceptedVer' ]:null);
			$state[ '_eulaClearPrevStorage' ] = true;
		}

		return( $state );
	}

	static function OnOptWrite_State( $state )
	{
		if( (isset($state[ '_eulaClearPrevStorage' ])?$state[ '_eulaClearPrevStorage' ]:null) )
		{
			PluginOptions::Del( 'Eula', __CLASS__ . '::' );
			unset( $state[ '_eulaClearPrevStorage' ] );
		}

		return( $state );
	}

	static function OnOptRead_Eula( $data, $verFrom )
	{
		$data[ 'acceptedVer' ] = $verFrom;
		return( $data );
	}

	static function _OnPopup_QuestionnaireDeactivate( $id, $prms )
	{
		$rmtCfg = PluginRmtCfg::Get();
		$rmtCfgFldCtx = Plugin::RmtCfgFld_GetCtx( $rmtCfg );

		$q = $prms[ 'q' ];
		$qParams = $q[ 'params' ];

		Ui::PostBoxes_Popup( $id, Plugin::RmtCfgFld_GetLocEx( $rmtCfgFldCtx, $qParams, 'Title' ),
			function( $callbacks_args, $box )
			{
				extract( $box[ 'args' ] );

				echo( Ui::Tag( 'p', Plugin::RmtCfgFld_GetLocEx( $rmtCfgFldCtx, $qParams, 'Description' ) ) );

				echo( Ui::SettBlock_ItemSubTbl_Begin( array( 'class' => 'std questionnaire_body' ) ) );
				{
					$groupName = 'seraph_accel_questionId';
					$qList = Gen::GetArrField( $qParams, 'Items', array() );
					$userDataMaxSymbolsGlobal = Plugin::RmtCfgFld_GetLocEx( $rmtCfgFldCtx, $rmtCfg, 'Questionnaires.UserDataMaxSymbols' );

					foreach( $qList as $itemId => $qListItem )
					{
						$commentText = Plugin::RmtCfgFld_GetLocEx( $rmtCfgFldCtx, $qListItem, 'Content' );
						$userData = Plugin::RmtCfgFld_GetLocEx( $rmtCfgFldCtx, $qListItem, 'UserData' );
						$isUerDataMultiline = (isset($qListItem[ 'IsUserDataMultiline' ])?$qListItem[ 'IsUserDataMultiline' ]:null);

						echo( Ui::TagOpen( 'tr', array( 'class' => 'item' ) ) . Ui::TagOpen( 'td' ) );

						$subContent = '';
						{
							if( !empty( $commentText ) )
								$subContent .= Ui::Tag( 'div', $commentText, array( 'class' => 'item description' ) );

							if( !empty( $userData ) )
							{
								$attrs = array( 'placeholder' => $userData, 'class' => 'item userdata', 'style' => array( 'width' => '100%' ) );
								if( !empty( $userDataMaxSymbolsGlobal ) )
									$attrs[ 'maxlength' ] = $userDataMaxSymbolsGlobal;

								if( $isUerDataMultiline )
								{
									$attrs[ 'style' ][ 'min-height' ] = 2 * (3/2) . 'em';
									$attrs[ 'style' ][ 'max-height' ] = 5 * (3/2) . 'em';
									$attrs[ 'type' ][ 'max-height' ] = 'text';
									$subContent .= Ui::Tag( 'textarea', null, $attrs );
								}
								else
									$subContent .= Ui::TextBox( null, null, $attrs );
							}
						}

						echo( Ui::RadioBox( Plugin::RmtCfgFld_GetLocEx( $rmtCfgFldCtx, $qListItem, 'Label' ), $groupName, $itemId ) );
						if( !empty( $subContent ) )
							echo( Ui::Tag( 'div', $subContent, array( 'class' => 'subblock', 'style' => array(  ) ) ) );

						echo( Ui::TagClose( 'td' ) . Ui::TagClose( 'tr' ) );
					}
				}
				echo( Ui::SettBlock_ItemSubTbl_End() );

				echo( Ui::TagOpen( 'span' ) );
				{
					echo( Ui::Button( esc_html( Wp::GetLocString( 'Deactivate' ) ), true, null, 'ctlSpaceAfter ctlVaMiddle actOk', 'button', array( 'disabled' => '', 'style' => array( 'min-width' => '7em' ), 'onclick' => '' ) ) );
					echo( Ui::Button( esc_html( Wp::GetLocString( 'Cancel' ) ), false, null, 'ctlSpaceAfter ctlVaMiddle actCancel', 'button', array( 'style' => array( 'min-width' => '7em' ), 'onclick' => '' ) ) );
					echo( Ui::Spinner( false, array( 'class' => 'ctlVaMiddle', 'style' => array( 'display' => 'none' ) ) ) );
				}
				echo( Ui::TagClose( 'span' ) );

?>

				<script>
					(function()
					{
						var box = jQuery( "#<?php echo( esc_attr( $box[ 'id' ] ) ); ?>" );
						var deactivateUrl = "<?php echo( $prms[ 'href' ] ); ?>";

						box.find( ".questionnaire_body .item input[type=\'radio\']" ).change(
							function()
							{
								box.find( ".questionnaire_body .item" ).removeClass( "selected" );
								jQuery( this ).closest( ".item" ).addClass( "selected" );

								box.find( ".actOk" ).prop( "disabled", false );
							}
						);

						box.find( ".actCancel" ).on( "click",
							function()
							{
								seraph_accel.Ui.BannerMsgClose( box );
							}
						);

						box.find( ".actOk" ).on( "click",
							function()
							{
								var selItem = box.find( ".questionnaire_body .item.selected" );
								_ProcessAnswerAndDeactivateAndClose(
									selItem.find( "input[type=\'radio\']" ).val(),
									selItem.find( ".userdata" ).val(),
								);
							}
						);

						function _DeactivateAndClose()
						{
							box.find( ".seraph_accel_spinner" ).hide();
							seraph_accel.Ui.BannerMsgClose( box );
							//


							location = deactivateUrl;
							//
						}

						function _ProcessAnswerAndDeactivateAndClose( answerId, answerUserData )
						{
							if( answerId == '_' )
							{
								_DeactivateAndClose();
								return;
							}

							box.find( ".actOk" ).prop( "disabled", true );
							box.find( ".seraph_accel_spinner" ).show();

							var sendDataUrl = "<?php echo( Gen::GetArrField( $rmtCfg, 'Questionnaires.SendAnswerUrlTpl' ) ); ?>";
							sendDataUrl = sendDataUrl.replace( "{EndPointId}",					encodeURI( "<?php echo( Wp::GetSiteId() ); ?>" ) );
							sendDataUrl = sendDataUrl.replace( "{PluginVersion}",				encodeURI( "2.22.14" ) );
							sendDataUrl = sendDataUrl.replace( "{PluginMode}",					encodeURI( "base" ) );
							sendDataUrl = sendDataUrl.replace( "{PluginPackage}",				encodeURI( "Base" ) );
							sendDataUrl = sendDataUrl.replace( "{QuestionnaireId}",				encodeURI( "<?php echo( (isset($q[ 'id' ])?$q[ 'id' ]:null) ); ?>" ) );
							sendDataUrl = sendDataUrl.replace( "{QuestionnaireVersionId}",		encodeURI( "<?php echo( (isset($qParams[ 'VersionId' ])?$qParams[ 'VersionId' ]:null) ); ?>" ) );
							sendDataUrl = sendDataUrl.replace( "{AnswerId}",					encodeURI( answerId ) );
							sendDataUrl = sendDataUrl.replace( "{AnswerUserData}",				answerUserData ? encodeURI( answerUserData ) : "" );

							jQuery.ajax( { url: sendDataUrl, type: "POST", data: "" } ).then(
								function( res )
								{
									_DeactivateAndClose();
								},

								function( res )
								{
									_DeactivateAndClose();
								},
							);
						}
					})();
				</script>

				<?php
			},
			get_defined_vars()
		);
	}

	static function OnActivate()
	{

		Gen::CallFunc( 'seraph_accel\\OnActivate' );
	}

	static function OnDeactivate()
	{

		Gen::CallFunc( 'seraph_accel\\OnDeactivate' );
	}

}

class RmtCfgFldLoc
{
	static function GetCtx( $rmtCfg )
	{
		$locToSiteLang = Gen::GetArrField( $rmtCfg, 'Prms.LocaleToSiteLang' );
		if( !is_array( $locToSiteLang ) )
			$locToSiteLang = array();
		return( array( 'locToSiteLang' => $locToSiteLang ) );
	}

	static function GetEx( $locale, $ctx, $data, $fieldPath, $sep = '.' )
	{
		$aLocaleSearch = array();
		{
			$aLocaleSearch[] = $locale;
			if( ( $posSep = strpos( $locale, '_' ) ) !== false )
				$aLocaleSearch[] = substr( $locale, 0, $posSep );
			$aLocaleSearch[] = '';
		}

		$v = null;
		foreach( $aLocaleSearch as $localeSearch )
		{
			$v = Gen::GetArrField( $data, $fieldPath . ( $localeSearch ? ( '-' . $localeSearch ) : '' ), null, $sep );
			if( $v !== null )
				break;
		}

		if( !is_string( $v ) )
			return( $v );

		$posSiteLang = strpos( $v, '{SiteLang}' );
		if( $posSiteLang === false )
			return( $v );

		$siteLang = null;
		{
			$locToSiteLang = $ctx[ 'locToSiteLang' ];

			$siteLang = null;
			foreach( $aLocaleSearch as $localeSearch )
			{
				$siteLang = (isset($locToSiteLang[ $localeSearch ])?$locToSiteLang[ $localeSearch ]:null);
				if( $siteLang !== null )
					break;
			}

			if( !is_string( $siteLang ) )
				$siteLang = '';
		}

		$v = substr( $v, 0, $posSiteLang ) . $siteLang . substr( $v, $posSiteLang + strlen( '{SiteLang}' ) );
		return( $v );
	}
}

function OnAdminApi_StateSet( $args )
{
	$fields = array();
	foreach( $args as $id => $val )
	{
		if( $val === '' )
			$val = null;
		else
		{
			$val = Wp::SanitizeText( $val );
			if( is_numeric( $val ) )
			{
				if( strpos( $val, '.' ) !== false )
					$val = floatval( $val );
				else
					$val = intval( $val );
			}
		}

		$fields[ Wp::SanitizeId( $id ) ] = $val;
	}

	Plugin::StateUpdateFlds( $fields );
}


<?php

class MailsterTemplate {

	public $raw;
	public $doc;
	public $data;
	public $modules;

	public $path;
	public $url;

	public $slug;
	private $file;

	private $templateurl;
	public $exists;

	private $templatepath;
	private $headers = array(
		'name'        => 'Template Name',
		'label'       => 'Name',
		'uri'         => 'Template URI',
		'description' => 'Description',
		'author'      => 'Author',
		'author_uri'  => 'Author URI',
		'version'     => 'Version',
	);

	private $custom_modules;

	/**
	 *
	 *
	 * @param unknown $slug (optional)
	 * @param unknown $file (optional)
	 */
	public function __construct( $slug = null, $file = 'index.html', $custom_modules = false ) {

		$this->file = basename( $file );

		$this->path = MAILSTER_UPLOAD_DIR . '/templates';
		$this->url  = MAILSTER_UPLOAD_URI . '/templates';

		$this->custom_modules = $custom_modules;

		if ( ! is_null( $slug ) ) {
			$this->load_template( $slug );
		}
	}


	/**
	 *
	 *
	 * @param unknown $modules      (optional)
	 * @param unknown $absolute_path (optional)
	 * @return unknown
	 */
	public function get( $modules = true, $absolute_path = false ) {
		if ( ! $modules ) {

			if ( ! $this->doc ) {
				return '';
			}

			$xpath           = new DOMXpath( $this->doc );
			$modulecontainer = $xpath->query( '//*/modules' );

			foreach ( $modulecontainer as $container ) {

				$activemodules = $this->get_modules( true );
				while ( $container->hasChildNodes() ) {
					$container->removeChild( $container->firstChild );
				}
				foreach ( $activemodules as $domElement ) {
					$domNode = $this->doc->importNode( $domElement, true );
					$container->appendChild( $domNode );
				}
			}

			$html = $this->doc->saveHTML();

		} else {

			$html = $this->raw;

		}
		if ( strpos( $html, 'data-editable' ) ) {

			$x    = $this->new_template_language( $html );
			$html = $x->saveHTML();

		}
		if ( $absolute_path ) {
			$html = $this->make_paths_absolute( $html );
		}

		$html = str_replace( array( '%7B', '%7D' ), array( '{', '}' ), $html );

		return $html;
	}

	public function use_notification() {
		$this->raw = $this->get_notification_template( $this->raw );
		$this->raw = $this->load_template_html( $this->raw );
	}


	/**
	 *
	 *
	 * @param unknown $html
	 * @return unknown
	 */
	private function make_paths_absolute( $html ) {

		preg_match_all( "/(src|background)=[\"'](.*)[\"']/Ui", $html, $images );
		preg_match_all( "/@import[ ]*['\"]{0,}(url\()*['\"]*([^;'\"\)]*)['\"\)]*/ui", $html, $assets );
		$images = array_unique( array_merge( $images[2], $assets[2] ) );
		foreach ( $images as $image ) {
			if ( empty( $image ) ) {
				continue;
			}
			if ( substr( $image, 0, 7 ) == 'http://' ) {
				continue;
			}

			if ( substr( $image, 0, 8 ) == 'https://' ) {
				continue;
			}

			$html = str_replace( $image, $this->url . '/' . $this->slug . '/' . $image, $html );
		}
		return $html;
	}


	/**
	 *
	 *
	 * @param unknown $html
	 * @return string
	 */
	public function load_template( $slug = '' ) {

		$slug = basename( $slug );

		$this->templatepath = $this->path . '/' . $slug;
		$this->templateurl  = $this->url . '/' . $slug;
		$this->slug         = $slug;

		// plain text
		if ( $this->file == '-1' ) {
			$this->raw = '{headline}<br>{content}';
			return $this->raw;
		}

		$file = $this->templatepath . '/' . $this->file;

		if ( ! class_exists( 'DOMDocument' ) ) {
			wp_die( "PHP Fatal error: Class 'DOMDocument' not found" );
		}
		if ( $this->exists = file_exists( $file ) ) {

			$raw           = file_get_contents( $file );
			$raw           = str_replace( '//dummy.newsletter-plugin.com/', '//dummy.mailster.co/', $raw );
			$template_data = $this->get_template_data( $file );
			if ( $template_data && $template_data['name'] ) {
				$this->data = $template_data;

			}
			// use custom custom modules
			if ( $this->custom_modules ) {
				$custom_modules = $this->get_custom_modules();
				$raw            = preg_replace( '#<modules>(.*)</modules>#s', '<modules>' . "\n" . $custom_modules . "\n" . '</modules>', $raw );
			}
		} else {

			// fallback to base template
			$base_html = file_get_contents( $this->templatepath . '/index.html' );
			$raw       = $this->get_notification_template( $base_html );

		}

		return $this->load_template_html( $raw );
	}


	/**
	 *
	 *
	 * @param unknown $html (optional)
	 * @return unknown
	 */
	public function load_template_html( $html ) {

		$doc                  = new DOMDocument();
		$doc->validateOnParse = true;
		$doc->formatOutput    = true;

		$i_error = libxml_use_internal_errors( true );
		$doc->loadHTML( $html );
		libxml_clear_errors();
		libxml_use_internal_errors( $i_error );

		$doc   = $this->new_template_language( $doc );
		$xpath = new DOMXPath( $doc );

		$modulecontainer = $xpath->query( '//*/modules' );
		if ( ! $modulecontainer->length ) {
			$modules = $xpath->query( '//*/module' );
			if ( $modules->length ) {
				$wrapper = $doc->createElement( 'modules' );
				$modules->item( 0 )->parentNode->insertBefore(
					$wrapper,
					$modules->item( 0 )
				);
				foreach ( $modules as $child ) {
					$wrapper->appendChild( $child );
				}
			}
		}

		$logo_id = mailster_option( 'logo' );
		if ( ! $logo_id ) {
			$logo_id = get_theme_mod( 'custom_logo' );
		}

		if ( $logo_id && $metadata = wp_get_attachment_metadata( $logo_id ) ) {
			$logos = $xpath->query( '//*/img[@label="Logo" or @label="logo" or @label="Your Logo" or @alt="Logo"]' );

			$high_dpi  = mailster_option( 'high_dpi' ) ? 2 : 1;
			$logo_link = mailster_option( 'logo_link' );

			$use_height = $metadata['height'] >= $metadata['width'];

			foreach ( $logos as $logo ) {

				$src    = $logo->getAttribute( 'src' );
				$width  = $logo->getAttribute( 'width' );
				$height = $logo->getAttribute( 'height' );

				if ( ! $src || ! $height || ! $width ) {
					continue;
				}

				if ( $use_height ) {
					$new_logo = mailster( 'helper' )->create_image( $logo_id, null, null, $height * $high_dpi, false );
				} else {
					$new_logo = mailster( 'helper' )->create_image( $logo_id, null, $width * $high_dpi, null, false );
				}

				if ( ! $new_logo ) {
					continue;
				}
				$logo->setAttribute( 'data-id', $new_logo['id'] );

				if ( $use_height ) {
					$logo->setAttribute( 'height', $height );
					if ( $new_logo['asp'] ) {
						$logo->setAttribute( 'width', round( $height * $new_logo['asp'] ) );
					}
				} else {
					$logo->setAttribute( 'width', $width );
					if ( $new_logo['asp'] ) {
						$logo->setAttribute( 'height', round( $width / $new_logo['asp'] ) );
					}
				}

				$logo->setAttribute( 'src', $new_logo['url'] );
				$alt = $logo->getAttribute( 'alt' );
				if ( empty( $alt ) ) {
					$logo->setAttribute( 'alt', __( 'Logo', 'mailster' ) );
				}

				if ( $logo_link ) {
					$link = $doc->createElement( 'a' );
					$link->setAttribute( 'href', $logo_link );
					$logo->parentNode->replaceChild( $link, $logo );
					$link->appendChild( $logo );
				}
			}
		}

		$services = mailster_option( 'services', array() );

		$social = $xpath->query( '//*/buttons[@social]' );
		if ( $social->length > 0 ) {
			$buttons = $social->item( 0 )->getElementsByTagName( 'a' );
		} else {
			// legacy
			$buttons = $xpath->query( '//*/a[@label="Social Media Button"]' );
		}

		if ( $buttons->length > 0 ) {

			$base_path = $this->templatepath . '/img/social/';
			$base_url  = $this->templateurl . '/img/social/';
			if ( ! is_dir( $base_path ) ) {
				$base_path = MAILSTER_UPLOAD_DIR . '/social/';
				$base_url  = MAILSTER_UPLOAD_URI . '/social/';
				if ( ! is_dir( $base_path ) ) {
					mailster( 'templates' )->copy_social_icons();
				}
			}

			$high_dpi = mailster_option( 'high_dpi' ) ? 2 : 1;

			$base_link  = $buttons->item( 0 )->cloneNode( true );
			$base_image = $base_link->firstChild;

			$parent = $buttons->item( 0 )->parentNode;

			while ( $parent->hasChildNodes() ) {
				$parent->removeChild( $parent->firstChild );
			}

			foreach ( $services as $service => $username ) {

				$url = mailster( 'helper' )->get_social_link( $service, $username );

				$icon = $base_path . 'dark/' . $service . '.png';
				if ( ! file_exists( $icon ) ) {
					$icon = $base_path . 'light/' . $service . '.png';
					if ( ! file_exists( $icon ) ) {
						$icon = $base_path . $service . '.png';
						if ( ! file_exists( $icon ) ) {
							continue;
						}
					}
				}

				$dimensions = getimagesize( $icon );
				if ( ! $dimensions ) {
					continue;
				}

				if ( ! ( $width = $base_image->getAttribute( 'width' ) ) ) {
					$width = round( $width / $high_dpi );
				}
				if ( ! ( $height = $base_image->getAttribute( 'height' ) ) ) {
					$height = round( $height / $high_dpi );
				}

				$link = $base_link->cloneNode( false );
				$img  = $base_image->cloneNode( false );

				$img->setAttribute( 'src', str_replace( $base_path, $base_url, $icon ) );
				// $img->setAttribute( 'width', $width );
				// $img->setAttribute( 'height', $height );
				// $img->setAttribute( 'style', "max-width:{$width}px;max-height:{$height}px;display:inline;" );
				// $img->setAttribute( 'class', 'social' );
				$img->setAttribute( 'alt', esc_attr( sprintf( __( 'Share this on %s', 'mailster' ), ucwords( $service ) ) ) );

				$link->setAttribute( 'href', $url );
				$link->setAttribute( 'editable', '' );
				$link->appendChild( $img );

				$parent->appendChild( $link );

			}
		}

		$html = $doc->saveHTML();
		if ( preg_match( '#<!--(.*?)-->#s', $html, $match ) ) {
			$header = $match[0];
			$html   = $header . "\n" . str_replace( $header, '', $html );
		}

		$this->doc = $doc;
		$this->raw = $html;

		return $html;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function reload( $custom_modules = false ) {
		$this->custom_modules = $custom_modules;

		if ( ! is_null( $this->slug ) ) {
			$this->load_template( $this->slug );
		}
	}


	/**
	 *
	 *
	 * @param unknown $slug (optional)
	 * @return unknown
	 */
	public function remove_template( $slug = '' ) {

		$this->templatepath = $this->path . '/' . $slug;

		if ( ! file_exists( $this->templatepath . '/index.html' ) ) {
			return false;
		}

		$wp_filesystem = mailster_require_filesystem();
		if ( $wp_filesystem && $wp_filesystem->delete( $this->templatepath, true ) ) {
			mailster( 'templates' )->remove_screenshot( $slug );
			return true;
		}

		return false;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function upload_template() {
		$result = wp_handle_upload(
			$_FILES['templatefile'],
			array(
				'mimes' => array( 'zip' => 'multipart/x-zip' ),
			)
		);
		if ( isset( $result['error'] ) ) {
			return $result;
		}

		$wp_filesystem = mailster_require_filesystem();

		$tempfolder = MAILSTER_UPLOAD_DIR . '/uploads';

		wp_mkdir_p( $tempfolder );

		return mailster( 'templates' )->unzip_template( $result['file'], $tempfolder );
	}


	/**
	 *
	 *
	 * @param unknown $name
	 * @param unknown $content   (optional)
	 * @param unknown $modules   (optional)
	 * @param unknown $active    (optional)
	 * @param unknown $overwrite (optional)
	 * @return unknown
	 */
	public function create_new( $name, $content = '', $modules = true, $active = false, $overwrite = false ) {

		if ( ! $this->slug ) {
			return false;
		}

		$filename = strtolower( sanitize_file_name( str_replace( '&amp;', '', $name ) ) . '.html' );

		if ( $name == esc_html__( 'Base', 'mailster' ) ) {
			$filename = 'index.html';
		}

		if ( $name == esc_html__( 'Notification', 'mailster' ) ) {
			$filename = 'notification.html';
		}

		if ( ! $overwrite && file_exists( $this->templatepath . '/' . $filename ) ) {
			$filename = str_replace( '.html', '-' . uniqid() . '.html', $filename );
		}

		if ( preg_match( '#<!--(.*?)-->#s', $content, $match ) ) {
			$header  = $match[0];
			$content = str_replace( $header, '', $content );
		}

		$pre = '<!--' . "\n\n";

		foreach ( $this->headers as $k => $v ) {
			if ( isset( $this->data[ $k ] ) ) {
				$pre .= "\t" . $this->headers[ $k ] . ': ' . ( $k == 'label' ? $name : $this->data[ $k ] ) . "\n";
			}
		}

		$pre .= "\n-->\n";

		if ( preg_match( '#<modules[^>]*>(.*)</modules>#s', $content, $hits ) ) {

			$original_modules_html = $modules ? $this->get_modules_html() : '';
			$custom_modules        = $hits[1];
			// remove all active
			$custom_modules = preg_replace( '#<module([^>]+)?( active="([^"]*)?")([^>]+)?>#', '<module$1$4>', $custom_modules );

			// add active
			if ( $active ) {
				$custom_modules = preg_replace( '#<module([^>]+)?>#', '<module$1 active>', $custom_modules );
			}

			$content = str_replace( $hits[0], '<modules>' . $custom_modules . $original_modules_html . '</modules>', $content );

		}

		$content = trim( $content );

		// remove absolute path to images from the template
		$content = str_replace( 'src="' . $this->url . '/' . $this->slug . '/', 'src="', $content );

		$content = str_replace( array( '%7B', '%7D' ), array( '{', '}' ), $content );

		$content = mailster()->sanitize_content( $content );

		$wp_filesystem = mailster_require_filesystem();

		if ( $wp_filesystem && $wp_filesystem->put_contents( $this->templatepath . '/' . $filename, $pre . $content, FS_CHMOD_FILE ) ) {
			mailster( 'templates' )->reset_query_cache();
			return $filename;
		}

		return false;
	}   /**
		 *
		 *
		 * @param unknown $name
		 * @param unknown $content (optional)
		 * @param unknown $auto    (optional)
		 * @return unknown
		 */
	public function add_module( $name, $content = '', $auto = false ) {

		// remove absolute path to images from the template
		$content = str_replace( 'src="' . $this->url . '/' . $this->slug . '/', 'src="', $content );

		$content = str_replace( array( '%7B', '%7D' ), array( '{', '}' ), $content );

		$content = '<module label="' . esc_attr( $name ) . '"' . ( $auto ? ' auto' : '' ) . '>' . "\n" . $content . "\n" . '</module>';

		// sanitize content
		$content = mailster()->sanitize_content( $content, null, false, true );

		// fixes potential HTML issues
		$content = $this->module_parser( $content );

		$hash = hash( 'crc32', md5( $content ) );
		if ( is_rtl() ) {
			$hash .= '-rtl';
		}
		$filename = strtolower( sanitize_file_name( $name ) ) . '-' . $hash . '.html';

		if ( mailster( 'helper' )->file_put_contents( $this->templatepath . '/modules/' . $filename, $content ) ) {
			mailster( 'templates' )->reset_query_cache();
			mailster( 'templates' )->schedule_screenshot( $this->slug, 'index.html', true, 0, true );
			return $filename;
		}

		return false;
	}


	/**
	 *
	 * Delete a custom module
	 *
	 * @param mixed $id
	 * @return bool
	 */
	public function delete_module( $id ) {

		// find module file
		$modules = glob( $this->templatepath . '/modules/*-' . $id . '.html' );

		if ( ! $modules ) {
			return false;
		}
		$screenshot_modules_folder = MAILSTER_UPLOAD_DIR . '/screenshots/';

		$wp_filesystem = mailster_require_filesystem();
		if ( ! $wp_filesystem ) {
			return false;
		}
		foreach ( $modules as $file ) {
			if ( $wp_filesystem->delete( $file, true ) ) {
				$screenshot = $this->get_module_image( $id );
				$wp_filesystem->delete( $screenshot_modules_folder . $screenshot, true );
			}
		}
		return true;
	}


	/**
	 *
	 *
	 * @param unknown $activeonly (optional)
	 * @return unknown
	 */
	private function module_parser( $html ) {

		$i_error              = libxml_use_internal_errors( true );
		$doc                  = new DOMDocument();
		$doc->validateOnParse = true;
		$doc->formatOutput    = true;

		$doc->loadHTML( $html );
		libxml_clear_errors();
		libxml_use_internal_errors( $i_error );

		$xpath = new DOMXpath( $doc );

		$module = $xpath->query( '//*/module' );

		$html = $this->get_html_from_nodes( $module );

		return $html;
	}


	/**
	 *
	 *
	 * @param unknown $activeonly (optional)
	 * @return unknown
	 */
	public function get_modules_list( $activeonly = false ) {

		$modules = $this->get_modules( $activeonly );

		$return = array();

		$screenshot_modules_folder     = MAILSTER_UPLOAD_DIR . '/screenshots/';
		$screenshot_modules_folder_uri = MAILSTER_UPLOAD_URI . '/screenshots/';

		$labels = array(
			'full size image'          => esc_html_x( 'Full Size Image', 'common module name', 'mailster' ),
			'intro'                    => esc_html_x( 'Intro', 'common module name', 'mailster' ),
			'separator'                => esc_html_x( 'Separator', 'common module name', 'mailster' ),
			'separator with button'    => esc_html_x( 'Separator with button', 'common module name', 'mailster' ),
			'full size text invert'    => esc_html_x( 'Full Size Text Invert', 'common module name', 'mailster' ),
			'iphone promotion'         => esc_html_x( 'iPhone Promotion', 'common module name', 'mailster' ),
			'macbook promotion'        => esc_html_x( 'Macbook Promotion', 'common module name', 'mailster' ),
			'quotation'                => esc_html_x( 'Quotation', 'common module name', 'mailster' ),
			'quotation left'           => esc_html_x( 'Quotation left', 'common module name', 'mailster' ),
			'quotation right'          => esc_html_x( 'Quotation right', 'common module name', 'mailster' ),
			'plans'                    => esc_html_x( 'Plans', 'common module name', 'mailster' ),
			'1/2 image full'           => sprintf( esc_html_x( '%s Image Full', 'common module name', 'mailster' ), '&#xBD;' ),
			'1/2 text invert'          => sprintf( esc_html_x( '%s Text Invert', 'common module name', 'mailster' ), '&#xBD;' ),
			'1/3 image full'           => sprintf( esc_html_x( '%s Image Full', 'common module name', 'mailster' ), '&#x2153;' ),
			'1/3 text invert'          => sprintf( esc_html_x( '%s Text Invert', 'common module name', 'mailster' ), '&#x2153;' ),
			'1/4 image full'           => sprintf( esc_html_x( '%s Image Full', 'common module name', 'mailster' ), '&#xBC;' ),
			'1/4 text invert'          => sprintf( esc_html_x( '%s Text Invert', 'common module name', 'mailster' ), '&#xBC;' ),
			'image on the left'        => esc_html_x( 'Image on the Left', 'common module name', 'mailster' ),
			'image on the right'       => esc_html_x( 'Image on the Right', 'common module name', 'mailster' ),
			'1/2 image on the left'    => sprintf( esc_html_x( '%s Image on the Left', 'common module name', 'mailster' ), '&#xBD;' ),
			'1/2 image on the right'   => sprintf( esc_html_x( '%s Image on the Right', 'common module name', 'mailster' ), '&#xBD;' ),
			'1/3 image on the left'    => sprintf( esc_html_x( '%s Image on the Left', 'common module name', 'mailster' ), '&#x2153;' ),
			'1/3 image on the right'   => sprintf( esc_html_x( '%s Image on the Right', 'common module name', 'mailster' ), '&#x2153;' ),
			'1/4 image on the left'    => sprintf( esc_html_x( '%s Image on the Left', 'common module name', 'mailster' ), '&#xBC;' ),
			'1/4 image on the right'   => sprintf( esc_html_x( '%s Image on the Right', 'common module name', 'mailster' ), '&#xBC;' ),
			'1/2 floating image left'  => sprintf( esc_html_x( '%s Floating Image left', 'common module name', 'mailster' ), '&#xBD;' ),
			'1/2 floating image right' => sprintf( esc_html_x( '%s Floating Image right', 'common module name', 'mailster' ), '&#xBD;' ),
			'1/2 image features left'  => sprintf( esc_html_x( '%s Image Features left', 'common module name', 'mailster' ), '&#xBD;' ),
			'1/2 image features right' => sprintf( esc_html_x( '%s Image Features right', 'common module name', 'mailster' ), '&#xBD;' ),
			'1/3 image features left'  => sprintf( esc_html_x( '%s Image Features left', 'common module name', 'mailster' ), '&#x2153;' ),
			'1/3 image features right' => sprintf( esc_html_x( '%s Image Features right', 'common module name', 'mailster' ), '&#x2153;' ),
			'1/1 text'                 => sprintf( esc_html_x( '%s Text', 'common module name', 'mailster' ), '1/1' ),
			'1/2 text'                 => sprintf( esc_html_x( '%s Text', 'common module name', 'mailster' ), '&#xBD;' ),
			'1/3 text'                 => sprintf( esc_html_x( '%s Text', 'common module name', 'mailster' ), '&#x2153;' ),
			'1/4 text'                 => sprintf( esc_html_x( '%s Text', 'common module name', 'mailster' ), '&#xBC;' ),
			'1/1 column'               => sprintf( esc_html_x( '%s Text', 'common module name', 'mailster' ), '1/1' ),
			'1/2 column'               => sprintf( esc_html_x( '%s Text', 'common module name', 'mailster' ), '&#xBD;' ),
			'1/3 column'               => sprintf( esc_html_x( '%s Text', 'common module name', 'mailster' ), '&#x2153;' ),
			'1/4 column'               => sprintf( esc_html_x( '%s Text', 'common module name', 'mailster' ), '&#xBC;' ),
		);

		$schedule_screenshots = false;

		foreach ( $modules as $i => $module ) {
			$html  = $this->get_html_from_node( $module );
			$id    = hash( 'crc32', md5( $html ) );
			$label = $modules->item( $i )->getAttribute( 'label' );

			if ( isset( $labels[ strtolower( $label ) ] ) ) {
				$label = $labels[ strtolower( $label ) ];
			} elseif ( empty( $label ) ) {
				$label = sprintf( esc_html__( 'Module %s', 'mailster' ), '#' . ( $i + 1 ) );
			}
			$image = $this->get_module_image( $id );
			if ( $image ) {
				$image_size = getimagesize( $screenshot_modules_folder . $image );
				$factor     = round( $image_size[0] / 150 );
			} else {
				$schedule_screenshots = true;
			}

			$html = $this->make_paths_absolute( $html );

			$module_html  = '<li data-id="' . $id . '" draggable="true">';
			$module_html .= '<script type="text/html">' . $html . '</script>';
			if ( $image ) {
				$module_html .= '<a class="mailster-btn addmodule has-screenshot" style="background-image:url(\'' . $screenshot_modules_folder_uri . $image . '\');height:' . ( ceil( $image_size[1] / $factor ) + 6 ) . 'px;" title="' . esc_attr( sprintf( esc_html__( 'Click to add %s', 'mailster' ), '"' . $label . '"' ) ) . '" data-id="' . $id . '" tabindex="0"><span>' . esc_html( $label ) . '</span><span class="hidden">' . esc_html( strtolower( $label ) ) . '</span></a>';
			} else {
				$module_html .= '<a class="mailster-btn addmodule" title="' . esc_attr( sprintf( esc_html__( 'Click to add %s', 'mailster' ), '"' . $label . '"' ) ) . '" data-id="' . $id . '" tabindex="0"><span>' . esc_html( $label ) . '</span><span class="hidden">' . esc_html( strtolower( $label ) ) . '</span></a>';
			}
			if ( $this->custom_modules ) {
				$module_html .= '<a class="deletemodule" title="' . esc_attr( sprintf( esc_html__( 'Delete Module %s', 'mailster' ), '"' . $label . '"' ) ) . '" data-id="' . esc_attr( $id ) . '" tabindex="-1">✕</a>';
			}
			$module_html .= '</li>';

			$return[] = array(
				'id'     => $id,
				'name'   => $label,
				'html'   => $html,
				'image'  => $image,
				'module' => $module_html,
			);
		}

		if ( $schedule_screenshots ) {
			mailster( 'templates' )->schedule_screenshot( $this->slug, $this->file, true, 0, $this->custom_modules );
		}

		return $return;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	private function get_custom_modules() {

		$modules = glob( $this->templatepath . '/modules/*.html' );

		$html = '';

		foreach ( $modules as $module ) {

			$module = basename( $module );

			$html .= file_get_contents( $this->templatepath . '/modules/' . $module ) . "\n";
		}

		return trim( $html );
	}

	private function get_notification_template( $html ) {

		// extract notification module
		if ( preg_match( '#<module[^>]*?type="notification"(.*?)".*?</module>#ms', $html, $modules ) ) {
			$module = $modules[0];
			$html   = preg_replace( '#<modules>(.*)</modules>#s', '<modules>' . "\n" . $module . "\n" . '</modules>', $html );

			// fallback to notification.html
		} elseif ( file_exists( $this->templatepath . '/notification.html' ) ) {
			$html = file_get_contents( $this->templatepath . '/notification.html' );

			// last resort
		} else {
			$html = $html;
			// $html = '{headline}<br>{content}';
		}

		return $html;
	}

	/**
	 *
	 *
	 * @param unknown $activeonly (optional)
	 * @param unknown $separator  (optional)
	 * @return unknown
	 */
	public function get_modules_html( $activeonly = false, $separator = "\n\n" ) {

		return $this->make_paths_absolute( $this->get_html_from_nodes( $this->get_modules( $activeonly ), $separator ) );
	}


	/**
	 *
	 *
	 * @param unknown $activeonly (optional)
	 * @return unknown
	 */
	public function get_modules( $activeonly = false ) {

		if ( ! $this->slug ) {
			return false;
		}

		$xpath = new DOMXpath( $this->doc );

		$modules = ( $activeonly )
			? $xpath->query( '//*/module[@active]' )
			: $xpath->query( '//*/module' );

		return $modules;
	}


	private function get_module_image( $id ) {

		$filedir = MAILSTER_UPLOAD_DIR . '/templates/' . $this->slug . '/' . $this->file;

		$hash = hash( 'crc32', md5_file( $filedir ) );
		if ( is_rtl() ) {
			$hash .= '-rtl';
		}
		$screenshot_modules_folder = MAILSTER_UPLOAD_DIR . '/screenshots/';

		$file = $this->slug . '/modules/' . $hash . '/' . $id . '.jpg';

		if ( file_exists( $screenshot_modules_folder . '/' . $file ) ) {
			return $file;
		}
		return false;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function get_styles() {
		if ( ! $this->raw ) {
			return '';
		}

		preg_match_all( '#<style[^>]*>(.*?)<\/style>#is', $this->raw, $matches );
		$style = '';
		if ( ! empty( $matches[1] ) ) {
			foreach ( $matches[1] as $styleblock ) {
				$style .= $styleblock;
			}
		}

		return $style;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function get_head() {
		if ( ! $this->raw ) {
			return '';
		}

		if ( $pos = strpos( $this->raw, '<body' ) ) {
			return $this->make_paths_absolute( trim( substr( $this->raw, 0, $pos ) ) );
		}
		return '';
	}


	/**
	 *
	 *
	 * @param unknown $html (optional)
	 * @return unknown
	 */
	public function get_background_links( $html = '' ) {
		if ( empty( $html ) ) {
			$html = $this->raw;
		}

		if ( ! $html ) {
			return array();
		}

		preg_match_all( "/background=[\"'](.*)[\"']/Ui", $html, $links );

		return array_filter( array_unique( $links[1] ) );
	}


	/**
	 *
	 *
	 * @param unknown $doc_or_html
	 * @return unknown
	 */
	public function new_template_language( $doc_or_html ) {

		if ( ! is_string( $doc_or_html ) ) {
			$doc = $doc_or_html;
		} else {
			$i_error              = libxml_use_internal_errors( true );
			$doc                  = new DOMDocument();
			$doc->validateOnParse = true;
			$doc->loadHTML( $doc_or_html );
			libxml_clear_errors();
			libxml_use_internal_errors( $i_error );

		}
		$xpath = new DOMXpath( $doc );

		// check if it's a new template
		$is_new_template = $doc->getElementsByTagName( 'single' );

		if ( $is_new_template->length ) {
			return $doc;
		}

		// Module container
		$modulecontainer = $xpath->query( "//*/div[@class='modulecontainer']" );

		foreach ( $modulecontainer as $container ) {

			$this->dom_rename_element( $container, 'modules', false );

		}

		// Modules
		$modules = $xpath->query( "//*/div[contains(concat(' ',normalize-space(@class),' '),' module ')]" );

		foreach ( $modules as $module ) {

			$label = $module->getAttribute( 'data-module' );
			$module->setAttribute( 'label', $label );
			$module->removeAttribute( 'data-module' );
			if ( $module->hasAttribute( 'data-auto' ) ) {
				$module->setAttribute( 'auto', null );
			}

			$this->dom_rename_element( $module, 'module' );

		}

		// images, editable
		$images = $xpath->query( '//*/img[@data-editable]' );

		foreach ( $images as $image ) {

			$label = $image->getAttribute( 'data-editable' );
			$image->setAttribute( 'editable', null );
			if ( $label ) {
				$image->setAttribute( 'label', $label );
			}

			$image->removeAttribute( 'data-editable' );

		}

		// other editable stuff
		$editables = $xpath->query( '//*[@data-editable]' );

		foreach ( $editables as $editable ) {

			$label = $editable->getAttribute( 'data-editable' );
			$editable->removeAttribute( 'data-editable' );
			if ( $label ) {
				$editable->setAttribute( 'label', $label );
			}

			if ( $editable->hasAttribute( 'data-multi' ) ) {

				$editable->removeAttribute( 'data-multi' );
				$this->dom_rename_element( $editable, 'multi' );
			} else {

				$this->dom_rename_element( $editable, 'single' );
			}
		}

		// wrap a diff around (for old templates)
		$editables = $doc->getElementsByTagName( 'single' );

		$div = $doc->createElement( 'div' );

		foreach ( $editables as $editable ) {

			$div_clone = $div->cloneNode();
			$editable->parentNode->replaceChild( $div_clone, $editable );
			$div_clone->appendChild( $editable );

		}
		$editables = $doc->getElementsByTagName( 'multi' );

		foreach ( $editables as $editable ) {

			$div_clone = $div->cloneNode();
			$editable->parentNode->replaceChild( $div_clone, $editable );
			$div_clone->appendChild( $editable );

		}

		// repeatable areas
		$repeatables = $xpath->query( '//*/*[@data-repeatable]' );

		foreach ( $repeatables as $repeatable ) {

			$label = $repeatable->getAttribute( 'data-repeatable' );
			$repeatable->setAttribute( 'repeatable', null );
			$repeatable->removeAttribute( 'data-repeatable' );

		}

		// buttons and buttongroups
		$buttons = $xpath->query( '//*/buttons' );

		if ( ! $buttons->length ) {

			$buttons = $xpath->query( "//*/div[@class='btn']" );

			foreach ( $buttons as $button ) {

				$button->removeAttribute( 'class' );
				$this->dom_rename_element( $button, 'buttons' );

			}

			$buttons = $doc->getElementsByTagName( 'buttons' );

			$new_div = $doc->createElement( 'div' );
			$new_div->setAttribute( 'class', 'btn' );

			foreach ( $buttons as $button ) {

				$div_clone = $new_div->cloneNode();
				$button->parentNode->replaceChild( $div_clone, $button );
				$div_clone->appendChild( $button );

				$children = $button->childNodes;
				foreach ( $children as $child ) {
					if ( strtolower( $child->nodeName ) == 'a' ) {
						$achildren = $child->childNodes;
						foreach ( $achildren as $achild ) {
							if ( strtolower( $achild->nodeName ) == 'img' ) {
								$label = $achild->getAttribute( 'label' );
								$achild->removeAttribute( 'editable' );
							}
						}

						$child->setAttribute( 'editable', null );
						$child->setAttribute( 'label', $label );
					}
				}
			}
		}

		$styles = $doc->getElementsByTagName( 'style' );

		foreach ( $styles as $style ) {

			$style->nodeValue = str_replace( 'img{outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;display:block;}', 'img{outline:none;text-decoration:none;-ms-interpolation-mode:bicubic;display:block;max-width:100%;}', $style->nodeValue );

		}

		return $doc;
	}


	/**
	 *
	 *
	 * @param unknown $slugsonly (optional)
	 * @return unknown
	 */
	public function get_templates( $slugsonly = false ) {

		$templates = array();
		$files     = list_files( $this->path );
		sort( $files );
		foreach ( $files as $file ) {
			if ( basename( $file ) == 'index.html' ) {

				$filename = str_replace( $this->path . '/', '', $file );
				$slug     = dirname( $filename );
				if ( ! $slugsonly ) {
					$templates[ $slug ] = $this->get_template_data( $file );
				} else {
					$templates[] = $slug;
				}
			}
		}
		return $templates;
	}


	/**
	 *
	 *
	 * @param unknown $slug (optional)
	 * @return unknown
	 */
	public function get_files( $slug = '' ) {

		if ( empty( $slug ) ) {
			$slug = $this->slug;
		}

		$templates = array();
		$files     = list_files( $this->path . '/' . $slug, 1 );

		sort( $files );

		$list = array(
			'index.html' => $this->get_template_data( $this->path . '/' . $slug . '/index.html' ),
		);

		if ( file_exists( $this->path . '/' . $slug . '/notification.html' ) ) {
			$list['notification.html'] = $this->get_template_data( $this->path . '/' . $slug . '/notification.html' );
		}

		foreach ( $files as $file ) {

			if ( strpos( $file, '.html' ) && is_file( $file ) ) {
				$list[ basename( $file ) ] = $this->get_template_data( $file );
			}
		}

		return $list;
	}


	/**
	 *
	 *
	 * @param unknown $slugsonly (optional)
	 * @return unknown
	 */
	public function get_versions( $slugsonly = false ) {

		$templates = $this->get_templates();
		$return    = array();
		foreach ( $templates as $slug => $data ) {

			$return[ $slug ] = $data['version'];
		}

		return $return;
	}


	/**
	 *
	 *
	 * @return unknown
	 */
	public function get_updates() {
		$updates = get_site_transient( 'mailster_updates' );
		if ( isset( $updates['templates'] ) ) {
			$updates = $updates['templates'];
		} else {
			$updates = array();
		}
		return $updates;
	}


	/**
	 *
	 *
	 * @param unknown $basefolder (optional)
	 */
	public function buttons( $basefolder = 'img' ) {

		if ( ! is_dir( $basefolder ) ) {
			$root = list_files( $this->path . '/' . $this->slug . '/' . $basefolder, 1 );
		} else {
			$root = list_files( MAILSTER_UPLOAD_DIR . '/social', 1 );
		}

		sort( $root );
		$folders = array();

		// common_button_folder_names in use for __($name, 'mailster')
		esc_html__( 'light', 'mailster' );
		esc_html__( 'dark', 'mailster' );

		foreach ( $root as $file ) {

			if ( ! is_dir( $file ) ) {
				continue;
			}

			$rootbtn = '';

			?>
		<div class="button-nav-wrap">
			<?php
			$nav   = $btn = '';
			$id    = basename( $file );
			$files = list_files( dirname( $file ) . '/' . $id, 1 );
			natsort( $files );
			foreach ( $files as $file ) {
				if ( is_dir( $file ) ) {
					$file      = str_replace( '//', '/', $file );
					$name      = basename( $file );
					$folders[] = $name;
					$nav      .= '<a class="nav-tab" href="#buttons-' . $id . '-' . $name . '">' . __( $name, 'mailster' ) . '</a>';
					$btn      .= $this->list_buttons( substr( $file, 0, -1 ), $id );
				} else {
					if ( $rootbtn ) {
						continue;
					}
					$rootbtn = $this->list_buttons( dirname( $file ), 'root' );

				}
			}
			if ( $nav ) :
				?>
				<div id="button-nav-<?php echo esc_attr( $id ); ?>" class="button-nav nav-tab-wrapper hide-if-no-js" data-folders="<?php echo implode( '-', $folders ); ?>"><?php echo $nav; ?></div>
				<?php
			endif;
			echo $btn;
			?>
		</div>
			<?php if ( $rootbtn ) : ?>
				<div class="button-nav-wrap button-nav-wrap-root"><?php echo $rootbtn; ?></div>
				<?php endif; ?>

			<?php
		}
	}


	/**
	 *
	 *
	 * @param unknown $folder
	 * @param unknown $id
	 * @return unknown
	 */
	public function list_buttons( $folder, $id ) {

		$files = list_files( $folder, 1 );
		natsort( $files );
		$btn = '<ul class="buttons buttons-' . basename( $folder ) . '" id="tab-buttons-' . $id . '-' . basename( $folder ) . '">';

		foreach ( $files as $file ) {

			if ( is_dir( $file ) ) {
				continue;
			}

			if ( ! in_array( strrchr( $file, '.' ), array( '.png', '.gif', '.jpg', '.jpeg' ) ) ) {
				continue;
			}

			$filename = basename( $file );
			$service  = substr( $filename, 0, strrpos( $filename, '.' ) );
			$btn     .= '<li><a class="btnsrc" title="' . ucwords( esc_attr( $service ) ) . '" data-link="' . mailster( 'helper' )->get_social_link( $service, 'USERNAME' ) . '"><img src="' . str_replace( array( MAILSTER_DIR, MAILSTER_UPLOAD_DIR, $this->path ), array( MAILSTER_URI, MAILSTER_UPLOAD_URI, $this->url ), $file ) . '" loading="lazy" width="32" height ="32"></a></li>';

		}

		$btn .= '</ul>';

		return $btn;
	}


	/**
	 *
	 *
	 * @param unknown $file (optional)
	 * @return unknown
	 */
	public function get_raw_template( $file = 'index.html' ) {
		if ( ! file_exists( $this->path . '/' . $this->slug . '/' . $file ) ) {
			return false;
		}

		return file_get_contents( $this->path . '/' . $this->slug . '/' . $file );
	}


	/**
	 *
	 *
	 * @param unknown $slug (optional)
	 * @param unknown $file (optional)
	 * @return unknown
	 */
	public function get_module_screenshots( $slug = null, $file = null ) {

		if ( ! mailster_option( 'module_thumbnails' ) ) {
			return false;
		}

		$modules = $this->get_modules();

		if ( ! $modules->length ) {
			return;
		}

		if ( is_null( $slug ) ) {
			$slug = $this->slug;
		}

		if ( is_null( $file ) ) {
			$file = $this->file;
		}

		$filedir = MAILSTER_UPLOAD_DIR . '/templates/' . $slug . '/' . $file;
		$fileuri = MAILSTER_UPLOAD_URI . '/templates/' . $slug . '/' . $file;

		$hash = hash( 'crc32', md5_file( $filedir ) );
		if ( is_rtl() ) {
			$hash .= '-rtl';
		}
		$screenshot_modules_folder     = MAILSTER_UPLOAD_DIR . '/screenshots/' . $slug . '/modules/' . $hash;
		$screenshot_modules_folder_uri = MAILSTER_UPLOAD_URI . '/screenshots/' . $slug . '/modules/' . $hash;

		if ( ! is_dir( $screenshot_modules_folder ) ) {

			mailster( 'templates' )->schedule_screenshot( $slug, $file, true, 0, false );

			return array();

		}

		$return = array();

		// add custom modules first
		if ( is_dir( $screenshot_modules_folder . '/custom' ) ) {
			$files = glob( $screenshot_modules_folder . '/custom/*.jpg' );
			natsort( $files );
			$files = array_values( $files );

			foreach ( $files as $screenshotfile ) {
				$return[ 'c' . basename( $screenshotfile ) ] = $hash . '/custom/' . basename( $screenshotfile );
			}
		}

		$files = glob( $screenshot_modules_folder . '/*.jpg' );
		natsort( $files );
		$files = array_values( $files );

		foreach ( $files as $screenshotfile ) {
			$return[ basename( $screenshotfile ) ] = $hash . '/' . basename( $screenshotfile );
		}

		// reschedule if not all modules are there
		if ( count( $return ) < $modules->length ) {
			mailster( 'templates' )->schedule_screenshot( $slug, $file, true, 10, false );
		}

		return $return;
	}


	/**
	 *
	 *
	 * @param unknown $nodes
	 * @param unknown $separator (optional)
	 * @return unknown
	 */
	private function get_html_from_nodes( $nodes, $separator = '' ) {

		$parts = array();

		if ( ! $nodes ) {
			return '';
		}

		foreach ( $nodes as $node ) {
			$parts[] = $this->get_html_from_node( $node );
		}

		return implode( $separator, $parts );
	}


	/**
	 *
	 *
	 * @param unknown $node
	 * @return unknown
	 */
	private function get_html_from_node( $node ) {

		$html = $node->ownerDocument->saveHTML( $node );

		// remove CDATA elements (keep content)
		$html = preg_replace( '~<!\[CDATA\[\s*|\s*\]\]>~', '', $html );
		return trim( $html );
	}


	/**
	 *
	 *
	 * @param object  $node
	 * @param unknown $name
	 * @param unknown $attributes (optional)
	 * @return unknown
	 */
	private function dom_rename_element( DOMElement $node, $name, $attributes = true ) {

		$renamed = $node->ownerDocument->createElement( $name );

		if ( $attributes ) {
			foreach ( $node->attributes as $attribute ) {
				$renamed->setAttribute( $attribute->nodeName, $attribute->nodeValue );
			}
		}
		while ( $node->firstChild ) {
			$renamed->appendChild( $node->firstChild );
		}

		return $node->parentNode->replaceChild( $renamed, $node );
	}

	public function get_colors( $html = array() ) {

		$html   = $this->raw ? $this->raw : $this->get( true );
		$colors = array();

		// get all style blocks, search for variables
		preg_match_all( '#<style(.*?)>(.*?)</style>#is', $html, $style_blocks );
		foreach ( $style_blocks[2] as $style_block ) {
			// get all variables
			preg_match_all( '/--mailster-([a-zA-z0-9-]+):([^;}]+)/', $style_block, $variables );
			foreach ( $variables[1] as $i => $variable ) {
				$colors[] = array(
					'id'    => $variable,
					'var'   => '--mailster-' . $variable,
					'value' => trim( $variables[2][ $i ] ),
					'label' => $this->color_label( $variable ),
				);
			}
		}

		// no colors => fallback to the legacy method < 4.0
		if ( empty( $colors ) ) {
			preg_match_all( '/#[a-fA-F0-9]{6}/', $html, $hits );
			$original_colors = array_keys( array_count_values( $hits[0] ) );

			foreach ( $original_colors as $i => $color ) {
				preg_match( '/' . $color . '\/\*([^*]+)\*\//i', $html, $match );
				$id       = strtoupper( $color );
				$colors[] = array(
					'id'    => $id,
					'value' => $id,
					'label' => isset( $match[1] ) ? $match[1] : $id,
				);
			}
		}

		return $colors;
	}

	private function color_label( $color ) {
		$label = ucwords( str_replace( '-', ' ', $color ) );
		return $label;
	}


	/**
	 *
	 *
	 * @param unknown $file
	 * @return unknown
	 */
	private function get_template_data( $file ) {

		return mailster( 'templates' )->get_template_data( $file );
	}
}

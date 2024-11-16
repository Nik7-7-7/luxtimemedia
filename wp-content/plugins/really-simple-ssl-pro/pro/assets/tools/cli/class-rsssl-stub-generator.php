<?php
/**
 * This is a class generator for events.
 *
 * @package     RSSSL\Pro\Security\Wordpress\Eventlog\Events
 */

namespace WP_CLI\Really_Simple_SSL;

use WP_CLI;
use WP_CLI\ExitException;
use WP_CLI_Command;
if ( ! class_exists( 'WP_CLI' ) ) {
	return;
}

try {
	WP_CLI::add_command( 'event generate', __NAMESPACE__ . '\Rsssl_Stub_Generator' );
} catch ( \Exception $e ) {
	WP_CLI::warning( $e->getMessage() );
}


/**
 * Class Rsssl_Stub_Generator
 *
 * Generates an Event class stub.
 *
 * @param string $args The arguments passed to the command.
 * @param string $assoc_args The associative arguments passed to the command.
 *
 * @package     RSSSL\Pro\Security\Wordpress\Eventlog\Events
 */
class Rsssl_Stub_Generator extends WP_CLI_Command {
	/**
	 * Generates an Event class stub.
	 *
	 * @param array $args The arguments passed to the command.
	 * @param array $assoc_args The associative arguments passed to the command.
	 *
	 * [<name>]
	 * : The name of the Event class to generate. If omitted, you'll be prompted to enter it.
	 *
	 * [--path=<path>]
	 * : Specify a path to generate the Event class in.
	 *
	 * ## EXAMPLES
	 *
	 *     wp event generate --path=wp-content/plugins/my-plugin/includes
	 *
	 * @throws ExitException If the class name is not provided.
	 */
	public function __invoke( $args, $assoc_args ) {
		// Check if the class name is provided, if not prompt the user.
		if ( empty( $args ) ) {
			WP_CLI::line( 'Please enter the name of the Event class:' );
			$class_name      = trim( fgets( STDIN ) );  // Reading input from STDIN.
			$class_name      = 'Rsssl_' . $this->class_name( $class_name );
			$file_name       = strtolower( $class_name );
			$class_file_name = 'class-' . preg_replace( '{/([^/]+)$}', '/class-$1.php', $file_name ) . '.php';
			// We replace all underscores with hyphens.
			$class_file_name = str_replace( '_', '-', $class_file_name );

			if ( empty( $class_name ) ) {
				WP_CLI::error( 'You must provide a valid class name.' );
				return;
			}
			WP_CLI::line( 'Please add a code for the event:' );
			$event_code = trim( fgets( STDIN ) );  // Reading input from STDIN.
			$event_code = (int) $event_code;
		} else {
			$class_name      = 'Rsssl_' . $this->class_name( $args[0] );
			$file_name       = strtolower( $class_name );
			$class_file_name = 'class-' . preg_replace( '{/([^/]+)$}', '/class-$1.php', $file_name ) . '.php';
			$class_file_name = str_replace( '_', '-', $class_file_name );
			$event_code      = $args[1];
		}

		$path = $assoc_args['path'] ?? rsssl_path . 'pro/security/wordpress/eventlog/events/' . $class_file_name;

		$file_content = <<<PHP
<?php
/**
* The '{$class_name}' class is a part of the 'Really Simple Security pro' plugin,
 * which is developed by the company 'Really Simple Plugins'.
 * This class is responsible for handling the {$class_name} event
 * 
 * @package     RSSSL\Pro\Security\Wordpress\Eventlog\Events  // The categorization of this class.
 */
namespace RSSSL\Pro\Security\Wordpress\EventLog\Events;

use RSSSL\Pro\Security\Wordpress\EventLog\Rsssl_Event_Log_Handler;

/**
* The '{$class_name}' class is a part of the 'Really Simple Security pro' plugin,
 * which is developed by the company 'Really Simple Plugins'.
 * This class is responsible for handling the {$class_name} event
 * with the event code of {$event_code}.
 * 
 * @package     RSSSL\Pro\Security\Wordpress\Eventlog\Events  // The categorization of this class.
 */
class $class_name extends Rsssl_Event_Log_Handler {
  /**
	 * Class constructor.
	 *
	 * Initializes the object with a value of 1000.
	 */
	public function __construct() {
		parent::__construct( $event_code );
	}
	
 /**
 * Handle an event.
 *
 * This method creates a new instance of the current class (\$_self) and gets the event associated with the event code.
 * It then logs the event with the provided data.
 *
 * @param array \$data The data related to the event (default: empty array).
 *
 * @return void
 */
 public static function handle_event(array \$data = array()): void {
		\$_self = new self();
		\$event = \$_self->get_event( \$_self->event_code );
		
		// we log the event with the data.
		\$_self->log_event( \$event, \$event_data );
	}
	
		/**
	 * Sanitizes an array of data.
	 *
	 * @param array \$data The data to sanitize.
	 *
	 * @return array The sanitized data.
	 */
	protected function sanitize( array \$data ): array {
		//based on the value if the data is a string we sanitize it.
		foreach ( \$data as \$key => \$value ) {
			if ( is_string( \$value ) ) {
				\$data[ \$key ] = sanitize_text_field( \$value );
			}
			if (isset( \$data['ip_address'] )) {
				\$data['ip_address'] = filter_var( \$data['ip_address'], FILTER_VALIDATE_IP );
			}
		}
		// Now here you can add more sanitization for the data for custom values.

		// Return the sanitized data.
		return \$data;
	}
	
		/**
	 * Sets a translated message using sprintf function.
	 *
	 * @param array \$args An array of arguments used in the message.
	 * @param string \$message The message to be translated and formatted.  
	 *
	 * @return string The formatted and translated message.
	 */
	protected function set_message( array \$args, string \$message ): string {
		return sprintf( __( \$message, 'really-simple-ssl' ), \$args['user_login'] );
	}
}

PHP;
		if ( file_exists( $path ) ) {
			WP_CLI::error( "File already exists at {$class_file_name}" );
		} else {
			global $wp_filesystem;
			if ( ! $wp_filesystem ) {
				if ( ! defined( 'FS_METHOD' ) ) {
					define( 'FS_METHOD', 'direct' );
				}
				require_once ABSPATH . '/wp-admin/includes/file.php';
				WP_Filesystem();
			}
			$wp_filesystem->put_contents( $path, $file_content );
			WP_CLI::success( "Generated {$class_name} class in {$class_file_name}" );
		}
	}

	/**
	 * Converts a given input string to snake_case.
	 *
	 * @param string $input The input string.
	 *
	 * @return string The converted snake_case string.
	 */
	private function class_name( string $input ): string {
		// now we make it a snake_case string. like Snake_Case.
		$class = strtolower( preg_replace( '/(?<!^)[A-Z]/', '_$0', $input ) );

		return str_replace( ' ', '_', ucwords( str_replace( '_', ' ', $class ) ) );
	}
}

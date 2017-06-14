<?php

defined( 'ABSPATH' ) or die( 'Unauthorized access' ) ;

class Sirmini_Hook_Hack_Install
{
	public static function install(){
		if ( !defined( 'SIRMINI_HOOK_HACK_INSTALLED' ) ) {
			$logger_path = dirname( __FILE__ ) . '/class-sirmini-hook-hack-logger.php';
			$plugin_php_file = ABSPATH . WPINC . '/plugin.php';
			copy( $plugin_php_file, ABSPATH . WPINC . '/sirmini-backup-plugin.php' );
			$content = file_get_contents( $plugin_php_file );
			$content = str_replace( 
					'// Initialize the filter globals.', 
					"define( 'SIRMINI_HOOK_HACK_INSTALLED', true ); include_once( '" . $logger_path . "' );\n" .
					"// Initialize the filter globals.", 
					$content );
			$content = str_replace( 
					'function apply_filters( $tag, $value ) {', 
					'function apply_filters( $tag, $value ) {' . "\n" .
					'Sirmini_Hook_Hack_Logger::log_filter( $tag );',
					$content );
			$content = str_replace(
					'function apply_filters_ref_array($tag, $args) {',
					'function apply_filters_ref_array($tag, $args) {' . "\n" .
					'Sirmini_Hook_Hack_Logger::log_filter( $tag );',
					$content );
			$content = str_replace(
					'function do_action($tag, $arg = \'\') {',
					'function do_action($tag, $arg = \'\') {' . "\n" .
					'Sirmini_Hook_Hack_Logger::log_action( $tag );',
					$content );
			$content = str_replace(
					'function do_action_ref_array($tag, $args) {',
					'function do_action_ref_array($tag, $args) {' . "\n" .
					'Sirmini_Hook_Hack_Logger::log_action( $tag );',
					$content );
			file_put_contents( $plugin_php_file, $content );		
			define( 'SIRMINI_HOOK_HACK_INSTALLED', true );	
		}
		if ( !file_exists( ABSPATH . 'hooks' ) ){
			mkdir( ABSPATH . 'hooks' );
		}

	}
	
	public static function deactivate() {
		
		define( 'SIRMINI_HOOK_HACK_UNINSTALL', true );
		if ( file_exists( ABSPATH . WPINC . '/sirmini-backup-plugin.php' ) ) {
			$content = file_get_contents( ABSPATH . WPINC . '/sirmini-backup-plugin.php' );
			file_put_contents( ABSPATH . WPINC . '/plugin.php', $content );
			unlink( ABSPATH . WPINC . '/sirmini-backup-plugin.php' );
		}
		
		if ( file_exists( ABSPATH . 'hooks' ) && is_dir( ABSPATH . 'hooks' ) ) {
			$file_list = scandir( ABSPATH . 'hooks' );
			foreach( $file_list as $single_file ){
				if( ! in_array( $single_file, array( '.', '..' ) ) ) unlink( ABSPATH . 'hooks/' . $single_file ) ;
			}
			rmdir( ABSPATH . 'hooks' );
		}
		$logger = Sirmini_Hook_Hack_Logger::instance();
		$logger->plugin_active = false;
	}
}
<?php

defined( 'ABSPATH' ) or die( 'Unauthorized access' ) ;

class Sirmini_Hook_Hack_Logger
{
	protected static $_instance = null;
	
	public static function instance(){
		if ( is_null( self::$_instance ) ) self::$_instance = new self();
		return self::$_instance;
	}
	
	public function __construct(){
		if ( is_null( self::$_instance ) ) self::$_instance = $this; 
		$this->dir = ABSPATH . $this->dir;
		
		if ( file_exists( $this->dir ) && is_dir( $this->dir ) ) {
			$this->initialization();
		} else {
			$this->plugin_active = false;
		}
		
	}
	
	
	/// FROM the previous project 
	
	protected $start_time = 0;
	protected $start_microtime = 0;
	protected $dir = 'hooks/';
	protected $action_file;
	protected $filter_file;
	protected $mixed_file;
	protected $filter_index = 0;
	protected $action_index = 0;
	protected $mixed_index = 0;
	protected $occurence = array();
	protected $filename = '';
	public $plugin_active = true;
	
	
	protected function initialization(){
		$this->clean_log_dir( 90 );
		if( isset( $_GET[ 'clearhooks' ] ) ){
			$file_list = scandir( $this->dir );
			foreach( $file_list as $single_file ){
				if( ! in_array( $single_file, array( '.', '..' ) ) ) unlink( $this->dir . $single_file ) ;
			}
		}
		$this->filename = str_replace( array( '/', '.php' ) , array( '.', ''), $_SERVER[ 'SCRIPT_NAME' ] );
		$this->start_time = time();
		$this->start_microtime = microtime( true );
		$k=0;
		while( file_exists( $this->dir . date( 'Y_m_d_H-i-s_', $this->start_time) . $k . '_actions_' . $this->filename . '.txt') ) $k++;
		$this->action_file = $this->dir . date( 'Y_m_d_H-i-s_', $this->start_time) . $k . '_actions_' . $this->filename . '.txt';
		$this->filter_file = $this->dir . date( 'Y_m_d_H-i-s_', $this->start_time) . $k . '_filters_' . $this->filename . '.txt';
		$this->mixed_file = $this->dir . date( 'Y_m_d_H-i-s_', $this->start_time) . $k . '_mixed_' . $this->filename . '.txt';
		$req = 'request : '.$_SERVER['REQUEST_URI'].' -- time : '.date(' d m Y H:i:s ', $this->start_time)."\n\n";
		ob_start();
		var_dump( $_POST );
		$post = ob_get_clean();
		$post .= "\n\n";
		file_put_contents( $this->action_file , 'Actions for : ' . $req . $post );
		file_put_contents( $this->filter_file , 'Filters for : ' . $req . $post );
		file_put_contents( $this->mixed_file , 'Filters and Actions for : ' . $req . $post );				
	
	}
	
	public static function log_action( $hook ){
		$I = self::instance();
		if ( $I->plugin_active ) {
			$I->log_hook( 'action', $hook );
		}

	}
	
	public static function log_filter( $hook ){
		$I = self::instance();
		if ( $I->plugin_active ) {
			$I->log_hook( 'filter', $hook );			
		}
	}
	
	public function log_hook( $type, $hook ){
		global $wp_filter;
		if( $type == 'filter'){
			$file = $this->filter_file;
			$this->filter_index++;
			$index = $this->filter_index;
		}elseif( $type == 'action'){
			$file = $this->action_file;
			$this->action_index++;
			$index = $this->action_index;
		}else return false;
		$mixed_file = $this->mixed_file;
		$this->mixed_index++;
		$mixed_index = $this->mixed_index;
		$delta_time = (int) ( ( microtime( true ) - $this->start_microtime ) * 1000 );
		$functions = array();
		if( isset($wp_filter[ $hook ] ) ){
			foreach( $wp_filter[ $hook ] as $priority){
				foreach( $priority as $callback)
					if ( !is_null( $callback['function']) ){
					if( is_array( $callback['function'])) {
						$convert = array();
						foreach( $callback['function'] as $item ){
							if( is_string( $item ) ) $convert[] = $item;
							elseif( is_object( $item ) ) $convert[] = get_class( $item );
						}
						$functions[] = implode( ' -> ', $convert );
					}elseif( is_string( $callback[ 'function' ] ) ){
						$functions[] = $callback[ 'function' ];
					}else{
						$functions[] = "Autre";
					}
				}
			}
		}
		if ( !isset( $this->occurence[ $hook ] ) )	$this->occurence[ $hook ] = 0;
		else	$this->occurence[ $hook ]++;
		$str = "\n" . $hook . " ( $type ) \n";
		$str .= "time=" . $delta_time . "ms - index=" . $index . " - mixed-index=" . $mixed_index . " - occurence=" . $this->occurence[ $hook ] . "\n";
		foreach( $functions as $func ){
			$str .= "\t $func \n";
		}
		file_put_contents( $file, $str ,FILE_APPEND);
		file_put_contents( $mixed_file, $str ,FILE_APPEND);
	}
	
	
	protected function clean_log_dir( $max_files = 100) {
		if ( file_exists( $this->dir ) && is_dir( $this->dir ) ) {
		$scan = scandir( $this->dir );
		if ( count( $scan ) > $max_files ) {
			foreach( $scan as $file ){
				if( ! in_array( $file, array( '.', '..' ) ) ) unlink( $this->dir .$file ) ;
			}
		}
		unset( $scan );			
		}

	}
	
}
<?php
/*
 * Plugin Name: Sirmini Hook Hack
 * Plugin URI: http://www.sirmons.fr/sirmini-plugins/sirmini-hook-hack
 * Author: Nathanael SIRMONS
 * Author URI: http://www.sirmons.fr
 * Version: 0.1.0
 * Description: Hacks the wp-includes/plugin.php file to logs into a file all the hooks as they are executed.
 * License: GPLv2 or later
 */

/*
 This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

defined( 'ABSPATH' ) or die( 'Unauthorized access' ) ;

require_once( dirname( __FILE__ ) . '/includes/class-sirmini-hook-hack-logger.php' ) ;
require_once( dirname( __FILE__ ) . '/includes/class-sirmini-hook-hack-install.php' ) ;

/* The main class of this plugin
 * @since 0.1.0
 */
class Sirmini_Hook_Hack
{
	
	public static function loader(){

		register_activation_hook( __FILE__, array( __CLASS__, 'activation_hook' ) );
		register_deactivation_hook( __FILE__, array( __CLASS__, 'deactivation_hook' ) ) ;
	} 
	
	public static function deactivation_hook(){
		Sirmini_Hook_Hack_Install::deactivate();
	}
	public static function activation_hook() {
		Sirmini_Hook_Hack_Install::install();
	}
	
}

Sirmini_Hook_Hack::loader();
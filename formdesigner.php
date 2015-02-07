<?php
/*
Plugin Name: FormDesigner
Plugin URI: http://wordpress.org/plugins/formdesigner
Description: Многофункциональный конструктор веб-форм в режиме реального времени. Не требуется специальных знаний и навыков.
Version: 1.0.0
Author: FormDesigner.ru
Author URI: http://formdesigner.ru

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo 'Hi there!  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define( 'FORMDESIGNER__PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'FORMDESIGNER__PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

define( 'FD_HASH', '31d3b5b80ec0f3f97e773f257ef771e0' );
define( 'FD_CRYPT_KEY', '82v5f6h5xkom6k54d870rts5w3run8cww2yog2fdhgduf7w2yti' );

require_once( FORMDESIGNER__PLUGIN_DIR . 'class.formdesigner.php' );
$FormDesigner = new FormDesigner( FD_HASH, FD_CRYPT_KEY );
?>
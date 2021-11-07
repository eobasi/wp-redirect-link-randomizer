<?php

/**
 * This software is intended for use with Wordpress Software http://www.wordpress.org/ and is a proprietary licensed product.
 * For more information see License.txt in the plugin folder.

 * ---
 * Copyright (c) 2021, Ebenezer Obasi
 * All rights reserved.
 * eobasilive@gmail.com.

 * Redistribution and use in source and binary forms, with or without modification, are not permitted provided.

 * This plugin should be bought from the developer. For details contact info@eobasi.com.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @since             1.0.0
 * @package           redirect_links_randomizer
 *
 * @wordpress-plugin
 * Plugin Name:       Redirect Links Randomizer
 * Plugin URI:        http://eobasi.com/wp-redirect-links-randomizer
 * Description:       Redirect Links Randomizer is designed to randomly distribute traffics to a set of urls in a group and store hit count for each url.
 * Version:           1.0.0
 * Author:            Ebenezer Obasi
 * Author URI:        http://eobasi.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       redirect-link-randomizer
 */

define('RANDRED_DOMAIN', 'randred');
define('RANDRED_PREFIX', 'randred_');
define('RANDRED_OPTIONS_PAGE', RANDRED_PREFIX . 'options');
define('RANDRED_VERSION', '1.0.0');
define('RANDRED_DS', DIRECTORY_SEPARATOR);

define('RANDRED_ROOT', dirname(__FILE__) . RANDRED_DS );
define('RANDRED_DIR_CORE', RANDRED_ROOT . 'core' . RANDRED_DS );
define('RANDRED_DIR_CTRL', RANDRED_ROOT . 'controllers' . RANDRED_DS );

require_once RANDRED_DIR_CORE. 'class.application.php';
require_once RANDRED_DIR_CORE . 'html_tag.php';
require_once RANDRED_DIR_CORE . 'form_elements.php';
require_once RANDRED_DIR_CORE . 'class.view.php';


$__randred__ = new RANDRED();

if ( is_admin() )
{
	require_once RANDRED_DIR_CORE . 'class.admin.php';
	require_once RANDRED_DIR_CTRL . 'admin.php';

	$__randred__->addRoute(RANDRED_Admin::SLUG_GROUPS, 'RANDRED_CTRL_Admin', 'groups');
	$__randred__->addRoute(RANDRED_Admin::SLUG_LINKS, 'RANDRED_CTRL_Admin', 'links');
	$__randred__->addRoute(RANDRED_Admin::SLUG_ADD_GROUP, 'RANDRED_CTRL_Admin', 'addGroup');
	$__randred__->addRoute(RANDRED_Admin::SLUG_ADD_LINK, 'RANDRED_CTRL_Admin', 'addLink');
	$__randred__->addRoute(RANDRED_OPTIONS_PAGE, 'RANDRED_CTRL_Admin', 'settings');

    $randred__admin = new RANDRED_Admin();
     
    // Create admin menu items.
    add_action( 'admin_menu', [ $randred__admin, 'addMenuItem' ] );
    add_filter( 'submenu_file', [ $randred__admin, 'submenuFilter' ] );
    // Register pdfreader_settings_init to the admin_init action hook.
    add_action( 'admin_init', [ $randred__admin, 'init' ] );
    add_action( 'wp_loaded', [ $randred__admin, 'processForm' ] );
} else {
	require_once RANDRED_DIR_CTRL . 'randomizer.php';
    // non-admin enqueues, actions, and filters
    add_filter( 'init', array( $__randred__, 'flush_rules' ) );
    add_action( 'wp', array( $__randred__, 'start' ) );
}

register_activation_hook( __FILE__, [$__randred__, 'install'] );
add_action( 'plugins_loaded', [$__randred__, 'update'] );
// One time activation functions
register_activation_hook( RANDRED_ROOT, array( $__randred__, 'flush_rules' ) );
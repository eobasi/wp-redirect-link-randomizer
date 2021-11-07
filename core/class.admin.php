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
 * @author Ebenezer Obasi <eobasilive@gmail.com>
 * @since 1.0.0
 * @package redirect_links_randomizer/core
 */

class RANDRED_Admin
{
    const SLUG_GROUPS = RANDRED_PREFIX . 'groups';
    const SLUG_LINKS = RANDRED_PREFIX . 'links';

    const SLUG_ADD_GROUP = RANDRED_PREFIX . 'add_group';
    const SLUG_ADD_LINK = RANDRED_PREFIX . 'add_link';

    private static $classInstance;

    public static function getInstance()
    {
        if( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @var RANDRED
     */
    public static $application;

    public function __construct()
    {
        global $__randred__;

        self::$application = $__randred__;
    }

    public function deleteLink()
    {
        if( empty($id = self::$application->getKeyValue('linkid')) )
        {
            wp_redirect(add_query_arg( [
                'page' => RANDRED_Admin::SLUG_ADD_LINK,
                'save_success' => 'error',
                'linkid' => $id,
            ], menu_page_url(RANDRED_Admin::SLUG_ADD_LINK, false)));
            
            return;
        }

        self::$application->deleteLink($id);

        wp_redirect(add_query_arg( [
            'page' => RANDRED_Admin::SLUG_LINKS,
            'save_success' => 'success',
        ], menu_page_url(RANDRED_Admin::SLUG_LINKS, false)));
    }

    public function deleteGroup()
    {
        if( empty($id = self::$application->getKeyValue('groupid')) )
        {
            wp_redirect(add_query_arg( [
                'page' => RANDRED_Admin::SLUG_ADD_GROUP,
                'save_success' => 'error',
                'groupid' => $id,
            ], menu_page_url(RANDRED_Admin::SLUG_ADD_GROUP, false)));
            
            return;
        }

        self::$application->deleteGroup($id);

        wp_redirect(add_query_arg( [
            'page' => RANDRED_Admin::SLUG_GROUPS,
            'save_success' => 'success',
        ], menu_page_url(RANDRED_Admin::SLUG_GROUPS, false)));
    }

    public function saveLink()
    {
        if( ($url = self::$application->postKeyValue('url'))
        && ($groupid = self::$application->postKeyValue('groupid')) )
        {
            $data = [
                'url' => $url,
                'groupid' => $groupid,
                'hitlimit' => self::$application->postKeyValue('hitlimit'),
                'createstamp' => time(),
            ];

            if( !empty( $linkId = self::$application->getKeyValue('linkid')) )
            {
                $data['id'] = $linkId;
            }

            $newId = self::$application->saveLink($data);

            wp_redirect(add_query_arg( [
                'page' => RANDRED_Admin::SLUG_ADD_LINK,
                'save_success' => 'success',
                'linkid' => $newId ,
            ], menu_page_url(RANDRED_Admin::SLUG_ADD_LINK, false)));
        }
        else
        {
            wp_redirect(add_query_arg( [
                'page' => RANDRED_Admin::SLUG_ADD_LINK,
                'save_success' => 'error',
            ], menu_page_url(RANDRED_Admin::SLUG_ADD_LINK, false)));
        }
    }

    public function saveGroup()
    {
        if( $name = self::$application->postKeyValue('name') )
        {
            $data = [
                'name' => $name,
                'slug' => self::$application->postKeyValue('slug'),
                'description' => self::$application->postKeyValue('description')
            ];

            if( !empty( $groupId = self::$application->getKeyValue('groupid')) )
            {
                $data['id'] = $groupId;
            }

            $newId = self::$application->saveGroup($data);

            wp_redirect(add_query_arg( [
                'page' => RANDRED_Admin::SLUG_ADD_GROUP,
                'save_success' => 'success',
                'groupid' => $newId ,
            ], menu_page_url(RANDRED_Admin::SLUG_ADD_GROUP, false)));
        }
        else
        {
            wp_redirect(add_query_arg( [
                'page' => RANDRED_Admin::SLUG_ADD_GROUP,
                'save_success' => 'error',
            ], menu_page_url(RANDRED_Admin::SLUG_ADD_GROUP, false)));
        }
    }

    public function processForm()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST')
        {
            switch( self::$application->postKeyValue(RANDRED_PREFIX . 'sent-admin-form') )
            {
                case 'group-form':
                    $this->saveGroup();
                    break;
                case 'link-form':
                    $this->saveLink();
                    break;
            }
        }
        elseif( !empty($command = self::$application->getKeyValue(RANDRED_PREFIX . 'post_command')) )
        {
            switch($command)
            {
                case 'delete-group':
                    $this->deleteGroup();
                    break;
                case 'delete-link':
                    $this->deleteLink();
                    break;
            }
        }
    }

    /**
     * Custom option and settings:
     *  - callback functions
     */
    public function init()
    {
        // Register a new setting for "pdfreader" page.
        register_setting( RANDRED_OPTIONS_PAGE, RANDRED_SettingField::DEFAULT_OPTIONS_NAME );
    }

    public function submenuFilter( $submenu_file ) {

        global $plugin_page;
    
        $hidden_submenus = array(
            self::SLUG_ADD_GROUP => self::SLUG_GROUPS,
            self::SLUG_ADD_LINK => self::SLUG_LINKS,
        );
    
        // Select another submenu item to highlight (optional).
        if ( $plugin_page && isset( $hidden_submenus[ $plugin_page ] ) ) {
            $submenu_file = $hidden_submenus[ $plugin_page ];
        }
    
        // Hide the submenu.
        foreach ( $hidden_submenus as $submenu => $unused ) {
            remove_submenu_page( self::SLUG_GROUPS, $submenu );
        }
    
        return $submenu_file;
    }

    /**
     * Add the top level menu page.
     */
    public function addMenuItem()
    {
        add_menu_page( 'Manage Link Groups', 'Redirect Link Randomizer', 'manage_options', self::SLUG_GROUPS,  [self::$application, 'renderer']);

        self::addSubMenu( 'Manage Groups', 'Groups', self::SLUG_GROUPS);
        self::addSubMenu( 'Manage Links', 'Links', self::SLUG_LINKS);

        self::addSubMenu( 'Create New Group', 'New Group', self::SLUG_ADD_GROUP);
        self::addSubMenu( 'Add New Link', 'New Link', self::SLUG_ADD_LINK);
        self::addSubMenu( 'Configure Redirect Link Randomizer', 'Settings', RANDRED_OPTIONS_PAGE);
    }

    public static function addSubMenu( $title, $label, $slug, $position = null, $parent =  self::SLUG_GROUPS )
    {
        add_submenu_page( $parent, $title, $label, 'manage_options', $slug, [self::$application, 'renderer'], $position);
    }

    public static function getOption( $key, $group = null, $default = null )
    {
        // Get the value of the setting we've registered with register_setting()
        $group = $group ? $group : RANDRED_SettingField::DEFAULT_OPTIONS_NAME;
        $data = get_option( $group );

        if( isset($data[$key]) )
        {
            return $data[$key];
        }

        return $default;
    }
}
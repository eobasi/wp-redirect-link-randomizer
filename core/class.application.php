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

class RANDRED
{
    const PAGE = RANDRED_PREFIX . 'awayto';

    /**
     * @var Wpdb
     */
    protected $wpdb;
    /**
     * @var string
     */
    protected $prefix;
    /**
     * @var array
     */
    public $routes;

    public function __construct()
    {
        global $wpdb;

        $this->wpdb = $wpdb;
        $this->prefix = $wpdb->prefix . RANDRED_PREFIX; 
    }

    /**
     * Makes header redirect to random URL.
     */
    public function start( )
    {
        function link_not_found()
        {
            exit( "no link found");
        }

        if( //try and get the query var we registered in our query_vars() function
            empty($slug = get_query_var( self::PAGE ))
            // group not found
            || empty($group = $this->findGroupBySlug(sanitize_title($slug)))
            // no link found
            || empty($links = $this->findGroupLinks($group->id))
        )
        {
            link_not_found();
        }

        $redirectLinks = [];

        foreach( $links as $link )
        {
            if( !empty($link->hitlimit) && (int) $link->hitlimit >= (int) $link->hits )
            {
                continue;
            }

            $redirectLinks[] = $link->url;
        }

        if( empty($redirectLinks) )
        {
            link_not_found();
        }

        $link = $redirectLinks[rand(0, (count($redirectLinks) - 1))];

        exit("<html>
            <head><meta http-equiv='refresh' content='0; url={$link}' /></head>
            <body><p>Continue to: <a href='{$link}'>{$link}</a></p></body>
        </html>");
    }

    public function flush_rules()
    {
        $this->rewrite_rules();

        flush_rewrite_rules();
    }

    public function rewrite_rules()
    {
        add_rewrite_rule( 'awayto/?$', 'index.php?' . self::PAGE . '=index', 'top');
        add_rewrite_rule( 'awayto/(.+?)/?$', 'index.php?' . self::PAGE . '=$matches[1]', 'top');
        add_rewrite_tag( '%' . self::PAGE . '%', '([^&]+)' );
    }

    public function deleteLink( $linkId )
    {
        $this->wpdb->query("DELETE FROM `{$this->prefix}link` WHERE id = $linkId");
    }

    public function deleteGroup( $groupId )
    {
        $this->wpdb->query("DELETE FROM `{$this->prefix}group` WHERE id = $groupId");
    }

    public function saveLink( $data )
    {
        if( isset($data['id']) && !empty($id = $data['id']) )
        {
            unset($data['id']);
            $this->wpdb->update( "{$this->prefix}link", $data, ['id' => $id] );

            return $id;
        }

        $this->wpdb->insert( "{$this->prefix}link", $data );
        
        return $this->wpdb->insert_id;
    }

    public function saveGroup( $data )
    {
        if( isset($data['id']) && !empty($id = $data['id']) )
        {
            unset($data['id']);
            $this->wpdb->update( "{$this->prefix}group", $data, ['id' => $id] );

            return $id;
        }

        $this->wpdb->insert( "{$this->prefix}group", $data );
        
        return $this->wpdb->insert_id;
    }

    public function findLink( $linkId )
    {
        $query = $this->wpdb->prepare("SELECT * FROM `{$this->prefix}link`
            WHERE `id` = $linkId
        ");
        
        return $this->wpdb->get_row($query);
    }

    public function findGroupLinks( $groupId )
    {
        $query = $this->wpdb->prepare("SELECT * FROM `{$this->prefix}link`
            WHERE `groupid` = $groupId
        ");
        
        return $this->wpdb->get_results($query);
    }

    public function findLinks()
    {
        $query = $this->wpdb->prepare("SELECT * FROM `{$this->prefix}link`");
		return $this->wpdb->get_results($query);
    }

    public function sanitizeTitle( $title )
    {
        if( strlen( $title ) > 100 )
        {
			$truncate = strpos( $title, ' ', 60 );
			$title = substr( $title, 0, $truncate );
        }

        $slug = sanitize_title($title);
        $i = 0;
        
        if( $this->findGroupBySlug($slug) )
        {
			$i++;
            while( $this->findGroupBySlug($slug . '-' . $i) )
            {
				$i++;
            }
            
			$slug = ($i == 0) ? $slug : $slug . '-' . $i;
        }
        
		return $slug;
    }

    public function findGroupBySlug( $slug )
    {
        $query = $this->wpdb->prepare("SELECT * FROM `{$this->prefix}group`
            WHERE `slug` = '$slug'
        ");
        
        return $this->wpdb->get_row($query);
    }

    public function findGroup( $groupId )
    {
        $query = $this->wpdb->prepare("SELECT * FROM `{$this->prefix}group`
            WHERE `id` = $groupId
        ");
        
        return $this->wpdb->get_row($query);
    }

    public function findGroups()
    {
        $query = $this->wpdb->prepare("SELECT * FROM `{$this->prefix}group`");
		return $this->wpdb->get_results($query);
    }

    public function countGroupLinks( $groupId )
    {
        $query = $this->wpdb->prepare("SELECT COUNT(*) AS `count`, SUM(hits) AS `hits` FROM `{$this->prefix}link`
            WHERE `groupid` = $groupId
        ");
        
        return $this->wpdb->get_row($query);
    }

    public function renderer()
    {
        $slug = $this->getKeyValue('page');
        
        if( isset($this->routes[$slug]) && !empty($route = $this->routes[$slug]) )
        {
            $ctrl = sanitize_text_field($route['controller']);
            $action = sanitize_text_field($route['action']);

            if( class_exists($ctrl) && method_exists(($controller = new $ctrl()), $action) )
            {
                $controller->{$action}();
                $controller->render();

                return;
            }
        }

        echo 'page not found!';
    }

    public function getKeyValue( $key )
    {
        if( !isset($_GET[$key]) )
        {
            return;
        }

        return sanitize_text_field($_GET[$key]);
    }

    public function postKeyValue( $key )
    {
        if( !isset($_POST[$key]) )
        {
            return;
        }

        return sanitize_text_field($_POST[$key]);
    }

    public function addRoute( $slug, $controller, $action)
    {
        $this->routes[$slug] = [
            'controller' => $controller,
            'action' => $action
        ];
    }

    /**
     * Get wp option value
     * 
     * @param string $key
     * @param mixed|false $default
     * 
     * @return mixed
     */
    public static function getConfig( $key, $default = false )
    {
        return get_option($key, $default);
    }

    /**
     * Update wp option value
     * 
     * @param string $key
     * @param mixed $value
     */
    public static function saveConfig( $key, $value )
    {
        update_option( RANDRED_PREFIX . $key, $value );
    }

    /**
     * Add wp option value
     * 
     * @param string $key
     * @param mixed $value
     */
    public static function addConfig( $key, $value )
    {
        add_option( RANDRED_PREFIX . $key, $value );
    }

    public function update()
    {
        if( self::getConfig('db_version') == RANDRED_VERSION )
        {
            return;
        }
        
        // install tables
        $this->installTables();

        // store db version
        self::saveConfig( 'db_version', RANDRED_VERSION );
    }

    public function install()
    {
        // install tables
        $this->installTables();

        // store db version
        self::addConfig( 'db_version', RANDRED_VERSION );

        // load default data
        $this->loadDefaultData();
    }

    public function loadDefaultData()
    {
        $sampleData = [
            'groups' => [
                [
                'name' => 'WhatsApp Groups',
                'description' => 'WhatsApp Group links',
                'slug' => 'whatsapp'
                ],
                [
                    'name' => 'Sample Links',
                    'description' => 'This is a sample links group',
                    'slug' => 'sample'
                ]
            ]
        ];

        foreach( $sampleData['groups'] as $groupsToAdd )
        {
            $this->wpdb->insert( "{$this->prefix}group", $groupsToAdd );
        }
    }

    public function installTables()
    {
        // character set
        $charset_collate = $this->wpdb->get_charset_collate();

        // require upgrade file
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

        // `randred_group` table
        dbDelta("CREATE TABLE {$this->prefix}group (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name tinytext NOT NULL,
        description text NOT NULL,
        slug varchar(55) DEFAULT '' NOT NULL,
        PRIMARY KEY  (id)
        ) $charset_collate;");

        // `randred_link` table
        dbDelta("CREATE TABLE {$this->prefix}link (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        groupid mediumint(9) NOT NULL,
        url text NOT NULL,
        hits mediumint(9) DEFAULT 0,
        hitlimit mediumint(9) DEFAULT 0,
        createstamp int(11) NOT NULL,
        PRIMARY KEY  (id)
        ) $charset_collate;");
    }
}
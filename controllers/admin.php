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
 * @package redirect_links_randomizer/controllers
 */

/**
 * @internal never define functions inside callbacks.
 * these functions could be run multiple times; this would result in a fatal error.
 */
class RANDRED_CTRL_Admin extends RANDRED_View
{
    public function links()
    {
        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        $this->setHeading(null, 'Add New', menu_page_url(RANDRED_Admin::SLUG_ADD_LINK, false));

        $links = $this->application->findLinks();

        $groupItem = RANDRAD_HtmlTag::generateTag('tr', [], true,
            RANDRAD_HtmlTag::generateTag('th', ['scope' => 'col', 'class' => 'manage-column'], true, 'URL') .
            RANDRAD_HtmlTag::generateTag('th', ['scope' => 'col', 'class' => 'manage-column'], true, 'Group') .
            RANDRAD_HtmlTag::generateTag('th', ['scope' => 'col', 'class' => 'manage-column'], true, 'Hits') .
            RANDRAD_HtmlTag::generateTag('th', ['scope' => 'col', 'class' => 'manage-column'], true, 'Hits Limit') .
            RANDRAD_HtmlTag::generateTag('th', ['scope' => 'col', 'class' => 'manage-column'], true, 'Added')
        );

        foreach( $links as $link )
        {
            $group = $this->application->findGroup($link->groupid);
    
            $linkUrl = add_query_arg( 'linkid', $group->id, menu_page_url(RANDRED_Admin::SLUG_ADD_LINK, false) );
            $deleteUrl = add_query_arg( [
                RANDRED_PREFIX . 'post_command' => 'delete-link',
                'linkid' => $link->id,
            ], menu_page_url(RANDRED_Admin::SLUG_ADD_LINK, false) );
            $groupUrl = add_query_arg( 'groupId', $group->id, menu_page_url(RANDRED_Admin::SLUG_ADD_GROUP, false) );
    
            $groupItem .= RANDRAD_HtmlTag::generateTag('tr', [], true,
                RANDRAD_HtmlTag::generateTag('td', ['scope' => 'col', 'class' => 'column-primary'], true, "
                    <strong>
                        <a href='{$linkUrl}' class='row-title'>{$link->url}</a>
                    </strong>
                    <div class='row-actions'>
                        <span class='edit'><a href='{$linkUrl}'>Edit</a></span>
                        <span class='view'><a href='{$link->url}'>View</a></span>
                        <span class='trash'><a href='{$deleteUrl}'>Delete</a></span>
                    </div>
                ") .
                RANDRAD_HtmlTag::generateTag('td', ['scope' => 'col', 'class' => 'manage-column'], true, "<a href='{$groupUrl}'>{$group->name}</a>") .
                RANDRAD_HtmlTag::generateTag('td', ['scope' => 'col', 'class' => 'manage-column'], true, (int) $link->hits) .
                RANDRAD_HtmlTag::generateTag('td', ['scope' => 'col', 'class' => 'manage-column'], true, $link->hitlimit) .
                RANDRAD_HtmlTag::generateTag('td', ['scope' => 'col', 'class' => 'manage-column'], true, date('d/m/Y h:sa', $link->createstamp))
            );
        }

        $linkList = RANDRAD_HtmlTag::generateTag('table', ['class' => 'wp-list-table widefat fixed striped table-view-list'], true, $groupItem);

        $this->addContent("<div id='col-container' class='wp-clearfix'><div id='col-left'><div class='col-wrap'>{$this->addLinkForm()}</div></div><div id='col-right'><div class='col-wrap'>{$linkList}</div></div></div>");
    }

    public function groups()
    {
        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }
        
        $this->setHeading(null, 'Add New', menu_page_url(RANDRED_Admin::SLUG_ADD_GROUP, false));

        $groups = $this->application->findGroups();

        $groupItem = RANDRAD_HtmlTag::generateTag('tr', [], true,
            RANDRAD_HtmlTag::generateTag('th', ['scope' => 'col', 'class' => 'manage-column'], true, 'Name') .
            RANDRAD_HtmlTag::generateTag('th', ['scope' => 'col', 'class' => 'manage-column'], true, 'Links') .
            RANDRAD_HtmlTag::generateTag('th', ['scope' => 'col', 'class' => 'manage-column'], true, 'Hits')
        );

        foreach( $groups as $group )
        {
            $groupStat = $this->application->countGroupLinks($group->id);

            $groupUrl = add_query_arg( 'groupid', $group->id, menu_page_url(RANDRED_Admin::SLUG_ADD_GROUP, false) );
            $viewUrl = home_url("awayto/{$group->slug}");
            $deleteUrl = add_query_arg( [
                RANDRED_PREFIX . 'post_command' => 'delete-group',
                'groupid' => $group->id,
            ], menu_page_url(RANDRED_Admin::SLUG_ADD_GROUP, false) );

            $groupItem .= RANDRAD_HtmlTag::generateTag('tr', [], true,
                RANDRAD_HtmlTag::generateTag('td', ['scope' => 'col', 'class' => 'column-primary'], true, "
                    <strong>
                        <a href='{$groupUrl}' class='row-title'>{$group->name}</a>
                    </strong>
                    <div class='row-actions'>
                        <span class='edit'><a href='{$groupUrl}'>Edit</a></span>
                        <span class='view'><a href='{$viewUrl}'>View</a></span>
                        <span class='trash'><a href='{$deleteUrl}'>Delete</a></span>
                    </div>
                ") .
                RANDRAD_HtmlTag::generateTag('td', ['scope' => 'col', 'class' => 'manage-column'], true, (int) $groupStat->count) .
                RANDRAD_HtmlTag::generateTag('td', ['scope' => 'col', 'class' => 'manage-column'], true, (int) $groupStat->hits)
            );
        }

        $groupList = RANDRAD_HtmlTag::generateTag('table', ['class' => 'wp-list-table widefat fixed striped table-view-list'], true, $groupItem);

        $this->addContent("<div id='col-container' class='wp-clearfix'><div id='col-left'><div class='col-wrap'>{$this->groupForm()}</div></div><div id='col-right'><div class='col-wrap'>{$groupList}</div></div></div>");
    }

    /**
     * Top level menu callback function
     */
    public function addLink()
    {
        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        if( $linkid = $this->application->getKeyValue('linkid') )
        {
            $this->editLink( $linkid );
            return;
        }
        
        $this->setHeading(null, 'Manage Links', menu_page_url(RANDRED_Admin::SLUG_LINKS, false));
        $this->addContent($this->addLinkForm());
    }

    /**
     * Top level menu callback function
     */
    public function addGroup()
    {
        // check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        switch( $this->application->getKeyValue('save_success') )
        {
            case 'success':
                $this->message('success', 'Group added!');
            break;
            case 'error':
                $this->message('success', 'Enter a valid group name');
            break;
        }

        if( $this->application->getKeyValue('save_error') )
        {
            $this->message('success', 'Group added!');
        }

        $groupId = $this->application->getKeyValue('groupid');

        if( $groupId )
        {
            $this->editGroup( $groupId );
            return;
        }

        $this->setHeading(null, 'Manage Groups', menu_page_url(RANDRED_Admin::SLUG_GROUPS, false));
        
        $this->addContent($this->groupForm());
    }

    protected function editLink( $linkId )
    {
        $this->setHeading( __("Manage Link", RANDRED_DOMAIN), 'Manage Links', menu_page_url(RANDRED_Admin::SLUG_LINKS, false));

        if( !($link = $this->application->findLink((int) $linkId)) )
        {
            $this->addContent(__("Link not found", RANDRED_DOMAIN));

            return;
        }

        $group = $this->application->findGroup($link->groupid);

        $groupItem = RANDRAD_HtmlTag::generateTag('tr', [], true,
            RANDRAD_HtmlTag::generateTag('th', ['scope' => 'col', 'class' => 'manage-column'], true, 'URL') .
            RANDRAD_HtmlTag::generateTag('th', ['scope' => 'col', 'class' => 'manage-column'], true, 'Group') .
            RANDRAD_HtmlTag::generateTag('th', ['scope' => 'col', 'class' => 'manage-column'], true, 'Hits') .
            RANDRAD_HtmlTag::generateTag('th', ['scope' => 'col', 'class' => 'manage-column'], true, 'Hits Limit') .
            RANDRAD_HtmlTag::generateTag('th', ['scope' => 'col', 'class' => 'manage-column'], true, 'Added')
        );

        $groupUrl = add_query_arg( 'groupId', $group->id, menu_page_url(RANDRED_Admin::SLUG_ADD_GROUP, false) );
        $deleteUrl = add_query_arg( [
            RANDRED_PREFIX . 'post_command' => 'delete-link',
            'linkid' => $link->id,
        ], menu_page_url(RANDRED_Admin::SLUG_ADD_LINK, false) );

        $groupItem .= RANDRAD_HtmlTag::generateTag('tr', [], true,
            RANDRAD_HtmlTag::generateTag('td', ['scope' => 'col', 'class' => 'column-primary'], true, "
                <strong class='row_title'>{$link->url}</strong>
                <div class='row-actions'>
                    <span class='view'><a href='{$link->url}'>View</a></span>
                    <span class='trash'><a href='{$deleteUrl}'>Delete</a></span>
                </div>
            ") .
            RANDRAD_HtmlTag::generateTag('td', ['scope' => 'col', 'class' => 'manage-column'], true, "<a href='{$groupUrl}'>{$group->name}</a>") .
            RANDRAD_HtmlTag::generateTag('td', ['scope' => 'col', 'class' => 'manage-column'], true, (int) $link->hits) .
            RANDRAD_HtmlTag::generateTag('td', ['scope' => 'col', 'class' => 'manage-column'], true, $link->hitlimit) .
            RANDRAD_HtmlTag::generateTag('td', ['scope' => 'col', 'class' => 'manage-column'], true, date('d/m/Y h:sa', $link->createstamp))
        );

        $linkInfo = RANDRAD_HtmlTag::generateTag('table', ['class' => 'wp-list-table widefat fixed striped table-view-list'], true, $groupItem);

        $this->addContent("<div id='col-container' class='wp-clearfix'><div id='col-left'><div class='col-wrap'>{$this->addLinkForm($link)}</div></div><div id='col-right'><div class='col-wrap'>{$linkInfo}</div></div></div>");
    }

    protected function editGroup( $groupId )
    {
        $this->setHeading( __("Manage Group", RANDRED_DOMAIN), 'Manage Groups', menu_page_url(RANDRED_Admin::SLUG_GROUPS, false));

        if( !($group = $this->application->findGroup((int) $groupId)) )
        {
            $this->addContent(__("Group not found", RANDRED_DOMAIN));

            return;
        }

        $links = $this->application->findGroupLinks($group->id);

        $groupItem = RANDRAD_HtmlTag::generateTag('tr', [], true,
            RANDRAD_HtmlTag::generateTag('th', ['scope' => 'col', 'class' => 'manage-column'], true, 'URL') .
            RANDRAD_HtmlTag::generateTag('th', ['scope' => 'col', 'class' => 'manage-column'], true, 'Hits') .
            RANDRAD_HtmlTag::generateTag('th', ['scope' => 'col', 'class' => 'manage-column'], true, 'Hits Limit')
        );

        foreach( $links as $link )
        {
            $linkUrl = add_query_arg( 'linkid', $link->id, menu_page_url(RANDRED_Admin::SLUG_ADD_LINK, false) );
            $deleteUrl = '';

            $groupItem .= RANDRAD_HtmlTag::generateTag('tr', [], true,
                RANDRAD_HtmlTag::generateTag('td', ['scope' => 'col', 'class' => 'column-primary'], true, "
                    <strong>
                        <a href='{$linkUrl}' class='row-title'>{$link->url}</a>
                    </strong>
                    <div class='row-actions'>
                        <span class='edit'><a href='{$linkUrl}'>Edit</a></span>
                        <span class='view'><a href='{$link->url}'>View</a></span>
                        <span class='trash'><a href='{$deleteUrl}'>Delete</a></span>
                    </div>
                ") .
                RANDRAD_HtmlTag::generateTag('td', ['scope' => 'col', 'class' => 'manage-column'], true, (int) $link->hits) .
                RANDRAD_HtmlTag::generateTag('td', ['scope' => 'col', 'class' => 'manage-column'], true, $link->hitlimit)
            );
        }

        $linkList = RANDRAD_HtmlTag::generateTag('table', ['class' => 'wp-list-table widefat fixed striped table-view-list'], true, $groupItem);

        $this->addContent("<div id='col-container' class='wp-clearfix'><div id='col-left'><div class='col-wrap'>{$this->groupForm($group)}</div></div><div id='col-right'><div class='col-wrap'>{$linkList}{$this->addLinkForm(null, $groupId)}</div></div></div>");
    }

    protected function groupForm( $group = null )
    {
        $fields = array();

        // group name field
        $name = new RANDRED_TextField('name');
        $name->setLabel('Name');
        $name->setDescription('The name is how it appears on your list.');
        $name->setValue( $group ? $group->name : '');
        $name->init();
        array_push($fields, $name);

        // group slug field
        $slug = new RANDRED_TextField('slug');
        $slug->setLabel('Slug');
        $slug->setDescription('The “slug” is the URL-friendly version of the name. It is usually all lowercase and contains only letters, numbers, and hyphens.');
        $slug->setValue( $group ? $group->slug : '');
        $slug->init();
        array_push($fields, $slug);

        // group description field
        $description = new RANDRED_Textarea('description');
        $description->setLabel('Description');
        $description->setDescription('The description is not prominent; just enough to help you remember why you created this group.');
        $description->setValue( $group ? $group->description : '');
        $description->init();
        array_push($fields, $description);

        $submit = new RANDRED_Submit('submit');
        $submit->setValue( $group ? __('Update Group', RANDRED_DOMAIN) : __('Add Group', RANDRED_DOMAIN));
        $submit->addAttribute('class', 'button button-primary');
        array_push($fields, $submit);

        $action = menu_page_url(RANDRED_Admin::SLUG_ADD_GROUP, false);
        $action = $group ? add_query_arg( 'groupid', $group->id, $action ) : $action;

        return $this->renderForm('group-form', $fields, null, $action);
    }

    protected function addLinkForm( $link = null, $groupId = null )
    {
        $fields = array();

        // link groupid field
        $groupid = new RANDRED_SelectField('groupid');
        $groupid->setLabel('Group');
        $groupid->setDescription('Set link group');
        
        foreach( $this->application->findGroups() as $linkGroup )
        {
            $groupid->addOption($linkGroup->id, $linkGroup->name);
        }

        if( $groupId ) 
        {
            $groupid->setValue( $groupId );
            $groupid->setDisabled();
        }
        elseif( $link && $link->groupid )
        {
            $groupid->setValue( $link->groupid );
            $groupid->setDisabled();
        }

        $groupid->init();
        array_push($fields, $groupid);

        // link url field
        $url = new RANDRED_TextField('url');
        $url->setLabel('URL');
        $url->setDescription('Target url (e.g: http://www.example.com/sample-page).');
        $url->setValue( $link ? $link->url : '');
        $url->init();
        array_push($fields, $url);

        // link hitlimit field
        $hitlimit = new RANDRED_TextField('hitlimit');
        $hitlimit->setLabel('Hit Limit');
        $hitlimit->setDescription('Limit number of traffic this link can receive');
        $hitlimit->setValue( $link ? $link->hitlimit : '');
        $hitlimit->init();
        array_push($fields, $hitlimit);

        $submit = new RANDRED_Submit('submit');
        $submit->setValue( $link ? __('Update Link', RANDRED_DOMAIN) : __('Add Link', RANDRED_DOMAIN));
        $submit->addAttribute('class', 'button button-primary');
        array_push($fields, $submit);

        $action = menu_page_url(RANDRED_Admin::SLUG_ADD_LINK, false);
        $action = $link ? add_query_arg( 'linkid', $link->id, $action ) : $action;

        return $this->renderForm('link-form', $fields, null, $action);
    }

    /**
     * Top level menu callback function
     */
    public function settings()
    {
        // add general settings section
        $this->addSection( RANDRED_SettingField::SECTION_INDEX, 'General Settings');

        // display ad field
        $displayAd = new RANDRED_OptionTextField('pdfreader_display_adsense');
        $displayAd->setLabel('Show Adsense');
        $displayAd->setSection(RANDRED_SettingField::SECTION_INDEX);
        $displayAd->setDefaultValue('DISPLAY_AD');
        $displayAd->init();
     
        // add error/update messages
     
        // check if the user have submitted the settings
        // WordPress will add the "settings-updated" $_GET parameter to the url
        if ( isset( $_GET['settings-updated'] ) ) {
            // add settings saved message with the class of "updated"
            add_settings_error( 'pdfreader_messages', 'pdfreader_message', __( 'Settings Saved', RANDRED_DOMAIN ), 'updated' );
        }
     
        // show error/update messages
        settings_errors( 'pdfreader_messages' );
        ?>
        <div class="wrap">
            <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
            <form action="options.php" method="post">
                <?php
                // output security fields for the registered setting "pdfreader"
                settings_fields( RANDRED_OPTIONS_PAGE );
                // output setting sections and their fields
                // (sections are registered for "pdfreader", each field is registered to a specific section)
                do_settings_sections( RANDRED_OPTIONS_PAGE );
                // output save settings button
                submit_button( 'Save Settings' );
                ?>
            </form>
        </div>
        <?php
    }
}
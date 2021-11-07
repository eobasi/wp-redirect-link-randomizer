<?php

abstract class RANDRED_View
{
    /**
     * @var string
     */
    protected $heading = '';
    /**
     * @var string
     */
    protected $content = '';
    /**
     * @var RANDRED
     */
    protected $application;

    public function __construct()
    {
        global $__randred__;

        $this->application = $__randred__;
    }

    public function addContent( $content )
    {
        $this->content .= $content;
    }

    public function setContent( $content )
    {
        $this->content = $content;
    }

    public function setHeading( $title = null, $btnLabel = null, $btnUrl = null )
    {
        $content = esc_html( $title ? $title : get_admin_page_title() );
        
        // add button
        if( $btnLabel && $btnUrl )
        {
            $content .= RANDRAD_HtmlTag::generateTag('a', [
                'href' => $btnUrl,
                'class' => 'page-title-action'
            ], true, esc_html( $btnLabel ));
        }

        $this->heading = RANDRAD_HtmlTag::generateTag('h1', [], true, $content);
    }

    /**
     * Register a new section in the $page
     */
    public function addSection( $id, $title, $page = null, $callback = null )
    {
        add_settings_section( $id, __( $title, RANDRED_DOMAIN ), $callback ? $callback : [$this, 'sectionCallback'], $page ? $page : RANDRED_DOMAIN );
    }

    /**
     * Developers section callback function.
     *
     * @param array $args  The settings array, defining title, id, callback.
     */
    public function sectionCallback( $params )
    {
        // echo UTIL_HtmlTag::generateTag('p', [ 'id' => esc_attr( $params['id'] ) ], true, esc_html_e( 'Follow the white rabbit.', RANDRED_DOMAIN ));
    }

    public function renderForm( $name, $fields, $id = null, $action = null )
    {
        $elements = RANDRAD_HtmlTag::generateTag('input', [
            'type' => 'hidden',
            'name'=> RANDRED_PREFIX . 'sent-admin-form',
            'value' => $name
        ]) ;
        
        foreach( $fields as $field )
        {
            $elements .= RANDRAD_HtmlTag::generateTag('div', ['class' => "form-field, {$field->getName()}-wrap"], true,
                // label
                RANDRAD_HtmlTag::generateTag('label', ['for' => $field->getName()], true, $field->getLabel()) .
                // input
                $field->render()
            );
        }

        $attributes = [
            'name' => $name,
            'id' => $id ? $id : uniqid($name . '_'),
            'method' => 'post',
        ];

        if( $action )
        {
            $attributes['action'] = $action;
        }

        return "<div class='form-wrap'>" . RANDRAD_HtmlTag::generateTag('form', $attributes, true, $elements) . "</div>";
    }

    public function message($type, $message, $key = 'page_feedback')
    {
        add_settings_error( RANDRED_PREFIX . $key, '',$message, $type);
        settings_errors( RANDRED_PREFIX . $key );
    }

    public function render( )
    {
        print(RANDRAD_HtmlTag::generateTag('div', ['class' => 'wrap'], true, $this->heading . $this->content));
    }
}
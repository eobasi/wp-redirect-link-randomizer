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

abstract class RANDRED_FormElements
{
    /**
     * @var string
     */
    protected $name;
    /**
     * @var string
     */
    protected $section;
    /**
     * @var mixed
     */
    protected $defaultValue;
    /**
     * @var mixed
     */
    protected $value;
    /**
     * @var string
     */
    protected $label = '';
    /**
     * @var string
     */
    protected $description = '';
    /**
     * @var string
     */
    protected $invitation = '';
    /**
     * @var array
     */
    protected $isDisabled = false;
    /**
     * @var array
     */
    protected $args;
    /**
     * @var array
     */
    protected $attributes = array();
    
    public function __construct( $name, $args = array() )
    {
        // set input name
        $this->setName( $name );

        $this->args = array_merge( array(
            'label_for'         => $this->getName(),
            'class'             => RANDRED_PREFIX . 'row',
        ), $args);
    }

    public function init()
    {
        $this->attributes['id'] = esc_attr( $this->args['label_for'] );
        $this->attributes['name'] = esc_attr( $this->args['label_for'] );
    }

    public function setName( $name )
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setLabel( $label )
    {
        $this->label = $label;

        return $this;
    }

    public function getLabel()
    {
        return __( $this->label, RANDRED_DOMAIN );
    }

    public function setSection( $section )
    {
        $this->section = $section;

        return $this;
    }

    public function setValue( $value )
    {
        $this->value = $value;

        return $this;
    }

    public function setDefaultValue( $value )
    {
        $this->defaultValue = $value;

        return $this;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setDescription( $description )
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return __( $this->description, RANDRED_DOMAIN );
    }

    public function setInvitation( $invitation )
    {
        $this->invitation = $invitation;

        return $this;
    }

    public function addAttribute( $key, $value )
    {
        $this->attributes[$key] = $value;
    }

    public function setDisabled( $isDisabled = true )
    {
        $this->isDisabled = $isDisabled;

        return $this;
    }

    protected function generateTag( $tag, $attributes = array(), $pair = false, $content = null, $description = null )
    {
        $description = $description ? $description : RANDRAD_HtmlTag::generateTag('p', ['class' => 'description'], true, $this->getDescription());
        $attributes = $attributes ? $attributes : $this->attributes;

        if( $this->isDisabled )
        {
            $attributes['class'] = isset($attributes['class']) ? $attributes['class'] . ' disabled' : 'disabled';
            $attributes['disabled'] = 'disabled';
        }

        return RANDRAD_HtmlTag::generateTag($tag, $attributes, $pair, $content) . $description;
    }
}

abstract class RANDRED_SettingField extends RANDRED_FormElements
{
    const DEFAULT_OPTIONS_NAME = RANDRED_PREFIX . 'options';
    const SECTION_INDEX = RANDRED_PREFIX . 'index';

    /**
     * @var string
     */
    protected $optionName;

    public function setOptionName( $optionName )
    {
        $this->optionName = $optionName;

        return $this;
    }

    public function init()
    {
        $this->optionName = $this->optionName ? $this->optionName : self::DEFAULT_OPTIONS_NAME;
        $this->section = $this->section ? $this->section : self::SECTION_INDEX;

        // Register a new field in the "pdfreader_index" section, inside the "pdfreader" page.
        add_settings_field( $this->getName(), $this->getLabel(), [$this, 'render'], RANDRED_OPTIONS_PAGE, $this->section, $this->args );

        var_dump($this->args);

        if( $this->value === NULL )
        {
            $this->setValue( RANDRED_Admin::getOption( $this->getName(), $this->optionName, $this->defaultValue ) );
        }

        $this->attributes['id'] = esc_attr( $this->args['label_for'] );
        $this->attributes['name'] = $this->optionName . '[' . esc_attr( $this->args['label_for'] ) . ']';
    }

    /**
     * Option field callback function.
     *
     * WordPress has magic interaction with the following keys: label_for, class.
     * - the "label_for" key value is used for the "for" attribute of the <label>.
     * - the "class" key value is used for the "class" attribute of the <tr> containing the field.
     * Note: you can add custom key value pairs to be used inside your callbacks.
     *
     * @param array $args
     */
    abstract public function render( $args );
}

class RANDRED_SelectField extends RANDRED_FormElements
{
    /**
     * @var array
     */
    protected $options = array();

    public function addOptions( $options )
    {
        foreach( $options as $key => $value )
        {
            $this->addOption( $key, $value );
        }

        return $this;
    }

    public function addOption( $key, $value )
    {
        $this->options[$key] = $value;
    }

    public function render( )
    {
        $options = '';

        foreach( $this->options as $key => $value )
        {
            $attributes = [
                'value' => $key
            ];

            if( !empty(selected( $this->value, $key, false )) )
            {
                $attributes['selected'] = 'selected';
            }

            $options .= RANDRAD_HtmlTag::generateTag('option', $attributes, true, $value);
        }

        return $this->generateTag('select', $this->attributes, true, $options);
    }
}

class RANDRED_CheckField extends RANDRED_FormElements
{
    public function render( )
    {
        $this->attributes['type'] = 'checkbox';

        if( $this->value )
        {
            $this->attributes['checked'] = 'checked';
        }

        return $this->generateTag('input', $this->attributes);
    }
}

class RANDRED_TextField extends RANDRED_FormElements
{
    public function render( )
    {
        $this->attributes['type'] = 'text';
        $this->attributes['value'] = $this->value;

        return $this->generateTag('input', $this->attributes);
    }
}

class RANDRED_OptionTextField extends RANDRED_SettingField
{
    public function render( $args )
    {
        $this->attributes['type'] = 'text';
        $this->attributes['value'] = $this->value;

        printf($this->generateTag('input', $this->attributes));
    }
}

class RANDRED_Textarea extends RANDRED_FormElements
{
    public function render( )
    {
        $this->attributes['value'] = $this->value;

        return $this->generateTag('textarea', $this->attributes, true);
    }
}

class RANDRED_Submit extends RANDRED_FormElements
{
    public function render( )
    {
        $this->attributes['value'] = $this->value;
        $this->attributes['type'] = 'submit';

        return RANDRAD_HtmlTag::generateTag('input', $this->attributes);
    }
}

class RANDRED_HiddenField extends RANDRED_FormElements
{
    public function render( )
    {
        $this->attributes['value'] = $this->value;
        $this->attributes['type'] = 'hidden';

        return RANDRAD_HtmlTag::generateTag('input', $this->attributes);
    }
}
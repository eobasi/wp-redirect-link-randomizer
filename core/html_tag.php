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

class RANDRAD_HtmlTag
{
    /**
     * Generates and returns HTML tag code.
     *
     * @param string $tag
     * @param array $attrs
     * @param boolean $pair
     * @param string $content
     * @return string
     */
    public static function generateTag( $tag, $attrs = null, $pair = false, $content = null )
    {
        $attrString = '';
        if ( $attrs !== null && !empty($attrs) )
        {
            foreach ( $attrs as $key => $value )
            {
                $attrString .= ' ' . $key . '="' . self::escapeHtmlAttr($value) . '"';
            }
        }

        return $pair ? '<' . $tag . $attrString . '>' . ( $content === null ? '' : $content ) . '</' . $tag . '>' : '<' . $tag . $attrString . ' />';
    }

    /**
     * Escape a string for the URI or Parameter contexts. This should not be used to escape
     * an entire URI - only a subcomponent being inserted. The function is a simple proxy
     * to rawurlencode() which now implements RFC 3986 since PHP 5.3 completely.
     *
     * @param string $string
     * @return string
     */
    public static function escapeUrl( $string = null )
    {
        if ( !$string )
        {
            return;
        }

        return rawurlencode($string);
    }

    /**
     * Escape a string for the HTML Body context where there are very few characters
     * of special meaning. Internally this will use htmlspecialchars().
     *
     * @param string $string
     * @return string
     */
    public static function escapeHtml( $string = null )
    {
        if ( !$string )
        {
            return;
        }

        return htmlspecialchars($string, ENT_QUOTES);
    }

    /**
     * Escape a string for the HTML Attribute context. We use an extended set of characters
     * to escape that are not covered by htmlspecialchars() to cover cases where an attribute
     * might be unquoted or quoted illegally (e.g. backticks are valid quotes for IE).
     *
     * @param string $string
     * @return string
     */
    public static function escapeHtmlAttr( $string = null )
    {
        if ( !$string )
        {
            return;
        }

        return htmlspecialchars($string, ENT_COMPAT);
    }

    /**
     * Escapes chars to make sure that string doesn't contain valid JS code
     * 
     * @param string $string
     * @return string
     */
    public static function escapeJs( $string = null )
    {
        if ( !$string )
        {
            return;
        }

        return strtr($string,
            array('\\' => '\\\\', "'" => "\\'", '"' => '\\"', "\r" => '\\r', "\n" => '\\n', '</' => '<\/'));
    }
}

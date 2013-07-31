<?php
/**
 * @description        Descrive come oggetto un elemento html
 *
 * @package            WPDK
 * @subpackage         WPDKHtmlElement
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            24/05/12
 * @version            1.0.0
 *
 * @filename           wpdk-htmlelement
 *
 */

class WPDKHtmlElement {


    /**
     * Standard common HTML attribute
     * @var string
     */
    var $action = '';
    var $class = '';
    var $enctype = '';
    var $href = '';
    var $id = '';
    var $legend = '';
    var $method = '';
    var $name = '';
    var $size = '';
    var $style = '';
    var $title = '';
    var $value = '';

    /**
     * SDF
     * @var string
     */
    var $type = '';

    /**
     * Matrix of controls
     *
     * @var array
     */
    private $_types = array(
                WPDK_FORM_FIELD_TYPE_CUSTOM     => array( 'method' => WPDK_FORM_FIELD_TYPE_CUSTOM  , 'class' => 'wpdk-form-custom'),
                WPDK_FORM_FIELD_TYPE_CHOOSE     => array( 'method' => WPDK_FORM_FIELD_TYPE_CHOOSE  , 'class' => ''),
                WPDK_FORM_FIELD_TYPE_TEXT       => array( 'method' => WPDK_FORM_FIELD_TYPE_TEXT    , 'class' => 'wpdk-form-input'),
                WPDK_FORM_FIELD_TYPE_TEXTAREA   => array( 'method' => WPDK_FORM_FIELD_TYPE_TEXTAREA, 'class' => 'wpdk-form-textarea'),
                WPDK_FORM_FIELD_TYPE_BUTTON     => array( 'method' => WPDK_FORM_FIELD_TYPE_BUTTON  , 'class' => 'wpdk-form-button'),
                WPDK_FORM_FIELD_TYPE_FILE       => array( 'method' => WPDK_FORM_FIELD_TYPE_FILE    , 'class' => 'wpdk-form-file'),
                WPDK_FORM_FIELD_TYPE_PASSWORD   => array( 'method' => WPDK_FORM_FIELD_TYPE_PASSWORD, 'class' => 'wpdk-form-input wpdk-form-password'),
                WPDK_FORM_FIELD_TYPE_CHECKBOX   => array( 'method' => WPDK_FORM_FIELD_TYPE_CHECKBOX, 'class' => 'wpdk-form-checkbox'),
                WPDK_FORM_FIELD_TYPE_RADIO      => array( 'method' => WPDK_FORM_FIELD_TYPE_RADIO   , 'class' => 'wpdk-form-radio'),
                WPDK_FORM_FIELD_TYPE_SELECT     => array( 'method' => WPDK_FORM_FIELD_TYPE_SELECT  , 'class' => 'wpdk-form-select'),
                WPDK_FORM_FIELD_TYPE_SWIPE      => array( 'method' => WPDK_FORM_FIELD_TYPE_SWIPE   , 'class' => 'wpdk-form-swipe'),
                WPDK_FORM_FIELD_TYPE_EMAIL      => array( 'method' => WPDK_FORM_FIELD_TYPE_EMAIL   , 'class' => 'wpdk-form-input wpdk-form-email'),
                WPDK_FORM_FIELD_TYPE_PHONE      => array( 'method' => WPDK_FORM_FIELD_TYPE_PHONE   , 'class' => 'wpdk-form-input wpdk-form-phone'),
                WPDK_FORM_FIELD_TYPE_NUMBER     => array( 'method' => WPDK_FORM_FIELD_TYPE_NUMBER  , 'class' => 'wpdk-form-input wpdk-form-number'),
                WPDK_FORM_FIELD_TYPE_DATE       => array( 'method' => WPDK_FORM_FIELD_TYPE_DATE    , 'class' => 'wpdk-form-input wpdk-form-date wpdk-form-has-button-clear-left'),
                WPDK_FORM_FIELD_TYPE_DATETIME   => array( 'method' => WPDK_FORM_FIELD_TYPE_DATETIME, 'class' => 'wpdk-form-input wpdk-form-datetime wpdk-form-has-button-clear-left'),
            );

    function __construct() {

    }

    function argsInProperty( $args = array() ) {
        if ( !empty( $args ) ) {
            foreach ( $args as $key => $arg ) {
                $this->$key = $arg;
            }
        }
    }

    function classAttribute( $type ) {
        $types = $this->_types;

        if( isset( $types[$this->type] ) ) {
            $this->class .= $types[$this->type]['class'];
        }
    }

    function addClass( $class ) {
        if ( is_string( $class ) ) {
            $class_string_parts = explode( ' ', $class );
            $class_parts        = explode( ' ', $this->class );
            $merge              = array_merge( $class_parts, $class_string_parts );
            $this->class        = join( ' ', $merge );
        }
    }

}

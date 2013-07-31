<?php
/**
 * UI helper and class
 *
 * @package            WPDK (WordPress Development Kit)
 * @subpackage         _WPDKForm
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            17/05/12
 * @version            1.0.0
 *
 *
 * +--------------------------+
 * | form                     |
 * |  +---------------------+ |
 * |  | fieldset            | |
 * |  |  +----------------+ | |
 * |  |  | rows           | | |
 * |  |  +----------------+ | |
 * |  +---------------------+ |
 * +--------------------------+
 *
 */

/* */
class WPDKHTMLElement {

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



/* */
class _WPDKForm {

    var $fields;
    var $name;
    var $action;

    var $method = '';
    var $enctype = '';
    var $target = '';


    private $_fieldsets = array();


    function __construct( $fields, $name = '', $action = '' ) {
        $this->fields = $fields;
        $this->name   = $name;
        $this->action = $action;
    }

    /**
     * Restituisce l'html del form con all'interno fieldset e campi, definiti nello standatd SDF.
     *
     * @param string $before
     * @param string $before_content
     * @param string $after
     * @param string $after_content
     * @return string
     */
    function html( $before = '', $before_content = '', $after = '', $after_content = '' ) {

        $content = $this->content();

        $html = <<< HTML
{$before}
<form id="{$this->id}"
name="{$this->name}"
method="{$this->method}"
target="{$this->id}"
class="wpdk-form {$this->class}"
enctype="{$this->enctype}"
title="{$this->title}"
style="{$this->style}"
action="{$this->action}">
{$before_content}
{$content}
{$after_content}
</form>
$after
HTML;

        return $html;
    }



    private function content() {
        $content = '';
        if ( !empty( $this->_fieldsets ) ) {
            foreach ( $this->_fieldsets as $fieldset ) {
                $content .= $fieldset->html();
            }
        }
        return $content;
    }
}

/* */

class WPDKFieldset extends WPDKHTMLElement {

    private $_rows = array();

    function __construct( $args = array() ) {
        $this->argsInProperty( $args );
    }

    function html( $before = '', $before_content = '', $after = '', $after_content = '' ) {

        $content = $this->content();

        $html = <<< HTML
{$before}
<fieldset id="{$this->id}" class="wpdk-form-fieldset {$this->class}" style="{$this->style}" title="{$this->title}">
<legend id="" class="" style="" title="">{$this->legend}</legend>
{$before_content}
{$content}
{$after_content}
</fieldset>
$after
HTML;

        return $html;
    }

    function content() {
        $content = '';
        if( !empty( $this->_rows ) ) {
            foreach( $this->_rows as $row ) {
                $content .= $row->html();
            }
        }
        return $content;
    }

    function addRow( $key, $args = array() ) {
        $row = new WPDKUIRow();
        $this->_rows[$key] = $row;
        return $row;
    }
}

/* */
class WPDKUIRow extends WPDKHTMLElement {

    private $_ui_elements = array();

    function __construct() {}

    function html( $before = '', $before_content = '', $after = '', $after_content = '' ) {

        $content = $this->content();

        $html = <<< HTML
{$before}
<div id="" class="wpdk-ui-row" style="" title="">
{$before_content}
{$content}
{$after_content}
</div>
$after
HTML;

        return $html;
    }

    function addUIElement( $key, $args = array() ) {
        $element = new WPDKUIElement( $args );
        $this->_ui_elements[$key] = $element;
        return $element;
    }

    private function content() {
        $content = '';
        if ( !empty( $this->_ui_elements ) ) {
            foreach ( $this->_ui_elements as $ui_element ) {
                $content .= $ui_element->html();
            }
        }
        return $content;
    }

}

/* */
class WPDKUIElement extends WPDKHTMLElement {

    function __construct( $args = array() ) {
        $this->argsInProperty( $args );

        parent::classAttribute( $this->type );
    }

    function html( $before = '', $before_content = '', $after = '', $after_content = '' ) {

        $content = $this->content();

        $html = <<< HTML
{$before}
{$before_content}
{$content}
{$after_content}
$after
HTML;
        return $html;
    }

    private function content() {

        $types = parent::types();

        if( isset( $types[$this->type] ) ) {
            return call_user_func( array( $this, $types[$this->type]['method'] ));
        }
    }

    /* Each elements */

    function text( $args = array() ) {

        parent::argsInProperty( $args );

        $html = <<< HTML
<input type="text" class="{$this->class}" name="{$this->name}" id="{$this->id}" value="{$this->value}" size="{$this->size}" title="{$this->title}" style="{$this->style}" />
HTML;
        return $html;
    }

    function date( $args = array() ) {

        parent::argsInProperty( $args );

        $html = <<< HTML
<input type="text" class="{$this->class}" name="{$this->name}" id="{$this->id}" value="{$this->value}" size="{$this->size}" title="{$this->title}" style="{$this->style}" />
HTML;
        return $html;
    }

    function dateTime( $args = array() ) {

        parent::argsInProperty( $args );

        $html = <<< HTML
<input type="text"
    class="{$this->class}"
    name="{$this->name}"
    id="{$this->id}"
    value="{$this->value}"
    size="{$this->size}"
    title="{$this->title}"
    style="{$this->style}" /><span class="wpdk-form-clear-left"></span>
HTML;
        return $html;
    }

    function button( $args = array() ) {

        parent::argsInProperty( $args );

        $html = <<< HTML
<input type="button" class="{$this->class}" name="{$this->name}" id="{$this->id}" value="{$this->value}" size="{$this->size}" title="{$this->title}" style="{$this->style}" />
HTML;
        return $html;
    }

}
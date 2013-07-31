<?php
/**
 * jQuery Helper
 *
 * @package            WPDK (WordPress Development Kit)
 * @subpackage         WPDKjQuery
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            14/05/12
 * @version            1.0.0
 *
 */

class WPDKjQuery {


    /**
     * Restituisce l'html con la giusta formattazione per i tabs jQuery
     *
     * @static
     *
     * @param WPDKjQueryTabs|array $tabs  Oggetto WPDKjQueryTabs o array dei tabs
     * @param array|string         $class Classe CSS aggiuntive, o stringa o array
     *
     * @return bool|string Ritorna false se errore, altrimenti l'html dei tabs jQuery
     *
     */
    public static function tabs( $tabs, $class = array() ) {

        if ( empty( $tabs ) ) {
            return false;
        }

        /* $tabs può essere direttamente l'array o un oggetto di tipo WPDKjQueryTabs. */
        if ( is_object( $tabs ) && is_a( $tabs, 'WPDKjQueryTabs' ) ) {
            $uniq_id_tabs_for_cookie = $tabs->id;
            $tabs = $tabs->tabs();
        } elseif ( !is_array( $tabs ) ) {
            return false;
        }

        /* Classi css aggiuntive, o sottoforma di stringa o di array */
        $classes = '';
        if ( !empty( $class ) ) {
            if ( is_array( $class ) ) {
                $classes = join( ' ', $class );
            } elseif ( is_string( $class ) ) {
                $classes = $class;
            } else {
                $classes = '';
            }
        }

        $html_titles             = '';
        $html_content            = '';

        foreach ( $tabs as $key => $tab ) {
            $html_titles .= sprintf( '<li class="%s"><a href="#%s">%s</a></li>', $key, $key, $tab['title'] );
            $html_content .= sprintf( '<div id="%s" class="clearfix">%s</div>', $key, $tab['content'] );
        }

        /* @todo wpdk-border-container va eliminata */
        $html = <<< HTML
<div class="wpdk-border-container">
    <div id="{$uniq_id_tabs_for_cookie}" class="wpdk-tabs {$classes}">
        <ul>
            {$html_titles}
        </ul>
        {$html_content}
    </div>
</div>
HTML;
        return $html;
    }
}

/**
 * jQuery Tabs
 *
 * @package            WPDKjQuery
 * @subpackage         WPDKjQueryTabs
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            14/05/12
 * @version            1.0.0
 *
 */
class WPDKjQueryTabs {

    var $id;

    private $_tabs = array();

    function __construct( $id ) {
        $this->id = $id;
    }

    function add( $id, $title, $content = '' ) {
        $this->_tabs[$id] = array(
            'title'   => $title,
            'content' => $content
        );
    }

    function tabs() {
        return $this->_tabs;
    }

    function display( $echo = true ) {
        $html = WPDKjQuery::tabs( $this );
        if( $echo ) {
            echo $html;
        } else {
            return $html;
        }
    }
}
<?php
/**
 * @description        Classe (mancante in WordPress) che descrive un post generico. Questa sarà usata sia come entry
 *                     point di metodi statici sia per essere ereditata e descrivere il profilo di post type custom.
 *
 * @package            WPDK
 * @subpackage         _WPDKPost
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            01/06/12
 * @version            1.0.0
 *
 * @filename           wpdk-post
 *
 */

class _WPDKPost {

    private $_record;


    /**
     * Init
     */
    function __construct( $record = null ) {

        /* Richiedo un post per id. */
        if ( !is_null( $record ) && is_numeric( $record ) ) {
            $id_post       = $record;
            $this->_record = $this->cache( $id_post );

        /* Richiedo un post per oggetto record. */
        } elseif ( !is_null( $record ) && is_object( $record ) && isset( $record->ID ) ) {
            $this->_record                        = $record;
            $_SESSION['wpdk_post_' . $record->ID] = serialize( $record );

        /* Voglio creare un nuovo post. */
        } elseif ( is_null( $record ) ) {
            /* @todo Create a new onfly post */
            //$this->_record = $record;
        }
        $this->_init();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Sanitize property
    // -----------------------------------------------------------------------------------------------------------------
    private function _init() {
        $record = $this->_record;

        /* Standard post. */
        $this->id            = $record->ID;
        $this->idAuthor      = $record->post_author;
        $this->date          = $record->post_date;
        $this->dateFormat    = WPDKDateTime::formatFromFormat( $record->post_date, MYSQL_DATE_TIME, __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        $this->dateGMT       = $record->post_date_gmt;
        $this->dateGMTFormat = WPDKDateTime::formatFromFormat( $record->post_date_gmt, MYSQL_DATE_TIME, __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        $this->update        = $record->post_modified;
        $this->updateFormat  = WPDKDateTime::formatFromFormat( $record->post_modified, MYSQL_DATE_TIME, __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        $this->name          = $record->post_title;
        $this->description   = $record->post_content;
        $this->content       = apply_filters( 'the_content', $record->post_content );
        $this->title         = apply_filters( 'the_title', $record->post_title );
        $this->status        = $record->post_status;
        $this->slug          = $record->post_name;
        $this->guid          = $record->guid;
        $this->type          = $record->post_type;

        /* Extra fields, post meta: ogni classe specifica, che erdita questa, sistemerà poi questi campi extra perché li
        conosce */
        $this->post_meta    = $record->post_meta;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Database
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Legge un post dal database comprensivo di tutti i suoi post meta posizionati nella proprietà post_meta
     *
     * @param int $id_post ID del post
     *
     * @return mixed
     */
    public function record( $id_post ) {

        $record            = get_post( $id_post );
        $record->post_meta = get_post_custom( $id_post );

        return $record;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Legge o imposta un transient/cache.
     * Questa verrà aggiornata ogni qualvolta viene inserito, modificato o cancellato un post.
     *
     * @param int      $id     ID del post
     * @param stdClass $record Oggetto record dal database. Passare questo parametro per memorizzarlo in cache.
     *
     * @return stdClass|null Restituisce un oggetto di tipo stdClass o null se errore
     */
    private function cache( $id, $record = null ) {
        if ( !WPDK_CACHE_POST ) {
            return $this->record( $id );
        } elseif ( WPDK_CACHE_POST && is_null( $record ) ) {
            if ( isset( $_SESSION['wpdk_post_' . $id] ) ) {
                unserialize( $_SESSION['wpdk_post_' . $id] );
            } else {
                $record                       = $this->record( $id );
                $_SESSION['wpdk_post_' . $id] = serialize( $record );
            }
        } elseif ( WPDK_CACHE_POST && is_object( $record ) ) {
            $_SESSION['wpdk_post_' . $id] = serialize( $record );
        }
        return $record;
    }

}

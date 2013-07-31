<?php
/**
 * Utility dedicate ai post meta
 *
 * @package     WPDK (WordPress Development Kit)
 * @subpackage  WPDKPostMeta
 * @author      =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright   Copyright (c) 2012 wpXtreme, Inc.
 * @link        http://wpxtre.me
 * @created     18/01/12
 * @version     1.0.0
 *
 */

class WPDKPostMeta {

	// -----------------------------------------------------------------------------------------------------------------
	// Utility
	// -----------------------------------------------------------------------------------------------------------------

    public static function updatePostMetaWithDeleteIfNotSet( $id_post, $meta_key, $meta_value ) {
        if ( isset( $meta_value ) || !is_null( $meta_value ) ) {
            if ( substr( $meta_key, -2 ) == '[]' ) {
                $meta_key = substr( $meta_key, 0, strlen( $meta_key ) - 2 );
            }
            update_post_meta( $id_post, $meta_key, $meta_value );
        } else {
            delete_post_meta( $id_post, $meta_key );
        }
    }
}

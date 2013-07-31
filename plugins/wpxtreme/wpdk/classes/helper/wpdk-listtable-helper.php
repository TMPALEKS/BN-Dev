<?php
/**
 * Utility per WordPress WP List Table
 *
 * @package            ${PACKAGE}
 * @subpackage         WPDKListTable
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            28/02/12
 * @version            1.0.0
 *
 */

class WPDKListTable {

    // -----------------------------------------------------------------------------------------------------------------
    // Actions
    // -----------------------------------------------------------------------------------------------------------------

    public static function actions( $args, $status ) {

        $id       = key( $args );
        $id_value = $args[$id];

        foreach ( $args['actions'] as $key => $label ) {
            $href          = add_query_arg( array( 'action' => $key, $id => $id_value, ) );
            $actions[$key] = sprintf( '<a href="%s">%s</a>', $href, $label );
        }

        if ( empty( $status ) || $status != 'trash' ) {
            unset( $actions['untrash'] );
            unset( $actions['delete'] );
        } else if ( $status == 'trash' ) {
            unset( $actions['edit'] );
            unset( $actions['trash'] );
        }

        return $actions;
    }

}

<?php
/**
 * Math utility and fix
 *
 * @package            WPDK (WordPress Development Kit)
 * @subpackage         WPDKMath
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            02/02/12
 * @version            1.0.0
 *
 */

define( 'WPDK_MATH_INFINITY', 'infinity' );

class WPDKMath {


    // -----------------------------------------------------------------------------------------------------------------
    // Modulo patch
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Mimica la funzione matematica Modulo presente in altri linguaggi allineando i relativi ritorni (ex. Ruby, Python
     * & TLC).
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKDateTime
     * @since      1.0.0
     * @author     =stid=
     *
     * @static
     *
     * @param $a, $n
     *
     * @return integer
     */
    public static function rModulus( $a, $n ) {
        return ( $a - ( $n * round( $a / $n ) ) );
    }

}

<?php
/**
 * @description
 *
 * @package            WPDK
 * @subpackage         WPDKFilesystemHelper
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            04/06/12
 * @version            1.0.0
 *
 * @filename           wpdk-filesystem-helper
 *
 */

class WPDKFilesystemHelper {

    /**
     * Init
     */
    function __construct() {
    }


    /**
     * @param string $filename      File name or path to a file
     * @param int    $precision     Digits to display after decimal
     *
     * @return string|bool Size (B, KiB, MiB, GiB, TiB, PiB, EiB, ZiB, YiB) or boolean
     */
    public static function fileSize( $filename, $precision = 2 ) {
        static $units = array(
            'Bytes',
            'KB',
            'MB',
            'G',
            'T',
            'P',
            'E',
            'Z',
            'Y'
        );

        if ( is_file( $filename ) ) {
            if ( !realpath( $filename ) ) {
                $filename = $_SERVER['DOCUMENT_ROOT'] . $filename;
            }
            $bytes = filesize( $filename );

            // hardcoded maximum number of units @ 9 for minor speed increase
            $e = floor( log( $bytes ) / log( 1024 ) );
            return sprintf( '%.' . $precision . 'f ' . $units[$e], ( $bytes / pow( 1024, floor( $e ) ) ) );
        }
        return false;
    }
}

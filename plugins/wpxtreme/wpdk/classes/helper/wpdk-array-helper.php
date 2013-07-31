<?php
/**
 * Helper per fli array
 *
 * @package            WPDK (WordPress Development Kit)
 * @subpackage         WPDKArray
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            13/02/12
 * @version            1.0.0
 *
 */

class WPDKArray {

    /**
     * Inserisce un $key => $value all'interno di un dato array ad un determinato indice
     *
     * @static
     *
     * @param array   $arr   Array da modificare
     * @param string  $key   Chiave
     * @param mixed   $val   Valore
     * @param int     $index Indice, 0 based
     *
     * @return array
     */
    public static function insertKeyValuePair( $arr, $key, $val, $index ) {
        $arrayEnd   = array_splice( $arr, $index );
        $arrayStart = array_splice( $arr, 0, $index );
        return ( array_merge( $arrayStart, array( $key=> $val ), $arrayEnd ) );
    }

    /**
     * Inserisce un array (anche $key => $value) all'interno di un dato array ad un determinato indice
     *
     * @static
     *
     * @param array   $arr   Array da modificare
     * @param         $new
     * @param int     $index Indice, 0 based
     *
     * @return array
     */
    public static function insert( $arr, $new, $index ) {
        $arrayEnd   = array_splice( $arr, $index );
        $arrayStart = array_splice( $arr, 0, $index );
        return ( array_merge( $arrayStart, $new, $arrayEnd ) );
    }


    /**
     * Esegue la funzione php http_build_query() su versione > 5, altrimenti la riproduce manualmente
     *
     * @static
     * @internal
     * @prototype
     *
     * @param array $formdata       Array $key => $value
     * @param null  $numeric_prefix Vedi documentazione http_build_query()
     * @param null  $arg_separator  Vedi documentazione http_build_query()
     *
     * @return string
     */
    public static function httpBuildQuery( $formdata, $numeric_prefix = null, $arg_separator = null ) {
        if ( defined( 'PHP_MAJOR_VERSION' ) && PHP_MAJOR_VERSION >= 5 ) {
            if ( PHP_MAJOR_VERSION >= 5 ) {
                return http_build_query( $formdata, $numeric_prefix, $arg_separator );
            }
        } else {
            $version = absint( phpversion() );
            if ( $version >= 5 ) {
                return http_build_query( $formdata, $numeric_prefix, $arg_separator );
            }
        }

        $result = '';
        $amp    = '';
        foreach ( $formdata as $key => $value ) {
            $result .= sprintf( '%s%s=%s', $amp, $key, urlencode( $value ) );
            $amp = $arg_separator;
        }

        return $result;
    }


    /* @todo Prototipo */
    public static function wrapArray( $array ) {
        $result = array();
        foreach( $array as $element ) {
            $result[] = array( $element );
        }
        return $result;
    }


    /**
     * Restituisce una copia dell'array $array_extract che possiede solo le chiavi presenti in $array_key
     *
     * @static
     * @param array $array_key
     * @param array $array_extract
     * @return array
     */
    public static function arrayWithKey( $array_key, $array_extract ) {
        $keys   = array_keys( $array_key );
        $result = array();
        foreach ( $array_extract as $key => $value ) {
            if ( in_array( $key, $keys ) ) {
                $result[$key] = $value;
            }
        }
        return $result;
    }

}

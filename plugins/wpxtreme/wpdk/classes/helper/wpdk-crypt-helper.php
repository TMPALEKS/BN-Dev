<?php
/**
 * Utility class for crypting, password and unique code
 *
 * @package            WPDK (WordPress Development Kit)
 * @subpackage         WPDKCrypt
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            21/02/12
 * @version            1.0.0
 *
 */

class WPDKCrypt {

    // -----------------------------------------------------------------------------------------------------------------
    // Uniq code generator
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Genera un codice univoco di 13 caratteri alfanumerici (esadecimale) con prefisso e suffisso opzionali, tenendo
     * conto di una massima lunghezza
     *
     * @param string $prefix
     * @param string $posfix
     * @param int    $max_length
     *
     * @return string
     */
    public static function uniqcode( $prefix = '', $posfix = '', $max_length = 64 ) {
        $uniqcode = uniqid( $prefix ) . $posfix;
        if ( ( $uniqcode_len = strlen( $uniqcode ) ) > $max_length ) {
            /* Catch from end */
            return substr( $uniqcode, -$max_length );
        }
        return $uniqcode;
    }

    /**
     * Restituisce una stringa casuale composta da caratteri alfa numerici di lunghezza arbitraria.
     *
     * @static
     *
     * @param int    $len   Lunghezza della stringa che si vuole ottenere, default 8
     * @param string $extra Caratteri extra separati da virgola, default = '#,!,.'
     *
     * @return string Stringa casuale composta da caratteri alfa numerici di lunghezza arbitraria
     *
     */
    public static function randomAlphaNumber($len = 8, $extra = '#,!,.') {
        $alfa = 'a,b,c,d,e,f,g,h,i,j,k,l,m,n,o,p,q,r,s,t,u,v,w,x,y,z,A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,W,X,Y,Z';
        $num  = '0,1,2,3,4,5,6,7,8,9';
        if ($extra != '') {
            $num .= ',' . $extra;
        }
        $alfa = explode(',', $alfa);
        $num  = explode(',', $num);
        shuffle($alfa);
        shuffle($num);
        $misc = array_merge($alfa, $num);
        shuffle($misc);
        $result = substr(implode('', $misc), 0, $len);

        return $result;
    }
}

<?php
/**
 *
 * Questa classe astratta - se ereditata - permette di aggiungere una migliore gestione delle proprietà, che su PHP non
 * è propriamente rigorosa. Grazie ai metodi magici del PHP, come __get() e __set(), permette di simulare la gestione
 * delle proprietà di Objective-C. Data quindi una variabile pubblica, nel momento che si prova ad impostarla, verrà
 * cercato il metodo accessorio set[Nome proprietà]; stessa cosa per la lettura.
 *
 * @package            WPDK (WordPress Development Kit)
 * @subpackage         WPDKObject
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            17/05/12
 * @version            1.0.0
 *
 */

class WPDKObject {

    /// Get accessor
    public function __get( $name ) {
        //NSLog("%s::%s - %s", __CLASS__, __FUNCTION__, $name);

        if ( method_exists( $this, ( $method = 'get' . ucfirst( $name ) ) ) ) {
            return $this->$method();
        } else {
            return $this->$name;
        }
    }

    /// Utility property
    public function __isset( $name ) {
        if ( method_exists( $this, ( $method = 'isset' . ucfirst( $name ) ) ) ) {
            return $this->$method();
        } else {
            return;
        }
    }

    /// Set accessor
    public function __set( $name, $value ) {
        if ( method_exists( $this, ( $method = 'set' . ucfirst( $name ) ) ) ) {
            $this->$method( $value );
        }
    }

    /// Utility property
    public function __unset( $name ) {
        if ( method_exists( $this, ( $method = 'unset' . ucfirst( $name ) ) ) ) {
            $this->$method();
        }
    }

}

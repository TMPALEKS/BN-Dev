<?php
/**
 * Gestisce le impostazioni ad albero
 *
 * @package            WPDK (WordPress Development Kit)
 * @subpackage         WPDKSettings
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            15/05/12
 * @version            1.0.0
 *
 * @todo Deve diventare NON statica o al massimo ibrida
 *
 */

class WPDKSettings {

    var $entry;
    var $option_name;

    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    function __construct( $option_name, $entry ) {
        $this->option_name = $option_name;
        $this->entry       = $entry;
    }
    
    // -----------------------------------------------------------------------------------------------------------------
    // General: get/Set Shorthand
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Legge o imposta una serie di impostazioni per Smart Shop.
     *
     * @package    WPDK (WordPress Development Kit)
     * @subpackage WPDKSettings
     * @since      1.0.0
     *
     * @param string $key    ID delle impostazioni
     * @param array  $values Se impostato le impostazioni sono scritte
     *
     * @return array Impostazioni o true se impostate, false se errore
     */
    public function settings( $key, $values = null ) {
        $settings = $this->options();
        if ( !is_null( $values ) ) {
            $settings[$this->entry][$key] = $values;
            return update_option( $this->option_name, $settings );
        } else {
            return $settings[$this->entry][$key];
        }
    }

    /**
     * Legge o imposta un valore (hash) nelle impostazioni
     *
     * @package    WPDK (WordPress Development Kit)
     * @subpackage WPDKSettings
     * @since      1.0.0
     *
     * @param string $section ID della sezione: general, wp_integration, ...
     * @param string $key     ID della impostazione
     * @param mixed  $values  Se impostato cambia i settings
     *
     * @return mixed|null Restituisce il valore dell'impostazione o null se essa non esiste. In caso di impostazione del
     *             valore viene restituito il rotorno della funzione update_option()
     */
    public function setting( $section, $key, $values = null ) {
        $settings = $this->options();
        if ( !is_null( $values ) ) {
            $settings[ $this->entry ][$section][$key] = $values;
            return update_option( $this->option_name, $settings );
        } else {
            if ( isset( $settings[ $this->entry ][$section][$key] ) ) {
                return $settings[ $this->entry ][$section][$key];
            }
            return null;
        }
    }

    /*
    Con PHP 5+ è possibile intercettare dinamicamente chiamate ai metodi, così da emulare metodi dinamici runtime.
    Decommentanto il codice qui sotto si ottengono in automatico dei metodi virtuali chiamati con il nome della
    impostazione in settings.
    Non usato in quanto c'è un notevole loop (foreach()) da eseguire. Per ragioni di velocità è meglio l'accesso
    hash del metodo di sopra ::setting()

    public static function  __callStatic( $method, $args ) {
        $options  = $this->options();
        $settings = $options[ $this->entry ];
        foreach ( $settings as $section => $methods ) {
            foreach ( $methods as $setting => $value ) {
                if ( $method == $setting ) {
                    return $settings[$section][$method];
                }
            }
        }
    }

    */

    /**
     * Imposta una sotto sezione delle impostazioni con i pvalori di dafault
     *
     * @package    WPDK (WordPress Development Kit)
     * @subpackage WPDKSettings
     * @since      1.0.0
     *
     * @param string $key Identificativo della sotto sezione delle impostazioni
     *
     * @return bool
     */
    public function resetDefault( $key ) {
        $defaults = $this->defaultOptions();
        return $this->settings( $key, $defaults[ $this->entry ][$key] );
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Methods
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Get/Set an options plugin.
     *
     * @package    WPDK (WordPress Development Kit)
     * @subpackage WPDKSettings
     * @since      1.0.0
     *
     * @param string $setting Id/Key dell'impostazione da leggero o impostare ( in base al parametro $value). Se null vengono restituite
     *                        tutte le option sotto forma di array
     * @param null   $value   Valore da impostare
     *
     * @return mixed|bool|null Se si cercava un'impostazione e questa non viene trovata, viene restisuito il valore di
     *             default, se anche questo non viene trovato restituisce null. Se si vuole registrare un impostazione restituisce
     *             true
     */
    private function options( $setting = null, $value = null ) {
        $options = get_option( $this->option_name );
        if ( is_null( $setting ) ) {
            $optionsDefault = $this->defaultOptions();
            $result         = wp_parse_args( $options, $optionsDefault );
            return $result;
        }
        if ( is_null( $value ) ) {
            if ( isset( $options[$setting] ) ) {
                return $options[$setting];
            } else {
                $optionsDefault = $this->defaultOptions();
                if ( isset( $optionsDefault[$setting] ) ) {
                    return $optionsDefault[$setting];
                } else {
                    return null;
                }
            }
        } else {
            $options[$setting] = $value;
            update_option( $this->option_name, $options );
            return true;
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Actions
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Aggiunge le opzioni alla wp_options di WordPress, se non ci sono. Usata anche durante attivazione e riattivazione
     * se vengono introdotte nuove opzioni di default, vedi aggiornamenti.
     *
     * @package    WPDK (WordPress Development Kit)
     * @subpackage WPDKSettings
     * @since      1.0.0
     *
     * @return void
     */
    public function init() {
        add_option( $this->option_name, $this->defaultOptions() );
        $options        = get_option( $this->option_name );
        $optionsDefault = $this->defaultOptions();
        $currentOptions = wp_parse_args( $options, $optionsDefault );
        update_option( $this->option_name, $currentOptions );
    }

    /**
     * Brute reset of the options plugin. Warning, use careful.
     *
     * @package    WPDK (WordPress Development Kit)
     * @subpackage WPDKSettings
     * @since      1.0.0
     *
     * @return bool False if option was not added and true if option was added.
     */
    public function optionReset() {
        delete_option( $this->option_name );
        return add_option( $this->option_name, $this->defaultOptions() );
    }

    /**
     * Elimina definitivamente un'impostazione
     *
     * @package    WPDK (WordPress Development Kit)
     * @subpackage WPDKSettings
     * @since      1.0.0
     *
     * @param $setting
     *
     * @return bool False if option was not added and true if option was added.
     */
    public function optionDelete( $setting ) {
        $options = get_option( $this->option_name );
        unset( $options[$setting] );
        return update_option( $this->option_name, $options );
    }

}

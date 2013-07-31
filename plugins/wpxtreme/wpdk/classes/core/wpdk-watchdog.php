<?php
/**
 * Debugger manager
 *
 * @package            WordPress Development Kit
 * @subpackage         WPDKWatchDog
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            01/02/12
 * @version            1.0.0
 *
 */

class WPDKWatchDog {

    const ERROR   = 'error';
    const WARNING = 'warning';
    const STATUS  = 'status';

    /**
     * Funzione proprietaria per la generazione di un log su disco. Questa crea un file di log in modalità append nella
     * cartella del plugin. Se da un lato le informazioni e la formattazione delle stesse sono a nostra discrezione e
     * completamente personalizzabili, bisogna passare le informazioni di classe, funzione e linea manualmente.
     *
     * In alternativa o in concorrnza è possibile usare trigger_error()
     *
     * @todo Watch dog sul database
     *
     * @static
     *
     * @param string $class    Nome della classe
     * @param string $txt      (opzionale) Testo libero aggiuntivo. Se omesso viene emessa una riga separatrice
     *
     * @see        trigger_error()
     */
    public static function watchDog( $class, $txt = null ) {
        if ( defined( 'WPDK_WATCHDOG_DEBUG') && WPDK_WATCHDOG_DEBUG ) {

            if( is_null( $txt ) ) {
                $txt = '---------------------------------------------------------------------------------------------';
            }

            /* Comune su file o sul trigger_error() é */
            $output = sprintf( "[%s] %s: %s\n", date( 'Y-m-d H:i:s' ), $class, $txt );

            if ( defined( 'WPDK_WATCHDOG_DEBUG_ON_FILE') && WPDK_WATCHDOG_DEBUG_ON_FILE ) {
                $handle = fopen( WPDK_LOG_FILE, "a+" );
                fwrite( $handle, $output );
                fclose( $handle );
            }

            if ( defined( 'WPDK_WATCHDOG_DEBUG_ON_TRIGGER_ERROR') && WPDK_WATCHDOG_DEBUG_ON_TRIGGER_ERROR ) {
                trigger_error( $output );
            }

            /* @todo Da pensare e fare */
            if ( defined( 'WPDK_WATCHDOG_DEBUG_ON_DATABASE') && WPDK_WATCHDOG_DEBUG_ON_DATABASE ) {
                // implement
            }
        }
    }


    /**
     * Costruisce un output con la queue di errore di un oggetto WP_Error
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKWatchDog
     * @since      1.0.0
     *
     * @static
     *
     * @param WP_Error $error Oggetto WP_Error
     * @param bool     $echo  Se true viene emesso un output, altrimenti viene restituito un buffer
     *
     * @return string|void In base al parametro $echo viene emesso l'output o restituitoil buffer
     */
    public static function displayWPError( $error, $echo = true ) {
        $message = '<div class="wpdk-watchdog-wp-error">';

        if ( is_wp_error( $error ) ) {

            foreach ( $error->errors as $code => $single ) {
                $message .= sprintf( '<code>Code: 0x%x, Description: %s</code>', $code, $single[0] );
                $error_data = $error->get_error_data( $code );
                if ( !empty( $error_data ) ) {
                    if ( is_array( $error_data ) ) {
                        foreach ( $error_data as $key => $data ) {
                            $message .= sprintf( '<code>Key: %s, Data: %s</code>', $key, urldecode( $data ) );
                        }
                    } else {
                        $message .= sprintf( '<code>Data: %s</code>', urldecode( $error_data ) );
                    }
                }
            }

        } else {
            $message .= __( 'No error detect', WPDK_TEXTDOMAIN );
        }

        /* log to file if enabled */
        self::watchDog( __CLASS__, esc_attr( wp_strip_all_tags( $message ) ) );

        $message .= '</div>';

        if ( $echo ) {
            echo $message;
            return true;
        }
        return $message;
    }


    /**
     * Restituisce il tipo di errore se il codice è nel formato [tipo]-[codice], o [prefix]_[tipo]-[codice]
     * ad esempio: 'wpss_warning-too_many_file' restituisce 'warning' oppure 'warning-too_many_file' restituisce sempre
     * 'warning'.
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKWatchDog
     * @since      1.0.0
     *
     * @static
     *
     * @param WP_Error $error Oggetto WP_Error
     *
     * @return bool|string Tipo di errore o false se errore
     */
    public static function getErrorType( WP_Error $error ) {
        if ( is_wp_error( $error ) ) {
            $code  = $error->get_error_code();
            $parts = explode( '-', $code );
            if ( strpos( $parts[0], '_' ) !== false ) {
                $prefix = explode( '_', $parts[0] );
                return $prefix[1];
            }
            return $parts[0];
        }
        return false;
    }

    /**
     * Restituisce il codice di errore se il codice è nel formato [tipo][codice],
     * ad esempio: 'wpss_warning-too_many_file' restituisce 'too_many_file'.
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKWatchDog
     * @since      1.0.0
     *
     * @static
     *
     * @param WP_Error $error Oggetto WP_Error
     *
     * @return bool|string Tipo di errore o false se errore
     */
    public static function getErrorCode( WP_Error $error ) {
        if ( is_wp_error( $error ) ) {
            $code  = $error->get_error_code();
            $parts = explode( '-', $code );
            return $parts[1];
        }
        return false;
    }

    /* Alias */
    public static function getStatusCode( WP_Error $status ) {
        return self::getErrorCode( $status );
    }

    public static function get_var_dump( $content ) {
        ob_start();
        ?><pre><?php var_dump( $content ) ?></pre><?php
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // has/is zone
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Verifica se errore
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKWatchDog
     * @since      1.0.0
     *
     * @static
     *
     * @param WP_Error $error Oggetto errore
     *
     * @return bool
     *             Restituisce true se il codice è nel formato [prefix]_[error]-[message] o [error]-[message]
     */
    public static function isError( $error ) {
        $type = self::getErrorType( $error );
        return ( $type && $type == self::ERROR );
    }

    /**
     * Verifica se status
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKWatchDog
     * @since      1.0.0
     *
     * @static
     *
     * @param WP_Error $error Oggetto errore
     *
     * @return bool
     *             Restituisce true se il codice è nel formato [prefix]_[error]-[message] o [error]-[message]
     */
    public static function isStatus( $error ) {
        $type = self::getErrorType( $error );
        return ( $type && $type == self::STATUS );
    }

    /**
     * Verifica se warning
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKWatchDog
     * @since      1.0.0
     *
     * @static
     *
     * @param WP_Error $error Oggetto errore
     *
     * @return bool
     *             Restituisce true se il codice è nel formato [prefix]_[error]-[message] o [error]-[message]
     */
    public static function isWarning( $error ) {
        $type = self::getErrorType( $error );
        return ( $type && $type == self::WARNING );
    }

}

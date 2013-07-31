<?php
/**
 * @class              WPXSmartShopPermalink
 *
 * @description        Gestisce i custom permalink per i post onfly
 *
 * @package            wpx SmartShop
 * @subpackage         core
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            15/03/12
 * @version            1.0.0
 *
 * @todo               Nel metodo permalink() che restituisce l'array dei permalink, è stata introdotto una patch per la
 *                     gestione del multilanguage. L'idea sarebbe riuscire a recuperare la traduzione con gettext() per
 *                     una determinata lingua. Questa parte è da studiare bene in modo più approfondito, ovvero: sto
 *                     visualizzando in 'italiano', mi dai i link delle altre (inglese) lingue?
 *
 */

class WPXSmartShopPermalink {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Lista dei custom permalink e relativa funzione. I metodi 'display' sono molto facili da implementare in quanto
     * non fanno altro che preparare un contenuto da passare al metodo post() della classe WPDKPost,
     * che crea un post onfly. Ottenuto questo post è possibile visualizzare il content.
     *
     * @static
     * @retval array
     *
     * @see WPSmartShopShowcase::display(), WPSmartShopShowcase::displayPost(), WPSmartShopShowcase::displayCustomPost()
     */
    public static function permalinks() {
        $permalinks = array(

            __( 'debug', WPXSMARTSHOP_TEXTDOMAIN ) => array(
                'callback'     => array(
                    'WPXSmartShopPermalink',
                    'debug'
                ),
                'localization' => array(
                    'it' => 'debug',
                    'en' => 'debug',
                )
            ),
            __( 'showcase', WPXSMARTSHOP_TEXTDOMAIN ) => array(
                'callback'     => array(
                    'WPSmartShopShowcase',
                    'display'
                ),
                'localization' => array(
                    'it' => 'vetrina',
                    'en' => 'showcase',
                )
            ),
            __( 'print', WPXSMARTSHOP_TEXTDOMAIN )    => array(
                'callback'     => array(
                    'WPXSmartShopInvoice',
                    'printing'
                ),
                'localization' => array(
                    'it' => 'stampa',
                    'en' => 'print',
                )
            ),

// @todo Da implementare
//            'payment'                        => array(
//                'callback'     => array(
//                    'WPSmartShopPaymentGateway',
//                    'payment'
//                ),
//                'localization' => array(
//                    'it' => 'pagamento',
//                    'en' => 'payment',
//                )
//            )
        );

        return $permalinks;
    }

    /**
     * Analizza la richiesta pervenuta a WordPress e verifica che ci sia un custom permalink nella lista dei
     * registrati. In caso affermativo chiama class/metodo. Viene anche effettuato un controllo base sulla presenza
     * di WPML, dando per scontato che sia stati impostato con '[url]/[language]
     *
     * @todo               Da testare e migliorare a seconda delle impostazioni dei pemalink di WordPress e di WPML
     *
     * @static
     * @retval bool
     */
    public static function dispatchRequest() {
        global $wp;

        /* Lista dei permalinks registrati con classe/metodo */
        $permalinks = self::permalinks();

        /* WP Request */
        $wp_request = $wp->request;

        /* Integrazione base con WPML */
        if ( WPXSmartShopWPML::isWPLM() ) {
            $language_prefix = sprintf( '%s/', ICL_LANGUAGE_CODE );
            $wp_request      = str_replace( $language_prefix, '', $wp_request );
        }


        /* Controllo tra i registrati ed eseguo */
        if ( isset( $permalinks[$wp_request] ) ) {
            status_header( 200 );
            call_user_func( $permalinks[$wp_request]['callback'] );
            return true;
        }
        return false;
    }

    /**
     * Restituisce il permalink virtuale localizzato. Questo è comodo nel contesto di WordPress multilanguage,
     * in particolare con WPML.
     *
     * @static
     * @param $language
     * @retval mixed|string
     */
    public static function localization( $language ) {
        global $wp;

        /* Lista dei permalinks registrati con classe/metodo */
        $permalinks = self::permalinks();

        /* WP Request */
        $wp_request = $wp->request;

        /* Integrazione base con WPML */
        if ( WPXSmartShopWPML::isWPLM() ) {
            $language_prefix = sprintf( '%s/', ICL_LANGUAGE_CODE );
            $wp_request      = str_replace( $language_prefix, '', $wp_request );
        }

        if ( isset( $permalinks[$wp_request] ) ) {
            return array( $wp_request, $permalinks[$wp_request]['localization'][$language] );
        }

        return false;
    }

    public static function debug() {
        include WPXSMARTSHOP_PATH_CLASSES . 'unit_test.php';
    }

}
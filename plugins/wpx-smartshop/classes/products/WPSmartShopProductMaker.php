<?php
/**
 * @class WPSmartShopProductMaker
 *
 * Gestisce la creazione automatica di prodotti seguendo determinate regole
 * Questa classe permette di creare un oggetto WPSmartShopProductMaker in grado di generare in automatico un certo
 * numero di prodtti (Post) e con determinate caratteristiche ripetitive.
 *
 * @package            wpx SmartShop
 * @subpackage         products
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @date               22/03/12
 * @version            1.0.0
 *
 */

class WPSmartShopProductMaker {

    /**
     * @var int Numero di prodotti da generare
     */
    private $_quantity = 1;

    /**
     * @var array Informazioni sulla creazione del post
     */
    private $_post_values = array();

    /**
     * @var string Specifica la sintassi da adottare per la generazione del titolo
     */
    private $_title_prefix = '';
    private $_title_body = '';
    private $_title_postfix = '';

    /**
     * @var array
     * Regole sul prezzo
     */
    private $_price_rules = array();

    /**
     * @var array
     * Varianti
     */
    private $_variants = array();

    private $_availability_date_start = null;
    private $_availability_date_expire = null;

    /**
     * @var array
     * Gestione magazzino
     */
    private $_warehouse = array();

    /**
     * @var float Prezzo base
     */
    public $price = 0.0;


    /**
     * Costruttore
     */
    function __construct() {
        $this->_post_values = $this->defaultPostValues();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static value
    // -----------------------------------------------------------------------------------------------------------------

    private function defaultPostValues() {

        /* L'utente attualmente loggato */
        /* @todo Aggiungere filtro */
        $post_author = get_current_user_id();

        /* Data adesso: 2012-03-22 12:41:42 */
        /* @todo Aggiungere filtro */
        $post_date     = date( 'Y-m-d H:i:s' );
        $post_date_gmt = $post_date;

        /* Stato pubblicato */
        /* @todo Aggiungere filtro */
        $post_status = 'publish';

        $defaults = array(
            'post_author'   => $post_author,
            'post_content'  => '',
            'post_excerpt'  => '',
            'post_date'     => $post_date,
            'post_date_gmt' => $post_date_gmt,
            'post_parent'   => 0,
            'post_status'   => $post_status,
            'post_title'    => '',
            'post_type'     => WPXSMARTSHOP_PRODUCT_POST_KEY
        );

        return $defaults;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Shorthand per l'impostazione delle proprietà del post
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Imposta il formato del titolo
     *
     * @param string $prefix Formato del titolo
     * @param string $body
     * @param string $postfix
     *
     * @retval string
     */
    function formatTitle( $prefix = '', $body = '', $postfix = '' ) {
        $this->_title_prefix  = $prefix;
        $this->_title_body    = $body;
        $this->_title_postfix = $postfix;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Shorthand per l'impostazione delle proprietà del post
    // -----------------------------------------------------------------------------------------------------------------


    // -----------------------------------------------------------------------------------------------------------------
    // Maker
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Crea un post (prodotto) in base alle specifiche informazioni
     *
     * @uses get_post()
     *
     * @retval object Oggetto Post
     */
    function create() {
        /* Recupero i valori di default */
        $values = $this->defaultPostValues();

        /* Imposto il titolo */
        $values['post_title'] = $this->title();

        $id_post = wp_insert_post( $values );

        /* Se - come per default - è pubblicato, genero il permalink */
        if ( $values['post_status'] == 'publish' ) {
            $post_slug = wp_unique_post_slug( sanitize_title( $values['post_title'] ), $id_post, $values['post_status'], $values['post_type'], $values['post_parent'] );
            wp_update_post( array( 'ID' => $id_post, 'post_name' => $post_slug ) );
        }

        $this->updatePostMeta( $id_post );

        return $id_post;
    }

    /**
     * Aggiorna i vari campi post meta
     *
     * @param $id_post ID del 'prodotto' post
     */
    function updatePostMeta( $id_post ) {

        /* Base price */
        update_post_meta( $id_post, 'wpss_product_base_price', $this->price );

        /* Price rules */
        if ( !empty( $this->_price_rules ) ) {
            update_post_meta( $id_post, 'wpss_product_price_for_rules', serialize( $this->_price_rules ) );
        }

        /* Warehouse availability */
        if ( !empty( $this->_availability_date_expire ) ) {
            update_post_meta( $id_post, 'wpss_product_available_to_date', $this->_availability_date_expire );
        }

        /* Warehouse count. */
        if ( !empty( $this->_warehouse ) ) {
            update_post_meta( $id_post, 'wpss_product_store_quantity', $this->_warehouse['product_store_quantity'] );
            update_post_meta( $id_post, 'wpss_product_store_quantity_for_order_confirmed', $this->_warehouse['product_store_quantity_for_order_confirmed'] );
            update_post_meta( $id_post, 'wpss_product_store_quantity_for_order_pending', $this->_warehouse['product_store_quantity_for_order_pending'] );
        }

        /* Variants */
        if ( !empty( $this->_variants ) ) {
            update_post_meta( $id_post, 'wpss_product_appearance', serialize( $this->_variants ) );
        }
    }

    /**
     * Aggiunge una regola di prezzo
     *
     * @param string $id          ID della regola: kWPSmartShopProductTypeRuleDatePrice o
     *                            kWPSmartShopProductTypeRuleOnlinePrice, oppure un ruolo utente
     * @param float  $price       Prezzo
     * @param int    $qty         Valido per una determonata Quantità per ordine
     * @param int    $abs_qty     Valido per una determonata Quantità per ordini già effettuati
     * @param float  $percentage  Percentuale da applicare a $price
     * @param string $date_start  Data inizio. Se $id = kWPSmartShopProductTypeRuleDatePrice
     * @param string $date_expiry Data fine. Se $id = kWPSmartShopProductTypeRuleDatePrice
     * @param bool $rounded controlla se il prezzo deve essere arrotondato o meno (true di default)
     */
    function addPriceRule( $id, $price, $qty, $abs_qty, $percentage, $date_start = '', $date_expiry = '', $rounded = true ) {
        if ( !empty( $price ) || !empty( $percentage ) ) {

            /* @todo Controllare gli arrotondamenti */
            if ( empty( $price ) ) {
            	if ($rounded) 
            		$price = round( $this->price * ( ( 100 - $percentage ) / 100 ) );
            	else
                	$price = $this->price * ( ( 100 - $percentage ) / 100 );
                $price = WPXSmartShopCurrency::formatCurrency( $price, true );
            }

            /* @todo Controllare gli arrotondamenti */
            if ( empty( $percentage ) ) {
            	if ($rounded) 
            		$percentage = round( 100 - ( ( $price / $this->price ) * 100 ) );
            	else
                	$percentage = 100 - ( ( $price / $this->price ) * 100 );
                $percentage = WPXSmartShopCurrency::formatPercentage( $percentage, true );
            }

            /* Sanitizzo qty */
            if ( !empty( $abs_qty ) && !empty( $qty ) ) {
                $abs_qty = max( $abs_qty, $qty );
            }

            $this->_price_rules[] = array(
                'wpss-product-rule-id'  => $id,
                'date_from'             => $date_start,
                'date_to'               => $date_expiry,
                'price'                 => $price,
                'percentage'            => $percentage,
                'qty'                   => $qty,
                'abs_qty'               => $abs_qty
            );
        }
    }

    function clearPriceRules() {
        $this->_price_rules = array();
    }

    /**
     * Aggiunge una variante
     *
     * @param $id_variant
     * ID della variante
     *
     * @param $args
     * Array keypair con chiave/valore
     *
     * @example
     *
     * wpss_product_appearance = array(
     *   'id_variante' => array(
     *       'weight'     => '',
     *       'width'      => '',
     *       'height'     => '',
     *       'depth'      => '',
     *       'volume'     => '',
     *       'color'      => '',
     *       'material'   => '',
     *       'model'      => '',
     *       'note'       => '',
     *       'value'      => ''
     *     ),
     *    'id_variante' => array( ... ), // come precedente
     * );
     *
     */
    function addVariant( $id_variant, $args ) {
        $this->_variants[$id_variant] = $args;
    }

    function removeVariant( $id_variant ) {
        unset( $this->_variants[$id_variant] );
    }

    function clearVariants() {
        $this->_variants = array();
    }

    function availability( $date_start = null, $date_expire = null ) {

        if ( !empty( $date_expire ) ) {
            $this->_availability_date_expire = WPDKDateTime::dateTime2MySql( $date_expire, __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        }
    }

    function warehouse( $store_quantity, $confirmed = 0, $pending = 0 ) {
        $this->_warehouse = array(
            'product_store_quantity'                     => $store_quantity,
            'product_store_quantity_for_order_confirmed' => $confirmed,
            'product_store_quantity_for_order_pending'   => $pending,
        );
    }
    
    /* @todo Restituisce un titolo ben formattato */

    function title() {
        $prefix  = date( $this->_title_prefix );
        $body    = $this->_title_body;
        $postfix = date( $this->_title_postfix );
        $title   = sprintf( '%s%s%s', $prefix, $body, $postfix );
        return $title;
    }


}

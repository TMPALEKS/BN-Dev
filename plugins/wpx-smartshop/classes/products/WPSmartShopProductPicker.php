<?php
/**
 * Pannello per la scelta ed inserimento dei prodotti dall'interno di un post type
 *
 * @package            wpx SmartShop
 * @subpackage         WPSmartShopProductPicker
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            19/12/11
 * @version            1.0
 *
 */

class WPSmartShopProductPicker {

    /**
     * Parametri di costruzione
     *
     * @package            wpx SmartShop
     * @subpackage         WPSmartShopProductPicker
     * @since              1.0
     *
     * @var
     */
    private $args;

    /**
     * Costruttore
     *
     * @param array $args Parametri di visualizzazione e comportamento
     *
     */
    function __construct( $args = array() ) {

        /* Argomenti di default */
        $defaults = self::defaults();

        /* Unisco con quelli degli inputs */
        $this->args = wp_parse_args( $args, $defaults );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    private function enqueueStyles() {
    }

    private function enqueueScripts() {
    }

    /**
     * Restituisce un array con le chiavi e valori di default per impostare il product picker
     *
     * @package            wpx SmartShop
     * @subpackage         WPSmartShopProductPicker
     * @since              1.0
     *
     * @static
     * @retval array
     */
    public static function defaults() {
        /* Argomenti di default */
        $defaults = array(
            'product_type_selected_id' => 0,
            'title'                    => __( 'Product Picker' ),
            'product_order'            => 'ASC',
            'product_orderby'          => 'ID',
            'hide_product'             => false,
            'hide_product_type'        => false,
            'sortable_product'         => false,
            'sortable_product_type'    => false,
            'draggable_product'        => false,
            'checkbox_product_type'    => false,
            'product_toolbar'          => true,
            'product_type_toolbar'     => true,
            'product_filter_for'       => 'title',

            'search_filter'            => '',
            'posts_per_page'           => 10,
            'paged'                    => 1,
        );
        return $defaults;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static methods
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Metodo di utilità per eliminare da una array sorgente quelle chiavi che non fanno parte delle impostazioni
     * proprio del product picker. Utile, ad esempio, se l'array sorgente è un $_POST
     *
     * @package            wpx SmartShop
     * @subpackage         WPSmartShopProductPicker
     * @since              1.0
     *
     * @static
     *
     * @param $args
     *
     * @retval mixed
     */
    public static function merge( $args ) {
        $defaults = self::defaults();

        foreach ( $args as $key => $arg ) {
            if ( !key_exists( $key, $defaults ) ) {
                unset( $args[$key] );
            }
        }
        return $args;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress integration
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Aggiunge le registrazioni per il bottone/icona del product picker all'editor di WordPress e prepara l'HTML per
     * visualizzare il dialogo jQuery.
     *
     * @package            wpx SmartShop
     * @subpackage         WPSmartShopProductPicker
     * @since              1.0
     *
     * @static
     *
     */
    public static function registerMCEButtons() {
        add_filter( 'mce_buttons', array( __CLASS__, 'mce_buttons' ) );
        add_filter( 'mce_external_plugins', array( __CLASS__, 'mce_external_plugins' ) );

        add_action( 'admin_footer-post.php', array( __CLASS__, 'dialogProductsPicker' ) );
        add_action( 'admin_footer-post-new.php', array( __CLASS__, 'dialogProductsPicker' ) );
    }

    /**
     * Integrazione con l'editor di WordPress
     *
     * @package            wpx SmartShop
     * @subpackage         WPSmartShopProductPicker
     * @since              1.0
     *
     * @static
     *
     * @param $buttons
     *
     * @retval array
     */
    public static function mce_buttons( $buttons ) {
        array_push( $buttons, '|', 'ProductPicker' );
        return $buttons;
    }

    /**
     * Indica quale script Javascript caricare per aggiungere (come plugin MCE) il bottone product picker all'editor
     * di WordPress.
     *
     * @package            wpx SmartShop
     * @subpackage         WPSmartShopProductPicker
     * @since              1.0
     *
     * @static
     *
     * @param $plugins
     *
     * @retval array
     */
    public static function mce_external_plugins( $plugins ) {
        $plugins['ProductPicker'] = WPXSMARTSHOP_URL . 'js/productpicker.js';
        return $plugins;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // UI
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce l'HTML del Product Picker
     *
     * @package            wpx SmartShop
     * @subpackage         WPSmartShopProductPicker
     * @since              1.0
     *
     * @param string $id Identificativo del product picker. Verrà usato come id del div contenitore principale
     * @param bool   $echo
     *
     * @retval string
     */
    public function display( $id, $echo = true ) {

        /* Store id for html markup below */
        $this->args['id'] = $id;

        /* JSON encode args array */
        $name           = $id . '_json';
        $json_args      = base64_encode( json_encode( $this->args ) );
        $html_json_args = <<< HTML
    <input type="hidden" name="{$name}" value="$json_args" />
HTML;

        /* Calcolate additional class */
        $class = $this->args['hide_product'] ? 'hide_product ' : '';
        $class .= $this->args['hide_product_type'] ? 'hide_product_type ' : '';

        /* Prepare HTML content container */
        $html_product_types = '';
        $html_products      = '';
        $html_title         = '';

        /* Title? */
        if ( !empty( $this->args['title'] ) ) {

            /**
             * Filtro sul titolo del product Picker
             *
             * @filters
             *
             * @param string $title Titolo del Product picker
             */
            $title      = apply_filters( 'wpss_product_picker_title', $this->args['title'] );
            $html_title = <<< HTML
    <h3>{$title}</h3>
HTML;
        }

        /* Build Product Types list */
        if ( !$this->args['hide_product_type'] ) {
            $html_product_types = $this->htmlProductTypes();
        }

        /* Build Products list */
        if ( !$this->args['hide_product'] ) {
            $html_products = $this->htmlProducts( $this->args['product_type_selected_id'] );
        }

        /* Toolbar? */
        if ( $this->args['product_toolbar'] && !$this->args['hide_product'] ) {
            $label        = __( 'Search', WPXSMARTSHOP_TEXTDOMAIN );
            $html_toolbar = <<< HTML
    <div class="wpss-product-picker-toolbar">
        <div><input type="text" class="wpdk-form-input" /> <input class="wpdk-form-button" type="button" value="{$label}" /></div>
    </div>
HTML;
        }

        /**
         * @filters
         *
         * @param string $before HTML da inserire prima del contenitore principale del Product Picker. Default ''
         */
        $html   = apply_filters( 'wpss_product_picker_display_before', '' );

        /**
         * @filters
         *
         * @param string $head HTML da inserire in testa al Product Picker all'interno del suo contenitore. Default ''
         */
        $head   = apply_filters( 'wpss_product_picker_display_head', '' );

        /**
         * @filters
         *
         * @param string $footer HTML da inserire prima della chiusura contenitore. Default ''
         */
        $footer = apply_filters( 'wpss_product_picker_display_footer', '' );

        $html .= <<< HTML
    <div id="{$id}" class="clearfix wpss-product-picker {$class}">
        {$head}
        {$html_json_args}
        {$html_title}
        {$html_product_types}
        <div id="{$id}_products" class="wpss-product-picker-products">
            {$html_toolbar}
            {$html_products}
        </div>
        {$footer}
    </div>
HTML;
        /**
         * @filters
         *
         * @param string $html HTML con tutto il Product Picker, così da poter aggiungere altro dopo il contentore.
         * Default ''
         */
        $html = apply_filters( 'wpss_product_picker_display_after', $html );

        if ( $echo ) {
            echo $html;
        } else {
            return $html;
        }
    }


    /**
     * Blocco prodotti
     *
     * @package            wpx SmartShop
     * @subpackage         WPSmartShopProductPicker
     * @since              1.0
     *
     * @param int $term_id
     *
     * @retval string
     */
    public function htmlProducts( $term_id = 0 ) {
        /* Prepare html output */
        $html_products = sprintf( '<h6>%s</h6>', __( 'Please select a Product Type', WPXSMARTSHOP_TEXTDOMAIN ) );

        /* Non visualizzo mai tutti i prodotti */
        if ( $term_id > 0 ) {
            $html_products = $this->htmlProductsList( $term_id );
        }

        $html_products_list = <<< HTML
    <div data-term_id="{$term_id}" class="wpss-product-picker-products-list">
        <ul>
            {$html_products}
        </ul>
    </div>
HTML;

        return $html_products_list;
    }

    /**
     * Restituisce l'HTML dei soli elementi prodotti compresi tra LI
     *
     * @package            wpx SmartShop
     * @subpackage         WPSmartShopProductPicker
     * @since              1.0
     *
     * @param int $term_id
     * @param int $paged
     *
     * @retval string
     */
    public function htmlProductsList( $term_id = 0, $paged = 1 ) {

        /* Prepare html output */
        $draggable      = '';
        $html_products  = '';
        $id             = $this->args['id'];
        $load_next      = __( 'Load next items', WPXSMARTSHOP_TEXTDOMAIN );
        $html_load_next = <<< HTML
    <li id="wpss-product-picker-load-next_{$id}" class="wpss-product-picker-load-next">
        <p>{$load_next}</p>
    </li>
HTML;
        /* Non visualizzo mai tutti i prodotti */
        if ( $term_id > 0 ) {
            $term = get_term( $term_id, kWPSmartShopProductTypeTaxonomyKey );

            $args = array(
                'post_status'      => 'publish',
                'suppress_filters' => false,
                'order'            => $this->args['product_order'],
                'orderby'          => $this->args['product_orderby'],
                'posts_per_page'   => $this->args['posts_per_page'],
                'paged'            => $paged,
                'tax_query'        => array(
                    array(
                        'taxonomy' => kWPSmartShopProductTypeTaxonomyKey,
                        'field'    => 'id',
                        'terms'    => $term_id
                    )
                ),
                'post_type'        => WPXSMARTSHOP_PRODUCT_POST_KEY,
            );

            /* Altero la where er get_posts() */
            if ( !empty( $this->args['search_filter'] ) ) {
                add_filter( 'posts_where', array(
                                                $this,
                                                'posts_where'
                                           ), 10, 2 );
            }

            if ( $this->args['draggable_product'] ) {
                $draggable = 'wpss-product-picker-draggable-product';
            }

            $products = get_posts( $args );

            /* Elimino filtro di alterazione where se precedentemente aggiunto */
            if ( !empty( $this->args['search_filter'] ) ) {
                remove_filter( 'posts_where', array(
                                                   $this,
                                                   'posts_where'
                                              ) );
            }

            if ( count( $products ) > 0 ) {
                $count = 0;
                foreach ( $products as $product ) {
                    $thumbnail  = WPXSmartShopProduct::thumbnail( $product->ID );
                    $title      = $product->post_title;
                    $id_product = $product->ID;
                    $html_products .= <<< HTML
    <li class="{$draggable}">
         <a data-id_product="{$id_product}" class="clearfix" href="#">
             {$thumbnail}
             <h4>{$title}</h4>
         </a>
    </li>
HTML;
                    $count++;
                }
                if ( $count != $this->args['posts_per_page'] ) {
                    $html_load_next = '';
                }

                $html_products .= $html_load_next;
            } else {
                $message       = __( 'No products found!', WPXSMARTSHOP_TEXTDOMAIN );
                $html_products = <<< HTML
    <h6>{$message}</h6>
HTML;

            }
        }

        return $html_products;
    }

    public function posts_where( $where, &$wp_query ) {
        global $wpdb;
        $search = '%' . esc_sql( like_escape( $this->args['search_filter'] ) ) . '%';
        $where .= sprintf( " AND %s.post_title LIKE '%s'", $wpdb->posts, $search );

        return $where;
    }

    /**
     * Restituisce l'html della lista dei Tipi prodotto
     *
     * @package            wpx SmartShop
     * @subpackage         WPSmartShopProductPicker
     * @since              1.0
     *
     * @retval string
     */
    public function htmlProductTypes() {

        /* Prepare HTML content container */
        $html_sortbale_list = '';
        $html_product_types = '';
        $html_toolbar       = '';

        $div_class = '';
        $ul_class  = '';

        /* Sortable? */
        if ( $this->args['sortable_product_type'] ) {
            $name               = $this->args['id'] . '_sorter';
            $html_sortbale_list = <<< HTML
    <input type="hidden" name="{$name}" value=""/>
HTML;
            $ul_class .= 'wpdk-ul-sortable ';
        }

        // -------------------------------------------------------------------------------------------------------------
        // Internal
        // -------------------------------------------------------------------------------------------------------------
        function __li( $id, $tax, $args ) {

            $result        = '';
            $html_checkbox = '';
            $tax_name      = $tax['name'];

            /* Checkbox? */
            if ( $args['checkbox_product_type'] ) {
                $html_checkbox = <<< HTML
    <input type="checkbox" name="wpss_showcase_product_type_id[]" value="{$id}" />
HTML;
            }

            if ( $tax['child'] && is_array( $tax['child'] ) ) {
                foreach ( $tax['child'] as $id_child => $child ) {
                    $result .= __li( $id_child, $child, $args );
                }
            }

            $selected = '';
            if ( !empty( $args['product_type_selected_id'] ) ) {
                $selected = ( $args['product_type_selected_id'] == $id ) ? 'selected' : '';
            }

            $html = <<< HTML
<li id="{$id}">
    {$html_checkbox}
    <a data-term_id="{$id}" class="{$selected}" href="#">{$tax_name}</a>
    <ul>
        {$result}
    </ul>
</li>
HTML;
            return $html;
        }

        // -------------------------------------------------------------------------------------------------------------
        // Internal
        // -------------------------------------------------------------------------------------------------------------

        /* Get all taxonomy */
        $terms = WPSmartShopProductTypeTaxonomy::arrayTaxonomy();

        if ( $terms ) {
            foreach ( $terms as $id => $term ) {
                $html_product_types .= __li( $id, $term, $this->args );
            }
        }

        /* Toolbar? */
        if ( $this->args['product_type_toolbar'] ) {
            /**
             * @filters
             *
             * @param string $title Titolo della sezione tipo prodotti. Default 'Product Types'
             */
            $title        = apply_filters( 'wpss_product_picker_title_product_types', __( 'Product Types', WPXSMARTSHOP_TEXTDOMAIN ) );
            $html_toolbar = <<< HTML
    <div class="wpss-product-picker-toolbar">
        <h3>{$title}</h3>
    </div>
HTML;
        }

        $html = <<< HTML
<div class="wpss-product-picker-product-types">
    {$html_toolbar}
    <div class="wpss-product-picker-product-types-list{$div_class}">
        {$html_sortbale_list}
        <ul class="{$ul_class}">
            {$html_product_types}
        </ul>
    </div>
</div>
HTML;
        return $html;
    }

    /**
     * Dialogo (generico - quello richiamato dal bottone MCE) per la selezione del Prodotto/Tipo prodotto
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopProductPicker
     * @since      1.0.0
     *
     * @todo Aggiungere filtro sul titolo ed eventuale classe del contenitore
     *
     * @static
     *
     */
    public static function dialogProductsPicker() { ?>
    <div style="display:none" id="wpss-product-picker" title="<?php _e( 'Select a product', WPXSMARTSHOP_TEXTDOMAIN ) ?>">
        <h6><?php _e( 'Loading...', WPXSMARTSHOP_TEXTDOMAIN ) ?></h6>
    </div>
    <?php
    }

}
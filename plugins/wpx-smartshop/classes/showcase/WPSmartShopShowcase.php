<?php
/**
 * Gestione Vetrina
 *
 * @package            wpx SmartShop
 * @subpackage         WPSmartShopShowcase
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            06/03/12
 * @version            1.0.0
 *
 */

class WPSmartShopShowcase {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    public static function markupHeader() {
        $html = <<< HTML
<div class="page-wrap">
    <div class="left sizeMedium">
        <article class="content box white">
            <h2 class="entry-title">[%TITLE%]</h2>
            [%CONTENT%]
        </article>
    </div>
HTML;
        return $html;

    }

    public static function markupFooter() {
        $html = <<< HTML
</div>
HTML;
        return $html;

    }

    // -----------------------------------------------------------------------------------------------------------------
    // Permalink filter (for post onfly)
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce il permalink (eventualmente da impostazioni backend) della vetrina showcase
     *
     * @todo Da studiare meglio, ma almeno è incapsulato in un metodo
     *
     * @prototype
     * @static
     *
     */
    public static function permalinkShowcase() {
        $result = sprintf( '/%s', __( 'showcase', WPXSMARTSHOP_TEXTDOMAIN ) );
        if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
            $language = ( ICL_LANGUAGE_CODE == WPXSMARTSHOP_WPML_DEFAULT_LANGUAGE ) ? '' : '/'
                .ICL_LANGUAGE_CODE;
            $result   = sprintf( '%s%s', $language, $result );
        }
        return $result;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Post Type
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce l'html di uno showcase
     *
     * @static
     * @param mixed $showcase ID, Post object o post array dello showcase che si vuole estrarre
     * @param array $custom_settings
     * @retval string
     */
    public static function showcase( $showcase = null, $custom_settings = array() ) {
        /* Questo mi serve per sovrascrivere la globale in modo che eventuali shortcode o altro continuino
        a funzionare */
        global $post;

        /* Se non ho post */
        if ( is_null( $showcase ) ) {
            /* Prende la prima vetrina disponibile */
            $post = self::showcaseAvailable();
            $id = $post->ID;
        } else {
            $post = $showcase;
        }

        /* Polimorfic */
        if ( is_object( $post ) ) {
            $id = $post->ID;
        } elseif ( is_numeric( $post ) ) {
            $id = $post;
        } elseif ( is_array( $post ) ) {
            $id = $post['ID'];
        }


        /* @todo Causa bugs WPML 2.5.1 viene sempre restituito il post id in inglese. */
        $id_showcase  = WPXSmartShopWPML::originalShowcaseID( $id );

        /* @todo Causa bugs WPML 2.5.1 viene sempre restituito il post id in inglese. */
        $post = get_post( $id );
        if( ICL_LANGUAGE_CODE == 'it' ) {
            $post = get_post( $id_showcase );
        }

        $toolbar      = get_post_meta( $id_showcase, 'wpss_showcase_toolbar', true );
        $html_toolbar = '';
        if ( !empty( $toolbar ) && $toolbar == 'on_top' ) {
            $html_toolbar = self::toolbarProductTypes( $id_showcase );
        }

        $title   = apply_filters( 'the_title', $post->post_title );
        $content = $html_toolbar . apply_filters( 'the_content', $post->post_content );

        /* Extra content */
        $products = self::products( $id_showcase );

        $html_products = '';
        foreach ( $products as $product ) {
            /* Impostazioni scheda prodotto da backend / sezione showcase */
            $args               = WPXSmartShop::settings()->settings( 'product_card' );
            $args['appearance'] = false;
            $args['variants']   = false;
            /**
             * @filters
             *
             */
            $args = apply_filters( 'wpss_showcase_product_card_args', $args, $product );
            $html_products .= WPXSmartShopProduct::card( $product, $args );
        }

        $html_toolbar = '';
        if ( !empty( $toolbar ) && $toolbar == 'after_content' ) {
            $html_toolbar = self::toolbarProductTypes( $id_showcase );
        }

        $content .= <<< HTML
    {$html_toolbar}
    <div class="wpss-showcase clearfix">
        {$html_products}
    </div>
HTML;
        /* Post onfly per ingannare WordPress */
        $post_onfly = WPDKPost::post( $title, $content, kWPSmartShopStorePagePostTypeKey );
        ob_start();
        self::displayPost( $post_onfly, $custom_settings );
        $result = ob_get_contents();
        ob_end_clean();
        return $result;
    }


    /**
     * Visualizza (onfly) un post di tipo vetrina. Questo metodo visualizza sia una vetrina reale tramite il suo
     * permalink, sia la prima disponibile in base ai criteri dello showcase.
     *
     * @package            wpx SmartShop
     * @subpackage         WPSmartShopShowcase
     * @since              1.0
     *
     * @static
     *
     */
    public static function display() {
        global $post;

        if( !empty( $post ) && $post->post_type != kWPSmartShopShowcasePostTypeKey ) {
            $post = null;
        }

        echo self::showcase( $post );
        die();
    }

    /**
     * Visualizza il contenuto di una vetrina in base alle impostazioni di backend. Viene utilizzato o un file del
     * tema o una visualizzazione tramite impostazioni personalizzate.
     *
     * @package            wpx SmartShop
     * @subpackage         WPSmartShopShowcase
     * @since              1.0
     *
     * @static
     *
     * @param object $post_onfly Oggetto Post
     * @param array  $custom_settings
     */
    private static function displayPost( $post_onfly, $custom_settings = array() ) {

        if ( empty( $custom_settings ) ) {
            $settings = WPXSmartShop::settings()->settings( 'showcase');
        } else {
            $settings = $custom_settings;
        }

        if ( $settings['theme_page'] == 'custom' ) {
            /* Visualizzazione personalizzata da backend */
            self::displayCustomPost( $post_onfly, $custom_settings );
        } else {
            /* Utilizzo un file del tema */
            /* @todo Remove sidebar */
            $filename = sprintf( '/%s.php', $settings['theme_page'] );
            include( TEMPLATEPATH . $filename );
        }
    }

    /**
     * Methodo di basso livello che recupera le impostazioni eseguite nel backend per visualizzare una vetrina in
     * modalità custom.
     *
     * @package            wpx SmartShop
     * @subpackage         WPSmartShopShowcase
     * @since              1.0
     *
     * @static
     *
     * @param object $post_onfly Oggetto post
     * @param array  $custom_settings
     */
    private static function displayCustomPost( $post_onfly, $custom_settings = array() ) {

        if ( empty( $custom_settings ) ) {
            $settings = WPXSmartShop::settings()->settings( 'showcase' );
        } else {
            $settings = $custom_settings;
        }

        if ( wpdk_is_bool( $settings['theme_header'] ) ) {
            get_header();
        }

        $content = base64_decode( $settings['theme_markup_header'] );
        $content = str_replace( '[%TITLE%]', $post_onfly->post_title, $content );
        $content = str_replace( '[%CONTENT%]', $post_onfly->post_content, $content );

        echo html_entity_decode( $content );

        if ( wpdk_is_bool( $settings['theme_sidebar'] ) ) {
            if ( empty( $settings['theme_sidebar_id'] ) ) {
                $sidebar_id = '';
            } else {
                $sidebar_id = $settings['theme_sidebar_id'];
            }
            get_sidebar( $sidebar_id );
        }

        $content = base64_decode( $settings['theme_markup_footer'] );
        $content = str_replace( '[%TITLE%]', $post_onfly->post_title, $content );
        $content = str_replace( '[%CONTENT%]', $post_onfly->post_content, $content );

        echo html_entity_decode( $content );

        if ( wpdk_is_bool( $settings['theme_footer'] ) ) {
            get_footer();
        }
    }

    /**
     * Restituisce la prima vetrina utile che rispetta determinate caratteristiche.
     *
     * @todo               Non considera gli ulteriori compi custom come data range e ruolo utente
     *
     * @static
     * @retval bool | object Record della vetrina disponibile, false se non c'è nessuna vetrina
     */
    public static function showcaseAvailable() {
        $args = array(
            'post_status'      => 'publish',
            'post_type'        => kWPSmartShopShowcasePostTypeKey,
            'numberposts'      => 1,
        );

        $showcase = get_posts( $args );
        if ( $showcase ) {
            return $showcase[0];
        }
        return false;
    }

    /**
     * Restituisce l'elenco dei prodotti indicati nella vetrina passata negli inputs.
     *
     * @note WPML: la get_posts() funziona in base alla lingua. Questo significa che se vengono passati id che esistono
     * solo in italiano, ad esempio, e ci troviamo in inglese, questo metodo restituisce solo quelli tradotti. Molto
     * probabilmente questo accede per via delle JOIN sottostanti.
     *
     * @package            wpx SmartShop
     * @subpackage         WPSmartShopShowcase
     * @since              1.0
     *
     * @static
     *
     * @param int $id_showcase
     *
     * @retval array
     */
    public static function products( $id_showcase ) {
        global $showcase_sequence;

        $showcase_sequence = get_post_meta( $id_showcase, 'wpss_showcase_products_sorter_sequence', true );
        $ids               = explode( ',', $showcase_sequence );

        /* WPML integration */
        $ids               = WPXSmartShopWPML::IDsProductIn( $ids, false );
        $showcase_sequence = join( ',', $ids );

        $args = array(
            'post_status'      => 'publish',
            'numberposts'      => -1,
            'suppress_filters' => false,
            'post_type'        => WPXSMARTSHOP_PRODUCT_POST_KEY,
            'post__in'         => $ids
        );

        /* @nested */
        function posts_orderby( $orderby ) {
            global $showcase_sequence, $wpdb;

            $orderby = 'FIND_IN_SET(' . $wpdb->posts . '.ID, \'' . $showcase_sequence . '\')';
            return $orderby;
        }
        /* @nested */

        add_filter( 'posts_orderby', 'posts_orderby' );

        $products = get_posts( $args );

        remove_filter( 'posts_orderby', 'posts_orderby' );

        return $products;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Toolbar Product Types
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce l'HTML della toolbar con l'elenco dei tipi prodotto selezionati e ordinati nella vestrina
     *
     * @package            wpx SmartShop
     * @subpackage         WPSmartShopShowcase
     * @since              1.0
     *
     * @static
     *
     * @param int $id_showcase ID della vetrina
     *
     * @retval string
     */
    public static function toolbarProductTypes( $id_showcase ) {

        $terms_sequence = get_post_meta( $id_showcase, 'wpss_showcase_product_types_sorter_sequence', true );
        $terms_id       = explode( ',', $terms_sequence );

        $html_terms = '';
        foreach ( $terms_id as $id ) {
            $term = get_term( $id, kWPSmartShopProductTypeTaxonomyKey );
            if ( $term ) {
                $term_slug = $term->slug;
                $term_link = get_term_link( $term );
                $term_name = apply_filters( 'the_category', $term->name );

                $html_terms .= <<< HTML
    <li class="wpss-showcase-prodcut-types-toolbar_{$term_slug}">
        <a href="{$term_link}">{$term_name}</a>
    </li>
HTML;
            }
        }

        $html = <<< HTML
    <div class="wpss-showcase-prodcut-types-toolbar clearfix">
        <ul>
            {$html_terms}
        </ul>
    </div>
HTML;
        return $html;
    }

}

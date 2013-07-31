<?php
/**
 * Definizioni e azioni relativi ai meta box aggiungi nella finestra di edit/add di una vetrina
 *
 * @package            wpx SmartShop
 * @subpackage         WPSmartShopShowcaseMetaBox
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            06/03/12
 * @version            1.0.0
 *
 */

class WPSmartShopShowcaseMetaBox {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    public static function fieldsProducts() {

        $showcase = '';

        $fields = array(
            __( 'Products', WPXSMARTSHOP_TEXTDOMAIN )          => array(
                __( 'Select the Product and their order', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'     => WPDK_FORM_FIELD_TYPE_CUSTOM,
                        'callback' => array( __CLASS__, 'dropShowcase' )
                    )
                ),
                array(
                    array(
                        'type'     => WPDK_FORM_FIELD_TYPE_CUSTOM,
                        'callback' => array( __CLASS__, 'productPicker')
                    ),
                )
            ),

            __( 'Visibility', WPXSMARTSHOP_TEXTDOMAIN )          => array(
                __( 'Choose when this showcase is visible', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_DATETIME,
                        'name'    => 'wpss_showcase_date_start',
                        'label'   => __( 'Date start', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => $showcase['wpss_showcase_date_start']
                    ),
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_DATETIME,
                        'name'    => 'wpss_showcase_date_expiry',
                        'label'   => __( 'Expiry', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => $showcase['wpss_showcase_date_expiry']
                    ),
                )
            ),
        );

        return $fields;
    }

    /**
     * SDF fields per il meta box della Toolbar dei tipi prodotti
     *
     * @static
     * @param null $id_showcase
     * @retval array
     */
    public static function fieldsProductTypes() {
        global $post;

        $id_showcase = absint( $post->ID );

        $wpss_showcase_toolbar = get_post_meta( $id_showcase, 'wpss_showcase_toolbar', true );

        $fields = array(
            __( 'Properties', WPXSMARTSHOP_TEXTDOMAIN )          => array(
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_RADIO,
                        'name'    => 'wpss_showcase_toolbar',
                        'label'   => __( 'No display', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => 'no_display',
                        'checked' => empty($wpss_showcase_toolbar) ? 'no_display' : $wpss_showcase_toolbar
                    ),
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_RADIO,
                        'name'    => 'wpss_showcase_toolbar',
                        'label'   => __( 'Toolbar on top', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => 'on_top',
                        'checked' => $wpss_showcase_toolbar
                    ),
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_RADIO,
                        'name'    => 'wpss_showcase_toolbar',
                        'label'   => __( 'Toolbar after content', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => 'after_content',
                        'checked' => $wpss_showcase_toolbar

                    ),
                ),
                array(
                    array(
                        'type'     => WPDK_FORM_FIELD_TYPE_CUSTOM,
                        'callback' => array( __CLASS__, 'productTypes')
                    ),
                )
            ),
        );

        return $fields;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress Meta Boxes registration
    // -----------------------------------------------------------------------------------------------------------------

    public static function registerMetaBoxes() {
        add_meta_box( 'wpss-showcase-metabox-products-div', __( 'Showcase Management', WPXSMARTSHOP_TEXTDOMAIN ), array( __CLASS__, 'displayProducts' ), kWPSmartShopShowcasePostTypeKey, 'normal', 'high' );
        add_meta_box( 'wpss-showcase-metabox-product-types-div', __( 'Product Types Toolbar', WPXSMARTSHOP_TEXTDOMAIN ), array( __CLASS__, 'displayProductTypes' ), kWPSmartShopShowcasePostTypeKey, 'side', 'high' );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Save actions
    // -----------------------------------------------------------------------------------------------------------------

    public static function save( $post ) {
        $id_showcase = absint( $post->ID );

        /* Save products sequence */
        if ( isset( $_POST['wpss_showcase_products_sorter_sequence'] ) &&
            !empty( $_POST['wpss_showcase_products_sorter_sequence'] )
        ) {
            $showcase_sequence = $_POST['wpss_showcase_products_sorter_sequence'];
            update_post_meta( $id_showcase, 'wpss_showcase_products_sorter_sequence', $showcase_sequence );
        } else {
            delete_post_meta( $id_showcase, 'wpss_showcase_products_sorter_sequence' );
        }

        /* Save toolbar */
        if ( isset( $_POST['wpss_showcase_toolbar'] ) && $_POST['wpss_showcase_toolbar'] != 'no_display' ) {
            update_post_meta( $id_showcase, 'wpss_showcase_toolbar', esc_attr( $_POST['wpss_showcase_toolbar'] ) );
        } else {
            delete_post_meta( $id_showcase, 'wpss_showcase_toolbar' );
        }

        /* Save product types sequence */
        if ( isset( $_POST['wpss_showcase_product_types_term'] ) ) {
            $showcase_sequence = join( ',', $_POST['wpss_showcase_product_types_term'] );
            update_post_meta( $id_showcase, 'wpss_showcase_product_types_sorter_sequence', $showcase_sequence );
        } else {
            delete_post_meta( $id_showcase, 'wpss_showcase_product_types_sorter_sequence' );
        }

    }

    // -----------------------------------------------------------------------------------------------------------------
    // Meta Box Views
    // -----------------------------------------------------------------------------------------------------------------

    public static function displayProducts() {
        WPDKForm::nonceWithKey( 'showcase' );
        WPDKForm::htmlForm( self::fieldsProducts() );
    }

    public static function displayProductTypes() {
        WPDKForm::htmlForm( self::fieldsProductTypes() );
    }


    // -----------------------------------------------------------------------------------------------------------------
    // UI Aux
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Vetrina dopo vengono rilasciti i prodotti
     *
     * @static
     *
     */
    public static function dropShowcase() {
        global $post;

        $id_showcase       = absint( $post->ID );
        $showcase_sequence = get_post_meta( $id_showcase, 'wpss_showcase_products_sorter_sequence', true );
        $products          = WPSmartShopShowcase::products( $id_showcase );
        $message           = __( 'Drop your product here and build your showcase', WPXSMARTSHOP_TEXTDOMAIN );
        $html_products     = <<< HTML
     <li class="placeholder">{$message}</li>
HTML;
        if ( !empty( $products ) ) {
            $html_products = self::productsSorterSequence( $products );
        }

        ?>
    <div class="wpss-showcase-droppable-content">
        <input type="hidden"
               name="wpss_showcase_products_sorter_sequence"
               id="wpss_showcase_products_sorter_sequence"
               value="<?php echo $showcase_sequence ?>"/>
        <ul class="clearfix wpss-showcase-droppable" style="height: 200px">
            <?php echo $html_products ?>
        </ul>
    </div>
    <div class="wpss-showcase-droppable-trash"></div>
    <?php
    }

    /**
     * Genera i prodotti nella vetrina
     *
     * @static
     * @param $products Elenco prodotti memorizzato nei post meta
     * @retval string
     */
    private static function productsSorterSequence( $products ) {
        $html = '';
        foreach ( $products as $product ) {
            $id_product = $product->ID;
            $title      = $product->post_title;
            $thumbnail  = WPXSmartShopProduct::thumbnail( $id_product );

            $html .= <<< HTML
<li class="wpss-product-picker-trashable">
    <a href="#" class="clearfix" data-id_product="{$id_product}">
        {$thumbnail}
        <h4>{$title}</h4>
    </a>
</li>
HTML;
        }
        return $html;

    }

    /**
     * Callback SDF per la visualizzazione del Product picker statico
     *
     * @static
     *
     */
    public static function productPicker() {
        $args          = array(
            'sortable_product_type' => false,
            'draggable_product'     => true,
        );
        $productPicker = new WPSmartShopProductPicker( $args );
        $productPicker->display( 'showcase' );
    }


    /**
     * Visualizza (per SDF) del tipi prodotto con 'sorter'
     *
     * @note Per questo è stata utilizzata una tecnica diversa rispetto all'ordinamento dei prodotti. Per i prodotti
     * abbiamo una funziona Javascript che esegue un update della lista dei prodotti selezionati e il loro ordine, che
     * poi viene memorizzato in un campo input type hidden. Qui la situazione è più semplice in quando l'ordinamento
     * dei tag li contiene un input type checkbox con gli id dei termini. Questi, ordinati via jQuery, vengono postati
     * nell'ordine giusto, quindi è stato sufficente usare un join() per crera una strainga con l'elenco (e ordine)
     * degli id dei termini da memorizzare nei post meta.
     *
     * @static
     *
     */
    public static function productTypes() {
        global $post;

        $id_showcase            = absint( $post->ID );
        $product_types_sequence = get_post_meta( $id_showcase, 'wpss_showcase_product_types_sorter_sequence', true );
        $comps                  = explode( ',', $product_types_sequence );
        $result                 = WPSmartShopProductTypeTaxonomy::arrayTaxonomySorter();
        $selected               = '';
        $unselected             = '';

        foreach ( $comps as $comp ) {
            foreach ( $result as $key => $term ) {
                if ( $comp == $term['id'] ) {
                    unset( $result[$key] );
                    $name = $term['name'];
                    $id   = $term['id'];
                    $selected .= <<< HTML
    <li class="wpdk-li-sortable-select">
        <input type="checkbox" name="wpss_showcase_product_types_term[]" value="{$id}" checked="checked" />
        {$name}
    </li>
HTML;
                }
            }
        }

        foreach ( $result as $term ) {
            $name = $term['name'];
            $id   = $term['id'];
            $unselected .= <<< HTML
    <li>
        <input type="checkbox" name="wpss_showcase_product_types_term[]" value="{$id}" />
        {$name}
    </li>
HTML;
        }

        $html = <<< HTML
    <div class="wpss-showcase-product-type">
        <ul class="wpdk-ul-sortable">
            {$selected}
            {$unselected}
        </ul>
    </div>
HTML;

        echo $html;
    }

}

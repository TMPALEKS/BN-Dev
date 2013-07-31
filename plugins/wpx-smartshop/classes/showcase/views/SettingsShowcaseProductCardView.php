<?php
/**
 * Vista per la gestione delle impostazioni della scheda (card) prodotto
 *
 * @package            wpx SmartShop
 * @subpackage         SettingsShowcaseProductCardView
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            14/03/12
 * @version            1.0.0
 *
 * @todo Da fare
 *
 */

class SettingsShowcaseProductCardView extends WPDKSettingsView {
    
    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    function __construct() {
        $this->key          = 'product_card';
        $this->title        = __( 'Product Card', 'wp-xtreme' );
        $this->introduction = __( 'Please, write an introduction', 'wp-xtreme' );
        $this->settings     = WPXSmartShop::settings();
    }
    
    /**
     * Prepara l'array che descrive i campi del form
     *
     * @package            wpx SmartShop
     * @subpackage         SettingsShowcaseProductCardView
     * @since              1.0
     *
     * @static
     * @retval array
     */
    function fields() {

        $values = WPXSmartShop::settings()->product_card();

        $fields = array(
            __( 'Product card', WPXSMARTSHOP_TEXTDOMAIN )          => array(
                array(
                    array(
                        'type'     => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'     => 'thumbnail',
                        'label'    => __( 'Display thumbail', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'    => 'y',
                        'checked'  => $values ? $values['thumbnail'] : ''
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'      => 'thumbnail_size',
                        'label'     => __( 'Size', WPXSMARTSHOP_TEXTDOMAIN ),
                        'options'   => self::arrayImageSizesForSDF(),
                        'value'     => $values ? $values['thumbnail_size'] : ''
                    )
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'    => 'permalink',
                        'label'   => __( 'Do Link', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => 'y',
                        'checked' => $values ? $values['permalink'] : ''
                    ),
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'    => 'display_permalink_button',
                        'label'   => __( 'Display Link as button', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => 'y',
                        'checked' => $values ? $values['display_permalink_button'] : ''
                    ),
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'    => 'price',
                        'label'   => __( 'Display price', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => 'y',
                        'checked' => $values ? $values['price'] : ''
                    ),
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'    => 'excerpt',
                        'id'      => '_excerpt',
                        'label'   => __( 'Display excerpt', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => 'y',
                        'checked' => $values ? $values['excerpt'] : ''
                    ),
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'    => 'display_add_to_cart',
                        'label'   => __( 'Display add to Shopping Cart button', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => 'y',
                        'checked' => $values ? $values['display_add_to_cart'] : ''
                    ),
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'    => 'product_types',
                        'label'   => __( 'Display Product Types', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => 'y',
                        'checked' => $values ? $values['product_types'] : ''
                    ),
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'    => 'product_types_tree',
                        'label'   => __( 'Display Product Types Tree', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => 'y',
                        'checked' => $values ? $values['product_types_tree'] : ''
                    ),
                ),
//                array(
//                    array(
//                        'type'     => WPDK_FORM_FIELD_TYPE_CUSTOM,
//                        'callback' => array(__CLASS__, 'preview')
//                    )
//                )
            ),
        );
        return $fields;
    }

    /**
     * Restituiscie un array nello standard SDF per mostrare il combo di selezione delle immagini
     *
     * @package            wpx SmartShop
     * @subpackage         SettingsShowcaseProductCardView
     * @since              1.0
     *
     * @static
     * @retval array
     */
    function arrayImageSizesForSDF() {
        $sizes  = WPXSmartShopProduct::imageSizes();
        $result = array();
        foreach ( $sizes as $key => $size ) {
            $result[$key] = sprintf( '%sx%s', $size['width'], $size['height'] );
        }
        return $result;
    }


    /* @todo Perché vedo i/il prodotti inglesi? */
    function preview() {

        $args  = array(
            'post_status' => 'publish',
            'numberposts' => 5,
            'post_type'   => WPXSMARTSHOP_PRODUCT_POST_KEY,
        );
        $posts = get_posts( $args );
        $product = $posts[0];

        $preview = WPXSmartShopProduct::card($product);

        $html = <<< HTML
    <div class="wpss-showcase-product-card-preview">
        {$preview}
    </div>
HTML;
    echo $html;
    }
    
    
    // -----------------------------------------------------------------------------------------------------------------
    // Settings actions
    // -----------------------------------------------------------------------------------------------------------------

    /**
     *
     * @todo Aggiungere operatore ternario
     */
    function save() {
        $values = array(
            'thumbnail'                => isset( $_POST['thumbnail'] ) ? $_POST['thumbnail'] : '',
            'thumbnail_size'           => $_POST['thumbnail_size'],
            'permalink'                => isset( $_POST['permalink'] ) ? $_POST['permalink'] : '',
            'display_permalink_button' => $_POST['display_permalink_button'],
            'price'                    => $_POST['price'],
            'excerpt'                  => $_POST['excerpt'],
            'display_add_to_cart'      => $_POST['display_add_to_cart'],
            'product_types'            => $_POST['product_types'],
            'product_types_tree'       => $_POST['product_types_tree'],
        );

        WPXSmartShop::settings()->product_card( $values );
    }

}

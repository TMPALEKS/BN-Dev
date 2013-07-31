<?php
/**
 * Vista per la gestione dell'integrazione con WordPress
 *
 * @package       wpx SmartShop
 * @subpackage    SettingsIntegrationView
 * @author        =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright     Copyright (c)2011 wpXtreme, Inc.
 * @created       18/11/11
 * @version       1.0
 *
 */

class SettingsIntegrationView extends WPDKSettingsView {
    
    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    function __construct() {
        $this->key          = 'wp_integration';
        $this->title        = __( 'WordPress Integration', WPXSMARTSHOP_TEXTDOMAIN );
        $this->introduction = __( 'Please, write an introduction', WPXSMARTSHOP_TEXTDOMAIN );
        $this->settings     = WPXSmartShop::settings();
    }


    /**
     * Prepara l'array che descrive i campi del form
     *
     * @static
     * @retval array
     */
    function fields() {

        $values =  WPXSmartShop::settings()->wp_integration();

        $fields = array(
            __( 'Services Pages URLs', WPXSMARTSHOP_TEXTDOMAIN )  => array(
                __( 'Smart Shop needs some page for internal use, like: checkout processing, store view, etc...', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'          => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'          => 'wpssCheckoutPermalink',
                        'label'         => __( 'Checkout', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'          => 64,
                        'value'         => $values ? $values['checkout_permalink'] : '',
                        'help'          => __( 'Checkout page slug name', WPXSMARTSHOP_TEXTDOMAIN )
                    )
                ),
                array(
                    array(
                        'type'          => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'          => 'payment_permalink',
                        'label'         => __( 'Payment', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'          => 64,
                        'value'         => $values ? $values['payment_permalink'] : '',
                        'help'          => __( 'Payment page slug name', WPXSMARTSHOP_TEXTDOMAIN )
                    )
                ),
                array(
                    array(
                        'type'          => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'          => 'receipt_permalink',
                        'label'         => __( 'Receipt', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'          => 64,
                        'value'         => $values ? $values['receipt_permalink'] : '',
                        'help'          => __( 'Receipt page slug name', WPXSMARTSHOP_TEXTDOMAIN )
                    )
                ),
                array(
                    array(
                        'type'          => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'          => 'error_permalink',
                        'label'         => __( 'Error', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'          => 64,
                        'value'         => $values ? $values['error_permalink'] : '',
                        'help'          => __( 'Error page slug name', WPXSMARTSHOP_TEXTDOMAIN )
                    )
                ),


                array(
                    array(
                        'type'          => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'          => 'wpssStorePermalink',
                        'label'         => __( 'Store', WPXSMARTSHOP_TEXTDOMAIN ),
                        'placeholder'   => 'http://',
                        'size'          => 64,
                        'value'         => $values ? $values['store_permalink'] : '',
                        'help'          => __( 'Store URL', WPXSMARTSHOP_TEXTDOMAIN )
                    )
                )
            ),
            __( 'Shopping Cart', WPXSMARTSHOP_TEXTDOMAIN ) => array(
                array(
                    array(
                        'type'           => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'           => 'shopping_cart_display_for_user_logon_only',
                        'label'          => __( 'Show only for user logon', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'          => 'y',
                        'checked'        => $values ? $values['shopping_cart_display_for_user_logon_only'] : ''
                    ),
                ),
                array(
                    array(
                        'type'           => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'           => 'shopping_cart_display_empty_button',
                        'label'          => __( 'Display empty button', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'          => 'y',
                        'checked'        => $values ? $values['shopping_cart_display_empty_button'] : ''
                    ),
                ),
            ),
            __( 'Product Picker', WPXSMARTSHOP_TEXTDOMAIN ) => array(
                __( 'Product Picker tools will display in new/edit page for this Post Type', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    'group'    => array(
                        array(
                            'type'     => WPDK_FORM_FIELD_TYPE_CUSTOM,
                            'name'     => 'product_picker_post_types',
                            'callback' => array( $this, 'postType' ),
                        )
                    ),
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'      => 'product_picker_hide_empty_product_type',
                        'label'     => __( 'Hide Product Type (category) when no product linked', WPXSMARTSHOP_TEXTDOMAIN ),
                        'checked'   => $values ? $values['product_picker_hide_empty_product_type'] : '',
                        'value'     => 'y',
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'  => 'product_picker_number_of_items',
                        'label' => __( 'Items number', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => $values ? $values['product_picker_number_of_items'] : '',
                    )
                )
            ),
        );
        return $fields;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Settings actions
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @static
     */
    function save() {

        $values = array(
            'checkout_permalink'=> esc_attr( $_POST['wpssCheckoutPermalink'] ),
            'payment_permalink' => esc_attr( $_POST['payment_permalink'] ),
            'receipt_permalink' => esc_attr( $_POST['receipt_permalink'] ),
            'error_permalink'   => esc_attr( $_POST['error_permalink'] ),

            'store_permalink'                            => esc_attr( $_POST['wpssStorePermalink'] ),

            'shopping_cart_display_for_user_logon_only'  => isset( $_POST['shopping_cart_display_for_user_logon_only'] ) ? $_POST['shopping_cart_display_for_user_logon_only'] : '',
            'shopping_cart_display_empty_button'         => isset( $_POST['shopping_cart_display_empty_button'] ) ? $_POST['shopping_cart_display_empty_button'] : '',

            'product_picker_post_types'                  => isset( $_POST['product_picker_post_types'] ) ? $_POST['product_picker_post_types'] : '',
            'product_picker_hide_empty_product_type'     => isset( $_POST['product_picker_hide_empty_product_type'] ) ? $_POST['product_picker_hide_empty_product_type'] : '',
            'product_picker_number_of_items'             => esc_attr( $_POST['product_picker_number_of_items'] ),

        );

        WPXSmartShop::settings()->wp_integration( $values );

    }

    // -----------------------------------------------------------------------------------------------------------------
    // Callback
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Callback usata per costruire l'elenco dei Post type
     *
     * @static
     *
     * @param $item
     */
    function postType( $item ) {

        /* get relevant post types */
        $post_types = get_post_types( array( 'public' => true, ) );

        /* Exclude WordPress internal post type */
        $remove = array_search( 'attachment', $post_types );
        if ( $remove ) {
            unset( $post_types[$remove] );
        }

        /* Exclude Myself SmartShop product */
        $remove = array_search( 'wpss-cpt-product', $post_types );
        if ( $remove ) {
            unset( $post_types[$remove] );
        }
        $remove = array_search( 'wpss-store-page', $post_types );
        if ( $remove ) {
            unset( $post_types[$remove] );
        }
        $remove = array_search( 'wpss-showcase-page', $post_types );
        if ( $remove ) {
            unset( $post_types[$remove] );
        }

        $values =  WPXSmartShop::settings()->wp_integration();

        foreach ( $post_types as $key => $post_type ) : $obj = get_post_type_object($key); ?>

        <input <?php wpdk_checked( $values['product_picker_post_types'], $key ) ?>
            name="product_picker_post_types[]" type="checkbox" value="<?php echo $key ?>"/>
        <label><strong><?php echo $obj->label ?></strong> (<?php  echo $post_type ?>)</label><br/>
        <?php endforeach;
    }

}
<?php
/**
 * @class              WPXSmartShopProductMetaBox
 * @description        Definizioni e azioni relativi ai meta box aggiungi nella finestra di edit/add di un prodotto
 *
 * @package            wpx SmartShop
 * @subpackage         products
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            09/02/12
 * @version            1.0.0
 *
 * @code
 * CUSTOM FIELDS
 * =============
 *
 * wpss_product_base_price
 * wpss_product_price_for_rules - array() con keys
 *
 * wpss_product_store_quantity
 * wpss_product_available_from_date
 * wpss_product_available_to_date
 * wpss_product_sku
 *
 * APPEARANCE & VARIANTS
 * =====================
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
 * SHIPPING
 * ========
 *
 * wpss_product_is_shipping - flag 1 | 0
 * wpss_product_is_shipping-with-carrier (array) - anche se adesso c'è un solo id corriere
 * wpss-product-is-not-shipping-for-zone - array() - anche se adesso c'è un solo id zona *
 *
 * @todo Mancano da gestire le ultime due key dello shipping di qui sopra
 *
 *
 * MEMBERSHIP
 * ==========
 *
 * wpss_product_membership_rules
 *
 *
 * COUPON
 * ======
 *
 * wpss_product_coupon_rules
 *
 * @endcode
 *
 */

class WPXSmartShopProductMetaBox {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce un array speciale che indica quali schede e quali view mostrare nei meta box prodotto.
     * La prima chiave è un id, dove poi seguono l'etichetta del tab e la classe (anche nome file) della view
     *
     * @static
     * @retval array
     */
    private static function tabs() {
        $tabs = array(
            'tab-price'     => array(
                'title' => __( 'Base price and rules', WPXSMARTSHOP_TEXTDOMAIN ),
                'view'  => 'viewPriceManager'
            ),

            'tab-purchasable'=> array(
                'title' => __( 'Purchasable', WPXSMARTSHOP_TEXTDOMAIN ),
                'view'  => 'viewPurchasable'
            ),

            'tab-appearance'=> array(
                'title' => __( 'Variants', WPXSMARTSHOP_TEXTDOMAIN ),
                'view'  => 'viewAppearance'
            ),

            'tab-shipping'  => array(
                'title' => __( 'Shipping', WPXSMARTSHOP_TEXTDOMAIN ),
                'view'  => 'viewShipping'
            ),

            'tab-warehouse' => array(
                'title' => __( 'Stocks', WPXSMARTSHOP_TEXTDOMAIN ),
                'view'  => 'viewWarehouse'
            ),
            'tab-membership'=> array(
                'title' => __( 'Membership', WPXSMARTSHOP_TEXTDOMAIN ),
                'view'  => 'viewMembership'
            ),
            'tab-coupons'   => array(
                'title' => __( 'Coupons', WPXSMARTSHOP_TEXTDOMAIN ),
                'view'  => 'viewCoupon'
            ),

            'tab-digital'   => array(
                'title' => __( 'Digital Product', WPXSMARTSHOP_TEXTDOMAIN ),
                'view'  => 'viewDigitalProduct'
            ),

        );
        return $tabs;
    }

    /* Prices */
    public static function fieldsPriceManager() {
        global $post;

        if ( WPXSmartShop::settings()->product_price_includes_vat() ) {
            $append = sprintf( __( 'From the <a href="%s">Settings</a> you have chosen <strong>to include VAT</strong> in the price.', WPXSMARTSHOP_TEXTDOMAIN ), '/wp-admin/edit.php?post_type=wpss-cpt-product&page=menuItemSettings'  );
        } else {
            $append = sprintf( __( 'From the <a href="%s">Settings</a> you have chosen <strong>not to include VAT</strong> in the price.', WPXSMARTSHOP_TEXTDOMAIN ), '/wp-admin/edit.php?post_type=wpss-cpt-product&page=menuItemSettings'  );
        }

        $fields = array(
            __( 'Price Manager', WPXSMARTSHOP_TEXTDOMAIN ) => array(
                __( 'Create your own list Price/Rule. You can add a Price for a certain category of users or for anyone and discount', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'  => 'wpss_product_base_price',
                        'value' => WPXSmartShopCurrency::formatCurrency( isset( $post ) ? get_post_meta( $post->ID, 'wpss_product_base_price', true ) : 0, true ),
                        'size'  => 16,
                        'label' => __( 'Base Price', WPXSMARTSHOP_TEXTDOMAIN ),
                        'help'  => __( 'This is the main-base price for a product. Select more rules/prices below.', WPXSMARTSHOP_TEXTDOMAIN ),
                        'append' => $append
                    )
                ),
            )
        );

        return $fields;
    }

    /**
     * Riga dinamica per aggiungere le regole:
     *
     * @static
     *
     * @retval array
     *
     */
    public static function columnsPriceRule() {
        $item = self::emptyPriceRule();

        $columns = array(
                    'wpss-product-rule-id'    => array(
                        'table_title'   => __( 'Price rules', WPXSMARTSHOP_TEXTDOMAIN ),
                        'type'          => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'          => 'wpss-product-rule-id[]',
                        'class'         => 'wpss-product-rule-id',
                        'data'          => array( 'placement' => 'left' ),
                        'title'         => __( 'Select a price rule', WPXSMARTSHOP_TEXTDOMAIN ),
                        'options'       => self::priceRules(),
                        'value'         => $item['wpss-product-rule-id'],
                    ),

                    'date_from'                => array(
                        'table_title'   => __( 'From date', WPXSMARTSHOP_TEXTDOMAIN ),
                        'id' => '',
                        'type'          => WPDK_FORM_FIELD_TYPE_DATETIME,
                        'name'          => 'wpss-product-rule-date_from[]',
                        'value'         => WPDKDateTime::formatFromFormat( $item['date_from'], 'Y-m-d H:i:s', __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) ),
                    ),

                    'date_to'              => array(
                        'table_title'   => __( 'To date', WPXSMARTSHOP_TEXTDOMAIN ),
                        'id' => '',
                        'type'          => WPDK_FORM_FIELD_TYPE_DATETIME,
                        'name'          => 'wpss-product-rule-date_to[]',
                        'value'         => WPDKDateTime::formatFromFormat( $item['date_to'], 'Y-m-d H:i:s', __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) ),
                    ),

                    'price'                    => array(
                        'table_title'   => __( 'Price', WPXSMARTSHOP_TEXTDOMAIN ) . WPXSmartShopCurrency::currencySymbol(),
                        'type'          => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'          => 'wpss-product-rule-price[]',
                        'class'         => 'wpss-product-rule-price',
                        'value'         => WPXSmartShopCurrency::formatCurrency( $item['price'], true ),
                    ),

                    'percentage'               => array(
                        'table_title'   => __( 'Percentage %', WPXSMARTSHOP_TEXTDOMAIN ),
                        'type'          => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'          => 'wpss-product-rule-percentage[]',
                        'size'          => 8,
                        'class'         => 'wpss-product-rule-percentage',
                        'value'         => WPXSmartShopCurrency::formatPercentage( $item['percentage'], true ),
                    ),

                    'qty'                      => array(
                        'table_title'   => __( 'For order', WPXSMARTSHOP_TEXTDOMAIN ),
                        'type'          => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'          => 'wpss-product-rule-qty[]',
                        'data'          => array( 'placement' => 'left' ),
                        'title'         => __( 'This is the max number of this products to buy for a single Order', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'          => 4,
                        'value'         => $item['qty'],
                    ),

                    'abs_qty'                      => array(
                        'table_title'   => __( 'For product', WPXSMARTSHOP_TEXTDOMAIN ),
                        'type'          => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'data'          => array( 'placement' => 'left' ),
                        'title'         => __( 'This is the max number of this product that it possible to buy for a user', WPXSMARTSHOP_TEXTDOMAIN ),
                        'name'          => 'wpss-product-rule-abs-qty[]',
                        'size'          => 4,
                        'value'         => $item['abs_qty'],
                    ),

                );

        return $columns;
    }

    /**
      * Costruisce l'array di regola da passare al campo select
      *
      * @static
      * @retval array
      */
    public static function priceRules() {
         global $wp_roles;

         $result = array(
             ''                                         => __( 'Select a rules', WPXSMARTSHOP_TEXTDOMAIN ),
             __( 'Common', WPXSMARTSHOP_TEXTDOMAIN )             => array(
                 kWPSmartShopProductTypeRuleOnlinePrice   => __( 'Online', WPXSMARTSHOP_TEXTDOMAIN ),
                 kWPSmartShopProductTypeRuleDatePrice     => __( 'Date range', WPXSMARTSHOP_TEXTDOMAIN )
             )
         );
         $roles = $wp_roles->get_names();
         foreach ($roles as $key => $role) {
             $result[__('User roles', WPXSMARTSHOP_TEXTDOMAIN )][$key] = $role;
         }
         return $result;
     }

    /**
     * Restituisce una regola vuota
     *
     * @static
     *
     * @retval array
     */
    private static function emptyPriceRule() {
        $item = array(
            'wpss-product-rule-id'       => '',
            'date_from'                  => '',
            'date_to'                    => '',
            'price'                      => 0,
            'percentage'                 => 0,
            'qty'                        => '',
            'abs_qty'                    => '',
        );
        return $item;
    }


    /* Purchasable */
    public static function fieldsPurchasable() {
        global $post;

		/* Recupero tutti i ruoli di WordPress e aggiungo un "non selezionato" all'inizio */
		$wpRoles = WPDKUser::allRoles();
		$wpRoles = array_merge( array( '' => __( 'None', WPXSMARTSHOP_TEXTDOMAIN ) ), $wpRoles );    
        
        /**
         * Filtro sule capabilities
         * 
         * @todo Da rinominare in wpxss_product_purchasable_capabilities_list
         *
         * @filters
         *
         * @param array $caps Array con la lista delle capabilities disponibili in WordPress,
         *                    scorrendo tutti i ruoli presenti ed estraendo le capabilities.
         */
        $allCapabilities = apply_filters( 'wpss_product_membership_capabilities_list', WPDKUser::allCapabilities() );

        /* Se sono in edit, recupero le capabilities selezionate dai custom meta */
        if ( isset( $post ) ) {
			$wpxss_product_purchasable = get_post_meta( $post->ID, 'wpxss_product_purchasable', true );
		}

		$index = 0;
        foreach ( $allCapabilities as $key => $cap ) {
            $wpCapabilities[] = array(
                'type'      => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                'walker'    => false,
                'name'      => 'wpxss_product_purchasable_capabilities[]',
                'label'     => $cap,
                'value'     => $key,
                'append'    => ( $index++ % 2 ) ? '<br/>' : '',
                'checked'   => isset( $wpxss_product_purchasable['capabilities'] ) ? ( in_array( $key, $wpxss_product_purchasable['capabilities'] ) ? $key : '' ) : ''
            );
		}

		$wpCapabilities = array(
			'group' => $wpCapabilities,
			'class' => 'wpss-membership-capabilities-box'
		);

        /* Questo è il legend che viene reso invisibile nelle tabs jQuery in quanto inutile */
        $fields = array(
            __( 'Purchasable management', WPXSMARTSHOP_TEXTDOMAIN ) => array(
                __( 'Allow only at the user\'s role and capabilities below to buy this product.', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_SELECT,
                        'walker'    => false,
                        'name'      => 'wpxss_product_purchasable_role',
                        'label'     => __( 'Purchasable only by this Role', WPXSMARTSHOP_TEXTDOMAIN ),
                        'options'   => $wpRoles,
                        'value'     => isset( $wpxss_product_purchasable['roles'] ) ? $wpxss_product_purchasable['roles'][0] : '',
                    ),
                ),
                $wpCapabilities,
            )
        );

        return $fields;
    }

    /* Appearances */
    public static function fieldsAppearance() {
        $fields = array(

            __( 'Appearance & variants', WPXSMARTSHOP_TEXTDOMAIN )    => array(
                __( 'Please describe the appearance of the product and its variants.', WPXSMARTSHOP_TEXTDOMAIN )
            ),

        );

        return $fields;
    }

    public static function fieldsAppearanceRule( $item, $key = '' ) {
        $fields = array();

        /* Bottone riga */
        $fields[] = array(
            array(
                'type'   => WPDK_FORM_FIELD_TYPE_TEXT,
                'name'   => 'wpss-product-appearance_id_name[]',
                'label'  => __( 'Name/ID', WPXSMARTSHOP_TEXTDOMAIN ),
                'size'   => 32,
                'value'  => $key,
                'help'   => __( 'This is the unique Name/ID identifier of this aspect/variant. Eg. Red version, Lux model, etc...', WPXSMARTSHOP_TEXTDOMAIN )
            ),
            array(
                'type'      => WPDK_FORM_FIELD_TYPE_CUSTOM,
                'item'      => $item,
                'callback'  => array( __CLASS__, 'buttonAppearanceRule' )
            )
        );

        /* Prima riga */
        $fields[] = array(
            array(
                'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                'name'   => 'wpss-product-appearance-weight[]',
                'label'  => __( 'Weight', WPXSMARTSHOP_TEXTDOMAIN ),
                'size'   => 32,
                'value'  => $item['weight'],
                'append' => WPXSmartShopMeasures::weightSymbol()
            ),
            array(
                'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                'name'   => 'wpss-product-appearance-volume[]',
                'label'  => __( 'Volume', WPXSMARTSHOP_TEXTDOMAIN ),
                'size'   => 32,
                'value'  => $item['volume'],
                'append' => WPXSmartShopMeasures::volumeSymbol()
            )
        );

        $fields[] = array(
            array(
                'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                'name'   => 'wpss-product-appearance-width[]',
                'label'  => __( 'Width', WPXSMARTSHOP_TEXTDOMAIN ),
                'size'   => 16,
                'value'  => $item['width'],
                'append' => WPXSmartShopMeasures::sizeSymbol()
            ),
            array(
                'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                'name'   => 'wpss-product-appearance-height[]',
                'label'  => __( 'Height', WPXSMARTSHOP_TEXTDOMAIN ),
                'size'   => 16,
                'value'  => $item['height'],
                'append' => WPXSmartShopMeasures::sizeSymbol()
            ),
            array(
                'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                'name'   => 'wpss-product-appearance-depth[]',
                'label'  => __( 'Depth', WPXSMARTSHOP_TEXTDOMAIN ),
                'size'   => 16,
                'value'  => $item['depth'],
                'append' => WPXSmartShopMeasures::sizeSymbol()
            ),

        );

        /* Seconda riga */
        $fields[] = array(
            array(
                'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                'name'  => 'wpss-product-appearance-color[]',
                'label' => __( 'Color', WPXSMARTSHOP_TEXTDOMAIN ),
                'size'  => 48,
                'value' => $item['color']
            )
        );
        $fields[] = array(
            array(
                'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                'name'  => 'wpss-product-appearance-material[]',
                'label' => __( 'Material', WPXSMARTSHOP_TEXTDOMAIN ),
                'size'  => 48,
                'value' => $item['material']
            )
        );
        $fields[] = array(
            array(
                'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                'name'  => 'wpss-product-appearance-model[]',
                'label' => __( 'Model', WPXSMARTSHOP_TEXTDOMAIN ),
                'size'  => 48,
                'value' => $item['model']
            )
        );

        /* Terza riga */
        $fields[] = array(
            array(
                'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                'name'   => 'wpss-product-appearance-value[]',
                'label'  => __( 'Value', WPXSMARTSHOP_TEXTDOMAIN ),
                'size'   => 6,
                'value'  => $item['value'],
                'append' => WPXSmartShopCurrency::currencySymbol() . '/%',
                'help'   => __( 'Enter only a number for currency. Else enter a number + "%" for percentage',
                    WPXSMARTSHOP_TEXTDOMAIN )
            ),
            array(
                'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                'name'  => 'wpss-product-appearance-note[]',
                'label' => __( 'Note', WPXSMARTSHOP_TEXTDOMAIN ),
                'size'  => 64,
                'value' => $item['note']
            ),

        );
        return $fields;
    }

    public static function buttonAppearanceRule( $item ) {

        if ( !self::isEmptyAppearanceRule( $item['item'] ) ) : ?>
        <input class="wpss-product-appearance-delete wpss-product-delete-row wpdk-form-button"
               type="button"
               value="<?php _e( 'Delete', WPXSMARTSHOP_TEXTDOMAIN ) ?>"/>
        <?php else: ?>
        <input class="wpss-product-appearance-add wpss-product-add-row wpdk-form-button"
               type="button"
               value="<?php _e( 'Add', WPXSMARTSHOP_TEXTDOMAIN ) ?>"/>
        <?php endif;
    }

    /**
     * Restituisce una regola vuota
     *
     * @static
     *
     * @retval array
     */
    private static function emptyAppearanceRule() {
        $item = array(
            'weight'    => '',
            'width'     => '',
            'height'    => '',
            'depth'     => '',
            'volume'    => '',

            'color'     => '',
            'material'  => '',
            'model'     => '',
            'note'      => '',
            'value'     => '',
        );
        return $item;
    }

    /**
     * Controlla se l'array $item contiene stringhe vuote o valori a zero
     *
     * @static
     * @param $item
     * @retval bool True se contiene stringhe vuote o a zero
     */
    public static function isEmptyAppearanceRule( $item ) {
        $result = true;
        foreach ( $item as $value ) {
            if ( !empty( $value ) ) {
                $result = false;
                break;
            }
        }
        return $result;
    }


    /* Shipping */
    public static function fieldsShipping() {
        global $post;

        $fields = array(
            __( 'Shipping', WPXSMARTSHOP_TEXTDOMAIN ) => array(
                __( 'Turn on for shipping this product', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'    => 'wpss_product_is_shipping',
                        'label'   => __( 'Is shipping', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => '1',
                        'checked' => WPXSmartShopProduct::shipping( $post->ID )
                    )
                )
            )
        );

        return $fields;
    }

    /* Warehouse */
    public static function fieldsWarehouse() {
        global $post;

        $fields = array(
            __( 'Warehouse Management', WPXSMARTSHOP_TEXTDOMAIN )    => array(
                __( 'Set stocks number and product\'s available rules. Furthermore if you set date start and date end, you can make available a product for a date range.', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'      => 'wpss_product_store_quantity',
                        'label'     => __( 'Store Quantity', WPXSMARTSHOP_TEXTDOMAIN ),
                        'help'      => __( 'Quantity available in store. Left to zero for unlimited stocks. When quantity is zero this product will not available for purchase. Furthermore if you set date start and date end, you can make available a product for date range.', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'     => isset( $post ) ? get_post_meta( $post->ID, 'wpss_product_store_quantity', true ) : '',
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'      => 'wpss_product_store_quantity_for_order_confirmed',
                        'label'     => __( 'Quantity for Confirmed', WPXSMARTSHOP_TEXTDOMAIN ),
                        'locked'    => true,
                        'size'      => 3,
                        'value'     => isset( $post ) ? get_post_meta( $post->ID, 'wpss_product_store_quantity_for_order_confirmed', true ) : '',
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'      => 'wpss_product_store_quantity_for_order_pending',
                        'label'     => __( 'Pending', WPXSMARTSHOP_TEXTDOMAIN ),
                        'locked'    => true,
                        'size'      => 3,
                        'value'     => isset( $post ) ? get_post_meta( $post->ID, 'wpss_product_store_quantity_for_order_pending', true ) : '',
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'      => 'wpss_product_store_quantity_for_order_cancelled',
                        'label'     => __( 'Cancelled', WPXSMARTSHOP_TEXTDOMAIN ),
                        'locked'    => true,
                        'size'      => 3,
                        'value'     => isset( $post ) ? get_post_meta( $post->ID, 'wpss_product_store_quantity_for_order_cancelled', true ) : '',
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'      => 'wpss_product_store_quantity_for_order_defunct',
                        'label'     => __( 'Expired', WPXSMARTSHOP_TEXTDOMAIN ),
                        'locked'    => true,
                        'size'      => 3,
                        'value'     => isset( $post ) ? get_post_meta( $post->ID, 'wpss_product_store_quantity_for_order_defunct', true ) : '',
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_DATETIME,
                        'name'      => 'wpss_product_available_from_date',
                        'label'     => __( 'Available from date', WPXSMARTSHOP_TEXTDOMAIN ),
                        'help'      => __( 'Left blank for make available from now', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'     => isset( $post ) ? WPDKDateTime::formatFromFormat( get_post_meta( $post->ID, 'wpss_product_available_from_date', true ), 'Y-m-d H:i:s', __('m/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) ) : ''
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_DATETIME,
                        'name'      => 'wpss_product_available_to_date',
                        'label'     => __( 'to date', WPXSMARTSHOP_TEXTDOMAIN ),
                        'help'      => __( 'Left blank for make available forever', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'     => isset( $post ) ? WPDKDateTime::formatFromFormat( get_post_meta( $post->ID, 'wpss_product_available_to_date', true ), 'Y-m-d H:i:s', __('m/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) ) : ''
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'      => 'wpss_product_sku',
                        'label'     => __( 'Stock Keeping Unit ID', WPXSMARTSHOP_TEXTDOMAIN ),
                        'help'      => __( "Is a unique numerical identifying number that refers to a specific stock item in a retailer's inventory or product catalog. The SKU is often used to identify the product, product size or type, and the manufacturer. In the retail industry, the SKU is a part of the backend inventory control system and enables a retailer to track a product in their inventory that may be in warehouses or in retail outlets.", WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'     => isset( $post ) ? get_post_meta( $post->ID, 'wpss_product_sku', true ) : '',
                    )
                )
            ),
        );

        return $fields;
    }

    /* Membership */
    public static function fieldsMembership() {

        /* Questo è il legend che viene reso invisibile nelle tabs jQuery in quanto inutile */
        $fields = array(
            __( 'Membership Management', WPXSMARTSHOP_TEXTDOMAIN ) => WPSmartShopProductMembership::fields(),
        );

        return $fields;
    }

    /* Coupon */
    public static function fieldsCoupon() {

        /* Questo è il legend che viene reso invisibile nelle tabs jQuery in quanto inutile */
        $fields = array(
            __( 'Coupon management', WPXSMARTSHOP_TEXTDOMAIN ) => WPSmartShopProductCoupon::fields()

        );

        return $fields;
    }

    /* Digital Product */
    public static function fieldsDigitalProduct() {
        global $post;

        $size = '';
        if( isset( $post ) ) {
            $filename = get_post_meta( $post->ID, 'wpss_product_digital_url', true );
            if( !empty( $filename ) ) {
                $size = WPDKFilesystemHelper::fileSize( $filename );
            }
        }

        /* Questo è il legend che viene reso invisibile nelle tabs jQuery in quanto inutile */
        $fields = array(
            __( 'Digital Product', WPXSMARTSHOP_TEXTDOMAIN ) => array(
                __( 'Set media digital product information.', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'      => 'wpss_product_digital_url',
                        'label'     => __( 'URL', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'      => 64,
                        'help'      => __( 'This is the real (unwatchable) url of the digital product.', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'     => isset( $post ) ? get_post_meta( $post->ID, 'wpss_product_digital_url', true ) : '',
                        'append'    => $size
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'      => 'wpss_product_digital_version',
                        'label'     => __( 'Version', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'      => 8,
                        'help'      => __( 'This is the version number, usaful for upgrade management. Eg. 1.4', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'     => isset( $post ) ? get_post_meta( $post->ID, 'wpss_product_digital_version', true ) : '',
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'      => 'wpss_product_digital_download_count',
                        'label'     => __( 'Download Count', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'      => 8,
                        'locked'    => true,
                        'value'     => isset( $post ) ? get_post_meta( $post->ID, 'wpss_product_digital_download_count', true ) : '',
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'      => 'wpss_product_digital_languages',
                        'label'     => __( 'Languages', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'      => 48,
                        'title'     => __( 'Insert available language comma separated.', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'     => isset( $post ) ? get_post_meta( $post->ID, 'wpss_product_digital_languages', true ) : '',
                    )
                ),
            )

        );

        return $fields;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress Meta Boxes registration
    // -----------------------------------------------------------------------------------------------------------------

    public static function registerMetaBoxes() {
        /* Il prezzo non è disabilitabile */
        add_meta_box( WPXSMARTSHOP_PRODUCT_POST_KEY . '-div', __('Smart Shop Product Settings', WPXSMARTSHOP_TEXTDOMAIN ), array( __CLASS__, 'displayMetaBox' ), WPXSMARTSHOP_PRODUCT_POST_KEY, 'normal', 'high');
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Meta Box Views
    // -----------------------------------------------------------------------------------------------------------------

    public static function displayMetaBox() {
        /* Array dei meta boxes */
        $enabled_tabs = WPXSmartShop::settings()->products();
        $tabs         = self::tabs();

        /* In base alle impostazioni unsetto le tabs che non devo rendere visibili. */
        foreach ( $tabs as $key => $tab ) {
            if ( !wpdk_is_bool( $enabled_tabs[$key] ) ) {
                unset( $tabs[$key] );
            }
        }
        ?>

    <div class="wpdk-jquery-ui">
        <?php
        $jquery_tabs = new WPDKjQueryTabs( 'wpss-product' );
        foreach ( $tabs as $key => $tab ) {
            $method = $tab['view'];

            /* @todo Fa parte della grossa patch da fare in WPDKForm */
            ob_start();
            self::$method();
            $content = ob_get_contents();
            ob_end_clean();

            $jquery_tabs->add( $key, $tab['title'], $content );
        }
        $jquery_tabs->display();
        ?>
    </div>

    <?php
    }

    /* Questo pannello c'è sempre */
    public static function viewPriceManager() {
        global $post;

        /* Tutto il blocco */
        $fields = self::fieldsPriceManager();

        /* Nonce key per controllo */
        WPDKForm::nonceWithKey( 'product' );
        WPDKForm::htmlForm( $fields );

        $columns = self::columnsPriceRule();
        $items   = array();

        if ( is_object( $post ) && !empty( $post->ID ) ) {
            $items = unserialize( get_post_meta( $post->ID, 'wpss_product_price_for_rules', true ) );
            $items = WPXSmartShopProduct::sanitizePriceRules( $items );
        }

        $table = new WPDKDynamicTable( 'wpss-dynamic-table-price-rules', $columns, $items );

        echo $table->view();
    }

    public static function viewPurchasable() {
        /* Tutto il blocco */
        $fields = self::fieldsPurchasable();

        WPDKForm::htmlForm( $fields );
    }

    public static function viewAppearance() {
        /* Tutto il blocco */
        $fields = self::fieldsAppearance();

        /* Aggiungo le righe per le varianti/aspetto */
        $fields = self::addFieldsAppearance( $fields );

        WPDKForm::htmlForm( $fields );

    }

    public static function viewShipping() {
        /* Tutto il blocco */
        $fields = self::fieldsShipping();

        WPDKForm::htmlForm( $fields );
    }

    public static function viewWarehouse() {
        /* Tutto il blocco */
        $fields = self::fieldsWarehouse();

        WPDKForm::htmlForm( $fields );

    }

    public static function viewMembership() {
        /* Tutto il blocco */
        $fields = self::fieldsMembership();

        WPDKForm::htmlForm( $fields );

    }

    public static function viewCoupon() {
        /* Tutto il blocco */
        $fields = self::fieldsCoupon();

        WPDKForm::htmlForm( $fields );

        /* Dialogs */
        WPXSmartShopCoupons::dialogProductsPicker();

    }

    public static function viewDigitalProduct() {
        /* Tutto il blocco */
        $fields = self::fieldsDigitalProduct();

        WPDKForm::htmlForm( $fields );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Save actions
    // -----------------------------------------------------------------------------------------------------------------

    /* Price */
    public static function savePrice( $post ) {

        $id_product = absint( $post->ID );

        $base_price = WPXSmartShopCurrency::sanitizeCurrency( esc_attr( $_POST['wpss_product_base_price'] ) );
        update_post_meta( $id_product, 'wpss_product_base_price', $base_price );

        /* Prices */
        $rulesPrices = array();
        for ( $i = 0; $i < count( $_POST['wpss-product-rule-id'] ); $i++ ) {
            if ( !empty( $_POST['wpss-product-rule-id'][$i] ) ) {

                /* Sanitize Qty */
                $qty = absint( $_POST['wpss-product-rule-qty'][$i] );
                if ( empty( $qty ) ) {
                    $qty = '';
                }

                /* Sanitize abs_qty */
                $abs_qty = absint( $_POST['wpss-product-rule-abs-qty'][$i] );
                if ( empty( $abs_qty ) ) {
                    $abs_qty = '';
                }

                $date_start  = WPDKDateTime::dateTime2MySql( $_POST['wpss-product-rule-date_from'][$i], __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
                $date_expiry = WPDKDateTime::dateTime2MySql( $_POST['wpss-product-rule-date_to'][$i], __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );

                $rulesPrices[] = array(
                    'wpss-product-rule-id'  => $_POST['wpss-product-rule-id'][$i],
                    'date_from'             => $date_start,
                    'date_to'               => $date_expiry,
                    'price'                 => $_POST['wpss-product-rule-price'][$i],
                    'percentage'            => $_POST['wpss-product-rule-percentage'][$i],
                    'qty'                   => $qty,
                    'abs_qty'               => $abs_qty
                );
            }
        }
        if ( count( $rulesPrices ) > 0 ) {
            update_post_meta( $id_product, 'wpss_product_price_for_rules', serialize( $rulesPrices ) );
        } else {
            delete_post_meta( $id_product, 'wpss_product_price_for_rules' );
        }
    }

    /* Purchasable */
    public static function savePurchasable( $post ) {
        $id_product = absint( $post->ID );

        $wpxss_product_purchasable = array(
            'roles'         => empty( $_POST['wpxss_product_purchasable_role'] ) ? array() : array( esc_attr( $_POST['wpxss_product_purchasable_role'] ) ),
            'capabilities'  => empty( $_POST['wpxss_product_purchasable_capabilities'] ) ? array() : $_POST['wpxss_product_purchasable_capabilities'],
        );

        if ( empty( $wpxss_product_purchasable['roles'] ) && empty( $wpxss_product_purchasable['capabilities'] ) ) {
            delete_post_meta( $id_product, 'wpxss_product_purchasable' );
        } else {
            update_post_meta( $id_product, 'wpxss_product_purchasable', $wpxss_product_purchasable );
        }
    }

    /* Appearance */
    public static function saveAppearance( $post ) {
        $id_product = absint( $post->ID );

        /* Rule sull'aspetto */
        $rulesAppearance = array();
        for ( $i = 0; $i < count( $_POST['wpss-product-appearance-weight'] ); $i++ ) {
            $temp = array(
                'weight'   => $_POST['wpss-product-appearance-weight'][$i],
                'width'    => $_POST['wpss-product-appearance-width'][$i],
                'height'   => $_POST['wpss-product-appearance-height'][$i],
                'depth'    => $_POST['wpss-product-appearance-depth'][$i],
                'volume'   => $_POST['wpss-product-appearance-volume'][$i],

                'color'    => $_POST['wpss-product-appearance-color'][$i],
                'material' => $_POST['wpss-product-appearance-material'][$i],
                'model'    => $_POST['wpss-product-appearance-model'][$i],
                'note'     => $_POST['wpss-product-appearance-note'][$i],
                'value'    => $_POST['wpss-product-appearance-value'][$i]
            );
            if ( !self::isEmptyAppearanceRule( $temp ) ) {
                /* @todo Questo non deve essere vuoto */
                $key                   = $_POST['wpss-product-appearance_id_name'][$i];
                $rulesAppearance[$key] = $temp;
            }
        }

        if ( count( $rulesAppearance ) > 0 ) {
            update_post_meta( $id_product, 'wpss_product_appearance', serialize( $rulesAppearance ) );
        } else {
            delete_post_meta( $id_product, 'wpss_product_appearance' );
        }
    }

    /* Shipping */
    public static function saveShipping( $post ) {
        //$ID = absint( $post->ID );

        if( !function_exists( 'internal_saveShipping_walker')) {
            function internal_saveShipping_walker( $item ) {
                global $post;
                WPDKPostMeta::updatePostMetaWithDeleteIfNotSet( $post->ID, $item['name'], $_POST[$item['name']] );
            }
        }

        WPDKForm::walker( self::fieldsShipping(), 'internal_saveShipping_walker' );
    }

    /* Warehouse */
    public static function saveWarehouse( $post ) {

        if( !function_exists( 'internal_saveWarehouse_walker')) {
            function internal_saveWarehouse_walker( $item ) {
                global $post;

                $id_product = absint( $post->ID );

                if ( isset( $_POST[$item['name']] ) && !empty( $_POST[$item['name']] ) ) {
                    $value = $_POST[$item['name']];
                } else {
                    $value = null;
                }

                if ( $item['type'] == WPDK_FORM_FIELD_TYPE_DATETIME && !is_null( $value ) ) {
                    $value = WPDKDateTime::dateTime2MySql( $value, __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
                }

                WPDKPostMeta::updatePostMetaWithDeleteIfNotSet( $id_product, $item['name'], $value );
            }
        }

        WPDKForm::walker( self::fieldsWarehouse(), 'internal_saveWarehouse_walker' );

    }

    /* Membership */
    public static function saveMembership( $post ) {
        $id_product = absint( $post->ID );

        if ( isset( $_POST['wpss-membership'] ) ) {
            $wpss_membership = array(
                'role'          => esc_attr( $_POST['wpss-membership-role'] ),
                'capabilities'  => empty( $_POST['wpss-membership-capabilities'] ) ? array() : $_POST['wpss-membership-capabilities'],
                'duration'      => $_POST['wpss-membership-duration'],
                'duration-type' => $_POST['wpss-membership-duration-type']
            );
            update_post_meta( $id_product, 'wpss_product_membership_rules', serialize( $wpss_membership ) );
        } else {
            delete_post_meta( $id_product, 'wpss_product_membership_rules' );
        }
    }

    /* Coupon */
    public static function saveCoupon( $post ) {

        $id_product = absint( $post->ID );

        if ( isset( $_POST['wpss_product_coupon_enabled'] ) ) {
            $coupon_rules = WPDKForm::arrayKeyItemPostValue( WPSmartShopProductCoupon::fields() );
            unset( $coupon_rules['wpss_product_coupon_enabled'] );
            if ( $_POST['wpss_coupon_restrict_product'] == 'none' ) {
                unset( $coupon_rules['id_product'] );
                unset( $coupon_rules['id_product_type'] );
            } else if ( $coupon_rules['wpss_coupon_restrict_product'] == 'product' ) {
                unset( $coupon_rules['id_product_type'] );
            } else {
                unset( $coupon_rules['id_product'] );
            }
            update_post_meta( $id_product, 'wpss_product_coupon_rules', serialize( $coupon_rules ) );
        } else {
            delete_post_meta( $id_product, 'wpss_product_coupon_rules' );
        }
    }

    /* Digital Product */
    public static function saveDigitalProduct( $post ) {
        $id_product = absint( $post->ID );

        if( !empty( $_POST['wpss_product_digital_url'])) {
            update_post_meta( $id_product, 'wpss_product_digital_url', esc_attr( $_POST['wpss_product_digital_url'] ) );
            update_post_meta( $id_product, 'wpss_product_digital_version', esc_attr( $_POST['wpss_product_digital_version'] ) );
            update_post_meta( $id_product, 'wpss_product_digital_download_count', esc_attr( $_POST['wpss_product_digital_download_count'] ) );
            update_post_meta( $id_product, 'wpss_product_digital_languages', esc_attr( $_POST['wpss_product_digital_languages'] ) );
        } else {
            delete_post_meta( $id_product, 'wpss_product_digital_url' );
            delete_post_meta( $id_product, 'wpss_product_digital_version' );
            delete_post_meta( $id_product, 'wpss_product_digital_download_count' );
            delete_post_meta( $id_product, 'wpss_product_digital_languages' );
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Aux Appearance rules
    // -----------------------------------------------------------------------------------------------------------------

    public static function addFieldsAppearance( $fields ) {
        global $post;

        if ( isset( $post ) ) {

            $rules = unserialize( get_post_meta( $post->ID, 'wpss_product_appearance', true ) );

            /* Recupera la sezione appearance */
            $root = key( $fields );

            if ( $rules ) {
                foreach ( $rules as $key => $item ) {
                    $fields[$root][] = array(
                        'group' => ( self::fieldsAppearanceRule( $item, $key ) ),
                        'class' => 'wpss-product-appearance-rules'
                    );
                }
            }

            $fields[$root][] = array(
                'group' => ( self::fieldsAppearanceRule( self::emptyAppearanceRule() ) ),
                'class' => 'wpss-product-appearance-rules'
            );
            $fields[$root][] = array(
                'group' => ( self::fieldsAppearanceRule( self::emptyAppearanceRule() ) ),
                'class' => 'wpss-product-appearance-master hidden wpss-product-appearance-rules'
            );
        }
        return $fields;
    }

}

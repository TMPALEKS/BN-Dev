<?php
/**
 * @class              WPXSmartShopCoupons
 *
 * @description        Gestione dei Coupon
 *
 * @package            wpx SmartShop
 * @subpackage         coupons
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            30/12/11
 * @version            1.0
 *
 */

class WPXSmartShopCoupons extends WPDKDBTable {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce il nome della tabella Coupon
     *
     * @static
     * @retval string
     */
    public static function tableName() {
        global $wpdb;
        return sprintf( '%s%s', $wpdb->prefix, WPXSMARTSHOP_DB_TABLENAME_COUPONS );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Short hand
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Converte il campo status_datetime in una forma localizzata leggibile
     *
     * @static
     *
     * @param $coupon
     *
     * @retval string
     */
    private static function statusDateTime( $coupon ) {
        return WPDKDateTime::formatFromFormat( $coupon['status_datetime'], 'Y-m-d H:i:s', __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
    }

    /**
     * Converte il campo date_from in una forma localizzata leggibile
     *
     * @static
     *
     * @param $coupon
     *
     * @retval string
     */
    private static function dateFrom( $coupon ) {
        return WPDKDateTime::formatFromFormat( $coupon['date_from'], 'Y-m-d H:i:s', __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
    }

    /**
     * Converte il campo date_to in una forma localizzata leggibile
     *
     * @static
     *
     * @param $coupon
     *
     * @retval string
     */
    private static function dateTo( $coupon ) {
        return WPDKDateTime::formatFromFormat( $coupon['date_to'], 'Y-m-d H:i:s', __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
    }

    /**
     * Costruisce e restituisce l'array usato dall'engine WPDKForm per l'inserimento e l'editing di un coupon
     *
     * @static
     *
     * @param null $id
     *
     * @retval array
     */
    public static function fields( $id = null ) {
        if ( !is_null( $id ) ) {
            $coupon = self::coupon( $id, ARRAY_A );
        } else {
            $coupon = false;
        }

        $fields = array(
            __( 'Coupon restrictions', WPXSMARTSHOP_TEXTDOMAIN )   => array(
                __( 'Restrict your coupon usage for', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_RADIO,
                        'name'      => 'wpss_coupon_restrict_product',
                        'id'        => 'restrict_none',
                        'class'     => 'wpss-coupon-restrict-product',
                        'checked'   => $coupon ? ( $coupon['id_product'] == 0 && $coupon['id_product_type'] == 0 ) : 'none',
                        'value'     => 'none',
                        'label'     => __( 'None', WPXSMARTSHOP_TEXTDOMAIN )
                    )
                ),
                array(
                    array(
                        'type'     => WPDK_FORM_FIELD_TYPE_RADIO,
                        'name'     => 'wpss_coupon_restrict_product',
                        'id'       => 'restrict_product',
                        'class'    => 'wpss-coupon-restrict-product',
                        'checked'  => $coupon ? ( $coupon['id_product'] > 0 ) : '',
                        'value'    => 'product',
                        'title'    => __( 'Restrict this Coupon for a specify product', WPXSMARTSHOP_TEXTDOMAIN ),
                        'label'    => __( 'Product', WPXSMARTSHOP_TEXTDOMAIN )
                    ),
                    array(
                        'type'     => WPDK_FORM_FIELD_TYPE_CHOOSE,
                        'name'     => 'id_product',
                        'title'    => __( 'This Coupon is restrict to this product', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'    => $coupon ? $coupon['id_product'] : '',
                        'label'    => $coupon ? $coupon['product_name'] : ''
                    )
                ),
                array(
                    array(
                        'type'     => WPDK_FORM_FIELD_TYPE_RADIO,
                        'name'     => 'wpss_coupon_restrict_product',
                        'id'       => 'restrict_product_type',
                        'class'    => 'wpss-coupon-restrict-product',
                        'checked'  => $coupon ? ( $coupon['id_product_type'] > 0 ) : '',
                        'value'    => 'product_type',
                        'title'    => __( 'Restrict this Coupon for a specify product type', WPXSMARTSHOP_TEXTDOMAIN ),
                        'label'    => __( 'Product type', WPXSMARTSHOP_TEXTDOMAIN )
                    ),
                    array(
                        'type'     => WPDK_FORM_FIELD_TYPE_CHOOSE,
                        'name'     => 'id_product_type',
                        'title'    => __( 'This Coupon is restrict to this product', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'    => $coupon ? $coupon['id_product_type'] : '',
                        'label'    => $coupon ? $coupon['product_type_name'] : ''
                    )
                ),
                array(
                    array(
                        'type'     => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'     => 'wpss_coupon_restrict_user',
                        'label'    => __( 'User', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'    => 'y',
                        'checked'  => $coupon ? ( $coupon['id_owner'] > 0 ) : '',
                    ),
                    array(
                        'type'     => WPDK_FORM_FIELD_TYPE_CHOOSE,
                        'name'     => 'id_owner',
                        'value'    => $coupon ? $coupon['id_owner'] : '',
                        'label'    => ( $coupon &&
                            !empty( $coupon['user_owner_user_email'] ) ) ? sprintf( '%s (%s)', $coupon['users_owner_display_name'], $coupon['user_owner_user_email'] ) : ''
                    )
                )
            ),

            __( 'Availability', WPXSMARTSHOP_TEXTDOMAIN )          => array(
                __( 'Makes this coupon available', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'    => 'wpss_coupon_status',
                        'label'   => __( 'Status', WPXSMARTSHOP_TEXTDOMAIN ),
                        'options' => self::arrayStatusesForSDF( self::arrayStatuses() ),
                        'value'   => $coupon ? $coupon['status'] : 'available'
                    ),
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_DATETIME,
                        'name'  => 'wpss_coupon_date_from',
                        'label' => __( 'Date start', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => isset( $coupon['date_from'] ) ? self::dateFrom( $coupon ) : ''
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_DATETIME,
                        'name'  => 'wpss_coupon_date_to',
                        'label' => __( 'To', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => isset( $coupon['date_to'] ) ? self::dateTo( $coupon ) : ''
                    )
                ),
            ),
        );

        /* Comune sia all'edit che all'insert */
        $valueField = array(
            'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
            'name'   => 'wpss_coupon_value',
            'label'  => __( 'Value', WPXSMARTSHOP_TEXTDOMAIN ),
            'value'  => $coupon ? $coupon['value'] : '',
            'help'   => __( 'This is a numeric value for currency money. Append % for percentage. For apply Free to a product insert 100%', WPXSMARTSHOP_TEXTDOMAIN ),
        );
        $limit      = array(
            'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
            'name'   => 'wpss_coupon_limit_product_type',
            'label'  => __( 'Limit', WPXSMARTSHOP_TEXTDOMAIN ),
            'value'  => $coupon ? $coupon['limit_product_qty'] : '',
            'help'   => __( 'Limit this coupon for product', WPXSMARTSHOP_TEXTDOMAIN ),
        );
        $cumulative = array(
            'type'       => WPDK_FORM_FIELD_TYPE_CHECKBOX,
            'name'       => 'wpss_coupon_cumulative',
            'label'      => __( 'Cumulative ', WPXSMARTSHOP_TEXTDOMAIN ),
            'value'      => '1',
            'checked'    => $coupon ? $coupon['cumulative'] : '',
            'help'       => __( 'Set on for use this Coupon at the edge of order', WPXSMARTSHOP_TEXTDOMAIN )
        );

        /* Differente tra edit ed insert per lo uniqcode */
        $uniqcode_edit = array(
            'type'        => WPDK_FORM_FIELD_TYPE_TEXT,
            'name'        => 'wpss_coupon_uniqcode',
            'label'       => __( 'Custom Unique Code', WPXSMARTSHOP_TEXTDOMAIN ),
            'locked'      => true,
            'value'       => $coupon ? $coupon['uniqcode'] : '',
            'help'        => __( 'Usually will be Smart Shop to create coupon uniqcode. Left empty for automatic unique code or enter a your custom code.', WPXSMARTSHOP_TEXTDOMAIN )
        );

        /* Insert */
        $uniqcode_insert = array(
            array(
                'type'        => WPDK_FORM_FIELD_TYPE_TEXT,
                'name'        => 'wpss_coupon_uniqcode_prefix',
                'label'       => __( 'Custom Unique Code', WPXSMARTSHOP_TEXTDOMAIN ),
                'value'       => '',
                'size'        => 8,
                'placeholder' => __( 'Prefix', WPXSMARTSHOP_TEXTDOMAIN ),
            ),
            array(
                'type'   => WPDK_FORM_FIELD_TYPE_TEXT,
                'name'   => 'wpss_coupon_uniqcode',
                'locked' => true,
                'value'  => '',
            ),
            array(
                'type'        => WPDK_FORM_FIELD_TYPE_TEXT,
                'name'        => 'wpss_coupon_uniqcode_postfix',
                'value'       => '',
                'size'        => 8,
                'placeholder' => __( 'Postfix', WPXSMARTSHOP_TEXTDOMAIN ),
                'help'        => __( 'Usually will be Smart Shop to create coupon uniqcode. You can set prefix and postfix. Left empty for automatic unique code or enter a your custom code.', WPXSMARTSHOP_TEXTDOMAIN )
            ),
        );

        if ( !is_null( $id ) ) {
            $uniqcode = array( $uniqcode_edit );
        } else {
            $uniqcode = $uniqcode_insert;
        }

        /* Se siamo in Edit aggiungo altre informazioni normalmente non richieste per l'insert */
        if ( !is_null( $id ) ) {

            $usedBy   = ( !empty( $coupon['user_display_name'] ) ) ? $coupon['user_display_name'] : __( 'Nobody', WPXSMARTSHOP_TEXTDOMAIN );
            $usedDate = ( !empty( $coupon['user_display_name'] ) ) ? '(' . self::statusDateTime( $coupon ) . ')' : '';

            $newFields = array(
                __( 'Primary Coupon informations', WPXSMARTSHOP_TEXTDOMAIN ),
                $uniqcode,
                array(
                    $valueField,
                    $limit
                ),
                array(
                    $cumulative
                ),
                array(
                    array(
                        'type'       => WPDK_FORM_FIELD_TYPE_LABEL,
                        'afterlabel' => '',
                        'label'      => __( 'This coupon has been used by ', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'      => $usedBy . $usedDate
                    )
                )
            );

        } else {
            $newFields = array(
                __( 'Primary Coupon informations', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    $uniqcode,
                ),
                array(
                    $valueField,
                    $limit
                ),
                array(
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'   => 'wpss_coupon_qty',
                        'label'  => __( 'Quantity', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'  => 1,
                        'help'   => __( 'Coupon number to generate', WPXSMARTSHOP_TEXTDOMAIN )
                    ),
                ),
                array(
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'   => 'wpss_coupon_same_uniqcode',
                        'label'  => __( 'Generate an only one unique code', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'  => 1,
                        'help'   => __( 'Set this flag for create more coupon with the same unique code.', WPXSMARTSHOP_TEXTDOMAIN )
                    )
                ),
                array(
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'   => 'wpss_coupon_unlimited',
                        'label'  => __( 'Makes quantity unlimited', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'  => 1,
                        'help'   => __( 'If you check this flag the coupons will be used to up "date to" value if present.', WPXSMARTSHOP_TEXTDOMAIN )
                    )
                ),
                array(
                    $cumulative
                ),
            );

        }

        $fields = WPDKForm::fieldsetAtBeginningWithLabel( $fields, $newFields, __( 'Coupon', WPXSMARTSHOP_TEXTDOMAIN ) );

        return $fields;
    }

    /**
     * Restituisce l'elenco degli stati della tabella wpss_coupon
     *
     * @package    wpx SmartShop
     * @subpackage WPXSmartShopCoupons
     * @since      1.0.0
     *
     * @static
     * @see        self::statusesWithCount()
     * @retval array
     */
     static function arrayStatuses() {

        $status = array(
            'all'                                  => array(
                'label' => __( 'All', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            WPXSMARTSHOP_COUPON_STATUS_AVAILABLE   => array(
                'label' => __( 'Available', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            WPXSMARTSHOP_COUPON_STATUS_PENDING     => array(
                'label' => __( 'Pending', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            WPXSMARTSHOP_COUPON_STATUS_CONFIRMED   => array(
                'label' => __( 'Confirmed', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            'trash'                                => array(
                'label' => __( 'Trash', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            )
        );
        return $status;
    }

    /**
     * Restituisce un array con il tipo di status, la sua label e la count sul database
     *
     * @static
     * @retval array
     */
    function statuses() {
        return parent::statusesWithCount( self::tableName(), self::arrayStatuses() );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Utility
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Genera un ID univoco universale per identificare il coupon
     *
     * @static
     *
     * @param string $prefix
     * @param string $postfix
     *
     * @retval string
     */
    public static function uniqCode( $prefix = '', $postfix = '' ) {
        if ( strpos( $prefix, '%' ) !== false ) {
            $prefix = date( str_replace( '%', '', $prefix ) );
        }

        if ( strpos( $postfix, '%' ) !== false ) {
            $postfix = date( str_replace( '%', '', $postfix ) );
        }

        $prefix  = strtoupper( $prefix );
        $postfix = strtoupper( $postfix );

        return strtoupper( WPDKCrypt::uniqcode( $prefix, $postfix ) );
    }

    /**
     * Applica lo sconto di un coupon ad un prezzo (valore) qualsiasi
     *
     * @static
     *
     * @param string $value Valore del coupon. Moneta o percentuale.
     * @param float  $price Prezzo a cui applicare lo sconto
     *
     * @retval float Restituisce il valore in float
     */
    public static function applyCouponValue( $value, $price ) {
        $result = WPXSmartShopCurrency::sanitizeCurrency( $price );
        $price  = $result;
        $apply  = WPXSmartShopCurrency::sanitizeCurrency( $value );
        if ( WPXSmartShopCurrency::isPercentage( $value ) ) {
            $percentage = ( $price * $apply ) / 100;
            $result     = $price - $percentage;
        } else {
            $result = $price - $apply;
        }
        return floatval( $result );
    }

    /**
     * Dialogo per la selezione dell'utente. Usato anche dal gateway Ajax per la paginazione tramite Table View
     *
     * @static
     * @param int $paged
     */
    public static function dialogUserPicker( $paged = 1 ) {
        $userPicker = new WPXSmartShopUsersPicker( $paged );
        if ( $paged == 1 ) {
            $userPicker->view();
        } else {
            $userPicker->body();
        }
    }

    /**
     * Dialogo per la selezione del Prodotto/Tipo prodotto
     *
     * @static
     */
    public static function dialogProductsPicker() { ?>
        <div style="display:none" id="wpss-dialog-coupon-product-picker" title="<?php _e('Select a restrict product', WPXSMARTSHOP_TEXTDOMAIN ) ?>">
            <h6><?php _e('Loading...', WPXSMARTSHOP_TEXTDOMAIN ) ?></h6>
        </div>
        <?php
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Database actions
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Esegue l'aggiornamento dei dati del Coupon. Qui non si è potuto usare il metodo update() dell'oggetto
     * $wpdb in quanto ci sono le date da impostare a NULL sul database. A causa di un baco di WordPress infatti non è
     * possibile utilizzare update() per impostare a NULL dei campi. Così si è usata una sql custom.
     *
     * @static
     *
     * @see updateUserID
     *
     * @retval
     */
    public static function update() {
        global $wpdb;

        $id_product      = 0;
        $id_product_type = 0;

        if ( $_POST['wpss_coupon_restrict_product'] == 'product' ) {
            $id_product = absint( $_POST['id_product'] );
        }

        if ( $_POST['wpss_coupon_restrict_product'] == 'product_type' ) {
            $id_product_type = absint( $_POST['id_product_type'] );
        }

        $id_owner = 0;
        if ( isset( $_POST['wpss_coupon_restrict_user'] ) && $_POST['wpss_coupon_restrict_user'] == 'y' && absint( $_POST['id_owner'] ) > 0 ) {
            $id_owner = absint( $_POST['id_owner'] );
        }

        $date_from = 'NULL';
        $date_to   = 'NULL';

        if ( isset( $_POST['wpss_coupon_date_from'] ) && $_POST['wpss_coupon_date_from'] != '' ) {
            $date_from = WPDKDateTime::dateTime2MySql( $_POST['wpss_coupon_date_from'], __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
            $date_from = "'{$date_from}'";
        }

        if ( isset( $_POST['wpss_coupon_date_to'] ) && $_POST['wpss_coupon_date_to'] != '' ) {
            $date_to = WPDKDateTime::dateTime2MySql( $_POST['wpss_coupon_date_to'], __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
            $date_to = "'{$date_to}'";
        }

        $uniqcode = trim( strtoupper( $_POST['wpss_coupon_uniqcode'] ) );

        if ( empty( $uniqcode ) ) {
            $uniqcode = self::uniqCode( $_POST['wpss-coupon-uniqcode-prefix'], $_POST['wpss-coupon-uniqcode-postfix'] );
        }

        $tableName         = self::tableName();
        $value             = $_POST['wpss_coupon_value'];
        $limit_product_qty = absint( !empty( $_POST['wpss_coupon_limit_product_qty'] ) ? $_POST['wpss_coupon_limit_product_qty'] : 0 );
        $cumulative        = absint( isset( $_POST['wpss_coupon_cumulative'] ) ? $_POST['wpss_coupon_cumulative'] : 0 );
        $id                = absint( $_POST['id'] );
        $status            = esc_attr( $_POST['wpss_coupon_status'] );

        $sql    = <<< SQL
UPDATE `{$tableName}`
SET
    `id_product` = {$id_product},
    `id_product_type` = {$id_product_type},
    `limit_product_qty` = {$limit_product_qty},
    `id_owner` = {$id_owner},
    `uniqcode` = '{$uniqcode}',
    `value` = '{$value}',
    `cumulative` = '{$cumulative}',
    `date_from` = {$date_from},
    `date_to` = {$date_to},
    `status` = '{$status}'
WHERE `id` = '{$id}'
SQL;
        $result = $wpdb->query( $sql );
        return $result;
    }

    /**
     * Aggiorna lo stato di un coupon con un dato ID
     *
     * @static
     *
     * @param int    $id_coupon
     * @param string $status
     *
     * @retval mixed
     */
    public static function updateStatus( $id_coupon, $status ) {
        $values = array(
            'status'    => $status
        );
        return parent::update( self::tableName(), $id_coupon, $values );
    }

    /**
     * Aggiorna l'id dell'utente che ha usato questo coupon. È possibile anche aggiornare lo stato; questo perché quando
     * il campo id_user è diverso da zero, lo status dovrebbe essere sempre WPXSMARTSHOP_COUPON_STATUS_CONFIRMED. Se questo
     * metodo viene usato anche per id_user = 0, allora status potrebbe diventare WPXSMARTSHOP_COUPON_STATUS_AVAILABLE o
     * qualsiasi altra cosa.
     *
     * @static
     *
     * @param int    $id_coupon
     * @param int    $id_user
     * @param string $status
     *
     * @retval mixed
     */
    public static function updateUserID( $id_coupon, $id_user, $status = WPXSMARTSHOP_COUPON_STATUS_CONFIRMED ) {
        $values = array(
            'id_user'   => $id_user,
            'status'    => $status
        );
        return parent::update( self::tableName(), $id_coupon, $values );
    }

    /**
     * Inserisci uno o più coupon in base alle regole definite dal form di backend.
     *
     * @static
     *
     * @param array $rules Questo parametro statp introdotto per far pilotare al WPXSmartShopCouponMaker la creazione di
     *                     coupon. Normalmente comunque questo corriposnde a $_POST
     *
     * @retval array|false Elenco degli id dei coupon inseriti o false se errore
     */
    public static function create( $rules = null ) {
        global $wpdb;

        if ( is_null( $rules ) ) {
            $rules = $_POST;
        }

        /* Utente che ha inserito questo coupon - utente corrente */
        $id_user_maker = isset( $rules['id_user_maker'] ) ? $rules['id_user_maker'] : get_current_user_id();

        /* Prodotto che ha inserito questo coupon  */
        $id_product_maker = isset( $rules['id_product_maker'] ) ? $rules['id_product_maker'] : 0;

        $id_product      = 0;
        $id_product_type = 0;

        if ( isset( $rules['wpss_coupon_restrict_product'] ) ) {
            if ( $rules['wpss_coupon_restrict_product'] == 'product' ) {
                $id_product = absint( $rules['id_product'] );
            }
            if ( $rules['wpss_coupon_restrict_product'] == 'product_type' ) {
                $id_product_type = absint( $rules['id_product_type'] );
            }
        }

        $id_owner = 0;
        if ( isset( $rules['wpss_coupon_restrict_user'] ) && $rules['wpss_coupon_restrict_user'] == 'y' ) {
            /* Se arrivano dati in post sono nella maschera di edit/add new: quindi l'utente lo sto passando con lo
            user picker. Altrimenti la generazione dei coupon è dovuta ad un prodotto, eventualmente acquistato da terzi
            */
            if( isset( $_POST['id_owner']) && absint( $_POST['id_owner'] ) > 0 ) {
                $id_owner = absint( $_POST['id_owner'] );
            } else {
                $id_owner = $id_user_maker;
            }
        }

        $unlimited = absint( isset( $rules['unlimited'] ) ? $rules['unlimited'] : false );
        $id_owner  = $unlimited ? -1 : $id_owner;

        $date_from = 'NULL';
        $date_to   = 'NULL';

        if ( isset( $rules['wpss_coupon_date_from'] ) && $rules['wpss_coupon_date_from'] != '' ) {
            $date_from = WPDKDateTime::dateTime2MySql( $rules['wpss_coupon_date_from'], __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
            $date_from = "'{$date_from}'";
        }

        if ( isset( $rules['wpss_coupon_date_to'] ) && $rules['wpss_coupon_date_to'] != '' ) {
            $date_to = WPDKDateTime::dateTime2MySql( $rules['wpss_coupon_date_to'], __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
            $date_to = "'{$date_to}'";
        }

        /* Calocolo fine data a parte da ora (dall'acquisto) - usato dalla creazione prodotto */
        if ( !empty( $rules['wpss_product_coupon_durability'] ) ) {
            $value      = $rules['wpss_product_coupon_durability'];
            $type       = $rules['wpss_product_coupon_durability_type'];
            $expiredate = strtotime( "+{$value} {$type}" );
            $date_to    = date( 'Y-m-d H:i:s', $expiredate );
            $date_to    = "'$date_to'";
        }

        $uniqcode_prefix  = isset( $rules['wpss_coupon_uniqcode_prefix'] ) ? $rules['wpss_coupon_uniqcode_prefix'] : '';
        $uniqcode_postfix = isset( $rules['wpss_coupon_uniqcode_postfix'] ) ? $rules['wpss_coupon_uniqcode_postfix'] : '';

        $tableName         = self::tableName();
        $value             = $rules['wpss_coupon_value'];
        $qty               = absint( $rules['wpss_coupon_qty'] );
        $same_uniqcode     = absint( isset( $rules['wpss_coupon_same_uniqcode'] ) ? $rules['wpss_coupon_same_uniqcode'] : false );
        $uniqcode          = self::uniqCode( $uniqcode_prefix, $uniqcode_postfix );
        $cumulative        = absint( isset( $rules['wpss_coupon_cumulative'] ) ? $rules['wpss_coupon_cumulative'] : 0 );
        $limit_product_qty = absint( !empty( $rules['wpss_coupon_limit_product_qty'] ) ? $rules['wpss_coupon_limit_product_qty'] : 0 );
        $status            = isset( $rules['wpss_coupon_status'] ) ? $rules['wpss_coupon_status'] : WPXSMARTSHOP_COUPON_STATUS_AVAILABLE;

        /*
         * Genera i/il coupon
         */
        $ids = array();
        for ( $q = 0; $q < $qty; $q++ ) {
            $uniqcode = $same_uniqcode ? $uniqcode : self::uniqCode( $uniqcode_prefix, $uniqcode_postfix );

            $sql    = <<< SQL
INSERT INTO `{$tableName}`
(`date_insert`, `id_user_maker`, `id_product_maker`, `id_owner`, `id_product`, `id_product_type`, `limit_product_qty`, `uniqcode`, `value`, `cumulative`, `date_from`, `date_to`, `status`)
VALUES (NOW(), {$id_user_maker}, {$id_product_maker}, {$id_owner}, {$id_product}, {$id_product_type}, {$limit_product_qty}, '{$uniqcode}', '{$value}', '{$cumulative}', {$date_from}, {$date_to}, '{$status}')
SQL;
            $result = $wpdb->query( $sql );
            $ids[] = $wpdb->insert_id;

            if ( $result === false ) {
                return $result;
            }

            /**
             * Crea un Coupon
             *
             * @actions
             *
             * @param $rules Regole del coupon creato
             */
            do_action( 'wpss_coupon_created', $rules );
        }

        return $ids;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Database
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Crea o aggiorna (esegue un delta) la tabella dei Coupon.
     * Questo metodo viene chiamato (di solito) all'attivazione del plugin, quindi una sola volta.
     *
     * @static
     */
    public static function updateTable() {
        if ( !function_exists( 'dbDelta' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }
        $dbDeltaTableFile = sprintf( '%s%s', WPXSMARTSHOP_PATH_DATABASE, WPXSMARTSHOP_DB_TABLENAME_FILENAME_COUPONS );
        $file             = file_get_contents( $dbDeltaTableFile );
        $sql              = sprintf( $file, self::tableName() );
        @dbDelta( $sql );
    }

    /**
     * Restituisce le informazioni su un coupon con un dato ID o uniqcode. In caso si usi lo uniqcode la riga estratta
     * è sempre una solo, nonostante possona esistere più coupon con lo stesso id. In questo caso verrà estratto il
     * primo ordinati per id.
     *
     * @todo Verificare se manca qualche condizione nella where come 'publish' o ancora valido.
     *
     * @static
     *
     * @param int|string $coupon ID o Uniqcode
     * @param string     $output
     *
     * @retval object|WP_Error
     */
    public static function coupon( $coupon, $output = OBJECT ) {
        global $wpdb;

        if ( is_numeric( $coupon ) ) {
            $where = sprintf( 'WHERE coupon.id = %s', $coupon );
        } elseif ( is_string( $coupon ) ) {
            $where = sprintf( 'WHERE coupon.uniqcode = "%s"', $coupon );
        } else {
            $message = __( 'Internal Error: Wrong coupon parameter!', WPXSMARTSHOP_TEXTDOMAIN );
            return new WP_Error( 'wpss_error-coupon_wrong_parameter', $message, $coupon );
        }

        $table = self::tableName();

        $sql = <<< SQL
SELECT coupon.*,
       users.display_name AS user_display_name,
       users_owner.display_name AS users_owner_display_name,
       users_owner.user_email AS user_owner_user_email,
       products.post_title AS product_name,
       terms_product_type.name AS product_type_name
FROM `{$table}` AS coupon
LEFT JOIN `{$wpdb->users}` AS users ON coupon.id_user = users.ID
LEFT JOIN `{$wpdb->users}` AS users_owner ON coupon.id_owner = users_owner.ID
LEFT JOIN `{$wpdb->posts}` AS products ON coupon.id_product = products.ID
LEFT JOIN `{$wpdb->terms}` AS terms_product_type ON coupon.id_product_type = terms_product_type.term_id
{$where}
ORDER BY id
SQL;
        $row = $wpdb->get_row( $sql, $output );
        return $row;
    }

    public static function coupons( $id_coupons ) {
        global $wpdb;

        $ids_coupons = join( ',', $id_coupons );

        $table = self::tableName();

        $sql = <<< SQL
SELECT coupon.*,
       users.display_name AS user_display_name,
       users_owner.display_name AS users_owner_display_name,
       users_owner.user_email AS user_owner_user_email,
       products.post_title AS product_name,
       terms_product_type.name AS product_type_name
FROM `{$table}` AS coupon
LEFT JOIN `{$wpdb->users}` AS users ON coupon.id_user = users.ID
LEFT JOIN `{$wpdb->users}` AS users_owner ON coupon.id_owner = users_owner.ID
LEFT JOIN `{$wpdb->posts}` AS products ON coupon.id_product = products.ID
LEFT JOIN `{$wpdb->terms}` AS terms_product_type ON coupon.id_product_type = terms_product_type.term_id
WHERE coupon.id IN ({$ids_coupons})
ORDER BY id
SQL;
        $results = $wpdb->get_results( $sql );

        return $results;
    }

    /* @todo Non usata solo per prova */
    public static function uniqCodes( $id_coupons ) {
        global $wpdb;

        $ids_coupons = join( ',', $id_coupons );

        $table = self::tableName();

        $sql = <<< SQL
SELECT uniqcode
FROM `{$table}` AS coupon
WHERE id IN ({$ids_coupons})
ORDER BY id
SQL;
        $results = $wpdb->get_results( $sql );

        return $results;
    }

    /**
     * Restituisce il primo coupon per un dato codice univoco
     *
     * @deprecated
     *
     * @static
     *
     * @param string $uniqcode Serial number del copupon
     * @param string $output Tipo di output
     *
     * @retval mixed Object o Array del coupon. NULL se non trovato
     */
    public static function couponWithUniqCode( $uniqcode, $output = OBJECT ) {
        _deprecated_function( __FUNCTION__, '1.0', 'coupon()' );
        return self::coupon( $uniqcode, $output );

        /*
        global $wpdb;

        $table = self::tableName();

        $sql = <<< SQL
SELECT coupon.*,
       users.display_name AS user_display_name,
       users_owner.display_name AS users_owner_display_name,
       products.post_title AS product_name,
       terms_product_type.name AS product_type_name
FROM `{$table}` AS coupon
LEFT JOIN `{$wpdb->users}` AS users ON coupon.id_user = users.ID
LEFT JOIN `{$wpdb->users}` AS users_owner ON coupon.id_owner = users_owner.ID
LEFT JOIN `{$wpdb->posts}` AS products ON coupon.id_product = products.ID
LEFT JOIN `{$wpdb->terms}` AS terms_product_type ON coupon.id_product_type = terms_product_type.term_id
WHERE coupon.uniqcode = '{$uniqcode}'
ORDER BY id
LIMIT 0,1
SQL;
        $row = $wpdb->get_row( $sql, $output );
        return $row;
        */
    }


    /**
     * Restituisce le informazioni su uno o più coupon con un dato uniqcode
     *
     * @static
     *
     * @param string $uniqcode
     * @param string $output
     *
     * @retval array Elenco dei coupon con codice $uniqcode. Questi possono essere più di uno in caso di stesso codice
     * applicato.
     */
    public static function couponsWithUniqCode( $uniqcode, $output = OBJECT ) {
        global $wpdb;

        $table = self::tableName();

        $sql    = <<< SQL
SELECT coupon.*,
       users.display_name AS user_display_name,
       users_owner.display_name AS users_owner_display_name,
       products.post_title AS product_name,
       terms_product_type.name AS product_type_name
FROM `{$table}` AS coupon
LEFT JOIN `{$wpdb->users}` AS users ON coupon.id_user = users.ID
LEFT JOIN `{$wpdb->users}` AS users_owner ON coupon.id_owner = users_owner.ID
LEFT JOIN `{$wpdb->posts}` AS products ON coupon.id_product = products.ID
LEFT JOIN `{$wpdb->terms}` AS terms_product_type ON coupon.id_product_type = terms_product_type.term_id
WHERE coupon.uniqcode = '{$uniqcode}'
SQL;
        $result = $wpdb->get_results( $sql, $output );
        return $result;
    }

    /**
     * Restituisce i coupon dispobili per un dato prodotto o tipo prodotto. Per disponibili si intende anche quelli che
     * non sono presenti in ordini con stato 'pending' o 'confirmed'. Questo metodo infatti controlla anche la tabella
     * delle statistiche cercando coupon usati ma non ancora riscossi.
     * Notare che nella select attualmente non è indicato esplicitamente diverso da 'confirmed' in quanto quando un
     * ordine passa allo stato 'confirmed' il campo id_user nella tabella coupon viene valorizzato e quindi quel
     * coupon è escluso automaticamente dalla condizione id_user = 0
     *
     * @note       Questa viene usata quando si deve applicare un coupon ad un prodotto. Per questo motivo viene
     *             richiesto il parametro id_product perché solo i coupon che hanno id_product o id_product_type
     *             impostato sono coupon possibii da usare sui prodotti, altrimenti sono coupon di tipo ordine.
     *
     *
     * @param string $uniqcode   Serial code del coupon
     * @param int    $id_product ID del prodotto
     * @param string $output     Tipo di output
     *
     * @retval bool|array Array dei coupon disponibile o false se nessuno presente
     */
    public static function couponsWithUniqCodeApplicableForProductID( $uniqcode, $id_product, $output = OBJECT ) {
        global $wpdb;

        $table = self::tableName();

        /*
         * Recupera le eventuali tipologie di un prodotto e ne costruisce una condizione di Where
         */
        $product_types       = get_the_terms( $id_product, kWPSmartShopProductTypeTaxonomyKey );
        $where_product_types = '';
        if ( $product_types != false ) {
            $id_product_types = array();
            foreach ( $product_types as $product_type ) {
                $id_product_types[] = $product_type->term_id;
            }
            $where_product_types = ' OR coupon.id_product_type IN(' . implode( ',', $id_product_types ) . ')';
        }

        $status      = WPXSMARTSHOP_COUPON_STATUS_AVAILABLE;

        $sql = <<< SQL
SELECT coupon.*,
       users.display_name AS user_display_name,
       users_owner.display_name AS users_owner_display_name,
       products.post_title AS product_name,
       terms_product_type.name AS product_type_name
FROM `{$table}` AS coupon
LEFT JOIN `{$wpdb->users}` AS users ON coupon.id_user = users.ID
LEFT JOIN `{$wpdb->users}` AS users_owner ON coupon.id_owner = users_owner.ID
LEFT JOIN `{$wpdb->posts}` AS products ON coupon.id_product = products.ID
LEFT JOIN `{$wpdb->terms}` AS terms_product_type ON coupon.id_product_type = terms_product_type.term_id

WHERE coupon.uniqcode = '{$uniqcode}'

AND (coupon.status = '{$status}' )
AND (coupon.id_user = 0)

AND (coupon.id_product = {$id_product} {$where_product_types})
AND (coupon.date_from IS NULL OR TIMESTAMP(coupon.date_from) <= TIMESTAMP(NOW()))
AND (coupon.date_to IS NULL OR TIMESTAMP(coupon.date_to) >= TIMESTAMP(NOW()))
SQL;

        $result = $wpdb->get_results( $sql, $output );

        if ( !empty( $result ) ) {
            return $result;
        }

        return false;
    }

    /**
     * Restituisce uno o più coupon ordine in base al codice univoco impostato.
     *
     * @static
     *
     * @param string $uniqcode   Codice coupon
     * @param bool   $cumulative Se True nella condizione di Where viene espressamente richiesto un coupon cumulativo.
     *                           Se false vengono estratti indistintamente si ai coupon cumulativi che quelli non.
     *
     * @param string $output
     *
     * @retval bool | array Array di oggetti record dal database che rappresentano i coupon. False nessun coupon trovato
     */
    public static function couponsWithUniqCodeApplicableForOrder( $uniqcode, $cumulative = false, $output = OBJECT ) {
        global $wpdb;

        $table            = self::tableName();
        $status           = WPXSMARTSHOP_COUPON_STATUS_AVAILABLE;
        $where_cumulative = $cumulative ? 'AND cumulative = \'1\'' : '';

        $sql = <<< SQL
SELECT coupon.*,
       users.display_name AS user_display_name,
       users_owner.display_name AS users_owner_display_name,
       products.post_title AS product_name,
       terms_product_type.name AS product_type_name
FROM `{$table}` AS coupon
LEFT JOIN `{$wpdb->users}` AS users ON coupon.id_user = users.ID
LEFT JOIN `{$wpdb->users}` AS users_owner ON coupon.id_owner = users_owner.ID
LEFT JOIN `{$wpdb->posts}` AS products ON coupon.id_product = products.ID
LEFT JOIN `{$wpdb->terms}` AS terms_product_type ON coupon.id_product_type = terms_product_type.term_id

WHERE coupon.uniqcode = '{$uniqcode}'

AND (coupon.status = '{$status}' )
AND (coupon.id_user = 0)
AND (coupon.id_product = 0)
AND (coupon.id_product_type = 0)

{$where_cumulative}

AND (coupon.date_from IS NULL OR TIMESTAMP(coupon.date_from) <= TIMESTAMP(NOW()))
AND (coupon.date_to IS NULL OR TIMESTAMP(coupon.date_to) >= TIMESTAMP(NOW()))
SQL;

        $result = $wpdb->get_results( $sql, $output );

        if ( count( $result ) > 0 ) {
            return $result;
        }

        return false;
    }



    // -----------------------------------------------------------------------------------------------------------------
    // has/is Zone
    // -----------------------------------------------------------------------------------------------------------------



    // -----------------------------------------------------------------------------------------------------------------
    // Database Commodity (exists, available and check)
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce True se esiste un coupon disponibile con uno specifico codice univoco. Il codice univoco del coupon
     * portebbe essere presente su più righe nel database, questo a causa del funzionamento del limite applicato ai
     * coupon stessi. Se voglio limitare l'uso di un coupon a 6 volte, verranno create 6 righe con lo stesso codice.
     * Questo metodo ne cerca almeno uno ancora disponibile.
     *
     * @todo       Da estendere nella Where, non tiene conto dei coupon negli ordini e nelle stats
     *
     * @static
     *
     * @param string $uniqcode Codice univoco (interno) che identifica un coupon.
     *
     * @retval bool True se il coupon esiste ed è ancora disponibile
     */
    public static function couponExistsWithUniqCode( $uniqcode ) {
        global $wpdb;

        $table  = self::tableName();
        $status = WPXSMARTSHOP_COUPON_STATUS_AVAILABLE;

        $sql     = <<< SQL
SELECT id FROM `{$table}`
WHERE `uniqcode` = '$uniqcode'
AND `status` = '{$status}'
AND `id_user` = 0
AND (date_from IS NULL OR TIMESTAMP(date_from) <= TIMESTAMP(NOW()))
AND (date_to IS NULL OR TIMESTAMP(date_to) >= TIMESTAMP(NOW()))
SQL;
        $results = $wpdb->get_results( $sql );

        return ( count( $results ) > 0 );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Delegate methods
    //
    // Questi sono metodi a tutti gli effetti ma con una nomenclatura (naming) particolare. Essi infatti sono chiamati
    // internamente da altre classi per segnalare qualcosa
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Aggiorna lo stato di uno o più coupon legati ad un ordine e stats.
     * Se un ordine viene cancellato o reso defunto, i coupon tornano disponibili.
     *
     * @static
     * @param string $trackID
     * @param string $status
     */
    public static function didOrderStatusUpdated( $trackID, $status ) {

        $staus_coupon = $status;

        if ( $status == WPXSMARTSHOP_ORDER_STATUS_DEFUNCT || $status == WPXSMARTSHOP_ORDER_STATUS_CANCELLED ) {
            $staus_coupon = WPXSMARTSHOP_COUPON_STATUS_AVAILABLE;
        }

        $order = WPXSmartShopOrders::order( $trackID );
        if ( $order ) {

            /* Aggiorna coupon ordine */
            if ( !empty( $order->id_coupon ) ) {
                self::updateStatus( $order->id_coupon, $staus_coupon );
            }

            /* Aggiorna Coupon prodotti e utente */
            $coupons = WPXSmartShopStats::productCoupons( $order->id );
            if ( $coupons ) {
                foreach ( $coupons as $coupon ) {
                    self::updateUserID( $coupon->id_coupon, $order->id_user_order, $staus_coupon );
                }
            }
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // UI
    // -----------------------------------------------------------------------------------------------------------------

}
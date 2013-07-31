<?php
/**
 * Gestisce Estensioni Fatture
 *
 * @package            BNMExtends
 * @subpackage         BNMExtendsInvoices
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (C)2011 Saidmade Srl.
 * @created            18/07/12
 * @version            1.0
 *
 */
class BNMExtendsInvoices {

    /**
     * Restituisce il nome della tabella delle Fatture
     * @static
     * @retval string
     */
    public static function tableName() {
        global $wpdb;
        return sprintf( '%s%s', $wpdb->prefix, kBNMExtendsDatabaseTableInvoices );
    }


    /**
     * Aggiunge informazioni sulla fatturazione in fondo al post
     * @param $fields
     * @param $order
     * @return mixed
     */
    public static function addInvoiceInformationsBox( $fields, $order) {

        if( isset( $order ) && $order ):

            $invoice = self::getInvoiceByOrder( $order );

            $new_fields = array(
                __( 'Invoice information', 'bnmextends' ) => array(
                    array(
                        /*
                        array(
                            'type'  => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                            'name'  => 'invoice_check',
                            'label' => __( 'Has Invoice? (y/n)', WPXSMARTSHOP_TEXTDOMAIN ),
                            'value' => isset( $invoice['invoice_check'] ) &&  ($invoice['invoice_check'] != 0) ? $invoice['invoice_check'] : '0',
                            'checked' => isset( $invoice['invoice_check'] ) &&  ($invoice['invoice_check'] != 0) ? 'checked' : ''
                        ),*/
                        array(
                            'type'  => WPDK_FORM_FIELD_TYPE_HIDDEN,
                            'name'  => 'invoice_id',
                            'value' => isset( $invoice['id'] ) &&  ($invoice['id'] != 0) ? $invoice['id'] : '0'
                        ),
                    ),

                    array(

                        array(
                            'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                            'name'  => 'invoice_company_name',
                            'size'  => 32,
                            'label' => __( 'Company Name', 'bnmextends' ),
                            'value' => isset( $invoice['invoice_company_name'] ) ? $invoice['invoice_company_name'] : ''
                        ),
                        array(
                            'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                            'name'  => 'invoice_vat_number',
                            'size'  => 32,
                            'label' => __( 'Vat or Fiscal Number', 'bnmextends' ),
                            'value' => isset( $invoice['invoice_vat_number'] ) ? $invoice['invoice_vat_number'] : ''
                    ),

                    array(
                        array(
                            'type'  => WPDK_FORM_FIELD_TYPE_TEXTAREA,
                            'name'  => 'invoice_note',
                            'cols'  => 42,
                            'label' => __( 'Notes', 'bnmextends' ),
                            'value' => isset( $invoice['invoice_note'] ) ? $invoice['invoice_note'] : ''
                        ),
                    ),
                        /*
                         * Ad uso futuro, per ora non è usato
                        array(
                            'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                            'name'  => 'invoice_fiscal_code',
                            'size'  => 32,
                            'label' => __( 'Fiscal Code', WPXSMARTSHOP_TEXTDOMAIN ),
                            'value' => isset( $invoice['invoice_fiscal_code'] ) ? $invoice['invoice_fiscal_code'] : ''
                        ),*/
                    )
                ),
            );

            $fields += $new_fields;
        endif;
        return $fields;
    }

    /**
     * @param string $order_id
     */
    public static function addOrUpdateInvoice( $order_id = "" ){
        $invoice = array();

        $invoice['invoice_check'] = isset( $_POST['invoice_check'] )  ? $_POST['invoice_check'] : 0 ;
        $invoice['invoice_company_name'] = $_POST['invoice_company_name'];
        $invoice['invoice_note'] = $_POST['invoice_note'];
        $invoice['invoice_vat_number'] = $_POST['invoice_vat_number'];
        $invoice['invoice_fiscal_code'] = isset( $_POST['invoice_fiscal_code'] ) ? $_POST['invoice_fiscal_code'] : "" ;

        if ( isset( $_POST['invoice_id']) && ( ( $_POST['invoice_id']) != 0 ) ) :
            //L'ordine non lo cambio, perchè la fattura ormai è legata!
            //Eventualmente sganciare l'ordine se si spunta la check box...da valutare
            $invoice['id'] = $_POST['invoice_id'];
            self::updateInvoice( $invoice );
        elseif ( isset( $_POST['invoice_company_name'] ) && ( trim( $_POST['invoice_company_name'] ) != "" ) && isset( $order_id ) && $order_id != "" ):
            $invoice['invoice_id_order'] = $order_id;
            self::createInvoice( $invoice );
        endif;

    }

    /**
     * @param $invoice
     * @return mixed
     */
    public static function createInvoice( $invoice ){
        global $wpdb;

        WPDKWatchDog::watchDog( __METHOD__, "Will Add " . $invoice['invoice_company_name'] . " VAT:" . $invoice['invoice_vat_number'] . " FISCAL CODE: " . $invoice['invoice_fiscal_code'] );

        $result = $wpdb->insert( self::tableName(), $invoice );

        $invoice['id'] = $wpdb->insert_id;

        if( is_wp_error( $result ) ) {
            WPDKWatchDog::watchDog( __METHOD__, "Error with result: " . $result );
        }

        return $result;

    }

    /**
     * @param $invoice
     * @return mixed
     */
    public static function updateInvoice( $invoice ){
        global $wpdb;


        WPDKWatchDog::watchDog( __CLASS__, __METHOD__, "Will Add " . $invoice['invoice_company_name'] . " VAT:" . $invoice['invoice_vat_number'] . " FISCAL CODE: " . $invoice['invoice_fiscal_code'] );

        $result = $wpdb->update( self::tableName(), $invoice );

        if( is_wp_error( $result ) ) {
            WPDKWatchDog::watchDog( __CLASS__, __METHOD__, "Error with result: " . $result );
        }

        return $result;
    }

    /*
    public static function updateUserProfile( $values ){
        global $wpdb;
        if ( !empty($values) ){
            $id_order = $values['invoice_id_order'];

            unset( $values['id'] );
            unset( $values['invoice_id_order'] );
            unset( $values['invoice_fiscal_code'] );
            unset( $values['invoice_check'] );

            $order = WPXSmartShopOrders::order( $id_order, ARRAY_A);

            $where = array(
                'id_user'   => $order['id_user'],
            );

            $result = $wpdb->update( BNMExtendsUser::tableName(), $values, $where );
            return $result;
        }
    }
    */

    /**
     * @param $order
     * @return mixed
     */
    public static function getInvoiceByOrder( $order ){
        global $wpdb;

        if ( is_string( $order) )
            $order_id = intval($order);
        else
            $order_id = $order['id'];

        $table = self::tableName();

        $sql = <<< SQL
        SELECT * FROM `{$table}`
        WHERE `invoice_id_order` =  {$order_id}
SQL;
        $invoice = $wpdb->get_row( $sql, ARRAY_A );

        return  $invoice;

    }
}

?>
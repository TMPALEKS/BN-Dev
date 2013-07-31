<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Webeing.net
 * Date: 27/11/12
 * Time: 18:28
 */
class BNMExtendsStats {


    /**
     * @return bool
     * Aggiornamento retroattivo della tabella statistiche
     */
    public static function batchUpdateCategoryForStats(){
        global $wpdb;
        $update_limit = 100000; //Limite di record da aggiornare

        $table = WPXSmartShopStats::tableName();

        $sql = <<<SQL
SELECT *
from {$table}
WHERE 1
AND {$table}.product_category_id = 0
ORDER BY id DESC
LIMIT 0,{$update_limit}
SQL;

        $withoutcats =  $wpdb->get_results($sql, ARRAY_A);
        #var_dump($withoutcats); die();

        $countitem = 0;

        foreach ( $withoutcats as $item ){
            $terms = wp_get_object_terms( $item['id_product'], kWPSmartShopProductTypeTaxonomyKey );

            if (!empty($terms)):
                #var_dump($terms, $item); die();
                foreach( $terms as $term):
                    if( $term->parent == 0 ){
                        $parent_term = $term;

                        //Aggiorno il valore della categoria
                        $update = <<<UPDATE
                        UPDATE {$table}
                        SET product_category_id={$parent_term->term_id}, product_category_description='{$parent_term->name}'
                        WHERE id = {$item['id']}
UPDATE;

                        $ups =  $wpdb->get_results($update, ARRAY_A);
                        $countitem ++;
                    }

                    else
                        continue;

                endforeach;
            endif;
        }
        return $countitem;
    }

    /**
     * @param $cat
     * Ritorna la label per la categoria SAP a partire dalla categoria ticket sul sito
     */
    public static function categoryConnectForSap( $cat ){

        $category = "";

        //@ToDo Controllare che gli ID delle cateogrie siano gli stessi
        $sapCategories = array(
            '32'    => 'merchandising', //Libri CD-DVD
            '36'    => 'merchandising', //Abbigliamento
            '37'    => 'merchandising', //Accessori
            '31'    => 'biglietti',     //Bliglietti di ingresso
            '33'    => 'tessere',       //Abbonamenti e Voucher
            '60'    => 'tessere',       //Memberships
        );

        if( array_key_exists($cat,$sapCategories) )
            $category = $sapCategories[$cat];

        return $category;
    }

    /*
    * Custom Filter on list view
    */
    public static function orderSummary( $data ) {
       return 'orders.status ASC';
    }

    public static function queryWhere( $where ){
        return " AND orders.status = '" . WPXSMARTSHOP_ORDER_STATUS_CONFIRMED . "'";
    }

    public static function addColumns( $columns ){
        $columns['status'] = 'status';
        return $columns;
    }

    public static function customColumnDefault( $item ){
        $column_values = array(
            'column_name'       => 'status',
            'column_default'    => $item['status']
        );
        return $column_values;
    }

    public static function addStatsProductCategories( $values, $id_product ){

        $terms = wp_get_object_terms( $id_product, kWPSmartShopProductTypeTaxonomyKey );

        foreach( $terms as $term):
            if( $term->parent == 0 )
                $parent_term = $term;

        endforeach;

        $values['product_category_id'] = $parent_term->term_id;
        $values['product_category_description'] = $parent_term->name;

        return $values;
    }

    /**
     * Export CSV
     */

    /**
     * @param $columns
     * @return array
     *
     * Aggiunge le colonne necessarie al nuovo export
     */
    public static function addColumnsCSV( $columns ){

        $new_columns =  array(
            __( 'Order Date', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Status', WPXSMARTSHOP_TEXTDOMAIN )
            );
        $columns = array_merge($columns, $new_columns);

        return $columns;

    }

    /**
     * @param $data
     * @return string
     *
     * Altera il buffer in output dalla funzione Export
     */
    public static function alterBufferCSV ( $data ){
        $buffer =  '';

        foreach( $data as $item ) {

            /**
             * @filters
             *
             * @param string $localizable_value
             * @param int    $id_product
             * @param string $id_variant
             * @param array  $variant
             * @param string $key
             */
            $model = apply_filters( 'wpss_product_variant_localizable_value', $item['model'] );

            $price_rule_code = $item['price_rule'];
            if ( $price_rule_code == kWPSmartShopProductTypeRuleOnlinePrice ) {
                /**
                 * @filters
                 * @todo Da documentare
                 */
                $price_rule = apply_filters( 'wpxss_stats_column_price_rule_online', __( 'Online', WPXSMARTSHOP_TEXTDOMAIN ), $price_rule_code );
            } elseif ( $price_rule_code == 'base_price' ) {
                /**
                 * @filters
                 * @todo Da documentare
                 */
                $price_rule = apply_filters( 'wpxss_stats_column_price_rule_base_price', __( 'Base price', WPXSMARTSHOP_TEXTDOMAIN ), $price_rule_code );
            } elseif ( $price_rule_code == kWPSmartShopProductTypeRuleDatePrice ) {
                /**
                 * @filters
                 * @todo Da documentare
                 */
                $price_rule = apply_filters( 'wpxss_stats_column_price_rule_date_range', __( 'Date range', WPXSMARTSHOP_TEXTDOMAIN ), $price_rule_code );
            } elseif ( isset( $roles_names[$price_rule_code] ) ) {
                $price_rule = $roles_names[$price_rule_code];
            } else {
                /**
                 * Invia il codice della regola per attività personalizzate esterne
                 *
                 * @filters
                 *
                 * @param string Decsription
                 * @param string ID key ruole
                 *
                 * @todo Da documentare
                 */
                $price_rule = apply_filters( 'wpxss_stats_column_price_rule', __( 'Unknown', WPXSMARTSHOP_TEXTDOMAIN ), $price_rule_code );
            }

            $buffer .= sprintf( '"%s - %s","%s","%s %s","%s %s","%s (%s)","%s","%s","%s","%s","%s","%s","%s","%s","%s"',
                $item['id_order'],
                $item['track_id'],
                $item['product_title'],

                $item['bill_last_name'],
                $item['bill_first_name'],

                $item['bill_email'],
                $item['bill_phone'],

                $item['user_display_name'],
                WPDKUser::roleNameForUserID( $item['id_user']  ),
                //$item['user_order_display_name'],
                //WPDKUser::roleNameForUserID( $item['id_user_order']  ),
                $item['order_note'],
                $item['id_variant'],
                $model,
                $item['coupon_uniqcode'],
                sprintf( '%s %s', WPXSmartShopCurrency::currencySymbol(), WPXSmartShopCurrency::formatCurrency( $item['product_amount'] ) ),
                $price_rule,
                sprintf( '%s %s', WPXSmartShopCurrency::currencySymbol(), WPXSmartShopCurrency::formatCurrency( $item['amount'] ) ),
                $item['order_datetime'],
                $item['status']
            );
            $buffer .= WPDK_CRLF;
        }
        return $buffer;

    }


    /// Hook when admin is init
    public static function adminInit() {


        /* Stats: Export CSV for SAP*/
        if ( isset( $_GET['export_stats_sap_csv'] ) ) {
            self::downalodSapCSV();
            exit;
        }
    }

    /**
    * Esegue fisicamente il download del file csv per Sap.
    * @static
    *
    */
    public static function downalodSapCSV() {
        /* Definisco un filename */
        $filename = sprintf( 'wpxSmartShop-Stats-%s.csv', date( 'Y-m-d H:i:s' ) );

        /* Contenuto */
        $buffer = get_transient( 'wpxss_stats_sap_csv' );

        /* Header per download */
        header( 'Content-Type: application/download' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Cache-Control: public' );
        header( "Content-Length: " . strlen( $buffer ) );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        echo $buffer;
    }

    /**
     * @param $data
     * @return string
     *
     * Provvede al salvataggio dei dati in formato CSV per SAP
     */
    public static function exportForSapCSV( $data ){

        $roles = new WP_Roles();
        $roles_names = $roles->get_names();

        $columns = array(
            __( 'Order', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Product title', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'CategorySAP', WPXSMARTSHOP_TEXTDOMAIN ),
           // __( 'EMail & Phone', WPXSMARTSHOP_TEXTDOMAIN ),
            //__( 'Ordered by', WPXSMARTSHOP_TEXTDOMAIN ),
            //__( 'Note', WPXSMARTSHOP_TEXTDOMAIN ),
            //__( 'Variant', WPXSMARTSHOP_TEXTDOMAIN ),
            //__( 'Model', WPXSMARTSHOP_TEXTDOMAIN ),
            //__( 'Coupon', WPXSMARTSHOP_TEXTDOMAIN ),
            //__( 'Price', WPXSMARTSHOP_TEXTDOMAIN ),
            //__( 'Price rule', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Purchased', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Date', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Status', WPXSMARTSHOP_TEXTDOMAIN ),
        );

        /* Crea il CSV */
        $buffer =  '';
        foreach( $data as $item ) {

            /**
             * @filters
             *
             * @param string $localizable_value
             * @param int    $id_product
             * @param string $id_variant
             * @param array  $variant
             * @param string $key
             */
            $model = apply_filters( 'wpss_product_variant_localizable_value', $item['model'] );

            $price_rule_code = $item['price_rule'];
            if ( $price_rule_code == kWPSmartShopProductTypeRuleOnlinePrice ) {
                /**
                 * @filters
                 * @todo Da documentare
                 */
                $price_rule = apply_filters( 'wpxss_stats_column_price_rule_online', __( 'Online', WPXSMARTSHOP_TEXTDOMAIN ), $price_rule_code );
            } elseif ( $price_rule_code == 'base_price' ) {
                /**
                 * @filters
                 * @todo Da documentare
                 */
                $price_rule = apply_filters( 'wpxss_stats_column_price_rule_base_price', __( 'Base price', WPXSMARTSHOP_TEXTDOMAIN ), $price_rule_code );
            } elseif ( $price_rule_code == kWPSmartShopProductTypeRuleDatePrice ) {
                /**
                 * @filters
                 * @todo Da documentare
                 */
                $price_rule = apply_filters( 'wpxss_stats_column_price_rule_date_range', __( 'Date range', WPXSMARTSHOP_TEXTDOMAIN ), $price_rule_code );
            } elseif ( isset( $roles_names[$price_rule_code] ) ) {
                $price_rule = $roles_names[$price_rule_code];
            } else {
                /**
                 * Invia il codice della regola per attività personalizzate esterne
                 *
                 * @filters
                 *
                 * @param string Decsription
                 * @param string ID key ruole
                 *
                 * @todo Da documentare
                 */
                $price_rule = apply_filters( 'wpxss_stats_column_price_rule', __( 'Unknown', WPXSMARTSHOP_TEXTDOMAIN ), $price_rule_code );
            }

            // Connessioni tipo per la categoria
            $categorySap = self::categoryConnectForSap( $item['product_category_id'] );

            $buffer .= sprintf( '"%s - %s","%s","%s","%s","%s", "%s"',
                $item['id_order'],
                $item['track_id'],
                $item['product_title'],
                $categorySap,
/*
                $item['bill_last_name'],
                $item['bill_first_name'],

                $item['bill_email'],
                $item['bill_phone'],

                $item['user_display_name'],
                WPDKUser::roleNameForUserID( $item['id_user']  ),
                //$item['user_order_display_name'],
                //WPDKUser::roleNameForUserID( $item['id_user_order']  ),
                $item['order_note'],
                $item['id_variant'],
                $model,
                $item['coupon_uniqcode'],
                sprintf( '%s %s', WPXSmartShopCurrency::currencySymbol(), WPXSmartShopCurrency::formatCurrency( $item['product_amount'] ) ),
                $price_rule,*/
                sprintf( '%s %s', WPXSmartShopCurrency::currencySymbol(), WPXSmartShopCurrency::formatCurrency( $item['amount'] ) ),
                $item['order_datetime'],
                $item['status'],
                "Todo"
            );
            $buffer .= WPDK_CRLF;
        }

       /* if ( $buffer ) {
            set_transient( 'wpxss_stats_csv_sap', $buffer );
        }*/

        $columns_row = sprintf( '"%s"', join( '","', $columns ) ) . WPDK_CRLF;
        $result      = $columns_row . $buffer;

        return $result;
    }


    public static function addExportForSapCSV( $data ){
        if ( ( $buffer = self::exportForSapCSV( $data ) ) ) {
            set_transient( 'wpxss_stats_sap_csv', $buffer );
        }
    }


    /**
     * Custom Functions
     */

    /**
     * @param $values
     */
    public static function createPlaces($values){
        global $wpdb;
        $table = WPXSmartShopStats::tableName();
        if($values){
            $wpdb->insert($table,$values);
        }

    }

    /**
     * @param $id
     * @param $pid
     * @param $value
     * @return bool
     */
    public static function updateNotes($id,$pid,$value){
        global $wpdb;
        $table = WPXSmartShopStats::tableName();

        $sql = <<<SQL
SELECT id, note
from {$table}
WHERE 1
AND id_order = {$id}
AND id_product = {$pid}
AND note <> ''
SQL;


        $places =  $wpdb->get_results($sql, ARRAY_A); //seleziono le note dal DB
        /*
         * SUl .com sembra che le statistiche siano multiple con gli stessi dati
         * Quindi è necessario aggiornare tutte le istanze che verranno fuori
         * $places[0]['id'] prendo la prima istanza come esempio e successivamente aggiorno tutte le altre con i nuovi valori
         */
        if ( $places ):
            $stats_id = $places[0]['id'];
            $textplaces = explode(",",$places[0]['note']);

            if( $textplaces != "" ):
                foreach($textplaces as $key => $textplace){

                    if(strpos($textplace,$value) > 0)
                        unset($textplaces[$key]);
                    else
                        continue;
                }
            endif;

            $new_places = implode(",",$textplaces);
                if ( !$new_places )
                    $new_places = "";

            foreach ( $places as $place):
                $stats_id = $place['id'];

                    //update
                    $update = <<<UPDATE
UPDATE {$table}
SET note = '{$new_places}'
WHERE id = {$stats_id}
UPDATE;

                $toreturn = $wpdb->query($update);
             endforeach;
        endif;
        return true;
    }

}
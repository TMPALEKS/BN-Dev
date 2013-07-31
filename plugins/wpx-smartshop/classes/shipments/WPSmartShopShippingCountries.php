<?php
/**
 * Shipping Zones Countries Manage
 *
 * @package            wpx SmartShop
 * @subpackage         WPSmartShopShippingCountries
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            06/02/12
 * @version            1.0.0
 *
 */

require_once ( 'WPSmartShopShippingCountriesViewController.php' );

class WPSmartShopShippingCountries {

    // -----------------------------------------------------------------------------------------------------------------
    // CRUD (Create, Read, Update & Delete)
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Read a specify record o records from database
     *
     * @package            WPDK (WordPress Development Kit)
     *
     * @since              1.0.0
     *
     * @static
     *
     * @param int    $id
     *  ID del record o null per tutti
     *
     * @param string $id_id
     *  Nome del campo ID
     *
     * @param string $orderby
     *  Stringa ordinamento SQL, default '', Es. ORDER BY 'name'
     *
     * @param string $where
     *  Stringa WHERE in SQL, Es. AND status = 'trash'
     *
     * @param mixed  $output
     *  Tipo di output
     *
     * @retval mixed
     *                     Riga o elenco nel formato $output
     */
    public static function read( $id = null, $id_id = 'id', $orderby = '', $where = '', $output = OBJECT ) {
        global $wpdb;

        $table = self::tableName();

        $where_cond = 'WHERE 1';

        if ( !is_null( $id ) ) {
            if ( is_numeric( $id ) ) {
                $where_cond = sprintf( 'WHERE %s = %s', $id_id, $id );
            } elseif ( is_string( $id ) ) {
                $where_cond = sprintf( 'WHERE %s = \'%s\'', $id_id, $id );
            }
        }

        if ( !empty( $where ) ) {
            $where_cond = sprintf( '%s %s', $where_cond, $where );
        }

        $sql = <<< SQL
        SELECT * FROM `{$table}`
        {$where_cond}
        {$orderby}
SQL;
        if ( !is_null( $id ) ) {
            $result = $wpdb->get_row( $sql, $output );
        } else {
            $result = $wpdb->get_results( $sql, $output );
        }
        return $result;
    }



    /**
     * Delete one or more records from database.
     * Elimina anche i relativi post meta
     *
     * @package            WPDK (WordPress Development Kit)
     *
     * @since              1.0.0
     *
     * @static
     *
     * @param int|array $ids
     * ID o array di ID da eliminare
     *
     * @param string    $id_id
     * Nome del campo ID
     *
     * @param bool      $delete_post_meta
     * Elimina la chiave '_[table name]_status dai post meta. Questa viene usata per memorizzare lo stato precedente
     * di un record, quando si ha una gestine a stati appunto: vedi 'trash' ad esempio. Vedi meotdo update per dettagli
     *
     * @retval mixed
     *                     Risultato della $wpdb->query()
     */
    public static function delete( $ids, $id_id = 'id', $delete_post_meta = true ) {
        global $wpdb;

        $table    = self::tableName();

        if ( !is_array( $ids ) ) {
            $ids = array( $ids );
        }

        if ( $delete_post_meta ) {
            $meta_key = sprintf( '_%s_status', $table );
            foreach ( $ids as $id ) {
                delete_post_meta( $id, $meta_key );
            }
        }

        $ids = implode( ',', $ids );

        $sql    = <<< SQL
		DELETE FROM `{$table}`
		WHERE {$id_id} IN({$ids})
SQL;
        $result = $wpdb->query( $sql );

        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Extra
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce il numero dei record di una tabella
     *
     * @package     WPDK (WordPress Development Kit)
     * @since       1.0.0
     *
     * @static
     * @retval int
     *              Restituisce il numero totale dei record
     */
    public static function count() {
        global $wpdb;

        $table = self::tableName();

        $sql = <<< SQL
		SELECT COUNT(*) AS count
		FROM `{$table}`
SQL;
        return absint( $wpdb->get_var( $sql ) );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Extra for trash like WordPress
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Imposta lo stato di un record a 'trash' e memorizza lo stato attuale nella post meta con chiave
     * '_[table name]_status'
     *
     * @package            WPDK (WordPress Development Kit)
     *
     * @since              1.0.0
     *
     * @static
     *
     * @param int|array $ids
     * ID o Array di ID del record
     *
     * @param string    $id_id
     * ome del campo ID
     *
     * @param string    $status
     * Nome del campo status, default 'post_status'
     *
     * @param string    $value
     * Valore dello stati, default 'trash'
     *
     * @retval mixed
     *                     Risultato della $wpdb->query()
     */
    public static function trash( $ids, $id_id = 'id', $status = 'post_status', $value = 'trash' ) {
        global $wpdb;

        $table    = self::tableName();
        $meta_key = sprintf( '_%s_status', $table );

        if ( !is_array( $ids ) ) {
            $ids = array( $ids );
        }

        /* Memorizzo lo stato precendete nella tabella options */
        foreach ( $ids as $id ) {
            $previous_status = self::status( $id, $id_id, $status );
            update_post_meta( $id, $meta_key, $previous_status );
        }

        $ids = implode( ',', $ids );

        $sql = <<< SQL
        UPDATE `{$table}`
        SET `{$status}` = '{$value}'
        WHERE {$id_id} IN({$ids})
SQL;

        $result = $wpdb->query( $sql );
        return $result;

    }

    /**
     * Repristina un record dal cestino recuperando lo stato precedente dalla chiave ''_[table name]_status'
     * nella post meta. Se non la trova pone il record in status 'unknown'
     *
     * @package            WPDK (WordPress Development Kit)
     *
     * @since              1.0.0
     *
     * @static
     *
     * @param int|array $ids
     * ID o array di ID del record da repristinare
     *
     * @param string    $id_id
     * Nome del campo ID
     *
     * @param string    $status
     * Nome del campo status
     *
     * @retval mixed
     *                     Risultato della $wpdb->query() o false se errore
     */
    public static function untrash( $ids, $id_id = 'id', $status = 'post_status' ) {
        global $wpdb;

        $table    = self::tableName();
        $meta_key = sprintf( '_%s_status', $table );
        $result   = false;

        if ( !is_array( $ids ) ) {
            $ids = array( $ids );
        }

        foreach ( $ids as $id ) {
            $previous_status = get_post_meta( $id, $meta_key, true );
            if ( empty( $previous_status ) ) {
                /* @todo Prendere il primo disponibile in base alla classe ereditaria */
                $previous_status = 'unknown';
            }
            $sql    = <<< SQL
            UPDATE `{$table}`
            SET `{$status}` = '{$previous_status}'
            WHERE {$id_id} = {$id}
SQL;
            $result = $wpdb->query( $sql );

            delete_post_meta( $id, $meta_key );
        }

        return $result;

    }

    /**
     * Legge lo stato attuale di un record
     *
     * @package            WPDK (WordPress Development Kit)
     *
     * @since              1.0.0
     *
     * @static
     *
     * @param int|array $id
     * ID del record da repristinare
     *
     * @param string    $id_id
     * Nome del campo ID
     *
     * @param string    $status
     * Nome del campo status
     *
     * @retval string
     *                     Restituisce la stringa che identifica lo stato, ritorno della $wpdb->get_var(()
     */
    private static function status( $id, $id_id = 'id', $status = 'post_status' ) {
        global $wpdb;

        $table = self::tableName();

        $sql    = <<< SQL
        SELECT `{$status}`
        FROM `{$table}`
        WHERE `{$id_id}` = $id
SQL;
        $status = $wpdb->get_var( $sql );

        return $status;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress WP List Table
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce un array con il tipo di status, la sua label e la count sul database
     *
     * @package            WPDK (WordPress Development Kit)
     *
     * @since              1.0.0
     *
     * @static
     * @retval array
     * Restituisce un array con il tipo di status, la sua label e la count sul database
     */
    public static function statusesWithCount() {
        global $wpdb;

        $statuses = self::arrayStatuses();
        $table    = self::tableName();

        $sql    = <<< SQL
        SELECT DISTINCT(`status`),
               COUNT(*) AS count
        FROM `{$table}` GROUP BY `status`
SQL;
        $result = $wpdb->get_results( $sql, ARRAY_A );

        foreach ( $result as $status ) {
            if ( !empty( $status['status'] ) ) {
                $statuses[$status['status']]['count'] = $status['count'];
            }
        }

        $statuses['all']['count'] = self::count();

        return $statuses;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Costruisce e restituisce l'array usato dall'engine WPDKForm per l'inserimento e l'editing di uno shipping
     * country
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountries
     * @since      1.0.0
     *
     * @static
     *
     * @param null $id
     *
     * @retval array
     */
    public static function fields( $id = null ) {
        if ( !is_null( $id ) ) {
            $shippingCountry = self::shippingCountry( $id, ARRAY_A );
        }

        $fields = array(
            __( 'Shipping Country Information', WPXSMARTSHOP_TEXTDOMAIN ) => array(
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'country',
                        'label' => __( 'Country', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value' => isset( $shippingCountry ) ? $shippingCountry['country'] : ''
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'continent',
                        'label' => __( 'Continent', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'  => 10,
                        'value' => isset( $shippingCountry ) ? $shippingCountry['continent'] : ''
                    ),
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'    => 'continent-select',
                        'options' => self::continentsArray()
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'zone',
                        'label' => __( 'Zone', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'  => 10,
                        'value' => isset( $shippingCountry ) ? $shippingCountry['zone'] : ''
                    ),
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'    => 'zone-select',
                        'options' => self::zonesArray()
                    ),
                ),

                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'currency',
                        'label' => __( 'Currency', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'  => 16,
                        'value' => isset( $shippingCountry ) ? $shippingCountry['currency'] : ''
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'symbol',
                        'label' => __( 'Symbol', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'  => 5,
                        'value' => isset( $shippingCountry ) ? $shippingCountry['symbol'] : ''
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'symbol_html',
                        'label' => __( 'Symbol HTML', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'  => 5,
                        'value' => isset( $shippingCountry ) ? htmlentities($shippingCountry['symbol_html'], ENT_QUOTES, 'UTF-8') : ''
                    ),
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'   => 'tax',
                        'label'  => __( 'Tax', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'   => 5,
                        'append' => '%',
                        'value'  => isset( $shippingCountry ) ? $shippingCountry['tax'] : '0.00'
                    ),
                ),

                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'isocode',
                        'label' => __( 'ISO Code', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'  => 10,
                        'value' => isset( $shippingCountry ) ? $shippingCountry['isocode'] : ''
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'code',
                        'label' => __( 'Code', WPXSMARTSHOP_TEXTDOMAIN ),
                        'size'  => 10,
                        'value' => isset( $shippingCountry ) ? $shippingCountry['code'] : ''
                    ),
                ),
            )
        );
        return $fields;
    }

    /**
     * Restituisce il nome della tabella delle zone di spedizione e dei paesi
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountries
     * @since      1.0.0
     *
     * @static
     * @retval string
     */
    public static function tableName() {
        global $wpdb;
        return sprintf('%s%s', $wpdb->prefix, WPXSMARTSHOP_DB_TABLENAME_SHIPPING_COUNTRIES);
    }


    /**
     * Restituisce l'elenco degli stati
     *
     * @static
     * @retval array
     */
    public static function arrayStatuses() {

        $statuses = array(
            'all'       => array(
                'label' => __( 'All', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            'publish'   => array(
                'label' => __( 'Publish', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            'trash'     => array(
                'label' => __( 'Trash', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            )
        );
        return $statuses;
    }

    /**
     * @static
     * @retval array
     */
    public static function arrayStatusesForSDF() {
        $statuses = self::arrayStatuses();
        $result = array();
        foreach ( $statuses as $key => $status ) {
            $result[$key] = $status['label'];
        }
        unset( $result['all'] );
        unset( $result['trash'] );

        return $result;
    }




    // -----------------------------------------------------------------------------------------------------------------
    // Database
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce le informazioni su uno shipping country con un dato ID
     *
     * @static
     *
     * @param int $id
     * @param string $output
     *
     * @retval mixed Array o Object della riga rappresentatnte uno shipping country
     */
    public static function shippingCountry( $id, $output = OBJECT ) {
        global $wpdb;

        $table = self::tableName();

        $sql = <<< SQL
        SELECT * FROM `{$table}`
        WHERE id = {$id}
SQL;
        $row = $wpdb->get_row( $sql, $output );
        return $row;
    }

    /**
     * Commodity estrae id e country dalla tabella. Ricordo che questa ha tante altre informazioni utili,
     * in questo caso questa è usata soprattutto per popolare combo con id/desc
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountries
     * @since      1.0.0
     *
     * @static
     * @retval mixed
     *             Array con array [id] e [country]
     */
    public static function countries() {
        global $wpdb;

        $tableName = self::tableName();

        $sql     = <<< SQL
        SELECT * FROM `{$tableName}`
        ORDER BY country
SQL;
        $results = $wpdb->get_results( $sql, ARRAY_A );
        return $results;
    }

    /**
     * Crea o aggiorna (esegue un delta) la tabella delle zone di spedizione e dei paesi
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountries
     * @since      1.0.0
     *
     * @static
     *
     */
    public static function updateTable() {
        if ( !function_exists( 'dbDelta' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }
        $dbDeltaTableFile = sprintf( '%s%s', WPXSMARTSHOP_PATH_DATABASE, kWPSmartShopShippingCountriesTableFilename );
        $file             = file_get_contents( $dbDeltaTableFile );
        $sql              = sprintf( $file, self::tableName() );
        @dbDelta( $sql );
    }

    /**
     * Popola la tabella {wp_prefix}_wpss_shipping_countries - con le indicazioni 'nazioni'->valuta
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountries
     * @since      1.0.0
     *
     * @static
     *
     */
    public static function loadDataTable() {
        global $wpdb;

        $tableName = self::tableName();

        $sql   = <<< SQL
        SELECT COUNT(*) FROM {$tableName}
SQL;
        $count = $wpdb->get_var( $sql );
        if ( $count == 0 ) {
            $insertFile = sprintf( '%s%s', WPXSMARTSHOP_PATH_DATABASE, kWPSmartShopShippingCountriesValuesFilename );
            $file       = file_get_contents( $insertFile );
            $sql        = sprintf( $file, $tableName );
            $wpdb->query( $sql );
        }
    }

    /**
     * Restituisce la zona in base all'id di un paese
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountries
     * @since      1.0.0
     *
     * @static
     * @param $id
     * @retval mixed
     */
    public static function zoneShippingCountry( $id ) {
        global $wpdb;

        $table = self::tableName();

        $sql    = <<< SQL
        SELECT zone
        FROM `{$table}`
        WHERE id = {$id}
SQL;
        $result = $wpdb->get_var( $sql );
        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Shorthand
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce l'iva del paese con $id
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountries
     * @since      1.0.0
     *
     * @todo Questo potrebbe essere messa in cache
     * @static
     *
     * @param $id
     *
     * @retval mixed
     */
    public static function vat( $id ) {
        global $wpdb;

        $table = self::tableName();

        $sql    = <<< SQL
        SELECT tax
        FROM `{$table}`
        WHERE id = {$id}
SQL;
        $result = $wpdb->get_var( $sql );
        return $result;
    }

    /**
     * Restituisce l'iva del paese impostato nel backend che corrisponde al paese dove si trova questo shop
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountries
     * @since      1.0.0
     *
     * @static
     * @retval float
     */
    public static function vatShop() {
        $id_country_shop = WPXSmartShop::settings()->setting( 'general', 'shop_country' );
        $vat             = floatval( self::vat( $id_country_shop ) );
        return $vat;
    }

    public static function currencySymbolShop() {
        $id_country_shop = WPXSmartShop::settings()->setting( 'general', 'shop_country' );
        $country_record  = self::shippingCountry( $id_country_shop );
        return $country_record->symbol;
    }

    public static function currencySymbolHTMLShop() {
        $id_country_shop = WPXSmartShop::settings()->setting( 'general', 'shop_country' );
        $country_record  = self::shippingCountry( $id_country_shop );
        return $country_record->symbol_html;
    }

    public static function currencyShop() {
        $id_country_shop = WPXSmartShop::settings()->setting( 'general', 'shop_country' );
        $country_record  = self::shippingCountry( $id_country_shop );
        return $country_record->currency;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Crud
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Inserisce un paese con tutte le sue informazioni
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountries
     * @since      1.0.0
     *
     * @static
     * @retval mixed
     */
    public static function create() {
        global $wpdb;

        $values = array(
            'country'        => esc_html( $_POST['country'] ),
            'isocode'        => esc_html( $_POST['isocode'] ),
            'currency'       => esc_html( $_POST['currency'] ),
            'symbol'         => esc_html( $_POST['symbol'] ),
            'symbol_html'    => esc_html( $_POST['symbol_html'] ),
            'code'           => esc_html( $_POST['code'] ),
            'zone'           => esc_html( $_POST['zone'] ),
            'tax'            => WPXSmartShopCurrency::formatPercentage( $_POST['tax'], true ),
            'continent'      => esc_html( $_POST['continent'] ),
        );

        $result = $wpdb->insert( self::tableName(), $values );

        return $result;
    }

    /**
     * Aggiorna uno shipping country
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountries
     * @since      1.0.0
     *
     * @static
     * @retval mixed
     */
    public static function update() {
        global $wpdb;
        $values = array(
            'country'        => esc_html( $_POST['country'] ),
            'isocode'        => esc_html( $_POST['isocode'] ),
            'currency'       => esc_html( $_POST['currency'] ),
            'symbol'         => esc_html( $_POST['symbol'] ),
            'symbol_html'    => esc_html( $_POST['symbol_html'] ),
            'code'           => esc_html( $_POST['code'] ),
            'zone'           => esc_html( $_POST['zone'] ),
            'tax'            => WPXSmartShopCurrency::formatPercentage( $_POST['tax'], true ),
            'continent'      => esc_html( $_POST['continent'] ),
        );

        $where = array(
            'id' => absint( $_POST['id'] )
        );

        if ( empty( $formats ) ) {
            $result = $wpdb->update( self::tableName(), $values, $where );
        } else {
            $where_formats = array( '%d' );
            $result        = $wpdb->update( self::tableName(), $values, $where, $formats, $where_formats );
        }


        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Database distinct group
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restistuisce la lista dei continenti raggruppata e ordinata
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountries
     * @since      1.0.0
     *
     * @static
     * @retval mixed
     */
    public static function continents() {
        global $wpdb;

        $tableName = self::tableName();

        $sql    = <<< SQL
        SELECT continent
        FROM {$tableName}
        GROUP BY continent
        ORDER BY continent
SQL;
        $result = $wpdb->get_results( $sql );

        return $result;
    }

    /**
     * Esegue un GROUP BY sulle zone inserite nella tabella delle nazioni
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountries
     * @since      1.0.0
     *
     * @static
     * @retval mixed
     */
    public static function zones() {
        global $wpdb;

        $tableName = self::tableName();

        $sql = <<< SQL
        SELECT zone
        FROM {$tableName}
        GROUP BY zone
        ORDER BY zone
SQL;
        $result = $wpdb->get_results($sql);

        return $result;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // WPDK SDF Form
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Short hand, restituisce un array da usare in SDF per la visualizzazione di un combo select
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountries
     * @since      1.0.0
     *
     * @static
     * @retval array
     */
    public static function countriesForSelectMenu() {
        $results   = WPSmartShopShippingCountries::countries();
        $countries = array(
            '' => __( 'Select a country', WPXSMARTSHOP_TEXTDOMAIN )
        );
        foreach ( $results as $country ) {
            $countries[$country['id']] = $country['country'];
        }

        return $countries;
    }

    /**
     * Versione estese, che mostra più informazioni di countriesForSelectMenu()
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountries
     * @since      1.0.0
     *
     * @todo Rinominare in arrayCountriesForSDF()
     *
     * @static
     * @retval array
     */
    public static function countriesForSelectMenuExtends() {
        $results   = WPSmartShopShippingCountries::countries();
        $countries = array(
            '' => __( 'Select a country', WPXSMARTSHOP_TEXTDOMAIN )
        );
        foreach ( $results as $country ) {
            $countries[$country['id']] = sprintf('%s - (%s) %s: %s', $country['country'], $country['currency'], __( 'VAT', WPXSMARTSHOP_TEXTDOMAIN ), $country['tax']);
        }

        return $countries;
    }

    /**
     * Array delle zone
     *
     * @todo Rinominare in arrayZone()
     *
     * @static
     * @retval array
     */
    public static function zonesArray() {
        $zones  = array();
        $result = self::zones();
        foreach ( $result as $zone ) {
            if ( !empty( $zone->zone ) ) {
                $zones[$zone->zone] = $zone->zone;
            }
        }
        return $zones;
    }

    /**
     * Array dei continenti
     *
     * @todo Rinominare in arrayContinents
     *
     * @todo Questa (come per zone) potrebbe essere inutile in quanto si potrebbe chiamare la primitiva che fa la
     * select sul database dicendogli di restituire un array di array invece che un array di object.
     *
     * @static
     * @retval mixed
     */
    public static function continentsArray() {
        $continents = array();
        $result     = self::continents();
        foreach ( $result as $continent ) {
            $continents[$continent->continent] = $continent->continent;
        }
        return $continents;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // UI
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Costruisce il combo menu select per i filtri nei list table
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountriesViewController
     * @since      1.0.0
     *
     * @static
     *
     * @param string $id_select Stringa da usare per l'attributo name e id del tag select
     * @param string $selected  Identificativo dell'elemento option selezionato
     *
     * @retval string
     */
    public static function selectFilterContinents( $id_select, $selected = '' ) {
        $continents = self::continents();

        $options = '';
        foreach ( $continents as $continent ) {
            if ( !empty( $continent->continent ) ) {
                $options .= sprintf( '<option %s value="%s">%s</option>', selected( $continent->continent, $selected, false ), $continent->continent, ucfirst( $continent->continent ) );
            }
        }

        $label = __( 'Filter by Continent', WPXSMARTSHOP_TEXTDOMAIN );

        $html = <<< HTML
        <select name="{$id_select}" id="{$id_select}">
            <option value ="">{$label}</option>
            {$options}
        </select>
HTML;
        return $html;
    }

    /**
     * Costruisce il combo menu select per i filtri nei list table
     *
     * @package    wpx SmartShop
     * @subpackage WPSmartShopShippingCountriesViewController
     * @since      1.0.0
     *
     * @static
     *
     * @param string $id_select Stringa da usare per l'attributo name e id del tag select
     * @param string $selected  Identificativo dell'elemento option selezionato
     *
     * @retval string
     */
    public static function selectFilterZones( $id_select, $selected = -1 ) {
        $zones = self::zonesArray();

        $options = '';
        foreach ( $zones as $zone ) {
            $options .= sprintf( '<option %s value="%s">%s</option>', selected( $zone, $selected, false ), $zone, $zone );
        }

        $label = __( 'Filter by Zone', WPXSMARTSHOP_TEXTDOMAIN );

        $html = <<< HTML
        <select name="{$id_select}" id="{$id_select}">
            <option value ="">{$label}</option>
            {$options}
        </select>
HTML;
        return $html;

    }


}

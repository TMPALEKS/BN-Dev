<?php
/**
 * Gateway Ajax
 *
 * @package            Blue Note Milano
 * @subpackage         BNMExtendsAjax
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (C)2011 Saidmade Srl.
 * @created            18/11/11
 * @version            1.0
 *
 */

if ( wpdk_is_ajax() ) {

    class BNMExtendsAjax {

        // -------------------------------------------------------------------------------------------------------------
        // Statics: method array to register
        // -------------------------------------------------------------------------------------------------------------
        private static function actionsMethods() {
            $actionsMethods = array(
                'action_artist_by_title'                        => true,
                'action_user_by_email'                          => true,
                'action_user_age'                               => true,
                'action_mailchimp_store_address'                => true,
                'action_summary_order_apply_discount'           => true,

                'action_summary_order_changed_id_country'       => true,
                'action_summary_order_changed_id_carrier'       => true,

                'bnm_action_product_title'                      => true,

                'wpph_reservations'                             => true, //Gestione reservation Box Office Placeholder
                'wpph_remove_reservation'                       => true,
                'wpph_update_who'                               => true,
                'wpph_datatables_update_places_notes'           => true,
                'wpph_datatables_summary'                       => true,
                'wpph_datatables_delete_places'                 => true
            );
            return $actionsMethods;
        }

        // -------------------------------------------------------------------------------------------------------------
        // Register Ajax methods
        // -------------------------------------------------------------------------------------------------------------
        public static function registerAjaxMethods() {
            $actionsMethods = self::actionsMethods();
            foreach ( $actionsMethods as $method => $nopriv ) {
                add_action( 'wp_ajax_' . $method, array( __CLASS__, $method ) );
                if ( $nopriv ) {
                    add_action( 'wp_ajax_nopriv_' . $method, array( __CLASS__, $method ) );
                }
            }
        }

        // -------------------------------------------------------------------------------------------------------------
        // Actions methods
        // -------------------------------------------------------------------------------------------------------------

        // -------------------------------------------------------------------------------------------------------------
        // Artists
        // -------------------------------------------------------------------------------------------------------------

        /**
        * Utilizzato per valorizzare il text input usato insieme a jQuery autocomplete per la selezione di un artista.
        * In questo caso viene alterata sia la normale query di WordPress che l'output tramite jQuery validate.
        *
        * @package            Blue Note Milano
        * @subpackage         BNMExtendsAjax
        * @since              1.0.0
        *
        */
        public static function action_artist_by_title() {

           /* Change standard where condiction */
           add_filter('posts_where', 'title_like_posts_where', 10, 2);

           function title_like_posts_where( $where, &$wp_query ) {
                global $wpdb;
                if ( $post_title_like = $_REQUEST['term'] ) {
                    $where .=
                        ' AND ' . $wpdb->posts . '.post_title LIKE \'%' . esc_sql( like_escape( $post_title_like ) ) .
                            '%\'';
                }
                return $where;
            }

           $args    = array(
               'post_type'        => kBNMExtendsArtistPostTypeKey,
               'suppress_filters' => 0
           );
           $artists = get_posts( $args );

           $result = array();

           foreach ( $artists as $artist ) {
               $result[] = array(
                   'id'    => $artist->ID,
                   'value' => $artist->post_title,
                   'icon'  => BNMExtendsArtistPostType::thumbnailSrc( $artist->ID )
               );
           }

           /* Remove filter */
           remove_filter( 'posts_where', 'title_like_posts_where' );

           echo json_encode( $result );

           die();
       }


        // -------------------------------------------------------------------------------------------------------------
        // Users
        // -------------------------------------------------------------------------------------------------------------

        /**
         * Restituisce alcune informazioni utente basandosi sulla email.
         * Questa viene usata nella fase di checkout per trovare indirizzo e altre info a partire da una email.
         *
         * @package            Blue Note Milano
         * @subpackage         BNMExtendsAjax
         * @since              1.0.0
         *
         */
        public static function action_user_by_email() {
            global $wpdb;

            $email = $_REQUEST['term'];

            /**
             * Seleziono tutti gli utenti 'confermati' nella tabella temporanea che hanno ID in comune con quella di
             * WordPress ed email 'simile' a quella che sto cercando
             */

            $userTemporaryTable = BNMExtendsUser::tableName();

            $sql   = <<< SQL
SELECT users.*, tmpUsers.*
FROM `{$wpdb->users}` AS users
RIGHT JOIN `{$userTemporaryTable}` AS tmpUsers
ON users.ID = tmpUsers.id_user
WHERE tmpUsers.status = 'confirmed'
AND users.user_email LIKE '%{$email}%'
SQL;
            $users = $wpdb->get_results( $sql );

            $result = array();
            $roles = BNMExtendsUser::roles();

            foreach ( $users as $user ) {
                $wp_user  = new WP_User( $user->ID );
                $key_role = key( $wp_user->roles );

                $result[] = array(
                    'id'               => $user->ID,
                    'role'             => $roles[$wp_user->roles[$key_role]]['description'],
                    'value'            => $user->user_email,
                    'bill_first_name'  => $user->first_name,
                    'bill_last_name'   => $user->last_name,
                    'bill_address'     => $user->bill_address,
                    'bill_country'     => $user->bill_country,
                    'bill_town'        => $user->bill_town,
                    'bill_zipcode'     => $user->bill_zipcode,
                    'bill_phone'       => $user->bill_phone,
                    'bill_mobile'      => $user->bill_mobile,
                );
            }

            echo json_encode( $result );
            die();
        }

        /**
         * Restituisce l'età di un utente rispetto ad oggi
         *
         * @package            Blue Note Milano
         * @subpackage         BNMExtendsAjax
         * @since              1.0.0
         *
         */
        function action_user_age() {
            $birthdate = $_POST['birth_date'];
            $age       = BNMExtendsUser::ageFromDate( $birthdate );
            echo $age;
            die();
        }


        // -------------------------------------------------------------------------------------------------------------
        // MailChimp integration
        // -------------------------------------------------------------------------------------------------------------

        /**
         * Iscrizione di un utente alla NewsLetter gestita da Mail Chimp
         *
         * @package            Blue Note Milano
         * @subpackage         BNMExtendsAjax
         * @since              1.0.0
         *
         */
        public static function action_mailchimp_store_address() {
            echo BNMExtendsWidgetNewsletter::storeAddress();
            die();
        }

        // -------------------------------------------------------------------------------------------------------------
        // Summary Order
        // -------------------------------------------------------------------------------------------------------------

        /**
         * Chiamata dalla pagina di checkout per gestire gli sconti personalizzati di Blue Note.
         * Questa applica lo sconto in base al combo menu che è aggiunto dinamicamente tramite un filtre al summary order
         * del check out
         *
         * @package            Blue Note Milano
         * @subpackage         BNMExtendsAjax
         * @since              1.0.0
         *
         */
        public static function action_summary_order_apply_discount() {
            $id_custom_discount = esc_attr( $_POST['id_custom_discount'] );
            $id_product_key     = esc_attr( $_POST['id_product_key'] );

            if ( WPDKUser::hasCaps( array ( 'bnm_cap_offline' ) ) ) {
                /* Forza la registrazione dei filtri usata per l'operatore box office */
                BNMExtendsSummaryOrder::registerBoxOffice();
            }

            WPXSmartShopSession::productCustomDiscount( $id_product_key, $id_custom_discount );

            $content = WPXSmartShopSummaryOrder::summaryOrder();

            $json = array(
                'content' => $content
            );

            echo json_encode( $json );

            die();
        }

        /**
         * Chiamata quando viene cambiato il combo select del paese
         *
         * @package            Blue Note Milano
         * @subpackage         BNMExtendsAjax
         * @since              1.0.0
         *
         * @static
         *
         */
        public static function action_summary_order_changed_id_country() {

            if ( WPDKUser::hasCaps( array( 'bnm_cap_offline' ) ) ) {
                /* Forza la registrazione dei filtri usata per l'operatore box office */
                BNMExtendsSummaryOrder::registerBoxOffice();
            }
            WPXSmartShopSession::orderShippingCountry( absint( $_POST['id_country'] ) );
            $content = WPXSmartShopSummaryOrder::summaryOrder();

            $json = array(
                'content' => $content
            );

            echo json_encode( $json );

            die();
        }

        public static function action_summary_order_changed_id_carrier() {

            if ( WPDKUser::hasCaps( array ( 'bnm_cap_offline' ) ) ) {
                /* Forza la registrazione dei filtri usata per l'operatore box office */
                BNMExtendsSummaryOrder::registerBoxOffice();
            }
            WPXSmartShopSession::orderShippingCarrier( absint( $_POST['id_carrier']) );
            $content = WPXSmartShopSummaryOrder::summaryOrder();

            $json = array(
                'content' => $content
            );

            echo json_encode( $json );
            die();
        }

        public static function bnm_action_product_title() {
            global $wpdb;

              /* ID, name, email... */
            $pattern = esc_attr( $_POST['term'] );

            if ( !empty( $pattern ) ) {

                $sql = <<< SQL
SELECT products.ID, products.post_title

FROM
wpbn_posts AS products,
wpbn_terms AS terms,
wpbn_term_taxonomy AS tt,
wpbn_term_relationships AS tr

WHERE 1
AND ( terms.slug = 'biglietti' OR terms.slug = 'bigliettialternativi' OR terms.slug = 'biglietti-di-ingresso' OR terms.slug = 'brunchcumulativo' OR terms.slug = 'brunchbambinosingolo' OR terms.slug = 'brunchadultosingolo-biglietti-di-ingresso' )
AND tt.taxonomy = 'wpss-ctx-product-type' AND tt.term_id = terms.term_id
AND tr.term_taxonomy_id = tt.term_taxonomy_id
AND tr.object_id = products.ID
AND products.post_title LIKE '%{$pattern}%'

ORDER BY products.post_title ASC
SQL;

                $products = $wpdb->get_results( $sql );

                if ( !empty( $products ) ) {
                    $result = array();
                    foreach ( $products as $product ) {
                        $result[] = array(
                            'id'    => $product->ID,
                            'value' => $product->post_title
                        );
                    }
                    echo json_encode( $result );
                }
            }
            die();
        }

        /**
         * Funzione che provvede a registrare i tavoli e creare un ordine
         * Chiamata dal frontend via Ajax
         */
        public static function wpph_reservations(){
            $results = array();
            $user = "";

            if( $_POST ):
                $places = $_POST['tablelist'];
                if ( isset( $_POST['user_id'] ) && ($_POST['user_id'] != "") && BNMExtendsUser::userExistsWithUniqiD($_POST['user_id']) ){
                    $user = $_POST['user_id'];
                }
                else{
                    $current_user = wp_get_current_user();
                    $user = $current_user->ID;
                }

                foreach($places as $place):

                    $start_datetime =  new DateTime(str_replace("/", "-", $_POST['date_start'] ));
                    $expiry_datetime =  new DateTime(str_replace("/", "-", $_POST['date_expiry'] ));

                    $reserve = WPPlaceholdersReservations::doReservation(
                        $place,
                        $start_datetime->format('Y-m-d H:i:s'),
                        $expiry_datetime->format('Y-m-d H:i:s'),
                        $user,
                        $note = $_POST['note']
                    ) ;

                    $results[] = $reserve ? $place : false;
                endforeach;
                /**
                $stats_places = implode(", ", $places);
                $values = array(
                    'id_product'    =>  $_POST['product_id'],
                    'product_title' =>  $_POST['product_title'],
                    'note'          =>  $stats_places
                );
                BNMExtendsStats::createPlaces($values); @Todo valutare se serve
                 * */

            endif;

            echo json_encode($results);
            exit();
        }

        /**
         * Cancella prenotazioni
         */
        public static function wpph_remove_reservation(){

             if( $_POST ):
                $place = $_POST['tablelist'];
                $start_datetime =  new DateTime(str_replace("/", "-", $_POST['date_start'] ));
                $expiry_datetime =  new DateTime(str_replace("/", "-", $_POST['date_expiry'] ));
                $aplace = WPPlaceholdersPlaces::place($place);
                //var_dump($aplace);
                BNMExtendsPlaceHolder::deleteReservation(
                    $aplace->id,
                    $start_datetime->format('Y-m-d H:i:s'),
                    $expiry_datetime->format('Y-m-d H:i:s')
                );
                $results = $place;
                //BNMExtendsStats::updateNotes($order_id,$product_id,trim($place));
             endif;

            echo json_encode($results);
            exit();

        }

        public static function wpph_datatables_update_places_notes(){
            $results = array();

            if( $_POST ):
                $places = $_POST['tablelist'];
                $note = $_POST['note'];
                $id_order = $_POST['order_id'];


                foreach($places as $place):

                    $aplace = WPPlaceholdersPlaces::place(trim($place));

                    $start_datetime =  new DateTime(str_replace("/", "-", $_POST['date_start'] ));
                    $expiry_datetime =  new DateTime(str_replace("/", "-", $_POST['date_expiry'] ));

                    if( $id_order > 0)
                        $reserve = BNMExtendsOrders::updateOrderNotes($id_order, $note);
                    else
                        $reserve = BNMExtendsPlaceHolder::updateNotes(
                            $aplace->id,
                            $start_datetime->format('Y-m-d H:i:s'),
                            $expiry_datetime->format('Y-m-d H:i:s'),
                            $note
                         ) ;

                    $results[] = $reserve ? $place : false;

                endforeach;
                /**
                $stats_places = implode(", ", $places);
                $values = array(
                'id_product'    =>  $_POST['product_id'],
                'product_title' =>  $_POST['product_title'],
                'note'          =>  $stats_places
                );
                BNMExtendsStats::createPlaces($values); @Todo valutare se serve
                 * */

            endif;

            echo json_encode($results);
            exit();
        }

/**
* Risponde al comando Datatables Edit
*/
        public static function wpph_update_who(){
            //@Todo
        }
/**
* Risponde al comando Datatables Delete
 */
        public static function wpph_datatables_delete_places(){
            global $wpdb;
            $date_start = "";
            $date_expiry = "";
            $tablelist = array();
            $product_id = "";
            $order_id = "";

            $toReturn = array(
                    'error' => '',
                    'success' => '',
                    'data'  => ''
            );


            /**
             * Estrazione di tutti i dati in $_POST
             */
            extract($_POST);

            $start_datetime =  new DateTime(str_replace("/", "-", $date_start ));
            $expiry_datetime =  new DateTime(str_replace("/", "-", $date_expiry ));

            if($tablelist){


                foreach($tablelist as $place){

                    $aplace = WPPlaceholdersPlaces::place(trim($place));

                    $deletion = BNMExtendsPlaceHolder::deleteReservation(
                        $aplace->id,
                        $start_datetime->format('Y-m-d H:i:s'),
                        $expiry_datetime->format('Y-m-d H:i:s')
                    );
                    if($order_id) //Se c'è anche l'ordine vado a modificare i valori sulle statistiche
                        BNMExtendsStats::updateNotes($order_id,$product_id,trim($place));
                    else //altrimenti modifico le note se sono state aggiornate
                        BNMExtendsPlaceHolder::updateNotes( $aplace->id,
                            $start_datetime->format('Y-m-d H:i:s'),
                            $expiry_datetime->format('Y-m-d H:i:s'),
                            $_POST['note']
                        );
                }
                $toReturn['success'] = "I dati sono stati eliminati";
            }
            else{
                $toReturn['success'] = "Non ci sono dati da eliminare";
            }
            echo json_encode($toReturn);
            exit();
        }

/**
* Crea il sommario
 */
        public static function wpph_datatables_summary(){
            global $wpdb;

            $json = "";

            $id_product = $_POST['id'];

            /* Catch filtro spettacolo/prodotto */
            $product = null;

            $product    = get_post( $id_product );


            /* Eseguo select se spettacolo selezionato */
            if ( !is_null( $product ) ):

                $title = $product->post_title;

                $time        = substr( $title, 0, 16 );
                $mktime      = WPDKDateTime::makeTimeFrom( 'd/m/Y H:i', $time );
                $date_start  = $mktime;
                $date_expiry = $mktime + 60 * 60 * 2;
                $date_start  = date( MYSQL_DATE_TIME, $date_start );
                $date_expiry = date( MYSQL_DATE_TIME, $date_expiry );


                $table_stats    = WPXSmartShopStats::tableName();
                $table_orders   = WPXSmartShopOrders::tableName();
                $table_coupons  = WPXSmartShopCoupons::tableName();
                //$table_reservations = WPPlaceholdersReservations::tableName();
                $table_products = $wpdb->posts;
                $table_places   = WPPlaceholdersPlaces::tableName();

                $with_dinner = BNMEXTENDS_WITH_DINNER_RESERVATION_KEY;


                $sql_places = <<<SQL

                SELECT
                    date_start,
                    date_expiry,
                    users_orders.display_name AS user_order_display_name,
                    places.name as places,
                    users_orders.user_email,
                    users_orders.id AS user_id,
                    note

                FROM wpbn_wpph_reservations AS res

                LEFT JOIN {$wpdb->users} AS users_orders ON  id_who = users_orders.ID
                LEFT JOIN {$wpdb->usermeta} AS usermeta ON  id_who = usermeta.user_id AND usermeta.meta_key = '{$wpdb->prefix}_capabilities'
                LEFT JOIN {$table_places} As places ON id_place = places.id

                WHERE 1
                AND TIMESTAMP(date_start) >= TIMESTAMP("{$date_start}")
                AND TIMESTAMP(date_expiry) <= TIMESTAMP("{$date_expiry}")
                AND  res.status = 'publish'
                ORDER BY user_order_display_name
SQL;

                $sql_orders = <<<SQL
                SELECT

                    "" as counter,
                    users_orders.display_name AS user_order_display_name,
                    stats.note as places_notes,
                    users_orders.user_email,
                    orders.id as orders_id,
                    orders.note AS order_note,
                    orders.id_user_order AS user_id

                FROM {$table_stats} AS stats

                LEFT JOIN {$table_orders} AS orders ON orders.id = stats.id_order

                LEFT JOIN {$table_products} AS products ON products.ID = stats.id_product
               /*  LEFT JOIN {$wpdb->users} AS users ON orders.id_user = users.ID */
                LEFT JOIN {$wpdb->users} AS users_orders ON orders.id_user_order = users_orders.ID
                LEFT JOIN {$wpdb->usermeta} AS usermeta ON orders.id_user = usermeta.user_id AND usermeta.meta_key = '{$wpdb->prefix}_capabilities'

                WHERE products.id = {$id_product}
                AND orders.status = 'confirmed'
                AND stats.status <> 'trash'
                AND stats.model = '{$with_dinner}'
                AND stats.note <> ''

                GROUP BY user_order_display_name, orders_id
                ORDER BY   user_order_display_name
SQL;

                $data_places = $wpdb->get_results( $sql_places, ARRAY_N );

                $data_orders = $wpdb->get_results( $sql_orders, ARRAY_N );




                $places_list_place = array();
                $places_list_orders = array();
                $places_results = array();

                //Scremo i dataplaces
                if( !empty( $data_places ) ){
                    foreach($data_places as $item):
                        if( $item[0] != "" && $item[1] != "" ){
                            $places_list_place[] = $item[3];
                        }
                    endforeach;
                }

                // Scremo i dataorders
                if( !empty( $data_orders ) ){
                    foreach($data_orders as $j => $item):

                        $places2 = explode(",",$item[2]);

                        $newitem = "";
                        $itemcount = 0;
                        $size = 0;

                        foreach($places2 as $k => $place){

                            $txt = explode("-",$place);
                            $sizeval = BNMExtendsPlaceHolder::occupationByPlace($txt[1]);
                            $size += intval($sizeval[0]['place_size']);

                            if( !empty( $places_list_place ) ){

                                $key = array_search($txt[1],$places_list_place);

                                if( $key >= 0 ){
                                    array_splice( $places_list_place,$key,1 ); //Elimino i doppioni dalla lista dei tavoli
                                }
                            }

                            $newitem .= ($itemcount++ > 0 ) ? ", " . $txt[1] : $txt[1];
                        }
                        $item[0] = $size;

                        $item[2] = $newitem;

                        $data_orders[$j] = $item;

                    endforeach;
                }

                //Creo i dati finali
                $groups = array();
                $size = 0;

                foreach($data_places as $key => $item){
                    $sizeval = BNMExtendsPlaceHolder::occupationByPlace($item[3]);
                    $size = intval($sizeval[0]['place_size']);
                    if( !in_array($item[3],$places_list_place ) )
                        unset($data_places[$key]);
                    else{
                        $key = $item[5];
                        if (!isset($groups[$key])) {
                            $groups[$key] = array(
                               // 0 => $item[0],
                               // 1 => $item[1],
                                1 => $item[2],
                                2 => $item[3],
                                3 => $item[4],
                                4 => "",
                                5 => $item[6],
                                6 => $item[5],
                                0 => 1 * $size,
                            );
                        } else {
                          //  $groups[$key][0] = $item[0];
                          //      $groups[$key][1] = $item[1];
                            $groups[$key][1] = $item[2];
                            $groups[$key][2] .= ", " . $item[3];
                            $groups[$key][3] = $item[4];
                            $groups[$key][4] = "";
                            $groups[$key][5] = $item[6];
                            $groups[$key][6] = $item[5];
                            $groups[$key][0] += 1 * $size;
                        }
                    }
                }


                $toReturn['aaData'] = array_merge($data_orders,$groups);
                $json = json_encode($toReturn);

        endif;
            echo $json;
            die();
        }

     }

    BNMExtendsAjax::registerAjaxMethods();
}
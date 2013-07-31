<?php
/**
 * @class              WPXSmartShopMemberships
 *
 * @description        Membership manage
 *
 * @package            wpx SmartShop
 * @subpackage         memberships
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            22/02/12
 * @version            1.0.0
 *
 */

require_once ( 'wpxss-memberships-viewcontroller.php' );

class WPXSmartShopMemberships extends WPDKDBTable {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce il nome della tabella Coupon
     *
     * @package    wpx SmartShop
     * @subpackage WPXSmartShopMemberships
     * @since      1.0.0
     *
     * @static
     * @retval string
     */
    public static function tableName() {
        global $wpdb;
        return sprintf( '%s%s', $wpdb->prefix, WPXSMARTSHOP_DB_TABLENAME_MEMBERSHIPS );
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Costruisce e restituisce l'array usato dall'engine WPDKForm per l'inserimento e l'editing di una membership
     *
     * @package    wpx SmartShop
     * @subpackage WPXSmartShopMemberships
     * @since      1.0.0
     *
     * @static
     *
     * @param null $id
     *
     * @retval array
     */
    public static function fields( $id = null ) {
        $membership    = null;
        $caps          = array();
        $role_previous = '';
        $user_email    = '';

        if ( !is_null( $id ) ) {
            $membership = self::record( self::tableName(), absint( $id ) );
            $caps       = explode( ',', $membership->caps );

            /* Edit only: Previous role */
            $role_previous = array(
                'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                'name'    => 'role_previous',
                'label'   => __( 'Previous Role', WPXSMARTSHOP_TEXTDOMAIN ),
                'options' => WPDKUser::arrayRolesForSDF(),
                'value'   => $membership->role_previous,
                'append'  => __( 'When this subscription is over, the user back with this role.', WPXSMARTSHOP_TEXTDOMAIN ),
            );

            $users = get_users( array( 'include' => $membership->id_user ) );
            $user = $users[0];
            $user_email = sprintf( '%s (%s)', $user->display_name, $user->user_email );
        }

        /**
         * Filtro sule capabilities
         *
         * @filters
         *
         * @param array $caps Array con la lista delle capabilities disponibili in WordPress,
         *                    scorrendo tutti i ruoli presenti ed estraendo le capabilities.
         */
		$allCapabilities = apply_filters( 'wpss_product_membership_capabilities_list', WPDKUser::allCapabilities() );

        $index          = 0;
        $wpCapabilities = array();
        foreach ( $allCapabilities as $key => $cap ) {
            $wpCapabilities[] = array(
                'type'      => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                'walker'    => false,
                'name'      => 'caps[]',
                'label'     => $cap,
                'value'     => $key,
                'append'    => ( $index++ % 2 ) ? '<br/>' : '',
                'checked'   => !is_null( $membership ) ? ( in_array( $key, $caps ) ? $key : '' ) : ''
            );
        }

		$wpCapabilities = array(
			'group' => $wpCapabilities,
            'label' => __( 'Capabilities', WPXSMARTSHOP_TEXTDOMAIN ),
			'class' => 'wpss-membership-capabilities-box'
		);

        $date_start = date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        $date_expired = date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ), time() + 60*60*24*365 );

        $fields = array(
            __( 'Membership information', WPXSMARTSHOP_TEXTDOMAIN )   => array(
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_DATETIME,
                        'label'     => __( 'Date start', WPXSMARTSHOP_TEXTDOMAIN ),
                        'name'      => 'date_start',
                        'size'      => 18,
                        'not null'  => true,
                        'value'     => !is_null( $membership ) ? WPDKDateTime::formatFromFormat( $membership->date_start, 'Y-m-d H:i:s', __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) ) : $date_start
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_DATETIME,
                        'label'     => __( 'Date expired', WPXSMARTSHOP_TEXTDOMAIN ),
                        'name'      => 'date_expired',
                        'size'      => 18,
                        'not null'  => true,
                        'value'     => !is_null( $membership ) ? WPDKDateTime::formatFromFormat( $membership->date_expired, 'Y-m-d H:i:s', __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) ) : $date_expired
                    ),
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'    => 'user_email',
                        'data'    => array(
                            'autocomplete_action'   => 'wpdk_action_user_by',
                            'autocomplete_target'   => 'id_user'
                        ),
                        'size'  => 64,
                        'label'   => __( 'Apply to user', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'   => $user_email
                    ),
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_HIDDEN,
                        'name'    => 'id_user',
                        'value'   => !is_null( $membership ) ? $membership->id_user : ''
                    ),
                ),
//                array(
//                    array(
//                        'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
//                        'name'    => 'id_user',
//                        'label'   => __( 'Apply to user', WPXSMARTSHOP_TEXTDOMAIN ),
//                        'options' => WPDKUser::arrayUserForSDF(),
//                        'value'   => !is_null( $membership ) ? $membership->id_user : ''
//                    )
//                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'    => 'role',
                        'label'   => __( 'Role', WPXSMARTSHOP_TEXTDOMAIN ),
                        'options' => WPDKUser::arrayRolesForSDF(),
                        'value'   => !is_null( $membership ) ? $membership->role : ''
                    )
                ),

                $wpCapabilities,

                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'    => 'status',
                        'label'   => __( 'Status', WPXSMARTSHOP_TEXTDOMAIN ),
                        'options' => self::arrayStatusesForSDF( self::arrayStatuses() ),
                        'value'   => !is_null( $membership ) ? $membership->status : WPXSMARTSHOP_MEMBERSHIPS_STATUS_AVAILABLE
                    ),
                ),
            ),
        );

        if ( !is_null( $membership ) ) {
            $insert = &$fields[key( $fields )];
            array_splice( $insert, 3, 0, array( array( $role_previous ) ) );
        }
        reset( $fields );

        return $fields;
    }

    /**
     * Restituisce l'elenco degli stati della tabella wpss_memberships
     *
     * @package    wpx SmartShop
     * @subpackage WPXSmartShopMemberships
     * @since      1.0.0
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
            WPXSMARTSHOP_MEMBERSHIPS_STATUS_CURRENT   => array(
                'label' => __( 'Current', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            WPXSMARTSHOP_MEMBERSHIPS_STATUS_EXPIRED   => array(
                'label' => __( 'Expired', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            WPXSMARTSHOP_MEMBERSHIPS_STATUS_AVAILABLE => array(
                'label' => __( 'Available', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            ),
            WPXSMARTSHOP_MEMBERSHIPS_STATUS_TRASH     => array(
                'label' => __( 'Trash', WPXSMARTSHOP_TEXTDOMAIN ),
                'count' => 0
            )
        );
        return $statuses;
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
    // Database
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Crea o aggiorna (esegue un delta) la tabella delle Membership.
     * Questo metodo viene chiamato (di solito) all'attivazione del plugin, quindi una sola volta.
     *
     * @package    wpx SmartShop
     * @subpackage WPXSmartShopMemberships
     * @since      1.0.0
     *
     * @static
     *
     */
    public static function updateTable() {
        if ( !function_exists( 'dbDelta' ) ) {
            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        }
        $dbDeltaTableFile = sprintf( '%s%s', WPXSMARTSHOP_PATH_DATABASE, kWPSmartShopMembershipsTableFilename );
        $file             = file_get_contents( $dbDeltaTableFile );
        $sql              = sprintf( $file, self::tableName() );
        @dbDelta( $sql );
    }

    /**
     * Esegue un flush delle membership utente. Questo metodo si preoccupa di eliminare e attivare le membership per
     * una data utenza. Le mebership scadute vengono poste allo stato "expired" e repristinati i ruoli e permessi
     * utenti. Nello stesso modo se una membership dev'essere attivata, viene posta nello stato 'current' e aggiornati
     * ruoli e permessi.
     * È possibile sfruttare le cation per inviare mail all'utente
     *
     * @action wpss_membership_expired, wpss_membership_warning_expiration, wpss_membership_activated
     * @filter wpss_membership_days_warning
     *
     * @static
     * @param $id_user
     */
    public static function flush( $id_user ) {
        global $wpdb;

        /* Oggetto WP_User per applicare role e capabilities */
        $user       = new WP_User( $id_user );
        /* Default status. */
        $status     = WPXSMARTSHOP_MEMBERSHIPS_STATUS_CURRENT;
        /* Table name */
        $membership = self::tableName();

        /* 1. Seleziono tutte le membership attive e scadute - repristino i ruoli e cap utente  */

        $sql  = <<< SQL
SELECT *, DATEDIFF( date_expired, NOW()) AS days_from_expired
FROM {$membership}
WHERE id_user = {$id_user}
      AND status = '{$status}'
      AND date_expired <= NOW()
ORDER BY date_expired ASC
SQL;
        $rows = $wpdb->get_results( $sql );

        if ( $rows ) {
            foreach ( $rows as $row ) {
                /**
                 * Invoco una action esterna per segnalare che è scaduta una memebrship e da quanto
                 *
                 * @action
                 *
                 * @param int    $id_user ID dell'utente
                 * @param object $row     Riga dal database
                 * @param int    $days    Scaduta da $day giorni
                 */
                do_action( 'wpss_membership_expired', $id_user, $row, $row->days_from_expired );

                $values = array(
                    'status' => WPXSMARTSHOP_MEMBERSHIPS_STATUS_EXPIRED
                );

                $where  = array(
                    'id' => $row->id
                );

                $wpdb->update( $membership, $values, $where );

                /* Reimposto gli eventuali ruoli */
                if ( !empty( $row->role_previous ) ) {

                    /* Per compatibilità in WordPress è meglio rimuovere il vecchio ruolo; alla fine tanto un ruolo
                    riviene aggiunto
                    $user->remove_role($role_row['role']);
                    */

                    /* Un ruolo l'utente lo deve avere per forza, repristino il precedente */
                    $user->set_role( $row->role_previous );
                }

                /* Reimposto le eventuali capabilities */
                if ( !empty( $row->caps ) ) {
                    $caps = explode( ',', $row->caps );
                    foreach ( $caps as $cap ) {
                        if ( !empty( $cap ) ) {
                            $user->remove_cap( $cap );
                        }
                    }
                }
            }
        }


        /**
         * Filtro sul numero di giorni prima della scadenza di una membership. Chiamato prima di eseguire un
         * controllo sulle scadenze degli abbonamenti. Indica quanti giorni prima bisogna avvertire l'utente.
         *
         * @filters
         *
         * @param int $day Numero di giorno prima di avvertimento scadenza membership. Default 30
         */
        $days_warning = apply_filters( 'wpss_membership_days_warning', 30 );

        /* 2. Cerco quelle attive (current) in scadenza per avvertire l'utente */

        $sql = <<< SQL
SELECT *,
DATEDIFF( date_expired, NOW()) AS day_to_expired
FROM {$membership}
WHERE id_user = {$id_user}
      AND status = '{$status}'
      AND NOW() >= DATE_SUB(date_expired, INTERVAL {$days_warning} DAY)
ORDER BY date_expired ASC
SQL;
        $rows = $wpdb->get_results($sql);

        if( $rows) {
            foreach($rows as $row) {
                /**
                 * Invoco una action esterna per segnalare che sta per scadere una memebrship
                 *
                 * @action
                 *
                 * @param int    $id_user ID dell'utente
                 * @param object $row     Riga dal database
                 * @param int    $days    Scaduta da $day giorni
                 */
                do_action( 'wpss_membership_warning_expiration', $id_user, $row, $row->day_to_expired );
            }
        }

        /* 3. Cerco quelle disponibili WPXSMARTSHOP_MEMBERSHIPS_STATUS_AVAILABLE e non attivate ancora - impostando
        l'utente */

        $status = WPXSMARTSHOP_MEMBERSHIPS_STATUS_AVAILABLE;

        $sql = <<< SQL
SELECT *
FROM {$membership}
WHERE id_user = {$id_user}
AND status = '{$status}'
AND date_start <= NOW()
SQL;
        $rows = $wpdb->get_results( $sql );

        if ( $rows ) {
            foreach ( $rows as $row ) {
                $values = array(
                    'role_previous' => end( $user->roles ),
                    'status'        => WPXSMARTSHOP_MEMBERSHIPS_STATUS_CURRENT
                );
                $where  = array(
                    'id' => $row->id
                );
                $wpdb->update( $membership, $values, $where );

                /* Imposto gli eventuali ruoli */
                if ( !empty( $row->role ) ) {

                    /* Per compatibilità in WordPress è meglio rimuovere il vecchio ruolo; alla fine tanto un ruolo
                    riviene aggiunto
                    $user->remove_role($role_row['role']);
                    */

                    /* Un ruolo l'utente lo deve avere per forza, repristino il precedente */
                    $user->set_role( $row->role );
                }

                /* Imposto le eventuali capabilities */
                if ( !empty( $row->caps ) ) {
                    $caps = explode( ',', $row->caps );
                    foreach ( $caps as $cap ) {
                        if ( !empty( $cap ) ) {
                            $user->add_cap( $cap );
                        }
                    }
                }

                /**
                 * Invoco una action esterna per segnalare che è stata attivata una membership.
                 * Azione chiamata quando un abbonamento viene attivato la prima volta.
                 *
                 * @action
                 *
                 * @param int    $id_user ID utente
                 * @param object $row     record dal database
                 */
                do_action( 'wpss_membership_activated', $id_user, $row );

            }
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Database CRUD
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Crea un record sul database che indica una membership
     *
     * @package    wpx SmartShop
     * @subpackage WPXSmartShopMemberships
     * @since      1.0.0
     *
     * @static
     *
     * @param string $values Array con i valori da mettere in tabella, potrebber essere $_POST se proviene dal form di
     *                       inserimento
     *
     * @retval mixed
     */
    public static function create( $values ) {

        /* Deve finire con una virgola */
        $caps = '';
        if ( !empty( $values['caps'] ) ) {
            $caps = join( ',', $values['caps'] ) . ',';
        }

        /* Product maker - questo non c'è se la membership è generata manualmente */
        $id_product_maker = 0;
        if ( !empty( $values['id_product_maker'] ) ) {
            $id_product_maker = absint( $values['id_product_maker'] );
        }

        /* ID dell'utente */
        $id_user = absint( $values['id_user'] );

        /* ID dell'utente loggato */
        $id_user_maker = get_current_user_id();

        /* Oggetto User per recuperare l'attuale ruolo */
        $user = new WP_User( $id_user );

        $values = array(
            'id_user'          => $id_user,
            'id_user_maker'    => $id_user_maker,
            'id_product_maker' => $id_product_maker,
            'date_start'       => WPDKDateTime::dateTime2MySql( $values['date_start'], __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) ),
            'date_expired'     => WPDKDateTime::dateTime2MySql( $values['date_expired'], __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) ),
            'role'             => esc_attr( $values['role'] ),
            'role_previous'    => end( $user->roles ),
            'caps'             => $caps,
            'status'           => $values['status'],
        );

        /* Pulizia delle date */
        if ( empty( $values['date_start'] ) ) {
            unset( $values['date_start'] );
        }
        if ( empty( $values['date_expired'] ) ) {
            unset( $values['date_expired'] );
        }

        return parent::create( self::tableName(), $values );

    }

    public static function update() {

        /* Deve finire con una virgola */
        $caps = join( ',', $_POST['caps'] ) . ',';

        $values = array(
            'id_user'       => absint( $_POST['id_user'] ),
            'date_start'    => WPDKDateTime::dateTime2MySql( $_POST['date_start'], __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) ),
            'date_expired'  => WPDKDateTime::dateTime2MySql( $_POST['date_expired'], __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) ),
            'role'          => esc_attr( $_POST['role'] ),
            'role_previous' => esc_attr( $_POST['role_previous'] ),
            'caps'          => $caps,
            'status'        => $_POST['status'],
        );

        $date_start_format   = '%s';
        $date_expired_format = '%s';

        /* Rendo NULL per WordPress con un hack */
        if ( empty( $_POST['date_start'] ) ) {
            $date_start_format    = '%NNULL';
            $values['date_start'] = null;
        }

        if ( empty( $_POST['date_expired'] ) ) {
            $date_expired_format    = '%NNULL';
            $values['date_expired'] = null;
        }

        $formats = array(
            '%d',
            $date_start_format,
            $date_expired_format,
            '%s',
            '%s',
            '%s',
            '%s',
        );

        return parent::update( self::tableName(), absint( $_POST['id']), $values, 'id', $formats );

    }

    // -----------------------------------------------------------------------------------------------------------------
    // User Membership
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Aggiunge una riga nella tabella membership indicando il tipo di membership da associare ad una determinata utenza
     * eseguendo un controllo sulla presenza di membership identiche e applica quindi una diversa data di
     * start ed expired
     *
     * @static
     *
     * @uses       self::capsForDatabase()
     * @uses       self::create()
     *
     * @param array                   $membership Regole membership prodotto, con le regole membership da creare
     * @param int|string|object|array $order      Ordine (id, trackID, object o array), con le informazioni dell'utente
     * @param int                     $id_product ID del prodotto che ha la membership
     *
     * @retval bool|object
     */
    public static function addMembership( $membership, $order, $id_product ) {
        $order   = WPXSmartShopOrders::order( $order );
        $id_user = $order->id_user_order;

        /* Calcolo data di inizio e fine in base ad altre eventuali membership */
        if ( ( $result = self::membershipWith( $id_user, $id_product, $membership['role'], $membership['capabilities'] ) ) ) {
            $date_start = $result->date_expired;
        } else {
            $date_start = $order->status_datetime;
        }

        $date_expired = WPDKDateTime::expirationDate( $date_start, $membership['duration'], $membership['duration-type'] );
        $date_expired = date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ), $date_expired );

        $values = array(
            'id_user'           => $id_user,
            'id_user_maker'     => $id_user,
            'id_product_maker'  => $id_product,
            'date_start'        => mysql2date( __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ), $date_start ),
            'date_expired'      => $date_expired,
            'role'              => $membership['role'],
            'caps'              => self::capsForDatabase( $membership['capabilities'] ),
            'status'            => WPXSMARTSHOP_MEMBERSHIPS_STATUS_AVAILABLE
        );

        $result = self::create( $values );
        return $result;
    }

    /**
     * Costruisce la stringa di capabilities separata da virgola con virgola finale a partire da una stringa o da
     * un array di capabilities, per l'inserimento nel database
     *
     * @static
     *
     * @param $caps
     *
     * @retval string
     */
    private static function capsForDatabase( $caps ) {
        $result = '';
        if ( !empty( $caps ) && !is_array( $caps ) ) {
            $caps = array( $caps );
        }
        if( !empty( $caps ) ) {
            $result = join( ',', $caps ) . ',';
        }
        return $result;
    }

    /**
     * Restituisce una membership disponibile o attiva (available o current) per un determinato prodotto o per ruolo o
     * capabilities.
     *
     * @package    wpx SmartShop
     * @subpackage WPXSmartShopMemberships
     * @since      1.0.0
     *
     * @static
     *
     * @param int    $id_user    ID dell'utente
     * @param int    $id_product ID del prodotto, se 0 non viene preso in cosiderazione
     * @param string $role       Ruolo da cercare
     * @param array  $caps       Capabilities o array di capabilities
     *
     * @retval bool | object Se FALSE nessuna mambership trovata per i parametri impostati, altrimenti restituisce
     *             quella con data di start più avanti nel tempo
     */
    public static function membershipWith( $id_user, $id_product, $role, $caps ) {
        global $wpdb;

        $membership = self::tableName();

        $where_role    = '';
        $where_caps    = '';
        $where_product = '';

        /* Role */
        if ( !empty( $role ) ) {
            $where_role = sprintf( 'role = "%s"', $role );
        }

        /* Capabilities */
        if ( !empty( $caps) ) {
            if( !is_array( $caps ) ) {
                $caps = array( $caps );
            }

            foreach ( $caps as $cap ) {
                if ( !empty( $where_caps ) ) {
                    $where_caps .= ' OR ';
                }
                $where_caps .= "caps LIKE '%{$cap},%'";
            }

            $where_caps = sprintf( ' (%s) ', $where_caps );
        }

        /* Product */
        if ( !empty( $id_product ) ) {
            $where_product = sprintf( ' OR id_product_maker = %s', $id_product );
        }

        $status_available = WPXSMARTSHOP_MEMBERSHIPS_STATUS_AVAILABLE;
        $status_current   = WPXSMARTSHOP_MEMBERSHIPS_STATUS_CURRENT;

        $sql    = <<< SQL
SELECT id, date_start, date_expired
FROM {$membership}
WHERE id_user = {$id_user}
AND ( status = '{$status_available}' OR status = '{$status_current}' )
AND (
 {$where_role}
 {$where_caps}
 {$where_product}
)
ORDER BY date_start DESC
SQL;

        $result = $wpdb->get_results( $sql );
        if ( $result ) {
            return $result[key( $result )];
        }
        return false;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // has / is zone
    // -----------------------------------------------------------------------------------------------------------------


    /**
     * Restituisce TRUE se per un determinato utente esiste una membership (available o current) creata da un
     * determinato prodotto
     *
     * @static
     *
     * @param int $id_user    ID utente
     * @param int $id_product ID prodotto
     *
     * @retval bool
     */
    public static function membershipExistsForProduct( $id_user, $id_product ) {
        global $wpdb;

        $membership       = self::tableName();
        $status_available = WPXSMARTSHOP_MEMBERSHIPS_STATUS_AVAILABLE;
        $status_current   = WPXSMARTSHOP_MEMBERSHIPS_STATUS_CURRENT;

        $sql    = <<< SQL
SELECT COUNT(*)
FROM {$membership}
WHERE id_user = {$id_user}
AND id_product_maker = {$id_product}
AND ( status = '{$status_available}' OR status = '{$status_current}' )
SQL;
        $result = $wpdb->get_var( $sql );
        return ( $result > 0 );
    }

    /**
     * Restituisce TRUE se un determinato utente ha almeno uno dei ruoli passati in $roles attivi o disponibili
     *
     * @static
     *
     * @param int          $id_user ID utente
     * @param string|array $roles   Ruolo o array di ruoli da controllare in OR
     *
     * @retval bool
     */
    public static function membershipExistsForRoles( $id_user, $roles ) {
        global $wpdb;

        $membership       = self::tableName();
        $status_available = WPXSMARTSHOP_MEMBERSHIPS_STATUS_AVAILABLE;
        $status_current   = WPXSMARTSHOP_MEMBERSHIPS_STATUS_CURRENT;

        if ( is_array( $roles ) ) {
            $roles = join( "','", $roles );
        }

        $sql    = <<< SQL
SELECT COUNT(*)
FROM {$membership}
WHERE id_user = {$id_user}
AND role IN ('{$roles}')
AND ( status = '{$status_available}' OR status = '{$status_current}' )
SQL;
        $result = $wpdb->get_var( $sql );
        return ( $result > 0 );
    }

    /**
     * Restituisce TRUE se un determinato utente ha almeno una delle capabilities passata negli input, sempre con
     * status available o current.
     *
     * @static
     *
     * @param int          $id_user ID dell'utente
     * @param string|array $caps    Capability o array di capabilities da cercare nella membership utente
     *
     * @retval bool
     */
    public static function membershipExistsForCapabilities( $id_user, $caps ) {
        global $wpdb;

        $membership       = self::tableName();
        $status_available = WPXSMARTSHOP_MEMBERSHIPS_STATUS_AVAILABLE;
        $status_current   = WPXSMARTSHOP_MEMBERSHIPS_STATUS_CURRENT;

        if ( !is_array( $caps ) ) {
            $caps = array( $caps );
        }

        $where_or = '';
        foreach ( $caps as $cap ) {
            if ( !empty( $where_or ) ) {
                $where_or .= ' OR ';
            }
            $where_or .= "caps LIKE '%{$cap},%'";
        }

        /* Seleziono tutti i record per utente e status */
        $sql    = <<< SQL
SELECT caps
FROM {$membership}
WHERE id_user = {$id_user}
AND ( status = '{$status_available}' OR status = '{$status_current}' )
AND ({$where_or})
SQL;
        $result = $wpdb->get_results( $sql );

        return !empty( $result );
    }

   // -----------------------------------------------------------------------------------------------------------------
    // UI
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Costruisce il combo menu select per i filtri nei list table
     *
     * @static
     *
     * @param string $id_select Imposta attributo name e id del tag select
     * @param string $selected  ID della option selezionata
     *
     * @retval string
     */
    public static function selectFilterUsers( $id_select, $selected = '' ) {
        global $wpdb;

        $table_memberships  = self::tableName();
        $table_wp_users = $wpdb->users;

        /* Seleziono gli utenti in group by */
        $sql   = <<< SQL
SELECT memberships.id_user, users.display_name
FROM `{$table_memberships}` AS memberships
LEFT JOIN `{$table_wp_users}` AS users ON users.ID = memberships.id_user_maker
GROUP BY memberships.id_user
ORDER BY users.display_name
SQL;
        $users = $wpdb->get_results( $sql );

        $options = '';
        foreach ( $users as $user ) {
            if ( !empty( $user->display_name ) ) {
                $options .= sprintf( '<option %s value="%s">%s</option>', selected( $user->id_user, $selected, false ), $user->id_user, $user->display_name );
            }
        }

        $label = __( 'Filter for User', WPXSMARTSHOP_TEXTDOMAIN );

        $html = <<< HTML
        <select name="{$id_select}" id="{$id_select}">
            <option value ="">{$label}</option>
            {$options}
        </select>
HTML;
        return $html;
    }
}

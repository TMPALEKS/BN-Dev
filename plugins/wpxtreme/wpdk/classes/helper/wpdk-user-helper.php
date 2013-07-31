<?php
/**
 * @class              WPDKUser
 * @description        Gestisce un estensione di un utente WordPress, dalla registrazione al login fine all'aggiunti di
 *                     campi extra come indirizzo, città, etc...
 *
 * @package            WPDK (WordPress Development Kit)
 * @subpackage         helper
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @date               07/12/11
 * @version            1.0
 *
 * @todo               Questa classe è da completare. Nella sua versione definitiva sarebbe carino se riuscisse a gestire la registrazione
 *                     utente con un double-opt-in, conferma, tabella utenti temporanea, richiesta nuova password, etc...
 * @todo               Terminare gli hook principali di login registrazione utente, cancellazione
 * @todo               creare metodo per la security key
 *
 * @todo               Fare documentazione sul Wiki di github
 *
 * @todo               Gestire in visualizzazione e nel codice il 'wpdk_user_internal-status_message'
 *
 * USER META
 * =========
 *
 * Questi hanno un prefisso per distinguere quelli interni da quelli custom estesi
 *
 * wpdk_user_internal-
 * wpdk_user_custom-
 *
 * @internal
 *
 * wpdk_user_internal-count_success_login
 * wpdk_user_internal-count_wrong_login
 * wpdk_user_internal-time_last_login
 * wpdk_user_internal-time_last_logout
 * wpdk_user_internal-status [confirmed | disabled | locked ]
 * wpdk_user_internal-status_message Es. [ Locked because 5 login wrong ]
 *
 *
 */

/**
 * @addtogroup filters Filters
 *    Documentazione di tutti i filtri disponibili
 * @{
 * @defgroup user_helper_filters Nel file wpdk-user-helper.php
 * @ingroup filters
 *    Filters in file wpdk-user-helper.php
 * @}
 */

/**
 * @addtogroup actions Actions
 *    Documentazione di tutte le azioni disponibili
 * @{
 * @defgroup user_helper_actions Nel file wpdk-user-helper.php
 * @ingroup actions
 *    Actions in file wpdk-user-helper.php
 * @}
 */

class WPDKUser {

    const kInternalPrefix = 'wpdk_user_internal-';
    const kCustomPrefix   = 'wpdk_user_custom-';

    const kUserStatusConfirmed = 'confirmed';
    const kUserStatusDisabled  = 'disabled';
    const kUserStatusLocked    = 'locked';

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    // -----------------------------------------------------------------------------------------------------------------
    // Database
    // -----------------------------------------------------------------------------------------------------------------


    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    public static function init() {
        /* Hook on Login */
        add_action( 'wp_login', array( __CLASS__, 'wp_login') );
        add_action( 'wp_logout', array( __CLASS__, 'wp_logout') );
        add_action( 'wp_login_failed', array( __CLASS__, 'wp_login_failed' ) );

        /* includes/wp_insert_user() Nuovo Utente registrato  */
        add_action( 'user_register', array( __CLASS__, 'user_register') );

        /* includes/wp_insert_user() Utente già registrato quindi aggiornamento dati */
        add_action( 'profile_update', array( __CLASS__, 'profile_update'), 10, 2 );
        add_action( 'delete_user', array( __CLASS__, 'delete_user') );
        add_action( 'deleted_user', array( __CLASS__, 'deleted_user') );

        /* Backend edit user update */
        add_action( 'personal_options_update', array( __CLASS__, 'personal_options_update') );
        add_action( 'edit_user_profile_update', array( __CLASS__, 'edit_user_profile_update') );

        /* Extends Users List Table */
        add_filter( 'manage_users_columns', array( __CLASS__, 'manage_users_columns' ) );
        add_action( 'manage_users_custom_column',  array( __CLASS__, 'manage_users_custom_column' ), 10, 3);

        /* Extends User edit profile */
        add_action( 'edit_user_profile', array( __CLASS__, 'edit_user_profile' ) );
        add_action( 'show_user_profile', array( __CLASS__, 'show_user_profile' ) );

        /* Disable and locking featured */
        add_filter( 'wp_authenticate_user', array( __CLASS__, 'wp_authenticate_user' ), 1 );

        /* Edit user/own profile */
        add_action( 'show_user_profile', array( __CLASS__, 'show_user_profile') );
        add_action( 'edit_user_profile', array( __CLASS__, 'edit_user_profile') );

        add_filter ( 'user_contactmethods', array( __CLASS__, 'user_contactmethods') );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress hook
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Non utilizzato per adesso in quanto non avendo la lista "pulita" contenuta in $contacts non è possibile fare una
     * serie di checkbox che indicano i campi da non mostrare. Per averla, infatti, dovrei usare proprio questo filtro
     * oppure la funzione privata/interna _wp_get_user_contactmethods() che, tuttavia, a sua volta richiama appunto
     * questo filtro; ergo, non sono gestibili.
     *
     * @static
     * @param array $contacts
     * @retval mixed
     */
    public static function user_contactmethods( $contacts ) {
        return $contacts;
    }

    /**
     * Eseguito quando un utente viene autenticato. Viene usato per gestire i lock sugli utenti
     *
     * @static
     *
     * @param WP_User $user Oggeto WP_User
     *
     * @retval WP_Error|WP_User Restituisce l'oggetto WP_User o un WP_Error in caso di blocco o errore
     */
    public static function wp_authenticate_user( $user ) {
        if ( is_wp_error( $user ) ) {
            return $user;
        }

        if ( $user->get( 'wpdk_user_internal-status' ) == self::kUserStatusDisabled ) {
            return new WP_Error( 'wpdk_error-login_user_disabled', __( 'Login not allowed because this user is disabled.', WPDK_TEXTDOMAIN ) );
        }

        if ( $user->get( 'wpdk_user_internal-status' ) == self::kUserStatusLocked ) {
            return new WP_Error( 'wpdk_error-login_user_locked', __( 'Login not allowed because this user is locked.', WPDK_TEXTDOMAIN ) );
        }

        return $user;
    }

    /**
     * Login utente
     *
     * @static
     *
     * @param string  $user_login user login
     * @param WP_User $user
     */
    public static function wp_login( $user_login, $user = null ) {
        if ( is_null( $user ) ) {
            $user = get_user_by( 'login', $user_login );
        }
        $count = absint( $user->get( 'wpdk_user_internal-count_success_login' ) );
        if ( empty( $count ) ) {
            $count = 0;
        }
        update_user_meta( $user->ID, 'wpdk_user_internal-count_success_login', $count + 1 );

        /* @todo Se l'utente si autentica correttamente, azzero il contatore dei login sbagliati: aggiungere filtro e/o impostazioni */
        update_user_meta( $user->ID, 'wpdk_user_internal-count_wrong_login', 0 );
        update_user_meta( $user->ID, 'wpdk_user_internal-time_last_login', time() );
    }

    /**
     * Logout
     *
     * @static
     */
    public static function wp_logout() {
        $user_id = get_current_user_id();
        update_user_meta( $user_id, 'wpdk_user_internal-time_last_logout', time() );
    }

    /**
     * Chiamata da WordPress quando un utente sbaglia il login
     *
     * @static
     * @param $user_login
     * @retval mixed
     */
    public static function wp_login_failed( $user_login ) {

        if( empty( $user_login) || !WPXtreme::$settings->enabled_wrong_login_attempts() ) {
            return;
        }

        $user = get_user_by( 'login', $user_login );
        $count = absint( $user->get( 'wpdk_user_internal-count_wrong_login' ) );
        if ( empty( $count ) ) {
            $count = 0;
        }

        /**
         * @defgroup wpdk_user_count_wrong_login wpdk_user_count_wrong_login
         * @{
         *
         * @ingroup  user_helper_filters
         *           Called when a login user was wrong and your count is increase
         *
         * @param int $count   Wrong login count + 1
         * @param int $id_user User ID
         *
         * @retval   int Wrong login count
         *
         * @}
         */
        $count = apply_filters( 'wpdk_user_count_wrong_login', ( $count +1 ), $user->ID );

        update_user_meta( $user->ID, 'wpdk_user_internal-count_wrong_login', $count );

        /* Recupero dalle impostazioni quanti login sbagliati l'utente può fare */
        $attempts = absint( WPXtreme::$settings->wrong_login_attempts() );
        if( $count >= $attempts ) {

            update_user_meta( $user->ID, 'wpdk_user_internal-status', self::kUserStatusLocked );

            /**
             * @defgroup wpdk_user_status wpdk_user_status
             * @{
             *
             * @ingroup  user_helper_actions
             *           Called when a user status is change
             *
             * @param int    $id_user  User ID
             * @param string $status   Status
             *
             * @}
             */
            do_action( 'wpdk_user_status', $user->ID, self::kUserStatusLocked );
        }
    }

    /**
     * Called when an user is created
     *
     * @todo Not used yet
     *
     * @static
     * @param int $id_user User ID
     */
    public static function user_register( $id_user ) {

    }

    /**
     * Called when updating user data after an insert
     * 
     * @todo Not used yet
     *
     * @static
     * @param int $id_user User ID
     * @param array $old_user_data User data
     */
    public static function profile_update( $id_user, $old_user_data ) {

    }

    /**
     * Called when your owner profile is updated
     *
     * @static
     * @param int $id_user User iD
     */
    public static function personal_options_update( $id_user ){
        /* Same for other users, see below */
        self::edit_user_profile_update( $id_user );
    }

    /**
     * Called when updating user data
     *
     * @static
     * @param int $id_user User ID
     */
    public static function edit_user_profile_update( $id_user ) {
        if ( !current_user_can( 'edit_user', $id_user ) ) {
            return false;
        }

        /* Questi sono i campi registrati nelle impostazioni, devo però impostare i valori nella user meta. */
        $items = WPXtreme::$settings->extrafields();
        if ( !empty( $items ) ) {
            foreach ( $items as $key => $item ) {
                if ( isset( $item['name'] ) ) {
                    /* @todo Qui andrebbero sanitizzati i valori in base al tipo del campo. */
                    $value = esc_attr( $_POST[$item['name']] );
                    update_user_meta( $id_user, $item['name'], $value );
                }
            }
        }

        update_user_meta( $id_user, 'bill_town', $_POST['bill_town'] );

    }

    /**
     * L'utente WordPress sta per essere eliminato. Analizzando il codice è comunque non possibile impedire tramite
     * questa action che l'utente venga eliminato.
     *
     * @static
     * @param int $id_user User ID
     */
    public static function delete_user( $id_user ) {

    }

    /**
     * L'utente WordPress è stato eliminato
     *
     * @static
     * @param int $id_user User ID
     */
    public static function deleted_user( $id_user ) {

    }

    /**
     * Altera le colonne della List table degli utenti di WordPress
     *
     * @static
     * @param array $columns Elenco array keypair delle colonne
     * @retval array
     */
    public static function manage_users_columns( $columns ) {

        $columns['wpdk_user_internal-time_last_login']     = __( 'Last login', WPDK_TEXTDOMAIN );
        $columns['wpdk_user_internal-time_last_logout']    = __( 'Last logout', WPDK_TEXTDOMAIN );
        $columns['wpdk_user_internal-count_success_login'] = __( '# Login', WPDK_TEXTDOMAIN );
        $columns['wpdk_user_internal-count_wrong_login']   = __( '# Wrong', WPDK_TEXTDOMAIN );
        $columns['wpdk_user_internal-status']              = __( 'Enabled', WPDK_TEXTDOMAIN );

        return $columns;
    }

    /**
     * Contenuto (render) di una colonna
     *
     * @static
     *
     * @param mixed  $value
     * @param string $column_name Column name
     * @param int    $user_id User ID
     */
    public static function manage_users_custom_column( $value, $column_name, $user_id ) {
        $result = new WP_User( $user_id );
        $value  = $result->get( $column_name );

        if( $column_name == 'wpdk_user_internal-time_last_login' || $column_name == 'wpdk_user_internal-time_last_logout') {
            if( !empty( $value )) {
                $value = WPDKDateTime::timeNewLine( date( __('m/d/Y H:i:s', WPDK_TEXTDOMAIN), $value ) );
            }
        } elseif( 'wpdk_user_internal-status' == $column_name ) {
            $item = array(
                'type'       => WPDK_FORM_FIELD_TYPE_SWIPE,
                'name'       => 'wpdk-user-enabled',
                'userdata'   => $user_id,
                'afterlabel' => '',
                'value'      => ( empty( $value ) || $value == self::kUserStatusConfirmed ) ? 'on' : 'off'
            );
            ob_start();
            WPDKForm::htmlSwipe( $item );
            $value = ob_get_contents();
            ob_end_clean();
        } elseif( 'wpdk_user_internal-count_wrong_login' == $column_name ) {
            if( empty( $value ) ) {
                $value = '0';
            }
            $value .= '/' .absint( WPXtreme::$settings->wrong_login_attempts() );
        }

        return $value;
    }

    /**
     * Pagina di modifica nel backend
     *
     * @static
     * @param $user
     */
    public static function edit_user_profile( $user ) {
        /* Per adesso mostro il profilo di un altro utente come se visualizzassi il mio personale */
        self::show_user_profile( $user );
    }

    /**
     * Called when the user edit view is displayed
     *
     * @todo Add more fields
     *
     * @static
     * @param object $user User object
     */
    public static function show_user_profile( $user ) {

        /* Questi sono i campi registrati nelle impostazioni, devo però prendermi i valori nella user meta. */
        $items    = WPXtreme::$settings->extrafields();
        if ( !empty( $items ) ) {
            foreach ( $items as $key => $item ) {
                if ( isset( $item['name'] ) ) {
                    $value = get_user_meta( $user->ID, $item['name'], true );
                    if ( $value ) {
                        /* @todo Qui andrebbero sanitizzati i valori in base al tipo del campo. */
                        $items[$key]['value'] = $value;
                    }
                }
            }
        }

        $fields = array(
            __( 'Extra fields', WPDK_TEXTDOMAIN ) => array(
                __( 'See User Settings for manage this extra fields', WPDK_TEXTDOMAIN ),
                WPDKArray::wrapArray( $items )
            )
        );

        WPDKForm::htmlForm( $fields );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress Roles list
    // -----------------------------------------------------------------------------------------------------------------

	/**
	 * Restituisce la lista di tutti i ruoli attualmente presenti in WordPress.
	 *
	 * @static
	 * @retval array
	 */
    public static function allRoles() {
        global $wp_roles;
        $result = array();

        $roles = $wp_roles->get_names();
        foreach ( $roles as $key => $role ) {
            $result[$key] = $role;
        }
        return $result;
    }

    /**
     * Restituisce il 'nome' del ruolo di un utente
     *
     * @static
     *
     * @param int $id_user User ID
     *
     * @retval bool|string Ruolo utente o FALSE se errore.
     */
    public static function roleNameForUserID( $id_user ) {
        global $wp_roles;

        $id_user = absint( $id_user );
        $user    = new WP_User( $id_user );
        if ( !empty( $user ) ) {
            $role_key = $user->roles[key( $user->roles )];
            if ( !empty( $role_key ) ) {
                return $wp_roles->roles[$role_key]['name'];
            }
        }
        return false;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress Capabilities List
    // -----------------------------------------------------------------------------------------------------------------

	/**
	 * Restituisce la lista di tutte le capabilities attualmente presenti in WordPress, scorrendo tutti i ruoli
	 * presenti ed estraendo le capabilities.
	 *
	 * @static
	 * @retval array
	 */
	public static function allCapabilities() {
		global $wp_roles;
		$merge = array();

		$roles = $wp_roles->get_names();
		foreach ( $roles as $key => $rolename ) {
			$role  = get_role( $key );
			$merge = array_merge( $merge, $role->capabilities );
		}
		$result = array_keys( $merge );
		sort( $result );
		$result = array_combine($result, $result);
		return $result;
	}

    /**
     * Aggiunge e/o rimuove i permessi (capability) da un utente. L'aggiunta avviene eseguendo una match tra una lista
     * di capability selezionate e una lista di confronto, che corrisponde in pratica alle capabilities che possono
     * essere aggiunte. Senza il parametro $capabilities verrebbero prese in considerazione tutte le capabilities, cosa
     * che ovviamente non va bene. In pratica questo metodo dice; in base a questa lista ($capabilities) quali tra
     * quelle selexionate ($selected_caps) devo attivate/disattivare ?
     *
     * @static
     *
     * @param int   $id_user       ID dell'utente
     * @param array $selected_caps Lista delle capability da aggiungere
     * @param array $capabilities  Lista di confronto per capire quale capability aggiungere e quale rimuovere
     */
	public static function updateUserCapabilities( $id_user, $selected_caps, $capabilities ) {
		if ( $id_user && is_array( $selected_caps ) ) {
			$user = new WP_User( $id_user );
			foreach ( $capabilities as $key => $cap ) {
				if ( in_array( $key, $selected_caps ) ) {
					/* Add */
					$user->add_cap( $key );
				} else {
					/* Del */
					$user->remove_cap( $key );
				}
			}
		}
	}

    /**
     * Legge o imposta lo user meta per visualizzare la toolbar di WordPress in front end
     *
     * @static
     *
     * @param int  $id_user Se NULL prende l'utente attualmente loggato
     * @param null $show    Se NULL restituisce il valore corrente per l'utente $id_user
     *
     * @retval bool|mixed True se l'impostazione è avvenuta con successo oppure una stringa che indica lo stato attuale della toolbar
     */
    public static function showAdminBarFront( $id_user = null, $show = null ) {
        if ( is_null( $id_user ) ) {
            $id_user = get_current_user_id();
        }
        if ( $id_user ) {
            $show_admin_bar_front = get_user_meta( $id_user, 'show_admin_bar_front', true );
            if ( is_null( $show ) ) {
                return $show_admin_bar_front;
            } else {
                $value = ( $show === true ) ? 'true' : 'false';
                update_user_meta( $id_user, 'show_admin_bar_front', $value );
                return true;
            }
        }
        return false;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // WordPress integration
    // -----------------------------------------------------------------------------------------------------------------

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress integration - Login
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Esegue il login in WordPress
     *
     * @static
     *
     * @param string $field Indica il campo da utilizzare come username
     *
     * @retval bool Restituisce true se il login ha avuto successo, altrime false per errore
     */
    public static function doLogin( $field = 'user_email' ) {
        global $wpdb;

        if ( isset( $_POST['action'] ) && $_POST['action'] == 'do_login' ) {
            $email    = sanitize_email( $_POST['username'] );
            $password = esc_attr( $_POST['password'] );
            if ( $email && $password ) {
                $sql = <<< SQL
    SELECT ID, user_login
    FROM `{$wpdb->users}`
    WHERE {$field} = '{$email}'
SQL;
                $row = $wpdb->get_row( $sql );
                if ( $row ) {
                    $result = wp_authenticate( $row->user_login, $password );
                    if ( !is_wp_error( $result ) ) {
                        wp_set_auth_cookie( $row->ID, isset( $_POST['remember'] ) );
                        $user = get_user_by( 'login', $row->user_login );
                        do_action( 'wp_login', $row->user_login, $user );
                        self::wp_login( $row->user_login, $user );
                        wp_set_current_user( $row->ID );
                        return true;
                    }
                }
            }
            do_action( 'wpdk_login_wrong' );
        }
        return false;
    }

    /**
     * Autentica uno user per email e password
     *
     * @static
     * @param string $email User email address
     * @param string $password Clear password
     * 
     * @retval int|bool Restituisce l'ID dell'utente o false se non autenticato
     */
    public static function authenticate( $email, $password ) {
        global $wpdb;

        if ( empty( $email ) || empty( $password ) ) {
            return false;
        }

        $email    = sanitize_email( $email );
        $password = esc_attr( $password );

        $sql = <<< SQL
    SELECT ID, user_login
    FROM `{$wpdb->users}`
    WHERE user_email = '{$email}'
SQL;
        $row = $wpdb->get_row( $sql );

        if ( $row ) {
            $result = wp_authenticate( $row->user_login, $password );
            if ( !is_wp_error( $result ) ) {
                self::wp_login( $row->user_login );
                return $row->ID;
            }
        }
        return false;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Commodity
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Unisce nome e cognome prendendo la prima lettere del nome, Eg. Mario Rossi > M.Rossi
     *
     * @static
     *
     * @param string $firstName Nome
     * @param string $lastName Cognome
     *
     * @retval string
     */
    public static function formatNiceName( $firstName, $lastName ) {
        $result = sprintf( '%s.%s', strtoupper( substr( $firstName, 0, 1 ) ), ucfirst( $lastName ) );
        return $result;
    }

    /**
     * Unisce nome e cognome o cognome e nome
     *
     * @static
     *
     * @param string $firstName Nome
     * @param string $lastName  Cognome
     * @param bool   $nameFirst
     *
     * @retval string
     */
    public static function formatFullName( $firstName, $lastName, $nameFirst = true ) {
        if ( $nameFirst ) {
            $result = sprintf( '%s %s', $firstName, $lastName );
        } else {
            $result = sprintf( '%s %s', $lastName, $firstName );
        }
        return $result;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // User info
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce un oggetto utente con anche le informazioni in user meta
     *
     * @deprecated Questa procedura non è necessaria in quanto l'oggetto WP_User già carica tutti gli user meta e li rende accessibili tramite il metodo get()
     *
     * @static
     * @param null $user
     * @retval null|WP_User
     */
    public static function user( $user = null ) {

        _deprecated_function( __FUNCTION__, '1.0', 'WP_User' );

        if ( is_null( $user ) ) {
            $id_user = get_current_user_id();
        } elseif( is_numeric( $user) ) {
            $id_user = $user;
        }

        if( !is_object( $user ) ) {
            $user = new WP_User( $id_user );
        }
        $user->user_meta = get_user_meta( $id_user );

        return $user;
    }

    /**
     * Restituisce l'id dell'utente con una determinata meta_key e meta_value
     *
     * @static
     *
     * @param string $meta_key   Identificativo della meta_key
     * @param string $meta_value Valore
     *
     * @retval int ID utente o false se non trovato
     */
    public static function userWithMetaAndValue( $meta_key, $meta_value ) {
        global $wpdb;

        $sql    = <<< SQL
SELECT user_id
FROM $wpdb->usermeta
WHERE meta_key = '$meta_key'
AND meta_value = '$meta_value'
SQL;
        $result = $wpdb->get_var( $sql );

        return $result;
    }

    /// Get users by meta key and value
    /**
     * Restituisce l'elenco degli ID utente che possiedono un determinata meta_key
     *
     * @todo Not implements yet
     *
     * @static
     *
     * @param string $meta_key
     * @param string $meta_value
     *
     * @retval array Users object or null
     */
    public static function usersWithMeta( $meta_key, $meta_value ) {
        /* ... */
    }

    /// Get the user display name
    /**
     * Restituisce il Display Name come impostato nel backend user
     *
     * @static
     *
     * @param int $id_user User ID or null for current user ID
     *
     * @retval string|bool Display name string or false if error
     */
    public static function displayName( $id_user = null ) {
        if ( is_null( $id_user ) ) {
            $id_user = get_current_user_id();
        }
        $user = new WP_User( $id_user );
        if ( $user ) {
            return $user->display_name;
        }
        return false;
    }

    /// Get the user age from birth date
    /**
     * Calcola l'eta (in anni) a partire da una data nel formato YYYY-MM-DD o DD/MM/YYYY
     *
     * @todo To improve in date format
     *
     * @static
     *
     * @param string $birthday Data di nascita. Questo può essere sia in formato MySQL YYYY-MM-DD o in formato data
     *                         unico vincolo per adesso è il supporto solo per data italiana, ovvero giorno/meso/anno
     *
     * @retval int Age
     */
    public static function ageFromDate( $birthday ) {
        $year_diff = 0;

        if ( !empty( $birthday ) ) {
            if ( strpos( $birthday, '-' ) !== false ) {
                list( $year, $month, $day ) = explode( '-', $birthday );
            } else {
                list( $day, $month, $year ) = explode( '/', $birthday );
            }
            $year_diff  = date( 'Y' ) - $year;
            $month_diff = date( 'm' ) - $month;
            $day_diff   = date( 'd' ) - $day;
            if ( $month_diff < 0 || ( $month_diff == 0 && $day_diff < 0 ) ) {
                $year_diff--;
            }
        }
        return intval( $year_diff );
    }

    /**
     * Converte la data di nascita visuale in quella MySQL
     *
     * @static
     *
     * @param string $birthdate
     *
     * @retval string
     */
    private static function birthDateToMySQL( $birthdate ) {
        return WPDKDateTime::formatFromFormat( $birthdate, __( 'm/d/Y', WPDK_TEXTDOMAIN ), 'Y-m-d' );
    }

    /// Convert a birth date for display
    /**
     * Converte la data di nascita di MySQL in quella visuale
     *
     * @static
     * @param string $birthdate Borth date in mySQL format (Y-m-d)
     *
     * @retval string Date for display
     */
    private static function birthDateToInput( $birthdate ) {
        return WPDKDateTime::formatFromFormat( $birthdate, 'Y-m-d', __( 'm/d/Y', WPDK_TEXTDOMAIN ) );
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Sanitize
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Esegue dei controlli per sanitizzare lo uniqID - può essere sovrascritta
     *
     * @static
     *
     * @param string $id
     *
     * @retval string
     */
    public static function sanitizeUserUniqID( $id ) {
        if ( substr( $id, 0, 1 ) != 'u' ) {
            return '';
        }

        //4ee5e4ab78c38
        $result = substr( $id, 0, 14 );
        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Gravatar
    // -----------------------------------------------------------------------------------------------------------------

    /// Get html img tag from gravatar.com service
    /**
     * Restituisce l'html dell'immagine sul servizio Gravatar
     *
     * @static
     *
     * @param int    $id_user User ID or null for current user ID
     * @param int    $size Gravatar size
     * @param string $alt Alternate string for alt attribute
     * @param string $default Gravatar ID for default (not found) gravatar image
     *
     * @retval string L'HTML del tag <img> dell'avatar, altrimenti false per errore
     */
    public static function gravatar( $id_user = null, $size = 40, $alt = '', $default = "wavatar" ) {
        if ( is_null( $id_user ) ) {
            $id_user = get_current_user_id();
        }
        $user = new WP_User( $id_user );
        if ( $user ) {
            $alt = empty( $alt ) ? $user->display_name : $alt;
            $src = sprintf( 'http://www.gravatar.com/avatar/%s?s=%s&d=%s', md5( $user->user_email ), $size, $default );

            $html = <<< HTML
            <img src="{$src}" alt="{$alt}" title="{$alt}" />
HTML;
            return $html;
        }
        return false;
    }

    /**
     * Restituisce un array in formato SDF con la lista degli utenti, formattata con 'display name (email)'
     *
     * @todo Sicuramente da migliorare in quanto poco flessibile
     *
     * @static
     * @retval array
     */
    public static function arrayUserForSDF() {
        $users      = array();
        $users_list = get_users();
        if ( $users_list ) {
            foreach ( $users_list as $user ) {
                $users[$user->ID] = sprintf( '%s (%s)', $user->display_name, $user->user_email );
            }
        }
        return $users;
    }

    /* @todo Alias allRoles() - quest'ultimo da eliminare ? */
    public static function arrayRolesForSDF() {
        global $wp_roles;
        $result = array();

        $roles = $wp_roles->get_names();
        foreach ( $roles as $key => $role ) {
            $result[$key] = $role;
        }
        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Registration/Profile/Double-optin
    // -----------------------------------------------------------------------------------------------------------------

    /// Create a WordPress user
    /**
     * Crea una utenza in WordPress.
     * Crea un utente nella tabella di WordPress seguendo i parametri impostati negli inputs.
     *
     * @static
     *
     * @param string        $first_name First name
     * @param string        $last_name  Last name
     * @param string        $email      Email address
     * @param bool|string   $password   Clear password, if set to false a random password is created
     * @param bool          $enabled    Se true l'utente viene creato e immediatamente abilitato, lasciare false per porlo in
     *                                  uno stato di pending
     *
     * @retval int|WP_Error
     */
    public static function create( $first_name, $last_name, $email, $password = false, $enabled = false ) {

        /* Recupero impostazioni di registrazione */
        $settings  = WPXtreme::$settings->registration();

        /* @note Per ragioni di sicurezza sarebbe bene creare sempre utenti con password. Evitare quindi di creare utenti
         *       con password nulla, ovvero vutoa.
         */
        if( $password === false ) {
            $password = WPDKCrypt::randomAlphaNumber();
        }

        $niceName = self::formatNiceName( $first_name, $last_name );

        $userInfo = array(
            "user_login"    => $email,
            'user_pass'     => $password,
            'user_email'    => $email,
            "user_nicename" => $niceName,
            "nickname"      => $niceName,
            "display_name"  => self::formatFullName( $first_name, $last_name ),
            "first_name"    => $first_name,
            "last_name"     => $last_name,
            "role"          => $settings['default_user_role']
        );

        $result = wp_insert_user( $userInfo );

        /* Se l'utente è stato inserito lo disabilito come da parametro. */
        if ( !is_wp_error( $result ) && $enabled === false ) {
            update_user_meta( $result, 'wpdk_user_internal-status', self::kUserStatusDisabled );
            /* @todo Aggiungere filtro. */
            update_user_meta( $result, 'wpdk_user_internal-status-message', __( 'Waiting for enabling', WPDK_TEXTDOMAIN ) );
        }

        return $result;
    }

    /// Get SDF fields for edit profile
    /**
     * Restituisce l'array dei campi in formato SDF per la visualizzazione del profilo
     *
     * @todo Estendere con i campi extra gestibili da backend
     *
     * @static
     * @retval array
     */
    public static function fieldsProfile( $user ) {
        /* Recupero le impostazioni per la registrazione. */
        $settings = WPXtreme::$settings->registration();

        $fields = array(
            __( 'Your profile', WPDK_TEXTDOMAIN ) => array(

                array(
                    'type'  => WPDK_FORM_FIELD_TYPE_HIDDEN,
                    'name'  => 'wpdk_action_profile',
                    'value' => 'update'
                ),

                /* Dato che un amministratore potrebbe editare anche profili di altri, riposto anche lo user ID che
                potrebbe essere diverso dall'utente attualmente loggato. Questo serve per aggiornare il profilo.
                */
                array(
                    'type'  => WPDK_FORM_FIELD_TYPE_HIDDEN,
                    'name'  => 'wpdk_action_profile_id',
                    'value' => $user->ID
                ),

                sprintf( __( '<strong>%s</strong> active from <strong>%s</strong><br/>Last login <strong>%s</strong>, last logout <strong>%s</strong>', WPDK_TEXTDOMAIN ),
                    $user->data->display_name,
                    WPDKDateTime::formatFromFormat( $user->data->user_registered, MYSQL_DATE_TIME, __( 'm/d/Y H:i', WPDK_TEXTDOMAIN ) ),
                    date( __( 'm/d/Y H:i', WPDK_TEXTDOMAIN ), $user->get( 'wpdk_user_internal-time_last_login') ),
                    date( __( 'm/d/Y H:i', WPDK_TEXTDOMAIN ), $user->get( 'wpdk_user_internal-time_last_logout') )
                ),

                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'wpdk_first_name',
                        'value' => $user->get('first_name'),
                        'label' => __( 'First name', WPDK_TEXTDOMAIN )
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'wpdk_last_name',
                        'value' => $user->get('last_name'),
                        'label' => __( 'Last name', WPDK_TEXTDOMAIN )
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'wpdk_email',
                        'value' => $user->data->user_email,
                        'data'  => array( 'placement' => 'right' ),
                        'title' => '',
                        'label' => __( 'Email', WPDK_TEXTDOMAIN )
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_SUBMIT,
                        'value' => __( 'Update', WPDK_TEXTDOMAIN ),
                        'class' => 'btn btn-primary'
                    )
                )
            ),
        );

        return $fields;
    }

    /// Get user profile form view
    /**
     * Restituisce la form del profilo utente
     *
     * @static
     *
     * @param WP_User $user Oggetto WP_User
     *
     * @retval string
     */
    public static function formProfile( $user ) {

        ob_start();
        WPDKForm::htmlForm( self::fieldsProfile( $user ) );
        $content = ob_get_contents();
        ob_end_clean();

        /* Controllo se update */
        $update = '';
        if ( isset( $_POST['wpdk_action_profile'] ) && $_POST['wpdk_action_profile'] == 'update' ) {
            $update = WPDKUI::message( __( 'Your profile was successfully updated', WPDK_TEXTDOMAIN ), false, 'alert alert-info alert-block', false, true );
        }

        $html = <<< HTML
{$update}
<form class="wpdk-form" name="" method="post" action="">
{$content}
</form>
HTML;
        return $html;
    }

    /// Get SDF fields for user registration
    /**
     * Campi in SFD per la form di registrazione
     *
     * @todo Estendere con i campi extra gestibili da backend
     *
     * @static
     * @retval array
     */
    public static function fieldsRegistration() {
        /* Recupero le impostazioni per la registrazione. */
        $settings  = WPXtreme::$settings->registration();

        $title_email =  __( 'This email address will be used for sending an email to you. You have to confirm url address in the email.', WPDK_TEXTDOMAIN );
        $double_optin = 'double-optin';
        if( !$settings['double_optin'] ) {
            $double_optin = 'no-double-optin';
            $title_email =  __( 'This email address will be the your username for login.', WPDK_TEXTDOMAIN );
        }

        $buttons = array(
            array(
                'type'  => WPDK_FORM_FIELD_TYPE_SUBMIT,
                'value' => __('Sign up', WPDK_TEXTDOMAIN ),
                'class' => 'btn btn-primary'
            )
        );

        $standard = array(
            __( 'Account information', WPDK_TEXTDOMAIN ) => array(

                array(
                    'type'  => WPDK_FORM_FIELD_TYPE_HIDDEN,
                    'name'  => 'wpdk_action_registration',
                    'value' => $double_optin
                ),

                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'wpdk_first_name',
                        'label' => __( 'First name', WPDK_TEXTDOMAIN )
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'wpdk_last_name',
                        'label' => __( 'Last name', WPDK_TEXTDOMAIN )
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'wpdk_email',
                        'data'  => array( 'placement' => 'right' ),
                        'title' => $title_email,
                        'label' => __( 'Email', WPDK_TEXTDOMAIN )
                    )
                ),
                $buttons
            )
        );

        return $standard;
    }

    /**
     * Restituisce la form di registrazione
     *
     * @todo Documentare parametri, sia qui che nello shortcode
     *
     * @static
     *
     * @param array $args Parametri provenienti dallo shortcode, ma utilizzabli anche direttamente
     *
     * @retval string
     */
    public static function formRegistration( $args ) {

        ob_start();
        WPDKForm::htmlForm( self::fieldsRegistration() );
        $content = ob_get_contents();
        ob_end_clean();

        $html = <<< HTML
<form class="wpdk-form" name="" method="post" action="">
{$content}
</form>
HTML;
        return $html;
    }

    /// Return HTML message for double optin
    /**
     * Procedura di Double optin. Controllo e registrazione temporanea.
     *
     * @static
     *
     * @param string $first_name First name
     * @param string $last_name  Last name
     * @param string $email      Email address
     *
     * @retval string
     */
    public static function registerUserForDoubleOptin( $first_name, $last_name, $email ) {

        /* Recupero impostazioni */
        $settings  = WPXtreme::$settings->registration();

        /* Creo utente in WordPress disabilitato. */
        /* @todo Se questo utente non viene confermato entro un certo lasso di tempo andrebbe eliminato. */

        /* Controllo che non esista già un utente con questa email. */
        if ( email_exists( $email ) ) {
            $message = __( 'Warning! Your email is not valid.', WPDK_TEXTDOMAIN );
        } else {
            /* Creo utente. */
            $result = self::create( $first_name, $last_name, $email );

            /* $result contiene l'id dell'utente creato o un oggetto WP_Error in caso di errore. */

            if ( is_wp_error( $result ) ) {
                $message = sprinf( __( 'Error: %s', WPDK_TEXTDOMAIN ), $result->get_error_message() );
            } else {
                /* Invio mail con il template definito nei setting all'utente $result appena inserito */
                WPXtremeMailCustomPostType::mail( $settings['email_slug_confirm'], $result );
                $message = __( 'Thanks, please check your email to confirm registration.', WPDK_TEXTDOMAIN );
            }
        }

        $html = <<< HTML
<h2>{$message}</h2>
HTML;
        return $html;
    }

    /// Enable an user by unlock code
    /**
     * Procedura di abilitazione utente.
     * La password qui viene rigenerata e aggiornata all'utente. Questo perché in prima istanza le utenze devono sempre
     * avere una password e secondo perché il crypt di WordPress non è reversibili (in tempi brevi).
     *
     * @static
     *
     * @param string $unlock_code Unlock code for enable an user
     *
     * @retval string|bool HTML message or false if error
     */
    public static function enableUserAfterDoubleOptin( $unlock_code ) {

        /* Recupero impostazioni */
        $settings = WPXtreme::$settings->registration();

        /* Verifico codice di sblocco. Questo è l'md5 della mail dell'utente */
        /* Cerco un utente 'lockato' */
        $id_user = self::userWithMetaAndValue( 'wpdk_unlock_code', $unlock_code );

        /* Se esiste questa utenza... */
        if ( $id_user ) {
            /* Genero nuova password, aggiorno utente e abilito. */
            $new_password = WPDKCrypt::randomAlphaNumber();
            $userdata     = array(
                'ID'        => $id_user,
                'user_pass' => $new_password
            );
            $result       = wp_update_user( $userdata );

            if ( $result == $id_user ) {

                /* Rimuovo lo user meta con il codice di sblocco */
                delete_user_meta( $id_user, 'wpdk_unlock_code' );

                /* Abilito/riabilito questa utenza */
                update_user_meta( $id_user, 'wpdk_user_internal-status', self::kUserStatusConfirmed );

                /* Invio mail con il template definito nei setting all'utente $result appena inserito */
                $extra = array(
                    WPXTREME_MAIL_PLACEHOLDER_USER_PASSWORD => $new_password
                );
                WPXtremeMailCustomPostType::mail( $settings['email_slug_confirmed'], $id_user, false, false, $extra );

                /* @todo Aggiungere filtro e pagina (slug) di redirect */
                $message = __( 'Thanks, please check your email for retrive username and password', WPDK_TEXTDOMAIN );

                $html = <<< HTML
<h2>{$message}</h2>
HTML;
                return $html;
            }
        }
        return false;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // is/has zone
    // -----------------------------------------------------------------------------------------------------------------

    /// Check if an user has a specify capability
    /**
     * Restutuisce true se l'utente passato negli inputs (o l'utente corrente se non viene passato id utente) possiede
     * un determinato permesso (capability)
     *
     * @static
     *
     * @param string $cap     Capability ID
     * @param int    $id_user User ID or null for get current user ID
     *
     * @retval bool True se l'utente supporta la capability
     */
    public static function hasCap( $cap, $id_user = null ) {
        if ( is_null( $id_user ) ) {
            $id_user = get_current_user_id();
        }
        $user = new WP_User( $id_user );
        if ( $user ) {
            return $user->has_cap( $cap );
        }
        return false;
    }

    /// Check if an user has one or more capabilities
    /**
     * Restituisce true se almeno uno dei permessi passati negli inputs è presente nella lista permessi utente.
     *
     * @static
     *
     * @param array $caps    Capabilities array
     * @param int   $id_user User ID or null for get current user ID
     *
     * @retval bool Se almeno uno dei permessi è presente restituisce true, altrimenti false
     */
    public static function hasCaps( $caps, $id_user = null ) {
        if ( is_null( $id_user ) ) {
            $id_user = get_current_user_id();
        }
        $user = new WP_User( $id_user );
        if ( $user ) {
            $all_caps = $user->allcaps;
            foreach ( $caps as $cap ) {
                if ( isset( $all_caps[$cap] ) ) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Restituisce true se l'utente corrente ha uno o più Ruoli nel suo profilo
     *
     * @todo Da verificare
     *
     * @param string | array $a Regola o array di rogle da verificare. Per ritornare true, in caso di array, basta che
     *                          una sola regola sia supportata dall'utente. Queste sono da considerarsi in OR non is AND
     *
     * @retval bool Se almeno una delle regole passate negli inputs è supportata ritirna True. Altrimenti False.
     */
    public static function hasCurrentUserRoles( $a ) {

        if ( !function_exists( 'wp_get_current_user' ) ) {
            require_once( ABSPATH . '/wp-includes/pluggable.php' );
        }
        global $wp_roles;

        if ( !isset( $wp_roles ) ) {
            $wp_roles = new WP_Roles();
        }

        $current_user = wp_get_current_user();
        $roles        = $current_user->roles;
        if ( is_array( $a ) ) {
            foreach ( $a as $i ) {
                if ( in_array( $i, $roles ) ) {
                    return true;
                }
            }
            return false;
        } else {
            return in_array( $a, $roles );
        }
    }

    /// This is a joke
    public static function isUserAdministrator() {
        $id_user = get_current_user_id();
        return ( $id_user == 1 );
    }

}
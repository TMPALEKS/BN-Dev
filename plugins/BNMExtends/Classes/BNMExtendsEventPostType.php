<?php
/**
 * Gestisce il post type di tipo Eventi
 *
 * @package            BNMExtends
 * @subpackage         BNMExtendsEventPostType
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (C)2011 Saidmade Srl.
 * @created            11/11/11
 * @version            1.0
 *
 */

class BNMExtendsEventPostType {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Array dei campi del meta box in backend nel formato SDF
     *
     * @todo Aggiungere creazione biglietti per brunch: checkbox, base price, questo dovrà creare
     * 3 biglietti diversi da associare a questo event.
     * 60 euro comulativo, 35 euro adulto singolo, 12 euro (< 12 anni) singolo bambino
     *
     * @static
     * @return array
     */
    public static function fields() {
        global $post;

        /* Per comodità la data di default di un evento è adesso */
        $event_date  = date( __( 'm/d/Y H:i', 'bnmextends' ) );
        $artist_name = '';
        $artist_id   = '';
        $append_img  = '';

        /* Checkbox per creazione ticket */
        $arrayTicket = array(
            'type'  => WPDK_FORM_FIELD_TYPE_CHECKBOX,
            'name'  => 'bnm_create_ticket',
            'label' => __( 'Create Ticket', 'bnmextends' ),
            'value' => 'y'
        );

        /* Input per prezzo base biglietto da creare in automatico */
        $arrayPrice = array(
            'type'  => WPDK_FORM_FIELD_TYPE_NUMBER,
            'name'  => 'bnm_create_base_price',
            'label' => __( 'Base price', 'bnmexteds' ),
            'value' => '0'
        );

        /* Biglietti brunch */
        $arrayTicketBrunch = array(
            'type'  => WPDK_FORM_FIELD_TYPE_CHECKBOX,
            'name'  => 'bnm_create_ticket_brunch',
            'label' => __( 'Create Ticket for Brunch', 'bnmextends' ),
            'title' => __( 'Crea altre 3 biglietti e li associa questo evento: 60 euro comulativo, 35 euro adulto singolo, 12 euro (< 12 anni) singolo bambino', 'bnmextends'),
            'value' => 'y'
        );

        /* Se impostato sono in edit */
        if ( isset( $post ) ) {
            /* WPML sanitize */
            $id_post = self::idWPMLDefaultLanguage( $post->ID );

            /* Biglietto standard creato? */
            $ticket = get_post_meta( $id_post, 'bnm-event-ticket', true );
            if ( !empty( $ticket ) ) {

                /* Verifico che esista fisicamente */
                $ticket_product = get_post( $ticket );

                if ( empty( $ticket_product ) || $ticket_product->post_status == 'trash' ) {
                    /* Qualcuno ha cancellato il prodotto, quindi elimino il post meta */
                    delete_post_meta( $id_post, 'bnm-event-ticket' );
                } else {
                    $arrayTicket = array(
                        'type'   => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'   => 'bnm_id_ticket',
                        'label'  => __( 'Ticket', 'bnmextends' ),
                        'value'  => $ticket,
                        'append' => sprintf( '<a href="/wp-admin/post.php?post=%s&action=edit">Edit</a>', $ticket )
                    );

                    $arrayPrice = null;
                }
            }

            /* Simile controllo per la tripletta dei brunch, a questo punto usiamo un solo custom field. */
            $tickets_brunch = get_post_meta( $id_post, 'bnm-event-tickets-brunch', true );
            if( !empty( $tickets_brunch ) ) {

                /* Recupero ogni singolo id dei biglietti brunch. */
                $array_single_tickets_brunch = explode( ',', $tickets_brunch );

                /* Controllo tutti e tre i biglietti brunch, se ne manca anche uno solo li elimino tutti */
                $delete_all_tickets_brunch = false;
                foreach( $array_single_tickets_brunch as $single_ticket_brunch ) {
                    $check_brunch = get_post( $single_ticket_brunch );
                    if ( empty( $check_brunch ) || $check_brunch->post_status == 'trash' ) {
                        $delete_all_tickets_brunch = true;
                        break;
                    }
                }

                /* Come detto se ne manca anche uno solo (o è nel cestino) li elimino tutti. */
                if( $delete_all_tickets_brunch ) {
                    foreach( $array_single_tickets_brunch as $single_ticket_brunch ) {
                        wp_delete_post( $single_ticket_brunch, true );
                    }
                    /* Elimino anche l'elenco nei custom post dell'evento. */
                    delete_post_meta( $id_post, 'bnm-event-tickets-brunch' );
                }

                /* Costruisco i links da appendere per l'edit singolo. */
                $links_tickets_brunch = array();
                foreach ( $array_single_tickets_brunch as $single_ticket_brunch ) {
                    $links_tickets_brunch[] = sprintf( '<a href="/wp-admin/post.php?post=%s&action=edit">%s</a>', $single_ticket_brunch, $single_ticket_brunch );
                }

                $arrayTicketBrunch = array(
                    'type'   => WPDK_FORM_FIELD_TYPE_TEXT,
                    'name'   => 'bnm_id_tickets_brunch',
                    'label'  => __( 'Tickets Brunch', 'bnmextends' ),
                    'value'  => $tickets_brunch,
                    'append' => join(' ', $links_tickets_brunch )
                );

            }

            $event_date = WPDKDateTime::formatFromFormat( get_post_meta( $id_post, kBNMExtendsEventMetaDateAndTime, true ), 'YmdHi', __( 'm/d/Y H:i', 'bnmextends' ) );
            if ( !$event_date ) {

                /* Per comodità la data di default di un evento è adesso */
                $event_date = date( __( 'm/d/Y H:i', 'bnmextends' ) );
            }

            /* Recuper info sull'artista - se associato */
            $artist                  = self::artistWithEventID( $id_post );
            $artist_thumbnail_small  = '';
            $artist_thumbnail_medium = '';
            $artist_thumbnail_style  = 'style="display:none"';

            if ( $artist ) {
                $artist_name            = $artist['post_title'];
                $artist_id              = $artist['ID'];
                $artist_thumbnail_small = BNMExtendsArtistPostType::thumbnailSrc( $artist_id, kBNMExtendsThumbnailSizeSmallKey );
                if ( $artist_thumbnail_small ) {
                    $artist_thumbnail_style = '';
                }
                $artist_thumbnail_medium = BNMExtendsArtistPostType::thumbnailSrc( $artist_id, kBNMExtendsThumbnailSizeMediumKey );

            }

            /* Thumbnails */
            $append_img = <<< HTML
<img src="{$artist_thumbnail_small}" border="0" alt="Artista" class="bnmArtistThumbnail" {$artist_thumbnail_style} />
<img src="{$artist_thumbnail_medium}" border="0" alt="Artista" class="bnmArtistThumbnailBig" style="display:none" />
HTML;
        }

        $fields = array(
            __( 'Event', 'bnmextends' ) => array(
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_DATETIME,
                        'name'  => kBNMExtendsEventMetaDateAndTime,
                        'label' => __( 'Data e ora evento' ),
                        'size'  => 18,
                        'value' => $event_date
                    )
                ),
                array(
                    array(
                        'type'        => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'        => 'bnm-event-artist-name',
                        'label'       => __( 'Associa artista' ),
                        'placeholder' => __( 'Nome artista' ),
                        'value'       => $artist_name
                    ),
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_HIDDEN,
                        'name'   => kBNMExtendsEventMetaArtistID,
                        'value'  => $artist_id,
                        'append' => $append_img
                    )
                ),
                array( $arrayTicket, $arrayPrice ),
                array( $arrayTicketBrunch )
            )
        );
        return $fields;
    }

    /**
     * Restituisce il nome della tabella comune tra eventi e artisti
     *
     * @static
     * @return string
     */
    public static function tableName() {
       global $wpdb;
       $result = sprintf('%s%s', $wpdb->prefix, kBNMExtendsDatabaseTableEventsArtistsName);

       return $result;
     }

    public static function scriptLocalization() {
        $result = array(
            'ajaxURL'                       => WPDKWordPress::ajaxURL(),

            'login_empty'                   => __( 'Empty parameters!', 'bnmextends' ),

            'placeholdersReservationsTitle' => __( 'Reservations Dinner Table', 'bnmextends' ),
            'placeholdersMaxPlacesMessage'  => __( 'The number of seats you select cannot exceed the number of tickets booked', 'bnmextends' ),
            'placeholdersMinPlacesMessage'  => __( 'You have select a minimun number of seats equal to the number of tickets', 'bnmextends' ),
            'Cancel'                        => __( 'Cancel', 'bnmextends' ),
            'Ok'                            => __( 'Ok', 'bnmextends' ),

            'boxOfficerMessage'             => __( 'For retrive full register user information, insert email before.', 'bnmextends' ),
            'under26'                       => __( 'Looks like your are up to 26 years old. Please upload a PDF of your ID card to get discount upon tickets.', 'bnmextends' ),
            'over65'                        => __( 'Looks like our age is 65 or more. Please upload a PDF of your ID card to get discount upon tickets.', 'bnmextends' ),

            'timeOnlyTitle'                 => __( 'Choose Time', 'bnmextends' ),
            'timeText'                      => __( 'Time', 'bnmextends' ),
            'hourText'                      => __( 'Hour', 'bnmextends' ),
            'minuteText'                    => __( 'Minutes', 'bnmextends' ),
            'secondText'                    => __( 'Seconds', 'bnmextends' ),
            'currentText'                   => __( 'Now', 'bnmextends' ),
            'dayNamesMin'                   => __( 'Su,Mo,Tu,We,Th,Fr,Sa', 'bnmextends' ),
            'monthNames'                    => __( 'January,February,March,April,May,June,July,August,September,October,November,December', 'bnmextends' ),
            'closeText'                     => __( 'Close', 'bnmextends' ),
            'phpDateTimeFormat'             => __( 'm/d/Y', 'bnmextends' ),
            'dateFormat'                    => __( 'mm/dd/yy', 'bnmextends' )
        );
        return $result;
    }

    public static function enqueueStyles() {
        global $typenow;
        if ($typenow == kBNMExtendsEventPostTypeKey) {
            wp_enqueue_style('bnm-event', kBNMExtendsURI . 'css/EventMetaBox.min.css');
        }
    }

    public static function enqueueScripts() {
        global $typenow;
        if ( $typenow == kBNMExtendsEventPostTypeKey ) {
            wp_enqueue_script( 'bnm-events', kBNMExtendsURI . 'js/events.js', array(), kBNMExtendsVersion, true );
            /* @todo Questa riga può essere eliminata se la localizzazione viene già caricata */
            wp_localize_script( 'bnm-events', 'bnmExtendsJavascriptLocalization', self::scriptLocalization() );
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Post Type
    // -----------------------------------------------------------------------------------------------------------------

    public static function registerPostType() {
        $labels = array(
            'name'               => 'Eventi',
            'singular_name'      => 'Evento',
            'add_new'            => 'Aggiungi nuovo',
            'add_new_item'       => 'Aggiungi Nuovo Evento',
            'edit_item'          => 'Modifica',
            'new_item'           => 'Nuovo Evento',
            'view_item'          => 'Visualizza Evento',
            'search_items'       => 'Ricerca Evento',
            'not_found'          => 'Eventi non trovati',
            'not_found_in_trash' => 'Nessun Evento nel cestino',
            'parent_item_colon'  => ''
        );
        $args   = array(
            'labels'               => $labels,
            'public'               => true,
            'publicly_queryable'   => true,
            'exclude_from_search' => false,
            'show_ui'              => true,
            'menu_icon'            => get_stylesheet_directory_uri() . '/images/admin_logo.png',
            'query_var'            => true,
            'rewrite'              => array(
                'slug'       => 'evento', //__('event', 'bnmextends'),
                'with_front' => false
            ),
            'capability_type'      => 'post',
            'hierarchical'         => false,
            'menu_position'        => kBNMExtendsEventPostTypeMenuItemPosition,
            'supports'             => array(
                'thumbnail',
                'title',
                'editor',
                'excerpt'
            ),
            'register_meta_box_cb' => array( __CLASS__, 'metaBox' )
        );

        /* Registro il mio custom post type */
        register_post_type( kBNMExtendsEventPostTypeKey, $args);

        /* Sync post meta */
        if( defined( 'ICL_LANGUAGE_CODE' ) ) {
            add_action( 'added_post_meta', array( __CLASS__, 'added_product_meta' ), 10, 4 );
            add_action( 'updated_post_meta', array( __CLASS__, 'updated_product_meta' ), 10, 4 );
            add_action( 'deleted_post_meta', array( __CLASS__, 'deleted_product_meta' ), 10, 4 );
        }

        /* Hook per il salvataggio dei dati extra */
        add_action('save_post', array(__CLASS__, 'save_post'));

        /* Register columns */
        add_filter('manage_edit-' . kBNMExtendsEventPostTypeKey . '_columns', array(__CLASS__, 'registerColumns'));

        /* Manage view custom columns */
        add_action('manage_' . kBNMExtendsEventPostTypeKey . '_posts_custom_column', array(__CLASS__, 'manageColumns'));

        /* Register sortable columns */
        if (self::isWPMLNoDefaultLanguage()) {
            add_filter('manage_edit-' . kBNMExtendsEventPostTypeKey . '_sortable_columns', array(__CLASS__, 'registerSortableColumns'));
            /* Fetch sortables */
            add_filter('request', array(__CLASS__, 'request'));
        }

        /* Cambia il titolo al meta box standard delle miniature */
        add_action('do_meta_boxes', array(__CLASS__, 'replaceThumbnailMetaBoxTitle'));

        /* Quando un evento viene eliminato */
        add_action('before_delete_post', array(__CLASS__, 'before_delete_post'));

        /* @todo Ne basterebbe anche uno solo... */
        add_action('admin_head', array(__CLASS__, 'enqueueStyles'));
        add_action('admin_head', array(__CLASS__, 'enqueueScripts'));
      

        /* @todo: Add clone button - da finire e capire come implementare */
        BNMExtendsClone::register();
    }

    public static function updated_product_meta( $meta_id, $object_id, $meta_key, $_meta_value ) {

        $wpml_object_id = icl_object_id( $object_id, kBNMExtendsEventPostTypeKey, true, 'en' );

        if ( $wpml_object_id != $object_id ) {
            update_post_meta( $wpml_object_id, $meta_key, $_meta_value );
        }
    }

    public static function added_product_meta( $meta_id, $object_id, $meta_key, $_meta_value ) {

        $wpml_object_id = icl_object_id( $object_id, kBNMExtendsEventPostTypeKey, true, 'en' );

        if ( $wpml_object_id != $object_id ) {
            update_post_meta( $wpml_object_id, $meta_key, $_meta_value );
        }
    }
    public static function deleted_product_meta( $meta_id, $object_id, $meta_key, $_meta_value ) {

        $wpml_object_id = icl_object_id( $object_id, kBNMExtendsEventPostTypeKey, true, 'en' );

        if ( $wpml_object_id != $object_id ) {
            delete_post_meta( $wpml_object_id, $meta_key );
        }
    }

    public static function replaceThumbnailMetaBoxTitle() {
        global $typenow;

        if ( $typenow == kBNMExtendsEventPostTypeKey ) {

            /* @todo Non pulitissimo, va bene per WordPress con backend monolingua - considerare pezza */
            add_filter( 'admin_post_thumbnail_html', function( $content ) {
                $content = str_replace( 'in evidenza', 'evento', $content );
                return $content;
            } );
            remove_meta_box( 'postimagediv', kBNMExtendsEventPostTypeKey, 'side' );
            add_meta_box( 'postimagediv', __( 'Event Image', 'bnmextends' ), 'post_thumbnail_meta_box', kBNMExtendsEventPostTypeKey, 'side', 'low' );
        }
    }

    public static function registerSortableColumns( $columns ) {
        $columns[kBNMExtendsEventMetaDateAndTime] = array(kBNMExtendsEventMetaDateAndTime, true);
        return $columns;
    }

    public static function request( $vars ) {
        global $typenow;

        if ( $typenow != kBNMExtendsEventPostTypeKey ) {
            return $vars;
        }
        // Sostituisco testo
        if ( isset( $vars['orderby'] ) ) {
            if ( kBNMExtendsEventMetaDateAndTime == $vars['orderby'] ) {
                $vars = array_merge( $vars, array(
                                                 'meta_key'  => kBNMExtendsEventMetaDateAndTime,
                                                 'orderby'   => 'meta_value_num'
                                            ) );
            }
        } else {
            $vars = array_merge( $vars, array(
                                             'meta_key'  => kBNMExtendsEventMetaDateAndTime,
                                             'orderby'   => 'meta_value_num'
                                        ) );
        }
        return $vars;
    }

    public static function manageColumns( $column ) {
        global $post;

        if ( kBNMExtendsEventMetaDateAndTime == $column ) {

            /* Recupera il custom field dal post della lingua di base */
            $id_event    = self::idWPMLDefaultLanguage( $post->ID );
            $event_date  = get_post_meta( $id_event, kBNMExtendsEventMetaDateAndTime, true );
            $format_date = WPDKDateTime::timeNewLine( mysql2date( __( 'm/d/Y H:i', 'bnmextends' ), $event_date ) );
            echo $format_date;

        } elseif ( 'icon' == $column ) {
            self::thumbnail( $post->ID );
        }
    }

    public static function registerColumns( $columns ) {
        $new = array(
            'icon'  => 'Foto',
            kBNMExtendsEventMetaDateAndTime => 'Data evento'
        );
        $columns['title'] = 'Evento';
        $columns          = WPDKArray::insert($columns, $new, 1);
        return $columns;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress (meta box) Integration
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Aggiunge un MetaBox alla schermata di inserimento/modifica di un pst di tipo Artista
     *
     * @return void
     */
    public static function metaBox() {
        if ( self::isWPMLNoDefaultLanguage() ) {
            add_meta_box( kBNMExtendsEventPostTypeKey . '-div', 'Anagrafica', array(__CLASS__, 'metaBoxView'), kBNMExtendsEventPostTypeKey, 'advanced', 'high' );
        }
    }

    public static function metaBoxView() {
        WPDKForm::nonceWithKey( 'event' );
        WPDKForm::htmlForm( self::fields() );
    }

    public static function save_post($ID /*, $post*/) {

        /* Local variables. */
        $ID               = absint( $ID );
        $post_type        = get_post_type();
        $post_type_object = get_post_type_object( $post_type );
        $capability       = '';

        /* Do nothing on auto save. */
        if ( defined( 'DOING_AUTOSAVE' ) && true === DOING_AUTOSAVE ) {
            return;
        }

        /* This function only applies to the following post_types. */
        if ( !in_array( $post_type, array( kBNMExtendsEventPostTypeKey ) ) ) {
            return;
        }

        /* Verify this came from the our screen and with proper authorization. */
        if ( !WPDKForm::isNonceVerify( 'event' ) ) {
            return;
        }

        /* Find correct capability from post_type arguments. */
        if ( isset( $post_type_object->cap->edit_posts ) ) {
            $capability = $post_type_object->cap->edit_posts;
        }

        /* Return if current user cannot edit this post. */
        if ( !current_user_can( $capability ) ) {
            return;
        }

        /* Save */

        $post = get_post( $ID );
        if ( $post->post_type == kBNMExtendsEventPostTypeKey ) {

            /*
             * Nota WPML: qui siamo per forza in "italiano" (lingua base) in quanto il meta box non viene visualizzato
             * nell'inserimento e modifica in inglese. Questo perchè la data e l'ora dell'evento sono sempre le stesse
             * anche se cambia l'ID dell'artista.
             */

            /* Store DateTime */
            if ( !empty( $_POST[kBNMExtendsEventMetaDateAndTime] ) ) {
                $new = WPDKDateTime::formatFromFormat( esc_attr( $_POST[kBNMExtendsEventMetaDateAndTime] ), __( 'm/d/Y H:i', 'bnmextends' ), 'YmdHi' );
                update_post_meta( $ID, kBNMExtendsEventMetaDateAndTime, $new );
            } else {
                delete_post_meta( $ID, kBNMExtendsEventMetaDateAndTime );
            }

            /* Store Artist */
            if( !empty( $_POST[kBNMExtendsEventMetaArtistID] ) ) {
                $new = $_POST[kBNMExtendsEventMetaArtistID];
                update_post_meta( $ID, kBNMExtendsEventMetaArtistID, $new );
            } else {
                delete_post_meta( $ID, kBNMExtendsEventMetaArtistID );
            }

            /* Crea, se richiesto, un prodotto di tipo biglietto associato. Dato che il metodo createTicket() a causa
            di un baco di WPML pulisce l'array $_POST, i dati li prendo subito. */

            $check_standard_ticket = isset( $_POST['bnm_create_ticket'] ) ? $_POST['bnm_create_ticket'] : '';
            $price_standard_ticket = isset( $_POST['bnm_create_base_price'] ) ? $_POST['bnm_create_base_price'] : '';
            $check_brunch_ticket   = isset( $_POST['bnm_create_ticket_brunch'] ) ? $_POST['bnm_create_ticket_brunch'] : '';

            $ticket = get_post_meta( $ID, 'bnm-event-ticket', true );

            if ( empty( $ticket ) && !empty( $check_standard_ticket ) && wpdk_is_bool( $check_standard_ticket )
            ) {
                /* Memorizzo il prezzo base */
                $base_price = floatval( str_replace( ',', '.', trim( $price_standard_ticket ) ) );
                update_post_meta( $ID, 'bnm_create_ticket_base_price', $base_price );
                self::createTicket( $ID, $post );
            }

            /* Ticket brunch. */
            $tickets_brunch = get_post_meta( $ID, 'bnm-event-tickets-brunch', true );
            if ( empty( $tickets_brunch ) && !empty( $check_brunch_ticket ) &&
                wpdk_is_bool( $check_brunch_ticket )
            ) {
                /* Creo i biglietti brunch */
                self::createTicketsBrunch( $ID, $post );
            }
        }
        return $ID;
    }

    /**
     * Crea un biglietto (standard) e lo collega a questo evento. Le regole che vengono seguite sono le seguenti:
     *
     * Prezzo base: 35
     * Prezzo online = Prezzo base - 5 - qty 8
     * Under 26 (bnm_role_3) solo ore 23:00 = Prezzo base - 40% - qty 1
     * Over 65 (bnm_role_4) = Prezzo base - 40% - qty 1
     * Club (bnm_role_5) = Prezzo base - 40% - qty 1
     * Club Platinum (bnm_role_5) = Gratis - qty 2
     *
     * @note La data del post (post_date e post_date_gmt) è quella della creazione
     *
     * @static
     *
     * @param int    $ID   Del post evento che sto salvando
     * @param object $post Il post
     */
    public static function createTicket( $ID, $post ) {
        if ( class_exists( 'WPSmartShopProductMaker' ) ) {
            /* Prima di tutto disattivo la creazione ticket per l'evento $ID */
            update_post_meta( $ID, 'bnm-event-ticket', '----' );

            /* Product Maker */
            $productMaker = new WPSmartShopProductMaker();

            /* Preparo il titolo */
            $event_date = get_post_meta( $ID, kBNMExtendsEventMetaDateAndTime, true);
            $event_date = WPDKDateTime::formatFromFormat( $event_date, 'YmdHi', __('m/d/Y H:i', 'bnmextends') );
            $body_title = sprintf( '%s - %s', $event_date, $post->post_title );
            $productMaker->formatTitle( '', $body_title, '' );

            /* Recupero orario da 23/03/2012 09:43 */
            $event_date_parts = explode( ' ', $event_date );
            $time_parts       = explode( ':', $event_date_parts[1] );
            $hour             = $time_parts[0];

            /* Prezzo base */
            $productMaker->price = get_post_meta( $ID, 'bnm_create_ticket_base_price', true);

            /* Regole per operatore Box Officer. */
            $productMaker->addPriceRule( 'bnm_role_8', ( $productMaker->price - 5 ), 0, 0, 0 );

            /* Regole per operatore Intermediario. */
            $productMaker->addPriceRule( 'bnm_role_7', 0, 0, 0, 20 );

            /* Regole sul prezzo advance (online). */
            $productMaker->addPriceRule( kWPSmartShopProductTypeRuleOnlinePrice, ( $productMaker->price - 5 ), 0, 0, 0 );

            if( $hour == 23 ) {
                /* Under 26 rules - solo se spettacolo 23 */
                $productMaker->addPriceRule( 'bnm_role_3', 0, 1, 1, 40 );
            }

            /* Over 65 */
            $productMaker->addPriceRule( 'bnm_role_4', 0, 1, 1, 40 );
            /* Club Member */
            $productMaker->addPriceRule( 'bnm_role_5', 0, 1, 1, 40 );
            /* Club Platinum */
            $productMaker->addPriceRule( 'bnm_role_6', 0, 2, 2, 100 );

            /* Convenzionato A */
            $productMaker->addPriceRule( 'bnm_role_10', 0, 2, 2, 20 );
            /* Convenzionato B */
            $productMaker->addPriceRule( 'bnm_role_11', 0, 1, 1, 20 );

            /* Lo ripeto qui sotto per farli apparire in sequenza, altrimenti il convenzionato finisce in alto */
            if( $hour == 23 ) {
                /* Convenzionato C */
                $productMaker->addPriceRule( 'bnm_role_12', 18, 1, 1, 0 );
            } else {
                $productMaker->addPriceRule( 'bnm_role_12', 0, 1, 1, 20 );
            }

            /* Disponibile fino a: 2 ore prima */
            $event_date_timestamp = WPDKDateTime::makeTimeFrom( 'd/m/Y H:i', $event_date );
            $event_date_timestamp -= ( 60 * 60 ) * 2;
            $productMaker->availability( '', date( 'd/m/Y H:i', $event_date_timestamp ) );

            /* Warehouse: magazzino. */
            $productMaker->warehouse( 300 );

            /* Varianti, la dicitura è sempre in inglse, viene tradotto a parte. */
            $dinner = array(
                'model' => sprintf( '%s, %s', BNMEXTENDS_WITHOUT_DINNER_RESERVATION_KEY, BNMEXTENDS_WITH_DINNER_RESERVATION_KEY )
            );
            $productMaker->addVariant( 'Dinner', $dinner );

            /* Altrimenti WPML non parte... :( */
            if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
                unset( $_POST );
            }
            $id_post = $productMaker->create();

            /* Imposto il tipo prodotto tramite il nome */

            $terms_by_name = WPSmartShopProductTypeTaxonomy::arrayTermsWithKeyName();

            if( $hour == 23 ) {
                $terms = array(
                    $terms_by_name['biglietti di ingresso'],
                    $terms_by_name['ore 23'],
                    $terms_by_name['ingresso abbonamento under 26'],
                    $terms_by_name['ingresso abbonamento rosa'],
                    $terms_by_name['ingresso abbonamento satchmo'],
                    $terms_by_name['ingresso abbonamento verde'],
                    $terms_by_name['ingresso club membership'],
                    $terms_by_name['ingresso dinner voucher'],
                    $terms_by_name['ingresso platinum membership'],
                    $terms_by_name['ingresso show voucher'],
                );
            } else {
                $terms = array(
                    $terms_by_name['biglietti di ingresso'],
                    $terms_by_name['ore 21'],
                    $terms_by_name['ingresso abbonamento satchmo'],
                    $terms_by_name['ingresso dinner voucher'],
                    $terms_by_name['ingresso platinum membership'],
                    $terms_by_name['ingresso show voucher'],
                );
            }

            /* Imposto la tipologia prodotto. */
            wp_set_post_terms( $id_post, $terms, kWPSmartShopProductTypeTaxonomyKey );

            /* Collego questo evento a questo biglietto */
            update_post_meta( $ID, 'bnm-event-ticket', $id_post );
            
        }
    }

    /**
     * Crea i 3 biglietti per il brunch.
     *
     * @static
     * @param $ID
     * @param $post
     */
    public static function createTicketsBrunch( $ID, $post ) {
        if ( class_exists( 'WPSmartShopProductMaker' ) ) {
            /* Prima di tutto disattivo la creazione ticket per l'evento $ID */
            update_post_meta( $ID, 'bnm-event-tickets-brunch', '----' );

            $terms_by_name = WPSmartShopProductTypeTaxonomy::arrayTermsWithKeyName();

            /* Product Maker */
            $productMaker = new WPSmartShopProductMaker();

            /*
             * 1.
             */

            /* Preparo il titolo */
            $event_date = get_post_meta( $ID, kBNMExtendsEventMetaDateAndTime, true );
            $event_date = WPDKDateTime::formatFromFormat( $event_date, 'YmdHi', __( 'm/d/Y H:i', 'bnmextends' ) );
            $body_title = sprintf( '%s - %s', $event_date, 'Brunch Cumulativo' );
            $productMaker->formatTitle( '', $body_title, '' );

            /* Recupero orario da 23/03/2012 09:43 */
            $event_date_parts = explode( ' ', $event_date );
            $time_parts       = explode( ':', $event_date_parts[1] );
            $hour             = $time_parts[0];

            /* Prezzo base */
            $productMaker->price = 60;

            /* Regole sul prezzo */
            $productMaker->addPriceRule( kWPSmartShopProductTypeRuleOnlinePrice, 60, 0, 0, 0 );
            /* Club Platinum */
            $productMaker->addPriceRule( 'bnm_role_6', 0, 1, 1, 100 );


            /* Disponibile fino a: 2 ore prima */
            $event_date_timestamp = WPDKDateTime::makeTimeFrom( 'd/m/Y H:i', $event_date );
            $event_date_timestamp -= ( 60 * 60 ) * 2;
            $productMaker->availability( '', date( 'd/m/Y H:i', $event_date_timestamp ) );

            /* Varianti */
            $comulative = array(
                'model'    => sprintf( '%s, %s, %s', BNMEXTENDS_2_ADULTS_KEY, BNMEXTENDS_2_ADULTS_1_CHILD_KEY, BNMEXTENDS_2_ADULTS_2_CHILDREN_KEY ),
            );
            $productMaker->addVariant( 'Comulative', $comulative );

            /* Warehouse: magazzino. */
            $productMaker->warehouse( 180 );

            /* Altrimenti WPML non parte... :( */
            if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
                unset( $_POST );
            }
            
            $ids_tickets_brunch[] = $id_ticket = $productMaker->create();

            /* Imposto la tipologia prodotto. */
            wp_set_post_terms( $id_ticket, array( $terms_by_name['brunch cumulativo'] ), kWPSmartShopProductTypeTaxonomyKey );
            
            /*
             * 2.
             */

            $body_title = sprintf( '%s - %s', $event_date, 'Brunch Adulto Singolo' );
            $productMaker->formatTitle( '', $body_title, '' );

            $productMaker->clearVariants();

            /* Prezzo base */
            $productMaker->price = 35;

            /* Regole sul prezzo */
            $productMaker->clearPriceRules();
            $productMaker->addPriceRule( kWPSmartShopProductTypeRuleOnlinePrice, 35, 0, 0, 0 );

            /* Warehouse: magazzino. */
            $productMaker->warehouse( 15 );

            $ids_tickets_brunch[] = $id_ticket = $productMaker->create();

            /* Imposto la tipologia prodotto. */
            wp_set_post_terms( $id_ticket, array( $terms_by_name['brunch adulto singolo'] ), kWPSmartShopProductTypeTaxonomyKey );

            /*
             * 3.
             */

            $body_title = sprintf( '%s - %s', $event_date, 'Brunch Bambino Singolo' );
            $productMaker->formatTitle( '', $body_title, '' );

            /* Prezzo base */
            $productMaker->price = 12;

            /* Regole sul prezzo */
            $productMaker->clearPriceRules();
            $productMaker->addPriceRule( kWPSmartShopProductTypeRuleOnlinePrice, 12, 0, 0, 0 );

            /* Warehouse: magazzino. */
            $productMaker->warehouse( 5 );

            $ids_tickets_brunch[] = $id_ticket = $productMaker->create();

            /* Imposto la tipologia prodotto, 59 è 'Biglietto Brunch Adulto Singolo'. */
            wp_set_post_terms( $id_ticket, array( $terms_by_name['brunch bambino singolo 12a'] ), kWPSmartShopProductTypeTaxonomyKey );

            /*
             * Fine
             */

            /* Inserisco gli ID (separati da virgola) nel custom post dell'evento. */
            update_post_meta( $ID, 'bnm-event-tickets-brunch', join( ',', $ids_tickets_brunch ) );
        }
    }



    // -----------------------------------------------------------------------------------------------------------------
    // Artist Integration
    // -----------------------------------------------------------------------------------------------------------------


    /**
     * This Hook is run when a post will deleted definitly, not when it is put in trashcan
     *
     * @static
     *
     * @param int $post_id ID del post che sta per essere eliminato
     *
     * @return bool
     */
    public static function before_delete_post( $post_id ) {
        global $wpdb;

        $post_id = self::idWPMLDefaultLanguage( $post_id );
        $post    = get_post( $post_id );
        if ( $post->post_type == kBNMExtendsEventPostTypeKey ) {

            /* Elimino il biglietto standard. */
            $standard_ticket = get_post_meta( $post_id, 'bnm-event-ticket', true );
            if( !empty( $standard_ticket ) ) {
                wp_delete_post( $standard_ticket, true );
            }

            /* Elimino i biglietti brunch. */
            $tickets_brunch = get_post_meta( $post_id, 'bnm-event-tickets-brunch', true );
            if( !empty( $tickets_brunch) ) {
                $array_single_tickets_brunch = explode( ',', $tickets_brunch );
                foreach( $array_single_tickets_brunch as $single_ticket_brunch ) {
                    wp_delete_post( $single_ticket_brunch, true );
                }
            }

            /* Delete all images attachment too */
            $sql          = sprintf("SELECT ID FROM `%s` WHERE `post_parent` = %s AND `post_type` = 'attachment'", $wpdb->posts, $post_id);
            $attachmentID = $wpdb->get_var($sql);
            wp_delete_attachment($attachmentID, true);

            /* Elimino l'evento anche dalla tabella di connessione tra eventi ed artisti
            $sql    = sprintf('DELETE FROM `%s` WHERE `id_event` = %s', self::tableName(), $post_id);
            $result = $wpdb->query($sql);
            return $result;
            */
        }
        return false;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // WPML Integration
    // -----------------------------------------------------------------------------------------------------------------

    public static function isWPMLNoDefaultLanguage() {
        return !defined('ICL_LANGUAGE_CODE') || ICL_LANGUAGE_CODE == kBNMExtendsWPMLIntegrationDefaultLanguage;
    }

    /**
     * Compatibilità con WPML. Restituisce l'id della lingua base per condividere i custom field e altre informazioni
     * condivise, come le thumbnail ad esempio.
     *
     * @static
     *
     * @param $id
     *
     * @return int
     */
    public static function idWPMLDefaultLanguage($id) {
        return defined('ICL_LANGUAGE_CODE') ? icl_object_id($id, kBNMExtendsEventPostTypeKey, true, kBNMExtendsWPMLIntegrationDefaultLanguage) : $id;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Commodity
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce un array con le informazioni di un artista a partire dall'id di un evento. In pratica verifica se un
     * evento ha un artista collegato.
     *
     * @static
     *
     * @param $id_event
     *
     * @return mixed|null
     */
    public static function artistWithEventID( $id_event ) {
        $artist    = null;
        $id_artist = get_post_meta( $id_event, kBNMExtendsEventMetaArtistID, true );
        if ( $id_artist ) {
            /* WPML sanitize */
            $id_artist = icl_object_id($id_artist,kBNMExtendsArtistPostTypeKey, true );
            //$id_artist = BNMExtendsArtistPostType::idWPMLDefaultLanguage( $id_artist );
            $artist    = get_post( $id_artist, ARRAY_A );
        }
        return $artist;
    }

    /**
     * Immagine di PlaceHolder
     *
     * @static
     *
     * @param string $size
     */
    public static function thumbnailPlaceholder( $size = kBNMExtendsThumbnailSizeSmallKey ) {
        ?><img src="<?php echo self::thumbnailPlaceholderSrc() ?>"/><?php
    }

    public static function thumbnailPlaceholderSrc( $size = kBNMExtendsThumbnailSizeSmallKey ) {
        // @todo Ritorna il placeholder sbagliato
        return kBNMExtendsURI . 'css/images/placeholder-artist-55x55.png';
    }


    /**
     * Restituisce l'immagine miniatura in compatibilità con WPML. Se l'id passato negli inputs ha l'immagine, sia esso
     * italiano o inglese, prende quella, altrimenti calcola l'id della lingua base e cerca la miniatura in quella.
     *
     * @static
     *
     * @param int    $id
     * @param string $size
     *
     * @return Se non esiste miniatura, viene restituita l'immagine di Placeholder
     */
    public static function thumbnail( $id, $size = kBNMExtendsThumbnailSizeSmallKey ) {
        if ( has_post_thumbnail( $id ) ) {
            echo get_the_post_thumbnail( $id, $size );
            return;
        } else {
            $id_event = self::idWPMLDefaultLanguage( $id );
            if ( has_post_thumbnail( $id_event ) ) {
                echo get_the_post_thumbnail( $id_event, $size );
            }
            return;
        }
        self::thumbnailPlaceholder( $size );
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Theme integration
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Costruisce la navigazione - per data - tra gli eventi
     *
     * @param null $eventDate
     * @param string $previousText
     * @param string $nextText
     * @return void
     */
    public static function themeNavigation( $eventDate = null, $previousText = 'Previous', $nextText = 'Next' ) {

        /* Previous */
        $meta_query = array(
            array(
                'key'     => kBNMExtendsEventMetaDateAndTime,
                'value'   => $eventDate,
                'type'    => 'numeric',
                'compare' => '<'
            )
        );

        $args       = array(
            'post_status' => 'publish',
            'post_type'   => kBNMExtendsEventPostTypeKey,
            'numberposts' => 1,
            'meta_key'    => kBNMExtendsEventMetaDateAndTime,
            'orderby'     => 'meta_value',
            'order'       => 'DESC',
            'meta_query'  => $meta_query
        );

        $events     = get_posts( $args );

        if ( count( $events ) > 0 ) {

            /* Qui è corretto che sia ICL_LANGUAGE_CODE, perché vado avanti e indietro nei "post" della mia lingua */
            $id_event = defined( 'ICL_LANGUAGE_CODE' ) ? icl_object_id( $events[0]->ID, kBNMExtendsEventPostTypeKey, true, ICL_LANGUAGE_CODE ) : $events[0]->ID;
            $id_event = absint( $id_event ); ?>
        <span class="left">
            <a class="button blue" href="<?php echo get_post_permalink( $id_event ) ?>"><?php _e( $previousText ) ?></a>
        </span>
        <?php
        }

        /* Next */
        $meta_query = array(
            array(
                'key'     => kBNMExtendsEventMetaDateAndTime,
                'value'   => $eventDate,
                'type'    => 'numeric',
                'compare' => '>'
            )
        );

        $args       = array(
            'post_status' => 'publish',
            'post_type'   => kBNMExtendsEventPostTypeKey,
            'numberposts' => 1,
            'meta_key'    => kBNMExtendsEventMetaDateAndTime,
            'orderby'     => 'meta_value',
            'order'       => 'ASC',
            'meta_query'  => $meta_query
        );

        $events = get_posts( $args );

        if ( count( $events ) > 0 ) {
            /* Qui è corretto che sia ICL_LANGUAGE_CODE, perché vado avanti e indietro nei "post" della mia lingua */
            $id_event = defined( 'ICL_LANGUAGE_CODE' ) ? icl_object_id( $events[0]->ID, kBNMExtendsEventPostTypeKey, true, ICL_LANGUAGE_CODE ) : $events[0]->ID; ?>
        <span class="right">
            <a class="button blue" href="<?php echo get_post_permalink( $id_event ) ?>"><?php _e( $nextText ) ?></a>
        </span>
        <?php

        }
    }
    
    /*
    
    public static function addTicketMetaBoxes() {
    	add_meta_box( 
    	    'bnm_linked_event_box',
    	    __( 'Events Connected', 'bnmextends' ),
    	    'addTicketMetaBoxesContent',
    	    'post' 
    	);  	
    }

    function addTicketMetaBoxesContent( $post ) {      
       // The actual fields for data entry
        echo '<label for="bnm_linked_event">';
             _e("Linked Event", 'bnmextends' );
        echo '</label> ';
        echo '<h1>Pippo</h1>';
    }
    */
    

}
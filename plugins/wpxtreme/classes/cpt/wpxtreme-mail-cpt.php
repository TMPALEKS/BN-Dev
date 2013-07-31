<?php
/**
 * @class              WPXtremeMailCustomPostType
 *
 * @description        Custom Post Type per l'invio di mail di varie circostanze, dalla registrazione utente alle mail
 *                     di servizio come il blocco di una utenza, report, etc....
 *
 * @package            wpXtreme
 * @subpackage         cpt
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc
 * @link               http://wpxtre.me
 * @created            19/06/12
 * @version            1.0.0
 *
 * @filename           wpxtreme-mail-cpt
 *
 */

require_once( WPXTREME_PATH_CLASSES . 'helper/wpxtreme-help.php' );
require_once( WPXTREME_PATH_CLASSES . 'cpt/wpxtreme-mail-metabox.php' );

class WPXtremeMailCustomPostType {

    static $cpt_key = WPXTREME_MAIL_CPT_KEY;
    static $cpt_query_var = WPXTREME_MAIL_CPT_QUERY_VAR;
    static $menu_position = 20;

    // -----------------------------------------------------------------------------------------------------------------
    // Post Type
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Registra il custom post type
     *
     * @static
     *
     */
    public static function registerPostType() {
        $labels = array(
            'name'               => __( 'Mail', WPXTREME_TEXTDOMAIN ),
            'singular_name'      => __( 'Mail', WPXTREME_TEXTDOMAIN ),
            'add_new'            => __( 'Add New', WPXTREME_TEXTDOMAIN ),
            'add_new_item'       => __( 'Add New Mail', WPXTREME_TEXTDOMAIN ),
            'edit_item'          => __( 'Edit', WPXTREME_TEXTDOMAIN ),
            'new_item'           => __( 'New Mail', WPXTREME_TEXTDOMAIN ),
            'view_item'          => __( 'View Mail', WPXTREME_TEXTDOMAIN ),
            'search_items'       => __( 'Mail Search', WPXTREME_TEXTDOMAIN ),
            'not_found'          => __( 'Mail not found', WPXTREME_TEXTDOMAIN ),
            'not_found_in_trash' => __( 'No Mail in trash', WPXTREME_TEXTDOMAIN ),
            'parent_item_colon'  => ''
        );
        $args   = array(
            'labels'               => $labels,
            'public'               => true,
            'publicly_queryable'   => true,
            'show_ui'              => true,
            'show_in_nav_menus'    => true,
            'show_in_admin_bar'    => true,
            'menu_icon'            => WPXTREME_URL_CSS . 'images/logo-16x16.png',
            'query_var'            => self::$cpt_query_var,
            'rewrite'              => array( 'slug' => __( 'mail', WPXTREME_TEXTDOMAIN ), 'with_front' => false ),
            'capability_type'      => 'post',
            'hierarchical'         => false,
            'menu_position'        => self::$menu_position,
            'supports'             => array(
                'thumbnail',
                'title',
                'editor',
                'excerpt',
                'author'
            ),
            'register_meta_box_cb' => array( __CLASS__, 'register_meta_box_cb' )
        );

        /* Registro il mio custom post type */
        register_post_type( self::$cpt_key, $args );

        /* Default Enter title */
        add_filter( 'enter_title_here', array( __CLASS__, 'enter_title_here' ) );

        /* Aggiunge una classe nel tag body nell'amministrazione per gli stili */
        add_action( 'admin_head-edit.php', array( __CLASS__, 'admin_head') );
        add_action( 'admin_head-post-new.php', array( __CLASS__, 'admin_head_post_new') );
        add_action( 'admin_head-post.php', array( __CLASS__, 'admin_head_post_new') );
        //add_filter( 'admin_body_class', array( __CLASS__, 'admin_body_class' ) );

        /* Hook per il salvataggio dei dati extra */
        add_action( 'save_post', array( __CLASS__, 'save_post' ), 10, 2);

        /* Gestione delle colonne */
        add_action( 'manage_' . self::$cpt_key . '_posts_custom_column', array( __CLASS__, 'manage_posts_custom_column' ) );
        add_filter( 'manage_edit-' . self::$cpt_key . '_columns', array( __CLASS__, 'manage_edit_columns') );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress hook
    // -----------------------------------------------------------------------------------------------------------------

    public static function enter_title_here( $title ) {
        global $post_type;
        if ( $post_type == self::$cpt_key ) {
            $title = __( 'Enter Email subject', WPXTREME_TEXTDOMAIN );
        }
        return $title;
    }

    public static function admin_head() {
        //global $typenow;
        global $post_type;

        if( $post_type == self::$cpt_key ) {
            add_filter( 'admin_body_class', array( __CLASS__, 'admin_body_class' ) );
        }

    }

    public static function admin_head_post_new() {
        global $post_type;

        if ( $post_type == self::$cpt_key ) {

            self::admin_head();

            /* Register help. */
            $screen = get_current_screen();
            $screen->add_help_tab( WPXtremeHelp::mail_introducing() );
            $screen->add_help_tab( WPXtremeHelp::mail_placeholder() );

            $screen->set_help_sidebar( WPXtremeHelp::sidebar() );
        }
    }

    public static function admin_body_class( $classes ) {
        $classes .= ' ' . self::$cpt_key;
        return $classes;
    }

    
    // -----------------------------------------------------------------------------------------------------------------
    // WordPress List Table columns
    // -----------------------------------------------------------------------------------------------------------------

    public static function manage_posts_custom_column( $column ) {

    }

    public static function manage_edit_columns( $columns ) {
        return $columns;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // WordPress integration
    // -----------------------------------------------------------------------------------------------------------------

    public static function register_meta_box_cb() {
        WPXtremeMailMetaBox::registerMetaBoxes();
    }

    /**
     * Chiamata quando il post è inserito o aggiornato
     *
     * @param int | string $ID ID del prodotto
     *
     * @param object $post Oggetto Post
     *
     * @return
     */
    public static function save_post($ID, $post) {

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
        if ( !in_array( $post_type, array( WPXTREME_MAIL_CPT_KEY ) ) ) {
            return;
        }

        /* Verify this came from the our screen and with proper authorization. */
        if ( !WPDKForm::isNonceVerify( WPXTREME_MAIL_CPT_KEY ) ) {
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

        WPXtremeMailMetaBox::save_mail_settings( $post );

    }


    // -----------------------------------------------------------------------------------------------------------------
    // Public methods
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Invia una mail partendo dal post di tipo Mail
     *
     * @static
     *
     * @param int|string      $id_post ID del post-mail dove recuperare i dati o il suo slug
     * @param string|int      $to      Destinatario 'nome <emmail>' o id dell'utente che farà da destinatario
     * @param bool|string     $subject Oggetto della mail o false per usare quello del post-mail
     * @param bool|int|string $from    Mittente 'nome <emmail>' o id dell'utente che farà da mittente, false per usare
     *                                 il custom post 'wpxm_mail_from'
     * @param array           $extra   Array keypair con codice filtro placeholder e valori. Questo serve per passare dei
     *                                 placeholder onfly
     *
     * @return bool Whether the mail contents were sent successfully.
     */
    public static function mail( $id_post, $to, $subject = false, $from = false, $extra = array() ) {

        /* Posso passare anche lo slug, per comodità. */
        if ( is_string( $id_post ) ) {
            $post    = get_page_by_path( $id_post, OBJECT, WPXTREME_MAIL_CPT_KEY );
            $id_post = $post->ID;
        } elseif ( is_numeric( $id_post ) ) {
            /* Sanitizzo l'id del post */
            $id_post = absint( $id_post );
            $post    = get_post( $id_post );
        }

        if ( $post ) {

            if ( is_numeric( $from ) ) {
                $user = new WP_User( $from );
                $from   = sprintf( '%s <%s>', $user->data->display_name,  $user->get( 'user_email' ) );
            }

            /* $from è nel formato 'NOME <email>', ad esempio: 'wpXtreme <info@wpxtre.me>' */
            if ( $from === false ) {
                $from = wp_specialchars_decode( get_post_meta( $id_post, 'wpxm_cpt_mail_from', true ) );
            }

            $headers = array(
                'From: ' . $from . "\r\n",
                'Content-Type: text/html' . "\r\n"
            );

            /* Se $to è un numero corriponde ad un id_user */
            $user = false;
            if ( is_numeric( $to ) ) {
                $user = new WP_User( $to );
                $to   = sprintf( '  %s <%s>', $user->data->display_name,  $user->get( 'user_email' ) );
            }

            if ( $subject === false ) {
                $subject = apply_filters( 'the_title', $post->post_title );
            }

            $body = apply_filters( 'the_content', $post->post_content );
            $body = self::filters( $body, $user, $extra );

            return wp_mail( $to, $subject, $body, $headers );
        }
        return false;
    }

    /**
     * Sostituisce i placeholder speciali con le informazioni relative.
     * Dato che la password non è decrittabile dalle informazioni utente, è uno dei parametri extra che viene passato
     * negli inputs.
     *
     * @static
     *
     * @param string          $content Contenuto da filtrare
     * @param bool|int|object $id_user Se false viene preso l'id dell'utente corrente, se numerico rappresenta l'id
     *                                 dell'utente, se object di tipo WP_User rappresenta l'utente
     *
     * @return string
     */
    private static function filters( $content, $id_user = false, $extra = array() ) {

        if ( $id_user === false ) {
            $id_user = get_current_user_id();
            $user    = new WP_User( $id_user );
        } elseif ( is_object( $id_user ) && is_a( $id_user, 'WP_User' ) ) {
            $user = $id_user;
        } elseif ( is_numeric( $id_user ) ) {
            $user = new WP_User( $id_user );
        } else {
            return $content;
        }

        /* Recupero impostazioni */
        $settings   = WPXtreme::$settings->registration();

        /* Per compatibilitò con WPML */
        $permalink  = wpdk_permalink_page_with_slug( $settings['page_registration_slug'] );

        /* Costruisco l'url di sblocco. Uso l'md5 della mail come codice di sblocco */
        $unlock_code = md5( $user->data->user_email );
        $unlock_url  = sprintf( '%s?wpdk_do=%s', $permalink, $unlock_code );

        /* Memorizzo negli user meta il codice di sblocco */
        update_user_meta( $user->ID, 'wpdk_unlock_code', $unlock_code );

        $str_replaces = array(
            WPXTREME_MAIL_PLACEHOLDER_USER_FIRST_NAME             => $user->get( 'first_name' ),
            WPXTREME_MAIL_PLACEHOLDER_USER_LAST_NAME              => $user->get( 'last_name' ),
            WPXTREME_MAIL_PLACEHOLDER_USER_DISPLAY_NAME           => $user->data->display_name,
            WPXTREME_MAIL_PLACEHOLDER_USER_EMAIL                  => $user->data->user_email,
            WPXTREME_MAIL_PLACEHOLDER_DOUBLE_OPTIN_ACTIVATION_URL => $unlock_url,
        );

        if( !empty( $extra ) ) {
            $str_replaces = array_merge( $str_replaces, $extra );
        }

        $content = strtr( $content, $str_replaces );

        return $content;
    }
}

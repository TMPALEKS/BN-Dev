<?php
/**
 * Questa classe, per adesso scritta appositamente per gli eventi, permette di fatto di duplicare un post o una pagina.
 * e stato inserito un controllo sul Post Type, quindi per ora clona solo gli eventi. Generalizzata potrà essere estesa
 * per clonare qualsiasi tipo di pagina o post.
 *
 * @package            BNMExtends
 * @subpackage         BNMExtendsClone.php
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (C)2011 Saidmade Srl.
 * @created            12/12/11
 * @version            1.0
 *
 */

class BNMExtendsClone {

    function __construct() {
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress Integration
    // -----------------------------------------------------------------------------------------------------------------
    public static function register() {
        add_filter('post_row_actions', array( __CLASS__, 'addCloneRow'), 10, 2);
        add_action('admin_action_clone_event', array( __CLASS__, 'clone_event'));
    }

    /**
     * Aggiunge una "voce" alle quick action che appaiono sotto al titolo nella list table view
     *
     * @param $actions
     * @param $post
     *
     * @return array
     */
    public static function addCloneRow( $actions, $post ) {
        if ( $post->post_type == kBNMExtendsEventPostTypeKey ) {

            $url  = admin_url( 'admin.php' );
            $args = array(
                'action'  => 'clone_event',
                'post'    => $post->ID,
                'orderby' => get_query_var( 'meta_key' ),
                'order'   => get_query_var( 'order' ),
            );

            $url = add_query_arg( $args, $url );

            $actions['clone'] = sprintf( '<a href="%s" title="%s" rel="permalink">%s</a>', $url, esc_attr( 'Clona' ), 'Clona' );
        }
        return $actions;
    }

    /**
     * Clona un evento. Questo metodo è scritto apposta per l'evento e ne incrementa anche la data di un giorno. È molto
     * probabile che ne scriveremo ad hoc anche altri.
     *
     * @todo Non copia le tassionomie
     *
     */
    public static function clone_event() {
        $id_event = isset( $_GET['post'] ) ? $_GET['post'] : $_POST['post'];

        $post = get_post( $id_event );

        $values = array(
            'post_author'   => $post->post_author,
            'post_content'  => $post->post_content,
            'post_date'     => $post->post_date,
            'post_date_gmt' => $post->post_date_gmt,
            'post_excerpt'  => $post->post_excerpt,
            'post_parent'   => $post->post_parent,
            'post_status'   => $post->post_status,
            'post_title'    => $post->post_title,
            'post_type'     => $post->post_type
        );
        $newID  = wp_insert_post( $values );

        // Permalink / Slug
        if ( $post->post_status == 'publish' ) {
            $newSlug = wp_unique_post_slug( sanitize_title( $post->post_title ), $newID, $post->post_status, $post->post_type, $post->post_parent );
            wp_update_post( array( 'ID' => $newID, 'post_name' => $newSlug ) );
        }


        self::cloneMeta( $post->ID, $newID );
        self::eventDateAddHours( $newID );
        
        //Controllo che ci sia un ticket associato al post
        if ( self::checkLinkedTicket( $post->ID ) )
        		self::createNewTicket( $post, $newID );

        if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
            self::wpml_translate_post( $newID, kBNMExtendsEventPostTypeKey, 'en', $post->ID );
        }

        // Prepara redirect
        $url  = admin_url( 'edit.php' );
        $args = array(
            'post_type' => $post->post_type,
            'orderby'   => isset( $_GET['orderby'] ) ? $_GET['orderby'] : '',
            'order'     => isset( $_GET['order'] ) ? $_GET['order'] : '',
        );

        $url = add_query_arg( $args, $url );

        wp_redirect( $url );
        exit;
    }
    
    /**
     * Controlla che il post corrente (da clonare) non abbia ticket associati
     *
     * @param $postID
     * @return mixed
     */
    public static function checkLinkedTicket( $postID ) {
    	$linkedTicket = get_post_meta( $postID,'bnm-event-ticket',true );
    	//var_dump($linkedTicket); die();
    	return isset( $linkedTicket ) && ( $linkedTicket != "" );
    }

    /**
     * Clona tutti i meta di un post su quello nuovo e incrementa la data (nei post meta) di un giorno
     *
     * @param $sourceID
     * @param $cloneID
     * @return mixed
     */
    public static function cloneMeta( $sourceID, $cloneID ) {
        global $wpdb;

        // Clone meta
        $sql = <<< SQL
        INSERT INTO `{$wpdb->postmeta}`
        (post_id, meta_key, meta_value)
        SELECT {$cloneID}, meta_key, meta_value FROM `{$wpdb->postmeta}` WHERE post_id = {$sourceID}
SQL;

        $result = $wpdb->query( $sql );

        return $result;
    }

    public static function createNewTicket ( $post, $id_dest ) {
        delete_post_meta( $id_dest, 'bnm-event-ticket' );

        $original_base_price = get_post_meta( $post->ID, 'bnm_create_ticket_base_price', true);
        update_post_meta( $id_dest, 'bnm_create_ticket_base_price', $original_base_price);

        $new_post = get_post( $id_dest );
        BNMExtendsEventPostType::createTicket( $id_dest, $new_post );
    }

    public static function eventDateAddHours( $id_post, $hours_to_add = 24 ) {
        global $wpdb;

        $field = kBNMExtendsEventMetaDateAndTime;
        // Incrementa la data evento di 1 giorno
        $sql = <<< SQL
        SELECT meta_value FROM `{$wpdb->postmeta}`
        WHERE meta_key = '{$field}'
        AND post_id = {$id_post}
SQL;

        $eventDate = $wpdb->get_var( $sql );

        $day   = substr( $eventDate, 6, 2 );
        $month = substr( $eventDate, 4, 2 );
        $year  = substr( $eventDate, 0, 4 );

        $hours   = substr( $eventDate, 8, 2 );
        $minutes = substr( $eventDate, 10, 2 );

        $time      = mktime( $hours, $minutes, 0, $month, $day, $year ) + $hours_to_add * 3600;
        $eventDate = date( 'YmdHi', $time );

        $result = update_post_meta( $id_post, kBNMExtendsEventMetaDateAndTime, $eventDate );

        return $result;
    }


    /**
     * Creates a translation of a post (to be used with WPML)
     *
     * @param int    $post_id   The ID of the post to be translated.
     * @param string $post_type The post type of the post to be transaled (ie. 'post', 'page', 'custom type', etc.).
     * @param string $lang      The language of the translated post (ie 'fr', 'de', etc.).
     *
     * @return the translated post ID
     *  */
    function wpml_translate_post( $post_id, $post_type, $lang, $parent_clone_id ) {
        global $wpdb;

        // Include WPML API
        include_once( WP_PLUGIN_DIR . '/sitepress-multilingual-cms/inc/wpml-api.php' );

        // Define title of translated post
        $original_post         = get_post( $post_id );
        $post_translated_title = $original_post->post_title;

        /* Prepare values */
        $values = array(
            'post_title'   => $post_translated_title,
            'post_type'    => $post_type,
            'post_status'  => $original_post->post_status,
        );

        /* C'è una traduzione? */
        $translate_id = icl_object_id( $parent_clone_id, $post_type, true, $lang );
        if ( $translate_id != $parent_clone_id ) {
            $trans                  = get_post( $translate_id );
            $values['post_content'] = $trans->post_content;
        }

        // Insert translated post
        $post_translated_id = wp_insert_post( $values );

        if ( $original_post->post_status == 'publish' ) {
            $newSlug = $original_post->post_name;
            wp_update_post( array( 'ID' => $post_translated_id, 'post_name' => $newSlug ) );
        }


        // Get trid of original post
        $trid = wpml_get_content_trid( 'post_' . $post_type, $post_id );

        // Get default language
        $default_lang = wpml_get_default_language();

        // Associate original post and translated post
        $wpdb->update( $wpdb->prefix . 'icl_translations', array(
                                                                'trid'                 => $trid,
                                                                'language_code'        => $lang,
                                                                'source_language_code' => $default_lang
                                                           ), array( 'element_id' => $post_translated_id ) );

        self::cloneMeta( $post_id, $post_translated_id );

        // Return translated post ID
        return $post_translated_id;
    }
}
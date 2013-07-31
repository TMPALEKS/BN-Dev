<?php
/**
 * Gestire il post type Artista
 *
 * @package            BNMExtends
 * @subpackage         BNMExtendsArtistPostType
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (C)2011 Saidmade Srl.
 * @created            11/11/11
 * @version            1.0
 *
 */

class BNMExtendsArtistPostType {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

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

    public static function enqueueStyles() {
        global $typenow;
        if ($typenow == kBNMExtendsArtistPostTypeKey) {
            //WPDK::enqueueStyles();
            wp_enqueue_style('bnm-artist', kBNMExtendsURI . 'css/ArtistMetaBox.min.css');
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Post Type
    // -----------------------------------------------------------------------------------------------------------------

    public static function registerPostType() {
        $labels = array(
            'name'               => 'Artisti',
            'singular_name'      => 'Artista',
            'add_new'            => 'Aggiungi nuovo',
            'add_new_item'       => 'Aggiungi Nuovo Artista',
            'edit_item'          => 'Modifica',
            'new_item'           => 'Nuovo Artista',
            'view_item'          => 'Visualizza Artista',
            'search_items'       => 'Ricerca Artista',
            'not_found'          => 'Artisti non trovati',
            'not_found_in_trash' => 'Nessun Artista nel cestino',
            'parent_item_colon'  => ''
        );

        $args = array(
            'labels'               => $labels,
            'public'               => true,
            'publicly_queryable'   => true,
            'show_ui'              => true,
            'menu_icon'            => get_stylesheet_directory_uri() . '/images/admin_logo.png',
            'query_var'            => true,
            'rewrite'              => array(
                'slug'       => 'artista', //__('artist', 'bnmextends'),
                'with_front' => false
            ),
            'capability_type'      => 'post',
            'hierarchical'         => false,
            'menu_position'        => kBNMExtendsArtistPostTypeMenuItemPosition,
            'supports'             => array(
                'title',
                'editor',
                'thumbnail'
            ),
            'register_meta_box_cb' => array(
                __CLASS__,
                'metaBox'
            )
        );

        /* Registro il mio custom post type */
        register_post_type( kBNMExtendsArtistPostTypeKey, $args);

        /* Hook per il salvataggio dei dati extra */
        add_action('save_post', array(__CLASS__, 'save_post'));

        /* Register columns */
        add_filter('manage_edit-' . kBNMExtendsArtistPostTypeKey . '_columns', array(__CLASS__, 'registerColumns'));

        /* Manage view custom columns */
        add_action('manage_' . kBNMExtendsArtistPostTypeKey . '_posts_custom_column', array(__CLASS__, 'manageColumns'));

        /* Cambia il titolo al meta box standard delle miniature */
        add_action('do_meta_boxes', array(__CLASS__, 'replaceThumbnailMetaBoxTitle'));

        /* Quando un artista viene eliminato */
        add_action('before_delete_post', array(__CLASS__, 'before_delete_post'));

        add_action('admin_enqueue_scripts', array(__CLASS__, 'enqueueStyles'));
    }

    public static function replaceThumbnailMetaBoxTitle() {

        global $typenow;

        if ( $typenow == kBNMExtendsArtistPostTypeKey ) {

            /* @todo Non pulitissimo, va bene per WordPress con backend monolingua - considerare pezza */
            add_filter( 'admin_post_thumbnail_html', function( $content ) {
                $content = str_replace( 'in evidenza', 'artista', $content );
                return $content;
            } );

            remove_meta_box( 'postimagediv', kBNMExtendsArtistPostTypeKey, 'side' );
            add_meta_box( 'postimagediv', __( 'Artist Image', 'bnmextends' ), 'post_thumbnail_meta_box', kBNMExtendsArtistPostTypeKey, 'side', 'low' );
        }
    }

    public static function manageColumns( $column ) {
        global $post;

        /* Recupera il custom field dal post della lingua di base - non usato per adesso */
        // $id_artist = self::idWPMLDefaultLanguage($post->ID);

        if ( 'icon' == $column ) {
            self::thumbnail( $post->ID );
        }
    }

    public static function registerColumns( $columns ) {
        $columns['title'] = 'Artista';
        $columns          = WPDKArray::insertKeyValuePair( $columns, 'icon', 'Anteprima', 1 );

        return $columns;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress (meta box) Integration
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Aggiunge un MetaBox alla schermata di inserimento/modifica di un post di tipo Artista, solo se in lingua italiano
     *
     * @return void
     */
    public static function metaBox() {
        if ( self::isWPMLNoDefaultLanguage() ) {
            add_meta_box( kBNMExtendsArtistPostTypeKey . '-div', 'Anagrafica', array(__CLASS__, 'metaBoxView'), kBNMExtendsArtistPostTypeKey, 'advanced', 'high' );
        }
    }

    public static function fields() {
        /* Questa ritorneà in formato WPDK SDF i campi per un eventuale form */
    }

    public static function metaBoxView($post) {
        /* Nessun campo aggiuntivo stabilito */
        ?>
        <p>Qui potranno essere inseriti campi extra se necessario</p>
    <?php
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
        if ( !in_array( $post_type, array( kBNMExtendsArtistPostTypeKey ) ) ) {
            return;
        }

        /* Verify this came from the our screen and with proper authorization. */
        if ( !WPDKForm::isNonceVerify( 'artist' ) ) {
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
        if ( $post->post_type == kBNMExtendsArtistPostTypeKey ) {
            /* Save meta data */
        }

        return $ID;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Event Integration
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * This Hook is run when a post will deleted definitly, not when it is put in trashcan
     *
     * @static
     * @param $post_id
     */
    public static function before_delete_post( $post_id ) {
        global $wpdb;

        $post_id = self::idWPMLDefaultLanguage( $post_id );
        $post    = get_post( $post_id );
        if ( $post->post_type == kBNMExtendsArtistPostTypeKey ) {

            /* Delete all images attachment too */
            $sql          = sprintf( "SELECT ID FROM `%s` WHERE `post_parent` = %s AND `post_type` = 'attachment'", $wpdb->posts, $post_id );
            $attachmentID = $wpdb->get_var( $sql );
            wp_delete_attachment( $attachmentID, true );

            /* Elimino l'artista anche dalla tabella di connessione tra eventi ed artisti */
            $sql    = sprintf( 'DELETE FROM `%s` WHERE `id_artist` = %s', self::tableName(), $post_id );
            $result = $wpdb->query( $sql );
            return $result;
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
     * @return null
     */
    public static function idWPMLDefaultLanguage($id) {
        return defined('ICL_LANGUAGE_CODE') ? icl_object_id($id, kBNMExtendsArtistPostTypeKey, true, kBNMExtendsWPMLIntegrationDefaultLanguage) : $id;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Commodity
    // -----------------------------------------------------------------------------------------------------------------


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
        return kBNMExtendsURI . 'css/images/placeholder-artist-55x55.png';
    }

    /**
     * Restituisce l'immagine miniatura on compatibilità con WPML. Se l'id passato negli inputs ha l'immagine, sia esso
     * italiano o inglese, prende quella, altrimenti calcola l'id della lingua base e cerca la miniatura in quella.
     *
     * @static
     *
     * @param        $id
     * @param string $size
     *
     * @return string | html
     *   Se non esiste miniatura, viene restituita l'immagine di PlaceHolder
     */
    public static function thumbnail( $id, $size = kBNMExtendsThumbnailSizeSmallKey ) {
        if ( has_post_thumbnail( $id ) ) {
            echo get_the_post_thumbnail( $id, $size );
            return;
        } else {
            $id_artist = self::idWPMLDefaultLanguage( $id );
            if ( has_post_thumbnail( $id_artist ) ) {
                echo get_the_post_thumbnail( $id_artist, $size );
            }
            return;
        }
        self::thumbnailPlaceholder( $size );
    }

    public static function thumbnailSrc( $id, $size = kBNMExtendsThumbnailSizeSmallKey ) {
        if ( has_post_thumbnail( $id ) ) {
            $image_id = get_post_thumbnail_id( $id );
            $image    = wp_get_attachment_image_src( $image_id, $size );
        } else {
            $id_artist = self::idWPMLDefaultLanguage( $id );
            if ( has_post_thumbnail( $id_artist ) ) {
                $image_id = get_post_thumbnail_id( $id_artist );
                $image    = wp_get_attachment_image_src( $image_id, $size );
            } else {
                $image = self::thumbnailPlaceholderSrc();
                return $image;
            }
        }
        return $image[0];
    }
}
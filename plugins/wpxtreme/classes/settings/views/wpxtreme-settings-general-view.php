<?php
/**
 * @description View
 *
 * @package            wpXtreme
 * @subpackage         WPXtremeSettingsGeneralView
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            23/05/12
 * @version            1.0.0
 *
 * @filename wpxtreme-settings-general-view.php
 *
 */


class WPXtremeSettingsGeneralView extends WPDKSettingsView {

    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    function __construct() {
        parent::__construct( 'general', __( 'Enhanced WordPress', WPXTREME_TEXTDOMAIN ), WPXtreme::$settings, false );
    }

    /**
     * Prepara l'array che descrive i campi del form
     *
     * @return array
     */
    function fields() {

        $values = WPXtreme::$settings->general();

        $fields = array(
            __( 'Appearance', WPXTREME_TEXTDOMAIN ) => array(
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'    => 'enhanced_wordpress_theme_styles',
                        'label'   => __( 'Enhanced WordPress theme styles', WPXTREME_TEXTDOMAIN ),
                        'value'   => 'y',
                        'checked' => $values ? $values['enhanced_wordpress_theme_styles'] : ''
                    )
                ),
            ),
            __( 'Posts', WPXTREME_TEXTDOMAIN ) => array(
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'  => 'posts_thumbnail_author',
                        'label' => __( 'Enabled Thumbnail Author', WPXTREME_TEXTDOMAIN ),
                        'value' => 'y',
                        'checked' => $values ? $values['posts_thumbnail_author'] : ''
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'  => 'posts_swipe_publish',
                        'label' => __( 'Enabled Swipe button for Publish/Draft', WPXTREME_TEXTDOMAIN ),
                        'value' => 'y',
                        'checked' => $values ? $values['posts_swipe_publish'] : ''
                    )
                ),
            ),
            __( 'Media', WPXTREME_TEXTDOMAIN ) => array(
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'  => 'media_thickbox_icon',
                        'label' => __( 'Enabled Thick Box icon', WPXTREME_TEXTDOMAIN ),
                        'title' => __( 'Apple the Lighbox effect on thumbnail icon of media.', WPXTREME_TEXTDOMAIN ),
                        'value' => 'y',
                        'checked' => $values ? $values['media_thickbox_icon'] : ''
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'  => 'media_thumbnail_author',
                        'label' => __( 'Enabled Enabled Thumbnail Author', WPXTREME_TEXTDOMAIN ),
                        'value' => 'y',
                        'checked' => $values ? $values['media_thumbnail_author'] : ''
                    )
                ),
            ),
            __( 'Pages', WPXTREME_TEXTDOMAIN ) => array(
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'  => 'pages_thumbnail_author',
                        'label' => __( 'Enabled Thumbnail Author', WPXTREME_TEXTDOMAIN ),
                        'value' => 'y',
                        'checked' => $values ? $values['pages_thumbnail_author'] : ''
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'  => 'pages_swipe_publish',
                        'label' => __( 'Enabled Swipe button for Publish/Draft', WPXTREME_TEXTDOMAIN ),
                        'value' => 'y',
                        'checked' => $values ? $values['pages_swipe_publish'] : ''
                    )
                ),
            ),
        );

        return $fields;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Settings actions
    // -----------------------------------------------------------------------------------------------------------------

    /**
     *
     */
    function save() {

        $values = array(
            'enhanced_wordpress_theme_styles' => isset( $_POST['enhanced_wordpress_theme_styles'] ) ? $_POST['enhanced_wordpress_theme_styles'] : 'n',

            'posts_thumbnail_author'          => isset( $_POST['posts_thumbnail_author'] ) ? $_POST['posts_thumbnail_author'] : 'n',
            'posts_swipe_publish'             => isset( $_POST['posts_swipe_publish'] ) ? $_POST['posts_swipe_publish'] : 'n',

            'media_thickbox_icon'             => isset( $_POST['media_thickbox_icon'] ) ? $_POST['media_thickbox_icon'] : 'n',
            'media_thumbnail_author'          => isset( $_POST['media_thumbnail_author'] ) ? $_POST['media_thumbnail_author'] : 'n',

            'pages_thumbnail_author'          => isset( $_POST['pages_thumbnail_author'] ) ? $_POST['pages_thumbnail_author'] : 'n',
            'pages_swipe_publish'             => isset( $_POST['pages_swipe_publish'] ) ? $_POST['pages_swipe_publish'] : 'n',
        );

        WPXtreme::$settings->general( $values );
    }

}

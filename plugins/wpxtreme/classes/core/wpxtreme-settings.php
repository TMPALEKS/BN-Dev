<?php
/**
 * @class              WPXtremeSettings
 * @description        Impostazioni di wpXtreme
 *
 * @package            WPXtreme
 * @subpackage         core
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            15/05/12
 * @version            1.0.0
 *
 */

class WPXtremeSettings extends WPDKSettings {

    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    /// Construct
    function __construct() {
        parent::__construct( 'wpxtreme-options', 'settings' );
    }

    /// Get/Set security
    function security( $values = null ) {
        if ( is_null( $values ) ) {
            return $this->settings( 'security' );
        } else {
            $this->settings( 'security', $values );
        }
    }

    /// Get/Set extrafields
    function extrafields( $values = null ) {
        if ( is_null( $values ) ) {
            return $this->settings( 'extrafields' );
        } else {
            $this->settings( 'extrafields', $values );
        }
    }

    /// Get/Set general
    function general( $values = null ) {
        if ( is_null( $values ) ) {
            return $this->settings( 'general' );
        } else {
            $this->settings( 'general', $values );
        }
    }

    /// Get/Set maintenance
    function maintenance( $values = null ) {
        if ( is_null( $values ) ) {
            return $this->settings( 'maintenance' );
        } else {
            $this->settings( 'maintenance', $values );
        }
    }

    /// Get/Set registration
    function registration( $values = null ) {
        if ( is_null( $values ) ) {
            return $this->settings( 'registration' );
        } else {
            $this->settings( 'registration', $values );
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /// Get default options array
    function defaultOptions() {
        $defaults = array(
            'version'   => WPXSMARTSHOP_VERSION,

            'settings'  => array(

                'general' => array(
                    'enhanced_wordpress_theme_styles' => 'y',

                    'posts_thumbnail_author'          => 'y',
                    'posts_swipe_publish'             => 'y',

                    'media_thickbox_icon'             => 'y',
                    'media_thumbnail_author'          => 'y',

                    'pages_thumbnail_author'          => 'y',
                    'pages_swipe_publish'             => 'y',
                ),

                'maintenance' => array(
                    'enabled'                 => 'n',
                    'date_start'              => '',
                    'date_expire'             => '',

                    'template'                => 'wp_die',

                    'disable_wp_login'        => 'n',
                    'ip_address'              => array( $_SERVER['REMOTE_ADDR'] => $_SERVER['REMOTE_ADDR'] ),

                    'user_roles'              => array(),
                    'users_id'                => array(),

                    'enabled_message_login'   => 'y',
                    'message_login'           => __( 'Maintenance mode is enabled.', WPXTREME_TEXTDOMAIN ),
                    'enabled_message_admin'   => 'y',
                    'message_admin'           => __( 'Maintenance mode is enabled.', WPXTREME_TEXTDOMAIN ),
                    'enabled_message_footer'  => 'y',
                    'message_footer'          => __( 'Maintenance mode is enabled.', WPXTREME_TEXTDOMAIN ),

                    'page_title'              => get_bloginfo( 'name' ),
                    'title'                   => __( 'Maintenance Mode', WPXTREME_TEXTDOMAIN ),
                    'note'                    => __( 'This website is currently in maintenance mode.', WPXTREME_TEXTDOMAIN ),

                ),

                'custom_login' => array(),

                'security' => array(
                    'enabled_wrong_login_attempts'    => 'n',
                    'wrong_login_attempts'            => 5,
                    'email_slug_wrong_login_attempts' => '',
                ),

                'registration' => array(
                    'page_registration_slug' => '',
                    'page_profile_slug'      => '',

                    'default_user_role'      => 'subscriber',
                    'default_user_status'    => 'disabled',
                    'email_slug_confirmed'   => '',

                    'double_optin'           => true,
                    'email_slug_confirm'     => '',
                ),

                'extrafields' => array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __( 'Address', WPXTREME_TEXTDOMAIN ),
                        'size'      => 32,
                        'name'      => 'bill_address',
                        'value'     => ''
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'label'     => __( 'ZIP code', WPXTREME_TEXTDOMAIN ),
                        'size'      => 6,
                        'name'      => 'bill_zipcode',
                        'value'     => ''
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __( 'Town', WPXTREME_TEXTDOMAIN ),
                        'size'      => 11,
                        'name'      => 'bill_town',
                        'value'     => ''
                    ),
                ),
            )
        );
        return $defaults;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Very useful helper
    // -----------------------------------------------------------------------------------------------------------------

    /// Get wrong login attenpts
    function wrong_login_attempts() {
        $settings = $this->settings( 'security' );
        return $settings['wrong_login_attempts'];
    }

    /// Get if wrong login is enabled
    function enabled_wrong_login_attempts() {
        $settings = $this->settings( 'security' );
        return wpdk_is_bool( $settings['enabled_wrong_login_attempts'] );
    }

    /// Get enhance
    function enhanced_wordpress() {
        $settings = $this->general();
        return wpdk_is_bool( $settings['enhanced_wordpress_theme_styles'] );
    }

}

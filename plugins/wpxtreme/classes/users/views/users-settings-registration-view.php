<?php
/**
 * @class              UsersSettingsRegistrationView
 * @description        Vista registrazione
 *
 * @package            wpXtreme
 * @subpackage         users/views
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            16/05/12
 * @version            1.0.0
 *
 */

class UsersSettingsRegistrationView extends WPDKSettingsView {

    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    function __construct() {
        $this->key          = 'registration';
        $this->title        = __( 'Registration', WPXTREME_TEXTDOMAIN );
        $this->introduction = __( 'Please, write an introduction', WPXTREME_TEXTDOMAIN );
        $this->settings     = WPXtreme::$settings;
    }

    /**
     * Prepara l'array che descrive i campi del form
     *
     * @return array
     */
    function fields() {
        /* Get options. */
        $values  = WPXtreme::$settings->registration();

        /* Custom post type Email new URL */
        $email_post_new_url = admin_url( 'post-new.php?post_type=wpx-email' );

        $fields = array(
            __( 'WordPress Integration', WPXTREME_TEXTDOMAIN ) => array(
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'      => 'page_registration_slug',
                        'label'     => __( 'Page reigstration slug', WPXTREME_TEXTDOMAIN ),
                        'value'     => $values ? $values['page_registration_slug'] : '',
                        'title'     => __( 'Page registration slug', WPXTREME_TEXTDOMAIN ),
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'      => 'page_profile_slug',
                        'label'     => __( 'Page profile slug', WPXTREME_TEXTDOMAIN ),
                        'value'     => $values ? $values['page_profile_slug'] : '',
                        'title'     => __( 'Page profile slug', WPXTREME_TEXTDOMAIN ),
                    )
                ),
            ),

            __( 'Default user settings', WPXTREME_TEXTDOMAIN ) => array(
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'      => 'default_user_role',
                        'label'     => __( 'Default user Role', WPXTREME_TEXTDOMAIN ),
                        'options'   => array( 'WPDKUser', 'arrayRolesForSDF' ),
                        'value'     => $values ? $values['default_user_role'] : '',
                        'title'     => __( 'When an user is added, this is the dafault role applied', WPXTREME_TEXTDOMAIN ),
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'      => 'default_user_status',
                        'label'     => __( 'Default user status', WPXTREME_TEXTDOMAIN ),
                        'options'   => array(
                            'disabled'  => __( 'Disabled', WPXTREME_TEXTDOMAIN ),
                            'enabled'   => __( 'Enabled', WPXTREME_TEXTDOMAIN ),
                        ),
                        'value'     => $values ? $values['default_user_status'] : '',
                        'title'     => __( 'When an user is added, this is the dafault status applied. Usually it is set on disabled.', WPXTREME_TEXTDOMAIN ),
                    )
                ),
                array(
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'   => 'email_slug_confirmed',
                        'label'  => __( 'Email slug for confirmed registration', WPXTREME_TEXTDOMAIN ),
                        'value'  => $values ? $values['email_slug_confirmed'] : '',
                        'append' => sprintf( '<a href="%s">%s</a>', $email_post_new_url, __( 'Create new', WPXTREME_TEXTDOMAIN ) ),
                        'title'     => __( 'Press down key for retrive email page list or enter any letter for slug or title.', WPXTREME_TEXTDOMAIN ),

                    )
                ),
            ),
            __( 'Double Optin', WPXTREME_TEXTDOMAIN ) => array(
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'      => 'double_optin',
                        'label'     => __( 'Enable Double optin', WPXTREME_TEXTDOMAIN ),
                        'value'     => $values ? $values['double_optin'] : '',
                        'checked'   => true,
                        'title'     => __( 'This setting allow to send an email confirmation to end user. The email contains an URL request to validate the user.', WPXTREME_TEXTDOMAIN ),
                    )
                ),
                array(
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'   => 'email_slug_confirm',
                        'label'  => __( 'Email slug for confirm', WPXTREME_TEXTDOMAIN ),
                        'value'  => $values ? $values['email_slug_confirm'] : '',
                        'append' => sprintf( '<a href="%s">%s</a>', $email_post_new_url, __( 'Create new', WPXTREME_TEXTDOMAIN ) ),
                        'title'     => __( 'Press down key for retrive email page list or enter any letter for slug or title.', WPXTREME_TEXTDOMAIN ),
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

        /* @todo 'subscriber' deve diventare una costante */

        $values = array(
            'page_registration_slug' => esc_attr( $_POST['page_registration_slug'] ),

            'default_user_role'    => isset( $_POST['default_user_role'] ) ? $_POST['default_user_role'] : 'subscriber',
            'default_user_status'  => isset( $_POST['default_user_status'] ) ? $_POST['default_user_status'] : 'disabled',
            'email_slug_confirmed' => esc_attr( $_POST['email_slug_confirmed'] ),

            'double_optin'         => isset( $_POST['double_optin'] ) ? $_POST['double_optin'] : false,
            'email_slug_confirm'   => esc_attr( $_POST['email_slug_confirm'] ),
        );

        WPXtreme::$settings->registration( $values );
    }

}

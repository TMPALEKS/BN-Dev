<?php
/**
 * @class              UsersSettingsSecurityView
 * @description        Vista security
 *
 * @package            wpXtreme
 * @subpackage         users/view
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            15/05/12
 * @version            1.0.0
 *
 */

class UsersSettingsSecurityView extends WPDKSettingsView {


    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    /// Construct
    function __construct() {
        $this->key          = 'security';
        $this->title        = __( 'Security', 'wp-xtreme' );
        $this->introduction = __( 'Please, write an introduction', 'wp-xtreme' );
        $this->settings     = WPXtreme::$settings;
    }

    /// Return the SDF fields for this view
    /**
     * Prepara l'array che descrive i campi del form
     *
     * @return array
     */
    function fields() {

        $values = WPXtreme::$settings->security();

        $fields = array(
            __( 'Login', 'wp-xtreme' ) => array(
                __('Enabled wrong login count and enter the number of attempts for wrong login, after these the user will be locked.', WPXTREME_TEXTDOMAIN ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'    => 'enabled_wrong_login_attempts',
                        'label'   => __( 'Enable wrong login count', WPXTREME_TEXTDOMAIN ),
                        'value'   => 'y',
                        'checked' => $values ? $values['enabled_wrong_login_attempts'] : ''
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'name'  => 'wrong_login_attempts',
                        'label' => __( 'Wrong login attempts', WPXTREME_TEXTDOMAIN ),
                        'value' => $values ? $values['wrong_login_attempts'] : ''
                    ),
                ),
                __('When a user reaches yours login attempts, the user is disabled and an e-mail is sent.', WPXTREME_TEXTDOMAIN ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'email_slug_wrong_login_attempts',
                        'label' => __( 'Email slug', WPXTREME_TEXTDOMAIN ),
                        'value' => $values ? $values['email_slug_wrong_login_attempts'] : ''
                    ),
                ),
            )
        );

        return $fields;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Settings actions
    // -----------------------------------------------------------------------------------------------------------------

    /// Save the settings
    /**
     * Save the settings
     */
    function save() {
        $values = array(
            'enabled_wrong_login_attempts'    => isset( $_POST['enabled_wrong_login_attempts'] ) ? $_POST['enabled_wrong_login_attempts'] : 'n',
            'wrong_login_attempts'            => absint( esc_attr( $_POST['wrong_login_attempts'] ) ),
            'email_slug_wrong_login_attempts' => esc_attr( $_POST['email_slug_wrong_login_attempts'] ),
        );

        WPXtreme::$settings->security( $values );
    }


}

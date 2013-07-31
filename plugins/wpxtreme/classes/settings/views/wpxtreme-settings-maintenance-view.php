<?php
/**
 * @description        View per il maintenance mode
 *
 * @package            wpXtreme
 * @subpackage         WPXtremeSettingsMaintenanceView
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            26/05/12
 * @version            1.0.0
 *
 * @filename           settings-maintenance-view
 *
 * @todo               Aggiungere scelta tra wp_die() e 503.php
 * @todo               Aggiungere title, page tiele e note nelle impostazioni - vedi settings
 *
 */

class WPXtremeSettingsMaintenanceView extends WPDKSettingsView {

    /**
     * Init
     */
    function __construct() {
        $this->key          = 'maintenance';
        $this->title        = __( 'Maintenance', WPXTREME_TEXTDOMAIN );
        $this->introduction = __( 'The Maintenance mode allow to put your site off-line for all not bypassing users. You can choose manual and time scheduling. Bypass users only have access to your site when maintenance mode is on.', WPXTREME_TEXTDOMAIN );
        $this->settings     = WPXtreme::$settings;
    }

    function templates() {
        $templates = array(
            'wp_die'    => __( 'Standard WordPress wp_die', WPXTREME_TEXTDOMAIN ),
            'theme-503' => __( '503.php in the current theme', WPXTREME_TEXTDOMAIN ),
        );
        return $templates;
    }

    /**
     * Prepara l'array che descrive i campi del form
     *
     * @return array
     */
    function fields() {

        $values     = WPXtreme::$settings->maintenance();
        $user_roles = WPDKUser::arrayRolesForSDF();
        $users_id   = WPDKUser::arrayUserForSDF();

        $fields = array(
            __( 'Scheduling', WPXTREME_TEXTDOMAIN ) => array(
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_SWIPE,
                        'name'  => 'enabled',
                        'label' => __( 'Enabled Now', WPXTREME_TEXTDOMAIN ),
                        'title' => __( 'If you enable this option the date start and expired below will be ignored.', WPXTREME_TEXTDOMAIN ),
                        'value' => $values ? $values['enabled'] : 'off'
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_DATETIME,
                        'name'  => 'date_start',
                        'label' => __( 'Enabled from', WPXTREME_TEXTDOMAIN ),
                        'value' => $values ? WPDKDateTime::formatFromFormat( $values['date_start'], 'YmdHi', __( 'm/d/Y H:i', WPXTREME_TEXTDOMAIN) ) : ''
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_DATETIME,
                        'name'  => 'date_expire',
                        'label' => __( 'Disable at', WPXTREME_TEXTDOMAIN ),
                        'value' => $values ? WPDKDateTime::formatFromFormat( $values['date_expire'], 'YmdHi', __( 'm/d/Y H:i', WPXTREME_TEXTDOMAIN) ) : '',
                        'append' => sprintf( ' <strong>%s</strong>: %s', __( 'Date on server', WPXTREME_TEXTDOMAIN ), date( __( 'm/d/Y H:i', WPXTREME_TEXTDOMAIN ) ) )
                    )
                ),
            ),

            __( 'Template', WPXTREME_TEXTDOMAIN ) => array(
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'    => 'template',
                        'label'   => __( 'Template', WPXTREME_TEXTDOMAIN ),
                        'options' => $this->templates(),
                        'title'   => __( 'Choose the design to be used for your Maintenance Mode screen.', WPXTREME_TEXTDOMAIN ),
                        'value'   => $values ? $values['template'] : ''
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'page_title',
                        'size'  => '48',
                        'label' => __( 'Page title', WPXTREME_TEXTDOMAIN ),
                        'title' => __( 'This is the page title for the Maintenance Mode page.', WPXTREME_TEXTDOMAIN ),
                        'value' => $values ? $values['page_title'] : ''
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'title',
                        'size'  => '48',
                        'label' => __( 'Title', WPXTREME_TEXTDOMAIN ),
                        'title' => __( 'This is the HTML title of the Maintenance Mode page.', WPXTREME_TEXTDOMAIN ),
                        'value' => $values ? $values['title'] : ''
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXTAREA,
                        'name'  => 'note',
                        'label' => __( 'Note', WPXTREME_TEXTDOMAIN ),
                        'cols'  => 40,
                        'title' => __( 'A brief note that will be included in the Maintenance Mode page.', WPXTREME_TEXTDOMAIN ),
                        'value' => $values ? $values['note'] : ''
                    )
                ),

            ),

            __( 'Advanced', WPXTREME_TEXTDOMAIN) => array(
                __( 'Disable backend login and restrict it for IP address. Be careful before active this option and you sure to have an ftp access.', WPXTREME_TEXTDOMAIN ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name' => 'disable_wp_login',
                        'label' => __( 'Disable WP Login', WPXTREME_TEXTDOMAIN ),
                        'value' => 'y',
                        'checked' => $values ? $values['disable_wp_login'] : '',
                    )
                ),
                array(
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'   => 'ip_address_temp',
                        'label'  => __( 'Bypass IP Address', WPXTREME_TEXTDOMAIN ),
                        'title'  => __( 'Enter an IP addrees like 193.34.32.21 and click to add button to the right. For your convenience it\'s already added your IP address.', WPXTREME_TEXTDOMAIN ),
                        'value'  => $_SERVER['REMOTE_ADDR'],
                        'append' =>
                        '<input data-options="clear_after_copy" data-copy="ip_address_temp" data-paste="ip_address" class="wpdk-form-button wpdk-form-button-copy-paste" type="button" value="' .
                            __( 'Add', WPXTREME_TEXTDOMAIN ) . '" />'
                    )
                ),
                array(
                    array(
                        'type'       => WPDK_FORM_FIELD_TYPE_SELECT,
                        'id'         => 'ip_address',
                        'name'       => 'ip_address[]',
                        'multiple'   => 'multiple',
                        'label'      => ' ',
                        'afterlabel' => '',
                        'size'       => 5,
                        'options'    => $values ? $values['ip_address'] : '',
                        'append'     => '<input data-remove_from="ip_address" class="wpdk-form-button wpdk-form-button-remove" type="button" value="' . __( 'Remove', WPXTREME_TEXTDOMAIN ) . '" />'
                    )
                ),
            ),

            __( 'Perimssions rules', WPXTREME_TEXTDOMAIN ) => array(
                __( 'Restrict access only for the rules below. You can choose for multiple user roles o one or more single user.', WPXTREME_TEXTDOMAIN ),
                array(
                    array(
                        'type'     => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'     => 'user_role_temp',
                        'label'    => __( 'Bypass User Role', WPXTREME_TEXTDOMAIN ),
                        'options'  => array( 'WPDKUser', 'arrayRolesForSDF' ),
                        'append'   => '<input type="button" data-copy="user_role_temp" data-paste="user_roles" class="wpdk-form-button wpdk-form-button-copy-paste" value="' . __( 'Add', WPXTREME_TEXTDOMAIN ) . '" />'
                    )
                ),
                array(
                    array(
                        'type'       => WPDK_FORM_FIELD_TYPE_SELECT,
                        'id'       => 'user_roles',
                        'name'       => 'user_roles[]',
                        'multiple'   => 'multiple',
                        'label'      => ' ',
                        'afterlabel' => '',
                        'size'       => 5,
                        'options'    => $values ? WPDKArray::arrayWithKey( $values['user_roles'], $user_roles ) : '',
                        'append'     => '<input data-remove_from="user_roles" class="wpdk-form-button wpdk-form-button-remove" type="button" value="' . __( 'Remove', WPXTREME_TEXTDOMAIN ) . '" />'
                    )
                ),

                array(
                    array(
                        'type'     => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'     => 'users_id_temp',
                        'label'    => __( 'Bypass Users', WPXTREME_TEXTDOMAIN ),
                        'options'  => array( 'WPDKUser', 'arrayUserForSDF' ),
                        'append'   => '<input data-copy="users_id_temp" data-paste="users_id" class="wpdk-form-button wpdk-form-button-copy-paste" type="button" value="' . __( 'Add', WPXTREME_TEXTDOMAIN ) . '" />'
                    )
                ),
                array(
                    array(
                        'type'       => WPDK_FORM_FIELD_TYPE_SELECT,
                        'id'       => 'users_id',
                        'name'       => 'users_id[]',
                        'multiple'   => 'multiple',
                        'label'      => ' ',
                        'afterlabel' => '',
                        'size'       => 5,
                        'options'    => $values ? WPDKArray::arrayWithKey( $values['users_id'], $users_id ) : '',
                        'append'     => '<input data-remove_from="users_id" class="wpdk-form-button wpdk-form-button-remove" type="button" value="' . __( 'Remove', WPXTREME_TEXTDOMAIN ) . '" />'
                    )
                ),
            ),

            __( 'Reminder messages for login as', WPXTREME_TEXTDOMAIN ) => array(
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'  => 'enabled_message_login',
                        'label' => __( 'Enabled a Warning message to backend login form', WPXTREME_TEXTDOMAIN ),
                        'value' => 'y',
                        'checked' => $values ? $values['enabled_message_login'] : ''
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXTAREA,
                        'name'  => 'message_login',
                        'cols'  => '100',
                        'value' => $values ? $values['message_login'] : ''
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'  => 'enabled_message_admin',
                        'label' => __( 'Enabled a Warning message to backend', WPXTREME_TEXTDOMAIN ),
                        'value' => 'y',
                        'checked' => $values ? $values['enabled_message_admin'] : ''
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXTAREA,
                        'name'  => 'message_admin',
                        'cols'  => '100',
                        'value' => $values ? $values['message_login'] : ''
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'  => 'enabled_message_footer',
                        'label' => __( 'Enabled a Warning on the footer page', WPXTREME_TEXTDOMAIN ),
                        'value' => 'y',
                        'checked' => $values ? $values['enabled_message_footer'] : ''
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXTAREA,
                        'name'  => 'message_footer',
                        'cols'  => '100',
                        'value' => $values ? $values['message_footer'] : ''
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

        /* Eseguo una seri di controlli e sanitize su alcuni capi critici. */

        /* Sanitizza indirizzi IP. */
        $ip = array();
        foreach ( $_POST['ip_address'] as $ip_address ) {
            /* regxp per verificare che sia un indirizzo ip */
            $ip_address      = $ip_address;
            $ip[$ip_address] = $ip_address;
        }

        /* Sanitizza user role. */
        $user_roles = array();
        foreach ( $_POST['user_roles'] as $role ) {
            $user_roles[$role] = $role;
        }

        /* Sanitizza gli user id. */
        $users_id = array();
        foreach ( $_POST['users_id'] as $id ) {
            $users_id[$id] = $id;
        }

        $values = array(
            'enabled'                 => isset( $_POST['enabled'] ) ? $_POST['enabled'] : 'n',
            'date_start'              => WPDKDateTime::formatFromFormat( $_POST['date_start'], __( 'm/d/Y H:i', WPXTREME_TEXTDOMAIN), 'YmdHi' ),
            'date_expire'             => WPDKDateTime::formatFromFormat( $_POST['date_expire'], __( 'm/d/Y H:i', WPXTREME_TEXTDOMAIN), 'YmdHi' ),
            'template'                => $_POST['template'],

            'page_title'              => $_POST['page_title'],
            'title'                   => $_POST['title'],
            'note'                    => $_POST['note'],

            'disable_wp_login'        => isset( $_POST['disable_wp_login'] ) ? $_POST['disable_wp_login'] : 'n',
            'ip_address'              => $ip,

            'enabled_message_login'   => isset( $_POST['enabled_message_login'] ) ? $_POST['enabled_message_login'] : 'n',
            'message_login'           => $_POST['message_login'],
            'enabled_message_admin'   => isset( $_POST['enabled_message_admin'] ) ? $_POST['enabled_message_admin'] : 'n',
            'message_admin'           => $_POST['message_admin'],
            'enabled_message_footer'  => isset( $_POST['enabled_message_footer'] ) ? $_POST['enabled_message_footer'] : 'n',
            'message_footer'          => $_POST['message_footer'],
            'user_roles'              => $user_roles,
            'users_id'                => $users_id,
        );

        WPXtreme::$settings->maintenance( $values );
    }

}

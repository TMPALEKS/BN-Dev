<?php
/**
 * @class              WPDKShortcode
 *
 * @description
 *
 * @package            wpXtreme
 * @subpackage         core
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc
 * @link               http://wpxtre.me
 * @created            19/06/12
 * @version            1.0.0
 *
 * @filename           wpdk-shortcode
 *
 */

class WPDKShortcode {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce un array con tutti gli shortcode da registrare, L'array è un classico key => value dove la chiave
     * indica lo shortcode e il metodo, mentre il value (true|false) indica se lo shortcode dev'essere registrato.
     *
     * @static
     * @return array Restituisce un array con tutti gli shortcode da registrare
     */
    public static function shortcodes() {
        $shortcodes = array(
            'wpdk_user_registration' => true,
            'wpdk_user_profile'      => true,
        );
        return $shortcodes;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress integration
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Registra gli shortcode
     *
     * @static
     *
     */
    public static function registerShortcodes() {

        foreach ( self::shortcodes() as $shortcode => $to_register ) {
            if ( $to_register ) {
                add_shortcode( $shortcode, array( __CLASS__, $shortcode ) );
            }
        }

    }

    // -----------------------------------------------------------------------------------------------------------------
    // Shortcode
    // -----------------------------------------------------------------------------------------------------------------

    public static function wpdk_user_registration( $attrs ) {

        /* Qui arrivo dalla mail dell'utente, lo abilito e gli mando username e password. */
        if ( isset( $_GET['wpdk_do'] ) && !empty( $_GET['wpdk_do'] ) ) {
            return WPDKUser::enableUserAfterDoubleOptin( $_GET['wpdk_do'] );
        }

        /* Mostro la form di registrazione. */
        if ( !isset( $_POST['wpdk_action_registration'] ) ) {

            /* Attributi standard per questo shortcode */
            $defaults = array(
                'extra_fields' => false,
            );

            /* Merge i default con quelli passati nello shortcode */
            $args = wp_parse_args( $attrs, $defaults );

            return WPDKUser::formRegistration( $args );

        }
        elseif ( $_POST['wpdk_action_registration'] == 'double-optin' ) {

            $first_name = esc_attr( $_POST['wpdk_first_name'] );
            $last_name  = esc_attr( $_POST['wpdk_last_name'] );
            $email      = sanitize_email( $_POST['wpdk_email'] );

            /* Registro l'utente bloccata ed invio la prima email di conferma. */
            return WPDKUser::registerUserForDoubleOptin( $first_name, $last_name, $email );
        }
        elseif ( $_POST['wpdk_action_registration'] == 'no-double-optin' ) {
            /* Registro l'utente immediatamente senza il double-optin. */
            //return WPDKUser::registerUserForDoubleOptin();
        }
    }

    public static function wpdk_user_profile( $attrs ) {

        /* Se non si è loggati esco */
        if( !is_user_logged_in() ) {
            return false;
        }

        /* Devo aggiornare? */
        if ( isset( $_POST['wpdk_action_profile'] ) && $_POST['wpdk_action_profile'] == 'update' ) {
            $update_user_id = absint( $_POST['wpdk_action_profile_id'] );
            $userdata = array(
                'ID'         => $update_user_id,
                'first_name' => esc_attr( $_POST['wpdk_first_name'] ),
                'last_name'  => esc_attr( $_POST['wpdk_last_name'] ),
                'user_email' => sanitize_email( $_POST['wpdk_email'] ),
            );
            $result = wp_update_user( $userdata );
            if ( $result != $update_user_id ) {
                return new WP_Error( 'wpdk_error-updating_user', __( 'Error while updating user profile.', WPDK_TEXTDOMAIN ), $userdata );
            }
        }

        /* Se sono amministratore, posso visualizzare anche il profilo di altri utenti */
        if( WPDKUser::isUserAdministrator() && isset( $_GET['id_user']) && !empty( $_GET['id_user'] ) ) {
            $id_profile = absint( $_GET['id_user'] );
        } else {
            $id_profile = get_current_user_id();
        }

        /* Attributi standard per questo shortcode */
        $defaults = array(
            'extra_fields' => false,
        );

        /* Merge i default con quelli passati nello shortcode */
        $args = wp_parse_args( $attrs, $defaults );

        $user = new WP_User( $id_profile );

        return WPDKUser::formProfile( $user );

    }
}
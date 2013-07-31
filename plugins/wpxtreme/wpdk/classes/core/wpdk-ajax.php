<?php
/**
 * @class              WPDKAjax
 *
 * @description Ajax gateway
 *
 * @package            WPDK
 * @subpackage         core
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc
 * @created            11/07/12
 * @version            1.0.0
 *
 */

if ( wpdk_is_ajax() ) {

    class WPDKAjax {

        // -------------------------------------------------------------------------------------------------------------
        // Statics: method array to register
        // -------------------------------------------------------------------------------------------------------------

        private static function actionsMethods() {

            $actionsMethods = array(
                'wpdk_action_user_by' => true,
            );
            return $actionsMethods;
        }

        // -------------------------------------------------------------------------------------------------------------
        // Register Ajax methods
        // -------------------------------------------------------------------------------------------------------------

        public static function registerAjaxMethods() {
            $actionsMethods = self::actionsMethods();
            foreach ( $actionsMethods as $method => $nopriv ) {
                add_action( 'wp_ajax_' . $method, array( __CLASS__, $method ) );
                if ( $nopriv ) {
                    add_action( 'wp_ajax_nopriv_' . $method, array( __CLASS__, $method ) );
                }
            }
        }

        // -------------------------------------------------------------------------------------------------------------
        // Actions methods
        // -------------------------------------------------------------------------------------------------------------

        public static function wpdk_action_user_by() {

            /* ID, name, email... */
            $pattern = esc_attr( $_POST['term'] );

            if ( !empty( $pattern ) ) {
                $pattern = '*' . $pattern . '*';
                $users = get_users( array( 'search' => $pattern ) );
                if ( !empty( $users ) ) {
                    $result = array();
                    foreach ( $users as $user ) {
                        $result[] = array(
                            'id'    => $user->ID,
                            'value' => sprintf( '%s (%s)', $user->display_name, $user->user_email )
                        );
                    }
                    echo json_encode( $result );
                }
            }
            die();
        }
    }

    WPDKAjax::registerAjaxMethods();
}

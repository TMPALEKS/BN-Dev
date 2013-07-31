<?php
/**
 * @class              WPXtremeAjax
 * @description        Ajax gateway engine
 *
 * @package            wpXtreme
 * @subpackage         core
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            09/05/12
 * @version            1.0.0
 *
 * @filename           wpxtreme-ajax
 *
 * @todo               Aggiungere il prefisso wpxm_ alle actions
 *
 */

if ( wpdk_is_ajax() ) {


    class WPXtremeAjax {

        // -------------------------------------------------------------------------------------------------------------
        // Constants values
        // -------------------------------------------------------------------------------------------------------------

        // -------------------------------------------------------------------------------------------------------------
        // Statics: method array to register
        // -------------------------------------------------------------------------------------------------------------

        private static function actionsMethods() {
            $actionsMethods = array(
                'action_user_set_status'       => true,
                'action_post_set_publish'      => true,

                'action_plugin_store_featured' => true,
                'action_plugin_store_product'  => true,

                'action_slug_post_email'       => true,
                'action_send_email_test'       => true,

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

        public static function action_user_set_status() {
            $id_user = absint( $_POST['id_user'] );
            $status  = esc_attr( $_POST['status'] );
            update_user_meta( $id_user, 'wpdk_user_internal-status', $status );
            if( $status == WPDKUser::kUserStatusConfirmed ) {
                update_user_meta( $id_user, 'wpdk_user_internal-count_wrong_login', 0 );
            }
            $result = array();
            echo json_encode( $result );
            die();
        }

        public static function action_post_set_publish() {
            $id_post     = absint( $_POST['id_post'] );
            $post_status = esc_attr( $_POST['status'] );

            $post = array(
                'ID'            => $id_post,
                'post_status'   => $post_status
            );

            wp_update_post( $post );

            $result = array();
            echo json_encode( $result );

            die();
        }

        // -------------------------------------------------------------------------------------------------------------
        // Plugin Store
        // -------------------------------------------------------------------------------------------------------------

        public static function action_plugin_store_featured() {
            $content = WPXtremeAPI::plugstore_featured();

            $result = array(
                'content' => $content
            );

            echo json_encode( $result );
            die();

        }

        public static function action_plugin_store_product() {
            $id_product = isset( $_POST['id_product'] ) ? absint( $_POST['id_product'] ) : false;
            if( $id_product ) {

                $content = WPXtremeAPI::product( $id_product );

                $result = array(
                    'content' => $content
                );

            } else {
                $result = array(
                    'message' => __( 'Error in product retrive, no id product.', WPXTREME_TEXTDOMAIN )
                );
            }
            echo json_encode( $result );
            die();
        }

        // -------------------------------------------------------------------------------------------------------------
        // Admin backend
        // -------------------------------------------------------------------------------------------------------------

        public static function action_slug_post_email() {
            global $wpdb;

            $term        = $_POST['term'];
            $table_posts = $wpdb->posts;
            $post_type   = WPXTREME_MAIL_CPT_KEY;

            $where_post_name = '';
            if ( !empty( $term ) ) {
                $where_post_name = sprintf( ' AND ( post_name LIKE "%%%s%%" OR post_title LIKE "%%%s%%" )', $term, $term );
            }

            $sql    = <<< SQL
SELECT post_name, post_title
FROM {$table_posts}
WHERE 1
{$where_post_name}
AND post_type = '{$post_type}'
AND post_status = 'publish'
ORDER BY post_title
SQL;
            $result = $wpdb->get_results( $sql );
            if ( !is_wp_error( $result ) ) {
                foreach ( $result as $post ) {
                    $response[] = array(
                        'value' => $post->post_name,
                        'label' => apply_filters( 'the_title', $post->post_title ),
                    );
                }
            }

            echo json_encode( $response );
            die();
        }

        public static function action_send_email_test() {

            $to      = esc_attr( $_POST['to'] );
            $id_post = absint( $_POST['id_post'] );

            $result  = WPXtremeMailCustomPostType::mail( $id_post, $to, false, 'wpXtreme Test <info@wpxtre.me>' );

            if ( $result ) {
                $response = array(
                    'message' => __( 'Mail sending', WPXTREME_TEXTDOMAIN )
                );
            } else {
                $response = array(
                    'message' => __( 'Warning! Mail not send!', WPXTREME_TEXTDOMAIN )
                );
            }

            echo json_encode( $response );
            die();
        }


    }

    WPXtremeAjax::registerAjaxMethods();
}

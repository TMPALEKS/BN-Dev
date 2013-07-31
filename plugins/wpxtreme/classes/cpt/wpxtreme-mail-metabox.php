<?php
/**
 * @class              WPXtremeMailMetaBox
 *
 * @description        Gestisce il meta box delle mail custom post type. Questo permette di impostare varie opzioni
 *                     come il destinatario in copia nascosta, il from etc...
 *
 * @package            wpXtreme
 * @subpackage         cpt
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc
 * @link               http://wpxtre.me
 * @created            19/06/12
 * @version            1.0.0
 *
 * @filename           wpxtreme-mail-metabox
 *
 */

class WPXtremeMailMetaBox {

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress Meta Boxes registration
    // -----------------------------------------------------------------------------------------------------------------

    public static function registerMetaBoxes() {
        add_meta_box( 'mail_settings', __('Email Settings', WPXTREME_TEXTDOMAIN ), array( __CLASS__, 'display_mail_settings' ), WPXTREME_MAIL_CPT_KEY, 'normal', 'high');
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Meta Box Views
    // -----------------------------------------------------------------------------------------------------------------

    public static function display_mail_settings() {
        global $post;

        $fields = array(
            __( 'Mail header', WPXTREME_TEXTDOMAIN ) => array(
                array(
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'   => 'wpxm_cpt_mail_from',
                        'label'  => __( 'Sender', WPXTREME_TEXTDOMAIN ),
                        'value'  => isset( $post ) ? get_post_meta( $post->ID, 'wpxm_cpt_mail_from', true ) : 'wpXtreme <info@wpxtre.me>',
                        'append' => __( 'You can use <code>name &lt;email&gt;</code>, Eg. <code>James Kirk &lt;j.t.kirk@starfleet.com&gt;</code>', WPXTREME_TEXTDOMAIN )
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name' => 'wpxm_cpt_mail_cc',
                        'value'  => isset( $post ) ? get_post_meta( $post->ID, 'wpxm_cpt_mail_cc', true ) : '',
                        'label' => __('Cc', WPXTREME_TEXTDOMAIN)
                    )
                ),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name' => 'wpxm_cpt_mail_bcc',
                        'value'  => isset( $post ) ? get_post_meta( $post->ID, 'wpxm_cpt_mail_bcc', true ) : '',
                        'label' => __('Bcc', WPXTREME_TEXTDOMAIN)
                    )
                ),
            ),
            __( 'Test', WPXTREME_TEXTDOMAIN ) => array(
                __( 'Use this panel to send this mail for a quick test. please, choose an email address for check.', WPXTREME_TEXTDOMAIN),
                array(
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'  => 'wpxm_cpt_mail_test_to',
                        'label' => __('To', WPXTREME_TEXTDOMAIN),
                        'title' => __('Usually enter your email', WPXTREME_TEXTDOMAIN)
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_BUTTON,
                        'name' => 'wpxm_cpt_mail_test_sender',
                        'data' => array( 'id_post' => isset($post) ? $post->ID : '' ),
                        'value' => __('Send', WPXTREME_TEXTDOMAIN)
                    )
                ),
            )
        );

        WPDKForm::nonceWithKey( WPXTREME_MAIL_CPT_KEY );
        WPDKForm::htmlForm( $fields );
    }

    public static function save_mail_settings( $post ) {
        $id_post = absint( $post->ID );

        if ( empty( $_POST['wpxm_cpt_mail_from'] ) ) {
            delete_post_meta( $id_post, 'wpxm_cpt_mail_from' );
        } else {
            update_post_meta( $id_post, 'wpxm_cpt_mail_from', esc_attr( $_POST['wpxm_cpt_mail_from'] ) );
        }

        if ( empty( $_POST['wpxm_cpt_mail_cc'] ) ) {
            delete_post_meta( $id_post, 'wpxm_cpt_mail_cc' );
        } else {
            update_post_meta( $id_post, 'wpxm_cpt_mail_cc', esc_attr( $_POST['wpxm_cpt_mail_cc'] ) );
        }

        if ( empty( $_POST['wpxm_cpt_mail_bcc'] ) ) {
            delete_post_meta( $id_post, 'wpxm_cpt_mail_bcc' );
        } else {
            update_post_meta( $id_post, 'wpxm_cpt_mail_bcc', esc_attr( $_POST['wpxm_cpt_mail_bcc'] ) );
        }

    }


}

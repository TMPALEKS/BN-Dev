<?php
/**
 * Vista extra fields
 *
 * @package            wpXtreme
 * @subpackage         UsersSettingsExtrafieldsView
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            16/05/12
 * @version            1.0.0
 *
 */

class UsersSettingsExtrafieldsView extends WPDKSettingsView {

    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    function __construct() {
        $this->key          = 'extrafields';
        $this->title        = __( 'Extra Fields', WPXTREME_TEXTDOMAIN );
        $this->introduction = __( 'Please, write an introduction', WPXTREME_TEXTDOMAIN );
        $this->settings     = WPXtreme::$settings;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Custom display
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Use this instead fields()
     */
    function content() {

        $fields_type = array(
            ''                         => __( 'Select a field type', WPXTREME_TEXTDOMAIN ),
            WPDK_FORM_FIELD_TYPE_CHECKBOX => __( 'Checkbox', WPXTREME_TEXTDOMAIN ),
            WPDK_FORM_FIELD_TYPE_DATE     => __( 'Date', WPXTREME_TEXTDOMAIN ),
            WPDK_FORM_FIELD_TYPE_DATETIME => __( 'Date Time', WPXTREME_TEXTDOMAIN ),
            WPDK_FORM_FIELD_TYPE_EMAIL    => __( 'Email', WPXTREME_TEXTDOMAIN ),
            WPDK_FORM_FIELD_TYPE_FILE     => __( 'File', WPXTREME_TEXTDOMAIN ),
            WPDK_FORM_FIELD_TYPE_NUMBER   => __( 'Number', WPXTREME_TEXTDOMAIN ),
            WPDK_FORM_FIELD_TYPE_TEXT     => __( 'Text', WPXTREME_TEXTDOMAIN ),
            WPDK_FORM_FIELD_TYPE_TEXTAREA => __( 'Text Area', WPXTREME_TEXTDOMAIN ),
            WPDK_FORM_FIELD_TYPE_PASSWORD => __( 'Password', WPXTREME_TEXTDOMAIN ),
            WPDK_FORM_FIELD_TYPE_SELECT   => __( 'Combo Select', WPXTREME_TEXTDOMAIN ),
        );

        $columns = array(
            'type'    => array(
                'table_title'   => __( 'Type', WPXTREME_TEXTDOMAIN ),
                'type'          => WPDK_FORM_FIELD_TYPE_SELECT,
                'name'          => 'type[]',
                'class'         => 'wpxm_users_extra_field_type',
                'title'         => __( 'Select a field type', WPXTREME_TEXTDOMAIN ),
                'data'          => array( 'placement' => 'left' ),
                'options'       => $fields_type,
                'value'         => '',
            ),
            'name'    => array(
                'table_title'   => __( 'Name', WPXTREME_TEXTDOMAIN ),
                'type'          => WPDK_FORM_FIELD_TYPE_TEXT,
                'name'          => 'name[]',
                'class'         => 'wpxm_users_extra_field_name',
                'data'          => array( 'placement' => 'left' ),
                'size'          => 12,
                'value'         => '',
            ),
            'label'    => array(
                'table_title'   => __( 'Label', WPXTREME_TEXTDOMAIN ),
                'type'          => WPDK_FORM_FIELD_TYPE_TEXT,
                'name'          => 'label[]',
                'class'         => 'wpxm_users_extra_field_label',
                'data'          => array( 'placement' => 'left' ),
                'size'          => 16,
                'value'         => '',
            ),
            'placeholder'    => array(
                'table_title'   => __( 'Placeholder', WPXTREME_TEXTDOMAIN ),
                'type'          => WPDK_FORM_FIELD_TYPE_TEXT,
                'name'          => 'placeholder[]',
                'class'         => 'wpxm_users_extra_field_placeholder',
                'data'          => array( 'placement' => 'left' ),
                'size'          => 16,
                'value'         => '',
            ),
            'value'    => array(
                'table_title'   => __( 'Default value', WPXTREME_TEXTDOMAIN ),
                'type'          => WPDK_FORM_FIELD_TYPE_TEXT,
                'name'          => 'value[]',
                'class'         => 'wpxm_users_extra_field_value',
                'data'          => array( 'placement' => 'left' ),
                'size'          => 10,
                'value'         => '',
            ),
        );


        $items   = WPXtreme::$settings->extrafields();

        $table   = new WPDKDynamicTable( 'wpxm-dynamic-table-extra-fields', $columns, $items );

        echo $table->view();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Settings actions
    // -----------------------------------------------------------------------------------------------------------------

    /**
     *
     */
    function save() {

        $extra_fields = array();
        for ( $i = 0; $i < count( $_POST['type'] ); $i++ ) {
            if ( !empty( $_POST['type'][$i] ) ) {
                $extra_fields[] = array(
                    'type'        => esc_attr( $_POST['type'][$i] ),
                    'name'        => esc_attr( $_POST['name'][$i] ),
                    'label'       => esc_attr( $_POST['label'][$i] ),
                    'placeholder' => esc_attr( $_POST['placeholder'][$i] ),
                    'value'       => esc_attr( $_POST['value'][$i] ),
                );
            }
        }

        WPXtreme::$settings->extrafields( $extra_fields );
    }

}

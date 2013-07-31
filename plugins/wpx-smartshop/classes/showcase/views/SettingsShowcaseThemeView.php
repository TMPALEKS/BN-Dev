<?php
/**
 * Vista per la gestione delle impostazioni sulla vetrina.
 *
 * @package            wpx SmartShop
 * @subpackage         SettingsShowcaseThemeView
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            06/03/12
 * @version            1.0.0
 *
 */

class SettingsShowcaseThemeView extends WPDKSettingsView {
    
    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    function __construct() {
        $this->key          = 'showcase';
        $this->title        = __( 'Showcase', WPXSMARTSHOP_TEXTDOMAIN );
        $this->introduction = __( 'From this settings panel you can set all render layut theme for Showcase pages.', 'wp-xtreme' );
        $this->settings     = WPXSmartShop::settings();
    }
    
    /**
     * Prepara l'array che descrive i campi del form
     *
     * @static
     * @retval array
     */
    function fields() {

        $values = WPXSmartShop::settings()->showcase();

        $hidden = '';
        if( $values['theme_page'] != 'custom') {
            $hidden = 'hidden';
        }

        $fields = array(
            __( 'Theme integration', WPXSMARTSHOP_TEXTDOMAIN )          => array(
                __( 'Choose if use your page template for display Showcase', WPXSMARTSHOP_TEXTDOMAIN ),
                array(
                    array(
                        'type'          => WPDK_FORM_FIELD_TYPE_RADIO,
                        'name'          => 'theme_page',
                        'label'         => __( 'Use page layout <code>page.php</code>', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'         => 'page',
                        'checked'       => $values['theme_page']
                    ),
                    array(
                        'type'          => WPDK_FORM_FIELD_TYPE_RADIO,
                        'name'          => 'theme_page',
                        'label'         => __( 'Post <code>single.php</code>', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'         => 'single',
                        'checked'       => $values['theme_page']
                    ),
                    array(
                        'type'          => WPDK_FORM_FIELD_TYPE_RADIO,
                        'name'          => 'theme_page',
                        'label'         => __( 'Custom', WPXSMARTSHOP_TEXTDOMAIN ),
                        'value'         => 'custom',
                        'checked'       => $values['theme_page']
                    ),
                ),
                array(
                    'group' => array(
                        array(
                            array(
                                'type'          => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                                'name'          => 'theme_header',
                                'label'         => __( 'Include header', WPXSMARTSHOP_TEXTDOMAIN ),
                                'value'         => 'y',
                                'checked'       => $values['theme_header']
                            )
                        ),

                        array(
                            array(
                                'type'       => WPDK_FORM_FIELD_TYPE_TEXTAREA,
                                'name'       => 'theme_markup_header',
                                'value'      => base64_decode( $values['theme_markup_header'] ),
                                'label'      => __( 'HTML Markup (before and after sidebar)', WPXSMARTSHOP_TEXTDOMAIN ),
                                'afterlabel' => '',
                                'class'      => 'wpdk-form-textarea-code'
                            )
                        ),

                        array(
                            array(
                                'type'     => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                                'name'     => 'theme_sidebar',
                                'label'    => __( 'Include sidebar', WPXSMARTSHOP_TEXTDOMAIN ),
                                'value'    => 'y',
                                'checked'  => $values['theme_sidebar']
                            ),
                            array(
                                'type'     => WPDK_FORM_FIELD_TYPE_TEXT,
                                'name'     => 'theme_sidebar_id',
                                'label'    => __( 'Sidebar ID', WPXSMARTSHOP_TEXTDOMAIN ),
                                'size'     => 8,
                                'value'    => $values['theme_sidebar_id']
                            ),
                        ),

                        array(
                            array(
                                'type'  => WPDK_FORM_FIELD_TYPE_TEXTAREA,
                                'name'  => 'theme_markup_footer',
                                'value' => base64_decode( $values['theme_markup_footer'] ),
                                'class' => 'wpdk-form-textarea-code'
                            )
                        ),

                        array(
                            array(
                                'type'          => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                                'name'          => 'theme_footer',
                                'label'         => __( 'Include footer', WPXSMARTSHOP_TEXTDOMAIN ),
                                'value'         => 'y',
                                'checked'       => $values['theme_footer']
                            )
                        ),
                    ),
                    'class'       => 'wpss-showcase-custom-theme ' . $hidden
                )
            ),
        );
        return $fields;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Settings actions
    // -----------------------------------------------------------------------------------------------------------------

    /**
     *
     * @todo Aggiungere operatore ternario
     */
    function save() {
        $values = array(
            'theme_page'             => isset( $_POST['theme_page'] ) ? $_POST['theme_page'] : '',
            'theme_header'           => $_POST['theme_header'],
            'theme_footer'           => $_POST['theme_footer'],
            'theme_sidebar'          => $_POST['theme_sidebar'],
            'theme_sidebar_id'       => $_POST['theme_sidebar_id'],
            'theme_markup_header'    => base64_encode( stripcslashes( esc_attr( $_POST['theme_markup_header'] ) ) ),
            'theme_markup_footer'    => base64_encode( stripcslashes( esc_attr( $_POST['theme_markup_footer'] ) ) ),
        );

        WPXSmartShop::settings()->showcase( $values );
    }

}

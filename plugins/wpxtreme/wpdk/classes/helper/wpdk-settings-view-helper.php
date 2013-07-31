<?php
/**
 * @description        Classe base ereditata dalle view di settings
 *
 * @package            WPDK (WordPress Development Kit)
 * @subpackage         WPDKSettingsView
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            14/05/12
 * @version            1.0.0
 *
 * @filename           wpdk-settings-view-helper.php
 *
 * @todo               Da completare con dei filtri sui messaggi e/o metodi sulla static
 *
 */

class WPDKSettingsView {

    var $key;
    var $title;
    var $introduction;
    var $settings;

    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    function __construct( $key, $title, $settings, $introduction = '') {
        $this->key          = $key;
        $this->title        = $title;
        $this->settings     = $settings;
        $this->introduction = ( $introduction === false ) ? __( 'Please, write an introduction', WPDK_TEXTDOMAIN ) : '';
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Display
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Visualizza il form
     *
     */
    function display() {
        if ( WPDKForm::isNonceVerify( $this->key ) ) {
            $this->update();
        } ?>
    <form class="wpdk-settings-view-<?php echo $this->key ?> wpdk-form" action="" method="post">
        <?php WPDKForm::nonceWithKey( $this->key ) ?>

        <p><?php echo $this->introduction ?></p>

        <?php
            if( method_exists( $this, 'content' ) ) {
                $this->content();
            } else {
                WPDKForm::htmlForm( $this->fields() );
            }
        ?>

        <?php WPDKUI::buttonsUpdateReset() ?>
    </form><?php

    }

    /**
     * Restituisce il contenuto di ::display();
     *
     * @return string
     */
    function html() {
        ob_start();
        $this->display();
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Database/Options
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Verifica se è necessario fare un aggiornamento o reset dei dati
     *
     */
    function update() {

        if ( isset( $_POST['resetToDefault'] ) ) {
            /* Reset to default */
            $result = $this->settings->resetDefault( $this->key );

            /* @todo Aggiungere filtro */
            WPDKUI::message( sprintf( __( 'The <strong>%s</strong> settings were restored to defaults values successfully!', WPDK_TEXTDOMAIN ), $this->title ), true );
        } else {
            /* Save */
            if( method_exists( $this, 'save') ) {
                $this->save();
            } else {
                /* Autosave for key. */
                if ( method_exists( $this, 'valuesForSave' ) ) {
                    $values = $this->valuesForSave();
                    $key    = $this->key;
                    $this->settings->$key( $values );
                } else {
                    WPDKUI::error( __( 'No settings update!', WPDK_TEXTDOMAIN ) );
                    return;
                }
            }

            /* @todo Aggiungere filtro */
            WPDKUI::message( sprintf( __( 'The <strong>%s</strong> settings values were updated successfully!', WPDK_TEXTDOMAIN ), $this->title ) );
        }
    }


}

<?php
/**
 * wpXstore model
 *
 * @package            WPXtreme
 * @subpackage         WPXtremeStore
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            09/05/12
 * @version            1.0.0
 *
 */

class WPXtremeStore {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    public static function isLogin() {
        $options = get_option('wpxtreme');

        if( $options === false ) {
            /* Prima volta in assoluto */
        } else {
            $settings = unserialize( $options );
            if( empty($settings['secure_key'])) {
                return false;
            } else {
                $result = WPXtremeAPI::login( $settings['secure_key']);
                return $result;
            }
        }
    }

}

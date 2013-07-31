<?php
/**
 * @class              WPXtremeCreditsViewController
 * @description        Credits view controller
 *
 * @package            wpXtreme
 * @subpackage         admin
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @created            04/06/12
 * @version            1.0.0
 *
 */

class WPXtremeCreditsViewController {

    /// Construct
    function __construct() {
    }

    /// Build credits
    function credits() {
        $credits = array(
            __( 'Developers & UI Designers', WPXRTEME_TEXTDOMAIN ) => array(
                array(
                    'name'  => 'Giovambattista Fazioli (Design & Develop)',
                    'mail'  => 'g.fazioli@wpxtre.me',
                    'site'  => 'http://www.undolog.com',
                ),
            ),

            __( 'Translations', WPXRTEME_TEXTDOMAIN ) => array(
                array(
                    'name'  => 'Baris Unver (Turkish)',
                    'mail'  => 'baris.unver@beyn.orge',
                    'site'  => 'http://beyn.org/',
                ),
                array(
                    'name'  => 'Valentin B (French)',
                    'mail'  => '',
                    'site'  => 'http://geekeries.fr/',
                ),
                array(
                    'name'  => 'rauchmelder (German)',
                    'mail'  => 'team@fakten-fiktionen.de',
                    'site'  => '#',
                ),
                array(
                    'name'  => 'Győző Farkas alias FYGureout (Hungarian)',
                    'mail'  => 'webmester@wordpress2you.com',
                    'site'  => 'http://www.wordpress2you.com',
                ),
            ),

            __( 'Bugs report and beta tester', WPXRTEME_TEXTDOMAIN ) => array(
                array(
                    'name'  => 'Lazy79',
                    'mail'  => '#',
                    'site'  => 'http://wordpress.org/support/profile/231784',
                ),
                array(
                    'name'  => 'Baris Unver',
                    'mail'  => 'baris.unver@beyn.org',
                    'site'  => 'http://beyn.org/',
                ),
            ),

            __( 'One more thanks...', WPXRTEME_TEXTDOMAIN ) => array(
                array(
                    'name'  => 'Matteo Fantuzzi (Support)',
                    'mail'  => 'm.fantuzzi@saidmade.com',
                    'site'  => 'http://wpxtre.me',
                ),
                array(
                    'name'  => 'Nicola Ballotta (Executive Producer)',
                    'mail'  => 'n.ballotta@saidmade.com',
                    'site'  => 'http://wpxtre.me',
                ),
            ),
        );
        return $credits;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Display
    // -----------------------------------------------------------------------------------------------------------------

    /// Display credits
    /* @todo Potremmo metterlo in wpdk, gli stili già sono li. */
    function display() {
        $content = WPDKUI::credits( self::credits() );
        echo WPDKUI::view( '', __( 'Credits', WPXRTEME_TEXTDOMAIN ), 'wpxm-icon-xtreme', $content );

    }

}

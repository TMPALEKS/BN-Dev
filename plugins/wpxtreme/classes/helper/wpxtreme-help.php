<?php
/**
 * @class              WPXtremeHelp
 *
 * @description        Tutto l'help viene concentrato in questa classe
 *
 * @package            wpXtreme
 * @subpackage         helper
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            08/06/12
 * @version            1.0.0
 *
 * @filename           wpxtreme-help
 *
 */

class WPXtremeHelp {

    /**
     * Init
     */
    function __construct() {
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Common
    // -----------------------------------------------------------------------------------------------------------------

    static function sidebar() {
        $html = '<p><strong>' . __('For more information:', WPXTREME_TEXTDOMAIN) . '</strong></p>' .
      		'<p>' . __('<a href="http://wpxtre.me/" target="_blank">wpXtreme Web Site</a>', WPXTREME_TEXTDOMAIN ) . '</p>' .
      		'<p>' . __('<a href="http://wpxtre.me/" target="_blank">Store</a>', WPXTREME_TEXTDOMAIN ) . '</p>' .
      		'<p>' . __('<a href="http://wpxtre.me/" target="_blank">Projects on Github</a>', WPXTREME_TEXTDOMAIN ) . '</p>' .
      		'<p>' . __('<a href="http://wpxtre.me/" target="_blank">WPDK Docs</a>', WPXTREME_TEXTDOMAIN ) . '</p>' .
      		'<p>' . __('<a href="http://wpxtre.me/" target="_blank">Official Forum</a>', WPXTREME_TEXTDOMAIN ) . '</p>' .
      		'<p>' . __('<a href="http://wpxtre.me/" target="_blank">Official Support</a>', WPXTREME_TEXTDOMAIN ) . '</p>';
        return $html;


    }

    // -----------------------------------------------------------------------------------------------------------------
    // Mail Custom Post Type
    // -----------------------------------------------------------------------------------------------------------------

    static function mail_introducing() {
        return array(
            'title'    => __('Introducing', WPXTREME_TEXTDOMAIN),
            'id'       => 'mail_introducing',
            'content'  => __( '<p>Mail Custom Post Type can be used for various purposes.</p>' .
                              '<p>For example, when a user is locked for some reason, a mail may be sent to the web master or any other email address.</p>', WPXTREME_TEXTDOMAIN ),
            'callback' => false
        );
    }
    static function mail_placeholder() {
        return array(
            'title'    => __('Placeholder', WPXTREME_TEXTDOMAIN),
            'id'       => 'mail_placeholder',
            'content'  => __( '<p>Mail post type supports several placeholder in order to format a comodity mail.</p>' .
                              '<p>The placeholder supported are:</p>' .
                              '<ul>' .
                              '<li><code>'.WPXTREME_MAIL_PLACEHOLDER_USER_FIRST_NAME.'</code> User first name</li>' .
                              '<li><code>'.WPXTREME_MAIL_PLACEHOLDER_USER_LAST_NAME.'</code> User last name</li>' .
                              '<li><code>'.WPXTREME_MAIL_PLACEHOLDER_USER_DISPLAY_NAME.'</code> User Display name</li>' .
                              '<li><code>'.WPXTREME_MAIL_PLACEHOLDER_USER_EMAIL.'</code> User email</li>' .
                              '<li><code>'.WPXTREME_MAIL_PLACEHOLDER_DOUBLE_OPTIN_ACTIVATION_URL.'</code> Link for confirm registration</li>' .
                              '<li><code>'.WPXTREME_MAIL_PLACEHOLDER_USER_PASSWORD.'</code> Password for confirm registration</li>' .
                              '</ul>', WPXTREME_TEXTDOMAIN ),
            'callback' => false
        );
    }


}

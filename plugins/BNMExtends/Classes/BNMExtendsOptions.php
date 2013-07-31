<?php
/**
 * Manage Plugin Options
 *
 * @package            Blue Note Milano
 * @subpackage         BNMExtendsOptions
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@saidmade.com>
 * @copyright          Copyright (c) 2012 Saidmade Srl.
 * @link               http://www.saidmade.com
 * @created            30/01/12
 * @version            1.0.0
 *
 */

class BNMExtendsOptions {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    public static function defaultOptions() {
        $default = array(
            'Version'        => kBNMExtendsVersion,

            'Settings'       => array(

                'General'     => array(),

                'HomePage'    => array(
                    'leaderboard'   => 'n',
                    'numberEvents'   => 5,
                    'numberFeatured' => 5
                ),

                'Products'  => array(
                    'percentage_advance'    => 15
                ),
            ),
        );
        return $default;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Chiamata quando il plugin è attivato la prima volta
     * @static
     *
     */
    public static function init() {
        add_option( kBNMExtendsPluginSlugName, self::defaultOptions() );
        $options        = get_option( kBNMExtendsPluginSlugName );
        $optionsDefault = self::defaultOptions();
        $currentOptions = wp_parse_args( $options, $optionsDefault );
        update_option( kBNMExtendsPluginSlugName, $currentOptions );
    }

    public static function resetToDefaults() {
        delete_option( kBNMExtendsPluginSlugName );
        return add_option( kBNMExtendsPluginSlugName, self::defaultOptions() );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Home Page
    // -----------------------------------------------------------------------------------------------------------------

    public static function version() {
        $options = get_option( kBNMExtendsPluginSlugName );
        return $options['Version'];
    }

    public static function leaderboard( $value = null ) {
        $options = get_option( kBNMExtendsPluginSlugName );
        if ( is_null( $value ) ) {
            return $options['Settings']['HomePage']['leaderboard'];
        } else {
            $options['Settings']['HomePage']['leaderboard'] = $value;
            update_option( kBNMExtendsPluginSlugName, $options );
        }
    }

    public static function numberEvents( $value = null ) {
        $options = get_option( kBNMExtendsPluginSlugName );
        if ( is_null( $value ) ) {
            return $options['Settings']['HomePage']['numberEvents'];
        } else {
            $options['Settings']['HomePage']['numberEvents'] = $value;
            update_option( kBNMExtendsPluginSlugName, $options );
        }
    }

    public static function numberFeatured( $value = null ) {
        $options = get_option( kBNMExtendsPluginSlugName );
        if ( is_null( $value ) ) {
            return $options['Settings']['HomePage']['numberFeatured'];
        } else {
            $options['Settings']['HomePage']['numberFeatured'] = $value;
            update_option( kBNMExtendsPluginSlugName, $options );
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Products
    // -----------------------------------------------------------------------------------------------------------------

    public static function percentageAdvance( $value = null ) {
        $options = get_option( kBNMExtendsPluginSlugName );
        if ( is_null( $value ) ) {
            return $options['Settings']['Products']['percentage_advance'];
        } else {
            $options['Settings']['Products']['percentage_advance'] = $value;
            update_option( kBNMExtendsPluginSlugName, $options );
        }
    }
}

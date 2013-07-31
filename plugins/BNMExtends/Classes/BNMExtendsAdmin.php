<?php
/**
 * Class for Admin
 *
 * @package         BNMExtends
 * @subpackage      BNMExtendsAdmin
 * @author          =undo= <g.fazioli@saidmade.com>
 * @copyright       Copyright © 2010-2011 Saidmade Srl
 *
 */

/* View Controller */
require_once('BNMExtendsOptionViewController.php');

class BNMExtendsAdmin {

    public static $menus;

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    public static function menus() {
        $menus = array(
            'bnm-options' => array(
                'parent_slug'        => 'options-general.php',
                'page_title'         => 'Blue Note Milano',
                'menu_title'         => 'Blue Note Milano',
                'capability'         => 'manage_options',
                'callback'           => array( __CLASS__, 'menuItemOptions' ),
            )
        );
        return $menus;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Init
    // -----------------------------------------------------------------------------------------------------------------

    public static function init() {

        /* Admin page is loaded */
        add_action('admin_menu', array( __CLASS__, 'admin_menu' ));

        /* Plugin list */
        add_action('plugin_action_links_' . basename(dirname(kBNMExtends__FILE__)) . '/' . basename(kBNMExtends__FILE__), array(__CLASS__, 'plugin_action_links'), 10, 4);
        add_filter('plugin_row_meta', array(__CLASS__, 'plugin_row_meta'), 10, 2);

        /* Patch WordPress backend */
        self::setDashboard();
        self::setStandardWidget();
        self::setPostMetaBoxes();
        self::setPostAndPageColumns();
        self::setCustomLogo();
        self::setAdminFooter();

        /* Uncomment the line below to display the file log in dashboard */
        //self::addWidgetMonitor();

        /* Dashboard */
        add_action('wp_dashboard_setup', array(__CLASS__, 'wp_dashboard_setup' ));

        /* Custom styles */
        add_action('admin_head', array( __CLASS__, 'admin_head') );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress Integration
    // -----------------------------------------------------------------------------------------------------------------

    public static function admin_menu() {
        self::$menus = self::menus();
        self::$menus = WPDKMenu::menus( self::menus() );

        //self::$menus['menuItemOptions']['hook'] = add_menu_page( self::$menus['menuItemOptions']['plugin_title'], self::$menus['menuItemOptions']['plugin_title'], 'manage_options', 'bnm-mainshow', self::$menus['menuItemOptions']['callback'] );

        /* Gli passo l'hook del menu; tutto il menu */
        BNMExtendsOptionViewController::init( self::$menus['bnm-options'] );
    }

    public static function admin_head() {
        ?>
    <style type="text/css" media="screen">
        .icon32-posts-bnm-event,
        .icon32-posts-bnm-system-page,
        .icon32-posts-bnm-artist {
            background-image    : url(data:image/png;base64,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) !important;
            background-position : 0 0 !important;
        }
    </style>
    <?php
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Dashboard
    // -----------------------------------------------------------------------------------------------------------------

    public static function wp_dashboard_setup() {
        if (current_user_can(kBNMExtendsDashboardCapabilitiy)) {
            wp_add_dashboard_widget(kBNMExtendsPluginName, '<img src=" ' . kBNMExtendsBase64AdminLogo . ' " /> ' . __('Blue Note - Summary Report'), function () {
                include_once(kBNMExtendspDashboardView);
            });
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Patch WordPress backend; hide some elements
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Hide or Show meta box in dashboard
     *
     * @return void
     */
    public static function setDashboard() {
        add_action('wp_dashboard_setup', 'remove_dashboard_widgets');

        function remove_dashboard_widgets() {
            remove_meta_box('dashboard_right_now', 'dashboard', 'normal');
            remove_meta_box('dashboard_recent_comments', 'dashboard', 'normal');
            remove_meta_box('dashboard_incoming_links', 'dashboard', 'normal');
            remove_meta_box('dashboard_plugins', 'dashboard', 'normal');

            remove_meta_box('dashboard_quick_press', 'dashboard', 'side');
            remove_meta_box('dashboard_recent_drafts', 'dashboard', 'side');
            remove_meta_box('dashboard_primary', 'dashboard', 'side');
            remove_meta_box('dashboard_secondary', 'dashboard', 'side');
        }
    }

    /**
     * Hide or Show default widgets in Widget area
     *
     * @return void
     */
    public static function setStandardWidget() {
        add_action('widgets_init', 'remove_some_wp_widgets', 1);

        function remove_some_wp_widgets() {
            unregister_widget('WP_Widget_Calendar');
            unregister_widget('WP_Widget_Search');
            unregister_widget('WP_Widget_Recent_Comments');
        }
    }

    /**
     * Hide or Show Post & Page Metaboxes
     *
     * @return void
     */
    public static function setPostMetaBoxes() {
        add_action('admin_init', 'customize_meta_boxes');

        function customize_meta_boxes() {
            /* Removes meta boxes from Posts */
            remove_meta_box('postcustom', 'post', 'normal');
            remove_meta_box('trackbacksdiv', 'post', 'normal');
            //remove_meta_box('commentstatusdiv', 'post', 'normal');
            //remove_meta_box('commentsdiv', 'post', 'normal');
            //remove_meta_box('tagsdiv-post_tag', 'post', 'normal');
            remove_meta_box('postexcerpt', 'post', 'normal');
            remove_meta_box('formatdiv', 'post', 'normal');
            /* Removes meta boxes from pages */
            //remove_meta_box('postcustom', 'page', 'normal');
            remove_meta_box('trackbacksdiv', 'page', 'normal');
            remove_meta_box('commentstatusdiv', 'page', 'normal');
            remove_meta_box('commentsdiv', 'page', 'normal');
        }
    }

    /**
     * Hide or Show columns in post browsing
     *
     * @return void
     */
    public static function setPostAndPageColumns() {
        add_filter('manage_posts_columns', 'custom_post_columns');
        add_filter('manage_pages_columns', 'custom_pages_columns');

        function custom_post_columns($defaults) {
            unset($defaults['comments']);
            return $defaults;
        }

        function custom_pages_columns($defaults) {
            unset($defaults['comments']);
            return $defaults;
        }
    }

    /**
     * Set a custom logo 16x16 on top admin area
     *
     * @return void
     */
    public static function setCustomLogo() {
        add_action('admin_head', 'customLogo');

        function customLogo() {
            ?>
        <style type="text/css">
            #header-logo {
                background: url(<?php echo kBNMExtendsBase64AdminLogo ?>) no-repeat !important;
            }
        </style>
        <?php

        }
    }

    /**
     * Set admin footer
     *
     * @return void
     */
    public static function setAdminFooter() {
        add_filter('admin_footer_text', 'modify_footer_admin');

        function modify_footer_admin() {
            ?>
        Created by <a href="http://www.saidmade.com">Saidmade</a>
        <?php

        }
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Commodity
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Demo
     *
     * @return void
     */
    function addWidgetMonitor() {
        function systemPHPErrorsWidget() {
            $logfile            = ('../wp-content/debug.log'); // Enter the server path to your logs file here
            $displayErrorsLimit = 100; // The maximum number of errors to display in the widget
            $errorLengthLimit   = 2300; // The maximum number of characters to display for each error
            $fileCleared        = false;
            $userCanClearLog    = current_user_can('manage_options');
            // Clear file?
            if ($userCanClearLog && isset($_GET["system-php-errors"]) && $_GET["system-php-errors"] == "clear") {
                $handle = fopen($logfile, "w");
                fclose($handle);
                $fileCleared = true;
            }
            // Read file
            if (file_exists($logfile)) {
                $errors = file($logfile);
                $errors = array_reverse($errors);
                if ($fileCleared) {
                    echo '<p><em>File cleared.</em></p>';
                }
                if ($errors) {
                    echo '<p>' . count($errors) . ' error';
                    if ($errors != 1) {
                        echo 's';
                    }
                    echo '.';
                    if ($userCanClearLog) {
                        echo' [ <b><a href="' . get_bloginfo('url') . '/wp-admin/?system-php-errors=clear" onclick="return confirm(\'Are you sure?\');">CLEAR LOG FILE</a></b> ]';
                    }
                    echo '</p>';
                    $filter = '';
                    if (isset($_POST['wpCleanFixFilter']) && $_POST['wpCleanFixFilter'] != '') {
                        $filter = $_POST['wpCleanFixFilter'];
                    }
                    echo '<div id="system-php-errors" style="height:250px;overflow:scroll;padding:2px;background-color:#333;border:1px solid #ccc;">';
                    echo '<ol style="padding:0;margin:0;">';
                    $i = 0;
                    foreach ($errors as $error) {
                        if ($filter != '' && stripos($error, $filter) === false) {
                            continue;
                        }
                        echo '<li style="padding:2px 4px 6px;border-bottom:1px solid #666;font-family:Monaco;color:#fa0">';
                        $errorOutput = preg_replace('/\[([^\]]+)\]/', '<b>[$1]</b>', $error, 1);
                        if ($filter != '') {
                            $errorOutput = str_ireplace($filter, '<span style="color:#fff;font-weight:bold;text-shadow:0 0 2px #fff;margin:0 3px">' . $filter . '</span>', $errorOutput);
                        }
                        if (strlen($errorOutput) > $errorLengthLimit) {
                            echo substr($errorOutput, 0, $errorLengthLimit) . ' [...]';
                        } else {
                            echo $errorOutput;
                        }
                        echo '</li>';
                        $i++;
                        if ($i > $displayErrorsLimit) {
                            echo'<li style="padding:2px;border-bottom:2px solid #ccc;"><em>More than ' . $displayErrorsLimit . ' errors in log...</em></li>';
                            break;
                        }
                    }
                    echo '</ol></div>';
                    ?>
                <form style="margin:8px 0 0 0" name="wpCleanFixMonitor" method="post">
                    <input style="width:80%;background:#444;color:#fa0;padding:6px 2px" type="text"
                           name="wpCleanFixFilter" value="<?php echo $filter ?>"/>
                    <button>Filter</button>
                </form>
                <?php

                } else {
                    echo '<p>No errors currently logged.</p>';
                }
            } else {
                echo '<p><em>There was a problem reading the error log file.</em></p>';
            }
        }

        // Add widgets
        function monitorDashboardWidgets() {
            wp_add_dashboard_widget('systemMonitor', 'PHP errors', 'systemPHPErrorsWidget');
        }

        add_action('wp_dashboard_setup', 'monitorDashboardWidgets');
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Plugin page Table List integration
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Add links on installed plugin list
     */
    function plugin_row_meta($links, $file) {
        if ($file == plugin_basename(kBNMExtends__FILE__)) {
            $links[] = '<strong style="color:#fa0">' . __('For more info and plugins visit', 'bnm-context') . ' <a href="http://www.saidmade.com">Saidmade</a></strong>';
        }
        return $links;
    }

    /**
     * Add setting to plugin list
     *
     * @param array $links
     *
     * @return array
     */
    function plugin_action_links($links) {
        $settings_link = '<a href="index.php">' . __('Settings') . '</a>';
        array_unshift($links, $settings_link);
        return $links;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Menu Item
    // -----------------------------------------------------------------------------------------------------------------

    public static function menuItemOptions() {

        /* Show */
        BNMExtendsOptionViewController::display();
    }

}
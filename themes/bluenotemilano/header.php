<!DOCTYPE html>

<!--[if lt IE 7 ]>
<html class="ie ie6 no-js" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 7 ]>
<html class="ie ie7 no-js" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 8 ]>
<html class="ie ie8 no-js" <?php language_attributes(); ?>> <![endif]-->
<!--[if IE 9 ]>
<html class="ie ie9 no-js" <?php language_attributes(); ?>> <![endif]-->
<!--[if gt IE 9]><!--><html class="no-js" <?php language_attributes(); ?>><!--<![endif]-->
<!-- the "no-js" class is for Modernizr. -->

<head>
    <meta charset="<?php bloginfo('charset'); ?>">

    <!-- Always force latest IE rendering engine (even in intranet) & Chrome Frame -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

    <!--[if lte IE 8]>
    <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <?php if (is_search()) { ?>
        <meta name="robots" content="noindex, nofollow"/>
    <?php } ?>

    <title><?php wp_title(''); ?></title>

    <?php
    // -----------------------------------------------------------------------------------------------------------------
    // iPhone/iPAd Mobile Device
    // -----------------------------------------------------------------------------------------------------------------
    if (strpos($_SERVER['HTTP_USER_AGENT'], 'iPad') || strpos($_SERVER['HTTP_USER_AGENT'], 'iPhone')) :
    ?>
    <meta name="viewport" content="width=1024"/>
    <?php endif; ?>

    <!--  Mobile Viewport meta tag
         j.mp/mobileviewport & davidbcalhoun.com/2010/viewport-metatag
         device-width : Occupy full width of the screen in its current orientation
         initial-scale = 1.0 retains dimensions instead of zooming out if page height > device height
         maximum-scale = 1.0 retains dimensions instead of zooming in if page width < device width -->
    <!-- Uncomment to use; use thoughtfully!
     <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
     -->

    <!-- <link rel="shortcut icon" href="<?php bloginfo('template_directory'); ?>/images/favicon.ico"> -->
    <!-- This is the traditional favicon.
          - size: 16x16 or 32x32
          - transparency is OK
          - see wikipedia for info on browser support: http://mky.be/favicon/ -->

    <link rel="apple-touch-icon" href="<?php bloginfo('template_directory'); ?>/images/apple-touch-icon.png">
    <!-- The is the icon for iOS's Web Clip.
          - size: 57x57 for older iPhones, 72x72 for iPads, 114x114 for iPhone4's retina display (IMHO, just go ahead and use the biggest one)
          - To prevent iOS from applying its styles to the icon name it thusly: apple-touch-icon-precomposed.png
          - Transparency is not recommended (iOS will put a black BG behind the icon) -->

    <!-- CSS: screen, mobile & print are all in the same file -->
    <link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>?<?php echo time() ?>">

    <!-- all our JS is at the bottom of the page, except for Modernizr. -->
    <script src="<?php bloginfo('template_directory'); ?>/js/modernizr-1.7.min.js"></script>

    <script type="text/javascript">
        if ( !window.console ) {
            (function () {
                var names = ["log", "debug", "info", "warn", "error", "assert", "dir", "dirxml",
                    "group", "groupEnd", "time", "timeEnd", "count", "trace", "profile", "profileEnd"];
                window.console = {};
                for ( var i = 0; i < names.length; ++i ) {
                    window.console[names[i]] = function () {
                    };
                }
            }());
        }
    </script>

    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>"/>

    <?php
    if (is_singular()) {
        wp_enqueue_script('comment-reply');
    } ?>

    <?php wp_head(); ?>

</head>

<body <?php body_class(); ?>>

<div id="topAdv" class="<?php echo (BNMExtendsOptions::leaderboard() == 'n') ? 'hidden' : '' ?>">
    <?php
    // -----------------------------------------------------------------------------------------------------------------
    // WP Bannerize
    // -----------------------------------------------------------------------------------------------------------------
    if ( function_exists( 'wp_bannerize' ) ) {
        wp_bannerize( 'group=header&random=1&limit=1' );
    }

    ?>
</div>


<header id="header">
    <div class="page-wrap">

        <div id="flags_language_selector">
            <?php if ( function_exists( 'language_selector_flags' ) ) {
            language_selector_flags();
        } ?></div>

        <?php if ( !is_user_logged_in() ) : ?>

        <div class="bnm-top-right-box" id="bnm-login-request-box">
            <!-- no log in -->
            <form class="bnm-login-signup" method="post" action="">
                <input id="buttonOpenLogin" class="button blue" type="button" value="<?php _e( 'Login', 'bnm' ) ?>"/>
                <input onclick="document.location = '<?php echo BNMExtends::pagePermalinkWithSlug( 'registrazione' ) ?>'"
                       id="buttonSignup"
                       class="button orange right"
                       type="button"
                       value="<?php _e( 'Register', 'bnm' ) ?>"/>
            </form>
        </div>
        <!-- after for zindex -->
        <div class="bnm-top-right-box" id="bnm-login-box" style="top:-400px">
            <form class="bnm-login" method="post" action="">
                <input type="hidden" name="action" value="do_login"/>
                <input type="text" name="username" placeholder="email" class="wpdk-form-input"/>
                <input type="password" name="password" placeholder="password" class="wpdk-form-input"/>
                <input type="submit" id="buttonSignin" class="button blue" value="<?php _e( 'Enter', 'bnm' ) ?>"/>
                <input type="button" id="buttonCancel" class="right button orange"
                       value="<?php _e( 'Cancel', 'bnm' ) ?>"/>

                <p class="aligncenter"><input type="checkbox"
                                              name="remember"
                                              id="remember"
                                              value="remember-login"/> <?php _e( 'Remember me', 'bnm' ); ?></p>
            </form>
            <div class="sep"></div>
            <form class="bnm-login-recovery" method="get" action="<?php echo site_url() ?>/wp-login.php">
                <input type="hidden" name="action" value="lostpassword"/>

                <p><?php _e( 'Forgot your password?', 'bnm' ) ?></p>
                <input type="submit"
                       id="buttonLostPassword"
                       class="button green right"
                       value="<?php _e( 'Retrieve', 'bnm' ) ?>"/>
            </form>
        </div>

        <?php else : ?>

        <div class="bnm-top-right-box" id="bnm-login-in">
            <!--            <div class="bnm-logon-avatar">--><?php //echo WPDKUser::gravatar() ?><!--</div>-->
            <p><?php _e( 'Hello,', 'bnm' ); ?> <?php echo WPDKUser::displayName() ?>

            <form class="bnm-login-logout" method="post" action="<?php echo wp_logout_url( get_bloginfo( 'url' ) ); ?>">
                <a class="button orange left" href="<?php echo BNMExtends::pagePermalinkWithSlug( 'profilo' ) ?>"><?php _e( 'Your profile', 'bnm' ); ?></a></p>
                <input type="submit" id="bnm-button-logout" class="button blue" value="<?php _e( 'Logout', 'bnm' ) ?>"/>

                <?php /*
                <?php if ( !WPSmartShopCart::isCartEmpty() ) : ?>
                <?php $total = WPSmartShopCart::productNumbers(); ?>
                <p class="bnm-has-product-in-cart">
                    <a href="<?php echo WPSmartShopOptions::checkoutPermalink() ?>">
                        <img alt="<?php _e( 'Checkout', 'bnm' ) ?>"
                             title="<?php _e( 'Checkout', 'bnm' ) ?>"
                             src="data:image/png;base64,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"/>
                    </a>
                </p>
                <?php endif; ?>
                */ ?>

            </form>
        </div>

        <?php endif; ?>

        <h1><a href="<?php echo get_option( 'home' ); ?>"><?php bloginfo( 'name' ); ?></a></h1>

        <a id="rmc-logo"
           href="<?php echo BNMExtends::pagePermalinkWithSlug('monte-carlo-nights') ?>"><img src="<?php bloginfo( 'template_directory' ) ?>/images/rmc.png"/></a>

        <div class="description"><?php bloginfo( 'description' ); ?></div>
        <div class="socialButtons">
            <img usemap="#Map" src="<?php bloginfo( 'template_directory' ) ?>/images/socialButtons.png" border="0"/>
            <map name="Map" id="Map">
                <area title="Facebook"
                      shape="rect"
                      coords="0,0,41,41"
                      href="http://www.facebook.com/bluenotemilano"
                      target="_blank"/>
                <area title="Twitter"
                      shape="rect"
                      coords="43,0,82,41"
                      href="http://twitter.com/#!/Bluenotemilano"
                      target="_blank"/>
                <area title="YouTube"
                      shape="rect"
                      coords="85,0,124,41"
                      href="http://www.youtube.com/user/Bluenotemilano"
                      target="_blank"/>
                <area title="FlickR"
                      shape="rect"
                      coords="127,0,166,41"
                      href="http://www.flickr.com/photos/bluenotemilano"
                      target="_blank"/>
                <area title="Feed RSS"
                      shape="rect"
                      coords="168,0,207,41"
                      href="http://www.bluenotemilano.com/feed/ "
                      target="_blank"/>
                <area title="FourSquare"
                      shape="rect"
                      coords="211,0,250,41"
                      href="https://foursquare.com/bluenotemilano"
                      target="_blank"/>
            </map>
        </div>
        <div id="search"><?php get_search_form() ?></div>
    </div>


</header>

<nav id="access" role="navigation" class="page-wrap box blue">
    <?php wp_nav_menu(array('theme_location' => 'primary')); ?>
</nav>

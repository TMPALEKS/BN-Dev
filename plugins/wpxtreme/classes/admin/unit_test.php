<?php
/**
 * $DESCRIPTION
 *
 * @package         ${PACKAGE}
 * @subpackage      ${NAME}
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright       Copyright (c) 2012 wpXtreme, Inc.
 * @link            http://wpxtre.me
 * @created         10/05/12
 * @version         1.0.0
 *
 */
?>
<pre class="monitor">
    <?php
    global $wpdb;

    // h18s6ms04AF7

    function output( $v ) {
        ?><pre><?php var_dump( $v) ?></pre></hr><?php
    }

    $wpdb->show_errors();
    error_reporting(E_ALL);
    ini_set('display_errors', true );
    $r = '';

    $r = get_default_post_to_edit();

    output( $r );


?>
</pre>
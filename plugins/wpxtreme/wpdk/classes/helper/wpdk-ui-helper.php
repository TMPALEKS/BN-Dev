<?php
/**
 * @description
 *
 * @package            WPDK
 * @subpackage         WPDKUI
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            25/05/12
 * @version            1.0.0
 *
 * @filename           wpdk-ui-helper
 *
 */

class WPDKUI {

    // -----------------------------------------------------------------------------------------------------------------
    // Message
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Helper per la creazione di un messaggio di risposta updated
     *
     * @static
     *
     * @param string       $message
     * @param bool         $highlight
     * @param string       $class
     * @param bool         $echo
     *
     * @return string
     */
    public static function message( $message, $highlight = false, $class = '', $echo = true, $dismiss = false ) {
        $highlight = $highlight ? 'highlight' : '';

        $dismiss = $dismiss ? '<a class="close" data-dismiss="alert" href="#">&times;</a>' : '';
        $updated = empty( $dismiss ) ? 'updated' : '';

        $html = <<< HTML
<div class=" {$class} fade in {$highlight} {$updated}">
    {$dismiss}
    <p>{$message}</p>
</div>
HTML;
        if ( $echo ) {
            echo $html;
        } else {
            return $html;
        }
    }

    /**
     * Helper per la creazione di un messaggio di errore
     *
     * @static
     *
     * @param string  $message
     * @param bool    $highlight
     * @param string  $class
     * @param bool    $echo
     *
     * @return string
     */
    public static function error( $message, $highlight = false, $class = '', $echo = true ) {
        $highlight = $highlight ? 'highlight' : '';

        $html = <<< HTML
<div class="error fade {$class} {$highlight}">
    <p>{$message}</p>
</div>
HTML;
        if ( $echo ) {
            echo $html;
        } else {
            return $html;
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // UI
    // -----------------------------------------------------------------------------------------------------------------

    /* @todo Inserire echo e inject */
    public static function buttonsUpdateReset() {
        ?>
    <p>
        <input type="submit" class="button-primary" value="<?php _e( 'Update', 'wp-smartshop' ) ?>"/>
        <input type="submit"
               class="button-secondary alignright"
               name="resetToDefault"
               value="<?php _e( 'Reset to default', 'wp-smartshop' ) ?>"/>
    </p>
    <?php
    }


    /* @todo Inserire echo e inject */
    public static function labelTruncate( $value, $size = 'small' ) {
        $html = <<< HTML
    <div class="wpdk-ui-truncate wpdk-ui-truncate-size_{$size}" title="{$value}">
        <span>{$value}</span>
    </div>
HTML;
        return $html;
    }

    /* @todo Crea un badged */
    public static function badged( $count = 0, $classes = '' ) {
        $result  = '';
        $classes = !empty( $classes ) ? ' ' . $classes : '';
        if ( !empty( $count ) ) {
            $result = sprintf( '<span class="update-plugins count-%s%s"><span class="plugin-count">%s</span></span>', $count, $classes, number_format_i18n( $count ) );
        } else {
            /* restituisco comunque un placeholder comodo per poter inserire onfly via javascript un badged. */
            $result = sprintf( '<span class="%s"></span>', $classes );
        }
        return $result;
    }

    /* @todo Doc */
    public static function view( $id, $title, $icon_class, $content ) {
        $html = <<< HTML
<div class="wrap">
    <div class="{$icon_class}"></div>
    <h2>{$title}</h2>
    <div class="wpdk-border-container {$id}">
        {$content}
    </div>
</div>
HTML;
        return $html;
    }

    /* @todo Doc */
    public static function credits( $credits ) {
        $html = '';
        foreach( $credits as $key => $value ) {
            $html .= sprintf( '<div class="wpdk-credits wpdk-credits-%s clearfix">', sanitize_title( $key ) );
            $html .= sprintf( '<h3>%s</h3>', $key );
            $html .= '<ul class="clearfix">';
            foreach( $value as $info ) {
                $html .= sprintf( '<li class="wpdk-tooltip clearfix" title="%s" data-placement="top"><img src="http://www.gravatar.com/avatar/%s?s=32&d=wavatar" /><a target="_blank" href="%s">%s</a></li>', $info['site'], md5( $info['mail'] ), $info['site'], $info['name'] );
            }
            $html .= '</ul></div>';
        }
        return $html;
    }

}

<?php
/**
 * Questa classe propone una serie di metodi per la conversioni delle date e orari da/a MySQL all'ambiente di sviluppo
 * sia verso i campi input che i vari plugin jQuery DatePicker e DateTimePicker. Qui, quindi, ci sono tutti i metodi di
 * conversione delle date da/a MySQL da/a datePicker vari da/a campi testo con anche i relativi protocolli di
 * localizzazione dd/mm/yyyy o mm/dd/yyyy
 *
 * @package            WPDK (WordPress Development Kit)
 * @subpackage         WPDKDateTime
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (C)2011 wpXtreme, Inc.
 * @created            15/12/11
 * @version            1.0
 *
 */

define('MYSQL_DATE', 'Y-m-d');
define('MYSQL_DATE_TIME', 'Y-m-d H:i:s');

class WPDKDateTime {

    // -----------------------------------------------------------------------------------------------------------------
    // Date
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Converte una data (comprensiva o meno dell'orario), da un formato ad un altro
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKDateTime
     * @since      1.0.0
     *
     * @static
     *
     * @param string $date Data da convertire del formato descritto da $from
     * @param string $from Default YmdHi
     * @param string $to   Default m/d/Y H:i
     *
     * @return string
     *             Data convertita
     */
    public static function formatFromFormat( $date, $from = 'YmdHi', $to = 'm/d/Y H:i' ) {
        if ( !empty( $date ) ) {
            try {
                $dateObject = DateTime::createFromFormat( $from, $date );
                if ( $dateObject === false ) {
                    throw new Exception( "WPDKDateTime::formatFromFormat($date, $from, $to) - Error while create data object" );
                }
                return $dateObject->format( $to );
            } catch (Exception $e) {
                error_log( var_export( $e->getTraceAsString(), true ) );
                trigger_error( $e->getMessage(), E_USER_ERROR );
            }
        }
        return $date;
    }

    /**
     * Restituisce un timestamp a partire da una stringa formattata
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKDateTime
     * @since      1.0.0
     *
     * @static
     *
     * @param $format
     * @param $date
     *
     * @return int
     */
    public static function makeTimeFrom( $format, $date ) {
        /* Get date in m/d/Y H:i */
        $sanitize_date = self::formatFromFormat( $date, $format );
        $split         = explode( ' ', $sanitize_date );
        $date_part     = explode( '/', $split[0] );
        $time_part     = explode( ':', $split[1] );
        $time          = mktime( $time_part[0], $time_part[1], 0, $date_part[0], $date_part[1], $date_part[2] );
        return $time;
    }

    /**
     * Restituisce una data di scadenza
     *
     * @static
     *
     * @param string $date          Data di partenza in formato mySQL YYYY-MM-DD HH:MM:SS
     * @param int    $duration      Durata
     * @param string $duration_type Tipo: days, minutes, months
     *
     * @return int Ritorna il timestamp che rappresenta la data di scadenza
     */
    public static function expirationDate( $date, $duration, $duration_type ) {
        $expiredate = strtotime( "+{$duration} {$duration_type}", strtotime( $date ) );
        return $expiredate;
    }

    /**
     * Restituisce il numero di giorni che mancano o che sono trascorsi rispetto ad un data
     *
     * @static
     *
     * @param int $date Timestamp della data su cui effettuare il calcolo rispetto ad 'ora'
     *
     * @return float Restituisce il numero di giorni che mancano ($date > now) o che sono trascorsi ($date <= now) rispetto a $date
     */
    public static function daysToDate( $date ) {
        $diff = $date - time();
        $days = round( $diff / ( 60 * 60 * 24 ) );
        return $days;
    }

    /**
     * Utility per porre l'orario su una nuova riga
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKDateTime
     * @since      1.0.0
     *
     * @static
     *
     * @param string $datetime Data e ora formattati in modo che lo spazio delimiti data e ora
     *
     * @return string
     */
    public static function timeNewLine( $datetime ) {
        return str_replace( ' ', '<br/>', $datetime );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // has/is zone
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Shorthand
     * Restituisce true se la data in timestamp $expiration è uguale o precedente a oggi
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKDateTime
     * @since      1.0.0
     *
     * @static
     *
     * @param int $exipration Timestamp della data che si vuole controllare
     *
     * @return bool Restituisce true se la data in timestamp $expiration è uguale o precedente a oggi,
     *             altrimenti false se ancora valida
     */
    public static function isExpired( $exipration ) {
        return ( ( $exipration - time() ) <= 0 );
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Alias
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Alias di formatFromFormat per convertire una data qualsiasi nel formato riconosciuto da MySQL
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKDateTime
     * @since      1.0.0
     *
     * @static
     *
     * @param string $date Data
     * @param string $from Formato data di input
     *
     * @return string Data nel formato YYYY-MM-DD
     */
    public static function date2MySql( $date, $from = 'm/d/Y' ) {
        return self::formatFromFormat( $date, $from, 'Y-m-d' );
    }

    /**
     * Formatta una data e time per essere inserita in mySQL
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKDateTime
     * @since      1.0.0
     *
     * @static
     *
     * @param string $date
     * @param string $from
     *
     * @return string
     */
    public static function dateTime2MySql( $date, $from = 'm/d/Y H:i:s' ) {
        if ( !empty( $date ) ) {
            return self::formatFromFormat( $date, $from, 'Y-m-d H:i:s' );
        }
        return $date;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Time
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Elimina i secondi da un time
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKDateTime
     * @since      1.0.0
     *
     * @static
     *
     * @param $time
     *
     * @return string
     */
    public static function stripSecondsFromTime( $time ) {
        return substr( $time, 0, 5 ); // 00:00
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Time Calculation
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Returns number of days to start of this week.
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKDateTime
     * @since      1.0.0
     * @author     =stid=
     *
     * @static
     *
     * @param string $date
     *
     * @param string $first_day
     *
     * @return int $date
     */
    public static function daysToWeekStart( $date, $first_day = 'monday' ) {

        $week_days = array(
            'monday'                        => 0,
            'tuesday'                       => 1,
            'wednesday'                     => 2,
            'thursday'                      => 3,
            'friday'                        => 4,
            'saturday'                      => 5,
            'sunday'                        => 6

        );

        $start_day_number   = $week_days[$first_day];
        $wday               = $date->format( "w" );
        $current_day_number = ( $wday != 0 ? $wday - 1 : 6 );
        return WPDKMath::rModulus( ( $current_day_number - $start_day_number ), 7 );

    }

    /**
     * Returns number of days to start of this week.
     *
     * @package    WordPress Development Kit
     * @subpackage WPDKDateTime
     * @since      1.0.0
     * @author     =stid=
     *
     * @static
     *
     * @param string $date
     *
     * @param string $first_day
     *
     * @return     $date
     */
    public static function beginningOfWeek( $date, $first_day = 'monday' ) {
        $days_to_start = WPDKDateTime::daysToWeekStart( $date, $first_day );
        return ( $date - $days_to_start );
    }


    public static function compareDate() {}
    public static function compareDatetime() {}

    public static function isInRangeDatetime( $date_start, $date_expire, $format = 'YmdHis', $timestamp = false ) {
        if ( !empty( $date_start ) || !empty( $date_expire ) ) {

            /* Get now in timestamp */
            $now = mktime();

            /* Le date sono in chiaro o anch'esse in timestamp? */
            if( !$timestamp ) {
                $date_start  = !empty( $date_start ) ? WPDKDateTime::makeTimeFrom( $format, $date_start ) : $now;
                $date_expire = !empty( $date_expire ) ? WPDKDateTime::makeTimeFrom( $format, $date_expire ) : $now;
            } else {
                $date_start  = !empty( $date_start ) ? $date_start : $now;
                $date_expire = !empty( $date_expire ) ? $date_expire : $now;
            }

            /* Verifico il range. */
            if ( $now >= $date_start && $now <= $date_expire ) {
                return true;
            }
        }
        return false;
    }


}

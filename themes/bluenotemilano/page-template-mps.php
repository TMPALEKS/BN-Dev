<?php
/**
 * Template Name: Gateway MPS
 *
 * Pagina per superare ostacolo imposto dal gateway MPS quando chiama una pagine per leggere il redirect
 *
 * @package         Blue Note Milano HTML5 Theme
 * @author          =undo= <g.fazioli@saidmade.com>
 * @copyright       Copyright © 2012 Saidmade Srl
 *
 * 4539990000000012
 *
 */

$PayID         = $_POST["paymentid"];
$TransID       = $_POST["tranid"];
$ResCode       = $_POST["result"];
$AutCode       = $_POST["auth"];
$PosDate       = $_POST["postdate"];
$TrckID        = $_POST["trackid"];
$language_code = $_POST["udf1"];
$UD2           = $_POST["udf2"];
$UD3           = $_POST["udf3"];
$UD4           = $_POST["udf4"];
$UD5           = $_POST["udf5"];

if ( defined( 'ICL_LANGUAGE_CODE' ) ) {
    global $sitepress;
    $sitepress->switch_lang( $language_code, false );
}

/* Pagina ricevuta come da impostazioni */
$receipt_slug      = WPXSmartShop::settings()->receipt_permalink();
$receipt_permalink = wpdk_permalink_page_with_slug( $receipt_slug, kWPSmartShopStorePagePostTypeKey );

/* Nell URL seguente inserire l'indirizzo corretto del proprio server */
$ReceiptURL =
    "REDIRECT=$receipt_permalink?PaymentID=" . $PayID . "&TransID=" . $TransID . "&TrackID=" . $TrckID . "&postdate=" .
        $PosDate . "&resultcode=" . $ResCode . "&auth=" . $AutCode;

echo $ReceiptURL;
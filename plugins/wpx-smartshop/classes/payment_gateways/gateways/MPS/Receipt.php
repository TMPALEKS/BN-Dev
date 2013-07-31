<?php
/// @cond private
/**
 * Questo file è stato inserito in quanto sono stati riscontrati dei problemi di comunicazione con il gateway MPS.
 *
 * @todo               Cercare di eliminare questo file e far rientrare il tutto nello standard WordPress:
 *                     probabilmente il problema è dovuto al fatto che usando l'indirizzo permalink viene emesso tutto il tema WordPress
 *                     quando invece il gateway si aspetto una semplice echo con "REDIRECT" vedi a fondo file.
 *
 * @todo               Rivedere naming dei parametri che vengono passati in GET alla nostra pagina
 *
 * @package            MPS
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (C) 2012 wpXtreme, Inc.
 *
 */

$PayID   = $_POST["paymentid"];
$TransID = $_POST["tranid"];
$ResCode = $_POST["result"];
$AutCode = $_POST["auth"];
$PosDate = $_POST["postdate"];
$TrckID  = $_POST["trackid"];
$UD1     = $_POST["udf1"];
$UD2     = $_POST["udf2"];
$UD3     = $_POST["udf3"];
$UD4     = $_POST["udf4"];
$UD5     = $_POST["udf5"];

// Nell URL seguente inserire l'indirizzo corretto del proprio server
$ReceiptURL = "REDIRECT=http://www.bluenotemilano.org/negozio/ricevuta/?PaymentID=" .
        $PayID . "&TransID=" . $TransID . "&TrackID=" . $TrckID . "&postdate=" . $PosDate . "&resultcode=" . $ResCode .
        "&auth=" . $AutCode;

echo $ReceiptURL;
/// @endcond
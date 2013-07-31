/**
 * Estende la classe String per eseguire una serie di test
 *
 * @package         WPDK (WordPress Development Kit)
 * @subpackage      String+Test
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright       Copyright (C)2011 wpXtreme, Inc.
 * @created         09/12/11
 * @version         1.0
 *
 */

/**
 * Lista di regular expression da applicare all0interno dei vari metodi di controllo
 */
String.prototype._filters =  {
    alphanumeric    : /^[a-zA-Z0-9 ]*$/,
    currency        : /^\s*(\+|-)?((\d+(\.\d\d)?)|(\.\d\d))\s*$/,
    decimal         : /^\s*(\+|-)?((\d+(\.\d+)?)|(\.\d+))\s*$/,
    digid           : /^[0-9]*$/,
    integer         : /^\s*(\+|-)?\d+\s*$/,
    lowercase       : /^([a-z])*$/,
    email           : /^([\w-]+(?:\.[\w-]+)*)@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$/i,
    emaila          : /^\s*[\w\-\+_]+(\.[\w\-\+_]+)*\@[\w\-\+_]+\.[\w\-\+_]+(\.[\w\-\+_]+)*\s*$/,
    emailb          : /^([a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4})*$/,
    unsignedinteger : /^\s*\d+\s*$/
};

// Ritorna True se una stringa è un'email
String.prototype.isEmail = function () {
    return String.prototype._filters.email.test(this);
};

// Ritorna True se è un numerico 0-9, senza spazi ne altri caratteri
String.prototype.isDigid = function() {
    return String.prototype._filters.digid.test(this);
}
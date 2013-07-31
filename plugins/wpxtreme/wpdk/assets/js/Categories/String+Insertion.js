/**
 * Estende la classe String
 *
 * @package         WPDK (WordPress Development Kit)
 * @subpackage      String+Insertion
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright       Copyright (C)2011 wpXtreme, Inc.
 * @created         09/12/11
 * @version         1.0
 *
 */

String.prototype.insertAt = function (loc, strChunk) {
    return (this.valueOf().substr(0, loc)) + strChunk + (this.valueOf().substr(loc))
}

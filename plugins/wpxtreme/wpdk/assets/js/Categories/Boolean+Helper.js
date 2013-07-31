/**
 * Estende la classse Boolean
 *
 * @package         WPDK (WordPress Development Kit)
 * @subpackage      Boolean+Helper
 * @author          =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright       Copyright (C)2011 wpXtreme, Inc.
 * @created         09/12/11
 * @version         1.0
 *
 */

Boolean.prototype.XOR = function (bool2) {
    var bool1 = this.valueOf();
    return (bool1 == true && bool2 == false) || (bool2 == true && bool1 == false);
    //return (bool1 && !bool2) || (bool2 && !bool1);
}
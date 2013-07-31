<?php
/**
 * @class              WPDKTableView
 * @description        Gestisce una lista generica di elementi, simile ad un table view su iPhone.
 *
 * @package            WPDK (WordPress Development Kit)
 * @subpackage         ui
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (C)2012 wpXtreme, Inc.
 * @created            03/01/12
 * @version            1.0
 *
 * @filename           wpdk-tableview
 *
 */

//if (!class_exists('WP_List_Table')) {
//    require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
//}

class WPDKTableView {

    var $_items;
    var $_columns;
    var $_args;
    var $_paged;
    var $_itemsPerPage;
    var $_totalItems;

    function __construct( $args = array() ) {
        $default             = array(
            'name'         => '',
            'title'        => '',
            'filter'       => '',
            'paged'        => 1,
            'itemsPerPage' => 10,
            'ajaxHook'     => ''
        );
        $args                = wp_parse_args( $args, $default );
        $this->_args         = $args;
        $this->_paged        = $args['paged'];
        $this->_itemsPerPage = $args['itemsPerPage'];
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Get/Set pseudo-properties
    // -----------------------------------------------------------------------------------------------------------------

    function paged($value = null) {
        if(is_null($value)) {
            return $this->_paged;
        }
        $this->_paged = $value;
        $this->prepareItems();
    }

    function totalItems($value = null) {
        if(is_null($value)) {
            return $this->_totalItems;
        }
        $this->_totalItems = $value;
    }

    function itemsPerPage($value = null) {
        if(is_null($value)) {
            return $this->_itemsPerPage;
        }
        $this->_itemsPerPage = $value;
    }

    function columns($value = null) {
        if(is_null($value)) {
            return $this->_columns;
        }
        $this->_columns = $value;
    }

    function items($value = null) {
        if(is_null($value)) {
            return $this->_items;
        }
        $this->_items = $value;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // To overwrite
    // -----------------------------------------------------------------------------------------------------------------
    function prepareItems() {
        die('function WPDKTableView::prepareItems() must be over-ridden in a sub-class.');
    }

    function item($item, $column) {
        echo $item;
    }

    function moreItems() {
        $over = $this->_paged * $this->_itemsPerPage;
        if($over < $this->_totalItems) : ?>
        <tr id="" class="wpdk-tableview-moreitems">
            <td><?php $this->moreItemsText() ?></td>
        </tr>
        <?php endif;
    }

    function moreItemsText() {
        _e('More', WPDK_TEXTDOMAIN);
    }

    public function backButton() {}
    public function title() {}
    public function search() {}

    public function viewWillAppear() {}
    public function viewDidAppear() {}

    // -----------------------------------------------------------------------------------------------------------------
    // View
    // -----------------------------------------------------------------------------------------------------------------
    public function view() {
        $this->viewWillAppear();
        ?>
    <div class="wpdk-tableview" id="<?php echo $this->_args['name'] ?>">
        <input type="hidden" id="wpdk-tableview-ajaxhook" value="<?php echo $this->_args['ajaxHook'] ?>" />
        <?php $this->navigationController() ?>
        <table class="wpdk-tableview-table" cellpadding="0" cellspacing="0" width="100%">
            <thead>
                <tr><?php $this->head() ?></tr>
            </thead>
            <tbody>
                <?php $this->body() ?>
            </tbody>
            <tfoot>
                <?php $this->footer() ?>
            </tfoot>
        </table>
    </div>
    <?php
        $this->viewDidAppear();
    }

    function body() {
        foreach($this->_items as $keyitem => $item) : ?>
            <tr id="wpdk-tableview-row_<?php echo $keyitem ?>" class="<?php echo $keyitem ?>">
            <?php foreach($this->_columns as $keycolumn => $column) : ?>
                <td id="wpdk-tableview-item_<?php echo $keycolumn ?>" class="<?php echo $keycolumn ?>"><?php $this->item($item, $keycolumn) ?></td>
            <?php endforeach; ?>
            </tr>
        <?php endforeach;
        $this->moreItems();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Private internal use
    // -----------------------------------------------------------------------------------------------------------------
    private function head() {
        foreach($this->_columns as $key => $column) : ?>
            <th class="<?php echo $key ?>"><?php echo $column ?></th>
        <?php endforeach;
    }


    private function footer() {}

    // -----------------------------------------------------------------------------------------------------------------
    // Navigation Controller
    // -----------------------------------------------------------------------------------------------------------------

    public function navigationController() {
        ?>
    <table class="wpdk-navigationcontroller" cellpadding="0" cellspacing="0" width="100%">
        <tbody>
        <tr>
            <td><?php $this->backButton() ?></td>
            <td><?php $this->title() ?></td>
            <td><?php $this->search() ?></td>
        </tr>
        </tbody>
    </table>
        <?php
    }

}

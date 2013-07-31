<?php
/**
 * @class              WPXSmartShopUsersPicker
 *
 * @description        Gestisce tutte le proprietà e connessioni con le utenze WordPress.
 *
 * @package            wpx SmartShop
 * @subpackage         core
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c)2012 wpXtreme, Inc.
 * @created            28/12/11
 * @version            1.0
 *
 * @filename           wpxss-users-picker
 *
 * @todo               Potrebbe essere spostato sotto una cartella picker/
 *
 */

class WPXSmartShopUsersPicker extends WPDKTableView {

    function __construct( $paged ) {
        parent::__construct(array(
                                 'name'         => 'wpss-userpicker',
                                 'title'        => __('Select an user', WPXSMARTSHOP_TEXTDOMAIN ),
                                 'paged'        => $paged,
                                 'itemsPerPage' => 5,
                                 'ajaxHook'     => 'action_user_more'
                            ));

        $this->columns(array(
                            'name'  => __('Name', WPXSMARTSHOP_TEXTDOMAIN )
                       ));

        $this->prepareItems();
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Prepare Items
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Carica la lista degli utenti, volendo filtrando e paginando
     *
     * @todo Fare paginazione
     */
    function prepareItems() {
        global $role, $usersearch;

        $role = isset( $_REQUEST['role'] ) ? $_REQUEST['role'] : '';

        //        $per_page       = ($this->is_site_users) ? 'site_users_network_per_page' : 'users_per_page';
        //        $users_per_page = $this->get_items_per_page($per_page);

        $offset = ( $this->paged() - 1 ) * $this->itemsPerPage();

        $args = array(
            'number'  => $this->itemsPerPage(),
            'offset'  => $offset,
            'role'    => $role,
            'orderby' => 'display_name',
            'search'  => $usersearch,
            'fields'  => 'all_with_meta'
        );


        if ( '' !== $args['search'] ) {
            $args['search'] = '*' . $args['search'] . '*';
        }

//        if ( $this->is_site_users ) {
//            $args['blog_id'] = $this->site_id;
//        }

        if ( isset( $_REQUEST['orderby'] ) ) {
            $args['orderby'] = $_REQUEST['orderby'];
        }

        if ( isset( $_REQUEST['order'] ) ) {
            $args['order'] = $_REQUEST['order'];
        }

        // Query the user IDs for this page
        $wp_user_search = new WP_User_Query( $args );

        $users             = $wp_user_search->get_results();
        $items             = array();
        $this->_totalItems = $wp_user_search->get_total();

        foreach ( $users as $key => $user ) {
            $items[$key] = $user->display_name . ' (' . $user->user_email . ')';
        }

        $this->items( $items );

        //   		$this->set_pagination_args( array(
        //   			'total_items' => $wp_user_search->get_total(),
        //   			'per_page' => $users_per_page,
        //   		) );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // View
    // -----------------------------------------------------------------------------------------------------------------
    function viewWillAppear() {
        ?><div id="wpss-dialog-userpicker" style="display: none"><?php
    }

    function viewDidAppear() {
        ?></div><?php
    }

}
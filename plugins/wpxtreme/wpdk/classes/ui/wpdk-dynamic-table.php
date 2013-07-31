<?php
/**
 * Gestisce una tabella dinamica utilizzata quando di hanno dei campi input da duplicare.
 * Questa permette di ordinare le righe e di aggiungerne altre in dinamico
 *
 * @package            WPDK (WordPress Development Kit)
 * @subpackage         WPDKDynamicTable
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            27/04/12
 * @version            1.0.0
 *
 */

class WPDKDynamicTable {

    /**
     * Nome colonna dove c'è il + e il -
     */
    const COLUMN_ROW_MANAGE = '_wpdk_dt_column_row_manage';

    /**
     * @var string
     * ID della tabella
     */
    private $_id;

    /**
     * @var string
     * Classe aggiuntiva (o classi separate da spazio) della tabella
     */
    private $_class;

    /**
     * @var array
     * Elenco delle colonne e del tipo di campo da visualizzare
     */
    private $_columns;

    /**
     * @var array
     * Elenco delle valori che devono avere una corrispondenza con le colonne
     *
     */
    private $_items;

    /**
     * @param string $id
     * ID della tabella
     *
     * @param array $columns
     * Elenco delle colonne
     *
     */
    function __construct( $id, $columns, $items ) {

        $this->_columns = $columns;
        $this->_items   = $items;
        $this->_class   = '';

        /* Added dynamic + */
        $this->_columns[self::COLUMN_ROW_MANAGE] = '';
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Get/Set pseudo-properties
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @return string
     */
    function buttonAdd() {
        $label = __('Add', WPXSMARTSHOP_TEXTDOMAIN );
        $title = __( 'Add a new empty row', 'wp-smartshop' );

        $html = <<< HTML
    <input data-placement="left" title="{$title}" title-backup="{$title}" type="button" value="{$label}" class="wpdk-tooltip wpdk-dt-add-row">
HTML;
        return $html;
    }

    /**
     * @return string
     */
    function buttonDelete() {
        $label = __('Delete', WPXSMARTSHOP_TEXTDOMAIN );
        $title = __( 'Delete entire row', 'wp-smartshop' );

        $html = <<< HTML
    <input data-placement="left" title="{$title}" content="{$title}" type="button" value="{$label}" class="wpdk-tooltip wpdk-dt-delete-row">
HTML;
        return $html;
    }

    /**
     * Get/Set l'elenco dei valori da mostrare in tabella
     * @return array
     */
    function items() {
        if ( func_num_args() > 0 ) {
            $this->_items = func_get_arg( 0 );
        } else {
            return $this->_items;
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // To overwrite
    // -----------------------------------------------------------------------------------------------------------------

    // -----------------------------------------------------------------------------------------------------------------
    // View
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * @return string
     */
    function view() {

        $html_thead = $this->thead();
        $html_tbody = $this->tbody();
        $html_tfoot = $this->tfoot();
        $id         = $this->_id;
        $class      = $this->_class;

        $html = <<< HTML
    <table id="{$id}" class="wpdk-dynamic-table {$class}" border="0" cellpadding="0" cellspacing="0">
        {$html_thead}
        {$html_tbody}
        {$html_tfoot}
    </table>
HTML;
        return $html;
    }




    /**
     * @return string
     */
    private function thead() {

        $ths = '';
        foreach( $this->_columns as $key => $column ) {
            if( $key != self::COLUMN_ROW_MANAGE) {
                $ths .= sprintf( '<th class="wpdk-dynamic-table-column-%s">%s</th>', $key, $column['table_title'] );
            } else {
                //$ths .= sprintf( '<th class="%s"></th>', $key );
            }
        }

        $html = <<< HTML
    <thead>
        <tr>
            {$ths}
        </tr>
    </thead>
HTML;
        return $html;
    }




    /**
     * @return string
     */
    private function tbody() {
        $trs = '';

        /* Il primo è sempre display none e usato per la clonazione */
        $trs .= sprintf( '<tr class="wpdk-dt-clone">%s</tr>', $this->tbody_row() );

        if ( !empty( $this->_items ) ) {
            foreach ( $this->_items as $item ) {
                $trs .= sprintf( '<tr>%s</tr>', $this->tbody_row( $item ) );
            }
        }
        $trs .= sprintf( '<tr>%s</tr>', $this->tbody_row() );

        $html = <<< HTML
    <tbody>
        {$trs}
    </tbody>
HTML;
        return $html;
    }

    /**
     * Restituisce l'html di un'intera riga del body, usato per popolare anche gli elementi interni
     *
     * @return string
     */
    private function tbody_row( $item = null) {
        $tds = '';
        foreach ( $this->_columns as $key => $column ) {
            if ( $key != self::COLUMN_ROW_MANAGE ) {

                if ( !is_null( $item ) ) {
                    $column['value'] = $item[$key];
                }

                /* @todo Zozzata da risolvere all'intero di WPDKForm */
                ob_start();
                WPDKForm::htmlItem( $column );
                $field = ob_get_contents();
                ob_end_clean();

                $tds .= sprintf( '<td class="wpdk-dynamic-table-cel-%s">%s</th>', $key, $field );
            } else {
                if ( is_null( $item ) ) {
                    $tds .= sprintf( '<td class="%s">%s<span class="wpdk-dt-clone-delete">%s</span></th>', $key, $this->buttonAdd(), $this->buttonDelete() );
                } else {
                    $tds .= sprintf( '<td class="%s">%s</th>', $key, $this->buttonDelete() );
                }

            }
        }
        return $tds;
    }



    /**
     * @return string
     */
    private function tfoot() {

        $tds = '';
        foreach ( $this->_columns as $key => $column ) {
            if ( $key != self::COLUMN_ROW_MANAGE ) {
                $tds .= sprintf( '<td class="wpdk-dynamic-table-cel-%s"></th>', $key );
            } else {
            }
        }
        $html = <<< HTML
    <tfoot>
        <tr>
            {$tds}
        </tr>
    </tfoot>
HTML;
        return $html;
    }

}

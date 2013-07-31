<?php
/**
 * Gestione della membership di un prodotto, ovvero di un prodotto di tipo (anche) membership
 *
 * @package            wpx SmartShop
 * @subpackage         WPSmartShopProductMembership
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (C)2012 wpXtreme, Inc.
 * @created            10/01/12
 * @version            1.0
 *
 */

class WPSmartShopProductMembership {

	// -----------------------------------------------------------------------------------------------------------------
	// Static values
	// -----------------------------------------------------------------------------------------------------------------

	/**
	 * Restituisce i campi nello standard SDF
	 *
	 * @package    wpx SmartShop
	 * @subpackage WPSmartShopProductMembership
	 * @since      1.0.0
	 *
	 * @static
	 * @retval array
	 */
	public static function fields() {
		global $post;

		/* Recupero tutti i ruoli di WordPress e aggiungo un "non selezionato" all'inizio */
		$wpRoles = WPDKUser::allRoles();
		$wpRoles = array_merge( array( '' => __( 'None', WPXSMARTSHOP_TEXTDOMAIN ) ), $wpRoles );

        /**
         * Filtro sule capabilities
         *
         * @filters
         *
         * @param array $caps Array con la lista delle capabilities disponibili in WordPress,
         *                    scorrendo tutti i ruoli presenti ed estraendo le capabilities.
         */
        $allCapabilities = apply_filters( 'wpss_product_membership_capabilities_list', WPDKUser::allCapabilities() );

        /* Se sono in edit, recupero le capabilities selezionate dai custom meta */
        if ( isset( $post ) ) {
			$wpss_membership = WPSmartShopProductMembership::membership( $post->ID );
		}
		$index = 0;
		foreach ( $allCapabilities as $key => $cap ) {
			$wpCapabilities[] = array(
				'type'      => WPDK_FORM_FIELD_TYPE_CHECKBOX,
				'walker'    => false,
				'name'      => 'wpss-membership-capabilities[]',
				'label'     => $cap,
				'value'     => $key,
				'append'    => ( $index++ % 2 ) ? '<br/>' : '',
				'checked'   => $wpss_membership ? ( in_array( $key, $wpss_membership['capabilities'] ) ? $key : '' ) : ''
			);
		}

		$wpCapabilities = array(
			'group' => $wpCapabilities,
			'class' => 'wpss-membership-capabilities-box'
		);

		$fields = array(
			__( 'Enable this feature to change the user\' role when a order (with this product) is confirmed', WPXSMARTSHOP_TEXTDOMAIN ),
			array(
				array(
					'type'      => WPDK_FORM_FIELD_TYPE_CHECKBOX,
					'name'      => 'wpss-membership',
					'walker'    => false,
					'label'     => __( 'Enabled Membership', WPXSMARTSHOP_TEXTDOMAIN ),
					'help'      => __( 'Switch user role when this product is purchased', WPXSMARTSHOP_TEXTDOMAIN ),
					'checked'   => $wpss_membership ? '1' : '',
					'value'     => '1',
				)
			),
			array(
				array(
					'type'      => WPDK_FORM_FIELD_TYPE_SELECT,
					'walker'    => false,
                    'name'      => 'wpss-membership-role',
                    'label'     => __( 'Change role in', WPXSMARTSHOP_TEXTDOMAIN ),
                    'help'      => __( "Switch user role when this product is purchased", WPXSMARTSHOP_TEXTDOMAIN ),
                    'options'   => $wpRoles,
                    'value'     => isset( $wpss_membership['role'] ) ? $wpss_membership['role'] : '',
                ),
            ),
            $wpCapabilities,
            array(
                array(
                    'type'       => WPDK_FORM_FIELD_TYPE_NUMBER,
                    'walker'     => false,
                    'name'       => 'wpss-membership-duration',
                    'label'      => __( 'Keep this role for', WPXSMARTSHOP_TEXTDOMAIN ),
                    'value'      => isset( $wpss_membership['duration'] ) ? $wpss_membership['duration'] : '',
                ),
                array(
                    'name'        => 'wpss-membership-duration-type',
                    'walker'      => false,
                    'type'        => WPDK_FORM_FIELD_TYPE_SELECT,
                    'afterlabel'  => '',
                    'options'     => self::durabilityType(),
                    'append'      => __( 'from purchase (or activation)', WPXSMARTSHOP_TEXTDOMAIN ),
                    'value'       => isset( $wpss_membership['duration-type'] ) ? $wpss_membership['duration-type'] : '',
                ),
            ),
        );
        return $fields;
	}

	/**
	 * Usato per popolare il combo menu della durata di una membership
	 *
	 * @package    wpx SmartShop
	 * @subpackage WPSmartShopProductMembership
	 * @since      1.0.0
	 *
	 * @static
	 * @retval array
	 */
	private static function durabilityType() {
		$result = array(
			'minutes'   => __( 'Minutes', WPXSMARTSHOP_TEXTDOMAIN ),
			'days'      => __( 'Days', WPXSMARTSHOP_TEXTDOMAIN ),
			'months'    => __( 'Months', WPXSMARTSHOP_TEXTDOMAIN ),
			'years'     => __( 'Years', WPXSMARTSHOP_TEXTDOMAIN ),
		);
		return $result;
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Membership Array Traversing
	// -----------------------------------------------------------------------------------------------------------------

	/**
	 * Restituisce un array con le informazioni di membership di un prodotto con un determinato ID
	 *
	 * @package    wpx SmartShop
	 * @subpackage WPSmartShopProductMembership
	 * @since      1.0.0
	 *
	 * @static
	 *
	 * @param $id_product
	 *   ID del prodotto
	 *
	 * @retval mixed
	 *   Se false il prodotto non è di tipo membership, altrimenti restituisce l'array con le informazioni sul
	 *   membership (role e capabilities)
	 *
	 * @example
	 * array(
	 *    'role'         => array('key' => 'role_key', 'duration' => '10', 'type' => 'days'),
	 *    'capabilities' => array('keys' => array('cap1', 'cap4'), 'duration' => '10', 'type' => 'days')
	 * );
	 *
	 */
	public static function membership( $id_product ) {
		$id_product = WPXSmartShopWPML::originalProductID( $id_product );
		$result     = unserialize( get_post_meta( $id_product, 'wpss_product_membership_rules', true ) );
		return $result;
	}

	/**
	 * Recupera, da un ordne confermato, la lista dei prodotti acquistati.
	 * Se uno o più prodotti risultano essere di tipo membership, questa viene applicata e aggiornata nei dati utente.
	 *
	 * @static
	 * @param $order Record dell'ordine confermato
	 */
    public static function membershipWithOrder( $order ) {

        $id_order = WPXSmartShopOrders::id( $order );

        $products = WPXSmartShopStats::productsWithOrderID( $id_order );
        foreach ( $products as $product ) {
            if ( ( $membership = self::membership( $product['id_product'] ) ) ) {
                WPXSmartShopMemberships::addMembership( $membership, $order, $product['id_product'] );

                /**
                 * @action
                 */
                do_action( 'wpxss_product_membership_added', $membership, $order, $product );

                /* Esegue il controllo di attivazine/disattivazione delle sottoscrizioni, lo stesso eseguito al login */
                WPXSmartShopMemberships::flush( $order->id_user_order );
            }
        }
    }




	// -----------------------------------------------------------------------------------------------------------------
	// has/is zone
	// -----------------------------------------------------------------------------------------------------------------

}

<?php
/**
 * @class              WPXSmartShopCouponMaker
 *
 * @description        Gestisce la creazione automatica di set di coupon partendo da determinate regole
 *
 * @package            wpx SmartShop
 * @subpackage         coupons
 * @author             =undo= <g.fazioli@undolog.com>, <g.fazioli@wpxtre.me>
 * @copyright          Copyright (c) 2012 wpXtreme, Inc.
 * @link               http://wpxtre.me
 * @created            07/05/12
 * @version            1.0.0
 *
 * @filename           wpxss-coupons-maker
 *
 */

class WPXSmartShopCouponMaker {

    /**
     * @var array Argomenti passati durante la creazione dell'oggetto
     */
    private $_rules;


    /**
     * Costruttore
     *
     * @param array $rules Regole che determinano la creazione dei coupon
     */
    function __construct( $rules = array() ) {
        $this->_rules = $rules;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Maker
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Crea una serie di coupon in base alle regole impostate in $this->args
     *
     * @retval array|false Elenco degli id dei coupon inseriti o false se errore
     */
    function create() {

        $result = false;

        if ( !empty( $this->_rules ) && is_array( $this->_rules ) ) {
            $rules = $this->_rules;

            $result = WPXSmartShopCoupons::create( $rules );
        }

        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Get/Set single parameters
    // -----------------------------------------------------------------------------------------------------------------

    function value() {
        if ( func_num_args() > 0 ) {
            $this->_rules['wpss_coupon_value'] = func_get_arg( 0 );
        } else {
            return $this->_rules['wpss_coupon_value'];
        }
    }

    function qty() {
        if ( func_num_args() > 0 ) {
            $this->_rules['wpss_coupon_qty'] = func_get_arg( 0 );
        } else {
            return $this->_rules['wpss_coupon_qty'];
        }
    }

    function same_uniqcode() {
        if ( func_num_args() > 0 ) {
            $this->_rules['wpss_coupon_same_uniqcode'] = func_get_arg( 0 );
        } else {
            return $this->_rules['wpss_coupon_same_uniqcode'];
        }
    }

    function cumulative() {
        if ( func_num_args() > 0 ) {
            $this->_rules['wpss_coupon_cumulative'] = func_get_arg( 0 );
        } else {
            return $this->_rules['wpss_coupon_cumulative'];
        }
    }

    function limit_product_qty() {
        if ( func_num_args() > 0 ) {
            $this->_rules['wpss_coupon_limit_product_qty'] = func_get_arg( 0 );
        } else {
            return $this->_rules['wpss_coupon_limit_product_qty'];
        }
    }

    function id_product() {
        if ( func_num_args() > 0 ) {
            $this->_rules['wpss_coupon_restrict_product'] = 'product';
            $this->_rules['id_product'] = func_get_arg( 0 );
        } else {
            return $this->_rules['id_product'];
        }
    }

    function id_product_type() {
        if ( func_num_args() > 0 ) {
            $this->_rules['wpss_coupon_restrict_product'] = 'product_type';
            $this->_rules['id_product_type'] = func_get_arg( 0 );
        } else {
            return $this->_rules['id_product_type'];
        }
    }

    function id_owner() {
        if ( func_num_args() > 0 ) {
            $this->_rules['wpss_coupon_restrict_user'] = 'y';
            $this->_rules['id_owner'] = func_get_arg( 0 );
        } else {
            return $this->_rules['id_owner'];
        }
    }

    function id_user_maker() {
        if ( func_num_args() > 0 ) {
            $this->_rules['id_user_maker'] = func_get_arg( 0 );
        } else {
            return $this->_rules['id_user_maker'];
        }
    }

    function id_product_maker() {
        if ( func_num_args() > 0 ) {
            $this->_rules['id_product_maker'] = func_get_arg( 0 );
        } else {
            return $this->_rules['id_product_maker'];
        }
    }

    function restrict_user() {
        if ( func_num_args() > 0 ) {
            $this->_rules['wpss_coupon_restrict_user'] = func_get_arg( 0 );
        } else {
            return $this->_rules['wpss_coupon_restrict_user'];
        }
    }

    function date_from() {
        if ( func_num_args() > 0 ) {
            $this->_rules['wpss_coupon_date_from'] = func_get_arg( 0 );
        } else {
            return $this->_rules['wpss_coupon_date_from'];
        }
    }

    function date_to() {
        if ( func_num_args() > 0 ) {
            $this->_rules['wpss_coupon_date_to'] = func_get_arg( 0 );
        } else {
            return $this->_rules['wpss_coupon_date_to'];
        }
    }

    function unlimited() {
        if ( func_num_args() > 0 ) {
            $this->_rules['wpss_coupon_unlimited'] = func_get_arg( 0 );
        } else {
            return $this->_rules['wpss_coupon_unlimited'];
        }
    }

    function prefix() {
        if ( func_num_args() > 0 ) {
            $this->_rules['wpss_coupon_uniqcode_prefix'] = func_get_arg( 0 );
        } else {
            return $this->_rules['wpss_coupon_uniqcode_prefix'];
        }
    }

    function postfix() {
        if ( func_num_args() > 0 ) {
            $this->_rules['wpss_coupon_uniqcode_postfix'] = func_get_arg( 0 );
        } else {
            return $this->_rules['wpss_coupon_uniqcode_postfix'];
        }
    }

    function durability() {
        if ( func_num_args() > 0 ) {
            $this->_rules['wpss_product_coupon_durability']      = func_get_arg( 0 );
            $this->_rules['wpss_product_coupon_durability_type'] = func_get_arg( 1 );
        } else {
            return array(
                $this->_rules['wpss_product_coupon_durability'],
                $this->_rules['wpss_product_coupon_durability_type']
            );
        }
    }

}
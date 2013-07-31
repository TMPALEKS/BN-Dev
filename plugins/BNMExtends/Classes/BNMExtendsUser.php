<?php
/**
 * Estensione dell'utenza di WordPress
 *
 * @package            Blue Note Milano
 * @subpackage         BNMExtendsUser
 * @author             =undo= <g.fazioli@saidmade.com>
 * @copyright          Copyright (c) 2012 Saidmade Srl.
 * @created            07/12/11
 * @version            1.0
 *
 * @todo Inserire un combo o input text con autocomplete, per scegliere l'utente da modificare quando si è amministratori
 *
 */

class BNMExtendsUser extends WPDKUser {

    // -----------------------------------------------------------------------------------------------------------------
    // Static values: roles & capabilities
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Lega in un array chiavi e descrizioni per i permessi (capabilities) utente
     *
     * @static
     * @return array
     */
    public static function capabilities() {
        $result = array(
            'bnm_cap_1'                     => array('description' => 'Medesimo trattamento 1 accompagnatore'),
            'bnm_cap_checkout_cash'         => array('description' => 'Acquisto senza transazione / allotment'),
            'bnm_cap_comments'              => array('description' => 'Abilitazione ai commenti'),
            'bnm_cap_coupon_subscription'   => array('description' => 'Utilizzo abbonamento codici a scalare'),
            'bnm_cap_discount_product'      => array('description' => 'Sconto X su merchandising / prodotti'),
            'bnm_cap_discount_tkts'         => array('description' => 'Acquisto tkts sconto X su tutti'),
            'bnm_cap_discount_tkts_first'   => array('description' => 'Acquisto tkts sconto 40% door primo set'),
            'bnm_cap_discount_tkts_second'  => array('description' => 'Acquisto tkts sconto 40% door secondo set'),
            'bnm_cap_facility_dinner'       => array('description' => 'Prenotazione tavoli cena in pianta'),
            'bnm_cap_facility_reservation'  => array('description' => 'Procedure agevolate di prenotazione e ingresso'),
            'bnm_cap_free'                  => array('description' => 'Ingresso gratuito tutti gli spettacoli'),
            'bnm_cap_free_year'             => array('description' => 'N ingressi gratis anno su Y eventi decisi da noi'),
            'bnm_cap_friend_free'           => array('description' => 'N ingressi gratis anno per amici'),
            'bnm_cap_full_price'            => array('description' => 'Acquisti prezzo pieno'),
            'bnm_cap_insert'                => array('description' => 'Inserimento ID cliente finale e note'),
            'bnm_cap_intermediaries'        => array('description' => 'Acquisto e prezzo intermediari'),
            'bnm_cap_offline'               => array('description' => 'Acquisto con transizione offline'),
            'bnm_cap_price_category'        => array('description' => 'Scelta categorie prezzo per utenza'),
            'bnm_cap_promo_code'            => array('description' => 'Utilizzo codici promo in genere'),
            'bnm_cap_standard_subscription' => array('description' => 'Acquisto abbonamenti standard'),
            'bnm_cap_store_max_item'        => array('description' => 'Tutti gli acquisti con limite max 8 per item / acquisto'),
            'bnm_cap_third_parties'         => array('description' => 'Acquisto conto terzi'),
            'bnm_cap_young_subscription'    => array('description' => 'Acquisto abbonamento giovani'),
        );
        return $result;
    }

    /**
     * Lega in un array chiavi e descrizioni per i ruoli utente e per ogni ruolo indica le capabilities
     *
     * @static
     * @return array
     */
    public static function roles() {
        $result = array(
            'bnm_role_2' => array(
                'description' => __( 'Registered User', 'bnmextends' ),
                'help'        => '',
                'caps'        => array(
                    'bnm_cap_comments',
                    'bnm_cap_full_price',
                    'bnm_cap_standard_subscription',
                    'bnm_cap_coupon_subscription',
                    'bnm_cap_promo_code',
                    'bnm_cap_store_max_item'
                )
            ),
            'bnm_role_3' => array(
                'description' => __( 'Under 26', 'bnmextends' ),
                'help'        => '',
                'caps'        => array(
                    'bnm_cap_comments',
                    'bnm_cap_full_price',
                    'bnm_cap_standard_subscription',
                    'bnm_cap_young_subscription',
                    'bnm_cap_coupon_subscription',
                    'bnm_cap_promo_code',
                    'bnm_cap_discount_tkts_second',
                    'bnm_cap_store_max_item',
                )
            ),
            'bnm_role_4' => array(
                'description' => __( 'Over 65', 'bnmextends' ),
                'help'        => '',
                'caps'        => array(
                    'bnm_cap_comments',
                    'bnm_cap_full_price',
                    'bnm_cap_standard_subscription',
                    'bnm_cap_coupon_subscription',
                    'bnm_cap_promo_code',
                    'bnm_cap_discount_tkts_first',
                    'bnm_cap_discount_tkts_second',
                    'bnm_cap_store_max_item'
                )
            ),
            'bnm_role_5' => array(
                'description' => __( 'Club Member', 'bnmextends' ),
                'help'        => '',
                'caps'        => array(
                    'bnm_cap_comments',
                    'bnm_cap_full_price',
                    'bnm_cap_standard_subscription',
                    'bnm_cap_coupon_subscription',
                    'bnm_cap_promo_code',
                    'bnm_cap_discount_tkts',
                    'bnm_cap_free',
                    'bnm_cap_1',
                    'bnm_cap_free_year',
                    'bnm_cap_discount_product',
                    'bnm_cap_facility_dinner',
                    'bnm_cap_store_max_item'
                )
            ),
            'bnm_role_6' => array(
                'description' => __( 'Club Platinum', 'bnmextends' ),
                'help'        => '',
                'caps'        => array(
                    'bnm_cap_comments',
                    'bnm_cap_full_price',
                    'bnm_cap_standard_subscription',
                    'bnm_cap_coupon_subscription',
                    'bnm_cap_promo_code',
                    'bnm_cap_1',
                    'bnm_cap_friend_free',
                    'bnm_cap_discount_product',
                    'bnm_cap_facility_reservation',
                    'bnm_cap_facility_dinner',
                    'bnm_cap_store_max_item'
                )
            ),
            'bnm_role_7' => array(
                'description' => __( 'Intermediaries', 'bnmextends' ),
                'help'        => '',
                'caps'        => array(
                    'bnm_cap_comments',
                    'bnm_cap_full_price',
                    'bnm_cap_standard_subscription',
                    'bnm_cap_coupon_subscription',
                    'bnm_cap_promo_code',
                    'bnm_cap_intermediaries',
                    'bnm_cap_third_parties',
                    'bnm_cap_insert',
                    'bnm_cap_store_max_item'
                )
            ),
            'bnm_role_8' => array(
                'description' => __( 'Box Office', 'bnmextends' ),
                'help'        => '',
                'caps'        => array(
                    'bnm_cap_comments',
                    'bnm_cap_full_price',
                    'bnm_cap_standard_subscription',
                    'bnm_cap_young_subscription',
                    'bnm_cap_coupon_subscription',
                    'bnm_cap_promo_code',
                    'bnm_cap_discount_tkts_first',
                    'bnm_cap_discount_tkts_second',
                    'bnm_cap_discount_tkts',
                    'bnm_cap_free',
                    'bnm_cap_1',
                    'bnm_cap_free_year',
                    'bnm_cap_friend_free',
                    'bnm_cap_facility_reservation',
                    'bnm_cap_facility_dinner',
                    'bnm_cap_intermediaries',
                    'bnm_cap_third_parties',
                    'bnm_cap_insert',
                    'bnm_cap_price_category',
                    'bnm_cap_offline',
                    'bnm_cap_checkout_cash',
                )
            ),

            'bnm_role_10' => array(
                'description' => __( 'Affiliated A - CRAL', 'bnmextends' ),
                'help'        => '',
                'caps'        => array(
                    'bnm_cap_comments',
                    'bnm_cap_standard_subscription',
                    'bnm_cap_coupon_subscription',
                    'bnm_cap_promo_code',
                )
            ),
            'bnm_role_11' => array(
                'description' => __( 'Affiliated B - CARTE', 'bnmextends' ),
                'help'        => '',
                'caps'        => array(
                    'bnm_cap_comments',
                    'bnm_cap_standard_subscription',
                    'bnm_cap_coupon_subscription',
                    'bnm_cap_promo_code',
                )
            ),
            'bnm_role_12' => array(
                'description' => __( 'Affiliated C - Music School', 'bnmextends' ),
                'help'        => '',
                'caps'        => array(
                    'bnm_cap_comments',
                    'bnm_cap_standard_subscription',
                    'bnm_cap_coupon_subscription',
                    'bnm_cap_promo_code',
                )
            ),
        );
        return $result;
    }


    public static function defaultRole() {
        $default = 'bnm_role_2';
        return $default;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Static values: sdf fields
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Usato per popolare il combo della form (registrazione e profilo) per ricevere privilegi e sconti extra in base
     * all'appartenenza a CRAL, associazioni e scuole di musica o al possesso della carta fedeltà
     *
     * @static
     * @return array
     */
    private static function associations() {
        $result = array(
            ''                     => __('None', 'bnmextends'),
            'association'          => __('Member of Association or CRAL', 'bnmextends'),
            'loyalty_card'         => __('Loyalty Card Holder', 'bnmextends'),
            'student_music_school' => __('Music School Student', 'bnmextends'),
        );
        return $result;
    }

    /**
     * Costruisce la lista dei checkbox usati nel profilo utente
     *
     * @static
     * @param $id_user
     * @return array
     */
    public static function capabilitiesCheckboxes( $id_user ) {
        $capabilities = self::capabilities();
        $user         = new WP_User( $id_user );
        $result       = array();
        foreach ( $capabilities as $key => $cap ) {
            $result[] = array(
                array(
                    'type'      => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                    'name'      => 'capability[]',
                    'label'     => $cap['description'],
                    'value'     => $key,
                    'checked'   => $user->has_cap( $key ) ? $key : ''
                )
            );
        }
        return $result;
    }

    /**
     * Restituisce un SDF per selezionare il ruolo utente
     *
     * @static
     * @param $id_user
     * @return array
     */
    public static function rolesComboMenu( $id_user ) {
        $roles       = new WP_Roles();
        $roles_names = $roles->get_names();
        $user        = new WP_User( $id_user );
        $user_roles  = $user->roles;
        $user_role   = $user_roles[key( $user_roles )];

        /* SDF */
        $result = array(
            'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
            'name'    => 'role',
            'label'   => __( 'User role', 'bnmextends' ),
            'value'   => $user_role,
            'options' => array()
        );

        foreach ( $roles_names as $key => $role ) {
            $result['options'][$key] = $role;
        }

        return array( $result );

    }

    public static function passwordField() {
        $result = array(
            array(
                'type'      => WPDK_FORM_FIELD_TYPE_PASSWORD,
                'name'      => 'password',
                'size'      => 32,
                'label'     => __('Insert New Password', 'bnmextends'),
                'value'     => '',
                'help'      => __('Leave blank if you do not want to change password. Min 6 chars, Max 12 chars.', 'bnmextends')
            )
        );
        return $result;
    }

    public static function passwordConfirmField() {
        $result = array(
            array(
                'type'      => WPDK_FORM_FIELD_TYPE_PASSWORD,
                'name'      => 'password_repeat',
                'size'      => 32,
                'label'     => __('Repeat New Password', 'bnmextends'),
                'value'     => ''
            )
        );
        return $result;
    }

    public static function usersComboMenu( $id_user ) {
        $users_list = get_users();
        if ( $users_list ) {
            foreach ( $users_list as $user ) {
                $users[$user->ID] = sprintf( '%s (%s)', $user->display_name, $user->user_email );
            }
        }
        $result = array(
            array(
                'type'    => WPDK_FORM_FIELD_TYPE_SELECT,
                'name'    => 'user_id',
                'class'   => 'wpdk-form-label-inline',
                'value'   => $id_user,
                'options' => $users
            ),
            array(
                'type'  => WPDK_FORM_FIELD_TYPE_BUTTON,
                'value' => __( 'Edit', 'bnmextends' ),
                'name'  => 'bnm-profile-button-edit',
                'id'    => 'bnm-profile-button-edit'
            )
        );
        return $result;
    }
    
    /**
     * SDF per la costruzione del form registrazione e modifica profilo
     *
     * @static
     * @param bool $id
     * @return array
     */
    public static function fields( $id = false ) {
        if ( $id ) {
            $user = self::userWithID( $id );
        } else {
            $user = false;
        }

        $enti_permalink    = BNMExtends::pagePermalinkWithSlug( 'enti-convenzionati' );
        $term_permalink    = BNMExtends::pagePermalinkWithSlug( 'condizioni-generali-di-contratto' );
        $privacy_permalink = BNMExtends::pagePermalinkWithSlug( 'privacy' );

        $fields = array(
            __( 'Login information', 'bnmextends')                => array(

                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __('First Name', 'bnmextends'),
                        'required'  => true,
                        'size'      => 32,
                        'name'      => 'first_name',
                        'value'     => $user ? $user->extra->first_name : ''
                    ),
                    array(
                        'type'   => WPDK_FORM_FIELD_TYPE_HIDDEN,
                        'name'   => 'id_user',
                        'value'  => $user ? $user->ID : '',
                    ),
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __('Last Name', 'bnmextends'),
                        'required'  => true,
                        'size'      => 32,
                        'name'      => 'last_name',
                        'value'     => $user ? $user->extra->last_name : ''
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_EMAIL,
                        'label'     => __('Email', 'bnmextends'),
                        'size'      => 32,
                        'help'      => __('This email address will be your username', 'bnmextends'),
                        'name'      => 'email',
                        'value'     => $user ? strtolower( $user->extra->email ) : ''
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_EMAIL,
                        'label'     => __('Repeat email', 'bnmextends'),
                        'size'      => 32,
                        'name'      => 'email_repeat',
                        'value'     => $user ? strtolower( $user->extra->email ) : ''
                    )
                ),
            ),

            __( 'Invoice information', 'bnmextends' )             => array(
                __( 'Fill out these fields if you order for a company and you need invoce.', 'bnmextends' ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'    => 'company_name',
                        'label'   => __( 'Company Name', 'bnmextends' ),
                        'value'   => $user ? $user->extra->company_name : ''
                    )
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_TEXT,
                        'name'    => 'vat_number',
                        'label'   => __( 'Vat or Fiscal Number', 'bnmextends' ),
                        'value'   => $user ? $user->extra->vat_number : '',
                        'size'    => 55
                    )
                ),
                array(
                    array(
                        'type'        => WPDK_FORM_FIELD_TYPE_TEXTAREA,
                        'name'        => 'invoice_note',
                        'label'       =>__('Full Company Address: please insert Street, City, Zip, Country', 'bnmextends'),
                        'cols'        => 42,
                        'value'       => $user ? get_user_meta( $user->ID, 'invoice_note', true ) : ''
                    )
                ),
            ),

            __( 'Personal information', 'bnmextends')             => array(
                __('Fill in these fields for shipments and to take advantage of discounts and concessions', 'bnmextends'),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_DATE,
                        'label'     => __('Birth date', 'bnmextends'),
                        'required'  => true,
                        'size'      => 9,
                        'name'      => 'birth_date',
                        'value'     => $user ? self::birthDateToInput($user->extra->birth_date) : ''
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_SELECT,
                        'label'     => __('Sex', 'bnmextends'),
                        'required'  => true,
                        'options'   => array(
                            ''  => __('Please Select', 'bnmextends'),
                            'm' => __('Male', 'bnmextends'),
                            'f' => __('Female', 'bnmextends')
                        ),
                        'name'      => 'sex',
                        'value'     => $user ? $user->extra->sex : ''
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_SELECT,
                        'label'     => __('Job position', 'bnmextends'),
                        'required'  => true,
                        'options'   => array(
                            ''              => __('Please Select', 'bnmextends'),
                            'employee'      => __('Employee', 'bnmextends'),
                            'self-employed' => __('Self-employed', 'bnmextends'),
                            'professional'  => __('Professional', 'bnmextends'),
                            'entrepreneur'  => __('Entrepreneur', 'bnmextends'),
                            'student'       => __('Student', 'bnmextends'),
                            'retired'       => __('Retired', 'bnmextends')
                        ),
                        'name'      => 'job_position',
                        'value'     => $user ? $user->extra->job_position : ''
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __('Address', 'bnmextends'),
                        'size'      => 32,
                        'name'      => 'bill_address',
                        'value'     => $user ? $user->extra->bill_address : ''
                    ),
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'label'     => __('ZIP code', 'bnmextends'),
                        'size'      => 11,
                        'name'      => 'bill_zipcode',
                        'value'     => $user ? $user->extra->bill_zipcode : ''
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __('City', 'bnmextends'),
                        'size'      => 11,
                        'name'      => 'bill_town',
                        'value'     => $user ? $user->extra->bill_town : ''
                    ),
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'      => 'bill_country',
                        'label'     => __('Country', 'bnmextends'),
                        'options'   => WPSmartShopShippingCountries::countriesForSelectMenu(),
                        'value'     => $user ? ( ( $user->extra->bill_country == '' ) ? '33' : $user->extra->bill_country ) : '33'
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_PHONE,
                        'label'     => __('Phone', 'bnmextends'),
                        'size'      => 11,
                        'name'      => 'bill_phone',
                        'value'     => $user ? $user->extra->bill_phone : ''
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_PHONE,
                        'label'     => __('Mobile', 'bnmextends'),
                        'size'      => 11,
                        'name'      => 'bill_mobile',
                        'value'     => $user ? $user->extra->bill_mobile : ''
                    ),
                ),
                array(
                    array(
                        'type'       => WPDK_FORM_FIELD_TYPE_CHECKBOX,
                        'name'       => 'shipping_address_different',
                        'label'      => __( 'Shipping to different address?', 'bnmextends' ),
                        'afterlabel' => '',
                        'value'      => ''
                    )
                )
            ),

            __( 'Shipping information', 'bnmextends')             => array(
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __('First Name', 'bnmextends'),
                        'size'      => 32,
                        'name'      => 'shipping_first_name',
                        'value'     => $user ? $user->extra->shipping_first_name : ''
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __('Last Name', 'bnmextends'),
                        'size'      => 32,
                        'name'      => 'shipping_last_name',
                        'value'     => $user ? $user->extra->shipping_last_name : ''
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_EMAIL,
                        'label'     => __('Email', 'bnmextends'),
                        'name'      => 'shipping_email',
                        'size'      => 32,
                        'value'     => $user ? strtolower( $user->extra->shipping_email ) : ''
                    ),
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __('Address', 'bnmextends'),
                        'size'      => 32,
                        'name'      => 'shipping_address',
                        'value'     => $user ? $user->extra->shipping_address : ''
                    ),
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_NUMBER,
                        'label'     => __('ZIP code', 'bnmextends'),
                        'size'      => 11,
                        'name'      => 'shipping_zipcode',
                        'value'     => $user ? $user->extra->shipping_zipcode : ''
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_TEXT,
                        'label'     => __('Town', 'bnmextends'),
                        'size'      => 11,
                        'name'      => 'shipping_town',
                        'value'     => $user ? $user->extra->shipping_town : ''
                    ),
                ),
                array(

                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'      => 'shipping_country',
                        'label'     => __('Country', 'bnmextends'),
                        'options'   => WPSmartShopShippingCountries::countriesForSelectMenu(),
                        'value'     => $user ? ( ( $user->extra->shipping_country == '' ) ? '' : '33' ) : '33'
                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_PHONE,
                        'label'     => __('Phone', 'bnmextends'),
                        'size'      => 11,
                        'name'      => 'shipping_phone',
                        'value'     => $user ? $user->extra->shipping_phone : ''
                    ),
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_PHONE,
                        'label'     => __('Mobile', 'bnmextends'),
                        'size'      => 11,
                        'name'      => 'shipping_mobile',
                        'value'     => $user ? $user->extra->shipping_mobile : ''
                    ),
                )
            ),

            __( 'Under 26', 'bnmextends')                         => array(
                __('Your age is up to 26 years old? Upload a PDF of your ID card to get discount upon tickets', 'bnmextends'),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_FILE,
                        'name'    => 'upload_pdf_under_26_id',
                        'label'   => __('Upload PDF', 'bnmextends'),
                        'append'  => sprintf('<a target="_blank" href="%s">%s</a>', $term_permalink, __('Read the terms for under 26 users', 'bnmextends'))
                    )
                )
            ),

            __( 'Over 65', 'bnmextends')                          => array(
                __('Your age is 65 or more? Upload a PDF of your ID card to get discount upon tickets', 'bnmextends'),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_FILE,
                        'name'    => 'upload_pdf_over_65_id',
                        'label'   => __('Upload PDF', 'bnmextends'),
                        'append'  => sprintf('<a target="_blank" href="%s">%s</a>', $term_permalink, __('Read the terms for over 65 users', 'bnmextends'))
                    )
                )
            ),

            __( 'Discount and extra privileges', 'bnmextends')    => array(
                __('<strong>Are you a member of an entitled association or CRAL?</strong><br/><strong>Are you a loyalty card holder?</strong><br/><strong>Are you a music school student?</strong><br/>Upload a PDF of your membership card or school badge to get discount upon tickets', 'bnmextends'),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_SELECT,
                        'name'      => 'associations',
                        'label'     => __('Select', 'bnmextends'),
                        'options'   => self::associations(),

                    )
                ),
                array(
                    array(
                        'type'      => WPDK_FORM_FIELD_TYPE_FILE,
                        'name'      => 'upload_pdf_cral',
                        'label'     => __( 'Upload PDF', 'bnmextends' ),
                        'append'    => sprintf( '<a target="_blank" href="%s">%s</a>', $enti_permalink, __( 'Read the terms and the list of entitled organizations', 'bnmextends' ) )
                    )
                )
            ),

            __( 'Newsletter', 'bnmextends' )                      => array(
                __( 'Our weekly newsletter contains the event calendar and other news and special offers', 'bnmextends' ),
                array(
                    array(
                        'type'       => WPDK_FORM_FIELD_TYPE_RADIO,
                        'label'      => __( 'Newsletter Sign Up? Yes', 'bnmextends' ),
                        'afterlabel' => '',
                        'name'       => 'newsletter',
                        'checked'    => $user ? $user->extra->newsletter : 'y',
                        'value'      => 'y'
                    ),
                    array(
                        'type'       => WPDK_FORM_FIELD_TYPE_RADIO,
                        'label'      => __( 'No', 'bnmextends' ),
                        'afterlabel' => '',
                        'name'       => 'newsletter',
                        'class'      => 'newsletter-no',
                        'value'      => 'n',
                        'checked'    => $user ? $user->extra->newsletter : '',
                    ),
                ),

            ),

            __( 'Privacy & Conditions Of Use', 'bnmextends')       => array(
                sprintf( __( 'In proceeding, I confirm that I have read and agreed to the <a href="%s" target="_blank">Terms and Conditions</a> and the <a href="%s" target="_blank">Privacy Policy</a>', 'bnmextends' ), $term_permalink, $privacy_permalink ),
                /*array(
                    array(
                        'type'  => 'checkbox',
                        'label' => __('Agree personal privacy', 'bnmextends'),
                        'name'  => 'privacy_agree_a',
                        'value' => 'y'
                    )
                ),
                array(
                    array(
                        'type'  => 'checkbox',
                        'label' => __('Agree personal privacy', 'bnmextends'),
                        'name'  => 'privacy_agree_b',
                        'value' => 'y'
                    )
                ),
                array(
                    array(
                        'type'    => WPDK_FORM_FIELD_TYPE_RADIO,
                        'name'    => 'privacy_agree_c',
                        'label'   => __('Privacy.. Yes', 'bnmextends'),
                        'value'   => 'y',
                        'checked' => 'y'
                    ),
                    array(
                        'type'  => WPDK_FORM_FIELD_TYPE_RADIO,
                        'name'  => 'privacy_agree_c',
                        'class' => 'privacy_agree_c_no',
                        'label' => __('No', 'bnmextends'),
                        'value' => 'n',
                    )
                )*/
            )
        );

        /* Se siamo in edit, elimino la parte della Privacy */
        if ( $user ) {
            array_splice( $fields, -1, 1 );
        } else {
            /* Altrimenti elimino le 5 schede centrali */
            array_splice( $fields, -7, 5 );
            /* Più la parte relativa alla fatturazione */
            array_splice( $fields, 1, 1 );
        }

        if ( $id ) {
            $fields[key( $fields )][] = self::passwordField();
            $fields[key( $fields )][] = self::passwordConfirmField();

            /* Se amministratore aggiungo anche gestione capabilities */
            if ( self::isSu() ) {
                /* User select */
                array_unshift( $fields[key( $fields )], self::usersComboMenu( $id ) );
                array_unshift( $fields[key( $fields )], __( '<strong>You are an administrator</strong>: select an user to edit', 'bnmextends' ) );

                $fields[key( $fields )][] = __( '<strong>You are an administrator</strong>: below you can edit roles & permissions', 'bnmextends' );
                $fields[key( $fields )][] = self::rolesComboMenu( $id );
                foreach ( self::capabilitiesCheckboxes( $id ) as $cap ) {
                    $fields[key( $fields )][] = $cap;
                }
            }
        }

        return $fields;
    }

    /**
     * Restituisce il nome della tabella temporanea degli utenti.
     *
     * @static
     * @return string
     */
    public static function tableName() {
        global $wpdb;
        return sprintf('%s%s', $wpdb->prefix, kBNMExtendsDatabaseTableUsersName);
    }


    // -----------------------------------------------------------------------------------------------------------------
    // WordPress integration
    // -----------------------------------------------------------------------------------------------------------------

	/* @todo Questa è da rivedere, deve loppare per ruoli e capabilities di sopra */
    public static function registerRolesAndCapabilities() {

        //self::registerBeta();

        /*
         * Questo è - per adesso - il ruolo di partenza di tutti gli utenti aggiuntivi. In futuro si potrebbe partire
         * da un'utenza diversa, come editor ad esempio, per permettere maggiori operazioni lato backend
         */
        $subscribeRole = get_role('subscriber');

        /*
         * Lo UserRegister, in Blue Note, corrisponde di fatto ad un semplice utente registrato ed ha quindi i suoi
         * stessi permessi (le role sono i Ruole mentre le Capability sono i permessi).
         */

        foreach ( self::roles() as $key => $role ) {
            $added_role = add_role( $key, $role['description'], $subscribeRole->capabilities );
            if ( !is_null( $added_role ) && isset( $role['caps'] ) && count( $role['caps'] ) > 0 ) {
                foreach ( $role['caps'] as $cap ) {
                    $added_role->add_cap( $cap );
                }
            }
        }
    }

    private static function registerBeta() {
    }

    /* Only per debug */
    public static function deregisterRolesAndCapabilities() {
        $roles        = self::roles();
        $capabilities = self::capabilities();

        foreach ( $roles as $key_role => $role ) {
            $role = get_role( $key_role );
            if ( $role ) {
                foreach ( $capabilities as $key_cap => $cap ) {
                    $role->remove_cap( $key_cap );
                }
                remove_role( $key_role );
            }
        }
    }

    public static function delete_user( $id_user ) {
        global $wpdb;

        $tablename = self::tableName();

        $sql = <<< SQL
DELETE FROM {$tablename}
WHERE id_user = {$id_user}
SQL;
        $wpdb->query( $sql );

    }


    // -----------------------------------------------------------------------------------------------------------------
    // Database
    // -----------------------------------------------------------------------------------------------------------------

	/**
	 * Esegue un delta sulla tabella utenti quando il plugin viene attivato
	 *
	 * @static
	 *
	 */
    public static function updateTable() {
        if (!function_exists('dbDelta')) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        }
        $dbDeltaTableFile = sprintf('%s%s', dirname(kBNMExtends__FILE__), kBNMExtendsDatabaseTableUsersFilename);
        $file             = file_get_contents($dbDeltaTableFile);
        $sql              = sprintf($file, self::tableName());

        @dbDelta($sql);
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Insert
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Aggiunge un utente alla tabella temporanea degli utenti. Questo per via del double-opt-in, dove viene inviata
     * all'utente una email di conferma. La tabella, infatti, supporta un campo di stato che per default è 'pending'
     *
     * Prima di inserire un utente esegue un controllo sui duplicati.
     *
     * @static
     * @return array
     *   False se errore, altrimenti valori utente inseriti
     */
    public static function addUserTemporary() {
        global $wpdb;

        $result = false;

        // Sanitizzo
        $email = strtolower( sanitize_email( $_POST['email'] ) );

        // Prima di tutto controllo i duplicati

        if ( !email_exists( $email ) && !self::userExistsWithEmail( $email ) ) {

            $values = array(
                'request_datetime'      => date('Y-m-d H:i:s'),

                'uniqid'                => uniqid('u'),
                'ip_address'            => $_SERVER['REMOTE_ADDR'],
                'browser'               => $_SERVER['HTTP_USER_AGENT'],

                'company_name'          => $_POST['company_name'],
                'vat_number'            => $_POST['vat_number'],

                'first_name'            => $_POST['first_name'],
                'last_name'             => $_POST['last_name'],
                'birth_date'            => self::birthDateToMySQL($_POST['birth_date']),
                'sex'                   => $_POST['sex'],
                'email'                 => $email,
                'job_position'          => $_POST['job_position'],
                'newsletter'            => $_POST['newsletter'],

                'bill_address'          => $_POST['bill_address'],
                'bill_country'          => $_POST['bill_country'],
                'bill_town'             => $_POST['bill_town'],
                'bill_zipcode'          => $_POST['bill_zipcode'],
                'bill_phone'            => $_POST['bill_phone'],
                'bill_mobile'           => $_POST['bill_mobile'],

                'shipping_first_name'   => $_POST['shipping_first_name'],
                'shipping_last_name'    => $_POST['shipping_last_name'],
                'shipping_address'      => $_POST['shipping_address'],
                'shipping_country'      => $_POST['shipping_country'],
                'shipping_town'         => $_POST['shipping_town'],
                'shipping_zipcode'      => $_POST['shipping_zipcode'],
                'shipping_email'        => strtolower( sanitize_email( $_POST['shipping_email'] ) ),
                'shipping_phone'        => $_POST['shipping_phone'],
                'shipping_mobile'       => $_POST['shipping_mobile'],

                'privacy_agree_a'       => $_POST['privacy_agree_a'],
                'privacy_agree_b'       => $_POST['privacy_agree_b'],
                'privacy_agree_c'       => $_POST['privacy_agree_c'],
            );

            // Se la data non è stata impostata (non è un campo obbligatorio) la unsetto così che venga NULL sul db
            if(empty($values['birth_date'])) {
                unset($values['birth_date']);
            }

            $result = $wpdb->insert(self::tableName(), $values);

            if ($result) {
                return $values;
            }
        }
        return $result;
    }

    /**
     * Aggiunge un utente a WordPress
     *
     * @static
     *
     * @param $info
     *
     * @return int | WP_Error
     *   Restituisce l'id WordPress dell'utente inserito o, in caso di errore, un oggetto WP_Error
     */
    public static function addUser( $info ) {
        $password = WPDKCrypt::randomAlphaNumber();
        $niceName = self::formatNiceName( $info->first_name, $info->last_name );

        $userInfo = array(
            "user_login"    => strtolower( $info->email ),
            'user_pass'     => $password,
            'user_email'    => strtolower( $info->email ),
            "user_nicename" => $niceName,
            "nickname"      => $niceName,
            "display_name"  => self::formatFullName( $info->first_name, $info->last_name ),
            "first_name"    => $info->first_name,
            "last_name"     => $info->last_name,
            "role"          => self::defaultRole()
        );
        $result   = wp_insert_user( $userInfo );

        // Se ritorna un ID e l'utente è stato inserito, allora cambio lo stato nella tabella temporanea, così da
        // abilitare definitivamente questa utenza. La tabella temporanea rimane per storico e controllo, ma anche per
        // i dati extra quando un utente (o un amministratore) li modificano.

        if ( !is_object( $result ) ) {
            self::updateUserConfirmedWithUniqID( $info->uniqid, $result );
            self::emailWithAccessData( $userInfo );
            WPDKUser::showAdminBarFront( $result, 'false' );
        }

        return $result;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Update
    // -----------------------------------------------------------------------------------------------------------------

    public static function updateUser( $id_user = false ) {
        global $wpdb;

        $result = false;

        /* Sanitizzo */
        $email = strtolower( sanitize_email( $_POST['email'] ) );

        if ( $id_user ) {
            $values = array(
                'ip_address'            => $_SERVER['REMOTE_ADDR'],
                'browser'               => $_SERVER['HTTP_USER_AGENT'],

                'company_name'          => $_POST['company_name'],
                'vat_number'            => $_POST['vat_number'],

                'first_name'            => $_POST['first_name'],
                'last_name'             => $_POST['last_name'],
                'birth_date'            => self::birthDateToMySQL( $_POST['birth_date'] ),
                'sex'                   => $_POST['sex'],
                'email'                 => $email,
                'job_position'          => $_POST['job_position'],
                'newsletter'            => $_POST['newsletter'],

                'bill_address'          => $_POST['bill_address'],
                'bill_country'          => $_POST['bill_country'],
                'bill_town'             => $_POST['bill_town'],
                'bill_zipcode'          => $_POST['bill_zipcode'],
                'bill_phone'            => $_POST['bill_phone'],
                'bill_mobile'           => $_POST['bill_mobile'],

                'shipping_first_name'   => $_POST['shipping_first_name'],
                'shipping_last_name'    => $_POST['shipping_last_name'],
                'shipping_address'      => $_POST['shipping_address'],
                'shipping_country'      => $_POST['shipping_country'],
                'shipping_town'         => $_POST['shipping_town'],
                'shipping_zipcode'      => $_POST['shipping_zipcode'],
                'shipping_email'        => strtolower( sanitize_email( $_POST['shipping_email'] ) ),
                'shipping_phone'        => $_POST['shipping_phone'],
                'shipping_mobile'       => $_POST['shipping_mobile'],

            );

            $where = array(
                'id_user'   => $id_user
            );

            update_user_meta( $id_user, 'invoice_note', esc_attr( $_POST['invoice_note']) );

            /* Se la data non è stata impostata (non è un campo obbligatorio) la unsetto così che venga NULL sul db */
            if ( empty( $values['birth_date'] ) ) {
                unset( $values['birth_date'] );
            }
            $result = $wpdb->update( self::tableName(), $values, $where );

            /* User capabilities */
            if ( self::isSu() && isset( $_POST['capability'] ) ) {
                self::updateUserCapabilities( $id_user, $_POST['capability'], self::capabilities() );
            }

            /* @todo Aggiornamento anche dell'email e nome e congnome, se necessario - come del nicename */

            /* User password */
            if ( isset( $_POST['password'] ) && $_POST['password'] != '' && strlen( $_POST['password'] ) > 6 ) {
                $userdata = array(
                    'ID'        => $id_user,
                    'user_pass' => $_POST['password']
                );
                $result   = wp_update_user( $userdata );
            }
            
            /* First Name */
            if ( isset( $_POST['first_name'] ) && $_POST['first_name'] != '' ) {
                $userdata = array(
                    'ID'        => $id_user,
                    'first_name' => $_POST['first_name']
                );
                $result   = wp_update_user( $userdata );
            }
            
            /* Last Name */
            if ( isset( $_POST['last_name'] ) && $_POST['last_name'] != '' ) {
                $userdata = array(
                    'ID'        => $id_user,
                    'last_name' => $_POST['last_name']
                );
                $result   = wp_update_user( $userdata );
            }
            
            /* Displayed Name */
            if ( isset( $_POST['last_name'] ) && $_POST['last_name'] != '' && isset( $_POST['first_name'] ) && $_POST['first_name'] != ''  ) {
            	
            	$showName = $_POST['first_name'] . " " . $_POST['last_name'];
                $userdata = array(
                    'ID'        => $id_user,
                    'display_name' => $showName
                );
                $result   = wp_update_user( $userdata );
            }
            

            /* Imposto i ruoli */
            $userdata = array( 'ID' => $id_user );

            if ( self::isSu() && isset( $_POST['role'] ) ) {
                $userdata['role'] = $_POST['role'];
            }
            /*
             * Questa parte rende automatico il passaggio a under 26 e over 65 basandosi sull'età: commentata da
             * richiesta cliente
            else {
                $user             = new WP_User( $id_user );
                $user_roles       = $user->roles;
                $userdata['role'] = self::role( $values['birth_date'], $user_roles[key( $user_roles )] );
            }
            */
            $result = wp_update_user( $userdata );

            /* Email con attach in caso di < 26, > 65 o membro */
            self::emailWithAttach();

        }
        return $result;
    }

    public static function updateUserBilling( $id_user = false ) {
        global $wpdb;

        $result = false;

        if ( $id_user ) {
            $values = array(
                'ip_address'            => $_SERVER['REMOTE_ADDR'],
                'browser'               => $_SERVER['HTTP_USER_AGENT'],

                'first_name'            => $_POST['bill_first_name'],
                'last_name'             => $_POST['bill_last_name'],
                'email'                 => strtolower( sanitize_email( $_POST['bill_email'] ) ),

                'bill_address'          => $_POST['bill_address'],
                'bill_country'          => $_POST['bill_country'],
                'bill_town'             => $_POST['bill_town'],
                'bill_zipcode'          => $_POST['bill_zipcode'],
                'bill_phone'            => $_POST['bill_phone'],
                'bill_mobile'           => $_POST['bill_mobile'],

                'shipping_first_name'   => $_POST['shipping_first_name'],
                'shipping_last_name'    => $_POST['shipping_last_name'],
                'shipping_address'      => $_POST['shipping_address'],
                'shipping_country'      => $_POST['shipping_country'],
                'shipping_town'         => $_POST['shipping_town'],
                'shipping_zipcode'      => $_POST['shipping_zipcode'],
                'shipping_email'        => strtolower( sanitize_email( $_POST['shipping_email'] ) ),
                'shipping_phone'        => $_POST['shipping_phone'],
                'shipping_mobile'       => $_POST['shipping_mobile'],

            );

            $where = array(
                'id_user'   => $id_user
            );

            $result = $wpdb->update( self::tableName(), $values, $where );

        }
        return $result;
    }

    public static function updateUserShipping( $id_user = false ) {
        global $wpdb;

        $result = false;

        if ( $id_user ) {
            $values = array(
                'ip_address'            => $_SERVER['REMOTE_ADDR'],
                'browser'               => $_SERVER['HTTP_USER_AGENT'],

                'shipping_first_name'   => $_POST['shipping_first_name'],
                'shipping_last_name'    => $_POST['shipping_last_name'],
                'shipping_address'      => $_POST['shipping_address'],
                'shipping_country'      => $_POST['shipping_country'],
                'shipping_town'         => $_POST['shipping_town'],
                'shipping_zipcode'      => $_POST['shipping_zipcode'],
                'shipping_email'        => strtolower( sanitize_email( $_POST['shipping_email'] ) ),
                'shipping_phone'        => $_POST['shipping_phone'],
                'shipping_mobile'       => $_POST['shipping_mobile'],
            );

            $where = array(
                'id_user'   => $id_user
            );

            $result = $wpdb->update( self::tableName(), $values, $where );

        }
        return $result;
    }

    public static function updateUserInvoice( $id_user ){
        global $wpdb;

        $result = false;

        if ( $id_user ) {
            $values = array(

                'company_name'          => $_POST['company_name'],
                'vat_number'            => $_POST['vat_number'],
                'invoice_note'          => esc_attr( $_POST['invoice_note'] )

            );

            $where = array(
                'id_user'   => $id_user
            );

            $result = $wpdb->update( self::tableName(), $values, $where );
            update_user_meta( $id_user, 'invoice_note', esc_attr( $_POST['invoice_note']) );

        }
        return $result;
    }

    /**
     * Aggiorna lo stato di un utente nella tabella temporanea utenti e ne registra anche l'ID WordPress
     *
     * @static
     *
     * @param $uniqid
     * @param $idUser
     *
     * @return mixed
     */
    private static function updateUserConfirmedWithUniqID($uniqid, $idUser) {
        global $wpdb;
        $values = array(
            'id_user' => $idUser,
            'status'  => 'confirmed'
        );
        $where  = array(
            'uniqid' => $uniqid
        );
        $result = $wpdb->update(self::tableName(), $values, $where);
        return $result;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // EMail sending
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Invia una mail (html) all'utente per confermare la registrazione e allega dati di accesso
     *
     * @static
     *
     * @param $info
     *   Array con le informazioni dell'utente, quelle usate per l'inserimento con wp_insert_user()
     */
    public static function emailWithAccessData( $info ) {
        // Preparo mail per avvertire l'utente che è stato registrato con successo e pronto ad accedere
        $page = get_page_by_path( 'conferma-registrazione', OBJECT, kBNMExtendsSystemPagePostTypeKey );

        $templateEmail = apply_filters( "the_content", $page->post_content );

        $templateEmail = sprintf( $templateEmail, $info['display_name'], $info['user_email'], $info['user_pass'] );

        $email_to      = $info['user_email'];
        $email_subject = $page->post_title;
        $email_message = $templateEmail;
        $headers       = array(
            BNMEXTENDS_EMAIL_FROM . "\r\n",
            'Content-Type: text/html' . "\r\n"
        );

        wp_mail( $email_to, $email_subject, $email_message, $headers );
    }

    /**
     * Invia una mail (html) all'utente ringraziandolo e fornendogli l'indirizzo per lo sbocco dell'utente
     *
     * @static
     *
     * @param $info Array con le informazioni dell'utente, quelle che ritornano da addUserTemporary()
     */
    public static function emailForConfirm( $info ) {
        $page = get_page_by_path( 'user-registration', OBJECT, kBNMExtendsSystemPagePostTypeKey );

        $templateEmail = apply_filters( "the_content", $page->post_content );

        $fullname  = sprintf( '%s %s', $info['first_name'], $info['last_name'] );
        $permalink = BNMExtends::pagePermalinkWithSlug( 'conferma-registrazione' );
        $unlockURL = sprintf( '%s?id=%s', $permalink, $info['uniqid'] );

        $templateEmail = sprintf( $templateEmail, $fullname, $unlockURL );

        $email_to      = $info['email'];
        $email_subject = $page->post_title;
        $email_message = $templateEmail;
        $headers       = array(
            BNMEXTENDS_EMAIL_FROM . "\r\n",
            'Content-Type: text/html' . "\r\n"
        );

        wp_mail( $email_to, $email_subject, $email_message, $headers );
    }

    /**
     * Viene usato sia in fase di registrazione che di cambio profilo per verificare se sono stati selezionati
     * documenti da mandare in attach ad una mail per dimostrare la propria età (under 26 e over 65) e apparteneza a
     * CRAL o varie.
     *
     * La mail viene mandata all'amministrazione (redazione) indicando l'utente che ne ha fatto richiesta
     *
     * @static
     *
     * @return bool
     */
    public static function emailWithAttach() {
        $result = false;

        // @todo
        //$url_user_profile = sprintf('%sprofile/?uniqid=%s', get_option('home', $info['uniqid]);

        /* Under 26 */

        if ( isset( $_FILES['upload_pdf_under_26_id'] ) && is_uploaded_file( $_FILES['upload_pdf_under_26_id']['tmp_name'] ) ) {

            $tmp_name = $_FILES['upload_pdf_under_26_id']['tmp_name'];
            $name     = $_FILES['upload_pdf_under_26_id']['name'];

            /* Sposto temporaneamente il file */
            if ( move_uploaded_file( $tmp_name, WP_CONTENT_DIR . '/uploads/' . basename( $name ) ) ) {
                $result      = true;
                $attachments = array( WP_CONTENT_DIR . "/uploads/" . $name );

//                $email_to      = BNMEXTENDS_PRIMARY_EMAIL;
//                $email_subject = __( 'Under 26 request', 'bnmextends' );
//                $email_message = 'Richiesta di abilitazione Under 26';
//                $headers       = array(
//                    BNMEXTENDS_EMAIL_FROM . "\r\n",
//                    'Content-Type: text/html' . "\r\n"
//                );

//                if ( wp_mail( $email_to, $email_subject, $email_message, $headers, $attachments ) ) {
//
//                    /* Elimino l'attachment */
//                    unlink( WP_CONTENT_DIR . "/uploads/" . $name );
//                }

                if( BNMExtendsMail::requestUnder26( $attachments ) ) {
                    unlink( WP_CONTENT_DIR . "/uploads/" . $name );
                }

            }
            /* Over 65 */
        } elseif ( isset( $_FILES['upload_pdf_over_65_id'] ) &&
            is_uploaded_file( $_FILES['upload_pdf_over_65_id']['tmp_name'] )
        ) {

            $tmp_name = $_FILES['upload_pdf_over_65_id']['tmp_name'];
            $name     = $_FILES['upload_pdf_over_65_id']['name'];

            /* Sposto temporaneamente il file */
            if ( move_uploaded_file( $tmp_name, WP_CONTENT_DIR . '/uploads/' . basename( $name ) ) ) {
                $result      = true;
                $attachments = array( WP_CONTENT_DIR . "/uploads/" . $name );

//                $email_to      = BNMEXTENDS_PRIMARY_EMAIL;
//                $email_subject = __( 'Over 65 request', 'bnmextends' );
//                $email_message = 'Richiesta di abilitazione Over 65';
//                $headers       = array(
//                    BNMEXTENDS_EMAIL_FROM . "\r\n",
//                    'Content-Type: text/html' . "\r\n"
//                );
//
//                if ( wp_mail( $email_to, $email_subject, $email_message, $headers, $attachments ) ) {
//
//                    /* Elimino l'attachment */
//                    unlink( WP_CONTENT_DIR . "/uploads/" . $name );
//                }
                if ( BNMExtendsMail::requestOver65( $attachments ) ) {
                    unlink( WP_CONTENT_DIR . "/uploads/" . $name );
                }
            }
        }

        /* Richieste aggiuntive */
        if ( isset( $_POST['associations'] ) && $_POST['associations'] != '' ) {
            $associations = self::associations();
            $association  = $associations[$_POST['associations']];

            if ( isset($_FILES['upload_pdf_cral'] ) && is_uploaded_file( $_FILES['upload_pdf_cral']['tmp_name'] ) ) {

                $tmp_name = $_FILES['upload_pdf_cral']['tmp_name'];
                $name     = $_FILES['upload_pdf_cral']['name'];

                /* Sposto temporaneamente il file */
                if ( move_uploaded_file( $tmp_name, WP_CONTENT_DIR . '/uploads/' . basename( $name ) ) ) {
                    $result      = true;
                    $attachments = array( WP_CONTENT_DIR . "/uploads/" . $name );

//                    $email_to      = BNMEXTENDS_PRIMARY_EMAIL;
//                    $email_subject = sprintf( __( '%1 request', 'bnmextends' ), $value );
//                    $email_message = 'Richiesta di abilitazione come membro di una associazione';
//                    $headers       = array(
//                        BNMEXTENDS_EMAIL_FROM . "\r\n",
//                        'Content-Type: text/html' . "\r\n"
//                    );
//
//                    if ( wp_mail( $email_to, $email_subject, $email_message, $headers, $attachments ) ) {
//
//                        /* Elimino l'attachment */
//                        unlink( WP_CONTENT_DIR . "/uploads/" . $name );
//                    }
                    if ( BNMExtendsMail::requestAssociations( $association, $attachments ) ) {
                        unlink( WP_CONTENT_DIR . "/uploads/" . $name );
                    }
                }
            }
        }

        return $result;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Commodity
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce il ruolo per un utente da inserire basandosi su alcuni paramentri. Questa viene usata quando un
     * utente conferma la sua registrazione dalla email ricevuta in automatico.
     *
     * @static
     *
     * @param $info
     *
     * @return string
     */
    public static function role( $birth_date, $default = 'bnm_role_2' ) {

        /**
         * Decommentare (come esempio) le righe qui sotto se si vuole alterare in automatico il ruolo di un utente
         * quando viene confermato. Di default un utente viene aggiunto con un ruolo di 'bnm_role_2'
         * cioè utente registrato semplice. Volendo, eseguendo dei controlli sui parametri di registrazione, si può
         * in automatico modificare questo comportamento.
         */
        if ( self::ageFromDate( $birth_date ) >= 65 ) {
            $default = 'bnm_role_4';
        } else if ( self::ageFromDate( $birth_date ) <= 26 ) {
            $default = 'bnm_role_3';
        }

        return $default;
    }

    private static function birthDateToMySQL($birthdate) {
        return WPDKDateTime::formatFromFormat($birthdate, __('m/d/Y', 'bnmextends'), 'Y-m-d');
    }

    private static function birthDateToInput($birthdate) {
        return WPDKDateTime::formatFromFormat($birthdate, 'Y-m-d', __('m/d/Y', 'bnmextends'));
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Exists zone
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Commodity
     *
     * @uses userExistsWith()
     * @static
     *
     * @param $id
     *
     * @return bool
     *       True se esiste un utente con uniqid = $id
     *
     */
    public static function userExistsWithUniqiD($id) {
        return self::userExistsWith(self::sanitizeUserUniqID($id), 'uniqid');
    }

    /**
     * Commodity
     *
     * @static
     *
     * @param $email
     *
     * @return bool
     *   True se esiste un utente con l'email = $email
     *
     */
    public static function userExistsWithEmail( $email ) {
        return self::userExistsWith( $email, 'email' );
    }

    /**
     * Cerca una utenza per un determinato campo/valore
     *
     * @static
     *
     * @param        $value
     * @param string $field
     *
     * @return bool
     */
    private static function userExistsWith($value, $field = 'email') {
        global $wpdb;

        $userTemporaryTable = self::tableName();

        $sql    = <<< SQL
        SELECT id FROM `{$userTemporaryTable}`
        WHERE {$field} = '{$value}'
SQL;
        $result = $wpdb->get_var($sql);

        return !is_null($result);
    }


    // -----------------------------------------------------------------------------------------------------------------
    // has/is zone
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce true se l'utente loggato è un super-user
     * In questo modo abbiamo incapsulato la regola dell'utente 'root'
     *
     * @static
     * @return bool
     */
    public static function isSu() {
        if ( is_user_logged_in() ) {
            $id_user_logged_in = get_current_user_id();
            $user              = new WP_User( $id_user_logged_in );
            if ( $id_user_logged_in == 1 || $user->has_cap( 'edit_posts' ) ) {
                return true;
            }
        }
        return false;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Getting zone
    // -----------------------------------------------------------------------------------------------------------------

    public static function userWithID( $id ) {
        global $wpdb;

        if ( $id ) {
            $user = get_userdata( $id );

            // Load extra data
            $table       = self::tableName();
            $sql         = <<< SQL
            SELECT * FROM {$table}
            WHERE id_user = {$id}
SQL;
            $result      = $wpdb->get_results( $sql );
            $user->extra = $result[0];

            return $user;
        }
        return false;
    }

    /**
     * Verifica che esista un utente con un determinato uniqID e in stato di 'pending'
     *
     * @static
     *
     * @param $id
     *
     * @return mixed
     *   False se utente non triovato, altrimenti restituisce tutto il record (la riga Object) della tabella temporanea
     */
    public static function userPendingWithUniqID($id) {
        global $wpdb;

        $userTemporaryTable = self::tableName();
        $sanitizeID = self::sanitizeUserUniqID($id);

        $sql    = <<< SQL
        SELECT * FROM `{$userTemporaryTable}`
        WHERE uniqid = '{$sanitizeID}'
        AND status = 'pending'
SQL;
        $result = $wpdb->get_row($sql);
        if (is_null($result)) {
            return false;
        }
        return $result;
    }

    /**
     * Verifica che esista un utente con un determinato uniqID e in stato di 'confirmed'
     *
     * @static
     *
     * @param $id
     *
     * @return mixed
     *   False se utente non triovato, altrimenti restituisce tutto il record (la riga Object) della tabella temporanea
     */
    public static function userConfirmedWithUniqID($id) {
        global $wpdb;

        $userTemporaryTable = self::tableName();
        $sanitizeID = self::sanitizeUserUniqID($id);

        $sql    = <<< SQL
        SELECT * FROM `{$userTemporaryTable}`
        WHERE uniqid = '{$sanitizeID}'
        AND status = 'confirmed'
SQL;
        $result = $wpdb->get_row($sql);
        if (is_null($result)) {
            return false;
        }
        return $result;
    }


    /**
     * Commodity. Legge od imposta lo stato (status) di un utente nella tabella tempornea degli utenti
     *
     * @static
     *
     * @param        $uniqid
     * @param string $status
     *
     * @return mixed
     */
    public static function userStatusWithUniqID($uniqid, $status = '') {
        global $wpdb;

        $userTemporaryTable = self::tableName();

        if ($status == '') {
            $sql   = <<< SQL
            SELECT status FROM `{$userTemporaryTable}`
            WHERE uniqid = '{$uniqid}'
SQL;
            $value = $wpdb->get_var($sql);
            return $value;
        } else {
            $values = array(
                'status' => $status
            );
            $where  = array(
                'uniqid' => $uniqid
            );
            $result = $wpdb->update(self::tableName(), $values, $where);
            return $result;
        }
    }

    /**
     * Restituisce True se un utente aveva chiesto di essere registrato alla Newsletter in fase di registrazione
     *
     * @static
     * @param $uniqid
     * @return bool
     */
    public static function shouldUserRegisterNewsletter($uniqid) {
        global $wpdb;

        $userTemporaryTable = self::tableName();

        $sql = <<< SQL
        SELECT newsletter FROM {$userTemporaryTable}
        WHERE uniqid = '{$uniqid}'
SQL;
        $newsletter = $wpdb->get_var($sql);

        return ($newsletter == 'y');
    }


    /**
     * @param $data
     * @return mixed|string|void
     * Export User for CSV
     */
    public static function exportCSV( $data ) {

        $columns = array(
            __( 'UniqID', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Cognome', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Nome', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Data di iscrizione', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'EMail', WPXSMARTSHOP_TEXTDOMAIN ),
            __( 'Ultimo Login', WPXSMARTSHOP_TEXTDOMAIN ),
            __( '#Login', WPXSMARTSHOP_TEXTDOMAIN ),
        );

        /* Crea il CSV */
        $buffer =  '';
        foreach( $data as $item ) {

            $lastlogin = WPDKDateTime::timeNewLine( date( __('m/d/Y H:i:s', WPDK_TEXTDOMAIN), get_user_meta( $item['id_user'], 'wpdk_user_internal-time_last_login', true ) ));
            $numebrlogin = get_user_meta( $item['id_user'], 'wpdk_user_internal-count_success_login', true);


            $buffer .= sprintf( '"%s","%s","%s","%s","%s","%s","%s"',
                $item['uniqid'],
                $item['last_name'],
                $item['first_name'],

                $item['status_datetime'],
                $item['email'],

                $lastlogin,
                $numebrlogin
            );
            $buffer .= WPDK_CRLF;

        }

        $columns_row = sprintf( '"%s"', join( '","', $columns ) ) . WPDK_CRLF;
        $result      = $columns_row . $buffer;

        return $result;
    }

    public static function downalodCSV() {
        /* Definisco un filename */
        $filename = sprintf( 'BNMExtendsUser-download-%s.csv', date( 'Y-m-d H:i:s' ) );

        /* Contenuto */
        $buffer = get_transient( 'wpxss_users_csv' );

        /* Header per download */
        header( 'Content-Type: application/download' );
        header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
        header( 'Cache-Control: public' );
        header( "Content-Length: " . strlen( $buffer ) );
        header( 'Pragma: no-cache' );
        header( 'Expires: 0' );

        echo $buffer;
    }
}
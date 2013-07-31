<?php
/**
 * @class              WPXSmartShopProduct
 * @description        Gestisce la parte più logica e alta di un prodotto, ponendosi al di sopra del sub-strato
 *                     WordPress relativo ai Post Type
 *
 * @package            wpx SmartShop
 * @subpackage         products
 * @author             =undo= <g.fazioli@wpxtre.me>
 * @copyright          Copyright (C) 2012 wpXtreme, Inc.
 * @created            10/01/12
 * @version            1.0
 *
 * @todo               Inserire un limite di acquisto/download - una sorta di counter, capire solo dove inserirlo, se
 *                     nel custom field del prodotto o nella tabella ordini
 * @todo               Il prezzo base: se lasciato vuoto (o a zero) il prodotto viene considerato gratuito
 *
 */

class WPXSmartShopProduct {

    public static $descriptionPriceRules;

    /**
     * @var string indica la regola sul prezzo indicata alla chiamata di ::price()
     */
    public static $price_rule;

    private $_product;

    var $id;
    var $title;
    var $content;
    var $ptoduct_types;

    /// Construct
    function _construct( $product ) {
        if ( is_numeric( $product ) ) {
            $this->_product = $this->cache( $product );
            if( empty( $this->_product) ) {
                $this->_product = $this->product( absint( $product ) );
                $this->_init();
            }
        } elseif ( is_object( $product ) && is_a( $product, 'WPXSmartShopProduct' ) ) {
            $this->_product = $product;
        } elseif ( is_object( $product ) && is_a( $product, 'stdClass' ) ) {
            $this->_init();
        }
    }

    /// Not use yet
    /**
     * Popola una serie di proprietà di questo oggetto in base al puntatore al record del database
     */
    private function _init() {
        /* Prettamente da db, proprietà del post eventualmente filtrate, rinominate, sanitizzate. */
        $product = $this->_product;

        $this->id            = $product->ID;
        $this->idAuthor      = $product->post_author;
        $this->date          = $product->post_date;
        $this->dateFormat    = WPDKDateTime::formatFromFormat( $product->post_date, MYSQL_DATE_TIME, __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        $this->dateGMT       = $product->post_date_gmt;
        $this->dateGMTFormat = WPDKDateTime::formatFromFormat( $product->post_date_gmt, MYSQL_DATE_TIME, __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        $this->update        = $product->post_modified;
        $this->updateFormat  = WPDKDateTime::formatFromFormat( $product->post_modified, MYSQL_DATE_TIME, __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
        $this->name          = $product->post_title;
        $this->description   = $product->post_content;
        $this->content       = apply_filters( 'the_content', $product->post_content );
        $this->title         = apply_filters( 'the_title', $product->post_title );
        $this->status        = $product->post_status;
        $this->slug          = $product->post_name;
        $this->guid          = $product->guid;
        $this->type          = $product->post_type;

        /* Post meta. */
        $this->priceBase            = $product->post_meta['wpss_product_base_price'][0];
        $this->sku                  = $product->post_meta['wpss_product_sku'][0];
        $this->digitalUrl           = $product->post_meta['wpss_product_digital_url'][0];
        $this->digitalVersion       = $product->post_meta['wpss_product_digital_version'][0];
        $this->version              = $product->post_meta['wpss_product_digital_version'][0]; // alias
        $this->digitalDownloadCount = $product->post_meta['wpss_product_digital_download_count'][0];

        $this->cache( $product, $this->_product );
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Static values
    // -----------------------------------------------------------------------------------------------------------------

    /// Not use yet
    /**
     * Legge o imposta un transient/cache
     *
     * @param int  $id ID del prodotto
     * @param WPXSmartShopProduct $product Oggetto WPXSmartShopProduct
     *
     * @retval WPXSmartShopProduct|null Restituisce un oggetto di tipo WPXSmartShopProduct o null se errore
     */
    private function cache( $id, $product = null ) {
        if ( WPXSMARTSHOP_CACHE_PRODUCT && is_null( $product ) ) {
            return get_transient( 'wpxss_product_' . $id );
        } elseif ( WPXSMARTSHOP_CACHE_PRODUCT && is_a( $product, 'WPXSmartShopProduct' ) ) {
            set_transient( 'wpxss_product_' . $id, $product, WPXSMARTSHOP_CACHE_PRODUCT_TIMEOUT );
        } else {
            /* Non è stato passato un valido oggetto WPXSmartShopProduct. */
            return null;
        }
        return $product;
    }

    /// Get a id product from mixed input
    /**
     * Metodo polimorfico in grado di restituire l'id di un prodotto in base al parametro di input
     *
     * @static
     *
     * @param int|string|object|array $product Prodotto (id, object o array)
     *
     * @retval int ID del prodotto
     */
    public static function id( $product ) {
        if ( is_numeric( $product ) ) {
            $result = $product;
        } elseif ( is_object( $product ) && isset( $product->ID ) ) {
            $result = $product->ID;
        } elseif ( is_array( $product ) && isset( $product['ID'] ) ) {
            $result = $product['ID'];
        } else {
            $message = __( 'Wrong product parameter', WPXSMARTSHOP_TEXTDOMAIN );
            $error   = new WP_Error( 'wpss_error-product_wrong_product_parameter', $message, $product );
            return $error;
        }
        return absint( $result );
    }

    /// Get sizes for thumbnails images
    /**
     * Restituisce un array con l'elenco delle dimensioni delle thumbail dei prodotti. Questo array viene usato per dire
     * a WordPress quali image size deve registrare e in altre parti per le impostazioni.
     *
     * @static
     * @retval array
     */
    public static function imageSizes() {
        $sizes = array(
            kWPSmartShopThumbnailSizeSmallKey => array(
                'width'  => kWPSmartShopThumbnailSizeSmallWidth,
                'height' => kWPSmartShopThumbnailSizeSmallHeight,
                'crop'   => true
            ),            
            kWPSmartShopThumbnailSizeMediumKey => array(
                'width'  => kWPSmartShopThumbnailSizeMediumWidth,
                'height' => kWPSmartShopThumbnailSizeMediumHeight,
                'crop'   => true
            ),            
            kWPSmartShopThumbnailSizeLargeKey => array(
                'width'  => kWPSmartShopThumbnailSizeLargeWidth,
                'height' => kWPSmartShopThumbnailSizeLargeHeight,
                'crop'   => true
            ),
        );
        return $sizes;
    }

    /// Get the rows for product card
    /**
     * Restituisce un array con l'elenco dei blocchi div da usare nella costruzione della scheda prodotto. Qui abbiamo
     * anche un filtro in grado di alterare sia la sequenza sia la presenza di queste elementi rendendo la scheda del
     * prodotto (card) estremamente configurabile.
     *
     * @static
     * @retval array Elenco blocchi div usati per la costruzione della scheda prodotto
     */
    public static function rowsCard() {
        $rows = array(
            'html_product_types',
            'html_thumbnail',
            'open_link',
            'html_title',
            'html_excerpt',
            'close_link',
            'html_price',
            'html_display_permalink_button',
            'html_content',
            'html_appearance',
        );

        /**
         * @filters
         *
         * @param array $rows Elenco dei blocchi div usati per la costruzione della scheda prodotto
         */
        $rows = apply_filters( 'wpss_product_card_rows', $rows );

        return $rows;
    }

    /// Get the product title name
    /**
     * Shortcode di Commodity. Restituisce il titolo di un prodotto
     *
     * @static
     *
     * @param $attrs Array key=>valore con key = id, slug, title o sku
     *
     * @retval string Titolo
     */
    public static function title( $attrs ) {
        $result = false;
        $post   = self::product( $attrs );
        if ( $post ) {
            $result = apply_filters( 'the_title', $post->post_title );
        }
        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Terms
    // -----------------------------------------------------------------------------------------------------------------

    /// Get the product terms
    /**
     * Restituisce un array lineare semplice con la lista dei termini associati ad un prodotto. È possibile scegliere
     * se ottenre un array di ID, di slug o altre proprietà dell'oggetto terms.
     *
     * @static
     *
     * @param mixed  $product Prodotto
     * @param string $key     Una delle properietà qui sotto elencato. Default 'term_id'
     *
     * @code
     *  object(stdClass)#5231 (9) {
     *      ["term_id"] => string(2) "31"
     *      ["name"] => string(21) "Biglietti di Ingresso"
     *      ["slug"] => string(21) "biglietti-di-ingresso"
     *      ["term_group"] => string(1) "0"
     *      ["term_taxonomy_id"] => string(2) "33"
     *      ["taxonomy"] => string(21) "wpss-ctx-product-type"
     *      ["description"] => string(0) ""
     *      ["parent"] => string(1) "0"
     *      ["count"] => string(2) "18"
     *    }
     * @endcode
     *
     * @retval array|WP_Error
     *
     */
    public static function arrayTerms( $product, $key = 'term_id' ) {

        $id_product = self::id( $product );
        if ( is_wp_error( $id_product ) ) {
            return $id_product;
        }


        $result = array();
        $terms  = get_the_terms( $id_product, kWPSmartShopProductTypeTaxonomyKey );

        if ( $terms ) {
            foreach ( $terms as $term ) {
                if( property_exists($term, $key )) {
                    $result[] = $term->$key;
                }
            }
        }
        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // WordPress integration
    // -----------------------------------------------------------------------------------------------------------------

    /// Set the SmartShop thumbnail image sizes for WordPress integration
    /**
     * Registra delle nuovo image size in modo che WordPress possa eseguire lo scaling e il crop
     *
     * @static
     */
    public static function registerImageSizes() {
        $sizes = self::imageSizes();
        foreach ( $sizes as $key => $size ) {
            add_image_size( $key, $size['width'], $size['height'], $size['crop'] );
        }
    }


    // -----------------------------------------------------------------------------------------------------------------
    // WordPress thumbnail
    // -----------------------------------------------------------------------------------------------------------------

    /// Get the HTML img tag for thumbnail product
    /**
     * Restituisce la thumbnail di un prodotto
     *
     * @static
     *
     * @param mixed  $product Prodotto
     * @param string $size    Thumbnail size. Default kWPSmartShopThumbnailSizeSmallKey
     * @param string $default Se la traduzione e l'originale non hanno thumbnail viene restituito il place holder
     *
     * @retval string Restituisce l'html dell'immagine
     */
    public static function thumbnail( $product, $size = kWPSmartShopThumbnailSizeSmallKey, $default = '' ) {

        $id_product = self::id( $product );
        if ( is_wp_error( $id_product ) ) {
            return $id_product;
        }

        if ( has_post_thumbnail( $id_product ) ) {
            $post  = get_post( $id_product );
            $attrs = array(
                'alt'   => $post->post_title,
                'title' => $post->post_title,
            );
            return get_the_post_thumbnail( $id_product, $size, $attrs );
        } else {
            $id_product = WPXSmartShopWPML::originalProductID( $id_product );
            if ( has_post_thumbnail( $id_product ) ) {
                $post  = get_post( $id_product );
                $attrs = array(
                    'alt'   => $post->post_title,
                    'title' => $post->post_title,
                );
                return get_the_post_thumbnail( $id_product, $size, $attrs );
            }
        }

        $post = get_post( $id_product );

        /* Constraint to image size */
        $size_attr  = '';
        $image_size = wpdk_get_image_size( $size );
        if ( $image_size ) {
            $size_attr = image_hwstring( $image_size['width'], $image_size['height'] );
        }

        return ( $default == '' ) ?
            '<img class="wpss-product-thumbnail-placeholder" alt="' . $post->post_title . '" title="' .
                $post->post_title . '" ' . $size_attr . ' src="' . WPXSMARTSHOP_URL_CSS .
                'images/placeholder256x256.png"/>' : $default;
    }

    /// Get the src HTML attribute for thumbnail product
    /**
     * Restituisce l'src (url) della thumbnail di un prodotto
     *
     * @static
     *
     * @param mixed  $product Prodotto
     * @param string $size    Thumbnail size. Default kWPSmartShopThumbnailSizeSmallKey
     * @param string $default Se la traduzione e l'originale non hanno thumbnail viene restituito il place holder
     *
     * @retval  string  Restituisce l'src (url) dell'immagine
     *
     */
    public static function thumbnailSrc( $product, $size = kWPSmartShopThumbnailSizeSmallKey, $default = '' ) {

        $id_product = self::id( $product );
        if ( is_wp_error( $id_product ) ) {
            return $id_product;
        }

        if ( has_post_thumbnail( $id_product ) ) {
            $image_id = get_post_thumbnail_id( $id_product );
            $image    = wp_get_attachment_image_src( $image_id, $size );
            return $image[0];
        } else {
            $id_product = WPXSmartShopWPML::originalProductID( $id_product );
            if ( has_post_thumbnail( $id_product ) ) {
                $image_id = get_post_thumbnail_id( $id_product );
                $image    = wp_get_attachment_image_src( $image_id, $size );
                return $image[0];
            }
        }
        return ( $default == '' ) ? WPXSMARTSHOP_URL_CSS . 'images/placeholder256x256.png' : $default;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Post Meta: Shipping
    // -----------------------------------------------------------------------------------------------------------------

    /// Get the is shipping flag
    /**
     * Get/Set shipping flag post meta
     *
     * @static
     *
     * @param mixed  $product Prodotto
     * @param null   $value
     *
     * @retval mixed
     */
    public static function shipping( $product, $value = null ) {

        $id_product = self::id( $product );
        if ( is_wp_error( $id_product ) ) {
            return $id_product;
        }

        if ( is_null( $value ) ) {
            return get_post_meta( $id_product, 'wpss_product_is_shipping', true );
        } else {
            return update_post_meta( $id_product, 'wpss_product_is_shipping', $value );
        }
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Post Meta: Product Prices
    // -----------------------------------------------------------------------------------------------------------------

    /// Get the meta rules pruduct price
    /**
     * Get the meta rules price for a specify product
     *
     * @static
     *
     * @param int $id_product ID product
     *
     * @return array Ritorna l'array delle regole sul prezzo
     *
     * @code
     * array(2) {
     *   [0]=> array(7) {
     *     ["wpss-product-rule-id"]=> string(10) "bnm_role_8"
     *     ["date_from"]=> string(0) ""
     *     ["date_to"]=> string(0) ""
     *     ["price"]=> int(45)
     *     ["percentage"]=> string(7) "10.0000"
     *     ["qty"]=> int(0)
     *     ["abs_qty"]=> int(0)
     *   }
     *   [1]=> array(7) {
     *     ["wpss-product-rule-id"]=> int(-2)
     *     ["date_from"]=> string(0) ""
     *     ["date_to"]=> string(0) ""
     *     ["price"]=> string(5) "40.00"
     *     ["percentage"]=> int(20)
     *     ["qty"]=> int(0)
     *     ["abs_qty"]=> int(0)
     *   }
     * }
     * @endcode
     *
     * @todo Polimorfic input on id_product
     *
     */
    public static function priceRules( $id_product ) {
        $rules = unserialize( get_post_meta( $id_product, 'wpss_product_price_for_rules', true ) );
        return $rules;
    }

    /*
      * Qui di seguito una serie di metodi tra Commodity e funzionali che riguardano l'informazione del prezzo. Queste
      * utili in svariati contesti e permetteno di incapsulare al meglio la gestione dei prezzi, anche in vista della
      * gestione della moneta, tasse etc...
      * Tutte questi metodi restituiscono comunque il prezzo base che, a parte il caso di un prodotto gratuito, ci deve
      * sempre stare.
      */

    /// Get the product price
    /**
     * Questo metodo è la summa di tutti i metodi e regole del sistema, a parte il caso "discount". In questo caso viene
     * restituito il miglior prezzo per la circostanza, ovvero: viene controllato prima se esiste un prezzo diverso per
     * la data di oggi. Se così non è, cioè il prezzo è uguale a quello base, allora si verifica se esiste un prezzo per
     * l'utenza.
     * Questa è la funzione usata nel carrello. Nelle schede prodotto, invece, è possibile fornire uteriori informazioni
     * sui prezzi di un prodotto, come apputo ad esempio la possibilità di visualizzare il prezzo in sconto o per utenze
     * particolari notificando casomai all'utente "se diventi utente xxx potrai acquistare a yyy" e via dicendo.
     *
     * @static
     *
     * @param int|object|array $product    ID del prodotto, oggetto da result set, array da result set
     * @param int              $qty        Quantità
     * @param string           $id_variant ID della variante
     * @param int              $nth        Ennesimo da cui considerare $qty. Questo corrisponde a "ne ho già presi...".
     *
     * @retval float Prezzo comprensivo di variante se indicata
     */
    public static function price( $product, $qty = 1, $id_variant = '', $nth = 0 ) {

        self::$descriptionPriceRules = array();

        /* Prima di tutto rendo il metodo polimorfico */
        $id_product = self::id( $product );
        if ( is_wp_error( $id_product ) ) {
            return $id_product;
        }

        /* Se non esistessero le regole sul prezzo, questo prodotto lo pagherei come prezzo base */
        $base_price = self::priceBase( $id_product, $qty );

        /* Recupero eventuali regole, dopotutto potrebbero non esserci, che altereranno $result */
        $rules = self::priceRules( $id_product );

        if ( !empty( $rules ) ) {

            /* Azzero - visto che ci sono regole - le info sui prezzi */
            self::$descriptionPriceRules = array();

            /* Recupero oggetto utente per ottenere la lista dei ruoli */
            $id_user = get_current_user_id();
            $user    = new WP_User( $id_user );
            $roles   = $user->roles;
            $result  = 0;

            /* 1. User role with date range */
            foreach ( $rules as $rule ) {
                foreach ( $roles as $role ) {
                    if ( $role == $rule['wpss-product-rule-id'] &&
                        ( !empty( $rule['date_from'] ) || !empty( $rule['date_to'] ) )
                    ) {
                        $result = self::priceRule( $id_product, $rule, $qty, $nth );
                    }
                }
            }

            /* 2. User role with no date range */
            foreach ( $rules as $rule ) {
                foreach ( $roles as $role ) {
                    if ( $role == $rule['wpss-product-rule-id'] &&
                        ( empty( $rule['date_from'] ) && empty( $rule['date_to'] ) )
                    ) {
                        $result += self::priceRule( $id_product, $rule, $qty, $nth );
                        break;
                    }
                }
            }

            /* 3. Date range */
            if ( $qty > 0 ) {
                foreach ( $rules as $rule ) {
                    if ( $rule['wpss-product-rule-id'] == kWPSmartShopProductTypeRuleDatePrice ) {
                        $result += self::priceRule( $id_product, $rule, $qty, $nth );
                    }
                }
            }

            /* 4. Online with date range */
            if ( $qty > 0 ) {
                foreach ( $rules as $rule ) {
                    if ( $rule['wpss-product-rule-id'] == kWPSmartShopProductTypeRuleOnlinePrice &&
                        ( !empty( $rule['date_from'] ) || !empty( $rule['date_to'] ) )
                    ) {
                        $result += self::priceRule( $id_product, $rule, $qty, $nth );
                    }
                }
            }

            /* 5. Online with no date range */
            if ( $qty > 0 ) {
                foreach ( $rules as $rule ) {
                    if ( $rule['wpss-product-rule-id'] == kWPSmartShopProductTypeRuleOnlinePrice &&
                        ( empty( $rule['date_from'] ) && empty( $rule['date_to'] ) )
                    ) {
                        $result += self::priceRule( $id_product, $rule, $qty, $nth );
                        break;
                    }
                }
            }

            /* 6. Base price */
            if ( $qty > 0 ) {
                $result += self::priceBase( $id_product, $qty );
            }

        } else {
            $result = $base_price;
        }

        /* @todo Questa parte delle varianti andrà poi rifatta */
        if ( !empty( $id_variant ) ) {
            /* Recupero tutte le varianti di questo prodotto */
            $array = unserialize( get_post_meta( $id_product, 'wpss_product_appearance', true ) );

            /* Recupero la variante indicata */
            $variant = $array[$id_variant];

            /* Ha un prezzo o percentuale aggiuntiva? */
            if ( !empty( $variant['value'] ) ) {

                $value = $variant['value'];

                /* È una percentuale? */
                if ( WPXSmartShopCurrency::isPercentage( $value ) ) {
                    /* Recupero prezzo base prodotto */
                    $apply      = WPXSmartShopCurrency::sanitizeCurrency( $value );
                    $percentage = ( $result * $apply ) / 100;
                    $additional = floatval( $percentage );
                } else {
                    $additional = floatval( $value ) * $qty;
                }
                $result += $additional;
            }
        }

        /* Ritorna un float con il calcolo della qty */

        /**
         * @filters
         *
         * @param float $price Prezzo
         */

        return apply_filters( 'wpss_product_price', floatval( $result ) );

    }

    /// Restituisce il prezzo di un prodotto in base alle regole passate negli Inputs
    /**
     * Restituisce il prezzo di un prodotto in base alle regole passate negli Inputs
     *
     * @static
     *
     * @param int   $id_product ID del prodotto
     * @param array $rule       Array di regola
     * @param int   $qty        Quantità
     * @param int   $nth        Ennesimo elemento da cui partire, in pratica corrisponde a 'ne ho già presi...'
     *
     * @retval float Prezzo
     */
    public static function priceRule( $id_product, $rule, &$qty = 1, $nth = 0 ) {

        /* Tutte le regole sono per data, quindi la prima cosa è verificare se siamo nella data stabilita */
        $now       = mktime();
        $date_from = !empty( $rule['date_from'] ) ? WPDKDateTime::makeTimeFrom( 'Y-m-d H:i:s', $rule['date_from'] ) : $now;
        $date_to   = !empty( $rule['date_to'] ) ? WPDKDateTime::makeTimeFrom( 'Y-m-d H:i:s', $rule['date_to'] ) : $now;

        if ( $now >= $date_from && $now <= $date_to ) {
            
            /* Se la qty e la abs_qty per regola non sono impostate le pago tutte scontate */
            if ( empty( $rule['qty'] ) && empty( $rule['abs_qty'] ) ) {
                self::descriptionPriceRules( $rule['wpss-product-rule-id'], $qty, $rule['price'] );
                $price = floatval( $rule['price'] * $qty );
                $qty = 0; // avanzo nessuno
                return $price;
            }

            /* Se le quantità di blocco (quella ordine o quella x prodotto) sono valorizzate, devo stabile a quale
            quantità posso applicare la regola passata.
            */

            $max_qty = $qty;

            if ( !empty( $rule['qty'] ) ) {
                $rule_qty = $rule['qty'];
                /* Nel caso ($rule_qty - $nth) < 0 */
                $max_qty  = max( min( ( $rule_qty - $nth ), $qty ), 0);
            }

            if ( !empty( $rule['abs_qty'] ) ) {
                $rule_abs_qty = $rule['abs_qty'];

                /* Nella mia vita ne ho già acquistate */
                $abs_qty = WPXSmartShopSession::countOrderedProduct( $id_product );

                /* Nel caso ($rule_abs_qty - $abs_qty) < 0 */
                $max_qty = max( min( $max_qty, ( $rule_abs_qty - ($abs_qty + $nth) ) ), 0 );
            }

            /* Quindi acquistandone $max_qty ne rimangono*/
            $qty = max( ($qty - $max_qty), 0);

            if( $max_qty > 0) {
                self::descriptionPriceRules( $rule['wpss-product-rule-id'], $max_qty, $rule['price'] );
                $price = floatval( $rule['price'] ) * $max_qty;

                return floatval( $price );
            }
        }
        return 0;
    }


    /// Get base price
    /**
     * Restituisce il prezzo base di un prodotto
     *
     * @static
     *
     * @param int $id_product ID del prodotto
     * @param int $qty        Quantità
     *
     * @retval float
     */
    public static function priceBase( $id_product, $qty = 1 ) {
        $id_product = WPXSmartShopWPML::originalProductID( $id_product );
        $price      = floatval( get_post_meta( $id_product, 'wpss_product_base_price', true ) );
        self::descriptionPriceRules( 'base_price', $qty, $price );
        return floatval( $qty * $price );
    }

    /**
     * Carica nella proprietà statica $descriptionPriceRules le regole del prezzo per poter indicare meglio il dettaglio
     * su quantità diverse di un prodotto dove ogne singolo pezzo è pagato in modo diverso
     *
     * @static
     *
     * @param string $rule_id ID della regola
     * @param int    $qty     Quantità
     * @param float  $price   Prezzo per pezzo
     *
     */
    public static function descriptionPriceRules( $rule_id = null, $qty = null, $price = null ) {
        if ( is_null( $rule_id ) ) {
            self::$descriptionPriceRules = array();
        } elseif ( !empty( $qty ) ) {
            self::$price_rule                      = $rule_id;
            self::$descriptionPriceRules[$rule_id] = array(
                'qty'   => $qty,
                'price' => $price
            );
        }
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Price rules
    // -----------------------------------------------------------------------------------------------------------------

    /// Check the price rules and remove duplicate
    /**
     * Questo metodo viene usato prima di visualizzare le regole sul prezzo nel backend. Esegue una serie di controlli
     * sia sul tipo di campo sia su eventuali duplicati; stessa regola stesso tipo di prezzo. Ciò che fa differenza in
     * pratica è la data (il date range) di applicazione
     *
     * @static
     *
     * @param array $rules Regole sul prezzo
     *
     * @retval array Regole sul prezzo sanitizzate
     */
    public static function sanitizePriceRules( $rules ) {
        if ( !empty( $rules ) && is_array( $rules ) ) {

            function _sort( $a, $b ) {
                return strcmp( $a['wpss-product-rule-id'], $b['wpss-product-rule-id'] );
            }

            /* Prima di tutto sorto per regola */
            usort( $rules, '_sort' );

            /* Questa mi serve per capire se ci sono due regole in congflitto */
            $conflict = array();

            foreach( $rules as $key => &$rule ) {

                if ( isset( $conflict[$rule['wpss-product-rule-id']] ) ) {
                    foreach ( $conflict[$rule['wpss-product-rule-id']] as $conflict_rule ) {
                        if ( $conflict_rule['date_from'] == $rule['date_from'] &&
                            $conflict_rule['date_to'] == $rule['date_to']
                        ) {
                            unset( $rules[$key] );
                            break;
                        }
                    }
                } else {
                    $conflict[$rule['wpss-product-rule-id']][] = $rule;
                }

                if( !is_numeric( $rule['price'] ) ) {
                    $rule['price'] = WPXSmartShopCurrency::formatCurrency(0, true);
                }

                if( !is_numeric( $rule['percentage'] ) ) {
                    $rule['percentage'] = WPXSmartShopCurrency::formatPercentage(0, true);
                }

                if ( !is_numeric( $rule['qty'] ) ) {
                    $rule['qty'] = '';
                } elseif ( is_numeric( $rule['abs_qty'] ) && $rule['abs_qty'] < $rule['qty'] ) {
                    $rule['abs_qty'] = $rule['qty'];
                }

                if( !is_numeric( $rule['abs_qty'] ) ) {
                    $rule['abs_qty'] = '';
                }

                if ( !empty( $rule['date_from'] ) ) {
                    $rule['date_from'] = WPDKDateTime::formatFromFormat( $rule['date_from'], MYSQL_DATE_TIME, __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
                }

                if ( !empty( $rule['date_to'] ) ) {
                    $rule['date_to'] = WPDKDateTime::formatFromFormat( $rule['date_to'], MYSQL_DATE_TIME, __( 'm/d/Y H:i', WPXSMARTSHOP_TEXTDOMAIN ) );
                }
            }

        }

        return $rules;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // Price view
    // -----------------------------------------------------------------------------------------------------------------

    /// Get an HTML format for product price
    /**
     * Restituisce l'HTML di un prezzo formattato con classi e altre informazioni: prezzo, decimale + informazioni
     * sull'iva
     *
     * @static
     * @uses   WPXSmartShopCurrency::currencyHTML()
     *
     * @param int $id_product ID del prodotto
     * @param int $qty        Quantità
     * @param int $nth        Ennesimo elemento, usato solo quando $qty = 1
     *
     * @retval string HTML del prezzo formattato
     *
     * @todo   Questa è più un alias ma manca l'id della variante
     */
    public static function priceHTML( $id_product, $qty = 1, $nth = 1 ) {
        $price = self::price( $id_product, $qty, '', $nth );

        /* Riformatto il prezzo */
        $result = WPXSmartShopCurrency::currencyHTML( $price );

        return $result;
    }


    // -----------------------------------------------------------------------------------------------------------------
    // UI
    // -----------------------------------------------------------------------------------------------------------------

    /// Product Card
    /**
     * Restituisce l'html della scheda di un prodotto
     *
     * @todo       Da migliorare ed espandere da utilizzare dapperttutto, vedi shortcode, picker, drag & drop
     *
     * @todo       Aggiungere filtri per classe div principale e contenuti vari
     *
     * @static
     * @uses       self::rowsCard()
     *
     * @param int|object   $product ID del prodotto o oggetto prodotto
     * @param array        $args    Parametri variabili in array per configurare l'output della scheda prodotto
     *
     * @retval string HTML scheda prodotto
     *
     */
    public static function card( $product, $args = array() ) {

        /* Argomenti di default */
        $defaults = array(
            'rows_card'                  => array(), // Righe come da self::rowsCard()

            'thumbnail'                  => true,
            'thumbnail_size'             => kWPSmartShopThumbnailSizeMediumKey,
            'thumbnail_click_to_enlarge' => false,
            'thumbnail_fancy_box_class'  => 'thickbox',

            'permalink'                  => true,
            'display_permalink_button'   => false,
            'price'                      => true,
            'label_price'                => '',
            'content'                    => false,
            'excerpt'                    => false,
            'title'                      => true,

            'display_add_to_cart'        => true,
            'product_types'              => true,
            'product_types_prefix'       => '',
            'product_types_tree'         => true,

            'appearance'                 => true,
            'variants'                   => true,
            'variant_labels'             => array(), // Mappa le chiavi delle varianti con delle label custom - invece di usare i filtri o entrambi
            'exclude_appearance'         => array(),
            'exclude_variants'           => array(),
        );

        /* Merging */
        $args = wp_parse_args( $args, $defaults );

        $product = self::product( $product );
        $id_product = $product->ID;
        if ( is_wp_error( $id_product ) ) {
            return $id_product;
        }

        /**
         * @filters
         */
        $permalink = apply_filters( 'wpss_product_card_link_product', get_post_permalink( $id_product ), $id_product );

        /* Thumbnail */
        $html_thumbnail = '';
        if ( $args['thumbnail'] ) {
            $thumbnail = self::thumbnail( $id_product, $args['thumbnail_size'] );

            /* Click to enlarge #42 */
            if ( $args['thumbnail_click_to_enlarge'] ) {
                $full  = self::thumbnailSrc( $id_product, 'full' );
                $class = $args['thumbnail_fancy_box_class'];
                /* @todo Aggiungere filtro */
                $title     = apply_filters( 'the_title', $product->post_title );
                $thumbnail = sprintf( '<a href="%s" title="%s" class="wpss-product-card-thumbnail-enlarge %s">%s</a>', $full, $title, $class, $thumbnail );
            } elseif ( $args['permalink'] ) {
                /* @todo Aggiungere filtro */
                $title     = __( 'Show product', WPXSMARTSHOP_TEXTDOMAIN );
                $thumbnail = sprintf( '<a data-id_product="%s" href="%s" title="%s" class="wpss-product-card-link">%s</a>', $id_product, $permalink, $title, $thumbnail );
            }

            $html_thumbnail = sprintf( '<div class="wpss-product-card-thumbnail">%s</div>', $thumbnail );
        }

        /* Link */
        $open_link  = '';
        $close_link = '';
        if ( $args['permalink'] ) {
            $open_link  = sprintf( '<a class="wpss-product-card-link" href="%s">', $permalink );
            $close_link = '</a>';
        }

        /* Price */
        $html_price = '';
        if ( $args['price'] ) {

            $qty_cart   = WPXSmartShopSession::countProductWithID( $id_product );
            $price      = self::price( $id_product, 1, '', $qty_cart );
            $price_html = self::priceHTML( $id_product, 1, $qty_cart );

            /**
             * @filters
             */
            $class_price         = apply_filters( 'wpss_product_card_price_class', '', $id_product, $price );
            $args['label_price'] = apply_filters( 'wpss_product_card_price_label', $args['label_price'], $id_product, $price );
            $price_html          = apply_filters( 'wpss_product_card_price', $price_html, $id_product, $price );

            $html_price = sprintf( '<div class="wpss-product-card-price %s"><span class="wpss-product-card-label-price">%s</span>%s</div>', $class_price, $args['label_price'], $price_html );
        }

        /* Excerpt */
        $html_excerpt = '';
        if ( $args['excerpt'] ) {
            if ( !empty( $product->post_excerpt ) ) {
                $excerpt      = apply_filters( 'get_the_excerpt', $product->post_excerpt );
                $html_excerpt = sprintf( '<div class="wpss-product-card-excerpt">%s</div>', $excerpt );
            }
        }

        /* Content */
        $html_content = '';
        if($args['content']) {
            $html_content = sprintf('<div class="wpss-product-card-content">%s</div>', apply_filters('the_content', $product->post_content) );
        }

        /* Title */
        $html_title = '';
        if ( $args['title'] ) {
            /**
             * @filters
             */
            $the_title                = apply_filters( 'the_title', $product->post_title );
            $wpxss_product_card_title = apply_filters( 'wpxss_product_card_title', $the_title );
            $html_title               = sprintf( '<h4 class="wpdk-tooltip" title="%s">%s</h4>', $the_title, $wpxss_product_card_title );
        }

        $html_display_permalink_button = '';
        if ( $args['display_permalink_button'] ) {
            /* @todo Aggiungere filtro */
            $label                         = __( 'Details', WPXSMARTSHOP_TEXTDOMAIN );
            $html_display_permalink_button = sprintf( '<div class="wpss-product-card-display_permalink_button"><a
            href="%s">%s</a></div>', $permalink, $label );
        }

        /* Product Types */
        $html_product_types = '';
        if ( $args['product_types'] ) {
            $param      = array();
            $terms      = wp_get_post_terms( $id_product, kWPSmartShopProductTypeTaxonomyKey, $param );
            $html_terms = array();
            foreach ( $terms as $term ) {
                if ( $args['product_types_tree'] || ( !$args['product_types_tree'] && empty( $term->parent ) ) ) {

                    /**
                     * @filters
                     */
                    $link_term = apply_filters( 'wpss_product_card_link_product_type', get_term_link( $term ), $term->term_id, $id_product );
                    $html_terms[] = sprintf( '<a data-term_id="%s" href="%s">%s</a>', $term->term_id, $link_term, apply_filters( 'the_category', $term->name ) );
                }
            }
            $html_product_types = sprintf( '<div class="wpss-product-card-terms">%s%s</div>',
                $args['product_types_prefix'], join( ' | ',
                $html_terms ) );
        }

        /* Appearance and Variants */
        $html_appearance = self::htmlAppearanceAndVariants( $id_product, $args );

        if( empty( $args['rows_card'] ) ) {
            $rows = self::rowsCard();
        } else {
            $rows = $args['rows_card'];
        }

        $body = '';
        foreach ( $rows as $row ) {
            $row_html = isset( ${$row} ) ? ${$row} : '';

            /**
             * @filters
             *
             * @param string $row_html   Contenuto del blocco div
             * @param int    $id_product ID del prodotto
             */
            $body .= apply_filters( "wpss_product_card_div_{$row}", $row_html, $id_product );
        }

        /* Encoding $args for Ajax */
        $data_args  = base64_encode( serialize( $args ) );

        $html = <<< HTML
    <div data-id_product="{$id_product}" data-args="{$data_args}" class="wpss-product-card clearfix">
        {$body}
    </div>
HTML;
        return $html;
    }
    
    
    // -----------------------------------------------------------------------------------------------------------------
    // Database
    // -----------------------------------------------------------------------------------------------------------------

    /// Get a product object record
    /**
     * Restituisce il puntatore ad un oggetto post di tipo prodotto. Questo metodo, oltre ad essere comodo, viene usato
     * soprattutto dagli shortcode. Il prodotto viene sempre restituito a prescindere dal suo stato di disponibilità.
     *
     * @static
     *
     * @param array | int $attrs Un array con i parametri di ricerca prodotto che possono essere: id, slug,
     *                           titolo e sku, oppure l'id del prodotto
     *
     * @retval object|bool Restituisce un oggetto post oppure false se il prodotto non è disponibile
     *
     */
    public static function product( $attrs, $meta = true ) {

        /* Nel caso abbiamo passata già un prodotto */
        if ( is_object( $attrs ) && is_a( $attrs, 'stdClass' ) && isset( $attrs->ID ) ) {
            return $attrs;
        }

        $post = false;

        if ( !function_exists( 'internal_product_post_meta' ) ) {
            function internal_product_post_meta( $fields ) {
                global $wpdb;
                $fields .= sprintf( ', %s.meta_key, %s.meta_value ', $wpdb->postmeta, $wpdb->postmeta );
                return ( $fields );
            }
        }

        /* Aggiungo un filtro in modo da estrarre anche le colonne dei post meta. */
        add_filter( 'posts_fields', 'internal_product_post_meta', 10, 1 );

        if ( is_numeric( $attrs ) ) {
            $id_product = absint( $attrs );
            $post       = get_post( absint( $id_product ) );
            if ( $post && $meta ) {
                $post->post_meta = get_post_custom( $post->ID );
            }
            return $post;
        }

        if ( is_array( $attrs ) ) {
            if ( isset( $attrs['id'] ) ) {
                $post = get_post( $attrs['id'] );
            } elseif ( isset( $attrs['slug'] ) ) {
                $post = get_page_by_path( $attrs['slug'], OBJECT, WPXSMARTSHOP_PRODUCT_POST_KEY );
            } elseif ( isset( $attrs['title'] ) ) {
                $post = get_page_by_title( $attrs['title'], OBJECT, WPXSMARTSHOP_PRODUCT_POST_KEY );
            } elseif ( isset( $attrs['sku'] ) ) {
                $meta_query = array(
                    array(
                        'key'     => 'wpss_product_sku',
                        'value'   => $attrs['sku'],
                        'type'    => 'string',
                        'compare' => '='
                    )
                );

                $args  = array(
                    'post_status'      => 'publish',
                    'suppress_filters' => false,
                    'post_type'        => WPXSMARTSHOP_PRODUCT_POST_KEY,
                    'meta_query'       => $meta_query
                );
                $posts = get_posts( $args );
                if( !empty( $posts ) ) {
                    $post  = $posts[0];
                }
            }
        }

        if( $meta && $post ) {
            $post->post_meta = get_post_custom( $post->ID );
        }

        return $post;
    }

    /// Get an array of product object record
    /**
     * Restituisce una serie di prodotti identificati dagli ID passati negl inputs
     *
     * @static
     *
     * @param array $IDs Array di id prodotto
     *
     * @retval array Array di record dal database
     */
    public static function productsWithID( $IDs ) {
        $args     = array(
            'post__in'    => $IDs,
            'order'       => 'ASC',
            'orderby'     => 'title',
            'post_type'   => WPXSMARTSHOP_PRODUCT_POST_KEY,
        );
        $products = get_posts( $args );
        return $products;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // Appearance and Variants
    // -----------------------------------------------------------------------------------------------------------------

    /// Get appearance fields
    /**
     * Restituisce l'array dei campi che possono essere singoli (appearance) o multipli (varianti),
     * escluso ovviamente il campo 'value' che viene controllato a parte.
     *
     * @static
     * @retval array
     */
    public static function appearanceFields() {
        $result = array(
            'weight'    => __( 'Weight', WPXSMARTSHOP_TEXTDOMAIN ),
            'width'     => __( 'Width', WPXSMARTSHOP_TEXTDOMAIN ),
            'height'    => __( 'Height', WPXSMARTSHOP_TEXTDOMAIN ),
            'depth'     => __( 'Depth', WPXSMARTSHOP_TEXTDOMAIN ),
            'volume'    => __( 'Volume', WPXSMARTSHOP_TEXTDOMAIN ),
            'color'     => __( 'Color', WPXSMARTSHOP_TEXTDOMAIN ),
            'material'  => __( 'Material', WPXSMARTSHOP_TEXTDOMAIN ),
            'model'     => __( 'Model', WPXSMARTSHOP_TEXTDOMAIN ),
        );
        return $result;
    }

    // -----------------------------------------------------------------------------------------------------------------
    // UI Helper
    // -----------------------------------------------------------------------------------------------------------------

    /**
     * Restituisce l'html di Aspetto e Varianti; usato nella scheda prodotto
     *
     * @static
     *
     * @param mixed   $product    Prodotto
     * @param array   $args       Parametri di visualizzazione
     *
     * @retval string|WP_Error HTML
     */
    public static function htmlAppearanceAndVariants( $product, $args = array() ) {

        $id_product = self::id( $product );
        if ( is_wp_error( $id_product ) ) {
            return $id_product;
        }

        /* Argomenti di default */
        $defaults = array(
            'appearance'                 => true,
            'variants'                   => true,
        );
        /* Merging */
        $args = wp_parse_args( $args, $defaults );

        /* Absurd... */
        if ( !$args['appearance'] && !$args['variants'] ) {
            return '';
        }

        $result          = '';
        $html_appearance = '';
        $html_variants   = '';

        /* Opero sui post meta, quindi se WPML è presente recupero sempre l'id originale */
        $id_originale_product = WPXSmartShopWPML::originalProductID( $id_product );

        $array = unserialize( get_post_meta( $id_originale_product, 'wpss_product_appearance', true ) );

        if ( $args['appearance'] && !empty( $array ) ) {

            /* Prima visualizzo l'aspetto che dovrebbe essere uno solo */
            foreach ( $array as $key => $appearance ) {
                if ( !empty( $key ) && self::isAppearance( $appearance ) ) {
                    $html_appearance .= self::htmlAppearance( $key, $appearance );
                    break;
                }
            }

            if ( !empty( $html_appearance ) ) {
                /* @todo Aggiungere filtro */
                $html_title_appearance = sprintf( '<h3>%s</h3>', __( 'Appearance', WPXSMARTSHOP_TEXTDOMAIN ) );

                $result = <<< HTML
    <div class="wpss-product-appearance">
        {$html_title_appearance}
        {$html_appearance}
    </div>
HTML;
            }
        }

        if ( $args['variants'] && !empty( $array ) ) {

            /* Poi visualizzo tutte le possibili varianti */

            foreach ( $array as $key => $variant ) {
                if ( !empty( $key ) && !self::isAppearance( $variant ) ) {
                    $html_variants .= self::htmlVariants( $id_product, $key, $variant, $args );
                }
            }

            if ( !empty( $html_variants ) ) {

                /* @todo Aggiungere filtro */
                $html_title_variants = sprintf( '<h3>%s</h3>', __( 'Available in', WPXSMARTSHOP_TEXTDOMAIN ) );

                $result .= <<< HTML
    <div class="wpss-product-variant">
        {$html_title_variants}
        {$html_variants}
    </div>
HTML;
            }
        }

        if ( empty( $html_variants ) ) {

            /* Add to Shopping Cart */
            $html_add_to_cart = sprintf( '<div class="wpss-product-variant-add-to-cart">%s</div>', WPXSmartShopShoppingCart::buttonAddShoppingCart( $id_product ) );
//            if ( WPXSmartShopShoppingCart::canDisplayAddShoppingCart( $id_product ) ) {
//            } else {
//                $html_add_to_cart = WPXSmartShopShoppingCart::messageYouHaveToLogin();
//            }

            $result .= <<< HTML
    <form name="wpss-product-variant-form" id="wpss-product-variant-form" method="post" action="" class="wpdk-form">
        {$html_add_to_cart}
    </form>
HTML;
        }

        return $result;
    }

    /**
     * Restituisce l'html per la visualizzazione dell'aspetto di un prodotto. Questa è una scheda informativa che
     * mostra le caratteristiche di questo prodotto ma senza obbligare una scelta in quanto tali caratteristiche si
     * riferiscono al prodotto così com'è.
     *
     * @static
     *
     * @param string $id ID dell'aspetto
     * @param array $appearance Aspetto
     *
     * @retval string
     */
    private static function htmlAppearance( $id, $appearance ) {

        /* Costruisco la lista delle caratteristiche dell'aspetto */
        $html_li = '';
        $fields  = self::appearanceFields();
        foreach ( $fields as $key => $field ) {
            /* @todo Aggiungere filtro */
            $value = __( $appearance[$key], WPXSMARTSHOP_TEXTDOMAIN );
            /* @todo Aggiungere filtro */
            $field = __( $field, WPXSMARTSHOP_TEXTDOMAIN );
            if ( !empty( $value ) &&
                ( empty( $args['exclude_appearance'] ) || !in_array( $key, $args['exclude_appearance'] ) )
            ) {
                $html_li .= sprintf( '<dt class="%s">%s</dt><dd class="%s">%s</dd>',
                    'wpss-product-appearance-' . $key, $field, 'wpss-product-appearance-value-' . $key, $value );
            }
        }

        $html = <<< HTML
    <dl id="{$id}">
        {$html_li}
    </dl>
HTML;
        return $html;
    }

    /**
     * Restituisce l'html per la visualizzazione delle varianti di un prodotto. Questa è una scheda interattiva con
     * la scelta delle varie combinazioni disponibili come colore, taglia, materiale etc...
     *
     * @static
     *
     * @param mixed    $product    ID Prodotto
     * @param string   $id_variant ID della variante
     * @param array    $variant    Variante
     * @param array    $args
     *
     * @retval string|WP_Error
     */
    public static function htmlVariants( $product, $id_variant, $variant, $args ) {

        $id_product = self::id( $product );
        if ( is_wp_error( $id_product ) ) {
            return $id_product;
        }

        /* Costruisco la lista delle caratteristiche della variante */
        $html    = '';
        $html_li = '';
        $fields  = self::appearanceFields();

        foreach ( $fields as $key => $field ) {

            if( empty( $args['variant_labels'] ) ) {
                $field = __( $field, WPXSMARTSHOP_TEXTDOMAIN );
            } else {
                foreach( $args['variant_labels'] as $key_label => $label ) {
                    if( $key == $key_label ) {
                        $field = $label;
                        break;
                    }
                }
            }

            /**
             * Filtro sulla label della variante, quella che appare prima del valore o del combo.
             *
             * @filters
             *
             * @param string $field Stringa label (eventualmente) tradotta
             * @param string $key             Label originale
             */
            $field = apply_filters( 'wpss_product_variant_label', $field, $key );

            if ( !empty( $variant[$key] ) &&
                ( empty( $args['exclude_variants'] ) || !in_array( $key, $args['exclude_variants'] ) )
            ) {

                /* Costruisco l'eventuale combo */
                $values = explode( ',', $variant[$key] );
                $values = array_map( 'trim', $values );
                if ( count( $values ) > 1 ) {
                    $options = '';
                    foreach ( $values as $value ) {
                        $localizable_value = __( $value, WPXSMARTSHOP_TEXTDOMAIN );

                        /**
                         * @filters
                         *
                         * @param string $localizable_value
                         * @param int    $id_product
                         * @param string $id_variant
                         * @param array  $variant
                         * @param string $key
                         */
                        $localizable_value = apply_filters( 'wpss_product_variant_localizable_value', $localizable_value, $id_product, $id_variant, $variant, $key );

                        /**
                          * @filters
                          *
                          * @param string $localizable_value
                          * @param int    $id_product
                          * @param string $id_variant
                          * @param array  $variant
                          * @param string $key
                          */
                        $value = apply_filters( 'wpss_product_variant_value', $value, $id_product, $id_variant, $variant, $key );


                        /* @todo Path per remember last variant */
                        $selected = '';
                        if ( isset( $_SESSION['wpss_last_product_variant'] ) ) {
                            $product = unserialize( $_SESSION['wpss_last_product_variant'] );
                            if ( isset( $product['id_variant'] ) && isset( $product[$key] ) ) {
                                //if ( $product[$key] == $localizable_value ) {
                                if ( $product[$key] == $value ) {
                                    $selected = 'selected="selected"';
                                }
                            }
                        }

                        $options .= sprintf( '<option %s value="%s">%s</option>', $selected, $value, $localizable_value );
                    }
                    $html_value = sprintf( '<select class="wpdk-form-select" name="%s">%s</select>', $key, $options );
                } else {
                    /* @todo Aggiungere filtro */
                    $html_value = __( $values[0], WPXSMARTSHOP_TEXTDOMAIN );
                }

                if( empty( $field ) ) {
                    $html_li .= sprintf( '<dd class="%s">%s</dd>', 'wpss-product-variant-values-' . $key, $html_value );
                } else {
                    $html_li .= sprintf( '<dt class="%s">%s</dt><dd class="%s">%s</dd>',
                        'wpss-product-variant-' . $key, $field, 'wpss-product-variant-values-' . $key, $html_value );
                }

            }
        }

        if ( !empty( $html_li ) ) {

            /* Add to Shopping Cart */

            $html_add_to_cart = sprintf( '<div class="wpss-product-variant-add-to-cart">%s</div>', WPXSmartShopShoppingCart::buttonAddShoppingCart( $id_product, $id_variant ) );
//            if ( WPXSmartShopShoppingCart::canDisplayAddShoppingCart($id_product, $id_variant) ) {
//            } else {
//                $html_add_to_cart = WPXSmartShopShoppingCart::messageYouHaveToLogin();
//            }

            $html = <<< HTML
    <form name="wpss-product-variant-form-{$id_variant}" id="wpss-product-variant-form-{$id_variant}" method="post" action="" class="wpdk-form">
        <dl>
            {$html_li}
        </dl>
        {$html_add_to_cart}
    </form>
HTML;
        }

        return $html;
    }


    /* @todo Prototipo
     *
     * @param $id_product
     * @param $id_variant
     * @param $field
     * @param $variants
     *
     * @retval string
     */
    public static function variantCustom( $product, $id_variant, $field, $variants ) {

        $id_product = self::id( $product );
        if ( is_wp_error( $id_product ) ) {
            return $id_product;
        }

        $options = '';
        foreach( $variants as $variant ) {
            $options .= sprintf( '<option value="%s">%s</option>', $variant, $variant );
        }

        $html_add_to_cart = sprintf( '<div class="wpss-product-variant-add-to-cart">%s</div>', WPXSmartShopShoppingCart::buttonAddShoppingCart( $id_product, $id_variant ) );
//        if ( WPXSmartShopShoppingCart::canDisplayAddShoppingCart( $id_product, $id_variant ) ) {
//        } else {
//            $html_add_to_cart = WPXSmartShopShoppingCart::messageYouHaveToLogin();
//        }

        $html_variant = <<< HTML
    <select class="wpdk-form-select" name="{$field}">
        {$options}
    </select>
HTML;

        $html = <<< HTML
<form name="wpss-product-variant-form-{$id_variant}" id="wpss-product-variant-form-{$id_variant}" method="post" action="" class="wpdk-form">
    {$html_add_to_cart}
    {$html_variant}
</form>
HTML;
        return $html;

    }


    // -----------------------------------------------------------------------------------------------------------------
    // has/is zone
    // -----------------------------------------------------------------------------------------------------------------

    /// Check if a product has any variants
    /**
     * Controlla che il prodotto con id $id_product abbia la variante o variante e campo o variante, campo e valore
     * uguali a quelli passati negli inputs
     *
     * @static
     *
     * @param mixed    $product    Prodotto
     * @param string   $id_variant ID della variante
     * @param string   $field      ID del campo variante
     * @param mixed    $value      Valore del campo
     *
     * @retval bool|WP_Error
     * Restituisce true se la variante del prodotto rispetta almeno uno dei parametri d'ingresso
     */
    public static function hasProductVariant( $product, $id_variant, $field = null, $value = null ) {

        $id_product = self::id( $product );
        if ( is_wp_error( $id_product ) ) {
            return $id_product;
        }

        $result = false;
        $array  = unserialize( get_post_meta( $id_product, 'wpss_product_appearance', true ) );

        if ( !empty( $array ) ) {
            foreach ( $array as $key => $variant ) {
                if ( !empty( $key ) && !self::isAppearance( $variant ) ) {
                    if ( !is_null( $value ) && !is_null( $field ) && isset( $variant[$field] ) ) {
                        $values = explode( ',', $variant[$field] );
                        if ( $id_variant == $key && in_array( $value, $values ) ) {
                            $result = true;
                            break;
                        }
                    } elseif ( !is_null( $field ) ) {
                        if ( $id_variant == $key && !empty( $variant[$field] ) ) {
                            $result = true;
                            break;
                        }
                    } elseif ( $id_variant == $key ) {
                        $result = true;
                        break;
                    }
                }
            }
        }
        return $result;
    }

    /// Is appearance
    /**
     * Restituisce true se l'appearance passato non contiene 'value' e non ha valori multipli (separati da virgola)
     *
     * @static
     *
     * @param array $appearance Appearance o Variant
     *
     * @retval bool
     */
    private static function isAppearance( $appearance ) {

        /* Se 'value' non è vuoto abbiamo sempre una variante, cioè qualcosa di diverso da aggiungere al carrello */
        if ( !empty( $appearance['value'] ) ) {
            return false;
        }

        /* Se 'value' è vuoto, controllo tutti i campi se hanno valori multipli, ovvero valori separati da ',' */
        $fields = self::appearanceFields();
        $find   = ',';
        foreach ( $fields as $key => $field ) {
            if ( isset( $appearance[$key] ) && strpos( $appearance[$key], $find ) !== false ) {
                /* Trovato valore multiplo */
                return false;
            }
        }
        return true;
    }

    /// Is available
    /**
     * Determina se un prodotto è disponibile oppure no. La regala di disponibilità di un prodotto è la seguente:
     * Si controllano i campi "qty" (quantità) e data start e data end.
     * Se un prodotto è in bozza "draft" potrebbe comunque essere disponibile.
     * Questa esiste come funzione globale accessibile dapperttutto in quanto è usata da più punti.
     *
     * @static
     *
     * @param mixed $product Prodotto
     *
     * @retval bool|WP_Error True se il prodotto è disponibile, False se non disponibile. WP_Error in caso di errore
     *
     */
    public static function isAvailable( $product ) {

        $id_product = self::id( $product );
        if ( is_wp_error( $id_product ) ) {
            return $id_product;
        }

        /* WPML Compatibility - get original base language product id */
        $id_product = WPXSmartShopWPML::originalProductID( $id_product );

        $date_start_meta  = get_post_meta( $id_product, 'wpss_product_available_from_date', true );
        $date_expiry_meta = get_post_meta( $id_product, 'wpss_product_available_to_date', true );

        /* Magazzino */
        $warehouse = WPXSmartShopProduct::warehouse( $id_product );
        $qty       = $warehouse['qty'];

        if ( wpdk_is_infinity( $qty ) || $qty > 0 ) {
            $now         = mktime();
            $date_start  = $now;
            $date_expiry = $now;

            if ( !empty( $date_start_meta ) ) {
                $date_start = WPDKDateTime::makeTimeFrom( 'Y-m-d H:i:s', $date_start_meta );
            }

            if ( !empty( $date_expiry_meta ) ) {
                $date_expiry = WPDKDateTime::makeTimeFrom( 'Y-m-d H:i:s', $date_expiry_meta );
            }
            $result = ( $now >= $date_start && $now <= $date_expiry );
            if ( !$result ) {
                /* @todo Filtro da documentare */
                $result = apply_filters( 'wpxss_product_date_expired', false, $id_product );
            }
            return $result;
        } else {
            /* @todo Filtro da documentare */
            $result = apply_filters( 'wpxss_product_stocks_sold_out', false, $id_product );
            return $result;
        }
    }

    /// Deprecated
    /**
     * Is available
     *
     * @static
     *
     * @deprecated Use isAvailable()
     *
     * @param $product
     *
     * @retval bool|WP_Error
     */
    public static function isProductAvailable( $product ) {
        _deprecated_function( __FUNCTION__, '1.0', 'isAvailable()' );
        return self::isAvailable( $product );
    }

    /// Is purchasable
    /**
     * Restituisce true se un prodotto è acquistabile
     *
     * @static
     *
     * @param mixed $product Prodotto
     *
     * @retval bool|int
     */
    public static function isPurchasable( $product ) {

        /* Recupero id del prodotto */
        $id_product = self::id( $product );
        if ( is_wp_error( $id_product ) ) {
            return $id_product;
        }

        $result                    = true;
        $wpxss_product_purchasable = get_post_meta( $id_product, 'wpxss_product_purchasable', true );

        if ( $wpxss_product_purchasable ) {
            $puchasable_by_role  = true;
            $purchasable_by_caps = true;
            if ( !empty( $wpxss_product_purchasable['roles'] ) ) {
                $puchasable_by_role = WPDKUser::hasCurrentUserRoles( $wpxss_product_purchasable['roles'][0] );
            }

            if ( !empty( $wpxss_product_purchasable['capabilities'] ) ) {
                $purchasable_by_caps = WPDKUser::hasCaps( $wpxss_product_purchasable['capabilities'] );
            }
            $result = ( $puchasable_by_role && $purchasable_by_caps );
        }
        return $result;
    }

    /// Deprecated
    /**
     * Restituisce la quantità di un prodotto disponibile in magazzino.
     *
     * @deprecated Use self::warehouse() instead
     *
     * @static
     * @uses       WPXSmartShop::settings()->orders_count_pending()
     *
     * @param int|object $product ID del prodotto o oggetto prodotto
     *
     * @retval int|string|WP_Error Se la quantità è illimitata restituisce WPDK_MATH_INFINITY, altrimenti contiene
     *             l'intero della differenza tra la quantità caricata e i prodotti confermati. A quest'ultimi possono
     *             venire aggiunti i prodotti in stato pending se da impostazioni da backend. Questo valore NON dovrebbe
     *             essere mai negativo.
     */
    public static function countWarehouse( $product ) {

        _deprecated_function( __FUNCTION__, '1.0', 'Use self::warehouse() instead' );

        return ( self::warehouse( $product ) );
    }

    /// Warehouse stocks
    /**
     * Restituisce una array keypair con tutte le informazioni sul magazzino
     *
     * @static
     *
     * @param int|object $product ID del prodotto o oggetto prodotto
     *
     * @retval array|string Restituisce WPDK_MATH_INFINITY se non esistono limiti al prodotto, oppure un array keypair
     * con tutte le informazioni sul magazzino
     *
     * 'product_store_quantity'
     * 'product_store_quantity_for_order_confirmed'
     * 'product_store_quantity_for_order_pending'
     * 'qty'
     */
    public static function warehouse( $product ) {
        $id_product = self::id( $product );
        if ( is_wp_error( $id_product ) ) {
            return $id_product;
        }

        /* Recupero da custom fields */
        $qty_store = get_post_meta( $id_product, 'wpss_product_store_quantity', true );

        /* Se non impostato significa senza limiti */
        if ( is_string( $qty_store ) && empty( $qty_store ) ) {
            $result = array(
                'qty' => WPDK_MATH_INFINITY
            );
            return $result;
        }

        $qty_confirmed = absint( get_post_meta( $id_product, 'wpss_product_store_quantity_for_order_confirmed', true ) );
        $qty_pending   = absint( get_post_meta( $id_product, 'wpss_product_store_quantity_for_order_pending', true ) );

        if ( WPXSmartShop::settings()->orders_count_pending() ) {
            $qty = intval( $qty_store - ( $qty_confirmed + $qty_pending ) );
        } else {
            $qty = intval( $qty_store - $qty_confirmed );
        }

        /* Preparo un array per restituire tutte le informazioni sul magazzino. */
        $result = array(
            'product_store_quantity'                     => $qty_store,
            'product_store_quantity_for_order_confirmed' => $qty_confirmed,
            'product_store_quantity_for_order_pending'   => $qty_pending,
            'qty'                                        => $qty
        );

        return $result;
    }
}
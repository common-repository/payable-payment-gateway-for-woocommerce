<?php

/**
 * Plugin Name: PAYable IPG WooCommerce
 * Plugin URI: http://woocommerce.com/products/woocommerce-extension/
 * Description: PAYable IPG allows you to accept payments on your WooCommerce store via Visa, MasterCard, AMEX, Diners Club and Discover.
 * Version: 1.2.7
 * Author: PAYable (Pvt) Ltd.
 * Author URI: https://www.payable.lk
 * Developer: Subashini Thanikaikumaran
 * Text Domain: woocommerce-extension
 * Domain Path: /languages
 *
 * Tested up to: 6.5.3
 * WC tested up to: 6.5.3
 *
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */

add_action('plugins_loaded', 'payable_init', 0);
define('PAYABLE_IMG', WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/assets/img/');

function payable_init()
{
    // Make sure WooCommerce is active
    if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) return;
    if (!class_exists('WC_Payment_Gateway')) return;

    add_action('woocommerce_checkout_process', 'payable_validations');

    /**
     * Checkout form validations 
     * */
    function validate_using_pattern($field_name, $pattern, $error_message)
    {
        $filtered_value = filter_input(INPUT_POST, $field_name);

        if (strlen(trim(preg_replace($pattern, '', $filtered_value))) > 0) {
            wc_add_notice(__($error_message), 'error');
        }
    }

    function validate_using_max_length($field_name, $max_val, $error_message)
    {
        $filtered_value = filter_input(INPUT_POST, $field_name);

        if (strlen($filtered_value) > $max_val) {
            wc_add_notice(__($error_message), 'error');
        }
    }

    function validate_using_min_length($field_name, $min_val, $error_message)
    {
        $filtered_value = filter_input(INPUT_POST, $field_name);

        if (strlen($filtered_value) < $min_val) {
            wc_add_notice(__($error_message), 'error');
        }
    }

    function validate_billing_first_name()
    {
        validate_using_pattern('billing_first_name', '/^[a-zA-Z0-9\\. ]*$/', 'Invalid format for Billing Details - Contact First Name.');
        validate_using_max_length('billing_first_name', 50, 'Billing Details - Contact First Name length should be less than or equal to 50.');
    }

    function validate_billing_last_name()
    {
        validate_using_pattern('billing_last_name', '/^[a-zA-Z0-9\\. ]*$/', 'Invalid format for Billing Details - Contact Last Name.');
        validate_using_max_length('billing_last_name', 50, 'Billing Details - Contact Last Name length should be less than or equal to 50.');
    }

    function validate_billing_phone()
    {
        $billing_phone = filter_input(INPUT_POST, 'billing_phone');
        if ($billing_phone != "" || $billing_phone != NULL) {
            validate_using_pattern('billing_phone', '/^[0-9\\+ ]*$/', 'Please enter a valid Billing Details - Customer Phone number [Format : 07XXXXXXXX OR +XXxxxxxxxxx].');
            validate_using_max_length('billing_phone', 15, 'Customer Phone cannot be more than 15 characters.');
            validate_using_min_length('billing_phone', 10, 'Customer Phone cannot be less than 10 characters.');
        }
    }
    function validate_billing_address_1()
    {
        validate_using_pattern('billing_address_1', '/^[a-zA-Z0-9&\.\-_\/,() ]*$/', 'Invalid format for Billing Address - Address Line 1.');
        validate_using_max_length('billing_address_1', 100, 'Billing Address - Address Line 1 length should be less than or equal to 100 characters.');
    }

    function validate_billing_address_2()
    {
        validate_using_pattern('billing_address_2', '/^[a-zA-Z0-9&\.\-_\/,() ]*$/', 'Invalid format for Billing Address - Address Line 2.');
        validate_using_max_length('billing_address_2', 100, 'Billing Address - Address Line 2 length should be less than or equal to 100 characters.');
    }


    function validate_billing_city()
    {
        validate_using_pattern('billing_city', '/^[a-zA-Z0-9\. ]*$/', 'Invalid format for Billing Address - City.');
        validate_using_max_length('billing_city', 100, 'Billing Address - City length should be less than or equal to 100 characters.');
    }


    function validate_billing_postal_code()
    {
        validate_using_pattern('billing_postcode', '/^[a-zA-Z0-9\- ]*$/', 'Invalid format for Billing Address - Postal Code.');
        validate_using_max_length('billing_postcode', 10, 'Billing Address - Postal Code length should be less than or equal to 10 characters.');
    }

    function validate_billing_company()
    {
        validate_using_pattern('billing_company', '/^[a-zA-Z0-9&\.\-\/,() ]*$/', 'Invalid format for Billing Details - Company Name.');
        validate_using_max_length('billing_company', 100, 'Billing Details - Company length should be less than or equal to 100 characters.');
    }

    function validate_billing_state()
    {
        validate_using_pattern('billing_state', '/^[a-zA-Z0-9\.- ]*$/', 'Invalid format for Billing Details - State / Province.');
        validate_using_max_length('billing_state', 25, 'Billing Details - State / Province length should be less than or equal to 25 characters.');
    }


    function validate_shipping_first_name()
    {
        validate_using_pattern('shipping_first_name', '/^[a-zA-Z0-9\\. ]*$/', 'Invalid format for Shipping Details - Contact First Name.');
        validate_using_max_length('shipping_first_name', 50, 'Shipping Details - Contact First Name length should be less than or equal to 50.');
    }
    function validate_shipping_last_name()
    {
        validate_using_pattern('shipping_last_name', '/^[a-zA-Z0-9\\. ]*$/', 'Invalid format for Shipping Details - Contact Last Name.');
        validate_using_max_length('shipping_last_name', 50, 'Shipping Details - Contact Last Name length should be less than or equal to 50.');
    }
    function validate_shipping_phone()
    {
        $shipping_phone = filter_input(INPUT_POST, 'shipping_phone');
        if ($shipping_phone != "" || $shipping_phone != NULL) {
            validate_using_pattern('shipping_phone', '/^[0-9\\+ ]*$/', 'Please enter a valid Shipping Address - Customer Phone number [Format : 07XXXXXXXX OR +XXxxxxxxxxx].');
            validate_using_max_length('shipping_phone', 15, 'Shipping Address - Customer Phone cannot be more than 15 characters.');
            validate_using_min_length('shipping_phone', 10, 'Shipping Address - Customer Phone cannot be less than 10 characters.');
        }
    }
    function validate_shipping_address_1()
    {
        validate_using_pattern('shipping_address_1', '/^[a-zA-Z0-9&\.\-_\/,() ]*$/', 'Invalid format for Shipping Address - Address Line 1.');
        validate_using_max_length('shipping_address_1', 100, 'Shipping Address - Address Line 1 length should be less than or equal to 100 characters.');
    }

    function validate_shipping_address_2()
    {
        validate_using_pattern('shipping_address_2', '/^[a-zA-Z0-9&\.\-_\/,() ]*$/', 'Invalid format for Shipping Address - Address Line 2.');
        validate_using_max_length('shipping_address_2', 100, 'Shipping Address - Address Line 2 length should be less than or equal to 100 characters.');
    }


    function validate_shipping_city()
    {
        validate_using_pattern('shipping_city', '/^[a-zA-Z0-9\. ]*$/', 'Invalid format for Shipping Address - City.');
        validate_using_max_length('shipping_city', 100, 'Shipping Address - City length should be less than or equal to 100 characters.');
    }


    function validate_shipping_postal_code()
    {
        validate_using_pattern('shipping_postcode', '/^[a-zA-Z0-9\- ]*$/', 'Invalid format for Shipping Address - Postal Code.');
        validate_using_max_length('shipping_postcode', 10, 'Shipping Address - Postal Code length should be less than or equal to 10 characters.');
    }

    function validate_shipping_company()
    {
        validate_using_pattern('shipping_company', '/^[a-zA-Z0-9&\.\-\/,() ]*$/', 'Invalid format for Shipping Details - Company Name.');
        validate_using_max_length('shipping_company', 100, 'Shipping Details - Company length should be less than or equal to 100 characters.');
    }

    function validate_shipping_state()
    {
        validate_using_pattern('shipping_state', '/^[a-zA-Z0-9\.- ]*$/', 'Invalid format for Shipping Details - State / Province.');
        validate_using_max_length('shipping_state', 25, 'Shipping Details - State / Province length should be less than or equal to 25 characters.');
    }
    function payable_validations()
    {
        validate_billing_last_name();
        validate_billing_first_name();
        validate_billing_phone();
        validate_billing_address_1();
        validate_billing_address_2();
        validate_billing_city();
        validate_billing_postal_code();
        validate_billing_company();
        validate_billing_state();
        $shipping_checkbox = filter_input(INPUT_POST, 'ship_to_different_address');
        if ($shipping_checkbox == 1) {
            validate_shipping_first_name();
            validate_shipping_last_name();
            validate_shipping_phone();
            validate_shipping_address_1();
            validate_shipping_address_2();
            validate_shipping_city();
            validate_shipping_postal_code();
            validate_shipping_company();
            validate_shipping_state();
        }
    }





    class Payable_WC extends WC_Payment_Gateway
    {
        public function __construct()
        {
            $this->id                 = 'payable';
            $this->method_title       = 'PAYable';
            $this->method_description = 'WooCommerce Payment Plugin of PAYable Payment Gateway.';
            $this->icon               = WP_PLUGIN_URL . "/" . plugin_basename(dirname(__FILE__)) . '/assets/img/logo.png';
            
            $this->has_fields         = false;
            
            $this->init_form_fields();
            $this->init_settings();
            

            $this->title       = $this->settings['title'];
            $this->description = $this->settings['description'];

            $envVal="pro";
            $paybalePaymentUrl="ipgpayment.payable.lk";

            $this->server_api = "https://".$paybalePaymentUrl.'/ipg/'.$envVal;
            
            $this->testmode = 'yes' === $this->settings['test_mode'];
            if ($this->settings['test_mode'] == 'yes') {
                $this->title       = $this->settings['title'] . '';
                $this->description = $this->settings['description'] . '<br/>(Sandbox mode is active. The payment will not be charged.)<br/>';
                $envVal="sandbox";
                $this->server_api = "https://".$envVal."".$paybalePaymentUrl.'/ipg/'.$envVal;               
            }

            

            $this->merchant_key = $this->testmode? $this->settings['test_merchant_key']: $this->settings['merchant_key'];
            $this->merchant_token = $this->testmode? $this->settings['test_merchant_token']: $this->settings['merchant_token'];
            $this->merchant_logo = $this->settings['merchant_logo'];
            // $this->redirect_page = $this->settings['redirect_page'];

            // redirect URL
            $this->redirect_page = "";
            $redirectPage = $this->settings['redirect_page']; // $this->get_option('redirect_page');
            if ($redirectPage != '' || $redirectPage != 0) {
                $this->redirect_page = get_permalink($redirectPage);
            } else {
                $this->redirect_page = get_site_url();
            }


            add_action('init', array(&$this, 'check_payable_response'));
            add_action('woocommerce_api_' . strtolower(get_class($this)), array($this, 'check_payable_response'));
            // add_action('woocommerce_thankyou', 'check_payable_response', 10);

            if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));
            } else {
                add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options'));
            }

            add_action('woocommerce_receipt_' . $this->id, array(&$this, 'receipt_page'));
        }




        /**
         * Admin page options
         */
        function init_form_fields()
        {
            error_log("Payable_WC init_form_fields");

            $this->form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'woo_payable'),
                    'type' => 'checkbox',
                    'label' => __('Enable PAYable', 'woo_payable'),
                    'default' => 'yes',
                    'description' => 'Show in the Payment List as a payment option'
                ),
                'title' => array(
                    'title' => __('Title', 'woo_payable'),
                    'type' => 'text',
                    'default' => __('Pay via PAYable', 'woo_payable'),
                    'description' => __('This controls the title which the user sees during checkout.', 'woo_payable'),
                    'desc_tip' => true
                ),
                'description' => array(
                    'title' => __('Description:', 'woo_payable'),
                    'type' => 'textarea',
                    'default' => __('Pay by Visa, MasterCard via PAYable.', 'woo_payable'),
                    'description' => __('This controls the description which the user sees during checkout.', 'woo_payable'),
                    'desc_tip' => true
                ),
                'test_merchant_key' => array(
                    'title' => __('Test Merchant Key', 'woo_payable'),
                    'type' => 'text',
                    'description' => __('Your PAYable Test Merchant Key'),
                    'desc_tip' => true
                ),
                'test_merchant_token' => array(
                    'title' => __('Test Merchant Token', 'woo_payable'),
                    'type' => 'text',
                    'description' => __('Your PAYable Test Merchant Token'),
                    'desc_tip' => true
                ),
                'merchant_key' => array(
                    'title' => __('Merchant Key', 'woo_payable'),
                    'type' => 'text',
                    'description' => __('Your PAYable Merchant Key'),
                    'desc_tip' => true
                ),
                'merchant_token' => array(
                    'title' => __('Merchant Token', 'woo_payable'),
                    'type' => 'text',
                    'description' => __('Your PAYable Merchant Token'),
                    'desc_tip' => true
                ),
                'merchant_logo' => array(
                    'title' => __('Merchant Logo URL', 'woo_payable'),
                    'type' => 'text',
                    'description' => __('Your PAYable Merchant Logo URL'),
                    'desc_tip' => true
                ),
                'test_mode' => array(
                    'title' => __('Sandbox Mode', 'woo_payable'),
                    'type' => 'checkbox',
                    'label' => __('Enable Sandbox Mode', 'woo_payable'),
                    'default' => 'yes',
                    'description' => __('PAYable sandbox can be used to test payments', 'woo_payable'),
                    'desc_tip' => true
                ),
                'redirect_page' => array(
                    'title' => __('Return Page'),
                    'type' => 'select',
                    'options' => $this->get_wordpress_page_list('Select Page'),
                    'description' => __('Page to redirect the customer after payment', 'woo_payable'),
                    'desc_tip' => true
                )
            );
        }

        /**
         * Get Page list from WordPress
         **/
        function get_wordpress_page_list($title = false, $indent = true)
        {
            $wp_pages  = get_pages('sort_column=menu_order');
            $page_list = array();
            if ($title)
                $page_list[] = $title;
            foreach ($wp_pages as $page) {
                $prefix = '';
                // show indented child pages?
                if ($indent) {
                    $has_parent = $page->post_parent;
                    while ($has_parent) {
                        $prefix .= ' - ';
                        $next_page  = get_post($has_parent);
                        $has_parent = $next_page->post_parent;
                    }
                }
                // add to page list array array
                $page_list[$page->ID] = $prefix . $page->post_title;
            }
            return $page_list;
        }

        function get_logo_url()
        {
            return esc_url(wp_get_attachment_image_src(get_theme_mod('custom_logo'), 'full')[0]);
        }

        function get_receipt_page_url($order)
        {
            if (version_compare(WOOCOMMERCE_VERSION, '2.1.0', '>=')) {
                return $order->get_checkout_payment_url(true);
            } else {
                return get_permalink(get_option('woocommerce_pay_page_id'));
            }
        }

        /**
         * Output settings in the correct format.
         */
        public function admin_options()
        {
            echo '<h3>' . __('PAYable', 'woo_payable') . '</h3>';
            echo '<p>' . __('WooCommerce Payment Plugin of PAYable Payment Gateway.') . '</p>';
            echo '<table class="form-table">';
            $this->generate_settings_html();
            echo '</table>';
        }

        /**
         *  There are no payment fields, but we want to show the description if set.
         **/
        function payment_fields()
        {
            if ($this->description) {
                echo wpautop(wptexturize($this->description));
            }
        }

        /**
         * Handle payment and process the order.
         * Also tells WC where to redirect the user, and this is done with a returned array.
         * Redirect to PAYable
         **/
        function process_payment($order_id)
        {
            global $woocommerce;
            $order = new WC_Order($order_id);
            //$orderDetails = $order->get_data();
            // $redirect_url = $order->get_checkout_order_received_url();

            $redirect_url = $order->get_checkout_order_received_url();
            //$redirect_url = $this->redirect_page;

            $notify_url = add_query_arg('wc-api', get_class($this), $redirect_url);
           // $payment_page = get_post_meta($order->get_id(), 'payable_payment_page', true);

           

            // Redirect URL : For WooCoomerce 2.0
            if (version_compare(WOOCOMMERCE_VERSION, '2.0.0', '>=')) {
                $returnUrl = add_query_arg(array('order-id' => $order_id, 'wc-api' => strtolower(get_class($this))), $redirect_url);
              
            }
            

            
            $merchantKey = $this->merchant_key; // TODO release
            $merchantToken =  $this->merchant_token; // TODO release
            $merchantLogo = $this->merchant_logo;
            $invoiceId = (string) $order_id; // 'invo' . rand();//
            $amount = $order->get_total();
            $currencyCode = get_woocommerce_currency();
            $mToken = strtoupper(hash('sha512', $merchantToken));
            $val = $merchantKey . '|' .  $invoiceId . '|' .  $amount . '|' .  $currencyCode . '|' . $mToken;
            $checkValue = strtoupper(hash('sha512', $val));

            $payable_args = array(

                'invoiceId' => $invoiceId,
                'merchantKey' =>  $this->merchant_key,// TODO release
                'paymentType' => 1, 
                'integrationType' => 'woocommerce',
                'integrationVersion' => '1.2.7',

                
                'refererUrl' =>  get_site_url(), // TODO release
                'webhookUrl' => $notify_url,
                // 'logoUrl' => $this->get_logo_url(),
                'logoUrl' => $merchantLogo,
                'returnUrl' => $this->redirect_page,                
                'statusReturnUrl' => $this->server_api."/status-view",

                'checkValue' => $checkValue,
                'amount' => $amount,
                'currencyCode' => $currencyCode,
                'orderDescription' => 'Order - ' . $order->get_item_count() . ' items',

                'customerFirstName' => $order->get_billing_first_name(),
                'customerLastName' => $order->get_billing_last_name(),
                'customerEmail' => $order->get_billing_email(),
                'customerMobilePhone' => $order->get_billing_phone(),

                'billingAddressStreet' => $order->get_billing_address_1(),
                'billingAddressStreet2' => $order->get_billing_address_2(),
                'billingAddressCity' => $order->get_billing_city(),
                'billingAddressCountry' => $order->get_billing_country(),
                'billingAddressPostcodeZip' => (
                    ( '' !== $order->get_billing_postcode() ) ? $order->get_billing_postcode() : '0000'
                ),
                'billingAddressStateProvince' => $order->get_billing_state(),
                'billingCompanyName' => $order->get_billing_company(),

                'shippingContactFirstName' => $order->get_shipping_first_name() ?? "",
                'shippingContactLastName' => $order->get_shipping_last_name() ?? "",
                // 'shippingContactEmail' => $order->get_shipping_email() ?? "",//( ( $order->get_shipping_email() !== null ) ? $order->get_shipping_email() : '' ),
                'shippingContactMobilePhone' => $order->get_shipping_phone() ?? "",
                'shippingAddressStreet' => $order->get_shipping_address_1() ?? "",
                'shippingAddressStreet2' => $order->get_shipping_address_2() ?? "",
                'shippingAddressCity' => $order->get_shipping_city() ?? "",
                'shippingAddressCountry' => $order->get_shipping_country() ?? "",
                'shippingAddressPostcodeZip' => $order->get_shipping_postcode() ?? "",
                'shippingCompanyName' => $order->get_shipping_company(),
                'shippingAddressStateProvince' => $order->get_shipping_state() ?? "",
            );

            

            $post_headers = array(
                'Content-Type' => 'application/json'
            );

            $post_args = array(
                'method' => 'POST',
                'headers' => $post_headers,
                'timeout' => 45,
                'body' => json_encode($payable_args),
            );

            $response = wp_remote_post($this->server_api, $post_args);
            

            if (!is_wp_error($response)) {

                $response_body = json_decode($response['body'], true);

                if ($response_body['status'] && $response_body['status'] == 400 && $response_body['errors'] && !empty($response_body['errors'])) {
                    // Validation errors here                                       
                    foreach ($response_body['errors'] as $key => $value) {
                        if (isset($value[0])) {
                            wc_add_notice(($value[0]), 'error');
                        }
                    }
                } else if ($response_body['error'] || !$response_body['paymentPage']) {
                    // wc_add_notice('Error: ' . ($response_body['error'] ?? "Something went wrong, please try again"), 'error');
                    wc_add_notice('Error: Something went wrong, Please contact your merchant.');
                } else if ($response_body['error']['err-message'] || !$response_body['paymentPage']) {
                    // wc_add_notice('Error: ' . ($response_body['error']['err-message'] ?? "Something went wrong, please try again"), 'error');
                    wc_add_notice('Error: Something went wrong, Please contact your merchant.');
                } else {
                    $order->add_order_note('Payable payment page: <a target="_blank" href="' . $response_body['paymentPage'] . '">Payment Link</a>');
                   // update_post_meta($order->get_id(), 'payable_payment_page', $response_body['paymentPage']);

                    return array(
                        'result' => 'success',
                        'redirect' => $response_body['paymentPage']
                    );
                }
            } else {
                wc_add_notice('Something went wrong, Please contact your merchant.', 'error');
            }
        }

        /**
         * Show receipt details
         **/
        function receipt_page($order_id)
        {
            $order = new WC_Order($order_id);
            $payment_page = get_post_meta($order->get_id(), 'payable_payment_page', true);
            wp_redirect($payment_page);
        }

        /**
         * Check for valid gateway server callback
         **/

        function check_payable_response()
        {
            error_log(print_r($_REQUEST, true));
            global $woocommerce;
            
            $paymentDetails=[];
            $statusIndicator="";
            $uid="";
            $order_id="";
            $error = "Something went wrong. Please contact for inquiries.";

            if (isset($_SERVER['REQUEST_METHOD']) &&  'POST'=== $_SERVER['REQUEST_METHOD']) { // nortification callback
                $rawData = file_get_contents('php://input');
                $paymentDetails = json_decode($rawData, true);
            }
            if (isset($_SERVER['REQUEST_METHOD']) && 'GET' == $_SERVER['REQUEST_METHOD']) {
                if (isset($_REQUEST['uid']) && isset($_REQUEST['statusIndicator'])) {
                    $statusIndicator        = filter_input(INPUT_GET, 'statusIndicator');
                    $uid        = filter_input(INPUT_GET, 'uid');
                } 
                if (isset($_REQUEST['order-id'])){
                    $order_id        = filter_input(INPUT_GET, 'order-id');  
                }
            }

            if (empty($paymentDetails)) {   
                // call custom check()
                try {
                    $order = wc_get_order($order_id);
                    if ($order && 'completed' !== $order->get_status()) {
                        $payable_args = array(
                            'uid' => $uid,
                            'statusIndicator' => $statusIndicator
                        );

                        $post_headers = array(
                            'Content-Type' => 'application/json'
                        );

                        $post_args = array(
                            'method' => 'POST',
                            'headers' => $post_headers,
                            'timeout' => 45,
                            'body' => json_encode($payable_args),
                        );
                        $paymentResponse = wp_remote_post($this->server_api . '/check-status', $post_args); 
                        if (!is_wp_error($paymentResponse)) {
                            $paymentData = json_decode($paymentResponse['body'], true);
                            if (200 == $paymentData['status'] && $paymentData['data']) {
                                $paymentDetails = $paymentData['data'];
                            } else {
                                // Payable API status code error
                            }
                        } else {
                            // Payable API error
                        }
                    }
                } catch (Exception $e) {
                    //wc_add_notice('Error: ' . $error, 'error');                        
                }
            }  
                              
            if (!empty($paymentDetails)) {   
                $invoiceNo = $paymentDetails['invoiceNo'] ?? '';
                $statusCode = $paymentDetails['statusMessage']?? "";                                                                   
                $payment_id = $paymentDetails['payableOrderId'] ?? '';                                   
                $txnId = $paymentDetails['payableTransactionId'] ?? '';
                $paymentScheme = $paymentDetails['paymentScheme'] ?? '';
                $cardHolderName = $paymentDetails['cardHolderName'] ?? '';
                $cardNumber = $paymentDetails['cardNumber'] ?? '';

                if($order_id == null || $order_id!=""){
                    $order_id = $invoiceNo;
                }

                if($order_id!=null && $order_id!=""){ 
                    try {
                        $order = wc_get_order($order_id);
                        $orderId = $order->get_id();

                        if ($order && 'completed' !== $order->get_status()  && 'processing' !== $order->get_status()) {
                            // update order data
                            update_post_meta($orderId, 'payable_order_id', $payment_id);
                            update_post_meta($orderId, 'payable_transaction_id', $txnId);
                            update_post_meta($orderId, 'statusMessage', $statusCode);
                            
                            update_post_meta($orderId, 'paymentScheme', $paymentScheme);
                            update_post_meta($orderId, 'cardHolderName', $cardHolderName);
                            update_post_meta($orderId, 'cardNumber', $cardNumber);

                            if ("SUCCESS" == $statusCode) { 
                                $order->add_order_note('Payable payment successful. <br/>Payment ID: ' . $payment_id);
                                $order->payment_complete();
                                $woocommerce->cart->empty_cart();
                            } elseif("FAILURE" == $statusCode){
                                $order->add_order_note('Payable payment unsuccessful. <br/>Payment ID: ' . $payment_id);
                                $order->update_status('failed');
                            } elseif("ONHOLD" ==$statusCode){  
                                $order->add_order_note('Payable payment status is pending. <br/>Payment ID: ' . $payment_id);
                                $order->update_status( 'on-hold' );
                                $woocommerce->cart->empty_cart(); 
                            } else {
                                $order->add_order_note('FAILURE transaction ERROR. Status Code: ' . $statusCode.'.<br/>Payment ID: ' . $payment_id);
                            }

                            $data_to_return = array(
                                'status' => 200,
                                'woocommerceStatus' => 'Payment status updated.',
                            );
                            wp_send_json($data_to_return); 

                        } else {
                            $data_to_return = array(
                                'status' => 200,
                                'woocommerceStatus' => 'Payment data recieved.',
                            ); 
                            wp_send_json($data_to_return);
                        }
                    } catch (Exception $e) {
                        // wc_add_notice('Error: ' . $error, 'error');                        
                    } 
                }                   
            }
        }





        function check_payable_response_returnStausCheck()
        {
            error_log(print_r($_REQUEST, true));
            global $woocommerce;

            $error = "Something went wrong. Please contact for inquiries.";
            
           if (isset($_SERVER['REQUEST_METHOD']) &&  'POST'=== $_SERVER['REQUEST_METHOD']) { // nortification callback
                $rawData = file_get_contents('php://input');
                $paymentDetails = json_decode($rawData, true);
                
                if (isset($_REQUEST['uid']) && isset($_REQUEST['statusIndicator'])) {
                    $status_indicator        = filter_input(INPUT_GET, 'statusIndicator');
                    $uid        = filter_input(INPUT_GET, 'uid');
                }               
                
                if (!empty($paymentDetails)) {

                    $this->updateOrderStatus($paymentDetails,null, $uid, $status_indicator);
                    $data_to_return = array(
                        'status' => 200,
                        'woocommerceStatus' => 'Payment status updated',
                    ); 
                    wp_send_json($data_to_return);                 
                }
                
            } 
             // return url access
           if (isset($_SERVER['REQUEST_METHOD']) && 'GET' == $_SERVER['REQUEST_METHOD']) {
                if (isset($_REQUEST['order-id']) && isset($_REQUEST['uid']) && isset($_REQUEST['statusIndicator'])) {
                    $order_id        = filter_input(INPUT_GET, 'order-id');
                    $status_indicator        = filter_input(INPUT_GET, 'statusIndicator');
                    $uid        = filter_input(INPUT_GET, 'uid');

                   
                    try {
                        $order = wc_get_order($order_id);
                       // var_dump($order->get_status()); die();
                        if ($order && 'completed' !== $order->get_status()) {
                            $payable_args = array(
                                'uid' => $uid,
                                'statusIndicator' => $status_indicator
                            );

                            $post_headers = array(
                                'Content-Type' => 'application/json'
                            );

                            $post_args = array(
                                'method' => 'POST',
                                'headers' => $post_headers,
                                'timeout' => 45,
                                'body' => json_encode($payable_args),
                            );
                            $paymentResponse = wp_remote_post($this->server_api . '/check-status', $post_args);

                            if (!is_wp_error($paymentResponse)) {
                                $paymentData = json_decode($paymentResponse['body'], true);
                                if (200 == $paymentData['status'] && $paymentData['data']) {
                                    $paymentDetails = $paymentData['data'];                                     
                                    $this->updateOrderStatus($paymentDetails, $order_id, $uid, $status_indicator);
                                    wp_redirect($this->server_api . '/status-view?uid=' . $uid . '&statusIndicator=' . $status_indicator);
                                    
                                } else {
                                    if ($paymentData['error']) {
                                        $order->add_order_note('Payable payment error: ' . $error);
                                        $error = $paymentData['error'];
                                        wc_add_notice('Error: ' . $error, 'error');
                                    }
                                }
                            } else {
                                // payable API error
                                $order->add_order_note('Payable payment error: ' . $error);
                                wc_add_notice('Error: ' . $error, 'error');
                                
                            }
                        }
                    } catch (Exception $e) {

                        wc_add_notice('Error: ' . $error, 'error');
                        
                    }
                } else {
                    // Invalid GET
                    wc_add_notice('Error: ' . $error, 'error');
                   
                }
            } 
            
            wc_add_notice('Error: ' . $error, 'error');
           //return wp_redirect($this->redirect_page);
        }
   



        function updateOrderStatus($paymentDetails,$order_id,  $uid=null, $status_indicator=null){
            global $woocommerce;            
             
            $invoiceNo= $paymentDetails['invoiceNo'] ?? '';
            $statusCode = $paymentDetails['statusMessage']?? "";                                                                   
            $payment_id= $paymentDetails['payableOrderId'] ?? '';                                   
            $txnId= $paymentDetails['payableTransactionId'] ?? '';
            $paymentScheme= $paymentDetails['paymentScheme'] ?? '';
            $cardHolderName= $paymentDetails['cardHolderName'] ?? '';
            $cardNumber= $paymentDetails['cardNumber'] ?? '';
            
            if($order_id==null){
                $order_id=$invoiceNo;
            }
            if($order_id!=null && $order_id!=""){           

                $order = wc_get_order($order_id);
                $orderId=$order->get_id();
                // var_dump($order->get_status());
                //die(); 
                
                if ($order && 'completed' !== $order->get_status()  && 'processing' !== $order->get_status()) {

                    // update order data
                    update_post_meta($orderId, 'payable_order_id', $payment_id);
                    update_post_meta($orderId, 'payable_transaction_id', $txnId);
                    update_post_meta($orderId, 'statusMessage', $statusCode);
                    
                    update_post_meta($orderId, 'paymentScheme', $paymentScheme);
                    update_post_meta($orderId, 'cardHolderName', $cardHolderName);
                    update_post_meta($orderId, 'cardNumber', $cardNumber);                                   
                    
                    if ("SUCCESS" == $statusCode) { 
                        // success
                    
                        $order->add_order_note('Payable payment successful. <br/>Payment ID: ' . $payment_id);                
                        // if($uid!=null && $status_indicator!=null){
                        //     $status_page = $this->server_api . '/status-view?uid=' . $uid . '&statusIndicator=' . $status_indicator;
                        //     update_post_meta($order->get_id(), 'payable_payment_received', $status_page);
                        // }

                        $order->payment_complete();
                        $woocommerce->cart->empty_cart();

                    
                    } elseif("FAILURE" == $statusCode){
                        // failed
                        $order->add_order_note('Payable payment unsuccessful. <br/>Payment ID: ' . $payment_id);
                        $order->update_status( 'failed' );                                      
                    }
                    elseif("ONHOLD" ==$statusCode){
                        // on hold
                        $order->add_order_note('Payable payment status is pending. <br/>Payment ID: ' . $payment_id);
                        $order->update_status( 'on-hold' );
                        $woocommerce->cart->empty_cart();                                      
                    } else {
                        // Order Updated : Failed
                        // Need to check the Refund case
                        $order->add_order_note('FAILURE transaction ERROR. Status Code: ' . $statusCode.'.<br/>Payment ID: ' . $payment_id);
                    }
                } else {    
                    // If the nortific url get late to recive               
                    $updated_value = get_post_meta($orderId, 'payable_order_id', true);                    
                    if($updated_value=="" && "SUCCESS" == $statusCode){
                       // update order data
                        update_post_meta($orderId, 'payable_order_id', $payment_id);
                        update_post_meta($orderId, 'payable_transaction_id', $txnId);
                        update_post_meta($orderId, 'statusMessage', $statusCode);
                        update_post_meta($orderId, 'paymentScheme', $paymentScheme);
                        update_post_meta($orderId, 'cardHolderName', $cardHolderName);
                        update_post_meta($orderId, 'cardNumber', $cardNumber);                       
                    }
                }
            }
        }

    } //End Class



    /**
     * Add the PAYable to WooCommerce
     **/
    function woocommerce_add_gateway_payable_gateway($methods)
    {
        $methods[] = 'PAYable_WC';
        return $methods;
    }

    add_filter('woocommerce_payment_gateways', 'woocommerce_add_gateway_payable_gateway');
} // End function payable_init()



/**
 * 'Settings' link on plugin page
 **/
function payable_add_action_plugin($actions, $plugin_file)
{
    static $plugin;
    if (!isset($plugin))
        $plugin = plugin_basename(__FILE__);
    if ($plugin == $plugin_file) {
        $settings = array(
            'settings' => '<a href="admin.php?page=wc-settings&tab=checkout&section=payable">' . __('Settings') . '</a>'
        );
        $actions  = array_merge($settings, $actions);
    }
    return $actions;
}

add_filter('plugin_action_links', 'payable_add_action_plugin', 10, 5);

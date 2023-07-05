<?php
# Include namespaces
use MorkvaMonoGateway\Morkva_Mono_Order;
use MorkvaMonoGateway\Morkva_Mono_Payment;

/**
 * Class WC_Gateway_Morkva_Mono file
 * */
class WC_Gateway_Morkva_Mono extends WC_Payment_Gateway
{
    /**
     * @var string Token connect with monopay
     * */
    private $mrkv_mono_token;

    /**
     * Constructor for the gateway
     * */
    public function __construct()
    {
        # Load all classes monopay connection
        mrkv_mono_loadMonoLibrary();

        # Get settings        
        $this->id = 'morkva-monopay';
        $this->icon = apply_filters('woocommerce_mono_icon', '');
        $this->has_fields = true;
        $this->method_title = _x('Morkva Monobank Payment', 'morkva-monobank-extended');
        $this->method_description = __('Accept credit card payments on your website via Morkva Monobank payment gateway.', 'morkva-monobank-extended');
        $this->supports[] = 'refunds';

        # Load the settings
        $this->init_form_fields();
        $this->init_settings();

        # Get settings
        $this->title = $this->get_option('title');
        $this->description  = $this->get_option( 'description' );
        $this->mrkv_mono_token = $this->get_option('API_KEY');

        # Include functions
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
        add_action('woocommerce_api_morkva-monopay', array($this, 'mrkv_mono_callback_success'));
        
        # Callback function
        add_action('woocommerce_thankyou_'.$this->id, array( $this, 'return_handler' ) );

        # Add payment image
        add_filter( 'woocommerce_gateway_icon', array( $this, 'morkva_monopay_gateway_icon' ), 10, 2 );
    }

    /**
     * Initialise Gateway Settings Form Fields
     * 
     */
    public function init_form_fields() 
    {
        # Create fields gateway
        $this->form_fields = array(
            'enabled' => array(
                'title' => __( 'Enable/Disable', 'morkva-monobank-extended' ),
                'type' => 'checkbox',
                'label' => __( 'Enable Morkva Mono Payment', 'morkva-monobank-extended' ),
                'default' => 'yes'
            ),
            'title' => array(
                'title' => __( 'Title', 'morkva-monobank-extended' ),
                'type' => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'morkva-monobank-extended' ),
                'default' => '',
                'desc_tip' => true,
            ),
            'description' => array(
                'title' => __( 'Description', 'morkva-monobank-extended' ),
                'type' => 'textarea',
                'desc_tip' => true,
                'description' => __( 'This controls the description which the user sees during checkout.', 'morkva-monobank-extended' ),
            ),
            'API_KEY' => array(
                'title' => __( 'Api token', 'morkva-monobank-extended' ),
                'type' => 'text',
                'description' => __( 'You can find out your X-Token by the link: <a href="https://web.monobank.ua/" target="blank">web.monobank.ua</a>', 'morkva-monobank-extended' ),
                'default' => '',
            ),
            'url_monobank_img' => array(
                'title'       => __( 'URL Monobank Icon', 'morkva-monobank-extended' ),
                'type'        => 'text',
                'desc_tip'    => true,
                'description' => __( 'Enter full url to image', 'morkva-monobank-extended' ),
                'default'     => '',
            ),
            'hide_image' => array(
                'title' => __( 'Hide payment image', 'morkva-monobank-extended' ),
                'type' => 'checkbox',
                'label' => __( 'Check if need hide payment image', 'morkva-monobank-extended' ),
                'default' => 'no'
            ),
        );
    }

    /**
     * Process the payment and return the result.
     *
     * @param int $order_id Order ID
     * @return array Result query
     */
    public function process_payment( $order_id ) 
    {
        # Get user token
        $mrkv_mono_token = $this->mrkv_mono_getToken();

        # Include global woocommerce data
        global $woocommerce;

        # Get order data
        $mrkv_mono_order = new WC_Order( $order_id );

        # Get cart products
        $mrkv_mono_cart_info = $woocommerce->cart->get_cart();
        $mrkv_mono_basket_info = [];

        # Loop all Cart data
        foreach ($mrkv_mono_cart_info as $mrkv_mono_product) 
        {
            # Get and set product image
            $mrkv_mono_image_elem = $mrkv_mono_product['data']->get_image();
            $mrkv_mono_image = [];
            preg_match_all('/src="(.+)" class/', $mrkv_mono_image_elem, $mrkv_mono_image);

            # Set product data
            $mrkv_mono_basket_info[] = [
                "name" => $mrkv_mono_product['data']->name,
                "qty"  => intval($mrkv_mono_product['quantity']),
                "sum"  => round($mrkv_mono_product['line_total']*100),
                "icon" => $mrkv_mono_image[1][0],
                "code" => "" . $mrkv_mono_product['product_id']
            ];
        }

        # Set order data to send query
        $mrkvmonoOrder = new Morkva_Mono_Order();

        # Set data
        $mrkvmonoOrder->mrkv_mono_setCurrency($mrkv_mono_order->get_currency());
        $mrkvmonoOrder->mrkv_mono_setId($mrkv_mono_order->get_id());
        $mrkvmonoOrder->mrkv_mono_setReference($mrkv_mono_order->get_id());
        $mrkvmonoOrder->mrkv_mono_setAmount(round($mrkv_mono_order->get_total()*100));
        $mrkvmonoOrder->mrkv_mono_setBasketOrder($mrkv_mono_basket_info);

        # Check 
        $web_url = sanitize_text_field($_SERVER['HTTP_HOST']);
        if($web_url){
            $mrkvmonoOrder->mrkv_mono_setRedirectUrl('https://' . $web_url . '/checkout/order-received/' . $mrkv_mono_order->get_id() . '/?key=' . $mrkv_mono_order->get_order_key());
            $mrkvmonoOrder->mrkv_mono_setWebHookUrl('https://' . $_SERVER['HTTP_HOST'] . '/?wc-api=morkva-monopay');
        }

        # Create Payment object 
        $mrkv_mono_payment = new Morkva_Mono_Payment($mrkv_mono_token);
        $mrkv_mono_payment->mrkv_mono_setOrder($mrkvmonoOrder);

        # Check error
        try 
        {
            # Create invoice
            $mrkv_mono_invoice = $mrkv_mono_payment->mrkv_mono_create();
            # Check result
            if ( !empty($mrkv_mono_invoice) ) 
            {
                # Check status
                if ($mrkv_mono_order->get_status() != 'pending') 
                {
                    # Update status
                    $mrkv_mono_order->update_status('pending');
                }
            } 
            else 
            {
                # Show error
                throw new \Exception("Bad request");
            }
        } 
        catch (\Exception $e) 
        {
            # Show error notice
            wc_add_notice(  'Request error ('. $e->getMessage() . ')', 'error' );
            # Stop job
            return false;
        }

        # Return result
        return [
            'result'   => 'success',
            'redirect' => $mrkv_mono_invoice->pageUrl,
        ];
    }

    /**
     * Add custom gateway icon
     * 
     * @var string Icon
     * @var string Payment id
     * */
    function morkva_monopay_gateway_icon( $icon, $id ) {
        if ( $id === 'morkva-monopay' ) {
            if($this->get_option( 'hide_image' ) == 'no'){
                if($this->get_option( 'url_monobank_img' )){
                    return '<img src="' . $this->get_option( 'url_monobank_img' ) . '" > '; 
                }
                else{
                    return '<img height="24px" style="max-height:24px;" src="' . plugins_url( '../assets/images/monopay_light_bg.png', __FILE__ ) . '" > ';    
                }
            }
        } else {
            return $icon;
        }
    }

    /**
     * Add Callback function. Handle
     * */
    public function return_handler() 
    {
        # Main callback
        $this->mrkv_mono_callback_success();
    }

    /**
     * Callback success function
     * */
    public function mrkv_mono_callback_success() 
    {   
        # Get content
        $mrkv_mono_callback_json = @file_get_contents('php://input');

        # Get callback data
        $mrkv_mono_callback = json_decode($mrkv_mono_callback_json, true);

        # Check callback data
        if($mrkv_mono_callback){
            # Get response
            $mrkv_mono_response = new \MorkvaMonoGateway\Morkva_Mono_Response($mrkv_mono_callback);

            # Check status
            if($mrkv_mono_response->mrkv_mono_isComplete()) {
                global $woocommerce;

                $mrkv_mono_order_id = (int)$mrkv_mono_response->mrkv_mono_getOrderId();
                $mrkv_mono_order = new WC_Order( $mrkv_mono_order_id );

                $woocommerce->cart->empty_cart();

                $mrkv_mono_order->payment_complete($mrkv_mono_response->mrkv_mono_getInvoiceId());
            }
        }
    }

    /**
     * Function can refund
     * @param object Order data
     * @return mixed Data
     * */
    public function mrkv_mono_can_refund_order( $order ) 
    {
        # Get api key
        $mrkv_mono_has_api_creds = $this->get_option( 'API_KEY' );
        # Return data
        return $order && $order->get_transaction_id() && $mrkv_mono_has_api_creds;

    }

    /**
     * Function process refund
     * @var integer Order id
     * @var integer Order total
     * @var string Reason
     * @return Result 
     * */
    public function process_refund( $order_id, $amount = null, $reason = '' ) 
    {

        $mrkv_mono_order = wc_get_order( $order_id );
        $mrkv_mono_transaction_id = $mrkv_mono_order->get_transaction_id();

        if ( ! $this->mrkv_mono_can_refund_order( $mrkv_mono_order ) ) {
            return new WP_Error( 'error', __( 'Refund failed.', 'morkva-monobank-extended' ) );
        }

        $mrkv_mono_token = $this->mrkv_mono_getToken();
        $mrkv_mono_payment = new Morkva_Mono_Payment($mrkv_mono_token);
        $mrkv_mono_refund_order = array(
            "invoiceId" => $mrkv_mono_transaction_id,
            "amount" => $amount*100
        );
        $mrkv_mono_payment->mrkv_mono_setRefundOrder($mrkv_mono_refund_order);
        try {
            $mrkv_mono_result = $mrkv_mono_payment->mrkv_mono_cancel();
            if ( is_wp_error( $mrkv_mono_result ) ) {
                //$this->log( 'Refund Failed: ' . $result->get_error_message(), 'error' );
                return new WP_Error( 'error', $mrkv_mono_result->get_error_message() );
            }
            if ($mrkv_mono_result->stage == "c") {
                $mrkv_mono_order->add_order_note(
                    sprintf( __( 'Refunded %1$s - Refund ID: %2$s', 'morkva-monobank-extended' ), $amount, $mrkv_mono_result->cancelRef )
                );
                return true;
            }
        } catch (\Exception $e) {
            wc_add_notice('Request error (' . $e->getMessage() . ')', 'error');
            return false;
        }
        return false;
    }

    /**
     * Return settigs mono token
     * @return string Token
     * */
    protected function mrkv_mono_getToken() 
    {
        # Return monopay token
        return $this->mrkv_mono_token;
    }

}

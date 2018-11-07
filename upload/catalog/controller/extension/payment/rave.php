<?php 

class ControllerExtensionPaymentRave extends Controller {
    public function index()
    {
        $this->language->load('extension/payment/rave');

        $data['text_testmode'] = $this->language->get('text_testmode');
        $data['button_confirm'] = $this->language->get('button_confirm');

        $data['livemode'] = $this->config->get('rave_live');

       
        if ($this->config->get('rave_live')) {
            $this->document->addScript('https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js');
            $data['public_key'] = $this->config->get('rave_live_public_key');
            $data['script'] = 'https://api.ravepay.co/flwv3-pug/getpaidx/api/flwpbf-inline.js';
        } else {
            $this->document->addScript('https://ravesandboxapi.flutterwave.com/flwv3-pug/getpaidx/api/flwpbf-inline.js');
            $data['public_key'] = $this->config->get('rave_test_public_key');
             $data['script'] = 'https://ravesandboxapi.flutterwave.com/flwv3-pug/getpaidx/api/flwpbf-inline.js';
        }


        $this->load->model('checkout/order');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);

        if ($order_info) {

            $data['reference'] = uniqid('' . $this->session->data['order_id'] . '-');
            $data['amount'] = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);/// * 100;
            $data['email'] = $order_info['email'];
            $data['firstname'] = $order_info['firstname'];
            $data['phone']  = $order_info['telephone'];
            $data['custom_logo'] = $this->config->get('rave_modal_logo');
            $data['custom_title'] = $this->config->get('rave_modal_title');
            $data['custom_description'] = $this->config->get('rave_modal_desc');


            $data['lastname'] = $order_info['lastname'];
            $data['currency'] = $order_info['currency_code'];
            switch ($order_info['currency_code']) {
                case 'GHS':
                    $country = 'GH';
                    break;
                case 'KES':
                    $country = 'KE';
                    break;
                case 'ZAR':
                    $country = 'ZA';
                    break;
                default:
                    $country = 'NG';
                    break;
            }
            $data['country'] = $country;
            $data['callback_url'] = $this->url->link('extension/payment/rave/callback', 'reference=' . rawurlencode($data['reference']), 'SSL');

            if (file_exists(DIR_TEMPLATE . $this->config->get('config_template') . '/template/extension/payment/rave.tpl')) {
                return $this->load->view($this->config->get('config_template') . '/template/extension/payment/rave.tpl', $data);
            } else {
                return $this->load->view('extension/payment/rave.tpl', $data);

            }
        }
    }

    protected function verify_payment($reference)
    {
       if ($this->config->get('rave_live')) {
            $url =  'https://api.ravepay.co/flwv3-pug/getpaidx/api/verify';
            $secret_key = $this->config->get('rave_live_secret_key');
            
        } else {
            $url =  'https://ravesandboxapi.flutterwave.com/flwv3-pug/getpaidx/api/verify';
            $secret_key = $this->config->get('rave_test_secret_key');
            
        }
        
        $response = [];
        $postdata = array(
            'flw_ref' => $reference,
            'SECKEY' => $secret_key,
          'sslverify' => false
        );
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));                                              
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $headers = [
            'Content-Type: application/json',
        ];

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);

        $result =  json_decode($response, true);
        return $result;
    }


    public function callback()
    {
        $json = array();
        
        $this->load->model('checkout/order');
        
        if (isset($this->request->get['flw_reference'])) {
            $flw_reference = $this->request->get['flw_reference'];

            $response_api =  $this->verify_payment($flw_reference);
            $trxref = $response_api['data']['tx_ref'];
            $order_id = substr($trxref, 0, strpos($trxref, '-'));
            
            if(!$order_id) {
                $order_id = 0;
            }

            $order_info = $this->model_checkout_order->getOrder($order_id);
            if ($order_info) {

                $order_status_id = $this->config->get('config_order_status_id');
                $amount = $this->currency->format($order_info['total'], $order_info['currency_code'], $order_info['currency_value'], false);
                $currency = $order_info['currency_code'];
                
                if($response_api['data']['status'] === 'successful') {
                        
                    if(($amount ==  $response_api['data']['amount']) && ($currency ==  $response_api['data']['transaction_currency'])){ 
                        $order_status_id = $this->config->get('rave_approved_status_id');
                        $redir_url = $this->url->link('checkout/success');
                    }else{
                        $order_status_id = $this->config->get('rave_error_status_id');
                        $redir_url = $this->url->link('checkout/checkout', 'Invalid amount paid', 'SSL');
                    }
                }else {
                    $order_status_id = $this->config->get('rave_error_status_id');
                    $redir_url = $this->url->link('checkout/checkout');

                }

                $this->model_checkout_order->addOrderHistory($order_id, $order_status_id, "Transaction reference: ".$trxref);
                
                 $this->response->redirect($redir_url);

                 
                
            }
        }
      
    }

}

<?php
class ControllerExtensionPaymentRave extends Controller
{
    private $error = array();

    public function index() 
    {
        $this->load->language('extension/payment/rave');

        $this->document->setTitle($this->language->get('heading_title'));

        $this->load->model('setting/setting');
    
        
        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('rave', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/extension', 'token=' . $this->session->data['token']. '&type=payment', true));
        }

        $data['heading_title'] = $this->language->get('heading_title');
 
        $data['text_edit'] = $this->language->get('text_edit');
        $data['text_enabled'] = $this->language->get('text_enabled');
        $data['text_disabled'] = $this->language->get('text_disabled');
        $data['text_all_zones'] = $this->language->get('text_all_zones');
        $data['text_yes'] = $this->language->get('text_yes');
        $data['text_no'] = $this->language->get('text_no');
        $data['text_pay'] = $this->language->get('text_pay');
        $data['text_disable_payment'] = $this->language->get('text_disable_payment');
        
        $data['entry_test_public_key'] = $this->language->get('entry_test_public_key');
        $data['entry_test_secret_key'] = $this->language->get('entry_test_secret_key');
        $data['entry_live_public_key'] = $this->language->get('entry_live_public_key');
        $data['entry_live_secret_key'] = $this->language->get('entry_live_secret_key');
        
        
        $data['entry_live'] = $this->language->get('entry_live');
        $data['entry_debug'] = $this->language->get('entry_debug');
        $data['entry_total'] = $this->language->get('entry_total');
        $data['entry_modal_logo'] = $this->language->get('entry_modal_logo');
        $data['entry_modal_title'] = $this->language->get('entry_modal_title');
        $data['entry_modal_desc'] = $this->language->get('entry_modal_desc');
        $data['entry_meta_name'] = $this->language->get('entry_meta_name');
        $data['entry_meta_value'] = $this->language->get('entry_meta_value');
        $data['entry_approved_status'] = $this->language->get('entry_approved_status');
        $data['entry_declined_status'] = $this->language->get('entry_declined_status');
        $data['entry_error_status'] = $this->language->get('entry_error_status');
        $data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
        $data['entry_status'] = $this->language->get('entry_status');
        $data['entry_sort_order'] = $this->language->get('entry_sort_order');

        $data['help_live'] = $this->language->get('help_live');
        $data['help_debug'] = $this->language->get('help_debug');
        $data['help_total'] = $this->language->get('help_total');

        $data['error_permission'] = $this->language->get('error_permission');
        $data['error_test_public_key'] = $this->language->get('error_test_public_key');
        $data['error_test_secret_key'] = $this->language->get('error_test_secret_key');
        $data['error_live_public_key'] = $this->language->get('error_live_public_key');
        $data['error_live_secret_key'] = $this->language->get('error_live_secret_key');
        
        $data['button_save'] = $this->language->get('button_save');
        $data['button_cancel'] = $this->language->get('button_cancel');

        $data['tab_general'] = $this->language->get('tab_general');
        $data['tab_order_status'] = $this->language->get('tab_order_status');

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        if (isset($this->error['keys'])) {
            $data['error_keys'] = $this->error['keys'];
        } else {
            $data['error_keys'] = '';
        }

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
        'text' => $this->language->get('text_home'),
        'href' => $this->url->link('common/home', 'token=' . $this->session->data['token'], true)
        );

        $data['breadcrumbs'][] = array(
        'text' => $this->language->get('text_payment'),
        'href' => $this->url->link('extension/extension', 'token=' . $this->session->data['token'].'&type=payment', true)
        );

        $data['breadcrumbs'][] = array(
        'text' => $this->language->get('heading_title'),
        'href' => $this->url->link('extension/payment/rave', 'token=' . $this->session->data['token'], true)
        );

        $data['action'] = $this->url->link('extension/payment/rave', 'token=' . $this->session->data['token'], true);

        $data['cancel'] = $this->url->link('extension/payment/rave', 'token=' . $this->session->data['token'] . '&type=payment', true);

        $parameters  = array(
                'rave_test_public_key',
                'rave_live_public_key',
                'rave_test_secret_key',
                'rave_live_secret_key',
                'rave_live',
                'rave_total',
                'rave_modal_logo',
                'rave_modal_title',
                'rave_modal_desc',
                'rave_approved_status_id',
                'rave_declined_status_id',
                'rave_error_status_id',
                'rave_geo_zone_id',
                'rave_status',
                'rave_sort_order',


        );

        foreach ($parameters as $key => $param) {
           if (isset($this->request->post[$param])) {
                $data[$param] = $this->request->post[$param];
            } else {
                $data[$param] = $this->config->get($param);
            }
        }
    
        $this->load->model('localisation/order_status');

        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();


        $this->load->model('localisation/geo_zone');

        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();


        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/rave.tpl', $data));
    }
    
    private function validate() 
    {
        if (!$this->user->hasPermission('modify', 'extension/payment/rave')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }
        if($this->request->post['rave_live'] == 1){
            if (empty($this->request->post['rave_live_secret_key'])) {
             $this->error['keys']  = $this->language->get('error_live_secret_key');
            }

            if (empty($this->request->post['rave_live_public_key'])) {
                $this->error['keys'] = $this->language->get('error_live_public_key');
            }
        }else{
            if (empty($this->request->post['rave_test_secret_key'])) {
                 $this->error['keys']  = $this->language->get('error_test_secret_key');
            }
            if (empty($this->request->post['rave_test_public_key'])) {
                $this->error['keys'] = $this->language->get('error_test_public_key');
            }
        }
         return !$this->error;
    }
}
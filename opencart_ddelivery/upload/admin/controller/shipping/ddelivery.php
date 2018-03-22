<?php
class ControllerShippingDdelivery extends Controller {
	private $error = array();

	public function index() {
		$this->load->language('shipping/ddelivery');

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('setting/setting');
		
		$this->load->model('shipping/ddelivery');
		$adapter = new OcAdapter();
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->model_setting_setting->editSetting('ddelivery', $this->request->post);

			$this->session->data['success'] = $this->language->get('text_success');
			$key = $this->config->get('ddelivery_api_key');
			if(!empty($key)){
				$container = new \DDelivery\Adapter\Container(array('adapter'=>$adapter));
				$container->getBusiness()->initStorage();
			}
			$this->response->redirect($this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL'));
		}

		$data['heading_title'] = $this->language->get('heading_title');
		
		$data['text_edit'] = $this->language->get('text_edit');
		$data['text_enabled'] = $this->language->get('text_enabled');
		$data['text_disabled'] = $this->language->get('text_disabled');
		$data['text_all_zones'] = $this->language->get('text_all_zones');
		$data['text_none'] = $this->language->get('text_none');

		$data['entry_geo_zone'] = $this->language->get('entry_geo_zone');
		$data['entry_status'] = $this->language->get('entry_status');
		$data['entry_sort_order'] = $this->language->get('entry_sort_order');
		$data['entry_api_key'] = $this->language->get('entry_api_key');
		$data['entry_shop_id'] = $this->language->get('entry_shop_id');

		$data['button_save'] = $this->language->get('button_save');
		$data['button_cancel'] = $this->language->get('button_cancel');

		if (isset($this->error['warning'])) {
			$data['error_warning'] = $this->error['warning'];
		} else {
			$data['error_warning'] = '';
		}

		$data['breadcrumbs'] = array();

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('text_shipping'),
			'href' => $this->url->link('extension/shipping', 'token=' . $this->session->data['token'], 'SSL')
		);

		$data['breadcrumbs'][] = array(
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('shipping/ddelivery', 'token=' . $this->session->data['token'], true)
		);

		$data['action'] = $this->url->link('shipping/ddelivery', 'token=' . $this->session->data['token'], true);

		$data['cancel'] = $this->url->link('extension/shipping', 'token=' . $this->session->data['token'] . '&type=shipping', true);

		if (isset($this->request->post['ddelivery_geo_zone_id'])) {
			$data['ddelivery_geo_zone_id'] = $this->request->post['ddelivery_geo_zone_id'];
		} else {
			$data['ddelivery_geo_zone_id'] = $this->config->get('ddelivery_geo_zone_id');
		}

		if (isset($this->request->post['ddelivery_api_key'])) {
			$data['ddelivery_api_key'] = $this->request->post['ddelivery_api_key'];
		} else {
			$data['ddelivery_api_key'] = $this->config->get('ddelivery_api_key');
		}

		if (isset($this->request->post['ddelivery_shop_id'])) {
			$data['ddelivery_shop_id'] = $this->request->post['ddelivery_shop_id'];
		} else {
			$data['ddelivery_shop_id'] = $this->config->get('ddelivery_shop_id');
		}

		$this->load->model('localisation/geo_zone');

		$data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

		if (isset($this->request->post['ddelivery_status'])) {
			$data['ddelivery_status'] = $this->request->post['ddelivery_status'];
		} else {
			$data['ddelivery_status'] = $this->config->get('ddelivery_status');
		}

		if (isset($this->request->post['ddelivery_sort_order'])) {
			$data['ddelivery_sort_order'] = $this->request->post['ddelivery_sort_order'];
		} else {
			$data['ddelivery_sort_order'] = $this->config->get('ddelivery_sort_order');
		}

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		
		$data['ddelivery_link'] = (empty($data['ddelivery_api_key']))?'':$adapter->getEnterPoint()."?action=admin";
		
		$this->response->setOutput($this->load->view('shipping/ddelivery.tpl', $data));
	}

	protected function validate() {
		if (!$this->user->hasPermission('modify', 'shipping/ddelivery')) {
			$this->error['warning'] = $this->language->get('error_permission');
		}

		return !$this->error;
	}
}
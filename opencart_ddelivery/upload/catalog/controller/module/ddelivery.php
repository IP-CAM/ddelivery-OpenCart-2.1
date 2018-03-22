<?php 

class ControllerModuleDdelivery extends Controller {
	public function index() {
		$this->response->setOutput();
	}

	public function getToken() {

		$this->load->model('shipping/ddelivery');

		$token = $this->model_shipping_ddelivery->createSDK();

		$this->response->setOutput($token); 
	}

	public function setConfig() {
		if (isset($this->request->post['token'])) {
			$this->session->data['ddelivery_token'] = $this->request->post['token'];
		}
		
		if (isset($this->request->post['price'])) {
			$this->session->data['ddelivery_price'] = $this->request->post['price'];
			$this->session->data['shipping_method']['cost'] = $this->request->post['price'];
		}
		
		if (isset($this->request->post['order_id'])) {
			$this->session->data['ddelivery_order_id'] = $this->request->post['order_id'];
		}
	}

	public function getConfig() {
		echo $this->cart->getTotal();
		print_r($this->session->data);
		$this->response->setOutput(123);
	}

	public function ddeliveryEndpoint() {
		$this->load->model('shipping/ddelivery');

		$ddelivery = $this->model_shipping_ddelivery->createSDK();

		$this->response->setOutput($ddelivery); 
	}

	public function fordev() {
		print_r($this->session->data);
	}

}
?>
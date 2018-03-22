<?php
use DDelivery\Adapter\Container;
use DDelivery\Business\Business;

require('ddelivery/ddelivery_helper.php');

class ModelShippingDdelivery extends Model {

	function createAdapter() {
		$products = $this->cart->getProducts();
		$products_array = array();

        foreach ($products as $product) {
            $products_array[] = array(
                'id'        => $product['product_id'],
                'name'      => $product['name'],
                'price'     => $product['price'],
                'width'     => $product['width'],
                'height'    => $product['height'],
                'length'    => $product['length'],
                'weight'    => $product['weight'],
                'quantity'  => $product['quantity'],
                'sku'       => $product['model'],
            );
        }

		$adapter = new OcAdapter(array(
										'form' => $products_array,
								));
		return $adapter;
	}

	function createSDK() {
		$adapter = $this->createAdapter();
		$container = new Container(array('adapter' => $adapter));
		// trying to get request params from url like index.php?route=***/***/?action=someAction
		$request = $_REQUEST;
		$route = isset( $request[ 'route' ] ) ? (string) $request[ 'route' ] : '';
		if(strpos($route,'?')!==false){
			$query = explode('?',$route,2);
			$query = $query[1];
			$rez=array();
			parse_str($query, $rez);
			$request = array_merge($request,$rez);
		}
		return $container->getUi()->render($request);
	}

	function sendOrder($order_id) {
		$this->load->model('checkout/order');
		$this->load->model('catalog/product');
		$order = $this->model_checkout_order->getOrder($order_id);
		
		$this->load->model('account/order');
		$products = $this->model_account_order->getOrderProducts($order_id);
		
		$products_array = array();

        foreach ($products as $product) {
        	$db_product = $this->model_catalog_product->getProduct($product['product_id']);
            $products_array[] = array(
                'id'        => $product['product_id'],
                'name'      => $product['name'],
                'price'     => $product['price'],
                'width'     => $db_product['width'],
                'height'    => $db_product['height'],
                'length'    => $db_product['length'],
                'weight'    => $db_product['weight'],
                'quantity'  => $product['quantity'],
                'sku'       => $product['model'],
            );
        }

        //print_r($products_array);

		$adapter = new OcAdapter(array(
										'form' => $products_array,
								));
		$container = new Container(array('adapter' => $adapter));

		$business = $container->getBusiness();

		$paymentID = OcAdapter::stringToNumber($order['payment_code']);
		
		$ddeliveryId = (int)$business->cmsSendOrder(
                        $order['ddelivery_id'],
                        $order['order_id'],
                        $paymentID,
                        '12',
                        $order['shipping_firstname'],
                        $order['telephone'],
                       	$order['email'],
                        null
        );

        // echo "<br>sdkid: " . $this->session->data['ddelivery_order_id'];
        // echo "<br>oc_order_id: " . $this->session->data['order_id'];
        // echo "<br>payment_method: " . $this->session->data['payment_method']['code'];
        // echo "<br>name: " . "Status";
        // echo "<br>phone: " . $this->session->data['simple']['customer']['firstname'];
        // echo "<br>telephone: " . $this->session->data['simple']['customer']['telephone'];
       	// echo "<br>email: " . $this->session->data['simple']['customer']['email'];
       	// echo "<br>";

		// $api_key = $this->config->get('ddelivery_api_key');
		// $token = $this->session->data['ddelivery_token'];
		// $shop_refnum = $this->session->data['order_id'];
		// $helper = new DDeliveryHelper($api_key, false);
		// $params = [
		//     'session' => $token,
		//     'to_name' => $this->session->data['simple']['customer']['firstname'],
		//     'to_phone' => $this->session->data['simple']['customer']['telephone'],
		//     'to_email' => $this->session->data['simple']['customer']['email'],
		//     'shop_refnum' => $shop_refnum
		// ];
		// print_r($params);
		// print_r($helper->sendOrder($token, $params));
		// print_r($this->session->data);
		// print_r($ddeliveryId);
	}

	function getQuote($address) {
		$this->load->language('shipping/ddelivery');

		$api_key = $this->config->get('ddelivery_api_key');

		$query = $this->db->query("SELECT * FROM " . DB_PREFIX . "zone_to_geo_zone WHERE geo_zone_id = '" . (int)$this->config->get('ddelivery_geo_zone_id') . "' AND country_id = '" . (int)$address['country_id'] . "' AND (zone_id = '" . (int)$address['zone_id'] . "' OR zone_id = '0')");

		if (!$this->config->get('ddelivery_geo_zone_id')) {
			$status = true;
		} elseif ($query->num_rows) {
			$status = true;
		} else {
			$status = false;
		}

		$method_data = array();

		if ($status) {
			$quote_data = array();

			$ddelivery_price = (isset($this->session->data['ddelivery_price'])) ? $this->session->data['ddelivery_price'] : 0.0;

			$quote_data['ddelivery'] = array(
				'code'         => 'ddelivery.ddelivery',
				'title'        => $this->language->get('text_description'),
				'cost'         => $ddelivery_price,
				'tax_class_id' => 0,
				'ddelivery'	   => 'true',
				'text'         => ''
			);

			$method_data = array(
				'code'       => 'ddelivery',
				'title'      => $this->language->get('text_title'),
				'quote'      => $quote_data,
				'sort_order' => $this->config->get('ddelivery_sort_order'),
				'error'      => false
			);
		}

		return $method_data;
	}
}
<?php
use DDelivery\Adapter\Adapter;
use DDelivery\DDeliveryException;
/**
 * Created by PhpStorm.
 * User: mrozk
 * Date: 4/11/15
 * Time: 2:53 PM
 */
class OcAdapter extends Adapter  {
    /**
     *
     * Получить апи ключ
     *
     * @throws DDeliveryException
     * @return string
     */
    public function getApiKey(){
    	global $registry;
	    /**
	     * @var Config
	     */
		$config = $registry->get('config');
        return $config->get('ddelivery_api_key');
    }

    /**
     * Настройки базы данных
     * @return array
     */
    public function getDbConfig(){

        return array(
            'type' => self::DB_MYSQL,
            'dsn' => 'mysql:host='.DB_HOSTNAME.';dbname='.DB_DATABASE,
            'user' => DB_USERNAME,
            'pass' => DB_PASSWORD,
            'prefix' => '',
        );

    }

    /**
     *
     * При синхронизации статусов заказов необходимо
     * array(
     *      'id' => 'status',
     *      'id2' => 'status2',
     * )
     *
     * @param array $orders
     * @return bool
     */
    public function changeStatus(array $orders){
        // TODO: Implement changeStatus() method.
    }

    /**
     * Получить урл апи сервера
     *
     * @return string
     */
    public function getSdkServer(){
        return 'https://sdk.ddelivery.ru/api/v1/';
    }

    public function getCmsName(){
        return 'Open Cart';
    }

    public function getCmsVersion(){
        return '2.1.x';
    }

    public function getEnterPoint() {
        return "http://" . $_SERVER['HTTP_HOST'] . "/index.php?route=module/ddelivery/ddeliveryEndpoint/";
    }

    /**
     * Получить  заказ по id
     * ['city' => город назначения, 'payment' => тип оплаты, 'status' => статус заказа,
     * 'sum' => сумма заказа, 'delivery' => стоимость доставки]
     *
     * город назначения, тип оплаты, сумма заказа, стоимость доставки
     *
     * @param $id
     * @return array
     */
    public function getOrder($id){
        return array(
            'city' => 'Урюпинск',
            'payment_id' => 22,
            'payment_name' => "Карточкой",
            'status_id' => 11,
            'status' => 'Статус',
            'date' => '2015.12.12',
            'sum' => 2200,
            'delivery' => 220,
        );
    }
    /**
     * Получить список заказов за период
     * ['city' => город назначения, 'payment' => тип оплаты, 'status' => 'статус заказа'
     * 'sum' => сумма заказа, 'delivery' => стоимость доставки]
     *
     * город назначения, тип оплаты, сумма заказа, стоимость доставки
     *
     * @param $from
     * @param $to
     * @return array
     */
    public function getOrders($from, $to){
        return array(
            array(
                'city' => 'Урюпинск',
                'payment_id' => 22,
                'payment_name' => "Карточкой",
                'status_id' => 11,
                'status' => 'Статус',
                'date' => '2015.12.12',
                'sum' => 2200,
                'delivery' => 220,
            ),
            array(
                'city' => 'г. Москва, Московская область',
                'payment_id' => 22,
                'payment_name' => "Наличными",
                'status_id' => 11,
                'status' => 'Отгружен',
                'date' => '2015.13.14',
                'sum' => 2100,
                'delivery' => 120,
            ),
            array(
                'city' => 'Сити Питер',
                'payment_id' => 42,
                'payment_name' => "Рубли",
                'status_id' => 11,
                'status' => 'Отгружен',
                'date' => '2015.11.17',
                'sum' => 2100,
                'delivery' => 120,
            )
        );
    }

    /**
     * Получить скидку в рублях
     *
     * @return float
     */
    public function getDiscount(){
        return 0;
    }

    /**
     *
     * Получить содержимое корзины
     *
     * @return array
     */
    public function getProductCart(){
        return $this->params['form'];
    }

    /**
     * Получить массив с соответствием статусов DDelivery
     * @return array
     */
    public function getCmsOrderStatusList(){
        return array();
    }

    /**
     * Получить массив со способами оплаты
     * @return array
     */
	public function getCmsPaymentList() {
		global $loader, $registry, $session, $config, $language;
		
		unset( $session->data[ 'payment_methods' ] );
		unset( $session->data[ 'payment_method' ] );
		
		$loader->model( 'extension/extension' );
		
		$results = $registry->get( 'model_extension_extension' )
		                    ->getExtensions( 'total' );
		
		$total_data = array();
		$total      = 0;
		$cart       = new Cart( $registry );
		$taxes      = $cart->getTaxes();;
		foreach ( $results as $result ) {
			if ( $config->get( $result[ 'code' ] . '_status' ) ) {
				$loader->model( 'total/' . $result[ 'code' ] );
				
				$registry->get( 'model_total_' . $result[ 'code' ] )
				         ->getTotal( $total_data, $total, $taxes );
			}
		}
		
		$return = array();
		
		$loader->model( 'extension/extension' );
		
		$results = $registry->get( 'model_extension_extension' )
		                    ->getExtensions( 'payment' );
		
		foreach ( $results as $result ) {
			if ( $config->get( $result[ 'code' ] . '_status' ) ) {
				$loader->model( 'payment/' . $result[ 'code' ] );
				$lang          = $language->load( 'payment/' . $result[ 'code' ] );
				$code          = $result[ 'code' ];
				$id            = self::stringToNumber( $code );
				$return[ $id ] = $lang[ 'text_title' ];
			}
		};
		
		return $return;
	}

    /***
     *
     * В этом участке средствами Cms проверить права доступа текущего пользователя,
     * это важно так как на базе этого  метода происходит вход
     * на серверние настройки
     *
     * @return bool
     */
    public function isAdmin(){
        return true;
    }
	
	public static function stringToNumber( $string ) {
		$alphabet = array(
			'a' => '01',
			'b' => '02',
			'c' => '03',
			'd' => '04',
			'e' => '05',
			'f' => '06',
			'g' => '07',
			'h' => '08',
			'i' => '09',
			'j' => '10',
			'k' => '11',
			'l' => '12',
			'm' => '13',
			'n' => '14',
			'o' => '15',
			'p' => '16',
			'q' => '17',
			'r' => '18',
			's' => '19',
			't' => '20',
			'u' => '21',
			'v' => '22',
			'w' => '23',
			'x' => '24',
			'y' => '25',
			'z' => '26'
		);
		$string   = trim( (string) $string );
		$string   = strtolower( $string );
		$len      = strlen( $string );
		$rez      = '';
		for ( $i = 0; $i < $len; $i ++ ) {
			if ( isset( $alphabet[ $string[ $i ] ] ) ) {
				$rez .= $alphabet[ $string[ $i ] ];
			}
		}
		$rez = '1' . $rez;
		if ( strlen( $rez ) > 32 ) {
			$rez = substr( $rez,
			               0,
			               32 );
		}
		return (int) $rez;
	}

}
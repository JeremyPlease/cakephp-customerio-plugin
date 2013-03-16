<?php
/**
 * Customerio Component
 *
 * Licensed under The MIT License
 *
 * @package     customerio
 * @subpackage  customerio.controller.component
 * @link        http://github.com/jeremyplease/cakephp-customerio-plugin
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author 		Jeremy Pease 
 */
class CustomerioComponent extends Component{

	public $components = array('Session');

	public $settings = array(
		'site_id' => null,
		'api_key' => null,
		'id_prefix' => 'prod'
	);

	function __construct(ComponentCollection $collection, $settings = array()) {
		foreach($this->settings as $key => $value) {
			if ($value = Configure::read('Customerio.'.$key)) {
				$this->settings[$key] = $value;
			}
		}
		$settings = array_merge($this->settings, $settings);
		if (empty($settings['site_id']) || empty($settings['api_key'])) {
			throw new Exception("Must specify Customer.io site_id and api_key");
		}
        parent::__construct($collection, $settings);
    }
	
	public function initialize($controller) {
		Customerio::instance($this);
	}

	/**
	 * Set identity of currently logged in user to 
	 * be used in future calls to track or identify
	 * 
	 * @param $user_id
	 */
	public function setIdentity($user_id) {
		$this->Session->write('Customerio.user_id', $user_id);
	}

	/**
	 * Send identifying information of previously
	 * identified user or newly identified users
	 * 
	 * @param  array  $data Data that will be set as customer attributes
	 * @param  array or string $user_ids Optional user id(s) to use instead
	 */
	public function identify($data = array(), $user_ids = null) {
		$this->sendRequest('identify', $data, $user_ids);
	}

	/**
	 * Send tracking event information of previously
	 * identified user or newly identified users
	 * 
	 * @param  string $name Name of event to track
	 * @param  array  $data Optional data to send with event
	 * @param  [type] $user_ids Optional user id(s) to use instead
	 */
	public function track($name = '', $data = array(), $user_ids = null) {
		$formatted_data['name'] = $name;
		if (!empty($data)) {
			$formatted_data['data'] = $data;
		}
		$this->sendRequest('track', $formatted_data, $user_ids);
	}

	/**
	 * Send curl request(s) to customer.io api. If user_ids is an 
	 * array of ids then multiple requests are sent
	 * 
	 * @param  string $type Either 'identify' or 'track'
	 * @param  array  $data
	 * @param  array or string $user_ids
	 */
	private function sendRequest($type = 'identify', $data = array(), $user_ids) {
		if ($user_ids == null) {
			$user_ids = array($this->Session->read('Customerio.user_id'));
		}
		if (empty($user_ids)) {
			throw new Exception("Cannot track unidentified user.");
		}
		if (!is_array($user_ids)) {
			$user_ids = array($user_ids);
		}

		foreach ($user_ids as $user_id) {
			$customerio_url = 'https://track.customer.io/api/v1/customers/' .
				$this->settings['id_prefix'] . '_' . $user_id;
			
			$request_type = 'PUT';
			if ($type == 'track') {
				$customerio_url .= '/events';
				$request_type = 'POST';
			}

			$site_id = $this->settings['site_id'];
			$api_key = $this->settings['api_key'];

			$data_string = json_encode($data);

			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $customerio_url);
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($curl, CURLOPT_HTTPGET, true);
			curl_setopt($curl, CURLOPT_HEADER, false);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $request_type);
			curl_setopt($curl, CURLOPT_VERBOSE, true);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $data_string);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array(
				"X-HTTP-Method-Override: $request_type",
			    'Content-Type: application/json',
			));  
			curl_setopt($curl,CURLOPT_USERPWD,$site_id . ":" . $api_key);
			if(ereg("^(https)",$customerio_url)) curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);

			curl_exec($curl);
			curl_close($curl);
		}
	}

}

/**
 * Static methods for easy access anywhere with Customerio::methodName(...)
 */
class Customerio{
	static function instance($setInstance = null) {
		static $instance;
		if ($setInstance) {
			$instance = $setInstance;
		}
		if (!$instance) {
			throw new Exception(
				'CustomerioComponent not initialized properly!'
			);
		}
		return $instance;
	}

	public static function setIdentity($user_ids = null) {
		return self::instance()->setIdentity($user_ids);
	}

	public static function identify($data = array(), $user_ids = null) {
		return self::instance()->identify($data, $user_ids);
	}

	public static function track($name = '', $data = array(), $user_ids = null) {
		return self::instance()->track($name, $data, $user_ids);
	}
	
}
?>
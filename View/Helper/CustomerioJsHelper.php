<?php
/**
 * CustomerioJs Helper
 *
 * Licensed under The MIT License
 *
 * @package     customerio
 * @subpackage  customerio.view.helper
 * @link        http://github.com/jeremyplease/cakephp-customerio-plugin
 * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author 		Jeremy Pease 
 */
class CustomerioJsHelper extends Helper{

	public $settings = array(
		'site_id' => null,
		'api_key' => null,
		'id_prefix' => 'prod'
	);

	function __construct(View $view, $settings = array()) {
		foreach($this->settings as $key => $value) {
			if ($value = Configure::read('Customerio.'.$key)) {
				$this->settings[$key] = $value;
			}
		}
		$settings = array_merge($this->settings, $settings);
		if (empty($settings['site_id']) || empty($settings['api_key'])) {
			throw new Exception("Must specify Customer.io site_id and api_key");
		}
        parent::__construct($view, $settings);
    }

    /**
     * Generate javascipt snippet (inside script tags) for Customer.io tracking
     * 
     * @return string
     */
	public function snippet() {
		return $this->_View->element('Customerio.snippet', $this->settings);
	}

	/**
	 * Generate javascript that identifies user and sets attributes
	 * 
	 * @param  array  $data 
	 * @return string
	 */
	public function identify($data = array()) {
		if (isset($data['id'])) {
			$data['id'] = $this->settings['id_prefix'] . '_' . $data['id'];
		}
		return '_cio.identify('.json_encode($data).');';
	}

	/**
	 * Generate javascript that tracks event on user.
	 * 
	 * @param  sring $name Name of event to track
	 * @param  array  $data Event attributes to send
	 * @return string
	 */
	public function track($name = null, $data = array()) {
		return '_cio.track("'.$name.'", '.json_encode($data).');';
	}

}
?>
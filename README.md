# Customer.io Plugin

This is a CakePHP plugin that makes it easy to integrate with [Customer.io](http://customer.io).


## Requirements

* CakePHP 2.x
* PHP 5.3


## Installation

Different ways to install this CakePHP plugin:

#### _Manually:_

* Download and unzip this: [https://github.com/jeremyplease/cakephp-customerio-plugin/zipball/master](https://github.com/jeremyplease/cakephp-customerio-plugin/zipball/master)
* Copy the folder into `app/Plugin`
* Rename the folder you just copied to `Customerio`

#### _As a GIT Submodule_:

In your app directory type:

	git submodule add git://github.com/jeremyplease/cakephp-customerio-plugin.git Plugin/Customerio
	git submodule init
	git submodule update

#### _Via GIT Clone:_

In your plugin directory type:

	git clone git://github.com/jeremyplease/cakephp-customerio-plugin.git Customerio

### Enable plugin

In your `app/Config/bootstrap.php` file:

	CakePlugin::load('Customerio');

If you are already using `CakePlugin::loadAll();`, then this is not necessary.


## Configuration

Get your Site ID and API Key from [https://manage.customer.io/integration](https://manage.customer.io/integration).

#### Configuration Settings
* `site_id`: Your Customer.io site id.
* `api_key`: Your Customer.io api key.
* `id_prefix`: A prefix to be used for the Customer.io customer's id attribute. Defaults to 'prod' (ie. would make id prod_263).

You can set the CustomerioComponent and CustomerioJsHelper settings in `Config/bootstrap.php`:

	Configure::write('Customerio', array(
	    'site_id' => 'YOUR_SITE_ID',
	    'api_key' => 'YOUR_API_KEY',
	    'id_prefix' => 'prod'
	));

### CustomerioComponent Setup

Add the following to your `AppController` or a specific Controller:

	// If you set the settings in Config/bootstrap.php
	public $components = array('Customerio.Customerio');

	// If you did NOT set the settings in Config/bootstrap.php
	public $components = array('Customerio.Customerio' => array(
		'site_id' => 'YOUR_SITE_ID',
	    'api_key' => 'YOUR_API_KEY',
	    'id_prefix' => 'prod'
	));

### CustomerioJsHelper Setup

Add the following to your `AppController` or a specific Controller:

	// If you set the settings in Config/bootstrap.php
	public $helpers = array('Customerio.CustomerioJs');

	// If you did NOT set the settings in Config/bootstrap.php
	public $helpers = array('Customerio.CustomerioJs' => array(
		'site_id' => 'YOUR_SITE_ID',
	    'api_key' => 'YOUR_API_KEY',
	    'id_prefix' => 'prod'
	));

**Be sure to replace `YOUR_SITE_ID` and `YOUR_API_KEY` with your actual site id and api key from [Customer.io](https://manage.customer.io/integration).**

## Usage

### CustomerioComponent

Set the identity of the currently logged in user in any controller. Note that this does not
actually send any date to Customer.io. It just stores the current user id in the Session `Customerio.user_id`).

	$this->Customerio->setIdentity($user_id);


#### Identify Customers

To send identifying information to Customer.io and set the currently identified user's atributes use this in any controller. 
Note that all timestamps must be sent as seconds since epoch (so use `strtotime('Jan 24, 2010')` or `time()`).
	
	$customer_attributes = array(
		'email' => 'jane@example.com',
		'created_at' => time(),
		'name' => 'Jane Doe',
		'age' => 26,
		// friends will be converted to an array of objects
		'friends' => array(
			array('name' => 'Sally McGee', 'age' => 34),
			array('name' => 'George Lasner', 'age' => 22),
		),
		// likes will be converted to an array of strings
		'likes' => array('cheese', 'beer', 'feathers')
	);
	$this->Customerio->identify($customer_attributes);

If you would like to send idenifying information for one or more users that are not currently identified, you can 
use the second parameter, `user_ids`.

	$customer_attributes = array(
		'invited_to_party_at' => time(),
		'is_really_cool' => true,
	);

	// set customer attributes for one user
	$this->Customerio->identify($customer_attributes, 345);

	$user_ids = array(64,976,48,87,754);

	// set customer attribtues for multiple users
	$this->Customerio->identify($customer_attributes, $user_ids);

#### Track Custom Events

To send a custom event to Customer.io for the currently identified user use this in any controller. 
Note that all timestamps must be sent as seconds since epoch (so use `strtotime('Jan 24, 2010')` or `time()`).
	
	$event_attributes = array(
		'type_of_soup' => 'chicken noodle',
		'amount' => 30,
		'time_take' => 22,
	);
	$this->Customerio->track('ate_soup', $event_attributes);

If you would like to send custom event information for one or more users that are not currently identified, you can 
use the second parameter, `user_ids`.

	$event_attributes = array(
		'type_of_soup' => 'chicken noodle',
		'amount' => 30,
		'time_take' => 22,
	);

	// track custom event for one user
	$this->Customerio->track('ate_soup', $event_attributes, 345);

	$user_ids = array(64,976,48,87,754);

	// track custom event for multiple users
	$this->Customerio->track('ate_soup', $event_attributes, $user_ids);

#### Static Methods

The above functions can also be called as static methods with `Customerio::methodName(...)`. This is useful if you need to call the methods from within a model.

- `Customerio::setIdentity($user_id)`
- `Customerio::identify($customer_attributes [, $user_ids])`
- `Customerio::track($event_name, $event_attributes [, $user_ids])`

### CustomerioJsHelper

#### Insert Customerio javascript tracking snippet

Add the following code to your layout to output the complete javascript code for tracking users. 
Customer.io recommends placing this right before the closing body tag. 
This will automatically start tracking page views.

	echo $this->CustomerioJs->snippet();

Outputs this:
	
	<script type="text/javascript">
	  var _cio = _cio || [];
	  (function() {
	    var a,b,c;a=function(f){return function(){_cio.push([f].
	    concat(Array.prototype.slice.call(arguments,0)))}};b=["identify",
	    "track"];for(c=0;c<b.length;c++){_cio[b[c]]=a(b[c])};
	    var t = document.createElement('script'),
	        s = document.getElementsByTagName('script')[0];
	    t.async = true;
	    t.id    = 'cio-tracker';
	    t.setAttribute('data-site-id', 'YOUR SITE ID FROM SETTINGS GETS PUT HERE');
	    t.src = 'https://assets.customer.io/assets/track.js';
	    s.parentNode.insertBefore(t, s);
	  })();
	</script>

#### Identify Customers

Insert the following code to send identifying information to Customer.io. 
This should only be used when a user is logged in and the `id` key is required on the first call.

	<script type="text/javascript">
		<?php echo $this->CustomerioJs->identify(array(
				'id' => 54,
				'last_visited' => time()
			)); 
		?>
	</script>

Outputs this:

	<script type="text/javascript">
		_cio.identify({"id":"prod_54","last_visited":1363463951});		
	</script>

#### Track Custom Events

Insert the following code to send custom events to Customer.io. 
This should only be used after a user has been identified at least once.

	<script type="text/javascript">
		<?php echo $this->CustomerioJs->track('viewed_list', array(
				'list_name' => 'cheese',
				'list_items' => array(
					'cheddar', 'swiss', 'provolone'
				)
			)); 
		?>
	</script>

Outputs this:

	<script type="text/javascript">
		_cio.track('viewed_list', {"list_name":"cheese","list_items":["cheddar","swiss","provolone"]});		
	</script>

## License

Copyright (c) 2013 Jeremy Pease

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
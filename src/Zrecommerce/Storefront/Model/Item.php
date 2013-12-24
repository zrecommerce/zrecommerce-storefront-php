<?php
namespace Zrecommerce\Storefront\Model;

class Item extends \Zrecommerce\Storefront\Model\Rest {
	public function __construct($data = null, $options = null) {
		parent::__construct($data, $options);
		
		$this->restURL( API_URL . '/item' );
	}
}
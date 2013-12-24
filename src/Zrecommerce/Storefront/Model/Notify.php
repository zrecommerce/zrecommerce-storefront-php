<?php
namespace Zrecommerce\Storefront\Model;

class Notify extends \Zrecommerce\Storefront\Model\Rest {
	public function __construct($data = null, $options = null) {
		parent::__construct($data, $options);
		
		$this->restURL( API_URL . '/notify' );
	}
}
<?php

namespace Zrecommerce\Storefront\Rest\Encoder;

class Json extends \Zrecommerce\Storefront\Rest\Encoder
{
	public function __construct($contentType = null) {
		if (!isset($contentType)) $contentType = 'application/json';
		$this->setContentType($contentType);
	}
	
	public function pass($data) {
		$result = json_encode($data);
		return $result;
	}
}
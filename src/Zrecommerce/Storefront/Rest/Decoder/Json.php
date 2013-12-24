<?php

namespace Zrecommerce\Storefront\Rest\Decoder;

class Json extends \Zrecommerce\Storefront\Rest\Decoder
{
	public function __construct($contentType = null) {
		if (!isset($contentType)) $contentType = 'application/json';
		$this->setContentType($contentType);
	}
	public function pass($data) {
		$result = json_decode($data);
		return $result;
	}
}
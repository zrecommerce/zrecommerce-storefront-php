<?php
namespace Zrecommerce\Storefront\App\Controller;

class Restful extends \Zrecommerce\Storefront\App\Controller {
	
	function preDispatch() {
		parent::preDispatch();
		
		$jsonRenderer = new \Zrecommerce\Storefront\App\View\Renderer\Json;
		$this->setLayout(''); // No layout is used.
		$this->setRenderer($jsonRenderer);
	}
	function detectAction() {
		$verb = strtolower($_SERVER['REQUEST_METHOD']);
		return $verb;
	}
}
<?php
namespace Zrecommerce\Storefront\App\View;
class Renderer {
	public function render($path = '', $view = null) {
		if (!empty($path)) require_once($path);
	}
}
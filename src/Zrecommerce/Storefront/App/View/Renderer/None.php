<?php
namespace Zrecommerce\Storefront\App\View\Renderer;

/**
 * This is used on controllers that do something, then redirect without needing 
 * to output anything.
 */
class None extends \Zrecommerce\Storefront\App\View\Renderer
{
	public function render($path = '', $view = null) {
		// Ignore path... just output $view vars as JSON.
		
		$vars = $view;
		if (is_object($view)) {
			$vars = (array)$view;
		}
		
		header('Content-Type: text/plain');
		
		// Don't output anything.
	}
}
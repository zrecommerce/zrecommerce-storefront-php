<?php

namespace Zrecommerce\Storefront\App\Controller;

class Admin extends \Zrecommerce\Storefront\App\Controller
{
	protected $_signin_url = '/admin/sign-in';
	
	public function preDispatch() {
		parent::preDispatch();
		
		// Require an authenticated session.
		if (!\Zrecommerce\Storefront\Auth::isAuthenticated()) {
			$this->redirect($this->_signin_url);
		}
	}
}
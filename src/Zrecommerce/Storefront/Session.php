<?php
namespace Zrecommerce\Storefront;

class Session {
	static function isStarted() {
		return session_status() == PHP_SESSION_ACTIVE;
	}
	
	static function getNs($key, $default = null) {
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default;
	}
	
	static function setNs($key, $value) {
		$_SESSION[$key] = $value;
	}
}
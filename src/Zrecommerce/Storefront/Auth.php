<?php

namespace Zrecommerce\Storefront;

use Zrecommerce\Storefront;

class Auth
{
	static function signOn($user_name, $password) {
		$isAuthenticated = false;
		
		/**
		 * @todo Implement authentication check
		 * @todo Save authenticated session somewhere.
		 */
		
		$client = new Model\Rest(null, array(
			'restURL' => API_URL . '/user'
		));
		
		// ...Does this user exist?
		$client->option('query', array(
			'handle' => $user_name
		));
		
		$user = $client->Get();
		
		if ($user->result == 'ok') {
			if ( count($user->data) == 1 ) { // Expect ONLY 1 record!
				
				//...Ok, we have the User object.
				//...Check the password.
				$u = $user->data[0];
				
				$isAuthentic = self::comparePassword($password, $u->password_hash);
				
				if ($isAuthentic === true) {
					
					// Ok! Purge old sessions, create a new one.
					$session = new Model\Rest(null, array(
						'restURL' => API_URL . '/session'
					));
					
					$token = session_id();
					$ip = $_SERVER['REMOTE_ADDR'];
					
					$session->option('query', array(
						'token' => $token,
						'ip' => $ip
					));
					
					$sessions = $session->Get();
					if (count($sessions) > 0) {
						foreach($sessions->data as $sess) {
							$s = new Model\Rest($sess, array('restURL' => API_URL . '/session'));
							$s->Delete();
						}
					}
					
					
					// ...Create the new session
					$newSession = new Model\Rest(
						array(
							'token' => $token,
							'ip' => $ip
						), 
						array('restURL' => API_URL . '/session')
					);
					$newSession->Post();
					
					$isAuthenticated = true;
					
				} else {
					// Authentication failed.
				}
				
			} else {
				// Nope. Something isn't matching up.
			}
		} else {
			// Server error.
		}
		
		return $isAuthenticated;
	}
	
	static function isAuthenticated() {
		$isAuthenticated = false;
		
		$session = new Model\Rest(null, array(
			'restURL' => API_URL . '/session'
		));

		$token = session_id();
		$ip = $_SERVER['REMOTE_ADDR'];

		$session->option('query', array(
			'token' => $token,
			'ip' => $ip
		));
		
		$session->option('sort', array(
			'timestamp_added' => -1,
			'timestamp_modified' => -1, // 1 = ASC, -1 = DESC
			'timestamp_deactivated' => -1
		));

		$sessions = $session->Get();
		
		// Avoid excess sessions... Nuke oldest sessions.
		if (count($sessions->data) > 1) {
			foreach($sessions->data as $sess) {
				$s = new Model\Rest($sess, array('restURL' => API_URL . '/session'));
				$s->Delete();
			}
		} else {
			
			if (count($sessions->data) === 1) {
				// Ok, only one.
				$isAuthenticated = true;
			}
		}
		
		return $isAuthenticated;
	}
	
	static function signOff() {
		$session = new Model\Rest(null, array(
			'restURL' => API_URL . '/session'
		));

		$token = session_id();
		$ip = $_SERVER['REMOTE_ADDR'];

		$session->option('query', array(
			'token' => $token,
			'ip' => $ip
		));
		
		$session->option('sort', array(
			'timestamp_added' => -1,
			'timestamp_modified' => -1, // 1 = ASC, -1 = DESC
			'timestamp_deactivated' => -1
		));

		$sessions = $session->Get();
		
		// Remove all of this user's sessions.
		if (count($sessions->data) > 0) {
			foreach($sessions->data as $sess) {
				$s = new Model\Rest($sess, array('restURL' => API_URL . '/session'));
				$s->Delete();
			}
		}
		// New session required after this. Remember to redirect.
		session_regenerate_id();
	}
	
	static function comparePassword($password, $hash) {
		// Blowfish required.
		CRYPT_BLOWFISH or die('No "blowfish" encryption found, but required.');
		
		$hash_result = crypt($password, $hash);
		return $hash_result === $hash;
	}
}
<?php

namespace Zrecommerce\Storefront\Model;

class Rest extends \Zrecommerce\Storefront\Model
{
	/**
	 *
	 * @var \Rest
	 */
	private $_rest;
	
	public function __construct($data = null, $options = null) {
		parent::__construct($data, $options);
	}
	
	public function restURL($restURL) {
		
		if (isset($restURL)) {
			parent::option('restURL', $restURL);
			return $this;
		} else {
			return parent::option('restURL');
		}
	}
	
	public function findByQuery($query) {
		$class = get_class($this);
		$m = new $class(null, array(
			'query' => $query
		));
		$docs = $m->Get();
		
		return $docs->data;
	}
	
	public function findOneByQuery($query) {
		$docs = $this->findByQuery($query);
		
		return !empty($docs) ? $docs[0]: null;
	}
	/**
	 * Get the REST client.
	 * @return \Rest
	 */
	private function _RestClient() {
		
//		if (empty($this->_rest)) {
			$rest = new \Zrecommerce\Storefront\Rest(parent::option('restURL'));
			
			$headers = array(
				'API-USER: ' . API_USER,
				'API-KEY: ' . API_KEY,
				'API-VERSION: ' . API_VERSION
			);
			
			if ($this->option('query')) {
				
				$headers[] = 'API-QUERY: ' . json_encode($this->option('query'));
			}
			
			if ($this->option('limit')) {
				$headers[] = 'API-LIMIT: ' . (int) $this->option('limit');
			}
			
			if ($this->option('skip')) {
				$headers[] = 'API-SKIP: ' . (int) $this->option('skip');
			}
			
			if ($this->option('sort') && is_string($this->option('sort'))) {
				$headers[] = 'API-SORT: ' . $this->option('sort');
			}
			
			$rest->setHeaders($headers);
			
			$this->_rest = $rest;
//		}
		
		return $this->_rest;
	}
	
	public function Get() {
		return $this->_RestClient()->get($this->data());
	}
	
	public function Put() {
		return $this->_RestClient()->put($this->data());
	}
	
	public function Post() {
		return $this->_RestClient()->post($this->data());
	}
	
	public function Delete() {
		return $this->_RestClient()->delete($this->data());
	}
}
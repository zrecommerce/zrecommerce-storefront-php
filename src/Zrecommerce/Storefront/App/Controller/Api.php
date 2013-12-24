<?php
namespace Zrecommerce\Storefront\App\Controller;

class Api extends \Zrecommerce\Storefront\App\Controller
{
	protected $modelName = '';
	
	function preDispatch() {
		parent::preDispatch();
		$jsonRenderer = new \App\View\Renderer\Json;
		$this->setLayout(''); // No layout is used.
		$this->setRenderer($jsonRenderer);
		
		// Require an authenticated session.
		if (!\Auth::isAuthenticated()) {
			header('HTTP/1.1 401 Authorization Required');
			exit;
		}
	}
	
	function detectAction() {
		$verb = strtolower($_SERVER['REQUEST_METHOD']);
		return $verb;
	}
	
	
	/**
	 * Get the appropriate REST model.
	 * @return \Model\Rest
	 */
	function getRestModel($checkId = false, $autoRemoveId = false) {
		$data = $this->getRequest()->getParams();

		if (isset($data['format'])) unset($data['format']);
		
		$model = $this->modelName;
		$restURL = API_URL . '/' . $model;
		
		$mongoKeyMap = array(
			'mongo:query' => 'query',
			'mongo:offset' => 'skip',
			'mongo:count' => 'limit',
			'mongo:sort' => 'sort',
		);
		$validMongoKeys = array_keys($mongoKeyMap);
		$mongoParams = array();
		
		foreach($data as $k => $var) {
			if (in_array($k, $validMongoKeys) && $var !== '') {
				$mongoParams[$mongoKeyMap[$k]] = $var;
			}
		}
		
		if ($checkId == true) {
			if (!empty($data['id'])) {
				$id = $data['id'];
				$restURL .= '/' . $id;
			} else if (!empty($data['_id'])) {
				$id = $data['_id'];
				$restURL .= '/' . $id;
				
				if ($autoRemoveId) unset($data['_id']);
			}
		}
		
		$restModel = new \Model\Rest($data, array(
			'restURL' => $restURL
		));
		
		if (count($mongoParams) > 0) {
			foreach($mongoParams as $key => $p) {
				$restModel->option($key, $p);
				
				$d = $restModel->data();
				unset($d[$key]);
				$restModel->data($d);
			}
		}
		
		return $restModel;
	}
	/**
	 * @todo Require a valid session
	 */
	function indexAction() {
		$this->view = $this->getRestModel(true)->Get();
	}
	
	function getAction() {
		$this->forward('index');
	}
	
	function putAction() {
		$this->view = $this->getRestModel(true, true)->Put();
	}
	
	function postAction() {
		$this->view = $this->getRestModel()->Post();
	}
	
	function deleteAction() {
		$this->view = $this->getRestModel(true)->Delete();
	}
}
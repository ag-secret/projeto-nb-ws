<?php

namespace App\Controller;

use App\Model\Account;

class AccountsController extends AppController
{
	function __construct()
	{
		parent::__construct();
		$this->Account = new Account;
	}

	public function getAccess($account_id = null)
	{
		if (!$account_id) {
			$this->response(400, 'Error', 'Você nãoo informou o  ID');
		}

		$profile = $this->Account->getAccess($account_id);

		if ($profile) {
			return $this->response(200, 'ok', $profile);
		} else {
			return $this->response(400, 'Error', 'Conta não existe.');
		}
		
	}
}
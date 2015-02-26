<?php

namespace App\Controller;

use App\Model\Account;

use Mayhem\Controller\Controller;

/**
 * App controller, the logics from here will be visible for all controllers
 */
class AppController extends Controller
{

	public function beforeFilter()
	{
		$account = $this->auth();
		if (!$account) {
			return $this->Response->error(403, 'Acesso negado');
		} else {
			$this->user = $account;
		}
	}

	public function auth(){
		$id = !isset($this->request->get['id']) ? null : $this->request->get['id'];

		if (!$id) {
			return $this->Response->error(400, 'ID da conta não foi passado');
		}

		$Account = new Account;
		$query = $Account->find();
		$query
			->cols([
				'Account.id',
				'Account.android_device_registration_id',
				'Profile.name',
				'Profile.id AS profile_id'
			])
			->where('Account.id = :id')
			->join(
				'INNER',
				'profiles Profile',
				'Profile.account_id = Account.id'
			)
			->bindValues(['id' => $id]);
		$account = $Account->one($query);

		return $account;
	}

}
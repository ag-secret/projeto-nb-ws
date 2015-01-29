<?php

namespace App\Model;

class Account extends AppModel
{
	/**
	 * O nome da tabela em que este Model está ligado
	 * @var string
	 */
	public $tableName = 'accounts AS Account';

	public function getAccess($account_id)
	{
		$query = $this->find();
		$query
			->cols([
				'Profile.name',
				'Profile.account_id',
				'Profile.gender',
				'Account.username'
			])
			->join(
				'INNER',
				'profiles Profile',
				'Profile.account_id = Account.id'
			)
			->where('Account.id = :id')
			->bindValues(['id' => $account_id]);
		$profile = $this->one($query);

		return $profile;
	}
}
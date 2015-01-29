<?php

namespace App\Controller;

use App\Model\Profile;

class ProfilesController extends AppController
{
	function __construct()
	{
		parent::__construct();
		$this->Profile = new Profile;
	}

	/**
	 * Pega o perfil de todo mundo que está no evento em que você escolheu para procurar perfils
	 */
	public function getCloseProfiles($place_id)
	{
		$query = $this->Profile->find();
		$query
			->cols(['Profile.name'])
			->where('place_id = :place_id')
			->limit($this->Profile->closeProfilesLimit)
			->bindValues(['place_id' => $place_id]);

		$profiles = $this->Profile->all($query);

		$this->response(200, 'ok', $profiles);
	}

	/**
	 * Pega todos os perfils em que você está combinado
	 */
	public function getChats()
	{
		$query = $this->Profile->find();
		$query->cols(['Profile.name']);

		$profiles = $this->Profile->all($query);

		$this->response(200, 'ok', $profiles);
	}

	/**
	 * Pega o Perfil de alguem que está no chat com você
	 * @param  int $id Id da pessoa a ter o perfil mostrado
	 */
	public function getChat($id = null)
	{
		if (!$id) {
			$this->response(400, 'Error', 'Você não informou o ID do perfil a ser mostrado');
		}

		$query = $this->Profile->find();
		$query
			->cols(['Profile.name'])
			->where('Profile.id = :id')
			->bindValues(['id' => $id]);

		$profile = $this->Profile->one($query);

		$this->response(200, 'ok', $profile);
	}
}
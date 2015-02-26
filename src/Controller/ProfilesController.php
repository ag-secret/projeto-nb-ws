<?php

namespace App\Controller;

use App\Model\Account;
use App\Model\Combination;
use App\Model\Profile;

use Valitron\Validator;
use Mayhem\Validation\ValitronAdapter;

use JWT;

class ProfilesController extends AppController
{
	public function beforeFilter()
	{
		parent::beforeFilter();
		$this->Profile = new Profile;
	}

	public function teste()
	{
		$key = "654sdf06";
		$token = ['id' => 1];

		$jwt = JWT::encode($token, $key);
		print_r($jwt);
		$decoded = JWT::decode($jwt, $key);
		print_r($decoded);

	}

	public function updateProfilePicture($account_id)
	{
		$imageURL = $this->request->header_body_json['image_url'];

		$Account = new Account;
		$query = $Account->find();
		$query
			->cols(['facebook_id'])
			->where('id = :account_id')
			->bindValues(['account_id' => $account_id]);

		$account = $Account->one($query);

		if (!$account) {
			return $this->Response->error(400, 'Conta não existe');
		}

		$imageFolder = $Account->getProfilePictureFolder($account->facebook_id);

		$Account->resizeAndSaveProfilePicture($imageURL, $imageFolder);

		return $this->Response->success('Salvo com sucesso!');
	}

	/**
	 * Pega o perfil de todo mundo que está no evento em que você escolheu para procurar perfils
	 */
	public function getCloseProfiles()
	{
		$event_id = $this->request->get['event_id'];
		$account_id = $this->user->id;

		// SELECT account_id2 FROM combinations WHERE account_id1 = 39 AND account_id2 = acc.id
		
		$Combination = new Combination;
		$subQuery = $Combination->find();
		$subQuery
			->cols(['Combination.account_id2'])
			->where('account_id1 = :account_id')
			->where('account_id2 = Account.id');

		$query = $this->Profile->find();
		$query
			->cols([
				'Account.id',
				'Profile.name',
				"concat('http://bbgl.kinghost.net/img/',Account.facebook_id,'/profile_picture.jpg') AS profile_picture"
			])
			->join(
				'INNER',
				'accounts Account',
				'Profile.account_id = Account.id'
			)
			->where('Profile.event_id = :event_id')
			->where('Profile.account_id != :account_id')
			->where('Profile.account_id NOT IN ('.$subQuery->__toString().')')
			->limit($this->Profile->closeProfilesLimit)
			->bindValues([
				'event_id' => $event_id,
				'account_id' => $account_id
			]);

		$profiles = $this->Profile->all($query);

		$this->Response->success($profiles);
	}

	/**
	 * Pega todos os perfils em que você está combinado
	 */
	public function all()
	{
		$query = $this->Profile->find();
		$query
			->cols([
				'Profile.id',
				'Profile.name',
				"concat('http://bbgl.kinghost.net/img/',Account.facebook_id,'/profile_picture.jpg') AS profile_picture",
				"concat('http://bbgl.kinghost.net/img/',Account.facebook_id,'/profile_picture_small.jpg') AS profile_picture_small"
				])
			->join(
				'LEFT',
				'accounts AS Account',
				'Account.id = Profile.account_id'
			);

		$result = $this->Profile->all($query);

		$profiles = [];
		$i = 0;

		foreach ($result as $row) {
			$profiles[$i]['id'] = $row->id;
			$profiles[$i]['name'] = $row->name;
			$profiles[$i]['profile_picture']['regular'] = $row->profile_picture;
			$profiles[$i]['profile_picture']['small'] = $row->profile_picture_small;
			$i++;
		}

		return $this->Response->success($profiles);
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

	public function setLocation()
	{

		if ($this->Profile->update($this->request_data)) {
			$this->response(200, 'ok', 'Savo!');
		} else {
			$this->response(400, 'ok', 'Erro ao salvar a localização');
		}
	}

	public function getLocation($id)
	{
		$query = $this->Profile->find();
		$query
			->cols(['lat', 'lng'])
			->where('id = :id')
			->bindValues(['id' => $id]);

		$location = $this->Profile->one($query);
		return $this->response(200, 'ok', $location);
	}

	public function getDistanceBetween($id, $id2){


		if ($this->Profile->update($this->request_data)) {
			return $this->response(200, 'ok', 'Savo!');
		} else {
			return $this->response(400, 'ok', 'Erro ao salvar a localização');
		}
	}

	public function setCurrentEvent()
	{
		
		$event_id = $this->request->header_body_json['event_id'];
		
		$id = $this->user->profile_id;

		if (!$this->Profile->update(['id' => $id, 'event_id' => $event_id])) {
			return $this->Response->error(400, 'Não salvou');
		}

		return $this->Response->success('Salvou');
	}

	public function setPreferedGender()
	{
		$account_id = $this->user->id;
		$data = ['prefered_gender' => $this->Profile->getShortGender($this->request->header_body_json['gender'])];

		// $validator = new Validator($data);
		// $validator = ValitronAdapter::AdaptRules($this->Profile->validations, $validator, 'update');

		if (/*$validator->validate()*/ 1 == 1) {
			$update = $this->Profile->customUpdate();
			$update
				->cols($data)
				->where('account_id = :account_id')
				->bindValues(['account_id' => $account_id]);

			if ($this->Profile->executeQuery($update)) {
				return $this->Response->success('Salvo');
			} else {
				return $this->Response->error(400, 'Ocorreu um erro ao salvar');
			}
		} else {
			$this->Response->error(400, $validator->errors());
		}

	}

	public function setPreferedAge()
	{
		$account_id = $this->user->id;

		$data = [
			'account_id' => $account_id,
			'prefered_age_min' => $this->request->header_body_json['min'],
			'prefered_age_max' => $this->request->header_body_json['max']
		];


		// $validator = new Validator($data);
		// $validator = ValitronAdapter::AdaptRules($this->Profile->validations, $validator, 'update');

		if (/*$validator->validate()*/ 1 == 1) {
			$update = $this->Profile->customUpdate();
			$update
				->cols($data)
				->where('account_id = :account_id')
				->bindValues(['account_id' => $account_id]);

			if ($this->Profile->executeQuery($update)) {
				return $this->Response->success('Salvo');
			} else {
				return $this->Response->error(400, 'Ocorreu um erro ao salvar');
			}
		} else {
			$this->Response->error(400, $validator->errors());
		}
	}
}
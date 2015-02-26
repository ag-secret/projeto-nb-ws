<?php

namespace App\Controller;

use App\Model\Account;
use App\Model\Profile;

use App\Model\Combination;
use App\Controller\Component\AndroidPushNotification;

use Datetime;

class CombinationsController extends AppController
{

	private $Combination;

	public function saveTeste()
	{
		$dataAccount = [
			'created' => '2015-10-10',
			'facebook_access_token' => '123',
			'app_access_token' => '123',
			'android_device_registration_id' => '123',
			'platform' => 'android',
		];
		$dataProfile = [
			'birthday' => '2015-10-10',
			'created' => '2015-10-10',
			'gender' => 'm',
			'event_id' => 1,
			'prefered_gender' => 'f',
			'prefered_age_min' => 18,
			'prefered_age_max' => 60,
			'profile_img_url' => '123',
		];

		$Account = new Account;
		$Profile = new Profile;
		$Combination = new Combination;

		for ($i=42; $i < 200; $i++) { 
			$Profile->delete($i);
			$Account->delete($i);
			// $Combination->delete($i);
		}

		for ($i=42; $i < 200; $i++) { 
			$dataAccount['id'] = $i;
			$dataAccount['username'] = $i . '@gmail.com';
			$dataAccount['facebook_id'] = $i;

			$dataProfile['id'] = $i;
			$dataProfile['account_id'] = $i;
			$dataProfile['name'] = 'Teste ' . $i;

			$Account->save($dataAccount);
			$Profile->save($dataProfile);
		}

		for ($i=42; $i < 200; $i++) { 
			$Combination->save([
				'account_id1' => $i,
				'account_id2' => $this->user->id,
				'response' => 1,
				'combinou' => 1,
				'event_id' => 1,
				'created' => '2015-10-10'
			]);
			$Combination->save([
				'id' => $Combination->lastInsertId + 1,
				'account_id1' => $this->user->id,
				'account_id2' => $i,
				'response' => 1,
				'combinou' => 1,
				'event_id' => 1,
				'created' => '2015-10-10'
			]);
		}

	}

	public function beforeFilter()
	{
		parent::beforeFilter();
		$this->Combination = new Combination;
	}

	/**
	 * Pega as combinações do usuario paginando baseado no parametro passado
	 * @param  int $page 	Página atual a ser pega
	 * @return array       	Combinações
	 */
	public function getMatches($page)
	{
		/**
		 * Limite de resultados por página
		 * @var integer
		 */
		$limit = 10;
		$offset = $limit * $page;

		$Profile = new Profile;
		$Combination = new Combination;

		$subQuery = $Combination->find();
		$subQuery
			->cols(['account_id1'])
			->where('Combination.account_id1 = Profile.account_id')
			->where('Combination.account_id2 = :account_id')
			->where('Combination.combinou = 1');

		$query = $Profile->find();
		$query
			->cols([
				'Profile.id',
				'Profile.name',
				'Profile.account_id',
				"concat('http://bbgl.kinghost.net/img/',Account.facebook_id,'/profile_picture.jpg') AS profile_picture",
				"concat('http://bbgl.kinghost.net/img/',Account.facebook_id,'/profile_picture_small.jpg') AS profile_picture_small"
			])
			->where('Profile.account_Id IN ('.$subQuery.')')
			->join(
				'INNER',
				'accounts Account',
				'Profile.account_id = Account.id'
			)
			->offset($offset)
			->limit($limit)
			->bindValues(['account_id' => $this->user->id]);

		$result = $Profile->all($query);

		$matches = [];
		$i = 0;
		
		// Formata o array 
		if ($result) {
			foreach ($result as $row) {
				$matches[$i]['id'] = $row->id;
				$matches[$i]['name'] = $row->name;
				$matches[$i]['account_id'] = $row->account_id;
				$matches[$i]['profile_picture']['regular'] = $row->profile_picture;
				$matches[$i]['profile_picture']['small'] = $row->profile_picture_small;
				$i++;
			}
		}

		return $this->Response->success($matches);
	}

	public function setResponse()
	{	
		$account_id = $this->user->id;

		$target = $this->request->header_body_json['target'];
		// $target = $this->request->get['target'];
		$response = $this->request->header_body_json['response'];
		// $response = $this->request->get['response'];

		// Pega a linha da tabela combination para ver se o alvo já havia dado alguma resposta
		// para o id, caso sim faz o update colocando o cambo match = 1
		$query = $this->Combination->find();
		$query
			->cols(['*'])
			->where('account_id1 = :target')
			->where('account_id2 = :account_id')
			->bindValues([
				'target' => $target,
				'account_id' => $account_id
			]);

		$result1 = $this->Combination->one($query);

		// Seta a response, independente de qualquer coisa
		$now = new Datetime;
		$data = [
			'account_id1' => $account_id,
			'account_id2' => $target,
			'response' => $response,
			'event_id' => 1,
			'created' => $now->format('Y-m-d H:i:s')
		];

		if ($this->Combination->save($data)) {
			if ($result1) {
				if ($result1->response == 1 && $response == 1){
					$update = $this->Combination->customUpdate();
					$update
						->cols([
							'combinou' => 1
						])
						->where('(account_id1 = :account_id AND account_id2 = :target)')
						->orWhere('(account_id2 = :account_id AND account_id1 = :target)')
						->bindValues([
							'target' => $target,
							'account_id' => $account_id
						]);

					if (!$this->Combination->executeQuery($update)) {
						return $this->Response->error(400, 'Ocorreu um erro ao tentar salvar combinação');
					} else {
						

						$Account = new Account;
						$account = $Account->get($target, ['Profile.name','Account.android_device_registration_id']);

						$payloadData = [
							'title' => 'Desenrolo',
							'message' => "{$account->name} combinou com você.",
						];

						try {
							AndroidPushNotification::sendGCM($payloadData, $this->user->android_device_registration_id);
						} catch (Exception $e) {
							return $this->Response->error(400, 'Erro ao mandar notificação');
						}
						
						// Manda para o outro
						$payloadData = [
							'title' => 'Desenrolo',
							'message' => "{$this->user->name} combinou com você.",
						];

						try {
							AndroidPushNotification::sendGCM($payloadData, $account->android_device_registration_id);
						} catch (Exception $e) {
							return $this->Response->error(400, 'Erro ao mandar notificação');
						}
					}
				}
			}

			return $this->Response->success('Resposta salva com sucesso!');

		} else {
			return $this->Response->error(400, 'Ocorreu um erro ao tentar salvar a sua resposnta');
		}
	}

}
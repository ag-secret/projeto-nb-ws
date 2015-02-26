<?php

namespace App\Controller;

use App\Model\Message;
use App\Model\Account;

use App\Controller\Component\AndroidPushNotification;

use Datetime;

class MessagesController extends AppController
{
	public function beforeFilter()
	{
		parent::beforeFilter();
		$this->Message = new Message;
	}

	public function add()
	{
		$data = $this->request->header_body_json;
		$now = new Datetime;
		$data['created'] = $now->format('Y-m-d H:i:s');;

		if ($this->Message->save($data)) {

			// Pega regid e name do target
			$Account = new Account;
			$account = $Account->get($data['account_id2'], ['Profile.name','Account.android_device_registration_id']);
			
			// Manda para o outro
			$payloadData = [
				'title' => 'Desenrolo',
				'view' => 'chat',
				'message' => $this->user->name . ': ' . substr($data['message'], 0, 40)
			];

			try {
				AndroidPushNotification::sendGCM($payloadData, $account->android_device_registration_id, rand(1, 100));
			} catch (Exception $e) {
				return $this->Response->error(400, 'Erro ao mandar notificação');
			}

			return $this->Response->success('ok');
		} else {
			return $this->Response->error(400, 'Erro ao salvar a mensagem');
		}
	}

	public function get()
	{
		$target = $this->request->get['target'];
		$lastUpdate = $this->request->get['lastUpdate'];

		$query = $this->Message->find();
		$query
			->cols(['*'])
			->where('((Message.account_id1 = :account_id AND Message.account_id2 = :target)')
			->orWhere('(Message.account_id2 = :account_id AND Message.account_id1 = :target))')
			->bindValues([
				'account_id' => $this->user->id,
				'target' => $target
			]);

		if ($lastUpdate != 'null'){	
			$query->where("Message.created >= '{$lastUpdate}'");
		}

		// echo $query->__toString();

		$messages = $this->Message->all($query);

		return $this->Response->success($messages);
	}
}
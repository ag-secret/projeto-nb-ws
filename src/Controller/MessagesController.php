<?php

namespace App\Controller;

use App\Model\Message;

class MessagesController extends AppController
{
	function __construct()
	{
		parent::__construct();
		$this->Message = new Message;
	}

	public function getChatMessages($account_id = null, $account_id2 = null)
	{
		if (!$account_id) {
			$this->response(400, 'error', 'Você não informou o ID da sua conta');
		}

		if (!$account_id2) {
			$this->response(400, 'error', 'Você não informou o ID da conta do  perfil a ser mostrado as mensagens');
		}

		$messages = $this->Message->getChatMessages($account_id, $account_id2);

		$this->response(200, 'ok', $messages);
	}
}
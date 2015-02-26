<?php

namespace App\Model;

class Message extends AppModel
{
	/**
	 * Nome da tabela que em este Model está ligado.
	 * @var string
	 */
	public $tableName = 'messages';
	public $tableAlias = 'Message';

	/**
	 * Pega as mensagens trocadas por dois perfis
	 * @param  integer $account_id  ID da conta da pessoa que está procurando as mensagens
	 * @param  integer $account_id2 ID da conta da pessoa que está conversando com a pessoa que está procurando as mensagens
	 * @return array              	Array com as mensagens trocadas pelos dois participantes da conversa
	 */
	// public function getChatMessages($account_id, $account_id2)
	// {
	// 	$query = $this->find();
	// 	$query
	// 		->cols([
	// 			'Message.account_id',
	// 			'Message.account_id2',
	// 			'Message.message'
	// 		])
	// 		->where('(Message.account_id = :account_id AND Message.account_id2 = :account_id2)')
	// 		->orWhere('(Message.account_id = :account_id2 AND Message.account_id2 = :account_id)')
	// 		->bindValues([
	// 			'account_id' => $account_id,
	// 			'account_id2' => $account_id2
	// 		]);

	// 	return $this->all($query);
	// }
}
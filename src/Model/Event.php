<?php

namespace App\Model;

use PDO;

class Event extends AppModel
{
	/**
	 * O nome da tabela em que este Model está ligado
	 * @var string
	 */
	public $tableName = 'events';
	public $tableAlias = 'Event';

	/**
	 * Checa os eventos que você está dentro 
	 * @return [type] [description]
	 */
	public function checkImIn()
	{
		$now = date('H:i:s');

		$query = $this->find();
		$query
			->cols([
				'Event.id',
				'Event.name',
				'Place.name AS place_name'
			])
			->join(
				'INNER',
				'places AS Place',
				'Place.id = Event.place_id'
			)
			->where('Place.status = 1');

		$result = $this->all($query);

		$events = [];
		if ($result) {
			$i = 0;
			foreach ($result as $row) {
				$events[$i]['id'] = $row->id;
				$events[$i]['name'] = $row->name;
				$events[$i]['place']['name'] = $row->place_name;
				$i++;
			}
		}

		return $events;
	}

}
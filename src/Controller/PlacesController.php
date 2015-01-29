<?php

namespace App\Controller;

use App\Model\Place;

class PlacesController extends AppController
{

	function __construct()
	{
		parent::__construct();
		$this->Place = new Place;
	}

	public function getCurrentPlace()
	{
		$query = $this->Place->find();
		$query
			->cols(['Place.id', 'Place.name'])
			->where('Place.status = 1');
		$places = $this->Place->all($query);

		return $this->response(200, 'ok', $places);
	}

	public function view($id)
	{
		$query = $this->Place->find();
		$query
			->cold(['*'])
			->where('Place.id = :id')
			->bindValues(['id' => $id]);

		$place = $this->Place->one($query);

		return $this->response(200, 'ok', $place);
	}
}
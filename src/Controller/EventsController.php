<?php

namespace App\Controller;

use App\Model\Event;

class EventsController extends AppController
{
	public function beforeFilter()
	{
		$this->Event = new Event;
	}

	public function checkImIn()
	{
		$events = $this->Event->checkImIn();
		return $this->Response->success($events);
	}
}
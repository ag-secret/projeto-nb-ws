<?php

namespace App\Model;

class Profile extends AppModel
{
	/**
	 * O nome da tabela em que este Model está ligado
	 * @var string
	 */
	public $tableName = 'profiles as Profile';

	/**
	 * O limite máximo de perfils que ProfilesController::getCloseProfiles() irá trazer
	 * @var integer
	 */
	public $closeProfilesLimit = 20;
}
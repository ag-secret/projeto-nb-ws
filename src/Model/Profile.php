<?php

namespace App\Model;

class Profile extends AppModel
{
	/**
	 * O nome da tabela em que este Model está ligado
	 * @var string
	 */
	public $tableName = 'profiles';
	public $tableAlias = 'Profile';

	/**
	 * O limite máximo de perfils que ProfilesController::getCloseProfiles() irá trazer
	 * @var integer
	 */
	public $closeProfilesLimit = 20;

	public function getShortGender($gender)
	{
		switch ($gender) {
			case 'Masculino':
				return 'm';
				break;
			case 'Feminino':
				return 'f';
				break;
			case 'Masculino e Feminino':
				return 'm,f';
				break;
		}
	}

	public function getFulltGender($gender)
	{
		switch ($gender) {
			case 'm':
				return 'Masculino';
				break;
			case 'f':
				return 'Feminino';
				break;
			case 'm,f':
				return 'Masculino e Feminino';
				break;
		}
	}

}
<?php

namespace App\Model;

use Facebook\FacebookSession;
use Facebook\FacebookRequest;
use Facebook\GraphUser;
use Facebook\FacebookRequestException;

use App\Controller\Component\MyFacebookApp;

use WideImage\WideImage;

use \Exception;

class Account extends AppModel
{
	/**
	 * O nome da tabela em que este Model está ligado
	 * @var string
	 */
	public $tableName = 'accounts';
	public $tableAlias = 'Account';

	public function getAccess($account_id)
	{
		$query = $this->find();
		$query
			->cols([
				'Account.username',
				'Account.app_access_token',
				'Account.facebook_id', // Para pegar a pasta aonde a foto está
				'Profile.id',
				'Profile.name',
				'Profile.account_id',
				'Profile.gender',
				'Profile.prefered_age_min',
				'Profile.prefered_age_max',
				'Profile.prefered_gender',
				'Profile.profile_img_url'
			])
			->join(
				'INNER',
				'profiles Profile',
				'Profile.account_id = Account.id'
			)
			->where('Profile.account_id = :account_id')
			->bindValues(['account_id' => $account_id]);

		$profile = $this->one($query);

		if ($profile) {
			$profile = $this->formatArrayGetAccess($profile);
		}
		
		return $profile;
	}

	public function formatArrayGetAccess($profile)
	{
		$result = [];

		$result['account']['id'] = $profile->account_id;
		$result['account']['username'] = $profile->username;
		$result['account']['app_access_token'] = $profile->app_access_token;

		$result['account']['profile']['id'] = $profile->id;
		$result['account']['profile']['name'] = $profile->name;
		$result['account']['profile']['gender'] = $profile->gender;
		$result['account']['profile']['profile_img']['regular'] = "http://bbgl.kinghost.net/img/{$profile->facebook_id}/profile_picture.jpg";
		$result['account']['profile']['profile_img']['small'] = "http://bbgl.kinghost.net/img/{$profile->facebook_id}/profile_picture_small.jpg";
		
		
		$result['account']['profile']['settings']['prefered_age']['min'] = $profile->prefered_age_min;
		$result['account']['profile']['settings']['prefered_age']['max'] = $profile->prefered_age_max;
		$result['account']['profile']['settings']['prefered_gender'] = $this->getGenderFullValue($profile->prefered_gender);

		return $result;
	}

	public function getGenderFullValue($value)
	{
		$result = null;
		switch ($value) {
			case 'm':
				$result = 'Masculino';
				break;
			case 'f':
				$result = 'Feminino';
				break;
			case 'm,f':
				$result = 'Masculino e Feminino';
				break;
		}

		return $result;
	}

	public function getFacebookAccessToken($account_id)
	{
		$query = $this->find();
		$query
			->cols(['facebook_access_token'])
			->where('id = :account_id')
			->bindValues(['account_id' => $account_id]);

		$account = $this->one($query);

		return $account->facebook_access_token;
	}

	public function getFacebookMe($session)
	{
		$params = ['fields' => 'id,email,name,gender,birthday'];
		try {
			$me = (new FacebookRequest(
				$session,
				'GET',
				'/me',
				$params
			))->execute()->getGraphObject(GraphUser::className());
		} catch (FacebookRequestException $e) {
			throw new Exception($e->getMessage());
		} catch (\Exception $e) {
			throw new Exception($e->getMessage());
		}

		return $me;
	}

	public function getByFacebookId($facebookId)
	{
		$query = $this->find();
		$query
			->cols([
				'Account.id',
			])
			->where('facebook_id = :facebook_id')
			->bindValues(['facebook_id' => $facebookId]);

		return $account = $this->one($query);
	}

	public function getProfilePictureFolder($id)
	{
		$imageFolder = WEBROOT . 'img' . DS . $id . DS;

		if (!file_exists($imageFolder)) {
			mkdir($imageFolder);
		}
		return $imageFolder;
	}

	public static function resizeAndSaveProfilePicture($img, $finalPath)
	{
		WideImage::load($img)
			->resize(400, 400, 'outside')
			->crop('center', 'top', 400, 400)
			->saveToFile($finalPath . 'profile_picture.jpg');

		WideImage::load($img)
			->resize(80, 80, 'outside')
			->crop('center', 'top', 80, 80)
			->saveToFile($finalPath . 'profile_picture_small.jpg');
	}

	public function get($account_id, $fields)
	{
		$query = $this->find();
		$query
			->cols($fields)
			->join(
				'INNER',
				'profiles Profile',
				'Profile.account_id = Account.id'
			)
			->where('account_id = :account_id')
			->bindValues(['account_id' => $account_id]);

		$profile = $this->one($query);

		return $profile;
	}

}
<?php

namespace App\Controller;

use App\Model\Account;
use App\Model\Profile;

use App\Controller\Component\MyFacebookApp;

use Facebook\FacebookRequest;
use Facebook\FacebookRequestException;
use Facebook\FacebookSession;
use Facebook\GraphUser;

// use Imagine\GD\Imagine;
// use Imagine\Image\Box;
// use Imagine\Image\ImageInterface;

use WideImage\WideImage;

use \DateTime;

class AccountsController extends AppController
{
	/**
	 * Executa antes das actions, funciona como um construtor
	 * @return void
	 */
	public function beforeFilter()
	{
		$this->Account = new Account;
	}

	public function politica_de_privacidade()
	{
		//echo '<img src="../img/teste.jpg">';
		$this->response(200, 'ok', 'texto aqui');
	}

	public function teste()
	{
		return $this->response(200, 'ok', 'YALL funciona please');
	}

	public function getAccess($account_id = null)
	{
		if (!$account_id) {
			return $this->Response->error(400, 'Você não informou o ID da conta');
		}

		$account = $this->Account->getAccess($account_id);

		if ($account) {
			return $this->Response->success($account);
		} else {
			return $this->Response->error(400, 'Conta inexistente');
		}
		
	}

	public function getAccountByFacebook()
	{
		$accessToken = $this->request->header_body_json['accessToken'];
		$regid = $this->request->header_body_json['pushRegid'];
		$platform = $this->request->header_body_json['platform'];

		FacebookSession::enableAppSecretProof(false);
		FacebookSession::setDefaultApplication(MyFacebookApp::APP_ID, MyFacebookApp::APP_SECRET);

		$session = new FacebookSession($accessToken);

		try {
			$me = $this->Account->getFacebookMe($session);
		} catch (Exception $e) {
			return $this->Response->error(400, $e->getMessage());
		}

		
		$mysqlDateTimeFormat = 'Y-m-d H:i:s';
		$now = new DateTime();

		$dataAccount = [
			'facebook_id' => $me->getId(),
			'android_device_registration_id' => $regid,
			'username' => $me->getEmail(),
			'created' => $now->format($mysqlDateTimeFormat),
			'facebook_access_token' => $accessToken,
			'platform' => $platform
		];

		$prefered_age_min = 18;
		$prefered_age_max = 60;

		$dataProfile = [
			'name' => $me->getName(),
			'gender' => $me->getGender() == 'male' ? 'm' : 'f',
			'prefered_gender' => $me->getGender() == 'male' ? 'f' : 'm',
			'prefered_age_min' => $prefered_age_min,
			'prefered_age_max' => $prefered_age_max,
			// 'birthday' => $me->getBirthday()->format($mysqlDateTimeFormat),
			'birthday' => $now->format($mysqlDateTimeFormat),
			'created' => $now->format($mysqlDateTimeFormat)
		];

		// Checa se tem esse ID no banco, caso não ele salva
		$account = $this->Account->getByFacebookId($me->getId());
		$app_access_token = $this->_appAccessTokenGenerator();

		if ($account) {
			$data = [
				'id' => $account->id,
				'app_access_token' => $app_access_token,
				'android_device_registration_id' => $regid // Mesmo se o usuario existir a gente deve fazer o upd do regid pq ele pode estar logando de outro device
			];
			$account->app_access_token = $data['app_access_token'];
			if ($this->Account->update($data)) {
				$account = $this->Account->getAccess($account->id);
			}
		} else {

			try {
				$mePicture = (new FacebookRequest(
					$session,
					'GET',
					'/me/picture',
					['redirect' => false, 'width' => 400, 'height' => 400]
				))->execute()->getGraphObject(GraphUser::className());

				$mePicture = $mePicture->asArray();
				$dataProfile['profile_img_url'] = $mePicture['url'];

				$folderPath = WEBROOT . 'img' . DS . $dataAccount['facebook_id'] . DS;

				if (!file_exists($folderPath)) {
					mkdir($folderPath, 0777, true);
				}

				Account::resizeAndSaveProfilePicture($mePicture['url'], $folderPath);

			} catch (FacebookRequestException $e) {
				return $this->Response->error(400, $e->getMessage());
			} catch (\Exception $e) {
				return $this->Response->error(400, $e->getMessage());
			}

			$dataAccount['app_access_token'] = $app_access_token;

			if ($this->Account->save($dataAccount)) {

				$dataProfile['account_id'] = $this->Account->lastInsertId;

				$Profile = new Profile;
				if ($Profile->save($dataProfile)) {
					$account = $this->Account->getAccess($dataProfile['account_id']);
				} else {
					return $this->Response->error(400, $this->Profile->validationErrors());
				}

			} else {
				return $this->Response->error(400, $this->Account->validationErrors());
			}
		}

		// $this->Account->update(['id' => $account['account']['id'], 'facebook_access_token' => $accessToken]);
		return $this->Response->success($account);
	}

	public function _appAccessTokenGenerator()
	{
		return rand(1000, 1999);
	}

	public function getFacebookProfilePictures($account_id)
	{
		$facebookAccessToken = $this->Account->getFacebookAccessToken($account_id);

		FacebookSession::enableAppSecretProof(false);
		FacebookSession::setDefaultApplication(MyFacebookApp::APP_ID, MyFacebookApp::APP_SECRET);

		$session = new FacebookSession($facebookAccessToken);

		try {
			$albums = (new FacebookRequest(
				$session,
				'GET',
				'/me/albums',
				['fields' => 'id,name']
			))->execute()->getGraphObject(GraphUser::className());

			$albums = $albums->asArray()['data'];
			$albumProfilePicturesId = $this->_getProfilePicturesId($albums);

		} catch (FacebookRequestException $e) {
			// The Graph API returned an error
			return $this->Response->error(400, $e->getMessage());
		} catch (\Exception $e) {
			// Some other error occurred
			return $this->Response->error(400, $e->getMessage());
		}

		try {
			$photos = (new FacebookRequest(
			$session,
			'GET',
			"/{$albumProfilePicturesId}/photos",
			['fields' => 'source']
		))->execute()->getGraphObject(GraphUser::className());

			$photos = $photos->asArray()['data'];
			
			return $this->Response->success($photos);

		} catch (FacebookRequestException $e) {
			// The Graph API returned an error
			return $this->Response->error(400, $e->getMessage());
		} catch (\Exception $e) {
			// Some other error occurred
			return $this->Response->error(400, $e->getMessage());
		}

	}

	public function _getProfilePicturesId($data)
	{
		$id = null;
	    foreach ($data as $key => $value) {
	    	if ($value->name == 'Profile Pictures') {
	    		$id = $value->id;
	    	}
	    }

	    return $id;
	}

	public function d()
	{
		$graphURL = 'https://graph.facebook.com/v2.2/me/';

	    $simpleFacebook = new SimpleFacebook($accessToken);
	    $response = $simpleFacebook->graphRequest('/me', ['fields' => 'albums']);
	    print_r($simpleFacebook->info);
	    // exit();
	    $albumId = null;
	    foreach ($response['albums']['data'] as $key => $value) {
	    	if ($value['name'] == 'Profile Pictures') {
	    		$albumId = $value['id'];
	    	}
	    }

	    if ($albumId) {
	    	$albumData = $simpleFacebook->graphRequest("/{$albumId}/photos", [
	    		'fields' => 'images',
	    		'limit' => 20
	    	]);

	    	$photos = [];
	    	foreach ($albumData['data'] as $key => $value) {
	    		foreach ($value['images'] as $image) {
	    			// Pega o mais alto
	    			$param = $image['width'] >= $image['height'] ? $image['width'] : $image['height'];
	    			if ($param <= 400) {
	    				$photos[] = $image['source'];
	    				break;
	    			}
	    		}
	    	}

	    	print_r($photos);
	    }
	    
	}
}
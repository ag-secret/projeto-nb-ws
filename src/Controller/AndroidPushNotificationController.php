<?php

namespace App\Controller;

use App\Model\Account;
use PHP_GCM\Sender;
use PHP_GCM\Message;

use App\Controller\Component\AndroidPushNotification;

use Exception;

class AndroidPushNotificationController extends AppController
{
	
	public function beforeFilter()
	{
		# code...
	}

	public function teste()
	{
		$regid = 'APA91bFCGBhualUqDRDF_3aRpHakdKhbR9kxOceSDwhDKLp1UqfWSe2XkmfxjqdzfCLoVzHimzoPfiMsQTKsVN_7WG0gnGaMBZGwI4jklM1AXmdcGBpcY2XzlIArg2ChBXL8ONYBA5S1R0vM3Wap0ccB6PcctThxse4z6cWZ9dNGozWCm11inBw';

		$data = [
			'title' => 'Totalmente diferente',
			'message' => 'Aqui tb'
		];

		try {
			AndroidPushNotification::sendGCM($data, $regid, 'teste');
		} catch (Exception $e) {
			echo $e->getMessage();
		}
		
		$data = [
			'title' => 'Segunda mensagem!!!',
			'message' => 'Aqui tb'
		];
		try {
			AndroidPushNotification::sendGCM($data, $regid, 'teste');
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	public function sendGCM()
	{
		$gcmApiKey = 'AIzaSyABl7lMCnSgjF9EltyXm5MWi3SaAJW0D1o';

		$deviceRegistrationId = 'APA91bFCGBhualUqDRDF_3aRpHakdKhbR9kxOceSDwhDKLp1UqfWSe2XkmfxjqdzfCLoVzHimzoPfiMsQTKsVN_7WG0gnGaMBZGwI4jklM1AXmdcGBpcY2XzlIArg2ChBXL8ONYBA5S1R0vM3Wap0ccB6PcctThxse4z6cWZ9dNGozWCm11inBw';

		$collapseKey = rand(0,100);
		$payloadData = [
			'title' => 'Teste',
			'message' => 'tey'
		];

		$numberOfRetryAttempts = 5;
		
		$payloadData = [
			'title' => 'Desenrolo',
			'message' => 'Você tem uma nova combinação.'
		];

		$sender = new Sender($gcmApiKey);
		$message = new Message($collapseKey, $payloadData);

		try {
		    $result = $sender->send($message, $deviceRegistrationId, $numberOfRetryAttempts);
		} catch (\InvalidArgumentException $e) {
		    // $deviceRegistrationId was null
		    throw new Exception($e->GetMessage(), 1);
		    
		} catch (PHP_GCM\InvalidRequestException $e) {
		    // server returned HTTP code other than 200 or 503
		   throw new Exception($e->GetMessage(), 1);
		} catch (\Exception $e) {
		    // message could not be sent
		    throw new Exception($e->GetMessage(), 1);
		}
	}

	public function saveDeviceRegistrationId($id)
	{

		$Account = new Account;

		if ($Account->update(['id' => $id, 'android_device_registration_id' => $this->header_body_json['regid']])) {
			return $this->Response->success('OK');
		} else {
			return $this->Response->error(400, 'Ocorreu um erro ao atualizar o drID');
		}
	}

}
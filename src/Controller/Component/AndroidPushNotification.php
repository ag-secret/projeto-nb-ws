<?php

namespace App\Controller\Component;

use PHP_GCM\Sender;
use PHP_GCM\Message;

use Exception;

class AndroidPushNotification
{
	const API_KEY = 'AIzaSyABl7lMCnSgjF9EltyXm5MWi3SaAJW0D1o';

	public static function sendGCM($payloadData, $deviceRegistrationId, $collapseKey = null)
	{

		$numberOfRetryAttempts = 5;

		$sender = new Sender(self::API_KEY);

		$message = new Message($collapseKey, $payloadData);

		try {
		   $sender->send($message, $deviceRegistrationId, $numberOfRetryAttempts);
		} catch (\InvalidArgumentException $e) {
		    // $deviceRegistrationId was null
		    throw new Exception($e->getMessage());
		    
		} catch (PHP_GCM\InvalidRequestException $e) {
		    // server returned HTTP code other than 200 or 503
		   throw new Exception($e->getMessage());
		} catch (Exception $e) {
		    // message could not be sent
		    throw new Exception($e->getMessage());
		}
	}
}
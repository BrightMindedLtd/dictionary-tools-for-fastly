<?php

namespace App\Fastly;

class ErrorMapper
{
	public static function getErrorMessage($httpCode)
	{
		switch ((int) $httpCode) {
			case 404:
				return 'Fastly error 404. Have you entered your service and dictionary IDs correctly?';
				break;
			case 401:
				return 'Fastly error 401. Have you entered your API key correctly?';
				break;
			case 429:
				return 'Fastly error 429. Rate limit reached, please try again later.';
				break;
			case 500:
				return 'Fastly error 500. Your dictionary may contain unexpected data. Please try again later or contact Fastly support for details.';
				break;
		}

		return "Fastly error $httpCode. Please try again later or contact Fastly support for details.";
	}
}
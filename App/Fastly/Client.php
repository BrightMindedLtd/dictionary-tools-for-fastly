<?php

namespace App\Fastly;

use Requests;

class Client
{
	/**
	 * @var string
	 */
	private $apiKey = '';

	public function __construct($apiKey)
	{
		$this->apiKey = $apiKey;
	}

	public function getDictionaryItems($serviceId, $dictionaryId)
	{
		$endpoint = sprintf('/service/%s/dictionary/%s/items', $serviceId, $dictionaryId);
		return json_decode($this->doRequest(
			$endpoint,
			Requests::GET
		)->body);
	}

	public function patchDictionaryItems($serviceId, $dictionaryId, $patchArray)
	{
		$endpoint = sprintf('/service/%s/dictionary/%s/items', $serviceId, $dictionaryId);
		$this->doRequest(
			$endpoint,
			Requests::PATCH,
			json_encode($patchArray)
		);
		return true;
	}

	public function doRequest($endpoint, $method, $data = [])
	{
		$response = Requests::request(
			'https://api.fastly.com' . $endpoint,
			array(
				'Content-Type' => 'application/json',
				'Fastly-Key' => $this->apiKey
			),
			$data,
			$method
		);
		if (!$response->success) {
			throw new \Exception('Request could not be made with response ' . $response->status_code);
		}
		return $response;
	}
}
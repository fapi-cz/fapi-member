<?php

namespace FapiMember\Model;

use FapiMember\Library\SmartEmailing\Types\StringType;

class ApiConnection
{
	private string|null $apiUser;
	private string|null $apiKey;

	public function __construct(string|null $apiUser, string|null $apiKey)
	{
		$this->apiUser = StringType::fromOrNull($apiUser);
		$this->apiKey = StringType::fromOrNull($apiKey);
	}

	public function getApiUser(): string|null
	{
		return $this->apiUser;
	}

	public function getApiKey(): string|null
	{
		return $this->apiKey;
	}

}

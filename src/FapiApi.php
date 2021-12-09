<?php

namespace FapiMember;

use WP_Error;

final class FapiApi
{

	public $lastError = null;

	private $apiUser;

	private $apiKey;

	private $apiUrl;

	public function __construct($apiUser, $apiKey, $apiUrl = 'https://api.fapi.cz/')
	{
		$this->apiUser = $apiUser;
		$this->apiKey = $apiKey;
		$this->apiUrl = $apiUrl;
	}

	/**
	 * @param int $id
	 * @return false|array<mixed>
	 */
	public function getInvoice($id)
	{
		$response = wp_remote_request(
			sprintf('%sinvoices/%s', $this->apiUrl, $id),
			[
				'method' => 'GET',
				'headers' => $this->createHeaders(),
			]
		);

		if ($response instanceof WP_Error || $response['response']['code'] !== 200) {
			$this->lastError = $this->findErrorMessage($response);

			return false;
		}

		return json_decode($response['body'], true);
	}

	/**
	 * @return array<mixed>
	 */
	protected function createHeaders()
	{
		return [
			'Content-Type' => 'application/json',
			'Accept' => 'application/json',
			'Authorization' => $this->createAuthHeader(),
		];
	}

	/**
	 * @return string
	 */
	protected function createAuthHeader()
	{
		return sprintf(
			'Basic %s',
			base64_encode(
				sprintf(
					'%s:%s',
					$this->apiUser,
					$this->apiKey
				)
			)
		);
	}

	/**
	 * @param WP_Error|array<mixed> $response
	 * @return mixed|string
	 */
	public function findErrorMessage($response)
	{
		if ($response instanceof WP_Error) {
			return $response->get_error_message();
		}

		if (isset($response['body'])) {
			return $response['body'];
		}

		return '';
	}

	/**
	 * @param int $id
	 * @return false|array<mixed>
	 */
	public function getVoucher($id)
	{
		$response = wp_remote_request(
			sprintf('%svouchers/%s', $this->apiUrl, $id),
			[
				'method' => 'GET',
				'headers' => $this->createHeaders(),
			]
		);

		if ($response instanceof WP_Error || $response['response']['code'] !== 200) {
			$this->lastError = $this->findErrorMessage($response);

			return false;
		}

		return json_decode($response['body'], true);
	}

	/**
	 * @param string $code
	 * @return false|array<mixed>
	 */
	public function getItemTemplate($code)
	{
		$response = wp_remote_request(
			sprintf('%sitem_templates/?code=%s', $this->apiUrl, $code),
			[
				'method' => 'GET',
				'headers' => $this->createHeaders(),
			]
		);

		if ($response instanceof WP_Error || $response['response']['code'] !== 200) {
			$this->lastError = $this->findErrorMessage($response);

			return false;
		}

		$data = json_decode($response['body'], true);

		if (!isset($data['item_templates'][0])) {
			return false;
		}

		return $data['item_templates'][0];
	}

	/**
	 * @return bool
	 */
	public function checkCredentials()
	{
		$response = wp_remote_request(
			sprintf('%s', $this->apiUrl),
			[
				'method' => 'GET',
				'headers' => $this->createHeaders(),
				'http_request_timeout' => 10
			]
		);

		if ($response instanceof WP_Error || $response['response']['code'] !== 200) {
			$this->lastError = $this->findErrorMessage($response);

			return false;
		}

		return true;
	}

	/**
	 * @param array<mixed> $invoice
	 * @param int $time
	 * @param string $expectedSecurity
	 * @return bool
	 */
	public function isInvoiceSecurityValid($invoice, $time, $expectedSecurity)
	{
		$id = isset($invoice['id']) ? (int) $invoice['id'] : '';
		$number = isset($invoice['number']) ? (int) $invoice['number'] : '';
		$itemsSecurityHash = '';
		$items = [];

		if (isset($invoice['items']) && is_array($invoice['items'])) {
			$items = $invoice['items'];
		}

		foreach ($items as $item) {
			$itemsSecurityHash .= md5($item['id'] . $item['name']);
		}

		return $expectedSecurity === sha1($time . $id . $number . $itemsSecurityHash);
	}

	/**
	 * @param array<mixed> $voucher
	 * @param array<mixed> $itemTemplate
	 * @param int $time
	 * @param string $expectedSecurity
	 * @return bool
	 */
	public function isVoucherSecurityValid($voucher, $itemTemplate, $time, $expectedSecurity)
	{
		$voucherId = isset($voucher['id']) ? $voucher['id'] : '';
		$voucherCode = isset($voucher['code']) ? $voucher['code'] : '';
		$itemTemplateId = isset($itemTemplate['id']) ? $itemTemplate['id'] : '';
		$itemTemplateCode = isset($itemTemplate['code']) ? $itemTemplate['code'] : '';
		$itemSecurityHash = md5($itemTemplateId . $itemTemplateCode);

		return $expectedSecurity === sha1($time . $voucherId . $voucherCode . $itemSecurityHash);
	}

}

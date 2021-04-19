<?php


class FapiApi {
	public $lastError = null;

	private $apiUser;
	private $apiKey;

	const FAPI_API_URL = 'https://api.fapi.cz/';

	public function __construct( $apiUser, $apiKey ) {
		$this->apiUser = $apiUser;
		$this->apiKey  = $apiKey;
	}

	public function getInvoice( $id ) {
		$resp = wp_remote_request(
			sprintf( '%sinvoices/%s', self::FAPI_API_URL, $id ),
			[
				'method'  => 'GET',
				'headers' => $this->createHeaders()
			]
		);
		if ( $resp instanceof WP_Error || $resp['response']['code'] !== 200 ) {
			$this->lastError = $resp['body'];

			return false;
		}

		return json_decode( $resp['body'], true );
	}

	public function getVoucher( $id ) {
		$resp = wp_remote_request(
			sprintf( '%svouchers/%s', self::FAPI_API_URL, $id ),
			[
				'method'  => 'GET',
				'headers' => $this->createHeaders()
			]
		);
		if ( $resp instanceof WP_Error || $resp['response']['code'] !== 200 ) {
			$this->lastError = $resp['body'];

			return false;
		}

		return json_decode( $resp['body'], true );
	}

	public function checkCredentials() {
		$resp = wp_remote_request(
			sprintf( '%s', self::FAPI_API_URL ),
			[
				'method'  => 'GET',
				'headers' => $this->createHeaders()
			]
		);

		return ( $resp['response']['code'] === 200 );
	}

	protected function createAuthHeader() {
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

	protected function createHeaders() {
		return [
			'Content-Type'  => 'application/json',
			'Accept'        => 'application/json',
			'Authorization' => $this->createAuthHeader(),
		];
	}

    protected function isInvoiceSecurityValid(array $invoice, int $time, string $expectedSecurity)
    {
        $id = (isset($invoice['id'])) ? $invoice['id'] : null;
        $number = (isset($invoice['number'])) ? $invoice['number'] : null;

        if ($id === null || $number === null) {
            return false;
        }

        $itemsSecurityHash = '';
        $items = (isset($invoice['items'])) ? $invoice['items'] : [];

        foreach ($items as $item) {
            $itemsSecurityHash .= \md5($item['id'] . $item['name']);
        }

        return ($expectedSecurity === \sha1($time . $id . $number . $itemsSecurityHash));
    }

    public static function isVoucherSecurityValid(array $voucher, array $itemTemplate, int $time, string $expectedSecurity)
    {
        $voucherId = (isset($voucher['id'])) ? $voucher['id'] : '';
        $voucherCode = (isset($voucher['code'])) ? $voucher['code'] : '';
        $itemTemplateId = (isset($itemTemplate['id'])) ? $itemTemplate['id'] : '';
        $itemTemplateCode = (isset($itemTemplate['code'])) ? $itemTemplate['code'] : '';
        $itemSecurityHash = \md5($itemTemplateId . $itemTemplateCode);

        return $expectedSecurity === \sha1($time . $voucherId . $voucherCode . $itemSecurityHash);
    }
}
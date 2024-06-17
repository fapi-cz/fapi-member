<?php

namespace FapiMember\Utils;

final class SecurityValidator
{
	public static function isInvoiceSecurityValid(
		array $invoice,
		int $time,
		string $expectedSecurity
	): bool
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

	public static function isVoucherSecurityValid(
		array $voucher,
		array $itemTemplate,
		int $time,
		string $expectedSecurity,
	): bool
	{
		$voucherId = isset($voucher['id']) ? $voucher['id'] : '';
		$voucherCode = isset($voucher['code']) ? $voucher['code'] : '';
		$itemTemplateId = isset($itemTemplate['id']) ? $itemTemplate['id'] : '';
		$itemTemplateCode = isset($itemTemplate['code']) ? $itemTemplate['code'] : '';
		$itemSecurityHash = md5($itemTemplateId . $itemTemplateCode);

		return $expectedSecurity === sha1($time . $voucherId . $voucherCode . $itemSecurityHash);
	}

}

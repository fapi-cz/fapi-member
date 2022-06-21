<?php

namespace FapiMember\Utils;

class SecurityValidator {


	/**
	 * @param  array<mixed> $invoice
	 * @param  int          $time
	 * @param  string       $expectedSecurity
	 * @return bool
	 */
	public static function isInvoiceSecurityValid( $invoice, $time, $expectedSecurity ) {
		$id                = isset( $invoice['id'] ) ? (int) $invoice['id'] : '';
		$number            = isset( $invoice['number'] ) ? (int) $invoice['number'] : '';
		$itemsSecurityHash = '';
		$items             = array();

		if ( isset( $invoice['items'] ) && is_array( $invoice['items'] ) ) {
			$items = $invoice['items'];
		}

		foreach ( $items as $item ) {
			$itemsSecurityHash .= md5( $item['id'] . $item['name'] );
		}

		return $expectedSecurity === sha1( $time . $id . $number . $itemsSecurityHash );
	}

	/**
	 * @param  array<mixed> $voucher
	 * @param  array<mixed> $itemTemplate
	 * @param  int          $time
	 * @param  string       $expectedSecurity
	 * @return bool
	 */
	public static function isVoucherSecurityValid( $voucher, $itemTemplate, $time, $expectedSecurity ) {
		$voucherId        = isset( $voucher['id'] ) ? $voucher['id'] : '';
		$voucherCode      = isset( $voucher['code'] ) ? $voucher['code'] : '';
		$itemTemplateId   = isset( $itemTemplate['id'] ) ? $itemTemplate['id'] : '';
		$itemTemplateCode = isset( $itemTemplate['code'] ) ? $itemTemplate['code'] : '';
		$itemSecurityHash = md5( $itemTemplateId . $itemTemplateCode );

		return $expectedSecurity === sha1( $time . $voucherId . $voucherCode . $itemSecurityHash );
	}
}

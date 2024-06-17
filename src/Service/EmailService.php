<?php

namespace FapiMember\Service;

use FapiMember\Container\Container;
use FapiMember\FapiMemberPlugin;
use FapiMember\Model\Enums\Keys\OptionKey;
use FapiMember\Model\Enums\Types\EmailType;
use FapiMember\Model\MemberLevel;
use FapiMember\Repository\EmailRepository;
use FapiMember\Repository\MembershipHistoryRepository;
use FapiMember\Utils\EmailHelper;
use FapiMember\Utils\SecurityValidator;

class EmailService
{
	private ApiService $apiService;
	private MembershipHistoryRepository $membershipHistoryRepository;
	private EmailRepository $emailRepository;

	public function __construct()
	{
		$this->apiService = Container::get(ApiService::class);
		$this->membershipHistoryRepository = Container::get(MembershipHistoryRepository::class);
		$this->emailRepository = Container::get(EmailRepository::class);
	}

	public function sendEmail(string $email, string $type, int $levelId, array $props): bool
	{
		$emails = $this->emailRepository->getTemplatesForLevel($levelId, true);

		if (!isset($emails[$type])) {
			return false;
		}

		$subject = $emails[ $type ]['s'];
		$body = $emails[ $type ]['b'];
		$subject = EmailHelper::replaceShortcodes($subject, $props);
		$body = EmailHelper::replaceShortcodes($body, $props);

		return wp_mail($email, $subject, $body);
	}

	/**
	 * @param array<MemberLevel> $levels
	 * @return array<array<string|MemberLevel>>
	 */
	public function findEmailsToSend(
		int $userId,
		array $levels,
		bool $wasUserCreated,
	): array
	{
		$toSend = [];

		foreach ($levels as $level) {
			if ($wasUserCreated === true) {
				$toSend[] = [EmailType::AFTER_REGISTRATION, $level];

				return $toSend;
			}

			$hadUserMembershipBefore = $this->membershipHistoryRepository
				->hadUserMembershipBefore($userId, $level->getId());

			if (!$hadUserMembershipBefore) {
				$toSend[] = [EmailType::AFTER_ADDING, $level];

				continue;
			}

			$toSend[] = [EmailType::AFTER_MEMBERSHIP_PROLONGED, $level];
		}

		return $toSend;
	}

	public function getEmailFromValidVoucher(array $data): array
	{
		$voucherId = $data['voucher'];
		$voucher = $this->apiService->getVoucher($voucherId);
		$voucherItemTemplateCode = $voucher['item_template_code'];
		$itemTemplate = $this->apiService->getItemTemplate($voucherItemTemplateCode);

		if ($voucher === false) {
			$this->apiService->callbackError(
				array(
					'class' => self::class,
					'description' => 'Error getting voucher.',
					'errors' => $this->apiService->getLastErrors(),
				)
			);
		}

		$itemTemplate = ($itemTemplate === false) ? array() : $itemTemplate;

		if (
			!FapiMemberPlugin::isDevelopment() &&
			!SecurityValidator::isVoucherSecurityValid(
				$voucher,
				$itemTemplate,
				$data['time'],
				$data['security'],
		)) {
			$this->apiService->callbackError(
				array(
					'class' => self::class,
					'description' => 'Voucher security is not valid.',
				)
			);
		}

		if (!isset($voucher['status']) || $voucher['status'] !== 'applied') {
			$this->apiService->callbackError(
				array(
					'class' => self::class,
					'description' => 'Voucher is not applied.',
				)
			);
		}

		if (!isset( $voucher['applicant']['email'])) {
			$this->apiService->callbackError(
				array(
					'class' => self::class,
					'description' => 'Cannot find applicant email in API response.',
				)
			);
		}

		return array('email' => $voucher['applicant']['email']);
	}

	public function getEmailFromPaidInvoice(array $data): array
	{
		$invoice = $this->apiService->getInvoice( $data['id'] );

		if ( $invoice === false ) {
			$this->apiService->callbackError(
				array(
					'class' => self::class,
					'description' => 'Error getting invoice.',
					'errors' => $this->apiService->getLastErrors(),
				)
			);
		}

		if (!FapiMemberPlugin::isDevelopment() &&
			!SecurityValidator::isInvoiceSecurityValid($invoice, $data['time'], $data['security'])
		) {
			$this->apiService->callbackError(
				array(
					'class' => self::class,
					'description' => 'Invoice security is not valid.',
				)
			);
		}

		if (isset($invoice['parent'])) {
			$this->apiService->callbackError(
				array(
					'class' => self::class,
					'description' => 'Invoice parent is set and not null.',
				)
			);
		}

		if (!isset($invoice['customer']['email'])) {
			$this->apiService->callbackError(
				array(
					'class' => self::class,
					'description' => 'Cannot find customer email in API response.',
				)
			);
		}

		return array(
			'email' => $invoice['customer']['email'],
			'first_name' => isset($invoice['customer']['first_name']) ? $invoice['customer']['first_name'] : null,
			'last_name' => isset($invoice['customer']['last_name']) ? $invoice['customer']['last_name'] : null,
		);
	}

	public function getEmailFromBodyWithValidToken(array $data): array
	{
		$token = get_option(OptionKey::TOKEN, null);

		if ($data['token'] !== $token) {
			$this->apiService->callbackError(
				array(
					'class' => self::class,
					'description' => 'Invalid token provided. Check token correctness.',
				)
			);
		}

		if (!isset($data['email'])) {
			$this->apiService->callbackError(
				array(
					'class' => self::class,
					'description' => 'Parameter email is missing.',
				)
			);
		}

		return array(
			'email' => $data['email'],
			'first_name' => isset($data['first_name']) ? $data['first_name'] : null,
			'last_name' => isset($data['last_name']) ? $data['last_name'] : null,
		);
	}

}

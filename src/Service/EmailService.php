<?php

namespace FapiMember\Service;

use FapiMember\Api\V2\ApiController;
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
	private ApiController $apiController;

	public function __construct()
	{
		$this->apiService = Container::get(ApiService::class);
		$this->membershipHistoryRepository = Container::get(MembershipHistoryRepository::class);
		$this->emailRepository = Container::get(EmailRepository::class);
		$this->apiController = Container::get(ApiController::class);
	}

	public function sendEmail(string $email, string $type, int $levelId, array $props): bool
	{
		$emails = $this->emailRepository->getTemplatesForLevel($levelId, true);

		if (!isset($emails[$type])) {
			return false;
		}

		$subject = $emails[$type]['s'];
		$body = $emails[$type]['b'];
		$subject = EmailHelper::replaceShortcodes($subject, $props);
		$body = EmailHelper::replaceShortcodes($body, $props);

		return wp_mail($email, $subject, $body);
	}

	/**
	 * @param array<MemberLevel> $levels
	 * @return array<array<string|MemberLevel>>
	 */
	public function findEmailsToSend(
		array $levels,
		bool $wasUserCreated,
		bool $newToMembership,
	): array
	{
		$toSend = [];

		foreach ($levels as $level) {
			if ($wasUserCreated === true) {
				$toSend[] = [EmailType::AFTER_REGISTRATION, $level];

				return $toSend;
			}

			if ($newToMembership) {
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

		if ($voucher === false) {
			$this->apiController->callbackError(
				array(
					'class' => self::class,
					'description' => 'Error getting voucher.',
					'errors' => $this->apiService->getLastErrors(),
				)
			);
		}

		if (!isset($voucher['status']) || $voucher['status'] !== 'applied') {
			$this->apiController->callbackError(
				array(
					'class' => self::class,
					'description' => 'Voucher is not applied.',
				)
			);
		}

		if (!isset($voucher['applicant']['email'])) {
			$this->apiController->callbackError(
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
		$invoice = $this->apiService->getInvoice((int) $data['id']);

		if ($invoice === false) {
			$this->apiController->callbackError(
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
			$this->apiController->callbackError(
				array(
					'class' => self::class,
					'description' => 'Invoice security is not valid.',
				)
			);
		}

		if (isset($invoice['parent'])) {
			$this->apiController->callbackError(
				array(
					'class' => self::class,
					'description' => 'Invoice parent is set and not null.',
				)
			);
		}

		if (!isset($invoice['customer']['email'])) {
			$this->apiController->callbackError(
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
			$this->apiController->callbackError(
				array(
					'class' => self::class,
					'description' => 'Invalid token provided. Check token correctness.',
				)
			);
		}

		if (!isset($data['email'])) {
			$this->apiController->callbackError(
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

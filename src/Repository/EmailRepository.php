<?php

namespace FapiMember\Repository;

use FapiMember\Container\Container;
use FapiMember\Model\Enums\Types\EmailType;

class EmailRepository extends Repository
{
	private LevelRepository $levelRepository;

	public function __construct()
	{
		$this->levelRepository = Container::get(LevelRepository::class);
	}

	public function remove(int $levelId, string $emailType): void
	{
		$this->deleteTermMeta($levelId, $this->getEmailTemplateKey($emailType));
	}

	public function update(int $levelId, string $emailType, string $mailSubject, string $mailBody): void
	{
		$this->updateTermMeta(
			$levelId,
			$this->getEmailTemplateKey($emailType),
			[
				's' => $mailSubject,
				'b' => $mailBody,
			],
		);
	}

	public function getTemplatesForLevel(int|null $levelId, bool $useCascade = false): array
	{
		if($levelId === null) {
			return [];
		}

		$meta = [];

		foreach (EmailType::getAvailableValues() as $type) {
			$template = $this->getTermMeta($levelId, $this->getEmailTemplateKey($type));

			if (!empty($template)) {
				$meta[$type] = $template;
			}
		}

		if ($useCascade && count($meta) !== count(EmailType::getAvailableValues())){
			$level = $this->levelRepository->getLevelById($levelId);
			$parentEmails = $this->getTemplatesForLevel($level->getParentId());

			foreach (EmailType::getAvailableValues() as $type) {
				if (!isset($meta[$type]) && isset($parentEmails[$type])) {
					$meta[$type] = $parentEmails[$type];
				}
			}
		}

		return $meta;
	}

	public function getEmailTemplateKey(string $type): string
	{
		return sprintf('fapi_email_%s', $type);
	}

}

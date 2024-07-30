<?php

namespace FapiMember\Utils;

use FapiMember\Container\Container;
use FapiMember\Model\Enums\Keys\MetaKey;
use FapiMember\Model\Enums\Keys\SettingsKey;
use FapiMember\Model\User;
use FapiMember\Repository\LevelRepository;
use FapiMember\Repository\MembershipRepository;
use FapiMember\Repository\SettingsRepository;
use FapiMember\Repository\UserRepository;

class ShortcodeSubstitutor
{
	private SettingsRepository $settingsRepository;
	private MembershipRepository $membershipRepository;
	private LevelRepository $levelRepository;
	private UserRepository $userRepository;

	public function __construct()
	{
		$this->settingsRepository = Container::get(SettingsRepository::class);
		$this->membershipRepository = Container::get(MembershipRepository::class);
		$this->levelRepository = Container::get(LevelRepository::class);
		$this->userRepository = Container::get(UserRepository::class);
	}

	public function shortcodeLoginForm(): string
	{
		return '
			<div class="fapiShortcodeLoginForm">
				<form method="post" action="' . wp_login_url() . '">
					<div class="f-m-row">
						<label for="log">' . __('Přihlašovací jméno', 'fapi-member') . '</label>
						<input type="text" name="log" id="user_login" value="" size="20">
					</div>
					<div class="f-m-row">
						<label for="pwd">' . __( 'Heslo', 'fapi-member' ) . '</label>
						<input type="password" name="pwd" id="user_pass" value="" size="20">
					</div>
					<div class="f-m-row">
					<a href="' . wp_lostpassword_url() . '">' . __( 'Zapomněli jste heslo?', 'fapi-member' ) . '</a>
					</div>
					<div class="f-m-row controls">
						<input type="submit" class="primary" value="' . __( 'Přihlásit se', 'fapi-member' ) . '">
					</div>
				</form>
			</div>     
    	';
	}

	public function shortcodeUser(): string
	{
		$u = $this->userRepository->getCurrentUser();

		if ($u instanceof User) {
			return '
				<div class="fapiShortcodeUser">
					<span class="i">
						<svg version="1.1" id="Capa_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
							 viewBox="0 0 512 512" style="enable-background:new 0 0 512 512;" xml:space="preserve">
						<g>
							<g>
								<path d="M437.02,330.98c-27.883-27.882-61.071-48.523-97.281-61.018C378.521,243.251,404,198.548,404,148
									C404,66.393,337.607,0,256,0S108,66.393,108,148c0,50.548,25.479,95.251,64.262,121.962
									c-36.21,12.495-69.398,33.136-97.281,61.018C26.629,379.333,0,443.62,0,512h40c0-119.103,96.897-216,216-216s216,96.897,216,216
									h40C512,443.62,485.371,379.333,437.02,330.98z M256,256c-59.551,0-108-48.448-108-108S196.449,40,256,40
									c59.551,0,108,48.448,108,108S315.551,256,256,256z"/>
							</g>
						</g>
						</svg>
					</span>
					<span class="h">' . __( 'Uživatel', 'fapi-member' ) . '</span>
					<div>
						<span class="l">' . $u->getLogin() . '</span><span class="dots">...</span>
					</div>
					<div class="f-m-submenu">
						<a href="' . wp_logout_url( get_permalink() ) . '">' . __( 'Odhlásit se', 'fapi-member' ) . '</a>
					</div>
				</div>    
			';
		}

		$setLoginPageId = $this->settingsRepository->getSetting(SettingsKey::LOGIN_PAGE);

		if ($setLoginPageId === null) {
			$url = wp_login_url();
		} else {
			$url = get_permalink($setLoginPageId);
		}

		return '
			<div class="fapiShortcodeUser notLogged">
				<span class="i">
					<svg id="bold" enable-background="new 0 0 24 24" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg"><path d="m18.75 9h-.75v-3c0-3.309-2.691-6-6-6s-6 2.691-6 6v3h-.75c-1.24 0-2.25 1.009-2.25 2.25v10.5c0 1.241 1.01 2.25 2.25 2.25h13.5c1.24 0 2.25-1.009 2.25-2.25v-10.5c0-1.241-1.01-2.25-2.25-2.25zm-10.75-3c0-2.206 1.794-4 4-4s4 1.794 4 4v3h-8zm5 10.722v2.278c0 .552-.447 1-1 1s-1-.448-1-1v-2.278c-.595-.347-1-.985-1-1.722 0-1.103.897-2 2-2s2 .897 2 2c0 .737-.405 1.375-1 1.722z"/></svg>
				</span>
				<span class="l"><a href="' . $url . '">' . __( 'Přihlásit se', 'fapi-member' ) . '</a></span>
			</div>
		';
	}

	public function shortcodeSectionExpirationDate(array $attrs): string
	{
		if ( ! isset( $attrs['section'] ) ) {
			return __( 'neznámá sekce nebo úrověň', 'fapi-member' );
		}

		$user = wp_get_current_user();

		if ( $user === null ) {
			return __( 'uživatel není přihlášen', 'fapi-member' );
		}

		$sectionOrLevelId = (int) $attrs['section'];

		$dateFormat = get_option( 'date_format' );

		if ( $dateFormat === null ) {
			$dateFormat = 'Y-m-d';
		}

		$memberships = $this->membershipRepository->getActiveByUserId($user->ID);
		$currentMemberShip = null;

		foreach ($memberships as $membership) {
			if ( $membership->getLevelId() === $sectionOrLevelId) {
				$currentMemberShip = $membership;

				break;
			}
		}

		if ( $currentMemberShip === null ) {
			return __( 'bez přístupu', 'fapi-member' );

		}

		if ( $currentMemberShip->getUntil() === null ) {
			return __( 'neomezeně', 'fapi-member' );
		}

		return $currentMemberShip->getUntil()->format($dateFormat);
	}

	public function shortcodeLevelUnlockDate(array $attrs): string
	{
		if (!isset( $attrs['level'] ) ) {
			return __( 'neznámá úrověň', 'fapi-member' );
		}

		$user = wp_get_current_user();

		if ( $user === null ) {
			return __( 'uživatel není přihlášen', 'fapi-member' );
		}

		$sectionOrLevelId = (int) $attrs['level'];

		$dateFormat = get_option('date_format');

		if ( $dateFormat === null ) {
			$dateFormat = 'Y-m-d';
		}

		$memberships = $this->membershipRepository->getActiveByUserId($user->ID);
		$currentMemberShip = null;
		$parentMembership = null;

		foreach ($memberships as $membership) {
			if ($membership->getLevelId() === $sectionOrLevelId) {
				$currentMemberShip = $membership;
				break;
			}
		}

		if (
			$currentMemberShip === null &&
			(bool) get_term_meta($sectionOrLevelId, MetaKey::TIME_UNLOCK, true) === false
		) {
			return __( 'bez přístupu', 'fapi-member' );
		}

		$level = $this->levelRepository->getLevelById($sectionOrLevelId);

		foreach ($memberships as $membership) {
			if ($membership->getLevelId() === $level->getParentId()) {
				$parentMembership = $membership;
				break;
			}
		}

		if (
			$parentMembership === null
		) {
			return __( 'bez přístupu', 'fapi-member' );
		}

		if ($currentMemberShip === null && $parentMembership->getRegistered() !== null) {
			$daysToUnlock = get_term_meta($sectionOrLevelId, MetaKey::DAYS_TO_UNLOCK, true);

			$unlockDate = date(
					'd.m.Y',
					strtotime($parentMembership->getRegistered()->format($dateFormat))
					+ (86400 * (int) $daysToUnlock),
			);

			return __( 'Bude odemčeno', 'fapi-member' ) . " " . $unlockDate;
		}

		if ($currentMemberShip->getUntil() === null) {
			return __( 'Neomezeně', 'fapi-member' );
		}

		return $currentMemberShip->getUntil()->format($dateFormat);
	}

	public function shortcodeUnlockLevel(array $attrs): string
	{
		if (!isset($attrs['level']) || $attrs['level'] === '') {
			return __('neznámá sekce nebo úrověň', 'fapi-member');
		}

		$levelId = (int) $attrs['level'];
		$page = isset($attrs['page'])
			? '&page_id=' . (int) $attrs['page']
			: '';

		return '<a href="?rest_route=/fapi/v2/memberships&action=unlockLevelForLoggedInUser&level_id='
			. $levelId . '&user_id=' . $this->userRepository->getCurrentUser()->getId() . $page .
			'" class="button-level-unlock-link">Odemknout úroveň</a>';
	}

}

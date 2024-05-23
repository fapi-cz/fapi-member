<?php declare(strict_types=1);

namespace FapiMember\Service;

use DateTimeInterface;
use FapiMember\Container\Container;
use FapiMember\Model\Enums\PostValue;
use FapiMember\Model\Enums\UserPermission;
use FapiMember\Model\MemberLevel;
use FapiMember\Model\MemberSection;
use FapiMember\Repository\LevelRepository;
use FapiMember\Repository\MembershipRepository;
use FapiMember\Repository\UserRepository;
use FapiMember\Utils\PostTypeHelper;
use FapiMember\Utils\TemplateProvider;
use WP_Post;
use WP_User;
use function add_meta_box;

class ElementService
{
	private TemplateProvider $templateProvider;
	private LevelRepository $levelRepository;
	private MembershipRepository $membershipRepository;
	private UserRepository $userRepository;

	public function __construct()
	{
		$this->templateProvider = Container::get(TemplateProvider::class);
		$this->levelRepository = Container::get(LevelRepository::class);
		$this->membershipRepository = Container::get(MembershipRepository::class);
		$this->userRepository = Container::get(UserRepository::class);
	}

	public function addUserProfileForm(WP_User $user): void
	{
		$sections = $this->levelRepository->getAllSections();
		$output[] = '<h2>' . __( 'Členské sekce', 'fapi-member' ) . '</h2>';

		foreach ($sections as $section ) {
			$output[] = $this->addUserProfileSection($section, $user->ID);
		}

		echo implode('', $output);
	}

	public function addUserProfileSection(MemberSection $section, int $userId): string
	{
		$levelsHtml = [];

		foreach ($section->getLevels() as $level) {
			$membership = $this->membershipRepository->getOneByUserIdAndLevelId(
				$userId,
				$level->getId(),
			);

			$checked = '';
			$isUnlimited = '';
			$regDate = '';
			$regTime = 'value="00:00"';
			$untilDate = '';

			if ($membership !== null) {
				$checked = 'checked';

				if ($membership->isUnlimited()) {
					$isUnlimited = 'checked';
				}

				if ($membership->getRegistered() !== null) {
					$regDate = sprintf( 'value="%s"', $membership->getRegistered()->format( 'Y-m-d' ) );
					$regTime = sprintf( 'value="%s"', $membership->getRegistered()->format( 'H:i' ) );
				}

				if ($membership->getUntil() !== null) {
					$untilDate = sprintf( 'value="%s"', $membership->getUntil()->format( 'Y-m-d' ) );
				}
			}

			$levelsHtml[] = sprintf(
				'
                    <div class="oneLevel">
                        <input class="check" type="checkbox" name="Levels[%s][check]" id="Levels[%s][check]" %s>
                        <label class="levelName"  for="Levels[%s][check]">%s</label>
                        <label class="registrationDate" for="Levels[%s][registrationDate]">' . __( 'Datum registrace', 'fapi-member' ) . '</label>
                        <input class="registrationDateInput" type="date" name="Levels[%s][registrationDate]" %s>
                        <label class="registrationTime" for="Levels[%s][registrationTime]">' . __( 'Čas registrace', 'fapi-member' ) . '</label>
                        <input class="registrationTimeInput" type="time" name="Levels[%s][registrationTime]" %s>
                        <label class="membershipUntil" data-for="Levels[%s][membershipUntil]" for="Levels[%s][membershipUntil]">' . __( 'Členství do', 'fapi-member' ) . '</label>
                        <input class="membershipUntilInput" type="date" name="Levels[%s][membershipUntil]" %s>
                        <label class="isUnlimited" for="Levels[%s][isUnlimited]">' . __( 'Bez expirace', 'fapi-member' ) . '</label>
                        <input class="isUnlimitedInput" type="checkbox" name="Levels[%s][isUnlimited]" %s>
                    </div>
                    ',
				$level->getId(), $level->getId(), $checked, $level->getId(), $level->getName(), $level->getId(),
				$level->getId(), $regDate, $level->getId(), $level->getId(), $regTime, $level->getId(),
				$level->getId(), $level->getId(), $untilDate, $level->getId(), $level->getId(), $isUnlimited
			);
		}

		$membership = $this->membershipRepository->getOneByUserIdAndLevelId(
			$userId,
			$section->getId(),
		);

		$checked = '';
		$isUnlimited = '';
		$regDate = '';
		$regTime = 'value="00:00"';
		$untilDate = '';

		if ($membership !== null) {
			$checked = 'checked';

			if ($membership->isUnlimited()) {
				$isUnlimited = 'checked';
			}

			if (is_a($membership->getRegistered(), DateTimeInterface::class)) {
				$regDate = sprintf('value="%s"', $membership->getRegistered()->format('Y-m-d'));
				$regTime = sprintf('value="%s"', $membership->getRegistered()->format('H:i'));
			}

			if (is_a($membership->getUntil(), DateTimeInterface::class)) {
				$untilDate = sprintf( 'value="%s"', $membership->getUntil()->format('Y-m-d'));
			}
		}

		return '
        <table class="wp-list-table widefat fixed striped fapiMembership">
            <thead>
            <tr>
                <td id="cb" class="manage-column column-cb check-column">
                    <label class="screen-reader-text" for="Levels[' . $section->getId() . '][check]">' . __( 'Vybrat', 'fapi-member' ) . '</label>
                    <input id="Levels[' . $section->getId() . '][check]" name="Levels[' . $section->getId() . '][check]" type="checkbox" ' . $checked . '>
                </td>
                <th scope="col" id="title" class="manage-column column-title column-primary">
                    <span>' . $section->getName() . '</span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a">' . __( 'Datum registrace', 'fapi-member' ) . '</span>
                    <span class="b">
                    <input type="date" name="Levels[' . $section->getId() . '][registrationDate]" ' . $regDate . '>
                    </span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a">' . __( 'Čas registrace', 'fapi-member' ) . '</span>
                    <span class="b">
                    <input type="time" name="Levels[' . $section->getId() . '][registrationTime]" ' . $regTime . '>
                    </span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a" data-for="Levels[' . $section->getId() . '][membershipUntil]">Členství do</span>
                    <span class="b">
                    <input type="date" name="Levels[' . $section->getId() . '][membershipUntil]" ' . $untilDate . '>
                    </span>
                </th>
                <th scope="col" class="manage-column fields">
                    <span class="a">' . __( 'Bez expirace', 'fapi-member' ) . '</span>
                    <span class="b">
                    <input class="isUnlimitedInput" type="checkbox" name="Levels[' . $section->getId() . '][isUnlimited]" ' . $isUnlimited . '>
                    </span>
                </th>
            </thead>
        
            <tbody id="the-list">
                <tr><td colspan="6">
                    ' . implode('', $levelsHtml) . '
                </td></tr>
            </tbody>
        </table>
        ';
	}

	public function addAdminMenu(): void
	{
		add_menu_page(
			'FAPI Member',
			'FAPI Member',
			UserPermission::REQUIRED_CAPABILITY,
			'fapi-member-options',
			array($this->templateProvider, 'displayCurrentTemplate'),
			sprintf(
				'data:image/svg+xml;base64,%s',
				base64_encode(file_get_contents( __DIR__ . '/../../_sources/F_fapi2.svg' ) )
			),
			81
		);
	}

	public function addMetaBoxes(): void
	{
		$screens = PostTypeHelper::getSupportedPostTypes();

		add_meta_box(
			'fapi_member_meta_box_id',
			'FAPI Member',
			function (WP_Post $post) {
				echo '<p>' . __( 'Přiřazené sekce a úrovně', 'fapi-member' ) . '</p>';
				echo '<input name="' . PostValue::SECTIONS . '[]" checked="checked" type="checkbox" value="-1" style="display: none !important;">';

				$levels = $this->levelRepository->getAllAsLevels();
				$sections = $this->levelRepository->getAllSections();
				$levelsForThisPage = [];

				foreach ($levels as $level) {
					if (in_array($post->ID, $level->getPageIds(), true)) {
						$levelsForThisPage[] = $level->getId();
					}
				}

				foreach ($sections as $section) {
						echo '<p>';
						echo $this->renderCheckbox($section, $levelsForThisPage);

						foreach ($section->getLevels() as $level) {
								echo '<span style="margin: 15px;"></span>' . $this->renderCheckbox( $level, $levelsForThisPage);
						}

						echo '</p>';
					}
			},
			$screens,
			'side'
		);
	}

	public function hideAdminBar($original) {
		$user = $this->userRepository->getCurrentUser();

		if ($user !== null && in_array('member', $user->getRoles(), true)) {
			return false;
		}

		return $original;
	}

	public static function renderCheckbox(MemberSection|MemberLevel $level, array $levelsForThisPage): string
	{
		$isAssigned = in_array($level->getId(), $levelsForThisPage, true);

		return '<input name="' . PostValue::SECTIONS . '[]" ' . ($isAssigned ? 'checked="checked"' : '' ) . 'type="checkbox" value="' . $level->getId(). '">' . $level->getName() . '<br>';
	}

}

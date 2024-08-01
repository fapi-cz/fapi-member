<?php declare(strict_types=1);

namespace FapiMember\Service;

use FapiMember\Container\Container;
use FapiMember\Model\Enums\PostValue;
use FapiMember\Model\Enums\UserPermission;
use FapiMember\Model\MemberLevel;
use FapiMember\Model\MemberSection;
use FapiMember\Repository\LevelRepository;
use FapiMember\Repository\UserRepository;
use FapiMember\Utils\PostTypeHelper;
use WP_Post;
use function add_meta_box;

class ElementService
{
	private LevelRepository $levelRepository;
	private UserRepository $userRepository;

	public function __construct()
	{
		$this->levelRepository = Container::get(LevelRepository::class);
		$this->userRepository = Container::get(UserRepository::class);
	}

	public function addAdminMenu(): void
	{
        $slug = 'fapi-member-settings';

        add_menu_page(
            __( 'Fapi Member', 'fapi-member' ),
            __( 'Fapi Member', 'fapi-member' ),
            UserPermission::REQUIRED_CAPABILITY,
            $slug,
            [$this, 'addMenuPage'],
            sprintf(
				'data:image/svg+xml;base64,%s',
				base64_encode( file_get_contents( __DIR__ . '/../../_sources/F_fapi2.svg') )
			)
        );
    }

    public function addMenuPage(): void
	{
        echo '<div class="wrap"><div id="fm-settings"></div></div>';
    }

	public function addUserMenuPage(): void
	{
        echo '<div class="wrap"><div id="fm-user-settings"></div></div>';
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

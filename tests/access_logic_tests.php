<?php declare(strict_types=1);

use FapiMember\Api\V2\ApiController;
use FapiMember\Api\V2\Endpoints\MembershipsController;
use FapiMember\Model\Enums\Format;
use FapiMember\Model\MemberLevel;
use FapiMember\Model\Membership;
use FapiMember\Repository\LevelRepository;
use FapiMember\Repository\MembershipRepository;
use FapiMember\Repository\PageRepository;
use FapiMember\Repository\UserRepository;
use FapiMember\Service\MembershipService;
use FapiMember\Service\RedirectService;
use FapiMember\Utils\DateTimeHelper;
use FapiMember\Utils\DisplayHelper;
use FapiMember\Utils\ShortcodeSubstitutor;

require __DIR__ . '/../vendor/autoload.php';

if (!class_exists('WP_REST_Request')) {
	class WP_REST_Request
	{
		public function __construct(private array $params = [])
		{
		}

		public function get_method(): string
		{
			return 'GET';
		}

		public function get_params(): array
		{
			return $this->params;
		}
	}
}

if (!function_exists('wp_timezone')) {
	function wp_timezone(): DateTimeZone
	{
		return new DateTimeZone('Europe/Prague');
	}
}

$currentUserId = 7;
$redirectUrl = null;

if (!function_exists('get_current_user_id')) {
	function get_current_user_id(): int
	{
		global $currentUserId;

		return $currentUserId;
	}
}

if (!function_exists('home_url')) {
	function home_url(): string
	{
		return 'https://example.test';
	}
}

if (!function_exists('get_site_url')) {
	function get_site_url(): string
	{
		return 'https://example.test';
	}
}

if (!function_exists('user_can')) {
	function user_can(int $userId, string $capability): bool
	{
		return false;
	}
}

if (!function_exists('wp_get_current_user')) {
	function wp_get_current_user(): object
	{
		return (object) ['ID' => 7];
	}
}

if (!function_exists('get_option')) {
	function get_option(string $key, mixed $default = false): mixed
	{
		return $key === 'date_format' ? 'Y-m-d' : $default;
	}
}

if (!function_exists('__')) {
	function __(string $text, string|null $domain = null): string
	{
		return $text;
	}
}

if (!function_exists('wp_redirect')) {
	function wp_redirect(string $url): void
	{
		global $redirectUrl;
		$redirectUrl = $url;
	}
}

final class AccessDenied extends RuntimeException
{
}

final class ExistingLevelRepository extends LevelRepository
{
	/** @param array<int> $existingLevelIds */
	public function __construct(private array $existingLevelIds)
	{
	}

	public function exists(int $levelId): bool
	{
		return in_array($levelId, $this->existingLevelIds, true);
	}
}

final class InMemoryMembershipRepository extends MembershipRepository
{
	/**
	 * @param array<Membership> $allMemberships
	 * @param array<Membership>|null $accessibleMemberships
	 */
	public function __construct(
		private array $allMemberships,
		LevelRepository $levelRepository,
		private array|null $accessibleMemberships = null,
	) {
		$property = new ReflectionProperty(MembershipRepository::class, 'levelRepository');
		$property->setValue($this, $levelRepository);
	}

	public function getAllByUserId(int $userId): array
	{
		return $this->allMemberships;
	}

	public function getAccessibleByUserId(int $userId): array
	{
		return $this->accessibleMemberships ?? $this->getActiveByUserId($userId, true);
	}
}

final class UnlockLevelRepository extends LevelRepository
{
	public function __construct(private MemberLevel $level)
	{
	}

	public function getLevelById(int $id): MemberLevel|null
	{
		return $id === $this->level->getId() ? $this->level : null;
	}

	public function isButtonUnlock(int $levelId): bool
	{
		return $levelId === $this->level->getId();
	}
}

final class TestApiController extends ApiController
{
	public function checkRequestMethod(WP_REST_Request $request, string $method): void
	{
	}

	public function extractParamOrNull(array $array, string $key, string $type): mixed
	{
		return isset($array[$key]) ? (int) $array[$key] : null;
	}
}

final class DenyingRedirectService extends RedirectService
{
	public function __construct()
	{
	}

	public function redirectToNoAccessPage($levelId): void
	{
		throw new AccessDenied();
	}
}

final class CapturingMembershipService extends MembershipService
{
	public Membership|null $savedMembership = null;

	public function __construct()
	{
	}

	public function saveOne(Membership $newMembership): void
	{
		$this->savedMembership = $newMembership;
	}
}

final class UnlockPageRepository extends PageRepository
{
	public function __construct()
	{
	}

	public function getCommonDashboardPageId(): int|null
	{
		return 123;
	}
}

final class FutureAwareMembershipService extends MembershipService
{
	/** @param array<Membership> $futureMemberships */
	public function __construct(private array $futureMemberships)
	{
	}

	public function getActiveByUserIdAndUpdate(int $userId): array
	{
		return $this->futureMemberships;
	}

	public function getActiveWithAccessByUserId(int $userId): array
	{
		return [];
	}
}

final class LoginLevelRepository extends LevelRepository
{
	public function __construct(private MemberLevel $level)
	{
	}

	public function getLevelById(int $id): MemberLevel|null
	{
		return $id === $this->level->getId() ? $this->level : null;
	}
}

final class LoginPageRepository extends PageRepository
{
	public function __construct()
	{
	}

	public function getPageIdsByLevelId(int $levelId): array
	{
		return [999];
	}

	public function getPageUrlById(int|null $pageId): string|null
	{
		return $pageId === 999 ? 'https://example.test/future-content' : null;
	}

	public function getCommonDashboardPageId(): int|null
	{
		return null;
	}
}

function assertSameValue(mixed $expected, mixed $actual, string $message): void
{
	if ($expected !== $actual) {
		throw new RuntimeException(sprintf(
			"%s\nExpected: %s\nActual: %s",
			$message,
			var_export($expected, true),
			var_export($actual, true),
		));
	}
}

function membership(
	int $levelId,
	DateTimeImmutable $registered,
	DateTimeImmutable|null $until,
	bool $isUnlimited = false,
): Membership {
	return new Membership([
		'level_id' => $levelId,
		'user_id' => 7,
		'registered' => $registered->format(Format::DATE_TIME),
		'until' => $until?->format(Format::DATE_TIME),
		'is_unlimited' => $isUnlimited,
	]);
}

function createUnlockController(
	MembershipRepository $membershipRepository,
	CapturingMembershipService $membershipService,
): MembershipsController {
	$level = new MemberLevel([
		'id' => 20,
		'name' => 'Child level',
		'parent_id' => 10,
		'unlock_type' => 'disallow',
		'page_ids' => [],
	]);

	$reflection = new ReflectionClass(MembershipsController::class);
	/** @var MembershipsController $controller */
	$controller = $reflection->newInstanceWithoutConstructor();

	foreach ([
		'apiController' => new TestApiController(),
		'levelRepository' => new UnlockLevelRepository($level),
		'membershipRepository' => $membershipRepository,
		'redirectService' => new DenyingRedirectService(),
		'membershipService' => $membershipService,
		'pageRepository' => new UnlockPageRepository(),
	] as $propertyName => $value) {
		$property = new ReflectionProperty(MembershipsController::class, $propertyName);
		$property->setValue($controller, $value);
	}

	return $controller;
}

$now = DateTimeHelper::getNow();
$repository = new InMemoryMembershipRepository(
	[
		membership(1, $now->modify('-1 day'), $now->modify('+1 day')),
		membership(2, $now->modify('+1 day'), null, true),
		membership(3, $now->modify('-2 days'), $now->modify('-1 day')),
		membership(4, $now->modify('-1 day'), null, true),
		membership(5, $now->modify('-1 day'), null, true),
	],
	new ExistingLevelRepository([1, 2, 3, 5]),
);

assertSameValue(
	[1, 5],
	array_values(array_map(
		static fn (Membership $membership): int => $membership->getLevelId(),
		$repository->getAccessibleByUserId(7),
	)),
	'Only started, unexpired memberships for existing levels may grant access.',
);

$membershipRepository = $repository;
assertSameValue(true, DisplayHelper::shouldContentBeRendered(true, [1], 7), 'Active membership must render protected content.');
assertSameValue(false, DisplayHelper::shouldContentBeRendered(true, [2], 7), 'Future membership must not render protected content.');
assertSameValue(false, DisplayHelper::shouldContentBeRendered(true, [3], 7), 'Expired membership must not render protected content.');
assertSameValue(false, DisplayHelper::shouldContentBeRendered(true, [4], 7), 'Missing level must not render protected content.');

$futureLevel = new MemberLevel([
	'id' => 2,
	'name' => 'Future level',
	'parent_id' => 10,
	'unlock_type' => 'disallow',
	'page_ids' => [999],
	'after_login_page_id' => 999,
]);
$redirectReflection = new ReflectionClass(RedirectService::class);
/** @var RedirectService $loginRedirectService */
$loginRedirectService = $redirectReflection->newInstanceWithoutConstructor();
foreach ([
	'levelRepository' => new LoginLevelRepository($futureLevel),
	'membershipService' => new FutureAwareMembershipService([
		membership(2, $now->modify('+1 day'), null, true),
	]),
	'pageRepository' => new LoginPageRepository(),
	'userRepository' => (new ReflectionClass(UserRepository::class))->newInstanceWithoutConstructor(),
] as $propertyName => $value) {
	$property = new ReflectionProperty(RedirectService::class, $propertyName);
	$property->setValue($loginRedirectService, $value);
}

assertSameValue(
	'https://example.test',
	$loginRedirectService->loginRedirect(7),
	'Future membership must not influence the post-login redirect.',
);

$shortcodeReflection = new ReflectionClass(ShortcodeSubstitutor::class);
/** @var ShortcodeSubstitutor $shortcodeSubstitutor */
$shortcodeSubstitutor = $shortcodeReflection->newInstanceWithoutConstructor();
$shortcodeMemberships = new InMemoryMembershipRepository(
	[membership(2, $now->modify('+1 day'), null, true)],
	new ExistingLevelRepository([2]),
);
$property = new ReflectionProperty(ShortcodeSubstitutor::class, 'membershipRepository');
$property->setValue($shortcodeSubstitutor, $shortcodeMemberships);

assertSameValue(
	'bez přístupu',
	$shortcodeSubstitutor->shortcodeSectionExpirationDate(['section' => 2]),
	'Future membership must not be presented as accessible by the expiration shortcode.',
);

$limitedParentUntil = $now->modify('+2 days');
$limitedParent = membership(10, $now->modify('-1 day'), $limitedParentUntil);
$unlockRepository = new InMemoryMembershipRepository(
	[$limitedParent],
	new ExistingLevelRepository([10, 20]),
	[$limitedParent],
);
$capturingService = new CapturingMembershipService();
$controller = createUnlockController($unlockRepository, $capturingService);
$controller->unlockLevelForLoggedInUser(new WP_REST_Request(['level_id' => 20, 'page_id' => 123]));

assertSameValue(false, $capturingService->savedMembership?->isUnlimited(), 'Button-unlocked level must inherit a limited parent term.');
assertSameValue(
	$limitedParentUntil->format(Format::DATE_TIME),
	$capturingService->savedMembership?->getUntil()?->format(Format::DATE_TIME),
	'Button-unlocked level must inherit the parent expiration.',
);

$futureParent = membership(10, $now->modify('+1 day'), null, true);
$futureRepository = new InMemoryMembershipRepository(
	[$futureParent],
	new ExistingLevelRepository([10, 20]),
	[],
);
$capturingService = new CapturingMembershipService();
$controller = createUnlockController($futureRepository, $capturingService);

try {
	$controller->unlockLevelForLoggedInUser(new WP_REST_Request(['level_id' => 20, 'page_id' => 123]));
	throw new RuntimeException('A future parent membership must not allow button unlock.');
} catch (AccessDenied) {
	assertSameValue(null, $capturingService->savedMembership, 'Denied button unlock must not save a membership.');
}

echo "Access logic tests passed.\n";

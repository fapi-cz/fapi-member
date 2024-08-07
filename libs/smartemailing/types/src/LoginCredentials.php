<?php

declare (strict_types=1);
namespace FapiMember\Library\SmartEmailing\Types;

use FapiMember\Library\SmartEmailing\Types\Comparable\ArrayComparableTrait;
use FapiMember\Library\SmartEmailing\Types\Comparable\ComparableInterface;
use FapiMember\Library\SmartEmailing\Types\ExtractableTraits\ArrayExtractableTrait;
final class LoginCredentials implements ToArrayInterface, ComparableInterface
{
    use ArrayExtractableTrait;
    use ArrayComparableTrait;
    private string $login;
    private string $password;
    /**
     * @param array<string> $data
     */
    private function __construct(array $data)
    {
        $this->login = StringType::extract($data, 'login');
        $this->password = StringType::extract($data, 'password');
    }
    public function getLogin(): string
    {
        return $this->login;
    }
    public function getPassword(): string
    {
        return $this->password;
    }
    /**
     * @return array<mixed>
     */
    public function toArray(): array
    {
        return ['login' => $this->login, 'password' => $this->password];
    }
}

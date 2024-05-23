<?php

/*
 * This file is part of the Fidry\Console package.
 *
 * (c) Théo FIDRY <theo.fidry@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare (strict_types=1);
namespace FapiMember\Library\Fidry\Console\Internal\Type;

use FapiMember\Library\Fidry\Console\Input\InvalidInputValueType;
use FapiMember\Library\Fidry\Console\Internal\InputAssert;
use function sprintf;
use function trim;
/**
 * @implements ScalarType<non-empty-string>
 */
final class NonEmptyStringType implements ScalarType
{
    public function coerceValue(array|bool|string|null $value, string $label): string
    {
        InputAssert::string($value, $label);
        $trimmedValue = trim($value);
        if ('' === $trimmedValue) {
            throw new InvalidInputValueType(sprintf('Expected a non-empty string for %s.', $label));
        }
        return $trimmedValue;
    }
    public function getTypeClassNames(): array
    {
        return [self::class];
    }
    public function getPsalmTypeDeclaration(): string
    {
        return 'non-empty-string';
    }
    public function getPhpTypeDeclaration(): ?string
    {
        return 'string';
    }
}

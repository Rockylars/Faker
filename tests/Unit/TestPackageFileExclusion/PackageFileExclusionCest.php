<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Unit\TestPackageFileExclusion;

use Rocky\Faker\Tests\Support\UnitTester;
use Rocky\PackageFiles\PackageParser;

final class PackageFileExclusionCest
{
    public function packageWillOnlyIncludeSrcAndInfo(UnitTester $tester): void
    {
        $tester->assertSame(
            [
                'LICENSE',
                'README.md',
                'composer.json',
                'src'
            ],
            PackageParser::simplePackageSearch(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . '..')
        );
    }
}

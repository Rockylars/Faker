<?php

declare(strict_types=1);

namespace Rocky\Faker\Tests\Unit\TestPackageFileExclusion;

use Rocky\Faker\Tests\Support\UnitTester;

final class PackageFileExclusionCest
{
    public function _before(UnitTester $tester): void
    {
        // This is some nasty code I wrote, gawd dayum.
        // - Lars
    }

    public function packageWillOnlyIncludeSrcAndInfo(UnitTester $tester): void
    {
        $filesOrFoldersExcluded = ['.', '..', '.git'];

        /** @var string $gitAttributes */
        $gitAttributes = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'.gitattributes');
        $tester->assertIsString($gitAttributes);
        $lines = explode("\n", $gitAttributes);
        foreach ($lines as $line) {
            $matches = [];
            if (preg_match('/^\/(.*?)\/? *export-ignore$/', $line, $matches)) {
                $filesOrFoldersExcluded[] = $matches[1];
            }
        }

        /** @var string $gitIgnored */
        $gitIgnored = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'.gitignore');
        $tester->assertIsString($gitIgnored);
        $lines = explode("\n", $gitIgnored);
        foreach ($lines as $line) {
            $matches = [];
            if (preg_match('/^\/(.*?)\/?$/', $line, $matches)) {
                $filesOrFoldersExcluded[] = $matches[1];
            }
        }

        /** @var array<int, string> $project */
        $project = scandir(__DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR);
        $tester->assertIsArray($project);

        $result = [];
        foreach ($project as $projectContents) {
            if (!in_array($projectContents, $filesOrFoldersExcluded, true)) {
                $result[] = $projectContents;
            }
        }

        $tester->assertSame(
            [
                'LICENSE',
                'README.md',
                'composer.json',
                'src'
            ],
            $result
        );
    }
}

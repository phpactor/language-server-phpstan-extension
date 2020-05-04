<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Tests\Model;

use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter;

class LinterTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideLint
     */
    public function testLint(string $source, array $expectedDiagnostics)
    {
        $linter = new Linter();
        $diagnostics = $linter->lint('test.php');
        self::assertEquals($expectedDiagnostics, $diagnostics);
    }

    public function provideLint()
    {
        yield [
            '<?php $foobar = "string";',
            []
        ];
    }
}

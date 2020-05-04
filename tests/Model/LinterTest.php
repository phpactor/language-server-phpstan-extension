<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Tests\Model;

use LanguageServerProtocol\Diagnostic;
use LanguageServerProtocol\DiagnosticSeverity;
use LanguageServerProtocol\Position;
use LanguageServerProtocol\Range;
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
        $diagnostics = \Amp\Promise\wait($linter->lint('test.php'));
        self::assertEquals($expectedDiagnostics, $diagnostics);
    }

    public function provideLint()
    {
        yield [
            '<?php $foobar = "string";',
            []
        ];

        yield [
            '<?php $foobar = $barfoo;',
            [
                new Diagnostic('Foobar', new Range(new Position(1, 2)), null, DiagnosticSeverity::ERROR, 'phpstan'),
            ]
        ];
    }
}

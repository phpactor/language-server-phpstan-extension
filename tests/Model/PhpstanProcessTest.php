<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Tests\Model;

use LanguageServerProtocol\Diagnostic;
use LanguageServerProtocol\DiagnosticSeverity;
use LanguageServerProtocol\Position;
use LanguageServerProtocol\Range;
use PHPUnit\Framework\TestCase;
use Phpactor\Extension\LanguageServerPhpstan\Model\Linter;
use Phpactor\Extension\LanguageServerPhpstan\Model\PhpstanProcess;

class PhpstanProcessTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideLint
     */
    public function testLint(string $source, array $expectedDiagnostics): void
    {
        $this->workspace()->reset();
        $this->workspace()->put('test.php', $source);
        $linter = new PhpstanProcess();
        $diagnostics = \Amp\Promise\wait($linter->analyse($this->workspace()->path('test.php')));
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
                new Diagnostic('Undefined variable: $barfoo', new Range(
                    new Position(1, 1),
                    new Position(1, 1)
                ), null, DiagnosticSeverity::ERROR, 'phpstan'),
            ]
        ];
    }
}

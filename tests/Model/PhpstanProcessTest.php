<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Tests\Model;

use Generator;
use LanguageServerProtocol\Diagnostic;
use LanguageServerProtocol\DiagnosticSeverity;
use LanguageServerProtocol\Position;
use LanguageServerProtocol\Range;
use Phpactor\Extension\LanguageServerPhpstan\Model\PhpstanProcess;
use Psr\Log\NullLogger;

class PhpstanProcessTest extends IntegrationTestCase
{
    /**
     * @dataProvider provideLint
     */
    public function testLint(string $source, array $expectedDiagnostics): void
    {
        $this->workspace()->reset();
        $this->workspace()->put('test.php', $source);
        $linter = new PhpstanProcess($this->workspace()->path(), __DIR__ . '/../../vendor/bin/phpstan', new NullLogger());
        $diagnostics = \Amp\Promise\wait($linter->analyse($this->workspace()->path('test.php')));
        self::assertEquals($expectedDiagnostics, $diagnostics);
    }

    /**
     * @return Generator<mixed>
     */
    public function provideLint(): Generator
    {
        yield [
            '<?php $foobar = "string";',
            []
        ];

        yield [
            '<?php $foobar = $barfoo;',
            [
                new Diagnostic('Undefined variable: $barfoo', new Range(
                    new Position(0, 1),
                    new Position(0, 100)
                ), null, DiagnosticSeverity::ERROR, 'phpstan'),
            ]
        ];
    }
}

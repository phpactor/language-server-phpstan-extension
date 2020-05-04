<?php

namespace Phpactor\Extension\LanguageServerPhpstan\Tests\Model;

use PHPUnit\Framework\TestCase;
use Phpactor\TestUtils\Workspace;

abstract class IntegrationTestCase extends TestCase
{
    protected function workspace(): Workspace
    {
        return Workspace::create(__DIR__ . '/Workspace');
    }
}

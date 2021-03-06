<?php

namespace Alchemy\Tests\Phrasea\Core\CLIProvider;

/**
 * @covers Alchemy\Phrasea\Core\CLIProvider\ComposerSetupServiceProvider
 */
class ComposerSetupServiceProviderTest extends ServiceProviderTestCase
{
    public function provideServiceDescription()
    {
        return [
            [
                'Alchemy\Phrasea\Core\CLIProvider\ComposerSetupServiceProvider',
                'composer-setup',
                'Alchemy\Phrasea\Utilities\ComposerSetup'
            ],
        ];
    }
}

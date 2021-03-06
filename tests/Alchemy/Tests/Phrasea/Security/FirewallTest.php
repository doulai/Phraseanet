<?php

namespace Alchemy\Tests\Phrasea\Security;

use Alchemy\Phrasea\Security\Firewall;

class FirewallTest extends \PhraseanetAuthenticatedWebTestCase
{
    protected $client;

    public function testRequiredAuth()
    {
        $res = self::$DI['app']['firewall']->requireAuthentication(self::$DI['app']);
        $this->assertInstanceOf('\\Alchemy\\Phrasea\\Security\\Firewall', $res);
    }

    /**
     * @expectedException \Symfony\Component\HttpKernel\Exception\HttpException
     */
    public function testRequiredAuthNotAuthenticated()
    {
        $this->logout(self::$DI['app']);
        self::$DI['app']['firewall']->requireAuthentication(self::$DI['app']);
    }
}

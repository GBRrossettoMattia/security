<?php

/*
 * This file is part of the Fxp package.
 *
 * (c) François Pluchino <francois.pluchino@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Fxp\Component\Security\Tests\Token;

use Fxp\Component\Security\Token\ConsoleToken;
use PHPUnit\Framework\TestCase;

/**
 * @author François Pluchino <francois.pluchino@gmail.com>
 */
class ConsoleTokenTest extends TestCase
{
    public function testConsoleToken()
    {
        $token = new ConsoleToken('key', 'username', [
            'ROLE_TEST',
        ]);

        $this->assertSame('', $token->getCredentials());
        $this->assertSame('key', $token->getKey());

        $tokenSerialized = $token->serialize();
        $this->assertInternalType('string', $tokenSerialized);

        $token2 = new ConsoleToken('', '');
        $token2->unserialize($tokenSerialized);

        $this->assertEquals($token, $token2);
    }
}

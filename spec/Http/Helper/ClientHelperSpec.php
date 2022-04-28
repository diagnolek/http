<?php
/**
 * @author Sebastian Pondo
 */

namespace spec\Diagnolek\Http\Helper;

use Diagnolek\Http\Helper\ClientHelper;
use PhpSpec\ObjectBehavior;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class ClientHelperSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(ClientHelper::class);
    }

    function it_has_implement_interfaces()
    {
        $this->shouldImplement(RequestFactoryInterface::class);
        $this->shouldImplement(ResponseFactoryInterface::class);
        $this->shouldImplement(UriFactoryInterface::class);
    }
}

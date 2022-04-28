<?php
/**
 * @author Sebastian Pondo
 */

namespace spec\Diagnolek\Http;

use PhpSpec\ObjectBehavior;
use Diagnolek\Http\Adapter\Event;
use Diagnolek\Http\Adapter\Response;
use Diagnolek\Http\Client;
use Diagnolek\Http\Helper\ClientHelper;
use Diagnolek\Http\Adapter\EventDispatcher;

class ClientSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(Client::class);
    }

    function it_should_not_send_invalid_url()
    {
        $req = ClientHelper::getInstance()->createRequest('put', '');
        $this->shouldThrow(\RuntimeException::class)
            ->during('sendRequest',[$req]);
    }

    function it_should_resp_correct_format()
    {
        $resp = $this->get('https://en.wikipedia.org/w/index.php',['search'=>'Polska']);
        $resp->shouldBeAnInstanceOf(Response::class);
        $resp->getStatus()->shouldBeInt();
    }

    function it_should_change_data_before_send()
    {
        $this->getDispatcher()->shouldBeAnInstanceOf(EventDispatcher::class);

        $params = [
            'format'=>'xml',
            'action'=>'query',
            'redirects'=>1,
            'prop'=>'info|pageprops',
            'inprop'=>'url',
            'ppprop'=>'disambiguation',
        ];

        $this->getDispatcher()->attach(Event::BEFORE_SEND_DATA, function ($data) use ($params) {
            return $data + $params;
        });

        $resp = $this->get('https://en.wikipedia.org/w/api.php',['titles'=>'Batman']);
        $resp->getContent()->shouldContain('<?xml');
    }
}

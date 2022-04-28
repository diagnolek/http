<?php
/**
 * @author Sebastian Pondo
 */

namespace Diagnolek\Http\Exception;

use Psr\Http\Client\ClientExceptionInterface;

class ClientException extends \RuntimeException implements ClientExceptionInterface
{

}
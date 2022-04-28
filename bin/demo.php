<?php
/**
 * @author Sebastian Pondo
 */

define('PATH_CORE', dirname(__DIR__));

require PATH_CORE.'/vendor/autoload.php';

$wiki = [
    'url' => 'https://en.wikipedia.org',
    'params' => [
        'format'=>'json',
        'action'=>'query',
        'redirects'=>1,
        'prop'=>'info|pageprops',
        'inprop'=>'url',
        'ppprop'=>'disambiguation'
    ]
];

if (count($argv) >= 3 && $argv[1] == 'search') {
    $search = $argv[2];
} else {
    $search = 'Batman';
}

$client = (new \Diagnolek\Http\Client([
    \Diagnolek\Http\Adapter\Options::OPT_VERIFY=>false,
]))->baseUrl($wiki['url']);
$client->getDispatcher()->attach(\Diagnolek\Http\Adapter\Event::BEFORE_SEND_DATA, function ($data) use ($wiki) {
    return $data + $wiki['params'];
});

$resp = $client->get('/w/api.php',['titles'=>$search]);
if ($resp->getStatus() == 200) {
    $content = $resp->getContent();
} else {
    $content = "invalid response";
}

echo $content . PHP_EOL;


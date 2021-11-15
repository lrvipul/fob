<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

if (!file_exists(__DIR__ . '/app/bootstrap.php')) {
    echo "The sample file must be placed in the Magento root folder!";
    return;
}

require __DIR__ . '/app/bootstrap.php';

$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
$app = $bootstrap->createApplication('Magento\Framework\App\Http');


$login = "***";
$password = "***";

$consumerKey = "***";
$consumerSecret = "***";
$accessTokenSecret = "***";
$accessToken = "***";

$website = "http://m226.wyomind.com";

echo "<pre>";

/**
 * REST V1 API
 */

$httpHeaders = new \Zend\Http\Headers();
$httpHeaders->addHeaders(
        [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]
);
$client = new \Zend\Http\Client();
$options = [
    'adapter' => 'Zend\Http\Client\Adapter\Curl',
    'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
    'maxredirects' => 0,
    'timeout' => 30
];
$client->setOptions($options);

$request = new \Zend\Http\Request();
$request->setHeaders($httpHeaders);

$request->setMethod(\Zend\Http\Request::METHOD_GET);
$request->setUri($website . "/index.php/rest/V1/aiorder/");
$params = new \Zend\Stdlib\Parameters(['userId' => "1", 'orderId' => '6']);
$request->setQuery($params);
$response = $client->send($request);
$result = json_decode(json_decode($response->getContent()));

echo "  >> Raw result: ".$response->getContent()."\n";
if (is_object($result) && $result->error) {
    echo "  >> Error when running the '".$cronJob."' job.\n";
    echo "  >> Message: " . $result->message;
}
echo "\n\n";

//=====================================================

$httpHeaders = new \Zend\Http\Headers();
$httpHeaders->addHeaders(
        [
            'Authorization' => 'Bearer ' . $accessToken,
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ]
);
$client = new \Zend\Http\Client();
$options = [
    'adapter' => 'Zend\Http\Client\Adapter\Curl',
    'curloptions' => [CURLOPT_FOLLOWLOCATION => true],
    'maxredirects' => 0,
    'timeout' => 30
];
$client->setOptions($options);

$request = new \Zend\Http\Request();
$request->setHeaders($httpHeaders);

$request->setMethod(\Zend\Http\Request::METHOD_GET);
$request->setUri($website . "/index.php/rest/V1/aiorders/?searchCriteria[filter_groups][0][filters][0][field]=entity_id&searchCriteria[filter_groups][0][filters][0][value]=6");
$params = new \Zend\Stdlib\Parameters(['userId' => "1"]);
$request->setQuery($params);
$response = $client->send($request);
$result = json_decode(json_decode($response->getContent()));

echo "  >> Raw result: ".$response->getContent()."\n";
if (is_object($result) && $result->error) {
    echo "  >> Error when running the '".$cronJob."' job.\n";
    echo "  >> Message: " . $result->message;
}
echo "\n\n";
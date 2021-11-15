<html>
<head>
<title>Web API call samples for Pickup At Store</title>
</head>
<body>

<?php

if (!file_exists(__DIR__ . '/app/bootstrap.php')) {
    echo "The sample file must be placed in the Magento root folder!";
    return;
}

require __DIR__ . '/app/bootstrap.php';

$bootstrap = \Magento\Framework\App\Bootstrap::create(BP, $_SERVER);
$app = $bootstrap->createApplication('Magento\Framework\App\Http');

$orderId = 3;

$login = "***";
$password = "***";

$accessToken = "***";

$website = "***";


echo "<pre>";


echo " == Rest request\n";
echo "\n";
$request = new SoapClient($website . "/index.php/soap/?wsdl&services=integrationAdminTokenServiceV1", array("soap_version" => SOAP_1_2));
$response = $request->integrationAdminTokenServiceV1CreateAdminAccessToken(array("username" => $login, "password" => $password));
$accessToken = $response->result;
echo "  >> access token: ".$accessToken."\n";
echo "\n";



/**
 * REST V1 API
 */

echo " == Getting information for order #".$orderId."\n";
echo "\n";

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
$request->setUri($website . "/index.php/rest/V1/pickupatstore/getsalesorderdata/".$orderId);
echo "  >> Call to ".$website . "/index.php/rest/V1/pickupatstore/getsalesorderdata/".$orderId."\n";
$response = $client->send($request);
$result = json_decode(json_decode($response->getContent()));

    echo "  >> Raw result: ".$response->getContent()."\n";
    if (isset($result->error) && $result->error) {
        echo "  >> Error\n";
        echo "  >> Message: " . $result->message;
    } else {
        if (!$result->pickup_store) {
            echo "  >> No pickup store found for the order #".$orderId."\n";
        } else {
            echo "  >> Pickup store found for the order #".$orderId.": ".$result->pickup_store."\n";
            echo "  >> Other data: \n".print_r($result->order,true);
        }
    }
echo "\n\n";

?>

</body>
</html>
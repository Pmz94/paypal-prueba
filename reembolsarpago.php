<?php

use PayPal\Api\Amount;
use PayPal\Api\Refund;
use PayPal\Api\RefundRequest;
use PayPal\Api\Sale;

require 'app/credentials.php';

if(!isset($_GET['idVenta'])) {
    die();
}
$saleId = $_GET['idVenta'];

try {
    $sale = Sale::get($saleId, $apiContext);
} catch(Exception $ex) {
    echo '<h1>Algo malio sal</h1><hr>';
    die($ex);
}

$currency = $sale->amount->currency;
$total = $sale->amount->total;

echo $saleId;
echo '<br>';
echo $sale;

$amt = new Amount();
$amt->setCurrency($currency)
    ->setTotal($total);

$refundRequest = new RefundRequest();
$refundRequest->setAmount($amt);

$sale = new Sale();
$sale->setId($saleId);

/*try {
    $refundedSale = $sale->refundSale($refundRequest, $apiContext);
} catch(Exception $ex) {
    echo '<h1>Algo malio sal</h1><hr>';
    die($ex);
}*/
<?php

use PayPal\Api\Payer;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Details;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payment;

session_start();

require 'app/credentials.php';

if(!isset($_POST['product'], $_POST['price'])) {
    die();
}

$product = $_POST['product'];
$quantity = $_POST['quantity'];
$price = $_POST['price'];
$shipping = 1.00;

$subtotal = $price * $quantity;
$total = $subtotal + $shipping;

$payer = new Payer();
$payer->setPaymentMethod('paypal');

$item = new Item();
$item->setName($product)
     ->setCurrency('MXN')
     ->setQuantity($quantity)
     ->setPrice($price);

$itemlist = new ItemList();
$itemlist->setItems([$item]);

$details = new Details();
$details->setShipping($shipping)
        ->setSubtotal($subtotal);

$amount = new Amount();
$amount->setCurrency('MXN')
       ->setTotal($total)
       ->setDetails($details);

$uniqid = uniqid();
$_SESSION['invoiceNumber'] = $uniqid;

$transaction = new Transaction();
$transaction->setAmount($amount)
            ->setItemList($itemlist)
            ->setDescription('Pagando algo')
            ->setInvoiceNumber($uniqid);

$redirectUrls = new RedirectUrls();
$redirectUrls->setReturnUrl(APP_PATH . '/pay.php?success=true')
             ->setCancelUrl(APP_PATH . '/pay.php?success=false');

$payment = new Payment();
$payment->setIntent('sale')
        ->setPayer($payer)
        ->setRedirectUrls($redirectUrls)
        ->setTransactions([$transaction]);

$request = clone $payment;

try {
    $payment->create($paypal);
} catch(Exception $e) {
    echo '<h1>Algo malio sal</h1><hr>';
    die($e);
}

$approvalUrl = $payment->getApprovalLink();
//$token = $payment->getToken();

//echo '<a href="'. $approvalUrl . '">' . $approvalUrl . '</a><br>';
//echo $token . '<br>';
//echo $uniqid; //invoice number
header('Location: ' . $approvalUrl);
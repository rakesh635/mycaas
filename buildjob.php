<?php
include "lib/class.app.php";
$app = new appClass();
$cnn = new AMQPConnection();
// set the hostname
$cnn->setHost('192.168.54.193');
$cnn->setLogin('test');
$cnn->setPassword('test');
$cnn->connect();

/* Check that connection is working */
if ($cnn->isConnected()) {
    echo "Connected to the broker \o/";
}
else {
    echo "Cannot connect to the broker";
}
$cnnChannel = new AMQPChannel($cnn);
$cnnEnvelope = new AMQPEnvelope();
$cnnExchange = new AMQPExchange($cnnChannel);
$cnnExchange->setName('buildjob');
$cnnExchange->setType(AMQP_EX_TYPE_DIRECT);
$cnnExchange->setFlags(AMQP_DURABLE);
$cnnExchange->declare();

$cnnQueue = new AMQPQueue($cnnChannel);
$cnnQueue->setName('buildjob_queue');
$cnnQueue->setFlags(AMQP_DURABLE);
$cnnQueue->declare();
$cnnQueue->consume(function ($cnnEnvelope, $cnnQueue) {
        processmsgnew($cnnEnvelope->getBody());
},AMQP_AUTOACK);
$cnnQueue->bind('buildjob', 'dco.marker');

function processmsgnew($envBody)
{
        $app->buildjob($cnnEnvelope->getBody());
}
?>
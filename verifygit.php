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
$cnnExchange->setName('gitverify');
$cnnExchange->setType(AMQP_EX_TYPE_DIRECT);
$cnnExchange->setFlags(AMQP_DURABLE);
$cnnExchange->declare();

$cnnQueue = new AMQPQueue($cnnChannel);
$cnnQueue->setName('gitverify_queue');
$cnnQueue->setFlags(AMQP_DURABLE);
$cnnQueue->declare();
$cnnQueue->consume(function ($cnnEnvelope, $cnnQueue) {
        $verifygit_msg = processmsgnew($cnnEnvelope->getBody());
        $app = new appClass();
        if($verifygit_msg == "true")
        {
                $app->buildstatusupdate($cnnEnvelope->getBody(),'git_verify');

                $cnn1 = new AMQPConnection();
                $cnn1->setHost('192.168.54.193');
                $cnn1->setLogin('test');
                $cnn1->setPassword('test');
                $cnn1->connect();
                /* Check that connection is working */
                if (!$cnn1->isConnected()) {
                        echo "Connected to the broker in git verify \o/";
                }
                $cnnChannel1 = new AMQPChannel($cnn1);
                $cnnExchange1 = new AMQPExchange($cnnChannel1);
                $cnnExchange1->setName('cicredential');
                $cnnExchange1->setType(AMQP_EX_TYPE_DIRECT);
                $cnnExchange1->setFlags(AMQP_DURABLE);
                $cnnExchange1->declare();
                $cnnQueue1 = new AMQPQueue($cnnChannel1);
                $cnnQueue1->setName('cicredential_queue');
                $cnnQueue1->setFlags(AMQP_DURABLE);
                $cnnQueue1->declare();
                $cnnQueue1->bind('cicredential', 'dco.marker');
                $cnnExchange1->publish("{'appid':'".$cnnEnvelope->getBody()."'}", 'dco.marker');
        }
        else
        {
                $app->builderror($cnnEnvelope->getBody(),$verifygit_msg);
        }
},AMQP_AUTOACK);
$cnnQueue->bind('gitverify', 'dco.marker');

function processmsgnew($envBody)
{
        $app = new appClass();
        return($app->verifygit($envBody));
}
?>
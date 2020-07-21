<?php

require 'vendor/autoload.php';

use Google\Cloud\PubSub\PubSubClient;

$googleProjectId = getenv('GCP_PROJECT_ID');
$googlePubSubSubscription = getenv('PUB_SUB_SUBSCRIPTION') ?: 'my-subscription';
$nbEntriesPerMessage = getenv('NB_ENTRIES_PER_MESSAGE') ?: 900;

$pubSub = new PubSubClient(['projectId' => $googleProjectId]);
$subscription = $pubSub->subscription($googlePubSubSubscription);

declare(ticks=1);
$continue = true;

function sig_handle()
{
    global $continue;
    $continue = false;
}

pcntl_signal(SIGINT, 'sig_handle');
pcntl_signal(SIGTERM, 'sig_handle');

while ($continue) {
    $messages = $subscription->pull(['maxMessages' => 1]);

    foreach ($messages as $message) {
        echo "Message id: " . $message->id() . PHP_EOL;
        for ($i = 1; $i <= $nbEntriesPerMessage; $i++) {
            echo "Dealing next entry $i of message " . $message->data() . " " . PHP_EOL;
            sleep(1);
            $subscription->modifyAckDeadline($message, 10);
        }
        $subscription->acknowledge($message);
        echo "Done dealing with message id: " . $message->id() . PHP_EOL;
    }
}

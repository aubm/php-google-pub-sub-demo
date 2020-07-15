<?php

require 'vendor/autoload.php';

use Google\Cloud\PubSub\PubSubClient;

$googleProjectId = getenv('GCP_PROJECT_ID');
$googlePubSubSubscription = getenv('PUB_SUB_SUBSCRIPTION') ?: 'my-subscription';

$pubSub = new PubSubClient(['projectId' => 'sandbox-aba']);
$subscription = $pubSub->subscription('my-subscription');

while (true) {
    $messages = $subscription->pull(['maxMessages' => 1]);

    foreach ($messages as $message) {
        echo "Message id: " . $message->id() . "\n";
        echo $message->data() . "\n";
        echo "Sleeping for 10 seconds...\n";
        sleep(10);
        $subscription->acknowledge($message);
    }
}

<?php

require 'vendor/autoload.php';

use Google\Cloud\PubSub\PubSubClient;

$googleProjectId = getenv('GCP_PROJECT_ID');
$googlePubSubSubscription = getenv('PUB_SUB_SUBSCRIPTION') ?: 'my-subscription';
$nbEntriesPerMessage = getenv('NB_ENTRIES_PER_MESSAGE') ?: 900;

$pubSub = new PubSubClient(['projectId' => $googleProjectId]);
$subscription = $pubSub->subscription($googlePubSubSubscription);

// Ticks are needed.
// https://www.php.net/manual/fr/internals2.opcodes.ticks.php
declare(ticks=1);

// Intercepts SIGINT & SIGTERM signals and sets $continue to false
// in order to exit the infinite control loop.
$continue = true;
function sig_handle()
{
    global $continue;
    $continue = false;
}
pcntl_signal(SIGINT, 'sig_handle');
pcntl_signal(SIGTERM, 'sig_handle');

while ($continue) {
    // Only pull one message at a time to avoid duplicates.
    // https://cloud.google.com/pubsub/docs/pull#dupes
    $messages = $subscription->pull(['maxMessages' => 1]);

    foreach ($messages as $message) {
        echo "Message id: " . $message->id() . PHP_EOL;
        for ($i = 1; $i <= $nbEntriesPerMessage; $i++) {
            echo "Dealing next entry $i of message " . $message->data() . " " . PHP_EOL;

            // Emulate some work.
            sleep(1);

            // Regularly reset the acknowledgement deadline to avoid duplicates.
            // https://cloud.google.com/pubsub/docs/pull#dupes
            $subscription->modifyAckDeadline($message, 10);
        }

        // Acknowledge the message and remove it from the Pub/Sub subscription.
        $subscription->acknowledge($message);
        echo "Done dealing with message id: " . $message->id() . PHP_EOL;
    }
}

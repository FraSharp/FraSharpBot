<?php

namespace skrtdev\Telegram;

use stdClass;
use skrtdev\Prototypes\simpleProto;

/**
 * Contains information about the current status of a webhook.
*/
class WebhookInfo extends \Telegram\WebhookInfo{

    use simpleProto;

    /** @var string Webhook URL, may be empty if webhook is not set up */
    public string $url;

    /** @var bool True, if a custom certificate was provided for webhook certificate checks */
    public bool $has_custom_certificate;

    /** @var int Number of updates awaiting delivery */
    public int $pending_update_count;

    /** @var string|null Currently used webhook IP address */
    public ?string $ip_address = null;

    /** @var int|null Unix time for the most recent error that happened when trying to deliver an update via webhook */
    public ?int $last_error_date = null;

    /** @var string|null Error message in human-readable format for the most recent error that happened when trying to deliver an update via webhook */
    public ?string $last_error_message = null;

    /** @var int|null Maximum allowed number of simultaneous HTTPS connections to the webhook for update delivery */
    public ?int $max_connections = null;

    /** @var ObjectsList|null A list of update types the bot is subscribed to. Defaults to all update types except chat_member */
    public ?ObjectsList $allowed_updates = null;

    
}

?>

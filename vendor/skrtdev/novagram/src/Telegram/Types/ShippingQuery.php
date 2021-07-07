<?php

namespace skrtdev\Telegram;

use stdClass;
use skrtdev\Prototypes\simpleProto;

/**
 * This object contains information about an incoming shipping query.
*/
class ShippingQuery extends \Telegram\ShippingQuery{

    use simpleProto;

    /** @var string Unique query identifier */
    public string $id;

    /** @var User User who sent the query */
    public User $from;

    /** @var string Bot specified invoice payload */
    public string $invoice_payload;

    /** @var ShippingAddress User specified shipping address */
    public ShippingAddress $shipping_address;

    
}

?>

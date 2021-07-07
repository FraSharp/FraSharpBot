<?php

namespace skrtdev\Telegram;

use stdClass;
use skrtdev\Prototypes\simpleProto;

/**
 * This object represents one button of the reply keyboard. For simple text buttons String can be used instead of this object to specify text of the button. Optional fields request_contact, request_location, and request_poll are mutually exclusive.
*/
class KeyboardButton extends \Telegram\KeyboardButton{

    use simpleProto;

    /** @var string Text of the button. If none of the optional fields are used, it will be sent as a message when the button is pressed */
    public string $text;

    /** @var bool|null If True, the user's phone number will be sent as a contact when the button is pressed. Available in private chats only */
    public ?bool $request_contact = null;

    /** @var bool|null If True, the user's current location will be sent when the button is pressed. Available in private chats only */
    public ?bool $request_location = null;

    /** @var KeyboardButtonPollType|null If specified, the user will be asked to create a poll and send it to the bot when the button is pressed. Available in private chats only */
    public ?KeyboardButtonPollType $request_poll = null;

    
}

?>

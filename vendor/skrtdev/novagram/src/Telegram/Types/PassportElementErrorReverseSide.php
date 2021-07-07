<?php

namespace skrtdev\Telegram;

use stdClass;
use skrtdev\Prototypes\simpleProto;

/**
 * Represents an issue with the reverse side of a document. The error is considered resolved when the file with reverse side of the document changes.
*/
class PassportElementErrorReverseSide extends \Telegram\PassportElementErrorReverseSide{

    use simpleProto;

    /** @var string Error source, must be reverse_side */
    public string $source;

    /** @var string The section of the user's Telegram Passport which has the issue, one of “driver_license”, “identity_card” */
    public string $type;

    /** @var string Base64-encoded hash of the file with the reverse side of the document */
    public string $file_hash;

    /** @var string Error message */
    public string $message;

    
}

?>

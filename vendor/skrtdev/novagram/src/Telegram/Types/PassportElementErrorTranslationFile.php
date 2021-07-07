<?php

namespace skrtdev\Telegram;

use stdClass;
use skrtdev\Prototypes\simpleProto;

/**
 * Represents an issue with one of the files that constitute the translation of a document. The error is considered resolved when the file changes.
*/
class PassportElementErrorTranslationFile extends \Telegram\PassportElementErrorTranslationFile{

    use simpleProto;

    /** @var string Error source, must be translation_file */
    public string $source;

    /** @var string Type of element of the user's Telegram Passport which has the issue, one of “passport”, “driver_license”, “identity_card”, “internal_passport”, “utility_bill”, “bank_statement”, “rental_agreement”, “passport_registration”, “temporary_registration” */
    public string $type;

    /** @var string Base64-encoded file hash */
    public string $file_hash;

    /** @var string Error message */
    public string $message;

    
}

?>

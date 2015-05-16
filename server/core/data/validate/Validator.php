<?php
namespace Tudu\Core\Data\Validate;

use \Tudu\Core\Chainable\Sentinel;

/**
 * Chainable data validation base class.
 * 
 * Validators take any input. If the input is valid, the validator simply
 * outputs the input. Otherwise, the validator outputs an error string wrapped
 * in a Sentinel object (see \Tudu\Core\Chainable\Sentinel).
 * 
 * When extending Validator, override process() to carry out the actual
 * validation. If validation fails, return an error string wrapped in a
 * sentinel.
 * 
 * The error string should follow these examples:
 * 
 *    "must be longer than 10 characters"
 *    "should be shorter than two dwarves"
 *    "cannot be a unicorn"
 *    "is too frobnicated"
 *    ...
 * 
 * Notice the lack of capitalization and ending punctuation. This is
 * intended and should be emulated. Also, the first word should always be a
 * verb. Remember: The error string may be presented to the end user, so
 * make it as concise as possible while still being readable.
 */
class Validator extends \Tudu\Core\Chainable\OptionsChainable {}
?>

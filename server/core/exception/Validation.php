<?php
namespace Tudu\Core\Exception;

/**
 * Validation exception class.
 * 
 * Validation exceptions indicate data validation errors. Such errors should
 * always carry context data indicating which fields failed to validate and why.
 */
class Validation extends Client {
    
    const ERROR_STRING = 'Validation Error';
}

?>

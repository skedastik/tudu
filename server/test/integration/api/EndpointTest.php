<?php
namespace Tudu\Test\Integration\Api;

use \Tudu\Test\Integration\Database\DatabaseTest;

abstract class EndpointTest extends DatabaseTest {
    
    /**
     * Decode JSON data from output buffer, returning an array.
     */
    protected function decodeOutputBuffer() {
        return json_decode(ob_get_contents(), true);
    }
}

?>

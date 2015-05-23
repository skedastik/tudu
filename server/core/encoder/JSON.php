<?php
namespace Tudu\Core\Encoder;

use \Tudu\Core\MediaType;

/**
 * JSON encoder.
 */
final class JSON implements Encoder {
    
    const MEDIA_TYPE = 'application/json; charset=utf-8';
    
    public function getMediaType() {
        return new MediaType(self::MEDIA_TYPE);
    }
    
    /**
     * Encode an array.
     * 
     * @return string|null JSON-encoded array data on success, NULL on failure.
     */
    public function encode(array $data) {
        $encoded = json_encode($data);
        return $encoded === false ? null : $encoded;
    }
    
    /**
     * Decode an encoded array.
     * 
     * @param string $data JSON-encoded array data.
     * @return array|null Array on success, NULL on failure.
     */
    public function decode($data) {
        return json_decode($data, true);
    }
}

?>

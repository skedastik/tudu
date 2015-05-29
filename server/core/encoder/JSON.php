<?php
namespace Tudu\Core\Encoder;

use \Tudu\Core\MediaType;

/**
 * JSON encoder.
 */
final class JSON implements Encoder {
    
    const MEDIA_TYPE = 'application/json; charset=utf-8';
    
    public function supportsMediaType($mediaType) {
        return (new MediaType(self::MEDIA_TYPE))->compare(new MediaType($mediaType));
    }
    
    public function getMediaType() {
        return self::MEDIA_TYPE;
    }
    
    public function encode(array $data) {
        $encoded = json_encode($data);
        return $encoded === false ? null : $encoded;
    }
    
    public function decode($data) {
        return json_decode($data, true);
    }
}

?>

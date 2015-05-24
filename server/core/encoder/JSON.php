<?php
namespace Tudu\Core\Encoder;

use \Tudu\Core\MediaType;

/**
 * JSON encoder.
 */
final class JSON implements Encoder {
    
    const MEDIA_TYPE = 'application/json; charset=utf-8';
    
    public function supportsMediaType($mediaType) {
        if ((new MediaType($mediaType))->compare(new MediaType(self::MEDIA_TYPE))) {
            return self::MEDIA_TYPE;
        }
        return false;
    }
    
    public function getSupportedMediaTypes() {
        return [self::MEDIA_TYPE];
    }
    
    public function encode(array $data, $mediaType = null) {
        $encoded = json_encode($data);
        return $encoded === false ? null : $encoded;
    }
    
    public function decode($data, $mediaType = null) {
        return json_decode($data, true);
    }
}

?>

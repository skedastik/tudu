<?php
namespace Tudu\Core\Encoder;

use \Tudu\Core\MediaType;

/**
 * JSON encoder.
 */
final class JSON implements Encoder {
    
    const MEDIA_TYPE = 'application/json; charset=utf-8';
    
    public function supportsMediaType(MediaType $mediaType) {
        return $mediaType->compare(new MediaType(self::MEDIA_TYPE));
    }
    
    public function getSupportedMediaTypes() {
        return [self::MEDIA_TYPE];
    }
    
    public function encode(array $data, MediaType $mediaType = null) {
        $encoded = json_encode($data);
        return $encoded === false ? null : $encoded;
    }
    
    public function decode($data, MediaType $mediaType = null) {
        return json_decode($data, true);
    }
}

?>

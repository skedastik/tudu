<?php
namespace Tudu\Core\Encoder;

/**
 * An encoder encodes and decodes PHP arrays from data of some media type.
 */
interface Encoder {
    
    /**
     * Get the media type of the encoder.
     * 
     * @return \Tudu\Core\MediaType
     */
    public function getMediaType();
    
    /**
     * Encode an array.
     * 
     * @param array $data Array.
     * @return mixed Encoded array data.
     */
    public function encode(array $data);
    
    /**
     * Decode an encoded array.
     * 
     * @param mixed $data Encoded array data.
     * @return array|null Array on success, NULL on failure.
     */
    public function decode($data);
}

?>

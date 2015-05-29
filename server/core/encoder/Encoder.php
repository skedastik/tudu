<?php
namespace Tudu\Core\Encoder;

/**
 * An encoder encodes or decodes PHP arrays into or from data of a given media
 * type.
 */
interface Encoder {
    
    /**
     * Check if encoder supports a given media type (possibily a media type wild
     * card or range like 'application/*')/
     * 
     * @param string $mediaType
     * @return bool TRUE if the input media type is supported, FALSE otherwise.
     */
    public function supportsMediaType($mediaType);
    
    /**
     * Get supported media type.
     * 
     * @return string Media type string.
     */
    public function getMediaType();
    
    /**
     * Encode an array.
     * 
     * @param array $data Array.
     * @return mixed Encoded array data on success, NULL on failure.
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

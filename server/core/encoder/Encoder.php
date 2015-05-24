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
     * @return string|false If the input media type is supported, a matching
     * output media type is returned (this is to facilitate wildcard media type
     * queries). Otherwise FALSE is returned.
     */
    public function supportsMediaType($mediaType);
    
    /**
     * Get supported media types.
     * 
     * @return array An array of media type strings.
     */
    public function getSupportedMediaTypes();
    
    /**
     * Encode an array.
     * 
     * Implementers may accept a second parameter specifying a media type.
     * 
     * @param array $data Array.
     * @param string $mediaType (optional)
     * @return mixed Encoded array data.
     */
    public function encode(array $data, $mediaType = null);
    
    /**
     * Decode an encoded array.
     * 
     * Implementers may accept a second parameter specifying a media type.
     * 
     * @param mixed $data Encoded array data.
     * @param string $mediaType (optional)
     * @return array|null Array on success, NULL on failure.
     */
    public function decode($data, $mediaType = null);
}

?>

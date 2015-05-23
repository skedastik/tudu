<?php
namespace Tudu\Core\Encoder;

use \Tudu\Core\MediaType;

/**
 * An encoder encodes or decodes PHP arrays into or from data of a given media
 * type.
 */
interface Encoder {
    
    /**
     * Check if encoder supports a given media type.
     * 
     * @param \Tudu\Core\MediaType $mediaType
     * @return bool TRUE is media type is supported, FALSE otherwise. Media type
     * is supported if media type and subtype match, regardless of media type
     * parameter.
     */
    public function supportsMediaType(MediaType $mediaType);
    
    /**
     * Get supported media types.
     * 
     * @return array An array of media type strings.
     */
    public function getSupportedMediaTypes();
    
    /**
     * Set the default media type used by this encoder.
     * 
     * This will be a no-op for encoder subclasses that support only one media
     * type. For subclasses that support multiple media types, use the default
     * media type in calls to encode() and decode() that do not supply a media
     * type argument.
     * 
     * @param \Tudu\Core\MediaType $mediaType
     */
    public function setDefaultMediaType(MediaType $mediaType);
    
    /**
     * Get the default media type used by this encoder.
     */
    public function getDefaultMediaType();
    
    /**
     * Encode an array.
     * 
     * Implementers may accept a second parameter specifying a media type.
     * 
     * @param array $data Array.
     * @param \Tudu\Core\MediaType $mediaType (optional)
     * @return mixed Encoded array data.
     */
    public function encode(array $data, MediaType $mediaType = null);
    
    /**
     * Decode an encoded array.
     * 
     * Implementers may accept a second parameter specifying a media type.
     * 
     * @param mixed $data Encoded array data.
     * @param \Tudu\Core\MediaType $mediaType (optional)
     * @return array|null Array on success, NULL on failure.
     */
    public function decode($data, MediaType $mediaType = null);
}

?>

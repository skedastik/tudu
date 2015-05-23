<?php
namespace Tudu\Core;

final class MediaType {
    
    protected $type;
    protected $subtype;
    protected $parameterAttribute;
    protected $parameterValue;
    
    /**
     * Constructor.
     * 
     * @param string $contentType Internet media type string
     */
    public function __construct($mediaType) {
        $this->type = null;
        $this->subtype = null;
        $this->parameterAttribute = null;
        $this->parameterValue = null;
        
        $mediaType = strtolower($mediaType);
        
        // this regex is intentionally lax to minimize false negatives
        $pattern = '/^\s*([^\/\s]+)\s*\/\s*([^\s;]+)(\s*;\s*([^\s=]+)\s*=\s*\"?\s*([^\s]+)\s*\"?\s*)?.*$/';
        if (preg_match($pattern, $mediaType, $matches) === 1) {
            $this->type = $matches[1];
            $this->subtype = $matches[2];
            if (isset($matches[4])) {
                $this->parameterAttribute = $matches[4];
                $this->parameterValue = $matches[5];
            }
        }
    }
    
    /**
     * Return type.
     * 
     * In "application/json; charset=utf8", the type is "application".
     */
    public function getType() {
        return $this->type;
    }
    
    /**
     * Return subtype.
     * 
     * In "application/json; charset=utf8", the subtype is "xml".
     */
    public function getSubtype() {
        return $this->subtype;
    }
    
    /**
     * Return parameter attribute.
     * 
     * In "application/json; charset=utf8", the parameter attribute is "charset".
     */
    public function getParameterAttribute() {
        return $this->parameterAttribute;
    }
    
    /**
     * Return parameter value.
     * 
     * In "application/json; charset=utf8", the parameter value is "utf8".
     */
    public function getParameterValue() {
        return $this->parameterValue;
    }
    
    /**
     * Strictly compare this media type against another.
     * 
     * @param \Tudu\Core\MediaType $mediaType
     * @return bool TRUE if input media type matches, FALSE otherwise. In order
     * to match, input media type must have same type and subtype, and must have
     * equivalent specificity. In other words, both media types must also
     * specify equivalent parameters, or both media types must omit parameters.
     */
    public function compareStrict(MediaType $mediaType) {
        return $mediaType->getType() === $this->getType()
            && $mediaType->getSubtype() === $this->getSubtype()
            && $mediaType->getParameterAttribute() === $this->getParameterAttribute()
            && $mediaType->getParameterValue() === $this->getParameterValue();
    }
    
    /**
     * Loosely compare this media type against another.
     * 
     * @param \Tudu\Core\MediaType $mediaType
     * @return bool TRUE if input media type matches, FALSE otherwise. In order
     * to match, input media type need only have same type and subtype.
     */
    public function compare(MediaType $mediaType) {
        return $mediaType->getType() === $this->getType()
            && $mediaType->getSubtype() === $this->getSubtype();
    }
    
    /**
     * Return the media type as a string.
     */
    public function asString() {
        return $this->type.'/'.$this->subtype.'; '.$this->parameterAttribute.'='.$this->parameterValue;
    }
}
?>

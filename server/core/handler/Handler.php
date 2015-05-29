<?php
namespace Tudu\Core\Handler;

use \Tudu\Core\Error;
use \Tudu\Core\Data\DbConnection;
use \Tudu\Core\Delegate;

/**
 * Request handler base class.
 */
abstract class Handler {
    
    protected $app;
    protected $db;
    
    /**
     * Constructor.
     * 
     * @param \Tudu\Core\Delegate\App $app Instance of an app delegate.
     * @param \Tudu\Core\Data\DbConnection $db Database connection instance.
     */
    public function __construct(Delegate\App $app, DbConnection $db) {
        $this->app = $app;
        $this->db = $db;
        $this->app->setResponseHeaders([
            'Content-Type' => null
        ]);
    }
    
    /**
     * Ensure that our application is capable of encoding its response payload
     * in a format specified by the request's "Accept" header.
     * 
     * This function may change the response "Content-Type" header.
     * 
     * If none of the media types specified by the "Accept" header is supported,
     * processing halts immediately and an error response is sent.
     * 
     * If no "Accept" header is provided, we are free to use any media type.
     */
    final protected function checkResponseAcceptable() {
        $accept = $this->app->getRequestHeader('Accept');
        if (is_null($accept)) {
            $this->useDefaultContentType();
            return;
        }
        
        // set response content type to first supported, accepted media type
        $encoder = $this->app->getEncoder();
        $acceptedMediaTypes = explode(',', $accept);
        foreach ($acceptedMediaTypes as $mediaType) {
            $supportedMediaType = $encoder->supportsMediaType($mediaType);
            if ($supportedMediaType) {
                $this->app->setResponseHeaders([
                    'Content-Type' => $supportedMediaType
                ]);
                return;
            }
        }
        
        // no supported media types are accepted, so send an error response
        $description = 'Accepted media types are not supported. See context for a list of supported media types.';
        $this->sendError(Error::Generic($description, $encoder->getSupportedMediaTypes(), 406));
    }
    
    /**
     * Check that the request's content type can be decoded.
     * 
     * If request's content type is not supported, halt processing immediately
     * and send an error response. It only makes sense to call this when
     * handling requests with payloads (i.e., POST, PUT, and PATCH).
     */
    final protected function checkRequestDecodable() {
        $requestContentType = $this->app->getRequestHeader('Content-Type');
        $encoder = $this->app->getEncoder();
        if (!$encoder->supportsMediaType($requestContentType)) {
            $description = 'Request content type not supported. See context for a list of supported media types.';
            $this->sendError(Error::Generic($description, $encoder->getSupportedMediaTypes(), 415));
        }
    }
    
    /**
     * Set "Content-Type" header to the first supported media type as reported
     * by the app encoder.
     */
    final private function useDefaultContentType() {
        $this->app->setResponseHeaders([
            'Content-Type' => $this->app->getEncoder()->getSupportedMediaTypes()[0]
        ]);
    }
    
    /**
     * Decode the request body.
     * 
     * @return array $data Request body data as a key/value array.
     */
    final protected function decodeRequestBody() {
        $mediaType = $this->app->getRequestHeader('Content-Type');
        $requestBody = $this->app->getRequestBody();
        $data = $this->app->getEncoder()->decode($requestBody, $mediaType);
        if (is_null($data)) {
            $description = 'Request body is malformed.';
            $this->sendError(Error::Generic($description, null, 400));
        }
        return $data;
    }
    
    /**
     * Render response body.
     * 
     * Input data must either be an Arrayable object or an array. Data is
     * encoded as a string before being rendered.
     * 
     * This method will automatically attempt to set a "Content-Type" header if
     * one doesn't already exist.
     * 
     * @param mixed $data Either an Arrayable object or an array.
     */
    final protected function renderBody($data) {
        if (is_null($this->app->getResponseHeader('Content-Type'))) {
            $this->useDefaultContentType();
        }
        if ($data instanceof Arrayable) {
            $data = $data->asArray();
        } else {
            $mediaType = $this->app->getResponseHeader('Content-Type');
            $data = $this->app->getEncoder()->encode($data, $mediaType);
        }
        echo $data;
    }
    
    /**
     * Halt processing immediately and send an error response.
     * 
     * @param \Tudu\Core\Error $error Error object.
     */
    final protected function sendError(\Tudu\Core\Error $error) {
        // var_dump($this->app->getResponseHeader('Content-Type'));
        $statusCode = $error->getHttpStatusCode();
        $this->app->setResponseStatus(is_null($statusCode) ? 400 : $statusCode);
        $this->renderBody($error->asArray());
        $this->app->send();
    }
}
    
?>

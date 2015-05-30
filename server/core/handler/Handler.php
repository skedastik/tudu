<?php
namespace Tudu\Core\Handler;

use \Tudu\Core\Exception;
use \Tudu\Core\Database\DbConnection;
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
     * @param \Tudu\Core\Database\DbConnection $db Database connection instance.
     */
    public function __construct(Delegate\App $app, DbConnection $db) {
        $this->app = $app;
        $this->db = $db;
        $this->app->setResponseHeaders([
            'Content-Type' => null
        ]);
    }
    
    /**
     * Handle the request.
     */
    final public function run() {
        try {
            $this->process();
        } catch (Exception\Client $exception) {
            $this->handleClientException($exception);
        }
    }
    
    /**
     * Handle a client exception.
     * 
     * Override for custom error handling.
     * 
     * @param \Tudu\Core\Exception\Client $exception Client exception.
     */
    protected function handleClientException(Exception\Client $exception) {
        $statusCode = $exception->getHttpStatusCode();
        $this->app->setResponseStatus(is_null($statusCode) ? 400 : $statusCode);
        $this->renderBody($exception->asArray());
    }
    
    /**
     * Perform actual request processing.
     */
    abstract protected function process();
    
    /**
     * Ensure that the application is capable of encoding its response payload
     * in a format specified by the request's "Accept" header.
     * 
     * If no "Accept" header is found, the application is free to use any media
     * type.
     * 
     * If an "Accept" header is found, this method will attempt to set the
     * response "Content-Type" header to one of the acceptable media types.
     * 
     * @param bool $send406 (optional) Pass TRUE to halt processing immediately
     * and send an error response if no acceptable media types are supported.
     * This is the default behavior. Pass FALSE to default to a supported media
     * type instead.
     */
    final protected function negotiateContentType($send406 = true) {
        $acceptableTypes = $this->app->getRequestHeader('Accept');
        if ($acceptableTypes) {
            $encoder = $this->app->getEncoder($acceptableTypes);
            if ($encoder) {
                $this->app->setResponseHeaders([
                    'Content-Type' => $encoder->getMediaType()
                ]);
                return;
            }
            if ($send406) {
                $description = 'Accepted media types are not supported. See context for a list of supported media types.';
                throw new Exception\Client($description, $this->app->getSupportedContentTypes(), 406);
            }
        }
        
        // set default content type
        $this->app->setResponseHeaders([
            'Content-Type' => $this->app->getEncoder()->getMediaType()
        ]);
    }
    
    /**
     * Decode the request body.
     * 
     * If request body's content type is not supported, processing halts
     * immediately and an error response is sent.
     * 
     * @return array $data Request body data as a key/value array.
     */
    final protected function decodeRequestBody() {
        $mediaType = $this->app->getRequestHeader('Content-Type');
        if (is_null($mediaType)) {
            $description = 'Request "Content-Type" header is missing. See context for a list of supported media types.';
            throw new Exception\Client($description, $this->app->getSupportedContentTypes(), 415);
        }
        
        $encoder = $this->app->getEncoder($mediaType);
        if (is_null($encoder)) {
            $description = 'Request "Content-Type" not supported. See context for a list of supported media types.';
            throw new Exception\Client($description, $this->app->getSupportedContentTypes(), 415);
        }
        
        $requestBody = $this->app->getRequestBody();
        $data = $encoder->decode($requestBody, $mediaType);
        if (is_null($data)) {
            $description = 'Request body is malformed.';
            throw new Exception\Client($description, null, 400);
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
            $this->negotiateContentType(false);
        }
        if ($data instanceof Arrayable) {
            $data = $data->asArray();
        } else {
            $mediaType = $this->app->getResponseHeader('Content-Type');
            $data = $this->app->getEncoder()->encode($data, $mediaType);
        }
        echo $data;
    }
}
    
?>

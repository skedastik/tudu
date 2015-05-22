<?php
namespace Tudu\Handler\Api\User;

use \Tudu\Core;
use \Tudu\Data\Repository;
use \Tudu\Data\Model;
use \Tudu\Core\Error;

/**
 * Request handler for /users/
 */
final class Users extends UserEndpoint {
    
    protected function getAllowedMethods() {
        return 'POST';
    }
    
    /**
     * POST to "/users/" to sign up a new user.
     */
    protected function post(Core\Data\Model $model) {
        $requiredProperties = ['email', 'password'];
        if (!$model->hasProperties($requiredProperties)) {
            $missingProperties = array_values(array_diff($requiredProperties, $model->asArray()));
            $this->renderError(Error::Validation('Resource descriptor is missing required properties.', $missingProperties, 400));
        }
        
        // TODO: Return error if extraneous data is provided?
        
        $repo = new Repository\User($this->db);
        
        $result = $repo->signupUser(
            $model->get('email'),
            $model->get('password'),
            $this->delegate->getRequestIp()
        );
        if ($result instanceof Error) {
            $this->renderError($result);
            return;
        }
        
        $this->delegate->setResponseStatus(201);
        $this->delegate->setResponseHeaders([
            'Location' => '/users/'.$result
        ]);
        $this->renderBody(new Model\User([
            'user_id' => $result
        ]));
    }
}

?>

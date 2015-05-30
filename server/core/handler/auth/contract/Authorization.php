<?php
namespace Tudu\Core\Handler\Auth\Contract;

use \Tudu\Core\Data\Model;

/**
 * HTTP authorization interface.
 * 
 * Authorization is a separate concern from authentication. While a request may
 * present authentic credentials, the user agent may not be authorized to carry
 * out that request.
 */
interface Authorization {
    /**
     * Authorize a request.
     * 
     * @param \Tudu\Core\Data\Model $requester Model of user that made the
     * request.
     * @return bool TRUE if authorization succeeded, FALSE otherwise.
     */
    public function authorize(Model $requester);
}
?>

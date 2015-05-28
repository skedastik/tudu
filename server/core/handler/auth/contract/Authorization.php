<?php
namespace Tudu\Core\Handler\Auth\Contract;

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
     * @param int $userId ID of user making the request.
     * @return bool TRUE if authorization succeeded, FALSE otherwise.
     */
    public function authorize($requesterId);
}
?>

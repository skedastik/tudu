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
     * @param string $param Authorization param from HTTP "Authorization" header
     * credentials.
     * @return bool TRUE if authorization succeeded, FALSE otherwise.
     */
    public function authorize($param);
}
?>

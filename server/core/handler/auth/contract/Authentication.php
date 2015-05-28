<?php
namespace Tudu\Core\Handler\Auth\Contract;

/**
 * HTTP authentication interface.
 */
interface Authentication {
    /**
     * Get the authentication scheme.
     * 
     * @return string The authentication scheme, e.g. "basic".
     */
    public function getScheme();
    
    /**
     * Authenticate a request.
     * 
     * @param string $param Authorization param from HTTP "Authorization" header
     * credentials.
     * @return int User ID on successful authentication, NULL otherwise.
     */
    public function authenticate($param);
}
?>

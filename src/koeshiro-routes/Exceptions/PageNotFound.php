<?php

namespace Koeshiro\Routes\Exceptions;

/**
 *
 * @author rustam
 *        
 */
class PageNotFound extends \HTTPException {
    protected string $message="Not Found";
    protected $httpCode = 404;
}


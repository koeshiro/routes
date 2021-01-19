<?php

namespace Koeshiro\Routes\Exceptions;

/**
 * Description of HTTPException
 *
 * @author koesh
 */
class HTTPException extends \Exception implements Interfaces\HTTPExceptionInterface {
    protected string $message="Internal Server Error";
    protected $httpCode = 500;
    public function setStatusCode(int $httpCode) {
        $this->httpCode = $httpCode;
    }
    public function getStatusCode() {
        return $this->httpCode;
    }
    public function __toString(): string {
        return '['.$this->getStatusCode().'] '.$this->getMessage();
    }
}

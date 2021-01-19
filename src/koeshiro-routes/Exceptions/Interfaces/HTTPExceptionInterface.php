<?php

namespace koeshiro\Routes\Exceptions\Interfaces;
/**
 *
 * @author koesh
 */
interface HTTPExceptionInterface extends \Throwable {
    public function getStatusCode();
}

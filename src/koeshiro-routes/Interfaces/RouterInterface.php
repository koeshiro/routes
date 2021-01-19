<?php

namespace Koeshiro\Routes\Interfaces;

use Closure;

/**
 * Класс для работы с роутингом
 * @author rustam
 *        
 */
interface RouterInterface {
	public function map(array $Methods,string $Rout,Closure $Callback);
	public function any(string $Rout,Closure $Callback);
	public function get(string $Rout,Closure $Callback);
	public function head(string $Rout,Closure $Callback);
	public function post(string $Rout,Closure $Callback);
	public function put(string $Rout,Closure $Callback);
	public function delete(string $Rout,Closure $Callback);
	public function connect(string $Rout,Closure $Callback);
	public function options(string $Rout,Closure $Callback);
	public function track(string $Rout,Closure $Callback);
	public function path(string $Rout,Closure $Callback);
	
	public function execute(array $Options=[],string $Method=null, string $Url=null);
}


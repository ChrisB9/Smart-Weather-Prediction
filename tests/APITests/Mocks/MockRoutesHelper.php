<?php

namespace cbenco\Tests\APITests\Mocks;

use cbenco\Routes\RoutesHelper;

class MockRoutesHelper
{
	public static function create(string $phpInput,string $contentType) : RoutesHelper {
		$router = new RoutesHelper();
		$router->setPHPInput($phpInput);
		$router->setServerContentType($contentType);
		return $router;
	}
}

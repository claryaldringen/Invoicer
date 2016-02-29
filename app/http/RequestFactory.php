<?php

namespace App\Http;


class RequestFactory extends \Nette\Http\RequestFactory
{
	/**
	 * Creates current HttpRequest object.
	 * @return Request
	 */
	public function createHttpRequest()
	{
		$rawBodyCallback = function() {
			static $rawBody;

			if (PHP_VERSION_ID >= 50600) {
				return file_get_contents('php://input');

			} elseif ($rawBody === NULL) { // can be read only once in PHP < 5.6
				$rawBody = (string) file_get_contents('php://input');
			}

			return $rawBody;
		};

		$request = parent::createHttpRequest();
		return new Request($request->getUrl(), NULL, $request->getPost(), $request->getFiles(), $request->getCookies(),
		$request->getHeaders(), $request->getMethod(), $request->getRemoteAddress(), $request->getRemoteHost(), $rawBodyCallback);
	}
}
<?php

namespace App\Http;


class Request extends \Nette\Http\Request
{

	/** @var mixed */
	protected $put = NULL;

	/**
	 * Returns variable provided to the script via PUT method.
	 * If no key is passed, returns the entire array.
	 *
	 * @param  string key
	 * @param  mixed  default value
	 * @return mixed
	 */
	public function getPut($key = NULL, $default = NULL)
	{
		if($this->isMethod('PUT')) {
			if(empty($this->put)) {
				$this->put = parse_str($this->getRawBody());
			}
			if (func_num_args() === 0) {
				return $this->put;
			} elseif (isset($this->put[$key])) {
				return $this->put[$key];
			}
		}
		return $default;
	}
}
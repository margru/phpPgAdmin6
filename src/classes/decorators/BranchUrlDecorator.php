<?php
namespace PHPPgAdmin\Decorators;

class BranchUrlDecorator extends Decorator {
	function __construct($base, $queryVars = null) {

		//\PC::debug($base, 'BranchUrlDecorator');

		$this->b = $base;
		if ($queryVars !== null) {
			$this->q = $queryVars;
		}

	}

	function value($fields) {
		$url = value($this->b, $fields);

		if ($url === false) {
			return '';
		}

		if (!empty($this->q)) {
			$queryVars = value($this->q, $fields);

			$sep = '?';
			foreach ($queryVars as $var => $value) {
				$varname  = value_url($var, $fields);
				$varvalue = value_url($value, $fields);
				if ($varname == 'action') {
					if ($varvalue == 'subtree') {
						$url = '/tree/' . str_replace('.php', '/subtree', $url);
					} else {
						$url = '/tree/' . str_replace('.php', '', $url);
					}
				}
				$url .= $sep . $varname . '=' . $varvalue;
				$sep = '&';
			}
		}
		return str_replace('.php', '', $url);
	}
}
<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |        Dmitry N. Lebedeff        / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |           <dl@adios.ru>         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____                            / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/          (C)2021          /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    interface dl\Immutable
	trait dl\PropertyGetter

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

// Immutable properties container
interface Immutable {
	public function __get(string $name): mixed;
	public function __isset(string $name): bool;
}

// Immutable implementation
trait PropertyGetter {
	abstract protected function initialize(): void;

	protected array $_property = [];

	final public function __get(string $name): mixed {
		if (isset($this->_property[$name])) {
			return $this->_property[$name];
		}

		return null;
	}

	final public function __isset(string $name): bool {
		return isset($this->_property[$name]);
	}
}

trait PropertyGetterCall {
	final public function __call(string $name, array $vars): mixed {
		if (isset($this->_property[$name])) {
			if (isset($vars[0])) {
				return \str_replace(
					\array_map(fn($key) => '{'.$key.'}', \array_keys($vars)),
					$vars,
					$this->_property[$name]
				);
			}
			
			return $this->_property[$name];
		}

		return null;
	} 
}

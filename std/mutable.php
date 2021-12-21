<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |        Dmitry N. Lebedeff        / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |           <dl@adios.ru>         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____                            / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/          (C)2021          /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    interface dl\Mutable
	trait dl\PropertySetter

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

// Mutable properties container
interface Mutable extends Immutable {
	public function __set(string $name, mixed $value): void;
	public function clean(): void;
}

// Muttable implementation
trait PropertySetter {
	use PropertyGetter;

	final public function __set(string $name, mixed $value): void {
		if (isset($this->_property[$name])) {
			$this->_property[$name] = $value;
		}
	}

	final public function clean(): void {
		$this->initialize();
	}
}

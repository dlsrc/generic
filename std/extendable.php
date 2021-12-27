<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |        Dmitry N. Lebedeff        / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |           <dl@adios.ru>         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____                            / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/          (C)2021          /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    interface dl\Extendable
	

	trait dl\PropertyCollector

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

/**
* Extendable container
*/
interface Extendable {
	public function attach(Attachable $att, bool $new_only = false): void;
	public function getExpectedProperties(): array;
}

/**
* Extendable implementation
*/
trait PropertyCollector {
	abstract protected function getAttachedPropertyHandler(string $property): callable|null;

	final public function attach(Attachable $att, bool $new_only = false): void {
		if ($this instanceof Extendable) {
			$state = $att->getState($this);

			if (empty($state)) {
				return;
			}
		}
		else {
			return;
		}

		foreach ($state as $name => $value) {
			if ($handler = $this->getAttachedPropertyHandler($name)) {
				$this->$name = $handler($this->$name, $value);
				continue;
			}

			if (!$new_only) {
				foreach ($value as $k => $v) {
					$this->$name[$k] = $v;
				}

				continue;
			}

			foreach ($value as $k => $v) {
				$this->$name[$k] ??= $v;
			}
		}
	}

	public function getExpectedProperties(): array {
		return [];
	}
}

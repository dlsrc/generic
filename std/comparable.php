<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |        Dmitry N. Lebedeff        / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |           <dl@adios.ru>         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____                            / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/          (C)2021          /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    interface dl\Comparable
	trait dl\Comparison

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

// Comparable object
interface Comparable {
	public function isCompatible(Immutable $getter, string $property = '_property', bool $by_vals = false): bool;
	public function isEqual(Comparable $with, string $property = '_property', bool $by_vals = false): bool;
}

// Comparable implementation
trait Comparison {
	final public function isCompatible(Immutable $getter, string $property = '_property', bool $by_vals = false): bool {
		if (!\property_exists($this, $property)) {
			return false;
		}

		if ($by_vals) {
			foreach ($this->$property as $name => $value) {
				if (!isset($getter->$name)) {
					return false;
				}

				if ($getter->$name !== $value) {
					return false;
				}
			}
		}
		else {
			foreach (\array_keys($this->$property) as $name) {
				if (!isset($getter->$name)) {
					return false;
				}
			}
		}

		return true;
	}

	public function isEqual(Comparable $with, string $property = '_property', bool $by_vals = false): bool {
		if (!$this->isCompatible($with, $property, $by_vals)) {
			return false;
		}

		if (!$with->isCompatible($this, $property, $by_vals)) {
			return false;
		}

		return true;
	}
}

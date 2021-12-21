<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |        Dmitry N. Lebedeff        / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |           <dl@adios.ru>         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____                            / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/          (C)2021          /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    interface dl\Attachable
	Интерфейс присоединяемости свойств внутри контейнера свойств

	trait dl\PropertyKit

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

// Attachable container
interface Attachable {
	public function getState(Extendable $ext): array;
}

// Attachable implementation
trait PropertyKit {
	final public function getState(Extendable $ext): array {
		$vars = \get_object_vars($this);
		$expect = $ext->getExpectedProperties();

		if (empty($expect)) {
			$expect = \array_keys($vars);
		}

		foreach(\array_keys($vars) as $name) {
			if (!\is_array($vars[$name]) || empty($vars[$name]) || !\in_array($name, $expect)) {
				unset($vars[$name]);
			}
		}

		return $vars;
	}
}

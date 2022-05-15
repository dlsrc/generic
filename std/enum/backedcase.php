<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    interface dl\PreferredBackedCase
	trait dl\CurrentBackedCase

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

/**
* Методы расширяющие возможности интерфейса dl\PreferredCase,
* добавляют дополнительную функциональность в типизированные перечисления.
*/
interface PreferredBackedCase extends PreferredCase {
	/**
	* Получить скалярное значение предпочитаемого варианта
	* в текущем перечислении.
	*/
	public static function get(): int|string;

	/**
	* Пытаться установить вариант в качестве основного (предпочитаемого)
	* в текущем перечислении посредством скалярного целого
	* или строкового значения.
	*/
	public static function set(int|string $value): void;
}

/**
* Реализация интерфейса dl\PreferredBackedCase.
* Использует трейт dl\CurrentCase как реализацию основной
* части интерфейса dl\PreferredCase.
*
* В перечислении использующем dl\CurrentBackedCase должен быть определен
* метод byDefault(), либо в перечислении нужно задействовать
* трейт dl\DefaultCase.
*/
trait CurrentBackedCase {
	use CurrentCase;

	final public static function get(): int|string {
		return self::now()->value;
	}

	final public static function set(int|string $value): void {
		self::now(self::tryFrom($value));
	}
}

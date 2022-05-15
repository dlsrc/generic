<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    interface dl\PreferredCase
	trait dl\CurrentCase

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

/**
* Интерфейс предпочитаемого варианта перечисления.
* Позволяет установить один из вариантов перечисления в качестве основного
* варианта, что позволяет использовать это значение в разных частях программы
* без явного на него указания.
*/
interface PreferredCase {
	/**
	* Вернуть вариант по умолчанию для текущего перечисления.
	* Необходимо, для установки в качестве основного варианта перечисления,
	* если основной вариант для перечисления не был выбран.
	*/
	public static function byDefault(): static;

	/**
	* Установить новый предпочитаемый вариант перечисления и вернуть предыдущий.
	* Если основной вариант до этого не устанавливался, вернет значение по умолчанию.
	* Если метод вызван без аргументов, вернуть текущей предпочитаемый выриант,
	* либо значение по умолчанию.
	*/
	public static function now(self|null $case = null): static;

	/**
	* Пытается установить предпочитаемый вариант по имени варианта перечисления.
	* Вернет предыдущий предпочитаемый вариант перечисления.
	*/
	public static function nowByName(string $name): static;

	/**
	* Вернуть имя предпочитаемого варианта в текущем перечислении.
	*/
	public static function name(): string;
	
	/**
	* Проверить, является ли текущее значение перечисления основным.
	*/
	public function current(): bool;
}

/**
* Реализация метода интерфейса dl\PreferredCase byDefault().
*/
trait DefaultCase {
	/**
	* Возвращает первый вариант из списка вариантов текщего перечисления.
	*/
	final public static function byDefault(): static {
		return self::cases()[0];
	}
}

/**
* Реализация методов интерфейса dl\PreferredCase:
* now(); nowByName(); name(); current().
*
* Метод now() использует другой метод интерфейса dl\PreferredCase - byDefault().
* Метод byDefault() должен быть определен в самом перечислении,
* либо в перечислении нужно задействовать трейт dl\DefaultCase.
* 
* Метод nowByName() использует метод byName() из трейта dl\SearchingCase.
*/
trait CurrentCase {
	use SearchingCase;

	final public static function now(PreferredCase|null $case = null): static {
		static $current = null;

		if (null == $case || $case::class != self::class) {
			return $current ?? self::byDefault();
		}

		$previous = $current ?? self::byDefault();
		$current  = $case;
		return $previous;
	}

	final public static function nowByName(string $name): static {
		return self::now(self::byName($name));
	}

	final public static function name(): string {
		return self::now()->name;
	}

	final public function current(): bool {
		return self::now() === $this;
	}
}

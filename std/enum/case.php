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

interface PreferredCase {
	public static function byDefault(): static;
	public static function now(self|null $case = null): static;
	public static function nowByName(string $name): static;
	public static function name(): string;
	public function current(): bool;
}

trait DefaultCase {
	final public static function byDefault(): static {
		return self::cases()[0];
	}
}

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

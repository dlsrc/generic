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
	public static function byDefault(): self;
	public static function now(self|null $case = null): self;
	public function current(): bool;
}

trait CurrentCase {
	final public static function now(PreferredCase|null $case = null): self {
		static $current = null;

		if (null == $case || $case::class != self::class) {
			return $current ?? self::byDefault();
		}

		$previous = $current ?? self::byDefault();
		$current  = $case;
		return $previous;
	}

	final public function current(): bool {
		return self::now() === $this;
	}
}

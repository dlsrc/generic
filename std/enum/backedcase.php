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

interface PreferredBackedCase extends PreferredCase {
	public static function get(): int|string;
	public static function set(int|string $value): void;
}

trait CurrentBackedCase {
	use CurrentCase;

	final public static function get(): int|string {
		return self::now()->value;
	}

	final public static function set(int|string $value): void {
		self::now(self::tryFrom($value));
	}
}

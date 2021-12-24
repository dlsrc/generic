<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

	trait dl\CaseSearch

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

trait CaseSearch {
    final public static function byName(string $name): static|null {
        foreach(self::cases() as $case) {
            if ($name == $case->name) {
                return $case;
            }
        }

        return null;
    }

    final public static function inCases(string $name, array $cases): bool {
        if (!$case = self::byName($name)) {
			return false;
		}

		return \in_array($case, $cases);
    }
}

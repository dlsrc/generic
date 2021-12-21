<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    enum dl\Lang

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

enum Lang: string implements PreferredBackedCase {
	use CurrentBackedCase;

	case en = 'en';
	case ru = 'ru';
	case ja = 'ja';
	case de = 'de';
	case es = 'es';
	case fr = 'fr';
	case pt = 'pt';
	case it = 'it';
	case ar = 'ar';
	case zh = 'zh';
	case uk = 'uk';

	public static function byDefault(): self {
		return self::en;
	}

	public static function inCases(string $name, array $cases): bool {
		if (!$case = self::tryFrom($name)) {
			return false;
		}

		return \in_array($case, $cases);
	}

	public function id(): string {
		return match($this) {
			self::ru => '1',
			self::en => '2',
			self::ar => '3',
			self::es => '4',
			self::zh => '5',
			self::fr => '6',
			self::de => '7',
			self::ja => '8',
			self::it => '9',
			self::pt => '10',
			self::uk => '11',
		};
	}

	public function name(): string {
		return match($this) {
			self::en => 'English',
			self::ru => 'Русский',
			self::ja => '日本語',
			self::de => 'Deutsch',
			self::es => 'Español',
			self::fr => 'Français',
			self::pt => 'Português',
			self::it => 'Italiano',
			self::ar => 'اللغة العربية',
			self::zh => '中文',
			self::uk => 'Українська',
		};
	}
}

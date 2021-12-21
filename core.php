<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    final class dl\Core

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

final class Core implements Sociable {
	use Informer;

	private static string $_header = __DIR__.'/header.txt';

	public static function e(mixed $object): bool {
		if (\is_object($object)) {
			if ('dl\\Error' == $object::class) {
				return true;
			}
		}

		return false;
	}

	public static function getHeader(string $file = ''): string {
		if ('' == $file) {
			$file = self::$_header;
		}

		if (!\file_exists($file)) {
			return '';
		}

		if (!$header = \file_get_contents($file)) {
			return '';
		}

		return $header;
	}

	public static function setHeader(string $file): bool {
		if (\file_exists($file)) {
			self::$_header = $file;
			return true;
		}

		if (!\str_contains($file, '/') && \file_exists(__DIR__.'/'.$file)) {
			self::$_header = __DIR__.'/'.$file;
			return true;
		}

		return false;
	}
}

<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    final class dl\IO

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

final class IO implements Sociable {
	use Informer;

	/**
	* Права по умолчанию для создаваемых папок
	*/
	private static int $_dir  = 0755;

	/**
	* Права по умолчанию для файлов
	*/
	private static int $_file = 0644;

	public static function dm(int $mode = 0): int {
		if (0 == $mode) {
			return self::$_dir;
		}

		$old = self::$_dir;
		self::$_dir = $mode;
		return $old;
	}

	public static function fm(int $mode = 0): int {
		if (0 == $mode) {
			return self::$_file;
		}

		$old = self::$_file;
		self::$_file = $mode;
		return $old;
	}

	/**
	* Проверить существование директории по имени,
	* если директория не существует, пытаться создать.
	*/
	public static function isdir(string $dir): bool {
		$dir  = \strtr($dir, '\\', '/');

		if ('/' == $dir) {
			return true;
		}

		if (\str_ends_with($dir, '/')) {
			$dir = \substr($dir, 0, -1);
		}

		if (!\is_dir($dir)) {
			$mtx = Mutex::make(__FILE__, \substr($dir, -1), true);

			if (Core::e($mtx)) {
				if (!\mkdir($dir, self::$_dir, true)) {
					if (\file_exists($dir)) {
						return true;
					}

					Error::log(self::message('e_make_dir', $dir), Error::MAKEDIR);
					return false;
				}
			}
			else {
				if ($mtx->acquire(true)) {
					if (!\file_exists($dir)) {
						if (!\mkdir($dir, self::$_dir, true)) {
							$mtx->release();
							Error::log(self::message('e_make_dir', $dir), Error::MAKEDIR);
							return false;
						}
					}

					$mtx->release();
				}
			}
		}

		return true;
	}

	/**
	* Проверить существование файла,
	* если файл не существует, пытаться проверить и создать директорию
	* в которой он должен был бы находиться.
	*/
	public static function indir(string $file): bool {
		if (!\is_file($file)) {
			return self::isdir(\dirname($file));
		}

		return true;
	}

	/**
	* Копировать папку и(или) все ее содержимое
	* Если третий параметр указан как TRUE то копируется сама директория
	* Если четвертый параметр указан как TRUE то источник удаляется
	*/
	public static function cp(string $from, string $to, bool $dir = false, bool $unlink = false): bool {
		if (!self::isdir($to)) {
			return false;
		}

		if (!\is_dir($from)) {
			Error::log(self::message('e_dir', $from), Error::NODIR, false);
			return false;
		}

		$from = \strtr(\realpath($from), '\\', '/');
		$to = \strtr(\realpath($to), '\\', '/');

		if ($dir) {
			$to = $to.\substr($from, \strrpos($from, '/'));
		}

		$from = [$from];
		$to   = [$to];
		$out  = [];
		$in   = [];

		for ($i=0; isset($from[$i]); $i++) {
			$scan = \scandir($from[$i]);

			foreach($scan as $name) {
				if ('.' == $name || '..' == $name) {
					continue;
				}

				if (\is_dir($from[$i].'/'.$name)) {
					if (!self::isdir($to[$i].'/'.$name)) {
						return false;
					}

					$from[] = $from[$i].'/'.$name;
					$to[] = $to[$i].'/'.$name;
				}
				else {
					$out[] = $from[$i].'/'.$name;
					$in[]  = $to[$i].'/'.$name;
				}
			}
		}

		foreach (\array_keys($out) as $i) {
			if (!\copy($out[$i], $in[$i])) {
				Error::log(
					self::message(
						'e_copy',
						\substr($out[$i], \strrpos($out[$i], '/')),
						\substr($out[$i], 0, \strrpos($out[$i], '/')),
						\substr($in[$i], 0, \strrpos($in[$i], '/'))
					),
					Error::COPY,
					false
				);

				return false;
			}

			\chmod($in[$i], self::$_file);

			if ($unlink && !\unlink($out[$i])) {
				Error::log(self::message('e_unlink', $out[$i]), Error::UNLINK);
			}
		}

		if ($unlink) {
			if (!$dir) {
				unset($from[0]);
			}

			$from = \array_reverse($from);

			foreach ($from as $name) {
				if (!\rmdir($name)) {
					Error::log(self::message('e_rmdir', $name), Error::RMDIR);
				}
			}
		}

		return true;
	}

	/**
	* Копировать файл
	*/
	public static function fc(string $from, string $to): bool {
		if (!self::indir($to)) {
			return false;
		}

		if (!\copy($from, $to)) {
			Error::log(
				self::message(
					'e_copy',
					\substr($from, \strrpos($from, '/')),
					\substr($from, 0, \strrpos($from, '/')),
					\substr($to, 0, \strrpos($to, '/'))
				),
				Error::COPY,
				false
			);

			return false;
		}

		\chmod($to, self::$_file);
		return true;
	}

	/**
	* Перенести файл
	*/
	public static function move(string $from, string $to): bool {
		if (!file_exists($from)) {
			Error::log(self::message('e_file', $from), Error::NOFILE);
			return false;
		}

		if (!self::indir($to)) {
			return false;
		}

		if (!\rename($from, $to)) {
			Error::log(self::message('e_rename', $from, $to), Error::RENAME);
			return false;
		}

		\chmod($to, self::$_file);
		return true;
	}

	/**
	* Перенести папку и(или) все ее содержимое
	*/
	public static function ren(string $from, string $to, bool $dir = false): bool {
		return self::cp($from, $to, $dir, true);
	}

	/**
	* Удалить папку и(или) все ее содержимое
	*/
	public static function rm(string $fold, bool $dir = false): bool {
		if (!\is_dir($fold)) {
			Error::log(self::message('e_dir', $fold), Error::NODIR);
			return false;
		}

		$fold = [\strtr(\realpath($fold), '\\', '/')];
		$file = [];

		for ($i=0; isset($fold[$i]); $i++) {
			$scan = \scandir($fold[$i]);

			foreach($scan as $name) {
				if ('.' == $name || '..' == $name) {
					continue;
				}

				if (\is_dir($fold[$i].'/'.$name)) {
					$fold[] = $fold[$i].'/'.$name;
				}
				else {
					$file[] = $fold[$i].'/'.$name;
				}
			}
		}

		foreach ($file as $name) {
			if (!\unlink($name)) {
				Error::log(self::message('e_unlink', $name), Error::UNLINK);
			}
		}

		if (!$dir) {
			unset($fold[0]);
		}

		$fold = \array_reverse($fold);

		foreach ($fold as $name) {
			if (!\rmdir($name)) {
				Error::log(self::message('e_rmdir', $name), Error::RMDIR);
			}
		}

		return true;
	}

	public static function fw(string $file, string $content): int {
		$len = \file_put_contents($file, $content);

		if (FALSE === $len) {
			Error::log(self::message('e_make_file', $file), Error::MAKEFILE);
			$len = -1;
		}
		elseif (!\chmod($file, self::$_file)) {
			Error::log(self::message('e_chmod', $file), Error::CHMOD);
		}

		return $len;
	}

	/**
	* Блокировка конструктора
	*/
	private function __construct() {}
}
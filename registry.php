<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    final class dl\Registry

	Выделенная из класса загрузчика ресурсоемкая, редко исполняемая
	процедура создания и обновления главного реестра классов.

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

final class Registry {
	private const PATTERN =
	'/(?:(?:abstract|final|)class|interface|trait|enum)
	\s+(\w+)
	(?:\s*\:\s+(?:int|string)|
	\s*\:\s+(?:int|string)\s+implements\s+[^\{]*\w|
	\s+implements\s+[^\{]*\w|\s+extends\s+[^\{]*\w|)
	\s+\{/xis';

	// Флаг пройденной перезагрузки реестра.
	private static bool $done = false;

	/**
	* Запросить построение (перезагрузку) реестра классов.
	* b - экземпляр загрузчика.
	*/
	public static function build(Boot $b): void {
		if (self::$done) {
			return;
		}

		$key = \ftok(__FILE__, 'b');

		if (-1 == $key) {
			return;
		}

		// Не менять последовательность подключения
		include_once __DIR__.'/std/enum/search.php';
		include_once __DIR__.'/std/enum/case.php';
		include_once __DIR__.'/std/enum/backedcase.php';
		include_once __DIR__.'/lang.php';
		include_once __DIR__.'/mode.php';
		include_once __DIR__.'/std/direct_callable.php';
		include_once __DIR__.'/std/callable_state.php';
		include_once __DIR__.'/std/immutable.php';
		include_once __DIR__.'/std/sociable.php';
		include_once __DIR__.'/std/storable.php';
		include_once __DIR__.'/std/container/container.php';
		include_once __DIR__.'/std/container/nameless.php';
		include_once __DIR__.'/std/container/getter.php';
		include_once __DIR__.'/core.php';
		include_once __DIR__.'/error.php';
		include_once __DIR__.'/errorlog.php';
		include_once __DIR__.'/io.php';
		include_once __DIR__.'/exporter.php';
		include_once __DIR__.'/lang/core.'.Lang::name().'.php';
		include_once __DIR__.'/lang/io.'.Lang::name().'.php';
		include_once __DIR__.'/std/process/mutex.php';

		if (\extension_loaded('sysvsem')) {
			include_once __DIR__.'/std/process/mutexsysvsem.php';
			$mtx = SysVSemMutex::get($key);
		}
		elseif (\extension_loaded('shmop')) {
			include_once __DIR__.'/std/process/mutexshmop.php';
			$mtx = ShmopMutex::get($key);
		}
		else {
			include_once __DIR__.'/std/process/mutexfile.php';
			$mtx = FileMutex::get($key);
			$mtx->setpath(\dirname($b->regdir));
		}

		$r = new Registry;

		if ($mtx->acquire()) {
			$r->create($b);
			self::$done = true;
			$mtx->release();
			return;
		}

		if ($b->wait) {
			if ($mtx->acquire(true)) {
				self::$done = true;
				$mtx->release();
			}
		}
	}

	private function __construct() {}

	/**
	* Поиск в исходном коде строковых литералов и их удаление.
	* code - ссылка на строку исходного кода,
	*        из которого нужно удалить строковые литералы.
	*/
	private function cutStrings(string &$code): void {
		for (;;) {
			$pos1 = \mb_strpos($code, '\'');
			$pos2 = \mb_strpos($code, '"');

			if (!$pos1 && !$pos2) {
				break;
			}

			if ((!$pos1 && $pos2) || ($pos1 && $pos2 && $pos1 > $pos2)) {
				$start = $pos2;
				$end   = '"';
			}
			elseif (($pos1 && !$pos2) || ($pos1 && $pos2 && $pos1 < $pos2)) {
				$start = $pos1;
				$end   = '\'';
			}

			$posend =  \mb_strpos($code, $end, $start+1);

			if (!$posend) {
				if (\is_bool($posend)) {
					break;	
				}
				else {
					$code = \mb_substr($code, 0, $start).\mb_substr($code, 1);
				}
			}
			else {
				while ('\\' == $code[$posend-1]) {
					for ($i = 2; '\\' == $code[$posend-$i]; $i++);

					if ($i % 2) {
						break;
					}

					$posend = \mb_strpos($code, $end, $posend + 1);
				}

				$code  = \mb_substr($code, 0, $start).\mb_substr($code, $posend+1);
			}
		}
	}

	/**
	* Поиск нескольких пространств имен в исходном коде.
	* Разделить исходнвй код на части по пространствам имен.
	* Вернуть список частей кода.
	* Если в коде нет пространств имен или пространство имен одно,
	* список будет состоять из одного элемента.
	* code - строка исходного кода.
	*/
	private function splitNamespaces(string $code): array {
		if (\preg_match_all('/\s+namespace\s*([\w\\\\]*)(\;|\s*\{)/is', $code, $match)) {
			$split = \preg_split('/\s+namespace\s*([\w\\\\]*)(\;|\s*\{)/is', $code);
			$ns = [];

			foreach ($match[1] as $i => $name) {
				if ('' != $name) {
					$name.= '\\';
				}

				if (isset($ns[$name])) {
					$ns[$name].= $split[$i+1];
				}
				else {
					$ns[$name] = $split[$i+1];
				}
			}
		}
		else {
			$ns = ['' => $code];
		}

		return $ns;
	}

	/**
	* Очистить исходный код от комментариев и сроковых литералов (см. dl\Registry::cutStrings()).
	* Вернуть оставшийся текст кода в виде списка, разделив код по пространствам имен.
	* file - файл с исходным кодом.
	*/
	private function splitCode(string $file): array {
		$code = \php_strip_whitespace($file);
		$code = \preg_replace('/<<<(?:\'|\")?([^\s\'\"]+)(?:\'|\")?.+\\1\;/Uis', '', $code);
		$this->cutStrings($code);
		return $this->splitNamespaces($code);
	}

	/**
	* Построение реестра классов с передачей результата в загрузчик классов
	* b - экземпляр объекта загрузчика
	*/
	public function create(Boot $b): void {
		$dir = $b->libs;
		$exc = $b->exc;
		$ext = $b->ext;

		if (empty($ext)) {
			$ext = '*';
		}
		else {
			$ext = '{'.\implode(',', $b->ext).'}';
		}

		$register = [];

		\ignore_user_abort(true);
		\set_time_limit(0);

		for ($i = 0; isset($dir[$i]); $i++) {
			if (!$files = \glob($dir[$i].'*.'.$ext, GLOB_BRACE)) {
				$files = [];
			}

			foreach ($files as $file) {
				foreach ($this->splitCode($file) as $name => $code) {
					if (\preg_match_all(self::PATTERN, $code, $match)) {
						foreach ($match[1] as $itc) {
							$itc = $name.$itc;
							
							if (isset($register[$itc])) {
								if ($register[$itc] == $file) {
									continue;
								}

								if (\filemtime($register[$itc]) > \filemtime($file)) {
									continue;
								}

								while (\in_array($file, $register)) {
									$key = \array_search($file, $register);
									unset($register[$key]);
								}
							}

							$register[$itc] = $file;
						}
					}
				}
			}

			if (!$list = \scandir($dir[$i])) {
				continue;
			}

			foreach ($list as $val) {
				if (\is_dir($dir[$i].$val)) {
					if ('.' == $val || '..' == $val) {
						continue;
					}

					if (\in_array($dir[$i].$val.'/', $exc)) {
						continue;
					}

					$dir[] = $dir[$i].$val.'/';
				}
			}
		}

		$b->addRegister($register);
	}
}

<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    final class dl\Boot

	Обнаружение и автоматическая загрузка классов, интерфейсов,
	трейтов и перечислений.

	Задействовать другие автозагрузчики	не нужно (но возможно).

	Список (class map) классов, интерфейсов, трейтов и перечислений хранится
	в файловом реестре, с возможностью выделения из него маленьких списков -
	веткок реестра.

	По умолчанию, ветка реестра создается для каждого исполняемого скрипта,
	но, так же, разбиение реестра на списки можно задавать вручную,
	например, в точках ветвления алгоритма.

	В многокомпонентных приложениях у каждого компонента может быть своя
	ветка реестра.

	Возможно создавать несколько пространств имен и(или) группировать
	несколько классов в одном файле, например, когда несколько простых
	объектов часто (или всегда) используются совместно или выстраиваются
	в единую композицию.

	Файлы, папки и пространства имен можно называть руководствуясь логикой
	приложения, а не искуственными правилами. Например, можно собрать
	классы и интерфейсы из разных пространств имен в одной папке если они
	композиционно составляют единый компонент приложения.

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

final class Boot {
	// Файл (только имя) возвращающий полный реестр всех классов и интерфейсов.
	private const REGISTRY = '00000000000000000000000000000000.php';

	// Экземпляр загрузчика
	private static self|null $_boot = null;

	// Список папок в файлах которых будет выполняться поиск классов и интерфейсов
	public array  $libs;

	// Папка в которую сохраняются файлы возвращающие реестры классов и интерфейсов
	public string $regdir;

	// Файл php, с кодом возврата списка с текущей веткой реестра классов
	public string $manifest;

	// Имя текущей ветки реестра классов
	public string $branch;

	// Список всех задействованых веток реестра классов
	public array $data;

	// Список файлов веток реестра подлежащих перезаписи
	public array $save;

	// Список путей (внутри папок поиска) изъятых из процесса сканирования
	public array $exc;

	// Файлы, в которых будет выполняться поиск по классам,
	// должны иметь указанные расширения.
	// Если список пуст, будут просмотрены все файлы во всех папках. 
	public array $ext;

	// Флаг, позволяющий процессу ждать окончания составления полного реестра классов,
	// начатое другим процессом.
	// Актуально для консольных приложений запускаемых по расписанию.
	// см. dl\Registry
	public bool $wait; 

	// Метод инициализации и настройки загрузчика.
	// array libs    - список папок включаемых в поиск классов
	// array exclude - список папок исключенных из поиска классов
	// string regdir - путь до папки реестров с результатами поиска 
	// string srcdir - исходная папка от которой строятся относительные пути ко всем директориям
	public static function start(
		array  $libs    = [],
		array  $exclude = [],
		string $regdir  = '',
		string $srcdir  = ''
	): void {
		if (self::$_boot) {
			return;
		}

		if ('' == $srcdir) {
			$srcdir = \strtr(\dirname(\realpath($_SERVER['SCRIPT_FILENAME'])), '\\', '/');
		}
		else {
			$srcdir = \strtr($srcdir, '\\', '/');
		}

		$fn_abs = function (string $dir) use ($srcdir): string {
			if ($count = \substr_count($dir, '../')) {
				$dir = \strtr(\dirname($srcdir, $count), '\\', '/').'/'.\str_replace('../', '', $dir);
			}
			elseif ('./' == $dir) {
				$dir = $srcdir;
			}
			elseif (\str_starts_with($dir, './')) {
				$dir = $srcdir.\mb_substr($dir, 1);
			}
			elseif (!\str_starts_with($dir, '/')) {
				$dir = $srcdir.'/'.$dir;
			}

			return $dir;
		};

		$fn_filter = function(array &$dir) use (&$fn_abs, $srcdir): void {
			foreach (\array_keys($dir) as $id) {
				$dir[$id] = $fn_abs($dir[$id], $srcdir);

				if (!\is_dir($dir[$id])) {
					unset($dir[$id]);
					continue;
				}

				if (!\str_ends_with($dir[$id], '/')) {
					$dir[$id] = $dir[$id].'/';
				}
			}
		};

		if (empty($libs)) {
			$libs = [\strtr(\dirname(__DIR__), '\\', '/')];
		}

		$fn_filter($libs);

		if (empty($libs)) {
			$libs = [\strtr(\dirname(__DIR__), '\\', '/').'/'];
		}
		else {
			$libs = \array_values($libs);
		}

		$fn_filter($exclude);

		if ('' == $regdir) {
			$regdir = \strtr(\dirname($libs[0]), '\\', '/').'/.manifest/';
		}
		else {
			$regdir = $fn_abs($regdir);

			if (!\is_dir($regdir)) {
				$regdir = \strtr(\dirname($libs[0]), '\\', '/').'/.manifest/';
			}
			else {
				$regdir = $regdir.'/.manifest/';
			}
		}

		self::$_boot = new Boot($libs, $exclude, $regdir);

		\spl_autoload_register([self::$_boot, 'load'], true, true);
	}

	/**
	* Переключиться на другую ветку реестра классов
	* name - строка с условным наименованием ветки
	* load - флаг немедленной загрузки всех классов перечисленных в ветке.
	*        По умолчанию FALSE - классы загружаются по требованию (__autoload()).
	*/
	public static function branch(string $name, bool $load = false): void {
		if (self::$_boot) {
			self::$_boot->changeManifest($name);

			if ($load) {
				self::$_boot->includeManifest();
			}
		}
	}

	/**
	* Изменить список расширений для файлов поиска
	*/
	public static function extension(string ...$ext): void {
		if (self::$_boot) {
			if (empty($ext)) {
				self::$_boot->ext = [];
			}
			else {
				self::$_boot->ext = $ext;
			}
		}
	}

	/**
	* Найти файл класса.
	* Вернет полный путь до файла класса.
	* class  - Имя класса.
	* remake - Создать заново реестр классов, если класс не найден
	*/
	public static function find(string $class, bool $remake = true): string {
		if (self::$_boot) {
			return self::$_boot->getClassPath($class, $remake);
		}

		return '';
	}

	/**
	* Изменить флаг ожидания процессом окончания процедуры поиска
	* начатое другим процессом.
	* wait - флаг ожидания.
	* TRUE - процесс дождется окончания поиска классов,
	* FALSE - процесс бросит исключение.
	*/
	public static function wait(bool $wait = true): void {
		if (self::$_boot) {
			self::$_boot->wait = $wait;
		}
	}

	/**
	* Конструктор
	* libs    - список папок поиска библиотек классов
	* exclude - список путей исключенных из поиска
	* regdir  - папка с файлами реестра классов
	*/
	private function __construct(array $libs, array $exclude, string $regdir) {
		$this->libs     = $libs;
		$this->regdir   = $regdir;
		$this->manifest = '';
		$this->branch   = '';
		$this->save     = [];
		$this->exc      = $exclude;
		$this->exc[]    = $this->regdir;
		$this->ext      = ['php'];
		$this->wait     = false;
		$this->changeManifest($_SERVER['SCRIPT_FILENAME']);
	}

	/**
	* Деструктор
	* Перезаписывает файлы реестра при необходимости.
	*/
	public function __destruct() {
		if (empty($this->save)) {
			return;
		}

		if (!IO::isdir($this->regdir)) {
			return;
		}

		$mode = Mode::now(Mode::Develop);

		$e = new Exporter;
		$p = Core::pattern('src_header');
		$date = \date('Y');

		foreach ($this->save as $manifest => $branch) {
			if (self::REGISTRY == $manifest) {
				continue;
			}

			\ksort($this->data[$manifest]);

			$e->setFilename($this->regdir.$manifest);

			$e->save(
				$this->data[$manifest],
				$p->replace(
					$branch,
					$date,
					\PHP_MAJOR_VERSION.'.'.\PHP_MINOR_VERSION
				)
			);
		}

		Mode::now($mode);
	}

	/**
	* Записать свежий реестр классов, переданный из
	* dl\Registry->rehash(Boot $b);
	*/
	public function addRegister(array $register): void {
		$this->data[self::REGISTRY] = $register;
		$this->save[self::REGISTRY] = Core::message('h_registry');

		\ksort($this->data[self::REGISTRY]);

		$mode = Mode::now(Mode::Develop);

		(new Exporter($this->regdir.self::REGISTRY))->save(
			$this->data[self::REGISTRY],
			Core::pattern('src_header')->replace(
				$this->save[self::REGISTRY],
				\date('Y'),
				\PHP_MAJOR_VERSION.'.'.\PHP_MINOR_VERSION
			)
		);

		Mode::now($mode);
	}

	/**
	* Подключить файл и проверить доступность класса, интерфейса,
	* трейта или перечисления (проверка без использования автозагрузчика).
	* class - имя класса, интерфейса, трейта или перечисления
	* Вернет TRUE если класс доступен, FALSE - если нет.
	*/
	private function isClass(string $class): bool {
		if (\file_exists($this->data[$this->manifest][$class])) {
			include_once $this->data[$this->manifest][$class];

			if (\class_exists($class, false)) {
				return true;
			}

			if (\interface_exists($class, false)) {
				return true;
			}

			if (\trait_exists($class, false)) {
				return true;
			}

			if (\enum_exists($class, false)) {
				return true;
			}
		}

		return false;
	}

	/**
	* Проверить попадание класса в текущую ветку реестра.
	* Если класс отсутствует в текущей ветке,
	* но находится в общем списке классов, он будет включен в текущую ветку.
	* Файл измененной ветки будет перезаписан в деструкторе.
	* class - имя класса, интерфейса, трейта или перечисления.
	* Вернет TRUE если класс зарегистрирован в текущей ветке,
	* FALSE - если класс нигде не обнаружен.
	*/
	private function isRegistered(string $class): bool {
		if (isset($this->data[self::REGISTRY][$class])) {
			$this->data[$this->manifest][$class] = $this->data[self::REGISTRY][$class];

			if ($this->isClass($class)) {
				$this->save[$this->manifest] ??= $this->branch;
				return true;
			}
		}

		return false;
	}

	/**
	* Запросить полную перезапись реестра.
	* см. dl\Registry
	*/
	private function reboot(): void {
		include_once __DIR__.'/registry.php';
		Registry::build($this);
	}

	/**
	* Загрузить указанный класс.
	* Функция автозагрузчика (см. dl\Boot::start()).
	* class - имя класса, интерфейса, трейта или перечисления.
	*/
	public function load(string $class): void {
		if (isset($this->data[$this->manifest][$class])) {
			if ($this->isClass($class)) {
				// Класс успешно загружен из текущей ветки реестра
				return;
			}

			$this->data[$this->manifest] = [];
			$this->save[$this->manifest] ??= $this->branch;
		}

		if (!isset($this->data[self::REGISTRY])) {
			if (\is_readable($this->regdir.self::REGISTRY)) {
				$this->data[self::REGISTRY] = include $this->regdir.self::REGISTRY;

				if (!\is_array($this->data[self::REGISTRY])) {
					$this->data[self::REGISTRY] = [];
				}
			}
			else {
				$this->data[self::REGISTRY] = [];
			}
		}

		if ($this->isRegistered($class)) {
			// Класс успешно загружен после подключения к главной ветке реестра
			return;
		}

		if (\in_array(self::REGISTRY, $this->save)) {
			// Класс до сих пор не загружен
			// и нет возможности перезагрузки реестра.
			$this->errorLoad($class);
			return;
		}

		$this->reboot();

		if ($this->isRegistered($class)) {
			// После обновления реестра класс успешно загружен.
			return;
		}

		$this->errorLoad($class);
	}

	/**
	* Выбросить исключение.
	* class - имя ненайденного класса, интерфейса, трейта или перечисления.
	*/
	private function errorLoad(string $class): never {
		throw new Failure(
			Error::log(Core::message('e_load', $class), Code::Noclass, true)
		);
	}

	/**
	* Смена ветки реестра.
	* Вызывается из dl\Boot::branch().
	* name - строка с условным наименованием ветки
	*/
	public function changeManifest(string $name): void {
		$this->branch = $name;
		$this->manifest = \md5($name).'.php';

		if (\is_readable($this->regdir.$this->manifest)) {
			$this->data[$this->manifest] = include $this->regdir.$this->manifest;

			if (!\is_array($this->data[$this->manifest])) {
				$this->data[$this->manifest] = [];
			}
		}
		else {
			$this->data[$this->manifest] = [];
		}
	}

	/**
	* Подключить все файлы перечисленные в ветке
	* Вызывается из dl\Boot::branch().
	*/
	public function includeManifest(): void {
		foreach ($this->data[$this->manifest] as $manifest) {
			if (\file_exists($manifest)) {
				include_once $manifest;
			}
		}
	}

	/**
	* Вернуть полный путь к файлу указанного класса или пустую строку,
	* если класс не загружен.
	* Вызывается из dl\Boot::find().
	* class - имя искомого класса, интерфейса, трейта или перечисления.
	* remake - флаг перегрузки реестра
	*/
	public function getClassPath(string $class, bool $remake): string {
		if (isset($this->data[$this->manifest][$class]) && \file_exists($this->data[$this->manifest][$class])) {
			return $this->data[$this->manifest][$class];
		}

		if (!isset($this->data[self::REGISTRY])) {
			if (\is_readable($this->regdir.self::REGISTRY)) {
				$this->data[self::REGISTRY] = include $this->regdir.self::REGISTRY;

				if (!\is_array($this->data[self::REGISTRY])) {
					$this->data[self::REGISTRY] = [];
				}
			}
			else {
				$this->data[self::REGISTRY] = [];

				if (!$remake && !isset($this->save[self::REGISTRY])) {
					$remake = true;
				}
			}
		}

		if (isset($this->data[self::REGISTRY][$class]) && \file_exists($this->data[self::REGISTRY][$class])) {
			return $this->data[self::REGISTRY][$class];
		}

		if (isset($this->save[self::REGISTRY])) {
			return '';
		}

		if (!$remake) {
			return '';
		}

		$this->reboot();

		if (isset($this->data[self::REGISTRY][$class])) {
			return $this->data[self::REGISTRY][$class];
		}

		return '';
	}
}

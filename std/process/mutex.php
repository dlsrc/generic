<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    abstract class dl\Mutex

	Мьютексы на основе эсклюзивно захваченных семафорах, когда \SyncMutex
	неприменимы.

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

abstract class Mutex {
	/**
	* Создать экземпляр мьютекса (см. dl\Mutex::__construct())
	*/
	abstract protected function create(): void;

	/**
	* Захватить семафор.
	* Вернёт TRUE в случае успеха или FALSE, если семафор уже занят.
	* blocking - флаг блокировки исполнения до захвата семафора текущим процессом.
	* Эмуляция поведения функции sem_acquire():
	* Если флаг установлен в TRUE, процесс будет дожидаться возможности захватить семафор,
	* в противном случае сразу вернется FALSE.
	*/
	abstract public function acquire(bool $blocking=false): bool;

	/**
	* Освободить семафор.
	*/
	abstract public function release(): bool;

	/**
	* Удалить семафор
	*/
	abstract public function ​remove(): bool;

	/**
	* Пул семафоров
	*/
	private static array $semaphore = [];

	/**
	* Идентификатор семафора
	*/
	protected int $key;

	/**
	* Объект экземпляр или имя файла семафора
	*/
	protected \SysvSemaphore|\Shmop|string|false $sem;

	/**
	* Флаг состояния мьютекса относительно текущего процесса
	* TRUE  - мьютекс эксклюзивно захвачн текущим процессом
	* FALSE - мьютекс свободен или захвачен другим процессом
	*/
	protected bool $status;

	/**
	* Получение объекта семафора на основании файлового пути и идентификатора проекта.
	*/
	public static function make(string $filename, string $project_id, bool $danger = false): static|Error {
		if (!\file_exists($filename)) {
			return Error::log(IO::message('e_file', $filename), IOCode::Nofile);
		}

		$key = \ftok($filename, $project_id);

		if (-1 == $key) {
			return Error::log(Core::message('e_ftok'), Code::User);
		}

		if (\extension_loaded('sysvsem')) {
			return SysVSemMutex::get($key);
		}
		elseif (\extension_loaded('shmop')) {
			return ShmopMutex::get($key);
		}
		else {
			$mtx = FileMutex::get($key);
			$mtx->setpath(\dirname(__DIR__, 3));

			if ($danger) {
				if (!$mtx->pathExists()) {
					$mtx->setpath(\dirname(__DIR__, 3), true);

					if (!$mtx->pathExists()) {
						return Error::log(Core::message('e_ext', 'process (sysvsem, shmop)'), Code::Ext);
					}
				}
			}

			return $mtx;
		}
	}

	/**
	* Получение объекта семафора по ключу System V IPC.
	*/
	public static function get(int $key): static {
		self::$semaphore[$key] ??= new static($key);
		return self::$semaphore[$key];
	}

	/**
	* Удаление объекта семафора из пула
	*/
	protected static function drop(int $key) {
		unset(self::$semaphore[$key]);
	}

	/**
	* Защищенный конструктор.
	*/
	protected function __construct(int $key) {
		$this->key = $key;
		$this->create();
	}

	/**
	* Освобождение семафора при окончании работы процесса
	*/
	public function __destruct() {
		$this->release();
	}

	/**
	* Семафор захвачен текущим процессом
	*/
	public function isAcquire(): bool {
		return $this->status;
	}
}

<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    final class dl\ShmopMutex

	Эмулятор мьютекса на основе доступа к сегментам разделяемой памяти.
	Использовать, если недоступны функции межпроцессного взаимодействия
	System V.

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

final class ShmopMutex extends Mutex {
	protected function create(): void {
		$this->status = false;

		if (!$this->sem = \shmop_open($this->key, 'c', 0666, 1)) {
			return;
		}

		$data = \shmop_read($this->sem, 0, 1);

		if ('a' != $data && 'f' != $data) {
			if (1 == \shmop_write($this->sem, 'a', 0)) {
				$this->status = true;
			}
		}
	}

	public function acquire(bool $blocking=false): bool {
		if ($this->status) {
			return true;
		}

		if (!$this->sem) {
			return false;
		}

		if ($blocking) {
			\set_time_limit(0);

			while ('a' == \shmop_read($this->sem, 0, 1)) {
				\sleep(1);
			}

			if (1 == \shmop_write($this->sem, 'a', 0)) {
				$this->status = true;
			}
		}
		elseif ('f' == \shmop_read($this->sem, 0, 1)) {
			if (1 == \shmop_write($this->sem, 'a', 0)) {
				$this->status = true;
			}
		}

		return $this->status;
	}

	public function release(): bool {
		if ($this->status) {
			if (1 == \shmop_write($this->sem, 'f', 0)) {
				$this->status = false;
				return true;
			}
		}

		return false;
	}

	public function ​remove(): bool {
		if ($this->status) {
			if (\shmop_delete($this->sem)) {
				$this->status = false;
				self::drop($this->key);
				return true;
			}
		}

		return false;
	}
}

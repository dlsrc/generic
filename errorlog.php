<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    final class dl\ErrorLog

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

final class ErrorLog implements Storable {
	use Filename;

	private array $_log;
	private array $_new;

	public function __construct(string $file) {
		$this->_file = '';
		$this->_log  = [];
		$this->_new  = [];

		$this->setFilename($file);

		if ('' != $this->_file && \file_exists($this->_file)) {
			$log = @include $this->_file;

			if (\is_array($log) && !empty($log) && $this->isErrorObject(\reset($log))) {
				$this->_log = $log;
			}
		}
	}

	public function __destruct() {
		if (empty($this->_new) || '' == $this->_file) {
			return;
		}

		$mode = Mode::now(Mode::Develop);

		(new Exporter($this->_file))->save(
			$this->_log,
			Core::message(
				'src_header',
				'Error log '.\date('Y-m-d H:i:s'),
				\date('Y'),
				\PHP_MAJOR_VERSION.'.'.\PHP_MINOR_VERSION
			)
		);

		Mode::now($mode);
	}

	public function prepare(array $log): void {
		if ('' == $this->_file) {
			return;
		}

		foreach ($log as $error) {
			if ($this->isErrorObject($error)) {
				$this->add($error);
			}
		}
	}

	public function add(Error $e): void {
		if (!isset($this->_log[$e->id])) {
			$this->_log[$e->id] = $e;
			$this->_new[]       = $e->id;
		}
	}

	private function isErrorObject(mixed $var): bool {
		if (\is_object($var) && \get_class($var) == 'dl\\Error') {
			return true;
		}

		return false;
	}
}

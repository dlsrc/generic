<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |        Dmitry N. Lebedeff        / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |           <dl@adios.ru>         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____                            / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/          (C)2021          /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    interface dl\Storable
	trait dl\Filename

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

// Storable container
interface Storable {
	public function getFilename(): string;
	public function setFilename(string $file): void;
}

// Storable implementation
trait Filename {
	private string $_file;

	public function getFilename(): string {
		return $this->_file;
	}

	public function setFilename(string $file): void {
		if ('' != $file && IO::indir($file)) {
			$this->_file = \strtr(\realpath(\dirname($file)).'/'.\basename($file), '\\', '/');
		}
	}
}

<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    final class dl\en\IO

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl\en;

final class IO extends \dl\Getter {
	protected function initialize(): void {
		$this->_property['e_chmod']     = 'Failed to change file access mode "{0}".';
		$this->_property['e_copy']      = 'Error copying file "{0}" from "{1}" to "{2}".';
		$this->_property['e_dir']       = 'Directory "{0}" does not exist.';
		$this->_property['e_file']      = 'File "{0}" does not exist.';
		$this->_property['e_make_dir']  = 'Error creating directory "{0}".';
		$this->_property['e_make_file'] = 'Unable to create file "{0}". Perhaps this is due to the rights to the folder in which the file is created.';
		$this->_property['e_rename']    = 'Error transferring file "{0}" to "{1}".';
		$this->_property['e_rmdir']     = 'Unable to delete directory "{0}".';
		$this->_property['e_unlink']    = 'Unable to delete file "{0}".';
	}
}

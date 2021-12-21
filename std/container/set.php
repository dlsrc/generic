<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    abstract class dl\Set

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

abstract class Set implements Mutable, Exportable, NamelessImportable {
	use NamelessContainer;
	use PropertySetter;
	use OwnExport;

	protected function __construct(array $state = []) {
		$this->_save = Save::Nothing;

		if (empty($state)) {
			$this->_file = '';
			$this->initialize();
		}
		else {
			$this->_file = $state['_file'];
			$this->_property = $state['_property'];
		}
	}
}

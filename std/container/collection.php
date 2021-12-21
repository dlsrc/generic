<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    abstract class dl\Collection

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

abstract class Collection implements Immutable, Extendable, Exportable, ImportableNamed {
	use ContainerName;
	use NamedContainer;
	use PropertyGetter;
	use PropertyCollector;
	use OwnExport;

	protected function __construct(array $state = [], string $name = '') {
		$this->_save = Save::Nothing;

		if (empty($state)) {
			if ('' == $name) {
				$this->_name = \get_class($this);
			}
			else {
				$this->_name = $name;
			}

			$this->_file = '';
			$this->initialize();
		}
		else {
			$this->_name = $state['_name'];
			$this->_file = $state['_file'];

			foreach ($state as $name => $value) {
				if (\property_exists($this, $name)) {
					$this->$name = $value;
				}
			}
		}
	}
}

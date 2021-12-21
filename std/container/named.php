<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    interface dl\Named
	trait dl\ContainerName
	trait dl\NamedContainerGetter
    interface dl\ImportableNamed
	trait dl\NamedContainer

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

// Named container
interface Named {
	public function getName(): string;
}

// Named implementation
trait ContainerName {
	private string $_name;

	public function getName(): string {
		return $this->_name;
	}
}

trait NamedContainerGetter {
	use PropertyContainer;

	final public static function get(string $name = ''): self {
		if ('' == $name) {
			$name = static::class;
		}

		if (!isset(self::$_container[$name])) {
			if (\is_subclass_of(static::class, 'dl\\Named')) {
				self::$_container[$name] = new static([], $name);
			}
			else {
				self::$_container[$name] = new static;
			}
		}

		return self::$_container[$name];
	}
}

// ImportableNamed container
interface ImportableNamed {
	public static function load(string $file, string $name = ''): self;
	public static function find(string $file, string $name = ''): self|null;
}

// ImportableNamed implementation
trait NamedContainer {
	use NamedContainerGetter;
	
	final public static function load(string $file, string $name = ''): self {
		if ('' == $name) {
			$name = static::class;
		}

		if (isset(self::$_container[$name])) {
			return self::$_container[$name];
		}

		if (\is_readable($file)) {
			self::$_container[$name] = @include $file;

			if (self::$_container[$name] instanceof static) {
				return self::$_container[$name];
			}

			if (!\is_object(self::$_container[$name])) {
				\unlink($file);
				Error::log(
					Core::message('e_type', $file, \gettype(self::$_container[$name])),
					Error::DOMAIN
				);
			}
			else {
				\unlink($file);
				Error::log(
					Core::message('e_class', $file, \get_class(self::$_container[$name]), static::class),
					Error::DOMAIN
				);
			}
		}

		self::$_container[$name] = new static([], $name);

		if (self::$_container[$name] instanceof Storable) {
			self::$_container[$name]->setFilename($file);

			if (self::$_container[$name] instanceof Exportable) {
				self::$_container[$name]->save(Save::NoError);
			}
		}

		return self::$_container[$name];
	}

	final public static function find(string $file, string $name = ''): self|null {
		if ('' == $name) {
			$name = static::class;
		}

		if (isset(self::$_container[$name])) {
			return self::$_container[$name];
		}

		if (!\is_readable($file)) {
			return null;
		}

		self::$_container[$name] = @include $file;

		if (self::$_container[$name] instanceof static) {
			return self::$_container[$name];
		}

		unset(self::$_container[$name]);
		return null;
	}
}

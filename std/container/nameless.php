<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

	trait dl\NamelessContainerGetter
    interface dl\NamelessImportable
	trait dl\NamelessContainer

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

trait NamelessContainerGetter {
	use PropertyContainer;

	final public static function get(): self {
		self::$_container[static::class] ??= new static;
		return self::$_container[static::class];
	}
}

// NamelessImportable container
interface NamelessImportable {
	public static function load(string $file): self;
	public static function find(string $file): self|null;
}

// NamelessImportable implementation
trait NamelessContainer {
	use NamelessContainerGetter;

	final public static function load(string $file): self {
		if (isset(self::$_container[static::class])) {
			return self::$_container[static::class];
		}

		if (\is_readable($file)) {
			self::$_container[static::class] = @include $file;

			if (self::$_container[static::class] instanceof static) {
				return self::$_container[static::class];
			}

			if (!\is_object(self::$_container[static::class])) {
				\unlink($file);
				Error::log(
					Core::message('e_type', $file, \gettype(self::$_container[static::class])),
					Code::Domain
				);
			}
			else {
				\unlink($file);
				Error::log(
					Core::message('e_class', $file, \get_class(self::$_container[static::class]), static::class),
					Code::Domain
				);
			}
		}

		self::$_container[static::class] = new static;

		if (self::$_container[static::class] instanceof Storable) {
			self::$_container[static::class]->setFilename($file);

			if (self::$_container[static::class] instanceof Exportable) {
				self::$_container[static::class]->save(Save::NoError);
			}
		}

		return self::$_container[static::class];
	}

	final public static function find(string $file): self|null {
		if (isset(self::$_container[static::class])) {
			return self::$_container[static::class];
		}

		if (!\is_readable($file)) {
			return null;
		}

		self::$_container[static::class] = @include $file;

		if (self::$_container[static::class] instanceof static) {
			return self::$_container[static::class];
		}

		unset(self::$_container[static::class]);
		return null;
	}
}
<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |        Dmitry N. Lebedeff        / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |           <dl@adios.ru>         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____                            / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/          (C)2021          /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    interface dl\Exportable

	Интерфейс объектов способных экспортировать себя в файл
	как возвращаемый PHP код.
	(дополнительно можно посмотреть описание для класса dl\Exporter).


	trait dl\OwnExport

	Обобщенная реализация интерфейса dl\Exportable.

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

// Флаг состояния экспортируемого объекта, варианты
// обозначающие степень необходимости сохранить себя.
enum Save {
	// Сохранить объект немедленно.
	case Now;

	// Сохранить объект перед уничтожением в любом случае.
	case Destruct;

	// Сохранить объект перед уничтожением,
	// если в процессе выполнения не регистрировались ошибки.
	case NoError;

	// Сохранять объект не нужно.
	case Nothing;
}

interface Exportable extends Storable, CallableState {
	// Класс, реализующий интерфейс dl\Exportable, должен
	// включать приватное свойство несущее одно из значений dl\Save.

	// Стандартный метод, в котором нужно реализовать экспорт,
	// на основе соответствия значения, указанного выше приватного свойства,
	// флагам Save::NoError и Save::Destruct.

	public function __destruct();

	// Экспортировать себя в указанный файл.
	//
	// file - полное имя файла, в который выполняется экспорт объекта.
	//        По умолчанию пустая строка, экспортировать в файл
	//        вычисленный при создании объекта.
	// save - флаг типа dl\Save, означающий как и когда выполняется експорт.
	//        Необходимо передать одно из значений:
	//        Save::NoError, Save::Destruct или Save::Now.
	//        По умолчанию Save::NoError.
	//
	// Класс, реализующий интерфейс dl\Exportable, должен самостоятельно
	// вычислять файл по умолчанию для сохранения объектов на его основе.
	// В большинстве случаев, достаточно задействовать трейт dl\Filename,
	// реализующий интерфейс dl\Storable.

	public function export(string $file = '', Save $save = Save::NoError): void;

	// Сохранить себя в файл по умолчанию.
	//
	// save - флаг типа dl\Save, означающий как и когда выполняется експорт.
	//        Необходимо передать одно из значений:
	//        Save::NoError, Save::Destruct или Save::Now.
	//        По умолчанию Save::NoError.
	//
	// Файл по умолчанию должен определяется реализацией конкретного класса.

	public function save(Save $save = Save::NoError): void;

	// Обновить экспортную копию объекта.
	// Используется, если список свойств в контейнере изменился.
	//
	// save - флаг типа dl\Save, означающий как и когда выполняется експорт.
	//        Необходимо передать одно из значений:
	//        Save::NoError, Save::Destruct или Save::Now.
	//        По умолчанию Save::Now.
	//
	// Файл по умолчанию должен определяется реализацией конкретного класса.

	public function update(Save $save = Save::Now): self;
}

// Реализация dl\Exportable
trait OwnExport {
	use Filename;
	use SetStateCall;

	// Флаг состояния объекта, указывающий на необходимость экспорта.
	//
	// Принимает одно из значений:
	//
	// dl\Save::Nothing,
	// dl\Save::NoError,
	// dl\Save::Destruct,
	// dl\Save::Now.

	private Save $_save;

	final public function __destruct() {
		if (Save::Destruct == $this->_save) {
			$this->save(Save::Now);
		}
		elseif (Save::NoError == $this->_save && !Error::exists()) {
			$this->save(Save::Now);
		}
	}

	final public function export(string $file = '', Save $save = Save::NoError): void {
		$this->setFilename($file);
		$this->save($save);
	}

	final public function status(): Save {
		return $this->_save;
	}

	final public function save(Save $save = Save::NoError): void {
		if (Save::Now == $save) {
			$this->_save = Save::Nothing;

			if ('' != $this->_file) {
				(new Exporter($this->_file))->save(
					$this,
					Core::message(
						'src_header',
						$this->_file,
						\date('Y'),
						\PHP_MAJOR_VERSION.'.'.\PHP_MINOR_VERSION
					)
				);
			}
		}
		else {
			$this->_save = $save;
		}
	}

	final public function update(Save $save = Save::Now): self {
		$type = \get_class($this);

		if ($this instanceof Named) {
			$name = $this->getName();
			$up = new $type([], $name);
		}
		else {
			$name = $type;
			$up = new $type;
		}

		$up->setFilename($this->getFilename());

		foreach ($this->_property as $key => $val) {
			$up->$key = $val;
		}

		$up->save($save);
		self::add($up, $name);
		return $up;
	}
}

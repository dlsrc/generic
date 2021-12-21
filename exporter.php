<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    final class dl\Exporter

	Создает файл и помещает в него исходный код PHP.

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

final class Exporter implements Storable {
	use Filename;

	public function __construct(string $file = '') {
		$this->_file = $file;
	}

	/**
	* Сохраняет переменную в файл как код возврата этой переменной.
	*
	* <?php return (mixed) $variable;
	* 
	* Код, помещенный в файл, возвращает значение экспортированной переменной
	* и может быть использован при включении файла оператором include:
	*
	* $var = include 'filename.php';
	*
	* Переменная может быть любого типа, кроме (resource).
	* Если необходимо в интерпретируемом коде возвращать объекты через оператор
	* new, не реализуя метод __set_state(), классы таких объектов должены
	* реализовать интерфейс dl\DirectCallable.
	*
	* code   - переменная PHP;
	* header - строка с заголовочным комментарием;
	* strict - флаг, указывающий что сохраняемый код будет использоваться в режиме строгого соответствия;
	*/
	public function save(mixed $variable, string $header = '', bool $strict = true): bool {
		if ('' == $this->_file) {
			return false;
		}

		if (!IO::indir($this->_file)) {
			return false;
		}

		if (IO::fw($this->_file, $this->_makeCode($variable, $header, $strict)) < 0) {
			return false;
		}

		return true;
	}

	/**
	* Сохраняет исходный код PHP в файл.
	*
	* code   - строка, являющаяся валидным исходным кодом PHP;
	* header - строка с заголовочным комментарием;
	* strict - флаг, указывающий что сохраняемый код будет использоваться в режиме строгого соответствия;
	*/
	public function put(string $code, string $header = '', bool $strict = true): bool {
		if ('' == $this->_file) {
			return false;
		}

		if (!IO::indir($this->_file)) {
			return false;
		}

		if (\str_contains($code, 'declare(strict_types=1);')) {
			$strict = false;
		}

		if (IO::fw($this->_file, $this->_prepareCode($code, $header, $strict)) < 0) {
			return false;
		}

		return true;
	}

	/**
	* Подготовка строки комментария для заголовка файла
	*/
	private function _prepareHeader(string $header): string {
		if (!\str_starts_with($header, '/*')) {
			$header = '/*'.\PHP_EOL.$header;
		}

		if (!\str_ends_with($header, '*/')) {
			$header = $header.\PHP_EOL.'*/';
		}

		return $header;
	}

	/**
	* Подготовить переменную к сохранению
	*/
	private function _makeCode(mixed $variable, string $header, bool $strict): string {
		if (Mode::Product->current() || '' == $header) {
			return '<?php'.\PHP_EOL.$this->_declare($strict).
			'return '.$this->_optimize(\var_export($variable, true), $this->_storable($variable)).';'.
			\PHP_EOL;
		}

		return '<?php'.\PHP_EOL.
		$this->_prepareHeader($header).\PHP_EOL.$this->_declare($strict).
		'return '.$this->_optimize(\var_export($variable, true), $this->_storable($variable)).';'.
		\PHP_EOL;
	}

	/**
	* Подготовить исходный код к сохранению
	*/
	private function _prepareCode(string $code, string $header, bool $strict): string {
		if (\str_starts_with($code, '<?php')) {
			return $code;
		}
		
		if (Mode::Product->current() || '' == $header) {
			return '<?php'.\PHP_EOL.$this->_declare($strict).$code.\PHP_EOL;
		}

		return '<?php'.\PHP_EOL.$this->_prepareHeader($header).\PHP_EOL.$this->_declare($strict).$code.\PHP_EOL;
	}

	/**
	* Включить для сохраняемого исходного кода режим строгой типизации
	*/
	private function _declare(bool $strict): string {
		if ($strict) {
			return 'declare(strict_types=1);'.\PHP_EOL;
		}

		return '';
	}

	/**
	* Оптимизировать исходный код перед сохранением
	*/
	private function _optimize(string $code, bool $storable): string {
		$seek = '/([\w\\\\]+)\:\:__set_state/is';
		$class = [];

		if (\preg_match_all($seek, $code, $match)) {
			$match = \array_unique($match[1]);

			foreach ($match as $name) {
				if (\is_subclass_of($name, 'dl\\DirectCallable')) {
					$class[] = $name;
				}
			}
		}

		if (!empty($class)) {
			$seek = '/('.\implode('|', \array_map(fn(string $text) => \preg_quote($text), $class)).')\:\:__set_state/is';
			$code = \preg_replace($seek, 'new $1', $code);
		}

		if ($storable) {
			$code = \preg_replace(
				'/\'_file\'\s*\=>\s*\'[^\']+\'\,/', '\'_file\' => __FILE__,', $code
			);
		}

		if (Mode::Develop->current()) {
			return $code;
		}

		return \preg_replace(
			['/\s+\=\>\s+/', '/\s*\(\n\s+/', '/\,\n\s*\)/', '/\,\n\s+/'],
			['=>', '(', ')', ','],
			$code
		);
	}
	
	/**
	* Выявляет интерфейс в экспортируемой переменной dl\Storable
	*/
	private function _storable(mixed $variable): bool {
		if (\is_object($variable) && ($variable instanceof Storable)) {
			return true;
		}

		return false;
	}
}

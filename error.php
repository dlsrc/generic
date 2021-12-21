<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

	final class dl\Error

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

final class Error implements CallableState {
	use SetStateCall;

	/**
	* Основные коды ошибок.
	* Остальные задаются в соответствующих библиотеках.
	*/
	public const SUCCESS  = 0;  // Ошибки отсутствуют.
	public const ERROR    = 1;  // Фатальная ошибка
	public const WARNING  = 2;  // Общая нефатальная ошибка
	public const FATAL    = 3;  // Фатальная пользовательская ошибка
	public const PARSE    = 4;  // Ошибка парсера
	public const USER     = 5;  // Пользовательская ошибка
	public const LOGIC    = 6;  // Ошибка в логике программы
	public const BADCALL  = 7;  // Вызов неправильной (неизвестной) функции или метода
	public const NOTICE   = 8;  // Предупреждение
	public const DOMAIN   = 9;  // Неверная область использования значения
	public const ARGUMENT = 10; // Неверный аргумент
	public const LENGTH   = 11; // Превышение допустимой длины
	public const RANGE    = 12; // Запрос несуществующего индекса
	public const NET      = 13; // Ошибка сети
	public const TCP      = 14; // Ошибка tcp-подключения
	public const MODE     = 15; // Попытка запуска программы в неверном режиме
	public const NOCLASS  = 17; // Описание класса, интерфейса, перечисления отсутствует
	public const FILENAME = 18; // Неверное имя файла
	public const MAKEDIR  = 19; // Ошибка при создании директории
	public const MAKEFILE = 20; // Ошибка при создании файла
	public const COPY     = 21; // Ошибка при копировании файла
	public const RENAME   = 22; // Ошибка при переименовании файла
	public const NODIR    = 23; // Директория не существует
	public const NOFILE   = 24; // Файл не существует
	public const RMDIR    = 25; // Не удалось удалить директорию
	public const UNLINK   = 26; // Не удалось удалить файл
	public const CHMOD    = 27; // Ошибка при изменении режима доступа к папкам и файлам
	public const SENDMAIL = 28; // Ошибка при передаче письма на отправку
	public const NOOBJECT = 29; // Ошибка при инстанцировании объекта
	public const SYSTEM   = 30; // Неправильные пастройки среды исполнения
	public const EXT      = 31; // Не загружено необходимое расширение

	/**
	* Список объектов dl\Error.
	* Организован как мультитон - ошибки повторно не регистрируются
	* Ключи массива соответствуют идентификатору ошибки,
	* значения массива - объекты dl\Error,
	* соответствующие заданному идентификатору ошибки (см. dl\Error->id).
	*/
	private static array $_log = [];

	/**
	* Завершающая функция.
	* [class, method]
	*/
	private static array $_shutdown = [];

	/**
	* Файл в котором логируются объекты dl\Error
	* см. class dl\ErrorLog
	*/
	private static string $_file = '';

	/**
	* Флаг включения
	*/
	private static bool $_listen = false;

	/**
	* Флаг произошедшей пользовательской ошибки,
	* отнесенной к разряду фатальных.
	*/
	private static bool $_fatal = false;

	/**
	* Рандомизатор - отвечает за частоту логирования ошибок
	*/
	private static int $_rand = 1;
	
	/**
	* Дата первого появления ошибки в формате "YYYY-mm-dd HH:ii:ss".
	*/
	public readonly string $date;

	/**
	* Идентификатор ошибки на основе её сигнатуры
	*/
	public readonly string $id;

	/**
	* Сообщение об ошибке.
	*/
	public readonly string $message; 

	/**
	* Описание контекста возникновения ошибки.
	*/
	public readonly string $context;

	/**
	* Файл в котором произошла ошибка или выброшено исключение.
	*/
	public readonly string $file;

	/**
	* Строка файла в котором произошла ошибка или выброшено исключение.
	*/
	public readonly int $line;

	/**
	* Код ошибки
	*/
	public readonly int $code;

	/**
	* Тип ошибки
	*/
	public readonly int $type;

	/**
	* Отметка времени последнего появления ошибки
	*/
	public readonly int $time;

	/**
	* Проверить наличие зарегистрированных ошибок
	*/
	public static function exists(): bool {
		return !empty(self::$_log);
	}

	/**
	* Волучить идентификаторы всех зарегистрированных ошибок
	*/
	public static function keys(): array {
		return \array_keys(self::$_log);
	}

	/**
	* Получить объект ошибки по её идентификатору
	*/
	public static function open(string $key): self|null {
		if (isset(self::$_log[$key])) {
			return self::$_log[$key];
		}

		return null;
	}

	/**
	* Экспортировать список объектов ошибок в файл
	* Если флаг reset установлен в значении TRUE,
	* то список ошибок будет очищен.
	* см. dl\ErrorLog::prepare()
	*/
	public static function dump(bool $reset = false): bool {
		if (empty(self::$_log)) {
			return false;
		}

		if ('' != self::$_file && (1 == self::$_rand || \mt_rand(1, self::$_rand) == self::$_rand || Mode::Develop->current())) {
			(new ErrorLog(self::$_file))->prepare(self::$_log);
		}

		if ($reset) {
			self::$_log = [];
		}

		return true;
	}

	/**
	* Встать на прослушивание и обработку ошибок.
	* Чтобы зарегистрировать ошибку, которая могла произойти до вызова
	* dl\Error::listen() нужно установить параметр last в значении TRUE
	* см. dl\Error::last()
	*/
	public static function listen(array $shutdown = [], string $logfile = '', int $random = 1, bool $last = false): void {
		if (self::$_listen) {
			return;
		}

		self::$_listen = true;
		\ini_set('display_errors', 'off');
		\set_error_handler([__CLASS__, 'error_handler']);
		\set_exception_handler([__CLASS__, 'exception_handler']);
		\register_shutdown_function([__CLASS__, 'listener']);

		self::shutdown($shutdown);
		self::report($logfile);
		self::ratio($random);

		if ($last) {
			self::last();
		}
	}

	/**
	* Callback код, который выполнится после завершения работы скрипта
	* или при вызове функции exit(), но только в том случае,
	* если до этого было активировано прослушивание и обработка ошибок.
	* см. dl\Error::listen()
	* Если задана корректная (callable) завершающая функция в dl\Error::$_shutdown,
	* она будет выполнена при наличии зарегистрированных фатальных ошибок
	* или в особом режиме разработки dl\Mode::Error при наличии любых ошибок.
	* см. dl\Error::shutdown()
	*/
	public static function listener(): void {
		if (!self::dump()) {
			return;
		}

		if (empty(self::$_shutdown)) {
			return;
		}

		if ((self::$_fatal || Mode::Error->current()) && \is_callable(self::$_shutdown)) {
			\call_user_func(self::$_shutdown, self::$_log);
		}
	}

	/**
	* Установить завершающую функцию.
	* Возвращает предыдущую завершающую функцию.
	*/
	public static function shutdown(array $shutdown = []): array {
		$old = self::$_shutdown;
		self::$_shutdown = $shutdown;
		return $old;
	}

	/**
	* Установить параметр частоты сброса данных об ошибках в лог.
	* rand - положительное число, отвечает за рандомизацию частоты записи в лог.
	* Чем больше значение параметра, тем реже происходит запись.
	* 1 - запись происходит каждый раз при возникновении ошибки.
	* На продакшене не имеет смысла ставить значение rand
	* меньше чем среднее количество посетителей в 10 минут.
	*/
	public static function ratio(int $rand): void {
		if ($rand > 0) {
			self::$_rand = $rand;
		}
	}

	/**
	* Установить файл лога
	* file - путь к файлу лога
	*/
	public static function report(string $logfile): void {
		if ('' != $logfile && IO::indir($logfile)) {
			self::$_file = $logfile;
		}
	}

	/**
	* Вывести все сообщения об ошибках
	*/
	public static function display(): void {
		if ('cli' == \PHP_SAPI) {
			$fn_display = fn(\dl\Error $e): string => $e->getErrorMessage().\PHP_EOL;
		}
		else {
			$fn_display = fn(\dl\Error $e): string => '<p>'.$e->getErrorMessage()."</p>\n";
		}
		
		foreach (self::$_log as $error) {
			echo $fn_display($error);
		}
	}

	/**
	* Зарегистрировать новый объект ошибки в журнале и вернуть его.
	* mesg  - сообшение об ошибке
	* code  - Код ошибки
	* fatal - для пользовательских ошибок флаг фатальной ошибки,
	* установить в TRUE, если при возникновении ошибки,
	* согласно логике программы, дальнейшее её выполнение бессмысленно.
	*/
	public static function log(string $mesg, int $type = self::USER, bool $fatal = false): self {
		$trace = \debug_backtrace();
		$debug = [];

		foreach (\array_keys($trace) as $i) {
			if (isset($trace[$i]['file']) && isset($trace[$i]['line'])) {
				$debug[] = $trace[$i];
			}
		}

		$trace = [
			'type'    => $type,
			'fatal'   => $fatal,
			'message' => $mesg,
			'file'    => $debug[0]['file'],
			'line'    => $debug[0]['line'],
			'context' => '[ThrownByUser]',
		];

		$trace['id'] = self::makeId($trace);

		if (isset(self::$_log[$trace['id']])) {
			return self::$_log[$trace['id']];
		}

		if (isset($debug[1])) {
			$trace['context'].= ' '.$debug[1]['class'].$debug[1]['type'].$debug[1]['function'];
		}

		$info = Core::info();
		$message = $info->w_trace.':'.\PHP_EOL;

		foreach (\array_keys($debug) as $i) {
			$message = $message.
			$info->w_file.': "'.$debug[$i]['file'].'", '.
			$info->w_line.': "'.$debug[$i]['line'].'"';

			if ($debug[$i]['class'] && $debug[$i]['type'] && $debug[$i]['function']) {
				$message = $message.', '.
				$info->w_invoker.': "'.$debug[$i]['class'].$debug[$i]['type'].$debug[$i]['function'].'"';
			}
			elseif ($debug[$i]['function']) {
				$message = $message.', '.
				$info->w_invoker.': "'.$debug[$i]['function'].'"';
			}

			$next = $i + 1;

			if (!isset($debug[$next])) {
				$message = $message.', '.
				$info->w_context.': GLOBAL';
			}
			elseif ($debug[$next]['class'] && $debug[$next]['type'] && $debug[$next]['function']) {
				$message = $message.', '.
				$info->w_context.': "'.$debug[$next]['class'].$debug[$next]['type'].$debug[$next]['function'].'"';
			}
			elseif ($debug[$next]['function']) {
				$message = $message.', '.
				$info->w_context.': "'.$debug[$next]['function'].'"';
			}

			$message = $message.\PHP_EOL;
		}

		$trace['message'] = $mesg.\PHP_EOL.$message;

		self::$_log[$trace['id']] = new Error($trace);

		self::fatal(self::$_log[$trace['id']]);

		return self::$_log[$trace['id']];
	}

	/**
	* Выбросить исключение, если есть зарегистрированные ошибки.
	* При вызове без параметров,
	* исключение выбрасывается для последней ошибки в журнале.
	* Если переданы коды(код) ошибок, то исключение будет брошено
	* для первой совпавшей с одним из кодов ошибки.
	* Если ошибки есть, но ни одна не совпала ни с одним из кодов,
	* исключение выброшено не будет.
	*/
	public static function halt(int ...$code): void {
		if (empty(self::$_log)) {
			return;
		}

		if (empty($code)) {
			$id = \array_key_last(self::$_log);
			throw new Failure(self::$_log[$id]);		
		}

		foreach (self::$_log as $e) {
			if (\in_array($e->code, $code)) {
				throw new Failure($e);
			}
		}
	}

	/**
	* Обработчик ошибок
	*/
	public static function error_handler(int $errno, string $errstr, string $errfile, int $errline): void {
		$trace = [
			'type'    => $errno,
			'fatal'   => true,
			'message' => $errstr,
			'file'    => $errfile,
			'line'    => $errline,
			'context' => '[ErrorHandler]',
		];

		$trace['id'] = self::makeId($trace);

		if (!isset(self::$_log[$trace['id']])) {
			self::$_log[$trace['id']] = new Error($trace);
			self::fatal(self::$_log[$trace['id']]);
		}
	}

	/**
	* Обработчик исключений
	*/
	public static function exception_handler(\Throwable $e): void {
		$code = $e->getCode();

		if ($e instanceof Failure) {
			if (\in_array($code, [self::WARNING, self::NOTICE, self::USER,])) {
				return;
			}

			exit;
		}

		$trace = [
			'type'    => $code,
			'fatal'   => true,
			'message' => $e->getMessage(),
			'file'    => $e->getFile(),
			'line'    => $e->getLine(),
		];

		$trace['id'] = self::makeId($trace);

		if (isset(self::$_log[$trace['id']])) {
			return;
		}

		if (!$code) {
			$trace['code'] = match ($e::class) {
				'ArgumentCountError', 'ArithmeticError', 'DivisionByZeroError',
				'Error', 'TypeError', 'UnhandledMatchError', 'ValueError' => \E_ERROR,
				'CompileError', 'ParseError' => \E_PARSE,
				'AssertionError' => \E_WARNING,
				default => $code,
			};
		}

		$trace['context'] = '[ExceptionHandler: '.$e::class.'] '.$e->getTraceAsString();

		self::$_log[$trace['id']] = new Error($trace);
		self::fatal(self::$_log[$trace['id']]);
	}

	/**
	* Получить строку контекста
	*/
	public function getContext(): string {
		if ('cli' == \PHP_SAPI) {
			return $this->context;
		}

		return '<br>'.\preg_replace('/(\#\d+)/', '<br>$1', $this->context);
	}

	/**
	* Получить строку сообщения об ошибке с отформатированным кодом ошибки.
	*/
	public function getErrorMessage(): string {
		if ('cli' == \PHP_SAPI) {
			return $this->getErrorCode().' in file "'.$this->file.'" on line '.$this->line.': "'.$this->message.'"';
		}

		return $this->getErrorCode().' in file &laquo;'.\htmlspecialchars($this->file).
		'&raquo; on line &laquo;'.$this->line.'&raquo;: &laquo;'.\htmlspecialchars($this->message).'&raquo;';
	}

	/**
	* Получить отформатированное строковое представление кода ошибки.
	*/
	public function getErrorCode(): string {
		$code  = (string)$this->code;
		$type  = (string)$this->type;

		switch (\mb_strlen($code)) {
		case 1:
			$code = ($type == $code) ? ('000'.$code) : ('000'.$code.'-'.$type);
			break;

		case 2:
			$code = ($type == $code) ? ('00'.$code) : ('00'.$code.'-'.$type);
			break;

		case 3:
			$code = ($type == $code) ? ('0'.$code) : ('0'.$code.'-'.$type);
			break;

		default:
			if ($type != $code) {
				$code = $code.'-'.$type;
			}
		}

		if ('cli' == \PHP_SAPI) {
			return '[ERROR #'.$code.']';
		}

		return '[ERROR&nbsp;#'.$code.']';
	}

	public function updateTime(): void {
		$this->time = \time();
	}

	/**
	* Установить глобальный флаг наличия в списке dl\Error::$_log фатальной ошибки,
	* если таковая перехвачена обработчикам ошибок или исключений,
	* или сгенерирована пользователем.
	*/
	private static function fatal(self $error): void {
		if (\in_array($error->code, [self::ERROR, self::FATAL, self::PARSE,])) {
			self::$_fatal = true;
		}
	}

	/**
	* Получить идентификатор ошибки
	*/
	private static function makeId(array $trace): string {
		//return md5($trace['message'].'::'.$trace['file'].'::'.$trace['line'].'::'.$trace['type']);
		return md5($trace['file'].'::'.$trace['line'].'::'.$trace['type']);
	}

	/**
	* Зарегистрировать последнюю ошибку.
	* см. dl\Error::listen() и dl\Error::shutdown()
	*/
	private static function last(): void {
		if (!$e = \error_get_last()) {
			return;
		}

		$e['message'] ??= 'Unknown error';
		$e['file']    ??= 'External source';
		$e['line']    ??= 0;
		$e['context'] = '[LastErrorBeforeListen]';

		$e['id'] = self::makeId($e);
		self::$_log[$e['id']] ??= new Error($e);
	}

	/**
	* Конструктор ошибки.
	* state - состояние данных трассировки после их обработки
	* и получения на их основе идентификатора ошибки.
	*/
	private function __construct(array $state) {
		$this->id      = $state['id'];
		$this->message = $state['message'];
		
		if (isset($state['context'])) {
			$this->context = $state['context'];
		}
		else {
			$this->context = '[UnknownSource]';
		}

		$this->file = $state['file'];
		$this->line = $state['line'];

		if (isset($state['code'])) {
			$this->code = $state['code'];
		}
		else {
			$state['fatal'] ??= false;
			$this->code = $this->setCode($state['type'], $state['fatal']);
		}

		if (0 == $state['type']) {
			$this->type = $this->code;
		}
		else {
			$this->type = $state['type'];
		}

		if (isset($state['time'])) {
			$this->time = $state['time'];
		}
		else {
			$this->time = \time();
		}
		
		if (isset($state['date'])) {
			$this->date = $state['date'];
		}
		else {
			$this->date = \date('Y-m-d H:i:s', $this->time);
		}
	}

	/**
	* Преобразовать системные коды ошибок.
	*/
	private function setCode(int $type, bool $fatal): int {
		return match ($type) {
			\E_ERROR, \E_CORE_ERROR, \E_COMPILE_ERROR, \E_USER_ERROR, \E_RECOVERABLE_ERROR => self::ERROR,
			\E_PARSE => self::PARSE,
			\E_WARNING, \E_CORE_WARNING, \E_COMPILE_WARNING, \E_USER_WARNING, \E_STRICT, \E_DEPRECATED, \E_USER_DEPRECATED => self::WARNING,
			\E_NOTICE, \E_USER_NOTICE => self::NOTICE,
			default => $fatal ? self::FATAL : self::USER,
		};
	}
}

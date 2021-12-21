<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

	final class dl\Failure

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

final class Failure extends \Exception {
	public readonly string $id;
	public readonly string $ecode;
	public readonly string $emesg;
	public readonly string $context;
	public readonly int    $type;

	public function __construct(Error $e) {
		parent::__construct($e->message, $e->code);
		$this->file    = $e->file;
		$this->line    = $e->line;
		$this->id      = $e->id;
		$this->ecode   = $e->getErrorCode();
		$this->emesg   = $e->getErrorMessage();
		$this->context = $e->getContext();
		$this->type    = $e->type;
	}
}

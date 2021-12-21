<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |        Dmitry N. Lebedeff        / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |           <dl@adios.ru>         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____                            / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/          (C)2021          /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    interface dl\CallableState
	
	trait dl\SetStateCall
	Основная реализация интерфейса dl\CallableState

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl;

interface CallableState {
	public static function __set_state(array $state): self;
}

// CallableState implementation
trait SetStateCall {
	final public static function __set_state(array $state): self {
		return new static($state);
	}
}

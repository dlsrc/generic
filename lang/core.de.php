<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    final class dl\de\Core

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl\de;

final class Core extends \dl\Getter {
	protected function initialize(): void {
		$this->_property['e_ext']      =
		'Das Programm benötigt die Erweiterung "{0}", um zu funktionieren.';

		$this->_property['e_class']    =
		'Die eingeschlossene Datei "{0}" hat ein Objekt der Klasse "{1}" '.
		'zurückgegeben. Es wurde ein Objekt der Klasse "{2}" erwartet.';

		$this->_property['e_type']     =
		'Die mitgelieferte Datei "{0}" hat den ungültigen Datentyp "{1}" '.
		'zurückgegeben. Das Objekt wurde erwartet.';

		$this->_property['e_load']     =
		'Schnittstelle (Klasse, Eigenschaft) "{0}" wurde beim Laden '.
		'nicht gefunden.';

		$this->_property['h_registry'] =
		'Vollständige Registrierung von Klassen und Schnittstellen.';

		$this->_property['e_ftok']  =
		'Der Pfad "{0}" und die Projekt-ID "{1}" können nicht in den '.
		'System-V-IPC-Schlüssel konvertiert werden.';

		$this->_property['w_trace']    = 'Verfolgen';
		$this->_property['w_file']     = 'Datei';
		$this->_property['w_line']     = 'Leitung';
		$this->_property['w_context']  = 'Kontext';
		$this->_property['w_invoker']  = 'Revoker';

		$this->_property['src_header'] = \dl\Core::getHeader();
	}
}

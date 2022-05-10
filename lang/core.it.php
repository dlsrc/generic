<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    final class dl\it\Core

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl\it;

final class Core extends \dl\Getter {
	protected function initialize(): void {
		$this->_property['success']    = 'Tutto bene.';

		$this->_property['e_ext']      =
		'Il programma richiede l\'estensione "{0}" per funzionare.';

		$this->_property['e_class']    =
		'Il file incluso "{0}" ha restituito un oggetto della classe "{1}". '.
		'Era previsto un oggetto della classe "{2}".';

		$this->_property['e_type']     =
		'Il file incluso "{0}" ha restituito il tipo di dati non valido "{1}". '.
		'L\'oggetto era previsto.';

		$this->_property['e_load']     =
		'L\'interfaccia (classe, tratto) "{0}" non è stata trovata durante '.
		'il caricamento.';

		$this->_property['h_registry'] =
		'Registro completo di classi e interfacce.';

		$this->_property['e_ftok']     =
		'Impossibile convertire il percorso "{0}" e l\'ID progetto "{1}" '.
		'nella chiave IPC System V.';

		$this->_property['w_trace']    = 'Tracciare';
		$this->_property['w_file']     = 'File';
		$this->_property['w_line']     = 'Fila';
		$this->_property['w_context']  = 'Contesto';
		$this->_property['w_invoker']  = 'Invoker';

		$this->_property['src_header'] = \dl\Core::getHeader();
	}
}

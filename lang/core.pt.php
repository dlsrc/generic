<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    final class dl\pt\Core

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl\pt;

final class Core extends \dl\Getter {
	protected function initialize(): void {
		$this->_property['e_ext']      =
		'O programa requer a extensão "{0}" para funcionar.';

		$this->_property['e_class']    =
		'O arquivo incluído "{0}" retornou um objeto da classe "{1}". '.
		'Um objeto da classe "{2}" era esperado.';

		$this->_property['e_type']     =
		'O arquivo incluído "{0}" retornou o tipo de dados inválido "{1}". '.
		'O objeto era esperado. ';

		$this->_property['e_load']     =
		'Interface (classe, traço) "{0}" não foi encontrada durante '.
		'o carregamento.';

		$this->_property['h_registry'] =
		'Registro completo de classes e interfaces.';

		$this->_property['e_ftok']  =
		'Não foi possível converter o caminho "{0}" e o ID do projeto "{1}" '.
		'para a chave IPC do System V.';

		$this->_property['w_trace']    = 'Vestígio';
		$this->_property['w_file']     = 'Arquivo';
		$this->_property['w_line']     = 'Linha';
		$this->_property['w_context']  = 'Contexto';
		$this->_property['w_invoker']  = 'Invocador';

		$this->_property['src_header'] = \dl\Core::getHeader();
	}
}

<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    final class dl\es\Core

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl\es;

final class Core extends \dl\Getter {
	protected function initialize(): void {
		$this->_property['e_ext']      =
		'El programa requiere la extensión "{0}" para funcionar.';

		$this->_property['e_class']    =
		'El archivo incluido "{0}" devolvió un objeto de clase "{1}". '.
		'Se esperaba un objeto de clase "{2}".';

		$this->_property['e_type']     =
		'El archivo incluido "{0}" devolvió el tipo de datos no válido "{1}". '.
		'Se esperaba el objeto.';

		$this->_property['e_load']     =
		'La interfaz (clase, rasgo) "{0}" no se encontró durante la carga.';

		$this->_property['h_registry'] =
		'Completa la Registro de Clases e Interfaces.';

		$this->_property['e_ftok']  =
		'No se puede convertir la ruta "{0}" y el ID del proyecto "{1}" '.
		'a la clave de IPC de System V.';

		$this->_property['w_trace']    = 'Rastro';
		$this->_property['w_file']     = 'Archivo';
		$this->_property['w_line']     = 'Línea';
		$this->_property['w_context']  = 'Contexto';
		$this->_property['w_invoker']  = 'Inventor';

		$this->_property['src_header'] = \dl\Core::getHeader();
	}
}

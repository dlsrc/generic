<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    final class dl\fr\Core

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl\fr;

final class Core extends \dl\Getter {
	protected function initialize(): void {
		$this->_property['e_ext']      =
		'Le programme nécessite l\'extension "{0}" pour fonctionner.';

		$this->_property['e_class']    =
		'Le fichier inclus "{0}" a renvoyé un objet de classe "{1}". '.
		'Un objet de classe "{2}" était attendu.';

		$this->_property['e_type']     =
		'Le fichier inclus "{0}" a renvoyé le type de données non valide "{1}". '.
		'L\'objet était attendu.';

		$this->_property['e_load']     =
		'Interface (classe, trait) "{0}" n\'a pas été trouvé lors du chargement.';

		$this->_property['h_registry'] =
		'Registre complet des classes et des interfaces.';

		$this->_property['e_ftok']  =
		'Impossible de convertir le chemin "{0}" et l\'ID de projet "{1}" '.
		'en clé IPC System V. ';

		$this->_property['w_trace']    = 'Trace';
		$this->_property['w_file']     = 'Fichier';
		$this->_property['w_line']     = 'Ligne';
		$this->_property['w_context']  = 'Le contexte';
		$this->_property['w_invoker']  = 'Invocateur';

		$this->_property['src_header'] = \dl\Core::getHeader();
	}
}

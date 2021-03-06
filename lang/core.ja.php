<?php
/******************************************************************************\
    ______  _                                    ____ _____  _  ____  ______
    | ___ \| |                                  / _  | ___ \| |/ __ \/ ____/
    | |  \ \ |          Dmitry Lebedev         / /_| | |  \ \ | /  \ \____ \
    | |__/ / |____      <dl@adios.ru>         / ___  | |__/ / | \__/ /___/ /
    |_____/|_____/                           /_/   |_|_____/|_|\____/_____/

    ------------------------------------------------------------------------

    final class dl\ja\Core

    ------------------------------------------------------------------------

    PHP 8.1                                                         (C) 2021

\******************************************************************************/
declare(strict_types=1);
namespace dl\ja;

final class Core extends \dl\Getter {
	protected function initialize(): void {
		$this->_property['e_ext']      =
		'プログラムが機能するには、拡張子 「{0}」が必要です。';

		$this->_property['e_class']    =
		'インクルードされたファイル 「{0}」は、クラス 「{1}」のオブジェクトを返しました。 '.
		'クラス「{2}」のオブジェクトが必要でした。';

		$this->_property['e_type']     =
		'含まれているファイル 「{0}」が無効なデータ型 「{1}」を返しました。 '.
		'オブジェクトが予想されました。';

		$this->_property['e_load']     =
		'ロード中にインターフェイス（クラス、形式） 「{0}」が見つかりませんでした。';

		$this->_property['h_registry'] = 'クラスとインターフェイスの完全なレジストリ。';

		$this->_property['e_ftok']  =
		'パス "{0}"とプロジェクトID "{1}"をSystemVIPCキーに変換できません。';

		$this->_property['w_trace']    = '痕跡';
		$this->_property['w_file']     = 'ファイル';
		$this->_property['w_line']     = '行';
		$this->_property['w_context']  = 'コンテキスト';
		$this->_property['w_invoker']  = 'Invoker';

		$this->_property['src_header'] = \dl\Core::getHeader();
	}
}

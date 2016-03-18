<?php

/**
 * Class TTypeFinancement
 */
class TTypeFinancement extends TObjetStd {
	function __construct() {
		global $langs;

		parent::set_table(MAIN_DB_PREFIX.'planform_c_type_financement');
		parent::add_champs('active,entity','type=entier;index;');
		parent::add_champs('code, label','type=chaine');

		parent::_init_vars();
		parent::start();

	}

}

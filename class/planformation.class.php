<?php

/**
 * Class TPlanFormation
 */
class TPlanFormation extends TObjetStd {
	function __construct() {
		global $langs;

		parent::set_table(MAIN_DB_PREFIX.'planform');
		parent::add_champs('fk_type_financement','type=entier;index;');
		parent::add_champs('date_start, date_end','type=date');
		parent::add_champs('title','type=chaine');

		parent::_init_vars();
		parent::start();

	}
}

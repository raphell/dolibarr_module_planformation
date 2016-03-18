<?php
/* <Plan Formation>
 * Copyright (C) 2015 ATM Consulting <support@atm-consulting.fr>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
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

/**
 * Class TSection
 */
class TSection extends TObjetStd {
	function __construct() {
		global $langs;

		parent::set_table(MAIN_DB_PREFIX.'planform_section');
		parent::add_champs('title,ref','type=chaine;index;');

		parent::_init_vars();
		parent::start();

	}
}

/**
 * Class TSectionUserGroup
 */
class TSectionUserGroup extends TObjetStd {
	function __construct() {
		global $langs;

		parent::set_table(MAIN_DB_PREFIX.'planform_usergroup');
		parent::add_champs('fk_usergroup,fk_section','type=entier;index;');

		parent::_init_vars();
		parent::start();



	}
}

/**
 * Class TSectionUserGroup
 */
class TSectionPlanFormation extends TObjetStd {
	function __construct() {
		global $langs;

		parent::set_table(MAIN_DB_PREFIX.'planform_section');
		parent::add_champs('fk_planform,fk_section','type=entier;index;');

		parent::_init_vars();
		parent::start();

	}
}

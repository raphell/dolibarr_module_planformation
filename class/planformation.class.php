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

class TPlanFormation extends TObjetStd
{
	/**
	 * __construct
	 */
	function __construct() {
		global $langs;

		parent::set_table(MAIN_DB_PREFIX . 'planform');
		parent::add_champs('fk_type_financement', 'type=entier;index;');
		parent::add_champs('date_start, date_end', 'type=date');
		parent::add_champs('ref,title', 'type=chaine');

		parent::_init_vars();
		parent::start();
	}

	/**
	 */
	public function getSQLFetchAll() {
		global $conf, $langs;

		require_once ('dictionnaire.class.php');

		$dict = new TTypeFinancement();

		$sql = 'SELECT rowid as ID ,';
		$sql .= ' ref, ';
		$sql .= ' title as title, ';
		$sql .= ' date_start as date_start, ';
		$sql .= ' date_end as date_end, ';
		$sql .= ' fk_type_financement as fk_type_financement, ';
		$sql .= ' ' . $dict->get_table() . '.code as type_fin_code, ';
		$sql .= ' ' . $dict->get_table() . '.label as type_fin_label, ';
		$sql .= ' FROM ' . $this->get_table();
		$sql .= ' LEFT JOIN ' . $dict->get_table() . ' ON ' . $this->get_table() . '.fk_type_financement=' . $dict->get_table() . '.rowid';
	}

	/**
	 *
	 * @param string $mode
	 */
	public function getTrans($mode = 'std') {
		global $langs;
		$langs->load('planformation@planformation');

		$transarray = array (
				'rowid' => $langs->trans('Id'),
				'ref' => $langs->trans('Ref'),
				'date_start' => $langs->trans('DateStart'),
				'date_end' => $langs->trans('DateEnd'),
				'title' => $langs->trans('Title'),
				'type_fin_label' => $langs->trans('PFTypeFin')
		);
		if ($mode == 'title') {
			foreach ( $transarray as $key => $val ) {
				$trans_array_title[$key . '_title'] = $val;
			}

			$transarray = $trans_array_title;
		}

		return $transarray;
	}

	/**
	 * Returns the reference to the following non used Lead used depending on the active numbering module
	 * defined into LEAD_ADDON
	 *
	 * @param int $fk_user Id
	 * @param Societe $objsoc Object
	 * @return string Reference libre pour la lead
	 */
	function getNextNumRef($fk_user = null, Societe $objsoc = null) {
		global $conf, $langs;
		$langs->load("planformation@planformation");

		$dirmodels = array_merge(array (
				'/'
		), ( array ) $conf->modules_parts['models']);

		if (! empty($conf->global->PF_ADDON)) {
			foreach ( $dirmodels as $reldir ) {
				$dir = dol_buildpath($reldir . "core/modules/planformation/");
				if (is_dir($dir)) {
					$handle = opendir($dir);
					if (is_resource($handle)) {
						$var = true;

						while ( ($file = readdir($handle)) !== false ) {
							if ($file == $conf->global->PF_ADDON . '.php') {
								$file = substr($file, 0, dol_strlen($file) - 4);
								require_once $dir . $file . '.php';

								$module = new $file();

								// Chargement de la classe de numerotation
								$classname = $conf->global->PF_ADDON;

								$obj = new $classname();

								$numref = "";
								$numref = $obj->getNextValue($fk_user, $objsoc, $this);

								if ($numref != "") {
									return $numref;
								} else {
									$this->error = $obj->error;
									return "";
								}
							}
						}
					}
				}
			}
		} else {
			$langs->load("errors");
			$this->errors[]= $langs->trans("Error") . " " . $langs->trans("ErrorModuleSetupNotComplete");
			return -1;
		}

		return null;
	}
}

/**
 * Class TSection
 */
class TSection extends TObjetStd
{
	function __construct() {
		global $langs;

		parent::set_table(MAIN_DB_PREFIX . 'planform_section');
		parent::add_champs('title,ref', 'type=chaine;index;');

		parent::_init_vars();
		parent::start();
	}
}

/**
 * Class TSectionUserGroup
 */
class TSectionUserGroup extends TObjetStd
{
	function __construct() {
		global $langs;

		parent::set_table(MAIN_DB_PREFIX . 'planform_usergroup');
		parent::add_champs('fk_usergroup,fk_section', 'type=entier;index;');

		parent::_init_vars();
		parent::start();
	}
}

/**
 * Class TSectionUserGroup
 */
class TSectionPlanFormation extends TObjetStd
{
	function __construct() {
		global $langs;

		parent::set_table(MAIN_DB_PREFIX . 'planform_section');
		parent::add_champs('fk_planform,fk_section', 'type=entier;index;');

		parent::_init_vars();
		parent::start();
	}
}

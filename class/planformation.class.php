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
		parent::add_champs('fk_type_financement', array('type'=>'integer','index'=>true));
		parent::add_champs('date_start, date_end', array('type'=>'date'));
		parent::add_champs('ref,title', array('type'=>'string'));

		parent::_init_vars();
		parent::start();

		$dt = new DateTime();
		$dt->setDate($dt->format('Y'), 1, 1);
		$this->date_start = $dt->getTimestamp();

		$dt->setDate($dt->format('Y'), 12, 31);
		$this->date_end = $dt->getTimestamp();

		$this->setChild('TSectionPlanFormation', 'fk_planform');
	}

	/**
	 *
	 * @return string
	 */
	public function getSQLFetchAll() {
		global $conf, $langs;

		require_once ('dictionnaire.class.php');

		$dict = new TTypeFinancement();

		$sql = 'SELECT planform.rowid as ID ,';
		$sql .= ' planform.ref, ';
		$sql .= ' planform.title, ';
		$sql .= ' planform.date_start, ';
		$sql .= ' planform.date_end, ';
		$sql .= ' planform.fk_type_financement,';
		$sql .= ' dict.code as type_fin_code, ';
		$sql .= ' dict.label as type_fin_label ';
		$sql .= ' FROM ' . $this->get_table().' as planform';
		$sql .= ' LEFT JOIN ' . $dict->get_table() . ' as dict ON (planform.fk_type_financement=dict.rowid)';

		return $sql;
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
	 * Returns the reference to the following non used plan formation used depending on the active numbering module
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
		parent::add_champs('title,ref', array('type'=>'string','index'=>true));

		parent::_init_vars();
		parent::start();

		$this->setChild('TSectionUserGroup', 'fk_section');
	}

	/**
	 *
	 * @return string
	 */
	public function getSQLFetchAll() {
		global $conf, $langs;

		$sql = 'SELECT s.rowid as ID ,';
		$sql .= ' s.ref, ';
		$sql .= ' s.title ';
		$sql .= ' FROM ' . $this->get_table().' as s';

		return $sql;
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
				'title' => $langs->trans('Title'),
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
	 * Returns the reference to the following non used section used depending on the active numbering module
	 * defined into PF_SECTION_ADDON
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

		if (! empty($conf->global->PF_SECTION_ADDON)) {
			foreach ( $dirmodels as $reldir ) {
				$dir = dol_buildpath($reldir . "core/modules/planformation/");
				if (is_dir($dir)) {
					$handle = opendir($dir);
					if (is_resource($handle)) {
						$var = true;

						while ( ($file = readdir($handle)) !== false ) {
							if ($file == $conf->global->PF_SECTION_ADDON . '.php') {
								$file = substr($file, 0, dol_strlen($file) - 4);
								require_once $dir . $file . '.php';

								$module = new $file();

								// Chargement de la classe de numerotation
								$classname = $conf->global->PF_SECTION_ADDON;

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
 * Class TSectionUserGroup
 */
class TSectionUserGroup extends TObjetStd
{
	function __construct() {
		global $langs;

		parent::set_table(MAIN_DB_PREFIX . 'planform_usergroup');
		parent::add_champs('fk_usergroup,fk_section', array('type'=>'integer','index'=>true));

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
		parent::add_champs('fk_planform,fk_section', array('type'=>'integer','index'=>true));

		parent::_init_vars();
		parent::start();
	}
}

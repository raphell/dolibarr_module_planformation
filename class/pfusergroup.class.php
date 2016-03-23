<?php
/* <Plan Formation>
 * Copyright (C) 2016 Florian HENRY <florian.henry@atm-consulting.fr>
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
 *	 \file       /planformation/class/pf_usergroup.class.php
 */

require_once DOL_DOCUMENT_ROOT.'/user/class/usergroup.class.php';

class TPFUserGroup extends UserGroup {

	public $lines = array();

	/**
	 * Load object in memory from the database
	 *
	 * @param string $sortorder Sort Order
	 * @param string $sortfield Sort field
	 * @param int    $limit     offset limit
	 * @param int    $offset    offset limit
	 * @param array  $filter    filter array
	 * @param string $filtermode filter mode (AND or OR)
	 *
	 * @return int <0 if KO, >0 if OK
	 */
	public function fetchAll($sortorder='', $sortfield='', $limit=0, $offset=0, array $filter = array(), $filtermode='AND')
	{
		dol_syslog(__METHOD__, LOG_DEBUG);

		$sql = 'SELECT';
		$sql .= ' t.rowid,';

		$sql .= " t.nom,";
		$sql .= " t.entity,";
		$sql .= " t.datec,";
		$sql .= " t.tms,";
		$sql .= " t.note";


		$sql .= ' FROM ' . MAIN_DB_PREFIX . $this->table_element. ' as t';

		$filter['t.entity']=getEntity($this->element,1);

		// Manage filter01
		$sqlwhere = array();
		if (count($filter) > 0) {
			foreach ($filter as $key => $value) {
				if ($key=='!array(id)') {
					$sqlwhere [] = '('.$key . ' NOT IN (' . $this->db->escape($value) . ')' .')';
				}elseif ($key=='t.entity') {
					$sqlwhere [] = '('.$key . ' IN (' . $this->db->escape($value) . ')'.')';
				} else {
					$sqlwhere [] = '('.$key . ' LIKE \'%' . $this->db->escape($value) . '%\''.')';
				}
			}
		}
		if (count($sqlwhere) > 0) {
			$sql .= ' WHERE ' . implode(' '.$filtermode.' ', $sqlwhere);
		}

		if (!empty($sortfield)) {
			$sql .= $this->db->order($sortfield,$sortorder);
		}
		if (!empty($limit)) {
		 $sql .=  ' ' . $this->db->plimit($limit + 1, $offset);
		}
		$this->lines = array();

		$resql = $this->db->query($sql);
		if ($resql) {
			$num = $this->db->num_rows($resql);

			while ($obj = $this->db->fetch_object($resql)) {
				$line = new UsergroupLine();

				$line->id = $obj->rowid;

				$line->nom = $obj->nom;
				$line->entity = $obj->entity;
				$line->datec = $this->db->jdate($obj->datec);
				$line->tms = $this->db->jdate($obj->tms);
				$line->note = $obj->note;


				$this->lines[] = $line;
			}
			$this->db->free($resql);

			return $num;
		} else {
			$this->errors[] = 'Error ' . $this->db->lasterror();
			dol_syslog(__METHOD__ . ' ' . join(',', $this->errors), LOG_ERR);

			return - 1;
		}
	}

}

/**
 * Class UsergroupLine
 */
class UsergroupLine
{
	/**
	 * @var int ID
	 */
	public $id;
	/**
	 * @var mixed Sample line property 1
	 */

	public $nom;
	public $entity;
	public $datec = '';
	public $tms = '';
	public $note;

	/**
	 * @var mixed Sample line property 2
	 */

}
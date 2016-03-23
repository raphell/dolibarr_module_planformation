<?php
require_once ('config.php');

// Security check
if ($user->societe_id)
	$socid = $user->societe_id;
$result = restrictedArea($user, 'planformation', 0, 'planformation');

require ('./class/planformation.class.php');
require ('./class/dictionnaire.class.php');

$langs->trans('planformation@planformation');

$PDOdb = new TPDOdb();

$tbs = new TTemplateTBS();
$pf = new TPlanFormation();
$typeFin = new TTypeFinancement();

$action = GETPOST('action');

// Pour que le bouton "Annuler" de la fiche d'un dossier annule et ne sauvegarde pas...
if (isset($_REQUEST['cancel']) && $_REQUEST['cancel'] == "Annuler") {
	$action = "";
}

if (! empty($action)) {
	switch ($action) {
		case 'default' :
			_list($PDOdb, $pf);
			break;
		case 'list' :
			_list($PDOdb, $pf);
			break;
		case 'add' :
		case 'new' :
			$pf->set_values($_REQUEST);
			_card($PDOdb, $pf, $typeFin, 'edit');

			break;
		case 'info' :
			if ($pf->load($PDOdb, GETPOST('id', 'int'))) {
				_info($PDOdb, $pf);
			} else {
				setEventMessage($langs->trans('ImpossibleLoadElement'), 'errors');
			}

			break;
		case 'edit' :
			if ($pf->load($PDOdb, GETPOST('id', 'int'))) {
				_card($PDOdb, $pf, $typeFin, 'edit');
			} else {
				setEventMessage($langs->trans('ImpossibleLoadElement'), 'errors');
			}
			break;

		case 'save' :
			if ($pf->load($PDOdb, GETPOST('id', 'int'))) {
				$pf->set_values($_REQUEST);

				$pf->save($PDOdb);
				_card($PDOdb, $pf, $typeFin, 'view');
			} else {
				setEventMessage($langs->trans('ImpossibleLoadElement'), 'errors');
			}
			break;

		case 'delete' :

			if ($pf->load($PDOdb, GETPOST('id', 'int'))) {
				$pf->delete($PDOdb);
				_list($PDOdb, $pf);
			} else {
				setEventMessage($langs->trans('ImpossibleLoadElement'), 'errors');
			}

			break;
	}
} elseif (isset($_REQUEST['id'])) {
	$pf->load($PDOdb, $_REQUEST['id']);

	_card($PDOdb, $pf, $typeFin, 'view');
} else {
	_list($PDOdb, $pf);
}

llxFooter();

/**
 *
 * @param TPDOdb $PDOdb
 * @param TPlanFormation $pf
 */
function _list(TPDOdb &$PDOdb, TPlanFormation &$pf) {
	global $langs, $db, $conf, $user, $action;

	llxHeader('', $langs->trans('PFPlanFormationList'));

	$r = new TListviewTBS('planform');

	$TOrder = array (
			'planform.date_start' => 'DESC'
	);
	if (isset($_REQUEST['orderDown']))
		$TOrder = array (
				$_REQUEST['orderDown'] => 'DESC'
		);
	if (isset($_REQUEST['orderUp']))
		$TOrder = array (
				$_REQUEST['orderUp'] => 'ASC'
		);

		// $PDOdb->debug = true;

	echo $r->render($PDOdb, $pf->getSQLFetchAll(), array (
			'limit' => array (
					'page' => (isset($_REQUEST['page']) ? $_REQUEST['page'] : 1),
					'nbLine' => $conf->liste_limit
			),
			'link' => array (
					'ref' => img_picto('', 'object_dir') . ' <a href="?id=@ID@">@val@</a>'
			),
			'type' => array (
					'date_start' => 'date',
					'date_end' => 'date'
			),
			'hide' => array (
					'ID',
					'fk_type_financement',
					'type_fin_code'
			),
			'title' => $pf->getTrans(),
			'liste' => array (
					'titre' => $langs->trans('PFPlanFormationList'),
					'image' => img_picto('', 'planformation@planformation', '', 0),
					'messageNothing' => $langs->transnoentities('NoRecDossierToDisplay')
			),
			'search' => array (
					'date_start' => array (
							'recherche' => 'calendars',
							'table' => 'planform'
					),
					'date_end' => array (
							'recherche' => 'calendars',
							'table' => 'planform'
					)
			),
			'orderBy' => $TOrder
	));

	$PDOdb->execute($sql);
}

/**
 *
 * @param TPDOdb $PDOdb
 * @param TPlanFormation $pf
 * @param string $mode
 */
function _info(TPDOdb &$PDOdb, TPlanFormation &$pf) {
	global $db, $langs, $user, $conf;

	dol_include_once('/planformation/lib/planformation.lib.php');
	require_once (DOL_DOCUMENT_ROOT . '/core/lib/functions2.lib.php');

	llxHeader('', $langs->trans("PFPlanFormation"));
	$head = planformation_prepare_head($pf);
	dol_fiche_head($head, 'info', $langs->trans("PFPlanFormationCard"), 0);

	$pf->date_creation = $pf->date_cre;
	$pf->date_modification = $pf->date_maj;
	print '<table width="100%"><tr><td>';
	dol_print_object_info($pf);
	print '</td></tr></table>';
	print '</div>';
}

/**
 *
 * @param TPDOdb $PDOdb
 * @param TPlanFormation $pf
 * @param string $mode
 */
function _card(TPDOdb &$PDOdb, TPlanFormation &$pf, TTypeFinancement &$typeFin, $mode = '') {
	global $db, $langs, $user, $conf;

	dol_include_once('/planformation/lib/planformation.lib.php');

	llxHeader('', $langs->trans("PFPlanFormation"));
	$head = planformation_prepare_head($pf);
	dol_fiche_head($head, 'planformation', $langs->trans("PFPlanFormationCard"), 0);

	$formDoli = new Form($db);
	$formCore = new TFormCore($_SERVER['PHP_SELF'], 'formscore', 'POST');
	$formCore->Set_typeaff($mode);

	echo $formCore->hidden('id', $pf->getId());
	echo $formCore->hidden('action', 'save');
	echo $formCore->hidden('entity', getEntity());

	$TBS = new TTemplateTBS();

	$btRetour = '<a class="butAction" href="' . dol_buildpath("/planformation/planformation.php?action=list", 1) . '">' . $langs->trans('BackToList') . '</a>';
	$btCancel = $formCore->btsubmit($langs->trans('Cancel'), 'cancel');
	$btSave = $formCore->btsubmit($langs->trans('Valid'), 'save');
	$btModifier = '<a class="butAction" href="' . dol_buildpath('/planformation/planformation.php?id=' . $pf->rowid . '&action=edit', 1) . '">' . $langs->trans('PFPlanFormationEdit') . '</a>';
	$btDelete = "<input type=\"button\" id=\"action-delete\" value=\"" . $langs->trans('Delete') . "\" name=\"cancel\" class=\"butActionDelete\" onclick=\"if(confirm('" . $langs->trans('PFDeleteConfirm') . "'))document.location.href='?action=delete&id=" . $pf->rowid . "'\" />";

	// Load type fin data
	$result = $typeFin->fetchAll($PDOdb, $typeFin->get_table());
	if ($result < 0) {
		setEventMessages(null, $typeFin->errors, 'errors');
	}

	// Fill form with title and data
	$data = $pf->getTrans('title');

	if ($mode == 'edit') {
		$data['titre'] = load_fiche_titre($pf->getId() > 0 ? $langs->trans("PFPlanFormationEdit") : $langs->trans("PFPlanFormationNew"), '');
		$data['title'] = $formCore->texte('', 'title', $pf->title, 30, 255);
		$data['type_fin_label'] = $formCore->combo('', 'fk_type_financement', $typeFin->lines, '');
		$data['date_start'] = $formCore->doliCalendar('date_start', $pf->date_start);
		$data['date_end'] = $formCore->doliCalendar('date_end', $pf->date_end);
		if ($conf->global->PF_ADDON == 'mod_planformation_universal') {
			$data['ref'] = $formCore->texte('', 'ref', $pf->ref, 15, 255);
		} elseif ($conf->global->PF_ADDON == 'mod_planformation_simple') {
			$result = $pf->getNextNumRef();
			if ($result == - 1) {
				setEventMessages(null, $pf->errors, 'errors');
			}
			$data['ref'] = $result;
			echo $formCore->hidden('ref', $result);
		}

		$buttons = $btCancel . $btSave;
	} else {
		$data['titre'] = load_fiche_titre($langs->trans("PFPlanFormationCard"), '');
		$data['type_fin_label'] = $typeFin->lines[$pf->fk_type_financement];
		$data['date_start'] = dol_print_date($pf->date_start);
		$data['date_end'] = dol_print_date($pf->date_end);
		$data['title'] = $pf->title;
		$data['ref'] = $formCore->texte('', 'ref', $pf->ref, 15);
		$buttons = $btRetour . ' ' . $btModifier . ' ' . $btDelete;
	}

	// Todo mandatory fields

	print $TBS->render('./tpl/planformation.tpl.php', array (),

	array (
			'planformation' => $data,
			'view' => array (
					'mode' => $mode
			),
			'buttons' => array (
					'buttons' => $buttons
			)
	));

	echo $formCore->end_form();
}

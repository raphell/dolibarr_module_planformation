<?php
require_once ('config.php');
dol_include_once('/comm/action/class/actioncomm.class.php');
// require('../../core/class/html.form.class.php');

// Security check
if ($user->societe_id)
	$socid = $user->societe_id;
$result = restrictedArea($user, 'planformation', 0, 'planformation');

require ('./class/planformation.class.php');
require ('./class/dictionnaire.class.php');

$langs->trans('planformation@planformation');

$PDOdb = new TPDOdb();
// $PDOdb->Execute("SET NAMES 'utf8'");

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
			_liste($PDOdb, $pf);
			break;
		case 'list' :
			_liste($PDOdb, $pf);
			break;
		case 'add' :
		case 'new' :
			$pf->set_values($_REQUEST);
			_fiche($PDOdb, $pf, $typeFin, 'edit');

			break;
		case 'edit' :

			$pf->load($PDOdb, $_REQUEST['id']);

			_fiche($PDOdb, $pf, $typeFin, 'edit');
			break;

		case 'save' :
			$pf->load($PDOdb, $_REQUEST['id']);
			$pf->set_values($_REQUEST);

			$pf->save($PDOdb);
			_fiche($PDOdb, $pf, $typeFin, 'view');

			break;

		case 'delete' :

			$pf->load($PDOdb, $_REQUEST['id']);
			$pf->delete($PDOdb);
			_liste($PDOdb, $pf);

			break;
	}
} elseif (isset($_REQUEST['id'])) {
	$pf->load($PDOdb, $_REQUEST['id']);

	_fiche($PDOdb, $pf, $typeFin, 'view');
} else {
	_liste($PDOdb, $pf);
}

llxFooter();

/**
 *
 * @param TPDOdb $PDOdb
 * @param TPlanFormation $pf
 */
function _liste(TPDOdb &$PDOdb, TPlanFormation &$pf) {
	global $langs, $db, $conf, $user, $action;

	llxHeader('', $langs->trans('PFPlanFormationList'));

	$r = new TSSRenderControler($pf);

	$TOrder = array (
			'date_start' => 'DESC'
	);
	if (isset($_REQUEST['orderDown']))
		$TOrder = array (
				$_REQUEST['orderDown'] => 'DESC'
		);
	if (isset($_REQUEST['orderUp']))
		$TOrder = array (
				$_REQUEST['orderUp'] => 'ASC'
		);

	$PDOdb->debug = true;

	$r->liste($PDOdb, $pf->getSQLFetchAll(), array (
			'limit' => array (
					'page' => (isset($_REQUEST['page']) ? $_REQUEST['page'] : 1),
					'nbLine' => $conf->liste_limit
			) // Limite dolibarr modifiable sur fiche user
,
			'link' => array (
					'ref' => img_picto('', 'object_dir') . ' <a href="?id=@rowid@">@val@</a>'
			),
			'type' => array (
					'date_start' => 'date',
					'date_end' => 'date'
			),
			'hide'=>array('rowid', 'fk_type_financement'),
			'title' => $pf->getTrans(),
			'liste' => array (
					'titre' => $langs->trans('PFPlanFormationList'),
					'image' => img_picto('', 'planformation@planformation', '', 0),
					'picto_precedent' => img_picto('', 'back.png', '', 0),
					'picto_suivant' => img_picto('', 'next.png', '', 0),
					'noheader' => 0,
					'messageNothing' => $langs->transnoentities('NoRecDossierToDisplay'),
					'order_down' => img_picto('', '1downarrow.png', '', 0),
					'order_up' => img_picto('', '1uparrow.png', '', 0)
			),
			'search' => array (
					'date_start' => array (
							'recherche' => 'calendars',
							'table' => 'd'
					),
					'date_end' => array (
							'recherche' => 'calendars',
							'table' => 'd'
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
function _fiche(TPDOdb &$PDOdb, TPlanFormation &$pf, TTypeFinancement &$typeFin, $mode = '') {
	global $db, $langs, $user, $conf;

	dol_include_once('/planformation/lib/planformation.lib.php');

	llxHeader('', $langs->trans("PFPlanFormation"));
	$head = planformation_prepare_head($pf);
	dol_fiche_head($head, 'planformation', $langs->trans("PFPlanFormationCard"), 0);

	$formCore = new TFormCore();
	$formDoli = new Form($db);
	$form = new TFormCore($_SERVER['PHP_SELF'], 'formscore', 'POST');
	$form->Set_typeaff($mode);

	echo $form->hidden('id', $pf->getId());
	echo $form->hidden('action', 'save');
	echo $form->hidden('entity', getEntity());

	$TBS = new TTemplateTBS();

	$btRetour = '<a class="butAction" href="' . dol_buildpath("/planformation/planformation.php?action=list", 1) . '">' . $langs->trans('BackToList') . '</a>';
	$btCancel = $form->btsubmit($langs->trans('Cancel'), 'cancel');
	$btSave = $form->btsubmit($langs->trans('Valid'), 'save');
	$btModifier = '<a class="butAction" href="' . dol_buildpath('/planformation/planformation.php?id=' . $pf->rowid . '&action=edit', 1) . '">' . $langs->trans('PFPlanFormationEdit') . '</a>';
	$btDelete = "<input type=\"button\" id=\"action-delete\" value=\"" . $langs->trans('Delete') . "\" name=\"cancel\" class=\"butActionDelete\" onclick=\"if(confirm('" . $langs->trans('') . "'))document.location.href='?action=delete&id=" . $dossier->rowid . "'\" />";

	// Load type fin data
	$result = $typeFin->fetchAll($PDOdb, $typeFin->get_table());
	if ($result < 0) {
		setEventMessages(null, $typeFin->errors, 'errors');
	}

	// Fill form with title and data
	$data = $pf->getTrans('title');

	if ($mode == 'edit') {
		$data['titre'] = load_fiche_titre($pf->getId() > 0 ? $langs->trans("PFPlanFormationEdit") : $langs->trans("PFPlanFormationNew"), '');
		$data['title'] = $form->texte('', 'title', $pf->title, 30);
		$data['type_fin_label'] = $formCore->combo('', 'fk_type_financement', $typeFin->lines, '');
		$data['date_start'] = $form->doliCalendar('date_start', (empty($pf->get_date('date_start')) ? dol_mktime(0, 0, 0, 1, 1, dol_print_date(dol_now(), '%Y')) : $pf->get_date('date_start')));
		$data['date_end'] = $form->doliCalendar('date_end', (empty($pf->get_date('date_end')) ? dol_mktime(0, 0, 0, 12, 31, dol_print_date(dol_now(), '%Y')) : $pf->get_date('date_end')));
		if ($conf->global->PF_ADDON == 'mod_planformation_universal') {
			$data['ref'] = $form->texte('', 'ref', $pf->ref, 15);
		} elseif ($conf->global->PF_ADDON == 'mod_planformation_simple') {
			$result = $pf->getNextNumRef();
			if ($result == - 1) {
				setEventMessages(null, $pf->errors, 'errors');
			}
			$data['ref'] = $result;
			echo $form->hidden('ref', $result);
		}

		$buttons = $btCancel . $btSave;
	} else {
		$data['titre'] = load_fiche_titre($langs->trans("PFPlanFormationCard"), '');
		$data['type_fin_label'] = $typeFin->lines[$pf->fk_type_financement];
		$data['date_start'] = dol_print_date($pf->get_date('date_start'));
		$data['date_end'] = dol_print_date($pf->get_date('date_end'));
		$data['title'] = $pf->title;
		$data['ref'] = $form->texte('', 'ref', $pf->ref, 15);
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

	echo $form->end_form();
}

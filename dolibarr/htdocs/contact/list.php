<?php
/* Copyright (C) 2001-2004  Rodolphe Quiedeville    <rodolphe@quiedeville.org>
 * Copyright (C) 2003       Eric Seigne             <erics@rycks.com>
 * Copyright (C) 2004-2012  Laurent Destailleur     <eldy@users.sourceforge.net>
 * Copyright (C) 2005-2012  Regis Houssin           <regis.houssin@capnetworks.com>
 * Copyright (C) 2013-2015  Raphaël Doursenaud      <rdoursenaud@gpcsolutions.fr>
 * Copyright (C) 2013       Cédric Salvador         <csalvador@gpcsolutions.fr>
 * Copyright (C) 2013       Alexandre Spangaro      <aspangaro.dolibarr@gmail.com>
 * Copyright (C) 2015       Jean-François Ferry     <jfefe@aternatik.fr>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/**
 *	    \file       htdocs/contact/list.php
 *      \ingroup    societe
 *		\brief      Page to list all contacts
 */

require '../main.inc.php';
require_once DOL_DOCUMENT_ROOT.'/contact/class/contact.class.php';
require_once DOL_DOCUMENT_ROOT.'/core/class/html.formother.class.php';

$langs->loadLangs(array("companies", "suppliers"));

$action=GETPOST('action','alpha');
$massaction=GETPOST('massaction','alpha');
$show_files=GETPOST('show_files','int');
$confirm=GETPOST('confirm','alpha');
$toselect = GETPOST('toselect', 'array');

// Security check
$id = GETPOST('id','int');
$contactid = GETPOST('id','int');
$ref = '';  // There is no ref for contacts
if ($user->societe_id) $socid=$user->societe_id;
$result = restrictedArea($user, 'contact', $contactid,'');

$sall=GETPOST('sall', 'alphanohtml');
$search_cti=preg_replace('/^0+/', '', preg_replace('/[^0-9]/', '', GETPOST('search_cti', 'alphanohtml')));	// Phone number without any special chars
$search_phone=GETPOST("search_phone");

$search_firstlast_only=GETPOST("search_firstlast_only");
$search_lastname=GETPOST("search_lastname");
$search_firstname=GETPOST("search_firstname");
$search_societe=GETPOST("search_societe");
$search_poste=GETPOST("search_poste");
$search_phone_perso=GETPOST("search_phone_perso");
$search_phone_pro=GETPOST("search_phone_pro");
$search_phone_mobile=GETPOST("search_phone_mobile");
$search_fax=GETPOST("search_fax");
$search_email=GETPOST("search_email");
$search_skype=GETPOST("search_skype");
$search_priv=GETPOST("search_priv");
$search_categ=GETPOST("search_categ",'int');
$search_categ_thirdparty=GETPOST("search_categ_thirdparty",'int');
$search_categ_supplier=GETPOST("search_categ_supplier",'int');
$search_status=GETPOST("search_status",'int');
$search_type=GETPOST('search_type','alpha');
if ($search_status=='') $search_status=1; // always display activ customer first

$optioncss = GETPOST('optioncss','alpha');


$type=GETPOST("type");
$view=GETPOST("view");

$limit = GETPOST('limit','int')?GETPOST('limit','int'):$conf->liste_limit;
$sortfield = GETPOST('sortfield', 'alpha');
$sortorder = GETPOST('sortorder', 'alpha');
$page = GETPOST('page', 'int');
$userid=GETPOST('userid','int');
$begin=GETPOST('begin');
if (! $sortorder) $sortorder="ASC";
if (! $sortfield) $sortfield="p.lastname";
if (empty($page) || $page < 0) { $page = 0; }
$offset = $limit * $page;

$langs->load("companies");

$contextpage='contactlist';
$titre = (! empty($conf->global->SOCIETE_ADDRESSES_MANAGEMENT) ? $langs->trans("ListOfContacts") : $langs->trans("ListOfContactsAddresses"));
if ($type == "p")
{
    $contextpage='contactprospectlist';
	$titre.='  ('.$langs->trans("ThirdPartyProspects").')';
	$urlfiche="card.php";
}
if ($type == "c")
{
    $contextpage='contactcustomerlist';
	$titre.='  ('.$langs->trans("ThirdPartyCustomers").')';
	$urlfiche="card.php";
}
else if ($type == "f")
{
    $contextpage='contactsupplierlist';
	$titre.=' ('.$langs->trans("ThirdPartySuppliers").')';
	$urlfiche="card.php";
}
else if ($type == "o")
{
    $contextpage='contactotherlist';
	$titre.=' ('.$langs->trans("OthersNotLinkedToThirdParty").')';
	$urlfiche="";
}

// Initialize technical object to manage hooks of page. Note that conf->hooks_modules contains array of hook context
$hookmanager->initHooks(array($contextpage));
$extrafields = new ExtraFields($db);

// fetch optionals attributes and labels
$extralabels = $extrafields->fetch_name_optionals_label('contact');
$search_array_options=$extrafields->getOptionalsFromPost($extralabels,'','search_');

// List of fields to search into when doing a "search in all"
$fieldstosearchall = array(
    'p.lastname'=>'Lastname',
    'p.firstname'=>'Firstname',
    'p.email'=>'EMail',
    's.nom'=>"ThirdParty",
);

// Definition of fields for list
$arrayfields=array(
    'p.lastname'=>array('label'=>$langs->trans("Lastname"), 'checked'=>1),
    'p.firstname'=>array('label'=>$langs->trans("Firstname"), 'checked'=>1),
    'p.poste'=>array('label'=>$langs->trans("PostOrFunction"), 'checked'=>1),
    'p.town'=>array('label'=>$langs->trans("Town"), 'checked'=>0),
    'p.zip'=>array('label'=>$langs->trans("Zip"), 'checked'=>0),
    'p.phone'=>array('label'=>$langs->trans("Phone"), 'checked'=>1),
    'p.phone_perso'=>array('label'=>$langs->trans("PhonePerso"), 'checked'=>0),
    'p.phone_mobile'=>array('label'=>$langs->trans("PhoneMobile"), 'checked'=>1),
    'p.fax'=>array('label'=>$langs->trans("Fax"), 'checked'=>1),
    'p.email'=>array('label'=>$langs->trans("EMail"), 'checked'=>1),
    'p.skype'=>array('label'=>$langs->trans("Skype"), 'checked'=>1, 'enabled'=>(! empty($conf->skype->enabled))),
    'p.thirdparty'=>array('label'=>$langs->trans("ThirdParty"), 'checked'=>1, 'enabled'=>empty($conf->global->SOCIETE_DISABLE_CONTACTS)),
    'p.priv'=>array('label'=>$langs->trans("ContactVisibility"), 'checked'=>1, 'position'=>200),
    'p.datec'=>array('label'=>$langs->trans("DateCreationShort"), 'checked'=>0, 'position'=>500),
    'p.tms'=>array('label'=>$langs->trans("DateModificationShort"), 'checked'=>0, 'position'=>500),
    'p.statut'=>array('label'=>$langs->trans("Status"), 'checked'=>1, 'position'=>1000),
);
// Extra fields
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
{
   foreach($extrafields->attribute_label as $key => $val)
   {
       $arrayfields["ef.".$key]=array('label'=>$extrafields->attribute_label[$key], 'checked'=>$extrafields->attribute_list[$key], 'position'=>$extrafields->attribute_pos[$key], 'enabled'=>$extrafields->attribute_perms[$key]);
   }
}

$object=new Contact($db);
if (($id > 0 || ! empty($ref)) && $action != 'add')
{
    $result=$object->fetch($id,$ref);
    if ($result < 0) dol_print_error($db);
}


/*
 * Actions
 */

if (GETPOST('cancel')) { $action='list'; $massaction=''; }
if (! GETPOST('confirmmassaction') && $massaction != 'presend' && $massaction != 'confirm_presend') { $massaction=''; }

$parameters=array();
$reshook=$hookmanager->executeHooks('doActions',$parameters, $object, $action);    // Note that $action and $object may have been modified by some hooks
if ($reshook < 0) setEventMessages($hookmanager->error, $hookmanager->errors, 'errors');

if (empty($reshook))
{
    // Selection of new fields
    include DOL_DOCUMENT_ROOT.'/core/actions_changeselectedfields.inc.php';

    // Did we click on purge search criteria ?
    if (GETPOST('button_removefilter_x') || GETPOST('button_removefilter.x') || GETPOST('button_removefilter'))	// All tests are required to be compatible with all browsers
    {
        $sall="";
        $search_firstlast_only="";
        $search_lastname="";
        $search_firstname="";
        $search_societe="";
        $search_poste="";
        $search_phone="";
        $search_phone_perso="";
        $search_phone_pro="";
        $search_phone_mobile="";
        $search_fax="";
        $search_email="";
        $search_skype="";
        $search_priv="";
        $search_status=-1;
        $search_categ='';
        $search_categ_thirdparty='';
        $search_categ_supplier='';
        $toselect='';
        $search_array_options=array();
    }

    // Mass actions
    $objectclass='Contact';
    $objectlabel='Contact';
    $permtoread = $user->rights->societe->lire;
    $permtodelete = $user->rights->societe->supprimer;
    $uploaddir = $conf->societe->dir_output;
    include DOL_DOCUMENT_ROOT.'/core/actions_massactions.inc.php';
}

if ($search_priv < 0) $search_priv='';


/*
 * View
 */

$form=new Form($db);
$formother=new FormOther($db);
$contactstatic=new Contact($db);

$title = (! empty($conf->global->SOCIETE_ADDRESSES_MANAGEMENT) ? $langs->trans("Contacts") : $langs->trans("ContactsAddresses"));

$sql = "SELECT s.rowid as socid, s.nom as name,";
$sql.= " p.rowid, p.lastname as lastname, p.statut, p.firstname, p.zip, p.town, p.poste, p.email, p.skype,";
$sql.= " p.phone as phone_pro, p.phone_mobile, p.phone_perso, p.fax, p.fk_pays, p.priv, p.datec as date_creation, p.tms as date_update,";
$sql.= " co.code as country_code";
// Add fields from extrafields
foreach ($extrafields->attribute_label as $key => $val) $sql.=($extrafields->attribute_type[$key] != 'separate' ? ",ef.".$key.' as options_'.$key : '');
// Add fields from hooks
$parameters=array();
$reshook=$hookmanager->executeHooks('printFieldListSelect',$parameters);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;
$sql.= " FROM ".MAIN_DB_PREFIX."socpeople as p";
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label)) $sql.= " LEFT JOIN ".MAIN_DB_PREFIX."socpeople_extrafields as ef on (p.rowid = ef.fk_object)";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."c_country as co ON co.rowid = p.fk_pays";
$sql.= " LEFT JOIN ".MAIN_DB_PREFIX."societe as s ON s.rowid = p.fk_soc";
if (! empty($search_categ)) $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX."categorie_contact as cc ON p.rowid = cc.fk_socpeople"; // We need this table joined to the select in order to filter by categ
if (! empty($search_categ_thirdparty)) $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX."categorie_societe as cs ON s.rowid = cs.fk_soc";       // We need this table joined to the select in order to filter by categ
if (! empty($search_categ_supplier)) $sql.= ' LEFT JOIN '.MAIN_DB_PREFIX."categorie_fournisseur as cs2 ON s.rowid = cs2.fk_soc";       // We need this table joined to the select in order to filter by categ
if (!$user->rights->societe->client->voir && !$socid) $sql .= " LEFT JOIN ".MAIN_DB_PREFIX."societe_commerciaux as sc ON s.rowid = sc.fk_soc";
$sql.= ' WHERE p.entity IN ('.getEntity('societe').')';
if (!$user->rights->societe->client->voir && !$socid) //restriction
{
	$sql .= " AND (sc.fk_user = " .$user->id." OR p.fk_soc IS NULL)";
}
if (! empty($userid))    // propre au commercial
{
    $sql .= " AND p.fk_user_creat=".$db->escape($userid);
}

// Filter to exclude not owned private contacts
if ($search_priv != '0' && $search_priv != '1')
{
	$sql .= " AND (p.priv='0' OR (p.priv='1' AND p.fk_user_creat=".$user->id."))";
}
else
{
	if ($search_priv == '0') $sql .= " AND p.priv='0'";
	if ($search_priv == '1') $sql .= " AND (p.priv='1' AND p.fk_user_creat=".$user->id.")";
}

if ($search_categ > 0)   $sql.= " AND cc.fk_categorie = ".$db->escape($search_categ);
if ($search_categ == -2) $sql.= " AND cc.fk_categorie IS NULL";
if ($search_categ_thirdparty > 0)   $sql.= " AND cs.fk_categorie = ".$db->escape($search_categ_thirdparty);
if ($search_categ_thirdparty == -2) $sql.= " AND cs.fk_categorie IS NULL";
if ($search_categ_supplier > 0)     $sql.= " AND cs2.fk_categorie = ".$db->escape($search_categ_supplier);
if ($search_categ_supplier == -2)   $sql.= " AND cs2.fk_categorie IS NULL";

if ($sall)                          $sql.= natural_search(array_keys($fieldstosearchall), $sall);
if (strlen($search_phone))          $sql.= natural_search(array('p.phone', 'p.phone_perso', 'p.phone_mobile'), $search_phone);
if (strlen($search_cti))            $sql.= natural_search(array('p.phone', 'p.phone_perso', 'p.phone_mobile'), $search_cti);
if (strlen($search_firstlast_only)) $sql.= natural_search(array('p.lastname', 'p.firstname'), $search_firstlast_only);

if ($search_lastname)               $sql.= natural_search('p.lastname', $search_lastname);
if ($search_firstname)              $sql.= natural_search('p.firstname', $search_firstname);
if ($search_societe)                $sql.= natural_search('s.nom', $search_societe);
if (strlen($search_poste))          $sql.= natural_search('p.poste', $search_poste);
if (strlen($search_phone_perso))    $sql.= natural_search('p.phone_perso', $search_phone_perso);
if (strlen($search_phone_pro))      $sql.= natural_search('p.phone', $search_phone);
if (strlen($search_phone_mobile))   $sql.= natural_search('p.phone_mobile', $search_phone_mobile);
if (strlen($search_fax))            $sql.= natural_search('p.phone_fax', $search_fax);
if (strlen($search_skype))          $sql.= natural_search('p.skype', $search_skype);
if (strlen($search_email))          $sql.= natural_search('p.email', $search_email);
if ($search_status != '' && $search_status >= 0) $sql.= " AND p.statut = ".$db->escape($search_status);
if ($type == "o")        // filtre sur type
{
    $sql .= " AND p.fk_soc IS NULL";
}
else if ($type == "f")        // filtre sur type
{
    $sql .= " AND s.fournisseur = 1";
}
else if ($type == "c")        // filtre sur type
{
    $sql .= " AND s.client IN (1, 3)";
}
else if ($type == "p")        // filtre sur type
{
    $sql .= " AND s.client IN (2, 3)";
}
if (! empty($socid))
{
    $sql .= " AND s.rowid = ".$socid;
}
// Add where from extra fields
foreach ($search_array_options as $key => $val)
{
    $crit=$val;
    $tmpkey=preg_replace('/search_options_/','',$key);
    $typ=$extrafields->attribute_type[$tmpkey];
    $mode=0;
    if (in_array($typ, array('int','double','real'))) $mode=1;    							// Search on a numeric
    if (in_array($typ, array('sellist')) && $crit != '0' && $crit != '-1') $mode=2;    		// Search on a foreign key int
    if ($crit != '' && (! in_array($typ, array('select','sellist')) || $crit != '0'))
    {
        $sql .= natural_search('ef.'.$tmpkey, $crit, $mode);
    }
}
// Add where from hooks
$parameters=array();
$reshook=$hookmanager->executeHooks('printFieldListWhere',$parameters);    // Note that $action and $object may have been modified by hook
$sql.=$hookmanager->resPrint;
// Add order
if ($view == "recent")
{
    $sql.= $db->order("p.datec","DESC");
}
else
{
    $sql.= $db->order($sortfield,$sortorder);
}

// Count total nb of records
$nbtotalofrecords = '';
if (empty($conf->global->MAIN_DISABLE_FULL_SCANLIST))
{
    $result = $db->query($sql);
    $nbtotalofrecords = $db->num_rows($result);
}

$sql.= $db->plimit($limit+1, $offset);

$result = $db->query($sql);
if (! $result)
{
    dol_print_error($db);
    exit;
}

$num = $db->num_rows($result);

$arrayofselected=is_array($toselect)?$toselect:array();

if ($num == 1 && ! empty($conf->global->MAIN_SEARCH_DIRECT_OPEN_IF_ONLY_ONE) && ($sall != '' || $seearch_cti != ''))
{
    $obj = $db->fetch_object($resql);
    $id = $obj->rowid;
    header("Location: ".DOL_URL_ROOT.'/contact/card.php?id='.$id);
    exit;
}

$help_url='EN:Module_Third_Parties|FR:Module_Tiers|ES:M&oacute;dulo_Empresas';
llxHeader('',$title,$help_url);

$param='';
if (! empty($contextpage) && $contextpage != $_SERVER["PHP_SELF"]) $param.='&contextpage='.$contextpage;
if ($limit > 0 && $limit != $conf->liste_limit) $param.='&limit='.$limit;
$param.='&begin='.urlencode($begin).'&view='.urlencode($view).'&userid='.urlencode($userid).'&contactname='.urlencode($sall);
$param.='&type='.urlencode($type).'&view='.urlencode($view);
if (!empty($search_categ)) $param.='&search_categ='.urlencode($search_categ);
if (!empty($search_categ_thirdparty)) $param.='&search_categ_thirdparty='.urlencode($search_categ_thirdparty);
if (!empty($search_categ_supplier)) $param.='&search_categ_supplier='.urlencode($search_categ_supplier);
if ($sall != '') $param.='&amp;sall='.urlencode($sall);
if ($search_lastname != '') $param.='&amp;search_lastname='.urlencode($search_lastname);
if ($search_firstname != '') $param.='&amp;search_firstname='.urlencode($search_firstname);
if ($search_societe != '') $param.='&amp;search_societe='.urlencode($search_societe);
if ($search_zip != '') $param.='&amp;search_zip='.urlencode($search_zip);
if ($search_town != '') $param.='&amp;search_town='.urlencode($search_town);
if ($search_job != '') $param.='&amp;search_job='.urlencode($search_job);
if ($search_phone_pro != '') $param.='&amp;search_phone_pro='.urlencode($search_phone_pro);
if ($search_phone_perso != '') $param.='&amp;search_phone_perso='.urlencode($search_phone_perso);
if ($search_phone_mobile != '') $param.='&amp;search_phone_mobile='.urlencode($search_phone_mobile);
if ($search_fax != '') $param.='&amp;search_fax='.urlencode($search_fax);
if ($search_email != '') $param.='&amp;search_email='.urlencode($search_email);
if ($search_status != '') $param.='&amp;search_status='.urlencode($search_status);
if ($search_priv == '0' || $search_priv == '1') $param.="&search_priv=".urlencode($search_priv);
if ($optioncss != '') $param.='&optioncss='.$optioncss;
// Add $param from extra fields
foreach ($search_array_options as $key => $val)
{
    $crit=$val;
    $tmpkey=preg_replace('/search_options_/','',$key);
    if ($val != '') $param.='&search_options_'.$tmpkey.'='.urlencode($val);
}

// List of mass actions available
$arrayofmassactions =  array(
//    'presend'=>$langs->trans("SendByMail"),
//    'builddoc'=>$langs->trans("PDFMerge"),
);
//if($user->rights->societe->creer) $arrayofmassactions['createbills']=$langs->trans("CreateInvoiceForThisCustomer");
if ($user->rights->societe->supprimer) $arrayofmassactions['delete']=$langs->trans("Delete");
if ($massaction == 'presend') $arrayofmassactions=array();
$massactionbutton=$form->selectMassAction('', $arrayofmassactions);

print '<form method="post" action="'.$_SERVER["PHP_SELF"].'" name="formfilter">';
if ($optioncss != '') print '<input type="hidden" name="optioncss" value="'.$optioncss.'">';
print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
print '<input type="hidden" name="formfilteraction" id="formfilteraction" value="list">';
print '<input type="hidden" name="sortfield" value="'.$sortfield.'">';
print '<input type="hidden" name="sortorder" value="'.$sortorder.'">';
print '<input type="hidden" name="page" value="'.$page.'">';
print '<input type="hidden" name="view" value="'.dol_escape_htmltag($view).'">';

print_barre_liste($titre, $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, $massactionbutton, $num, $nbtotalofrecords, 'title_companies.png', 0, '', '', $limit);

if ($sall)
{
    foreach($fieldstosearchall as $key => $val) $fieldstosearchall[$key]=$langs->trans($val);
    print $langs->trans("FilterOnInto", $sall) . join(', ',$fieldstosearchall);
}
if ($search_firstlast_only)
{
    print $langs->trans("FilterOnInto", $search_firstlast_only) . $langs->trans("Lastname").", ".$langs->trans("Firstname");
}

$moreforfilter='';
if (! empty($conf->categorie->enabled))
{
	require_once DOL_DOCUMENT_ROOT . '/categories/class/categorie.class.php';
    $moreforfilter.='<div class="divsearchfield">';
	$moreforfilter.=$langs->trans('Categories'). ': ';
	$moreforfilter.=$formother->select_categories(Categorie::TYPE_CONTACT,$search_categ,'search_categ',1);
	$moreforfilter.='</div>';
	if (empty($type) || $type == 'c' || $type == 'p')
	{
        $moreforfilter.='<div class="divsearchfield">';
        if ($type == 'c') $moreforfilter.=$langs->trans('CustomersCategoriesShort'). ': ';
    	else if ($type == 'p') $moreforfilter.=$langs->trans('ProspectsCategoriesShort'). ': ';
    	else $moreforfilter.=$langs->trans('CustomersProspectsCategoriesShort'). ': ';
    	$moreforfilter.=$formother->select_categories(Categorie::TYPE_CUSTOMER,$search_categ_thirdparty,'search_categ_thirdparty',1);
    	$moreforfilter.='</div>';
	}
	if (empty($type) || $type == 'f')
	{
    	$moreforfilter.='<div class="divsearchfield">';
		$moreforfilter.=$langs->trans('SuppliersCategoriesShort'). ': ';
    	$moreforfilter.=$formother->select_categories(Categorie::TYPE_SUPPLIER,$search_categ_supplier,'search_categ_supplier',1);
    	$moreforfilter.='</div>';
	}
}
if ($moreforfilter)
{
	print '<div class="liste_titre liste_titre_bydiv centpercent">';
	print $moreforfilter;
	$parameters=array('type'=>$type);
	$reshook=$hookmanager->executeHooks('printFieldPreListTitle',$parameters);    // Note that $action and $object may have been modified by hook
    print $hookmanager->resPrint;
	print '</div>';
}

$varpage=empty($contextpage)?$_SERVER["PHP_SELF"]:$contextpage;
$selectedfields=$form->multiSelectArrayWithCheckbox('selectedfields', $arrayfields, $varpage);	// This also change content of $arrayfields
if ($massactionbutton) $selectedfields.=$form->showCheckAddButtons('checkforselect', 1);

print '<div class="div-table-responsive">';
print '<table class="tagtable liste'.($moreforfilter?" listwithfilterbefore":"").'">'."\n";

// Lines for filter fields
print '<tr class="liste_titre_filter">';
if (! empty($arrayfields['p.lastname']['checked']))
{
    print '<td class="liste_titre">';
    print '<input class="flat" type="text" name="search_lastname" size="6" value="'.dol_escape_htmltag($search_lastname).'">';
    print '</td>';
}
if (! empty($arrayfields['p.firstname']['checked']))
{
    print '<td class="liste_titre">';
    print '<input class="flat" type="text" name="search_firstname" size="6" value="'.dol_escape_htmltag($search_firstname).'">';
    print '</td>';
}
if (! empty($arrayfields['p.poste']['checked']))
{
    print '<td class="liste_titre">';
    print '<input class="flat" type="text" name="search_poste" size="5" value="'.dol_escape_htmltag($search_poste).'">';
    print '</td>';
}
if (! empty($arrayfields['p.zip']['checked']))
{
    print '<td class="liste_titre">';
    print '<input class="flat" type="text" name="search_zip" size="3" value="'.dol_escape_htmltag($search_zip).'">';
    print '</td>';
}
if (! empty($arrayfields['p.town']['checked']))
{
    print '<td class="liste_titre">';
    print '<input class="flat" type="text" name="search_town" size="5" value="'.dol_escape_htmltag($search_town).'">';
    print '</td>';
}
if (! empty($arrayfields['p.phone']['checked']))
{
    print '<td class="liste_titre">';
    print '<input class="flat" type="text" name="search_phone_pro" size="6" value="'.dol_escape_htmltag($search_phone_pro).'">';
    print '</td>';
}
if (! empty($arrayfields['p.phone_perso']['checked']))
{
    print '<td class="liste_titre">';
    print '<input class="flat" type="text" name="search_phone_perso" size="6" value="'.dol_escape_htmltag($search_phone_perso).'">';
    print '</td>';
}
if (! empty($arrayfields['p.phone_mobile']['checked']))
{
    print '<td class="liste_titre">';
    print '<input class="flat" type="text" name="search_phone_mobile" size="6" value="'.dol_escape_htmltag($search_phone_mobile).'">';
    print '</td>';
}
if (! empty($arrayfields['p.fax']['checked']))
{
    print '<td class="liste_titre">';
    print '<input class="flat" type="text" name="search_fax" size="6" value="'.dol_escape_htmltag($search_fax).'">';
    print '</td>';
}
if (! empty($arrayfields['p.email']['checked']))
{
    print '<td class="liste_titre">';
    print '<input class="flat" type="text" name="search_email" size="6" value="'.dol_escape_htmltag($search_email).'">';
    print '</td>';
}
if (! empty($arrayfields['p.skype']['checked']))
{
    print '<td class="liste_titre">';
    print '<input class="flat" type="text" name="search_skype" size="6" value="'.dol_escape_htmltag($search_skype).'">';
    print '</td>';
}
if (! empty($arrayfields['p.thirdparty']['checked']))
{
    print '<td class="liste_titre">';
    print '<input class="flat" type="text" name="search_societe" size="8" value="'.dol_escape_htmltag($search_societe).'">';
    print '</td>';
}
if (! empty($arrayfields['p.priv']['checked']))
{
    print '<td class="liste_titre" align="center">';
   $selectarray=array('0'=>$langs->trans("ContactPublic"),'1'=>$langs->trans("ContactPrivate"));
   print $form->selectarray('search_priv',$selectarray,$search_priv,1);
   print '</td>';
}
// Extra fields
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
{
   foreach($extrafields->attribute_label as $key => $val)
   {
		if (! empty($arrayfields["ef.".$key]['checked']))
		{
            $align=$extrafields->getAlignFlag($key);
            $typeofextrafield=$extrafields->attribute_type[$key];
            print '<td class="liste_titre'.($align?' '.$align:'').'">';
		    if (in_array($typeofextrafield, array('varchar', 'int', 'double', 'select')) && empty($extrafields->attribute_computed[$key]))
			{
			    $crit=$val;
				$tmpkey=preg_replace('/search_options_/','',$key);
				$searchclass='';
				if (in_array($typeofextrafield, array('varchar', 'select'))) $searchclass='searchstring';
				if (in_array($typeofextrafield, array('int', 'double'))) $searchclass='searchnum';
				print '<input class="flat'.($searchclass?' '.$searchclass:'').'" size="4" type="text" name="search_options_'.$tmpkey.'" value="'.dol_escape_htmltag($search_array_options['search_options_'.$tmpkey]).'">';
			}
			print '</td>';
		}
   }
}
// Fields from hook
$parameters=array('arrayfields'=>$arrayfields);
$reshook=$hookmanager->executeHooks('printFieldListOption',$parameters);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;
// Date creation
if (! empty($arrayfields['p.datec']['checked']))
{
    print '<td class="liste_titre">';
    print '</td>';
}
// Date modification
if (! empty($arrayfields['p.tms']['checked']))
{
    print '<td class="liste_titre">';
    print '</td>';
}
// Status
if (! empty($arrayfields['p.statut']['checked']))
{
    print '<td class="liste_titre" align="center">';
    print $form->selectarray('search_status', array('-1'=>'', '0'=>$langs->trans('ActivityCeased'),'1'=>$langs->trans('InActivity')),$search_status);
    print '</td>';
}
// Action column
print '<td class="liste_titre" align="right">';
$searchpicto=$form->showFilterButtons();
print $searchpicto;
print '</td>';

print '</tr>';

// Ligne des titres
print '<tr class="liste_titre">';
if (! empty($arrayfields['p.lastname']['checked']))            print_liste_field_titre("Lastname",$_SERVER["PHP_SELF"],"p.lastname", $begin, $param, '', $sortfield,$sortorder);
if (! empty($arrayfields['p.firstname']['checked']))            print_liste_field_titre("Firstname",$_SERVER["PHP_SELF"],"p.firstname", $begin, $param, '', $sortfield,$sortorder);
if (! empty($arrayfields['p.zip']['checked']))            print_liste_field_titre("Zip",$_SERVER["PHP_SELF"],"p.zip", $begin, $param, '', $sortfield,$sortorder);
if (! empty($arrayfields['p.town']['checked']))            print_liste_field_titre("Town",$_SERVER["PHP_SELF"],"p.town", $begin, $param, '', $sortfield,$sortorder);
if (! empty($arrayfields['p.poste']['checked']))            print_liste_field_titre("PostOrFunction",$_SERVER["PHP_SELF"],"p.poste", $begin, $param, '', $sortfield,$sortorder);
if (! empty($arrayfields['p.phone']['checked']))            print_liste_field_titre("Phone",$_SERVER["PHP_SELF"],"p.phone", $begin, $param, '', $sortfield,$sortorder);
if (! empty($arrayfields['p.phone_perso']['checked']))            print_liste_field_titre("PhonePerso",$_SERVER["PHP_SELF"],"p.phone_perso", $begin, $param, '', $sortfield,$sortorder);
if (! empty($arrayfields['p.phone_mobile']['checked']))            print_liste_field_titre("PhoneMobile",$_SERVER["PHP_SELF"],"p.phone_mobile", $begin, $param, '', $sortfield,$sortorder);
if (! empty($arrayfields['p.fax']['checked']))            print_liste_field_titre("Fax",$_SERVER["PHP_SELF"],"p.fax", $begin, $param, '', $sortfield,$sortorder);
if (! empty($arrayfields['p.email']['checked']))            print_liste_field_titre("EMail",$_SERVER["PHP_SELF"],"p.email", $begin, $param, '', $sortfield,$sortorder);
if (! empty($arrayfields['p.skype']['checked']))            print_liste_field_titre("Skype",$_SERVER["PHP_SELF"],"p.skype", $begin, $param, '', $sortfield,$sortorder);
if (! empty($arrayfields['p.thirdparty']['checked']))            print_liste_field_titre("ThirdParty",$_SERVER["PHP_SELF"],"s.nom", $begin, $param, '', $sortfield,$sortorder);
if (! empty($arrayfields['p.priv']['checked']))            print_liste_field_titre("ContactVisibility",$_SERVER["PHP_SELF"],"p.priv", $begin, $param, 'align="center"', $sortfield,$sortorder);
// Extra fields
if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
{
   foreach($extrafields->attribute_label as $key => $val)
   {
       if (! empty($arrayfields["ef.".$key]['checked']))
       {
			$align=$extrafields->getAlignFlag($key);
			$sortonfield = "ef.".$key;
			if (! empty($extrafields->attribute_computed[$key])) $sortonfield='';
			print_liste_field_titre($extralabels[$key],$_SERVER["PHP_SELF"],$sortonfield,"",$param,($align?'align="'.$align.'"':''),$sortfield,$sortorder);
       }
   }
}
// Hook fields
$parameters=array('arrayfields'=>$arrayfields,'param'=>$param,'sortfield'=>$sortfield,'sortorder'=>$sortorder);
$reshook=$hookmanager->executeHooks('printFieldListTitle',$parameters);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;
if (! empty($arrayfields['p.datec']['checked']))  print_liste_field_titre("DateCreationShort",$_SERVER["PHP_SELF"],"p.datec","",$param,'align="center" class="nowrap"',$sortfield,$sortorder);
if (! empty($arrayfields['p.tms']['checked']))    print_liste_field_titre("DateModificationShort",$_SERVER["PHP_SELF"],"p.tms","",$param,'align="center" class="nowrap"',$sortfield,$sortorder);
if (! empty($arrayfields['p.statut']['checked'])) print_liste_field_titre("Status",$_SERVER["PHP_SELF"],"p.statut","",$param,'align="center"',$sortfield,$sortorder);
print_liste_field_titre($selectedfields, $_SERVER["PHP_SELF"],"",'','','align="center"',$sortfield,$sortorder,'maxwidthsearch ');
print "</tr>\n";


$i = 0;
$totalarray=array();
while ($i < min($num,$limit))
{
    $obj = $db->fetch_object($result);

    print '<tr class="oddeven">';

	$contactstatic->lastname=$obj->lastname;
	$contactstatic->firstname='';
	$contactstatic->id=$obj->rowid;
	$contactstatic->statut=$obj->statut;
	$contactstatic->poste=$obj->poste;
	$contactstatic->email=$obj->email;
	$contactstatic->phone_pro=$obj->phone_pro;
	$contactstatic->phone_perso=$obj->phone_perso;
	$contactstatic->phone_mobile=$obj->phone_mobile;
	$contactstatic->zip=$obj->zip;
	$contactstatic->town=$obj->town;

    // Name
    if (! empty($arrayfields['p.lastname']['checked']))
    {
        print '<td valign="middle">';
        print $contactstatic->getNomUrl(1,'',0);
        print '</td>';
	    if (! $i) $totalarray['nbfield']++;
    }
	// Firstname
    if (! empty($arrayfields['p.firstname']['checked']))
    {
        print '<td>'.$obj->firstname.'</td>';
	    if (! $i) $totalarray['nbfield']++;
    }
	// Zip
    if (! empty($arrayfields['p.zip']['checked']))
    {
        print '<td>'.$obj->zip.'</td>';
	    if (! $i) $totalarray['nbfield']++;
    }
	// Town
    if (! empty($arrayfields['p.town']['checked']))
    {
        print '<td>'.$obj->town.'</td>';
	    if (! $i) $totalarray['nbfield']++;
    }
    // Function
    if (! empty($arrayfields['p.poste']['checked']))
    {
        print '<td>'.dol_trunc($obj->poste,20).'</td>';
	    if (! $i) $totalarray['nbfield']++;
    }
    // Phone
    if (! empty($arrayfields['p.phone']['checked']))
    {
        print '<td>'.dol_print_phone($obj->phone_pro,$obj->country_code,$obj->rowid,$obj->socid,'AC_TEL').'</td>';
	    if (! $i) $totalarray['nbfield']++;
    }
    // Phone perso
    if (! empty($arrayfields['p.phone_perso']['checked']))
    {
        print '<td>'.dol_print_phone($obj->phone_perso,$obj->country_code,$obj->rowid,$obj->socid,'AC_TEL').'</td>';
	    if (! $i) $totalarray['nbfield']++;
    }
    // Phone mobile
    if (! empty($arrayfields['p.phone_mobile']['checked']))
    {
        print '<td>'.dol_print_phone($obj->phone_mobile,$obj->country_code,$obj->rowid,$obj->socid,'AC_TEL').'</td>';
	    if (! $i) $totalarray['nbfield']++;
    }
    // Fax
    if (! empty($arrayfields['p.fax']['checked']))
    {
        print '<td>'.dol_print_phone($obj->fax,$obj->country_code,$obj->rowid,$obj->socid,'AC_TEL').'</td>';
	    if (! $i) $totalarray['nbfield']++;
    }
    // EMail
    if (! empty($arrayfields['p.email']['checked']))
    {
        print '<td>'.dol_print_email($obj->email,$obj->rowid,$obj->socid,'AC_EMAIL',18).'</td>';
	    if (! $i) $totalarray['nbfield']++;
    }
    // Skype
    if (! empty($arrayfields['p.skype']['checked']))
    {
        if (! empty($conf->skype->enabled)) { print '<td>'.dol_print_skype($obj->skype,$obj->rowid,$obj->socid,'AC_SKYPE',18).'</td>'; }
	    if (! $i) $totalarray['nbfield']++;
    }
    // Company
    if (! empty($arrayfields['p.thirdparty']['checked']))
    {
		print '<td>';
        if ($obj->socid)
        {
		$objsoc = new Societe($db);
		$objsoc->fetch($obj->socid);
		print $objsoc->getNomUrl(1);
        }
        else
            print '&nbsp;';
        print '</td>';
	    if (! $i) $totalarray['nbfield']++;
    }

    // Private/Public
    if (! empty($arrayfields['p.priv']['checked']))
    {
	    print '<td align="center">'.$contactstatic->LibPubPriv($obj->priv).'</td>';
	    if (! $i) $totalarray['nbfield']++;
    }

	// Extra fields
	if (is_array($extrafields->attribute_label) && count($extrafields->attribute_label))
	{
	   foreach($extrafields->attribute_label as $key => $val)
	   {
			if (! empty($arrayfields["ef.".$key]['checked']))
			{
				print '<td';
				$align=$extrafields->getAlignFlag($key);
				if ($align) print ' align="'.$align.'"';
				print '>';
				$tmpkey='options_'.$key;
				print $extrafields->showOutputField($key, $obj->$tmpkey, '', 1);
				print '</td>';
	            if (! $i) $totalarray['nbfield']++;
			}
	   }
	}
    // Fields from hook
    $parameters=array('arrayfields'=>$arrayfields, 'obj'=>$obj);
	$reshook=$hookmanager->executeHooks('printFieldListValue',$parameters);    // Note that $action and $object may have been modified by hook
    print $hookmanager->resPrint;
    // Date creation
    if (! empty($arrayfields['p.datec']['checked']))
    {
        print '<td align="center">';
        print dol_print_date($db->jdate($obj->date_creation), 'dayhour');
        print '</td>';
	    if (! $i) $totalarray['nbfield']++;
    }
    // Date modification
    if (! empty($arrayfields['p.tms']['checked']))
    {
        print '<td align="center">';
        print dol_print_date($db->jdate($obj->date_update), 'dayhour');
        print '</td>';
	    if (! $i) $totalarray['nbfield']++;
    }
    // Status
    if (! empty($arrayfields['p.statut']['checked']))
    {
        print '<td align="center">'.$contactstatic->getLibStatut(3).'</td>';
	    if (! $i) $totalarray['nbfield']++;
    }

   // Action column
    print '<td class="nowrap" align="center">';
    if ($massactionbutton || $massaction)   // If we are in select mode (massactionbutton defined) or if we have already selected and sent an action ($massaction) defined
    {
        $selected=0;
		if (in_array($obj->rowid, $arrayofselected)) $selected=1;
		print '<input id="cb'.$obj->rowid.'" class="flat checkforselect" type="checkbox" name="toselect[]" value="'.$obj->rowid.'"'.($selected?' checked="checked"':'').'>';
    }
    print '</td>';
    if (! $i) $totalarray['nbfield']++;

    print "</tr>\n";
    $i++;
}

$db->free($result);

$parameters=array('arrayfields'=>$arrayfields, 'sql'=>$sql);
$reshook=$hookmanager->executeHooks('printFieldListFooter',$parameters);    // Note that $action and $object may have been modified by hook
print $hookmanager->resPrint;

print "</table>";
print "</div>";

//if ($num > $limit || $page) print_barre_liste('', $page, $_SERVER["PHP_SELF"], $param, $sortfield, $sortorder, '', $num, $nbtotalofrecords, 'title_companies.png', 0, '', '', $limit, 1);

print '</form>';


llxFooter();
$db->close();

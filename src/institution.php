<?php

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'manageinstitutions/obf');
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'obf');
define('SECTION_PAGE', 'index');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(dirname(__FILE__) . '/lib.php');
require_once('pieforms/pieform.php');
require_once('institution.php');

define('TITLE', get_string('openbadgefactory', 'interaction.obf'));

$institutionparam = param_alphanum('institution', '');
$authenticated = PluginInteractionObf::is_authenticated($institutionparam);
$subpages = array('settings');

if ($authenticated) {
    $subpages[] = 'privileges';
    $subpages[] = 'badges';
}

$paramtype = param_alpha('type', 'settings');
$type = !in_array($paramtype, $subpages) ? 'settings' : $paramtype;
$selector = institution_selector_for_page($institutionparam,
        get_config('wwwroot') . 'interaction/obf/institution.php?type=' . $type);
$content = '';
$institution = $selector['institution'];
$currentpath = '/interaction/obf/institution.php?institution=' . $institution . '&type='
        . $type;

if (empty($institution)) {
    $smarty = smarty();
    $smarty->display('admin/users/noinstitutions.tpl');
    exit;
}

switch ($type) {
    case 'settings':
        $content = PluginInteractionObf::get_settings_form($institution);
        break;
    case 'privileges':
        $content = PluginInteractionObf::get_privileges_form($institution);
        break;
    case 'badges':
        $content = PluginInteractionObf::get_badgelist($institution);
        break;
}

/**
 * 
 * @global Session $SESSION
 * @param Pieform $form
 * @param type $values
 */
function token_submit(Pieform $form, $values) {
    global $SESSION, $institution, $currentpath;

    try {
        PluginInteractionObf::authenticate($institution, $values['token']);
        $SESSION->add_ok_msg(get_string('authenticationsuccessful',
                        'interaction.obf'));
    } catch (Exception $ex) {
        $SESSION->add_error_msg($ex->getMessage());
    }

    redirect($currentpath);
}

function institutionissuers_submit(Pieform $form, $values) {
    global $institution, $SESSION, $currentpath;

    try {
        PluginInteractionObf::save_institution_issuers($institution, $values['users']);
        $SESSION->add_ok_msg(get_string('institutionissuersupdated',
                        'interaction.obf'));
    } catch (Exception $ex) {
        $SESSION->add_error_msg($ex->getMessage());
    }

    redirect($currentpath);
}

$cssfiles = $THEME->get_url('style/style.css', true,
        SECTION_PLUGINTYPE . '/' . SECTION_PLUGINNAME);
$cssfilesmodified = array();

// Let's add our own suffix to the theme name so that our styles don't override
// the admin styles using the same theme.
foreach ($cssfiles as $theme => $sheet) {
    $cssfilesmodified[$theme . '_obf'] = $sheet;
}

$smarty = smarty(array('/interaction/obf/js/obf.js'));

// A small hack here. The plugin stylesheet gets overridden by admin styles,
// so we need to add them manually.
$smarty->assign('STYLESHEETLIST',
        array_merge($smarty->get_template_vars('STYLESHEETLIST'),
                $cssfilesmodified));
$smarty->assign('institutionselector', $selector['institutionselector']);
$smarty->assign('content', $content);
$smarty->assign('type', $type);
$smarty->assign('subpages', $subpages);
$smarty->assign('institution', $institution);
$smarty->assign('INLINEJAVASCRIPT', $selector['institutionselectorjs']);
$smarty->display('interaction:obf:institution.tpl');

<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 *
 * @package    mahara
 * @subpackage interaction-obf
 * @author     Discendum Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) Discendum Ltd http://discendum.com
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('MENUITEM', 'supervisor/obf');
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'obf');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('lib.php');
require_once('pieforms/pieform.php');
require_once('institution.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/local/institution/lib.php');

define('TITLE', get_string('openbadgefactory', 'interaction.obf'));

$authenticated = false;
$institution = param_alphanum('institution', '');
$available = get_records_sql_menu(
        "SELECT i.name, i.displayname from {institution} i
    INNER JOIN {usr_institution} ui ON i.name = ui.institution
    WHERE ui.usr = ? AND ui.supervisor = 1
    ORDER BY i.displayname", array($USER->get('id'))
);

if (empty($available)) {
    throw new AccessDeniedException('');
}

if (empty($institution)) {
    $institution = current(array_keys($available));
}

try {
    $authenticated = PluginInteractionObf::is_authenticated($institution);
} catch (Exception $exc) {
    $content = PluginInteractionObf::get_error_template($exc->getMessage());
}

$subpages = array('settings');

if ($authenticated) {
    $subpages[] = 'privileges';
    $subpages[] = 'badges';
}

$paramtype = param_alpha('page', 'settings');
$page = !in_array($paramtype, $subpages) ? 'settings' : $paramtype;
$selector = supervisor\institution_selector($institution);
$content = '';
$currentpath = '/interaction/obf/supervisor.php?institution=' . $institution . '&page='
        . $page;

if (empty($institution)) {
    $smarty = smarty();
    $smarty->display('admin/users/noinstitutions.tpl');
    exit;
}

if (empty($content)) {
    switch ($page) {
        case 'settings':
            $content = PluginInteractionObf::get_settings_form($institution);
            break;
        case 'privileges':
            $content = PluginInteractionObf::get_privileges_form($institution);
            break;
        case 'badges':
            $content = PluginInteractionObf::get_badgelist($institution, null,
                            'supervisor');
            break;
    }
}

$cssfiles = $THEME->get_url('style/style.css', true,
        SECTION_PLUGINTYPE . '/' . SECTION_PLUGINNAME);
$cssfilesmodified = array();

// Let's add our own suffix to the theme name so that our styles don't override
// the admin styles using the same theme.
foreach ($cssfiles as $theme => $sheet) {
    $cssfilesmodified[$theme . '_obf'] = $sheet;
}

$js = $selector;
$js .= <<<JS
       \$j(document).ready(function() {
           \$j('#sub-nav li.badges').addClass('selected');
       });
JS;
$smarty = smarty(array('/interaction/obf/js/obf.js'));

// A small hack here. The plugin stylesheet gets overridden by admin styles,
// so we need to add them manually.
$smarty->assign('STYLESHEETLIST',
        array_merge($smarty->get_template_vars('STYLESHEETLIST'),
                $cssfilesmodified));
//$smarty->assign('institutionselector', $selector['institutionselector']);
$smarty->assign('content', $content);
$smarty->assign('page', $page);
$smarty->assign('subpages', $subpages);
$smarty->assign('institution', $institution);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('interaction:obf:supervisor.tpl');

/**
 * 
 * @global Session $SESSION
 * @param Pieform $form
 * @param type $values
 */
function token_submit(Pieform $form, $values) {
    global $SESSION, $institution, $currentpath, $USER;

    try {
        if (!PluginInteractionObf::user_is_supervisor_of($institution)) {
            throw new Exception(get_string('notadminforinstitution',
                    'interaction.obf'));
        }

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
        if (!PluginInteractionObf::user_is_supervisor_of($institution)) {
            throw new Exception(get_string('notadminforinstitution',
                    'interaction.obf'));
        }

        PluginInteractionObf::save_institution_issuers($institution,
                $values['users']);
        $SESSION->add_ok_msg(get_string('institutionissuersupdated',
                        'interaction.obf'));
    } catch (Exception $ex) {
        $SESSION->add_error_msg($ex->getMessage());
    }

    redirect($currentpath);
}

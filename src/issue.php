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
define('MENUITEM', 'groups/badges');
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'obf');

require(dirname(dirname(dirname(__FILE__)))) . '/init.php';
require('lib.php');
require_once('group.php');

define('GROUP', param_integer('group'));

$group = group_current_group();

define('TITLE', $group->name . ' - ' . get_string('badges', 'interaction.obf'));

$badgeid = param_variable('badgeid');
$institution = PluginInteractionObf::get_group_institution(GROUP);
$currentpath = '/interaction/obf/issue.php?badgeid=' . $badgeid . '&group=' . GROUP;
$badge = PluginInteractionObf::get_badge($institution, $badgeid);

$pagestrings = array(
    'interaction.obf' => array(
        'issuetoall'
    )
);

$smarty = smarty(array(), array(), $pagestrings, array('sidebars' => false));
$smarty->assign('group', GROUP);

if ($badge !== false) {
    $smarty->assign('form',
            PluginInteractionObf::get_issuance_form($badge, $institution));
    $smarty->assign('badge', $badge);
    $smarty->assign('events',
            PluginInteractionObf::get_group_events(GROUP, $badgeid));
}

$smarty->display('interaction:obf:issue.tpl');

/**
 * 
 * @global type $badgeid
 * @param Pieform $form
 * @param type $values
 */
function issuance_validate(Pieform $form, $values) {
    global $badgeid;
    $badgeid = $values['badge'];
}

/**
 * 
 * @global type $institution
 * @global Session $SESSION
 * @param Pieform $form
 * @param type $values
 */
function issuance_submit(Pieform $form, $values) {
    global $SESSION, $currentpath, $USER;

    try {
        PluginInteractionObf::issue_badge($USER, GROUP, $values['badge'],
                $values['users'], $values['issued'], $values['expires'],
                $values['subject'], $values['body'], $values['footer']);

        $SESSION->add_ok_msg(get_string('badgesuccessfullyissued',
                        'interaction.obf'));
    } catch (RemoteServerException $e) {
        $SESSION->add_error_msg($e->getMessage());
    }

    redirect($currentpath);
}

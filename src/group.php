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
require_once('lib.php');
require_once('group.php');

define('GROUP', param_integer('id'));

$group = group_current_group();

if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException();
}

define('TITLE', $group->name . ' - ' . get_string('badges', 'interaction.obf'));

$section = 'interaction.obf';
$badge = null;
$currentpath = 'interaction/obf/group.php?id=' . GROUP;
$institutions = PluginInteractionObf::get_issuable_institutions($USER);
$content = '';
$subpages = array(
    'badges' => array('icon' => 'certificate'),
    'history' => array('icon' => 'trophy')
);

$paramtype = param_alpha('page', 'badges');
$page = !in_array($paramtype, array_keys($subpages)) ? 'badges' : $paramtype;
$offset = param_integer('offset', 0);

switch ($page) {
    case 'badges':
        $content = PluginInteractionObf::get_badgelist($institutions, GROUP);
        break;
    case 'history':
        $content = PluginInteractionObf::get_eventlist($institutions, GROUP,
                        $currentpath, $offset);
        break;
}

$smarty = smarty(array('interaction/obf/js/obf.js'), array(), array(),
        array('sidebars' => false));
$smarty->assign('group', GROUP);
$smarty->assign('page', $page);
$smarty->assign('subpages', $subpages);
$smarty->assign('content', $content);
$smarty->display('interaction:obf:group.tpl');

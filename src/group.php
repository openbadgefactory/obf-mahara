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

$institution = PluginInteractionObf::get_group_institution(GROUP);
$section = 'interaction.obf';
$badge = null;
$currentpath = 'interaction/obf/group.php?id=' . GROUP;
$badges = PluginInteractionObf::get_badges($institution);
$content = '';
$subpages = array('badges', 'history');
$paramtype = param_alpha('page', 'badges');
$page = !in_array($paramtype, $subpages) ? 'badges' : $paramtype;
$offset = param_integer('offset', 0);

if ($badges === false) {
    $content = false;
}
else {
    switch ($page) {
        case 'badges':
            $content = PluginInteractionObf::get_badgelist($institution, GROUP);
            break;
        case 'history':
            $eventcount = PluginInteractionObf::get_event_count($institution,
                            GROUP);
            $pagination = build_pagination(array(
                'url' => get_config('wwwroot') . $currentpath . '&page=history',
                'count' => $eventcount,
                'limit' => EVENTS_PER_PAGE,
                'offset' => $offset
            ));
            $events = PluginInteractionObf::get_group_events(GROUP, null,
                            $offset, EVENTS_PER_PAGE);
            $sm = smarty_core();
            $sm->assign('events', $events);
            $sm->assign('pagination', $pagination['html']);
            $content = $sm->fetch('interaction:obf:events.tpl');
            break;
    }
}
$smarty = smarty(array(), array(), array(), array('sidebars' => false));
$smarty->assign('group', GROUP);
$smarty->assign('page', $page);
$smarty->assign('subpages', $subpages);
$smarty->assign('content', $content);
$smarty->display('interaction:obf:group.tpl');

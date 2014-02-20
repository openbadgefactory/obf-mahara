<?php

define('INTERNAL', 1);
define('MENUITEM', 'groups/badges');
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'obf');

require(dirname(dirname(dirname(__FILE__)))) . '/init.php';
require_once(__DIR__ . '/lib.php');
require_once("group.php");

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
            $sm = smarty();
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

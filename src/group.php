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
$currentpath = '/interaction/obf/group.php?id=' . GROUP;
$badges = PluginInteractionObf::get_badges($institution);

$subpages = array('badges', 'history');
$paramtype = param_alpha('type', 'badges');
$type = !in_array($paramtype, $subpages) ? 'badges' : $paramtype;
$content = '';

switch ($type) {
    case 'badges':
        $content = PluginInteractionObf::get_badgelist($institution, GROUP);
        break;
    case 'history':
        $events = PluginInteractionObf::get_group_events(GROUP);
        $sm = smarty();
        $sm->assign('events', $events);
        $content = $sm->fetch('interaction:obf:events.tpl');
        break;
}

$smarty = smarty(array(), array(), array(), array('sidebars' => false));
$smarty->assign('group', GROUP);
$smarty->assign('type', $type);
$smarty->assign('subpages', $subpages);
$smarty->assign('content', $content);
$smarty->display('interaction:obf:group.tpl');

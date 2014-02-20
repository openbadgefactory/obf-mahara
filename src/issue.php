<?php

define('INTERNAL', 1);
define('MENUITEM', 'groups/badges');
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'obf');

require(dirname(dirname(dirname(__FILE__)))) . '/init.php';
require(dirname(__FILE__)) . '/lib.php';
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
    global $SESSION, $currentpath;

    try {
        PluginInteractionObf::issue_badge(GROUP, $values['badge'],
                $values['users'], $values['issued'], $values['expires'],
                $values['subject'], $values['body'], $values['footer']);

        $SESSION->add_ok_msg(get_string('badgesuccessfullyissued',
                        'interaction.obf'));
    } catch (RemoteServerException $e) {
        $SESSION->add_error_msg($e->getMessage());
    }

    redirect($currentpath);
}

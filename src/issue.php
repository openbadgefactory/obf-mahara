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
$section = 'interaction.obf';
$badge = PluginInteractionObf::get_badge($institution, $badgeid);
$expiresdefault = empty($badge->expires) ? null : strtotime('+ ' . $badge->expires . ' months');

// TODO: check privileges
$form = pieform(array(
    'name' => 'issuance',
    'renderer' => 'table',
    'method' => 'post',
    'elements' => array(
        'badge' => array(
            'type' => 'hidden',
            'value' => $badgeid,
            'rules' => array(
                'required' => true
            )
        ),
        'issuancedetails' => array(
            'type' => 'fieldset',
            'legend' => get_string('issuancedetails', $section),
            'elements' => array(
                'users' => array(
                    'type' => 'userlist',
                    'title' => get_string('recipients', $section),
                    'lefttitle' => get_string('groupmembers', $section),
                    'righttitle' => get_string('grouprecipients', $section),
                    'group' => GROUP,
                    'filter' => false,
                    'searchscript' => 'interaction/obf/userlist.json.php',
                    'rules' => array(
                        'required' => true
                    )
                ),
                'issued' => array(
                    'type' => 'date',
                    'minyear' => date('Y') - 1,
                    'title' => get_string('issuedat', $section),
                    'rules' => array(
                        'required' => true
                    )
                ),
                'expires' => array(
                    'minyear' => date('Y'),
                    'type' => 'date',
                    'defaultvalue' => $expiresdefault,
                    'title' => get_string('expiresat', $section)
                )
            )
        ),
        'email' => array(
            'type' => 'fieldset',
            'legend' => get_string('emailtemplate', $section),
            'collapsible' => true,
            'collapsed' => true,
            'elements' => PluginInteractionObf::get_email_fields($badgeid, $institution)
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('issuebadge', $section)
        )
    )
        )
);

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

$pagestrings = array(
    'interaction.obf' => array(
        'issuetoall'
    )
);

$smarty = smarty(array(), array(), $pagestrings, array('sidebars' => false));
$smarty->assign('group', GROUP);
$smarty->assign('form', $form);
$smarty->assign('badge', $badge);
$smarty->assign('events',
        PluginInteractionObf::get_group_events(GROUP, $badgeid));
$smarty->display('interaction:obf:issue.tpl');

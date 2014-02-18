<?php

define('INTERNAL', 1);
define('MENUITEM', 'profile/backpack');


require(dirname(dirname(dirname(__FILE__)))) . '/init.php';

$backpackemail = get_backpack_email();
$form = '';
$helptext = '';
$currentpath = '/interaction/obf/profile.php?user=' . $USER->id;

define('TITLE', get_string('backpacksettings', 'interaction.obf'));

if ($backpackemail === false) {
    $helptext = get_string('backpackhelp', 'interaction.obf');
    $form = pieform(array(
        'name' => 'backpack',
        'jsform' => false,
        'presubmitcallback' => null, // This gets predefined somewhere. Wut?
        'elements' => array(
            'submit' => array(
                'type' => 'submit',
                'value' => get_string('savebackpack', 'interaction.obf')
            )
        )
    ));
}
else {
    $helptext = get_string('backpackconnectedhelp', 'interaction.obf', $backpackemail);
    $form = pieform(array(
        'name' => 'backpack_disconnect',
        'elements' => array(
            'disconnect' => array(
                'type' => 'submit',
                'value' => get_string('disconnectbackpack', 'interaction.obf')
            )
        )
    ));
}

$smarty = smarty();
$smarty->assign('helptext', $helptext);
$smarty->assign('form', $form);
$smarty->display('interaction:obf:profile.tpl');

function backpack_disconnect_submit(Pieform $form, $values) {
    global $USER, $SESSION, $currentpath;
    delete_records('interaction_obf_usr_backpack', 'usr', $USER->id);
    $SESSION->add_ok_msg(get_string('backpackdisconnected', 'interaction.obf'));
    redirect($currentpath);
}

function get_backpack_email() {
    global $USER;

    $email = get_field('interaction_obf_usr_backpack', 'email', 'usr', $USER->id);

    return $email;
}

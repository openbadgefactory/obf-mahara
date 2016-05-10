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
define('MENUITEM', 'settings/backpack');

require(dirname(dirname(dirname(__FILE__)))) . '/init.php';

$backpackemail = get_backpack_email();
$form = '';
$helptext = '';
$currentpath = '/interaction/obf/profile.php?user=' . $USER->id;

define('TITLE', get_string('backpacksettings', 'interaction.obf'));

// User doesn't yet have backpack email saved to database.
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

function backpack_submit(Pieform $form, $values) {
    
}

function get_backpack_email() {
    global $USER;

    $email = get_field('interaction_obf_usr_backpack', 'email', 'usr', $USER->id);

    return $email;
}

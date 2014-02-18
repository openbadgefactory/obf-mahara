<?php
define('INTERNAL', 1);
define('JSON', 1);
define('NOSESSKEY', 1);

require(dirname(dirname(dirname(__FILE__)))) . '/init.php';
require_once(__DIR__ . '/lib.php');

define('GROUP', param_integer('group'));

$group = group_current_group();
$badgeid = param_variable('badgeid');

// TODO: Check privileges

try {
    $institution = PluginInteractionObf::get_group_institution(GROUP);
    $emailobj = PluginInteractionObf::get_badge_email($badgeid, $institution);
    json_reply(false, $emailobj);
} catch (Exception $ex) {
    json_reply(true, '');
}
<?php

define('INTERNAL', 1);
define('JSON', 1);
define('NOSESSKEY', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$assertion = param_variable('assertion');

try {
    $email = PluginInteractionObf::verify_backpack_assertion($assertion);  
    PluginInteractionObf::save_backpack_email($email);
    json_reply(false, '');
} catch (Exception $ex) {
    json_reply(true, $ex->getMessage());
}

<?php

/**
 * Copied from json/userlist.php
 */
define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('searchlib.php');
require_once(dirname(__FILE__) . '/lib.php');

safe_require('search', 'internal');

try {
    $query = param_variable('query');
} catch (ParameterException $e) {
    json_reply('missingparameter', 'Missing parameter \'query\'');
}

// No referer sent from the client. Just in case.
if (!isset($_SERVER['HTTP_REFERER'])) {
    json_reply('missingreferer', 'interaction.obf');
}

$limit = param_integer('limit', 20);
$offset = param_integer('offset', 0);
$allfields = param_boolean('allfields');
$group = param_integer('group', 0);
$includeadmins = param_boolean('includeadmins', true);
$orderby = param_variable('orderby', 'firstname');

$options = array(
    'orderby' => $orderby,
);

$referer = parse_url($_SERVER['HTTP_REFERER']);
$params = array();

parse_str($referer['query'], $params);

$badgeid = $params['badgeid'];

if ($group) {
    $options['group'] = $group;
    $options['includeadmins'] = $includeadmins;
    $data = search_user($query, $limit, $offset, $options);
}
else {
    $data = search_user($query, $limit, $offset, $options);
}
if (empty($data['data'])) {
    $data['data'] = array();
}

if ($data['data']) {
    $ignorelist = PluginInteractionObf::get_ignored_users($group, $badgeid);
    $ignorelist[] = $USER->id; // Remove the issuer from the list.
    $validusers = array();

    foreach ($data['data'] as $result) {
        if (!in_array($result['id'], $ignorelist)) {
            $validusers[] = array('id' => $result['id'], 'name' => $result['name']);
        }
    }

    $data['data'] = $validusers;
}

json_reply(false, $data);

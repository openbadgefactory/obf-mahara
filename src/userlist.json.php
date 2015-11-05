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
/**
 * Copied from json/usersearch.php
 */
define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('searchlib.php');
require_once('lib.php');

safe_require('search', 'internal');

try {
    $query = param_variable('query');
} catch (ParameterException $e) {
    json_reply('missingparameter', 'Missing parameter \'query\'');
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

if ($group) {
    $data = get_group_user_search_results($group, $query, 0, 9999, '', null, null, null);
}
else {
    $data = search_user($query, $limit, $offset, $options);
}
if (empty($data['data'])) {
    $data['data'] = array();
}

if ($data['data']) {
    $ignorelist = array($USER->id); // Remove the issuer from the list.
    $validusers = array();

    // Opt out users that have already earned the selected badge and the badge
    // isn't expired.
    if (isset($_SERVER['HTTP_REFERER'])) {
        $referer = parse_url($_SERVER['HTTP_REFERER']);
        $params = array();

        parse_str($referer['query'], $params);

        $badgeid = $params['badgeid'];
        $ignorelist = array_merge($ignorelist,
                PluginInteractionObf::get_ignored_users($group, $badgeid));
    }

    foreach ($data['data'] as $result) {
        if (!in_array($result['id'], $ignorelist)) {
            $validusers[] = array('id' => $result['id'], 'name' => $result['name']);
        }
    }

    $data['data'] = $validusers;
}

json_reply(false, $data);

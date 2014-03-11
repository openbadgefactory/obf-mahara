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
defined('INTERNAL') || die();

require(dirname(__FILE__) . '/base.php');

class PluginInteractionObf extends ObfBase {

    protected static function navigation_hook($user, &$items) {
        // Add our page link to institution admin.
        if ($user->is_institutional_admin()) {
            $items['manageinstitutions/obf'] = array(
                'path' => 'manageinstitutions/obf',
                'url' => 'interaction/obf/institution.php',
                'title' => get_string('openbadgefactory', 'interaction.obf'),
                'weight' => 10);
        }
    }

    /**
     * Returns all the institutions in which the user can issue badges.
     * 
     * @param stdClass $user The user object.
     * @return string[] The id's of the institutions.
     */
    public static function get_issuable_institutions($user) {
        return get_column('interaction_obf_issuer', 'institution', 'usr',
                $user->id);
    }

    /**
     * Returns the HTML for list of badges.
     * 
     * @param string|string[] $institutions The id of the institution or an
     *      array of institution ids.
     * @param int $group The group id.
     * @return string The HTML markup.
     */
    public static function get_badgelist($institutions, $group = null) {
        $institutions = is_array($institutions) ? $institutions : array($institutions);
        $categories = array();
        $badges = self::get_badges($institutions);
        $sm = smarty_core();

        if ($badges !== false) {
            foreach ($institutions as $institution) {
                $clientid = self::get_client_id($institution);
                $categories = array_merge(self::get_categories($institution,
                                $clientid), $categories);
            }
        }

        $sm->assign('institution', $institutions);
        $sm->assign('badges', $badges);
        $sm->assign('categories', $categories);
        $sm->assign('group', $group);

        return $sm->fetch('interaction:obf:badgelist.tpl');
    }

    /**
     * Returns the issuance events of a single group.
     * 
     * @param int $groupid The id of the group.
     * @param string $badgeid The id of the badge.
     * @param int $offset Query offset.
     * @param int $limit The number of events to fetch.
     * @return stdClass[] The events.
     */
    public static function get_group_events($groupid, $badgeid = null,
                                            $offset = 0, $limit = 10) {
        global $USER;
        $institutions = self::get_issuable_institutions($USER);
        $events = array();

        foreach ($institutions as $institution) {
            $events = array_merge($events,
                    self::get_events($institution,
                            self::get_api_consumer_id($groupid), $badgeid,
                            $offset, $limit));
        }

        return $events;
    }

    /**
     * Returns the number of events of an institution, multiple institutions
     * or a group.
     * 
     * @param string|array $institution The institution id or an array of
     *      institution ids.
     * @param int $groupid The id of the group. If set, then the event count of
     *      the selected group is returned.
     * @param string $badgeid The badge id. If set, then the event count of
     *      the selected badge is returned (in selected institution/group).
     * @return int|false The number of events or false in case of an error.
     */
    public static function get_event_count($institution, $groupid = null,
                                           $badgeid = null) {
        $eventcount = 0;
        $institutions = is_array($institution) ? $institution : array($institution);

        foreach ($institutions as $inst) {
            $eventcount += self::get_institution_event_count($inst, $groupid,
                            $badgeid);
        }

        return $eventcount;
    }

    /**
     * Returns the number of events of an institution or a group.
     * 
     * @param string $institution The institution id.
     * @param int $groupid The id of the group. If set, then the event count of
     *      the selected group is returned.
     * @param string $badgeid The badge id. If set, then the event count of
     *      the selected badge is returned (in selected institution/group).
     * @return int|false The number of events or false in case of an error.
     */
    public static function get_institution_event_count($institution,
                                                       $groupid = null,
                                                       $badgeid = null) {
        $curlopts = self::get_curl_opts($institution);
        $clientid = self::get_client_id($institution);

        if (empty($clientid)) {
            return false;
        }

        $aci = self::get_api_consumer_id($groupid);
        $curlopts[CURLOPT_URL] = API_URL . 'event/' . $clientid . '?api_consumer_id=' .
                $aci . '&count_only=1';

        if (!is_null($badgeid)) {
            $curlopts[CURLOPT_URL] .= '&badge_id=' . $badgeid;
        }

        $resp = mahara_http_request($curlopts);
        $data = json_decode($resp->data);

        return $data->result_count;
    }
}

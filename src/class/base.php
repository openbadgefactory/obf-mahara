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

interface ObfInterface {

    static function get_head_data($menuitem, $menuexists, $userid, $theme);

    static function navigation_hook($user, &$items);

    static function get_group_events($groupid, $badgeid, $offset, $limit);

    static function get_institution_admins(Institution $institution);
}

abstract class ObfBase extends PluginInteraction implements ObfInterface {

    /**
     * Cache for the badges, so that we don't need request the whole batch
     * every time. The array is formatted as follows:
     * 
     * [
     *      'institution1' => [ badgeobj1, badgeobj2, ... badgeobjN ],
     *      'institution2' => [ badgeobjX, badgeobjY, ... badgeobjZ ]
     * ]
     * 
     * Where badgeobj is an object with badge data (from OBF API).
     * 
     * @var stdClass[]
     */
    protected static $badgecache = array();

    public static function get_cron() {
        $checkcertage = new stdClass();
        $checkcertage->callfunction = 'check_certificate_expiration_dates';
        $checkcertage->hour = '23';
        $checkcertage->minute = '23';

        return array($checkcertage);
    }

    /**
     * The hook that extends the main navigation. We make some dirty tricks here
     * to get our links to show in the menu.
     */
    public static function menu_items() {
        global $USER, $HEADDATA, $THEME;

        if (is_null($HEADDATA)) {
            $HEADDATA = array();
        }

        // We want to see our badge links in the main navigation. Unfortunately
        // it's not possible to extend the different sub navigations (in
        // profile and in groups), so we need this "small" hack. Mahara uses
        // global variables quite a lot. In this case we use global $HEADDATA
        // variable to include our custom JavaScript file to every page we want.
        // Our scripts create the links to the navigation.
        //
        // So until Mahara allows plugins to extend the navigation more freely,
        // we do it like this.

        if (!isset($HEADDATA['interaction.obf'])) {
            $userid = $USER->id;
            $obfheaddata = '';
            $menuexists = defined('MENUITEM');
            $isgrouppage = $menuexists && strpos(MENUITEM, 'groups/') === 0;
            $isprofilepage = $menuexists && strpos(MENUITEM, 'settings/') === 0;
            $canissuebadges = self::user_can_issue_badges($USER);
            $groupid = defined('GROUP') ? (int) GROUP : null;

            // Add our JS-files to group pages.
            if ($isgrouppage && !is_null($groupid) && $canissuebadges) {
                $jsonopts = json_encode(array(
                    'lang' => array(
                        'issuetoall' => get_string('issuetoall',
                                'interaction.obf'),
                        'badges' => get_string('badges', 'interaction.obf')
                )));

                $addcss = (MENUITEM != 'groups/badges');
                $obfheaddata .= self::get_assets($THEME, 'init_group',
                                array($groupid, $jsonopts), $addcss);
            }

            // Add our JS-files to profile pages.
            else if ($isprofilepage) {
                $jsonopts = json_encode(array(
                    'lang' => array(
                        'backpacksettings' => get_string('backpacksettings',
                                'interaction.obf')
                    )
                ));

                $addcss = (MENUITEM != 'settings/backpack');
                $obfheaddata .= self::get_assets($THEME, 'init_profile',
                                array($userid, $jsonopts), $addcss);
            }

            $obfheaddata .= static::get_head_data(MENUITEM, $menuexists,
                            $userid, $THEME);

            $HEADDATA['interaction.obf'] = $obfheaddata;
        }

        $items = array();

        static::navigation_hook($USER, $items);

        return $items;
    }

    /**
     * Returns the HTML-markup for the document head. Markup includes our
     * JavaScript file and stylesheet and a call to our selected init-function.
     * 
     * @param Theme $theme The current theme object.
     * @param string $initfunc The name of the JS-function (in Obf-namespace)
     *      to be called after the document is ready.
     * @param string[] $params The arguments of the init function.
     * @param boolean $addcss Whether to include our CSS-file.
     * @return string The HTML-markup.
     */
    public static function get_assets($theme, $initfunc,
                                      array $params = array(), $addcss = true) {
        $scripturl = get_config('wwwroot') . 'interaction/obf/js/obf.js';
        $args = implode(', ', $params);
        $obfheaddata = <<<HTML
<script type="text/javascript" src="$scripturl"></script>
<script type="text/javascript">
jQuery(document).ready(function () {
    Obf.$initfunc($args);
});
</script>
HTML;

        if ($addcss) {
            $obfcssurl = array_pop($theme->get_url('style/style.css', true,
                            'interaction/obf'));
            $obfheaddata .= '<link rel="stylesheet" type="text/css" href="' . $obfcssurl . '" />';
        }

        return $obfheaddata;
    }

    /**
     * Whether the user can issue badges or not.
     * 
     * @param stdClass $user The user object.
     * @return boolean True if the user can issue badges and false otherwise.
     */
    public static function user_can_issue_badges($user) {
        return record_exists('interaction_obf_issuer', 'usr', $user->id);
    }

    /**
     * Returns the OBF client id from the plugin config.
     * 
     * @param string $institution The institution id.
     * @return string|null Returns the client id or null if not found.
     */
    public static function get_client_id($institution) {
        // Yes, we should have our own table for clientid's, but what the heck.
        $key = self::get_config_key_name($institution);
        $clientid = get_config_plugin('interaction', 'obf', $key);

        return $clientid;
    }

    /**
     * Returns the name of the configuration key used to store the client id.
     * 
     * @param string $institution The institution id.
     * @return string The name of the configuration key.
     */
    public static function get_config_key_name($institution) {
        return $institution . '.clientid';
    }

    /**
     * Converts the stream returned by some OBF API calls into valid JSON.
     * 
     * @param string $str The JSON-stream.
     * @return array The decoded data.
     */
    public static function stream_to_json($str) {
        $json = '[' . implode(',', array_filter(explode("\r\n", $str))) . ']';
        return json_decode($json);
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
     * Returns the institution badges from the API (or cache if exists).
     * 
     * @param string $institution The institution id.
     * @return stdClass[] An array of badge objects.
     */
    public static function get_institution_badges($institution) {
        // Check cache first.
        if (isset(self::$badgecache[$institution])) {
            return self::$badgecache[$institution];
        }

        $curlopts = self::get_curl_opts($institution);
        $clientid = self::get_client_id($institution);
        $badges = false;

        if (!empty($clientid)) {
            $curlopts[CURLOPT_URL] = API_URL . 'badge/' . $clientid . '?draft=0';
            $ret = mahara_http_request($curlopts);

            if ($ret->info['http_code'] === 200) {
                $badges = self::stream_to_json($ret->data);

                foreach ($badges as &$badge) {
                    $badge->categoryjson = json_encode($badge->category);
                    $badge->institution = $institution;
                }

                self::$badgecache[$institution] = $badges;
            }
            else {
                log_warn('Error fetching badges (code ' . $ret->info['http_code'] . ')');
            }
        }

        return $badges;
    }

    /**
     * Returns the badges of a single institution or multiple institutions from
     * the API (or cache if exists).
     * 
     * @param string|string[] $institution The institution id or an array of
     *      institution ids.
     * @return stdClass[] An array of badge objects.
     */
    public static function get_badges($institutions) {
        if (!is_array($institutions)) {
            $institutions = array($institutions);
        }

        $badges = array();

        foreach ($institutions as $institution) {
            $badges = array_merge($badges,
                    self::get_institution_badges($institution));
        }

        return $badges;
    }

    /**
     * Returns the badge categories from the OBF API.
     * 
     * @param string $institution The institution id.
     * @param string $clientid The OBF API client id.
     * @return string[] The categories.
     */
    public static function get_categories($institution, $clientid = null) {
        $curlopts = self::get_curl_opts($institution);
        $clientid = is_null($clientid) ? self::get_client_id($institution) : $clientid;
        $curlopts[CURLOPT_URL] = API_URL . 'badge/' . $clientid . '/_/categorylist';

        $ret = mahara_http_request($curlopts);
        $categories = json_decode($ret->data);

        return $categories;
    }

    /**
     * Returns the data of a single badge from the OBF API.
     * 
     * @param string $institution The institution id.
     * @param string $badgeid The badge id.
     * @return stdClass|false Returns the badge object or false in case of an error.
     */
    public static function get_badge($institution, $badgeid) {
        $curlopts = self::get_curl_opts($institution);
        $clientid = self::get_client_id($institution);

        if (empty($clientid)) {
            return false;
        }

        $curlopts[CURLOPT_URL] = API_URL . 'badge/' . $clientid . '/' . $badgeid;
        $resp = mahara_http_request($curlopts);

        if ($resp->info['http_code'] !== 200) {
            return false;
        }

        $badgejson = json_decode($resp->data);

        return $badgejson;
    }

    /**
     * Returns the institution events from the OBF API.
     * 
     * @param string $institution The id of the institution.
     * @param string $apiconsumerid The api consumer id.
     * @param string $badgeid The badge id.
     * @param int $offset The query offset.
     * @param int $limit The number of events to fetch.
     * @return stdClass[]|false Returns the events or false in case of an error.
     */
    public static function get_events($institution, $apiconsumerid = null,
                                      $badgeid = null, $offset = 0, $limit = 10) {
        $curlopts = self::get_curl_opts($institution);
        $clientid = self::get_client_id($institution);

        if (empty($clientid)) {
            return false;
        }

        $aci = empty($apiconsumerid) ? self::get_api_consumer_id() : $apiconsumerid;
        $curlopts[CURLOPT_URL] = API_URL . 'event/' . $clientid .
                '?api_consumer_id=' . $aci . '&offset=' . $offset . '&limit=' .
                $limit . '&order_by=desc';

        if (!empty($badgeid)) {
            $curlopts[CURLOPT_URL] .= '&badge_id=' . $badgeid;
        }

        $resp = mahara_http_request($curlopts);
        $eventjson = self::stream_to_json($resp->data);

        foreach ($eventjson as &$item) {
            $item->image = self::get_badge_image($item->badge_id, $institution);
            $item->recipientcount = count($item->recipient);
            $item->recipientlist = implode("\r\n", $item->recipient);
        }

        return $eventjson;
    }

    /**
     * Returns the badge image url (or data url).
     * 
     * @param string $badgeid The id of the badge.
     * @param string $institution The institution id.
     * @return string The image url or null if not found.
     */
    public static function get_badge_image($badgeid, $institution) {
        $badges = self::get_badges($institution);

        foreach ($badges as $badge) {
            if ($badge->id == $badgeid) {
                return $badge->image;
            }
        }

        return null;
    }

    /**
     * Saves the badge email template to database.
     * 
     * @param string $badgeid The badge id.
     * @param string $subject The email subject.
     * @param string $body The email body.
     * @param string $footer The email footer.
     */
    public static function save_email_template($badgeid, $subject, $body,
                                               $footer) {
        $existingrecord = new stdClass();
        $existingrecord->badgeid = $badgeid;

        $updatedrecord = new stdClass();
        $updatedrecord->badgeid = $badgeid;
        $updatedrecord->subject = $subject;
        $updatedrecord->body = $body;
        $updatedrecord->footer = $footer;

        ensure_record_exists('interaction_obf_badge_email', $existingrecord,
                $updatedrecord);
    }

    /**
     * Returns the badge email template. Tries to get the local version first
     * from the database. If not found, gets the template from the OBF API.
     * 
     * @param string $badgeid The badge id.
     * @param string $institution The institution id.
     * @return array Returns an associative array with 'body', 'subject' and
     *      'footer' fields or false if template was not found.
     */
    public static function get_badge_email($badgeid, $institution) {
        // Try to get the template from db first.
        $record = get_record('interaction_obf_badge_email', 'badgeid', $badgeid);
        $subject = null;
        $body = null;
        $footer = null;

        // Template found from db.
        if ($record !== false) {
            $subject = $record->subject;
            $body = $record->body;
            $footer = $record->footer;
        }

        // Template not found, get from OBF instead.
        else {
            $badgejson = self::get_badge($institution, $badgeid);

            if ($badgejson !== false) {
                $subject = $badgejson->email_subject;
                $body = $badgejson->email_body;
                $footer = $badgejson->email_footer;
            }
        }

        if ($subject === null) {
            return false;
        }

        return array('body' => $body, 'subject' => $subject, 'footer' => $footer);
    }

    /**
     * Returns the names of the selected users.
     * 
     * @param int[] $userids The user ids.
     * @return string[] The display names of the users.
     */
    public static function get_recipient_names(array $userids) {
        require_once('user.php');

        $userdata = get_users_data($userids, false);
        $names = array();

        foreach ($userdata as $record) {
            $names[] = $record->display_name;
        }

        return $names;
    }

    /**
     * Returns the api consumer id. The id if different depending of the
     * selected context (group or institution).
     * 
     * @param int $groupid The id of the group. If not set, then the consumer
     *      id of the institution is returned.
     * @return string The api consumer id.
     */
    public static function get_api_consumer_id($groupid = null) {
        return (is_null($groupid) ? '' : API_CONSUMER_ID . '_group_' . $groupid);
    }

    /**
     * Issues a badge through the OBF API.
     * 
     * @param stdClass $user The user who is issuing the badge.
     * @param string $institution The institution that owns the badge.
     * @param int $groupid The id of the group in which context the badge is issued.
     * @param string $badgeid The id of the issued badge.
     * @param int[] $userids The ids of the users who are receiving the badge.
     * @param int $issuedat When the badge is issued, UNIX-timestamp.
     * @param int $expiresat When the badge will expire (null = never).
     * @param string $subject The subject of the email that will be sent to recipients.
     * @param string $body The email body.
     * @param string $footer The email footer.
     * @return boolean Returns true if the issuance was successful.
     * @throws RemoteServerException If something goes wrong while issuing the
     *      badge.
     */
    public static function issue_badge($user, $institution, $groupid, $badgeid,
                                       $userids, $issuedat, $expiresat,
                                       $subject, $body, $footer) {
        require_once('activity.php');

        $emails = self::get_backpack_emails($userids);
        $logentry = new stdClass();
        $logentry->groupid = $groupid;

        $postdata = array(
            'recipient' => $emails,
            'issued_on' => $issuedat,
            'api_consumer_id' => self::get_api_consumer_id($groupid),
            'email_subject' => $subject,
            'email_body' => $body,
            'email_footer' => $footer,
            'log_entry' => $logentry
        );

        if (!empty($expiresat)) {
            $postdata['expires'] = $expiresat;
        }

        $clientid = self::get_client_id($institution);
        $curlopts = self::get_curl_opts($institution);
        $curlopts[CURLOPT_URL] = API_URL . 'badge/' . $clientid . '/' . $badgeid;
        $curlopts[CURLOPT_POST] = true;
        $curlopts[CURLOPT_POSTFIELDS] = json_encode($postdata);

        $resp = mahara_http_request($curlopts);

        if ($resp->info['http_code'] !== 201) {
            throw new RemoteServerException(get_string('issuancefailed',
                    'interaction.obf', $resp->info['http_code']));
        }

        self::send_notification_to_issuer($user, $institution, $userids,
                $badgeid);

        return true;
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
     * Returns the HTML for a list of events.
     * 
     * @param string[] $institutions The institutions, whose events we're displaying.
     * @param int $groupId The id of the selected group.
     * @param string $currentpath The current relative path.
     * @param int $offset The start index of the event list.
     * @return string The HTML markup for the list.
     */
    public static function get_eventlist($institutions, $groupId, $currentpath,
                                         $offset) {
        $eventcount = self::get_event_count($institutions, $groupId);
        $pagination = build_pagination(array(
            'url' => get_config('wwwroot') . $currentpath . '&page=history',
            'count' => $eventcount,
            'limit' => EVENTS_PER_PAGE,
            'offset' => $offset
        ));
        $events = static::get_group_events(GROUP, null, $offset, EVENTS_PER_PAGE);
        $sm = smarty_core();
        $sm->assign('events', $events);
        $sm->assign('pagination', $pagination['html']);
        $content = $sm->fetch('interaction:obf:events.tpl');

        return $content;
    }

    /**
     * Sends a notification to the user who has issued a badge.
     * 
     * @param stdClass $user The user object.
     * @param string $institution The current institution id.
     * @param int[] $userids The ids of the recipients.
     * @param string $badgeid The id of the issued badge.
     */
    protected static function send_notification_to_issuer($user, $institution,
                                                          $userids, $badgeid) {
        $names = self::get_recipient_names($userids);
        $badgename = self::get_badgename($institution, $badgeid);
        $message = get_string('youhaveissuedbadgesmessage', 'interaction.obf',
                $badgename, implode("\r\n", $names));
        $notification = array(
            'users' => array($user->id),
            'subject' => get_string('youhaveissuedbadgessubject',
                    'interaction.obf'),
            'message' => $message
        );

        activity_occurred('maharamessage', $notification);
    }

    /**
     * Returns the name of the badge.
     * 
     * @param string $institution The institution id.
     * @param string $badgeid The id of the badge.
     * @return string|false The name of the badge or false if not found.
     */
    public static function get_badgename($institution, $badgeid) {
        $badges = self::get_badges($institution);

        foreach ($badges as $badge) {
            if ($badgeid == $badge->id) {
                return $badge->name;
            }
        }

        return false;
    }

    /**
     * Returns the ids of the users than can be ignored when creating a list
     * of possible badge recipients. The ignored users have already earned
     * the selected badge and the badge hasn't been expired yet.
     * 
     * @param int $groupid The id of the current group.
     * @param string $badgeid The id of the selected badge.
     * @return int[] The ids of the ignored users.
     */
    public static function get_ignored_users($groupid, $badgeid) {
        $events = static::get_group_events($groupid, $badgeid);
        $ignored = array();

        if ($events !== false) {
            $recipients = array();

            foreach ($events as $event) {
                // We don't need to ignore expired events.
                if (!empty($event->expires) && time() > $event->expires) {
                    continue;
                }

                $recipients = array_merge($recipients, $event->recipient);
            }

            $uniquerecipients = array_unique($recipients);

            if (count($uniquerecipients) > 0) {
                $placeholders = implode(', ',
                        array_fill(0, count($uniquerecipients), '?'));
                $sql = <<<SQL
SELECT
    u.id, u.email, bp.email
FROM
    {usr} u
LEFT JOIN
    {interaction_obf_usr_backpack} bp ON u.id = bp.usr
WHERE
    u.email IN ($placeholders) OR bp.email IN ($placeholders)
SQL;

                // Since we use $placeholders twice in the prepared statement,
                // we need to double the $uniquerecipients array (twice the
                // placeholders, twice the matching values):
                // ... WHERE u.email IN (?,?) OR bp.email IN (?,?) => 4 items total
                $records = get_records_sql_assoc($sql,
                        array_merge($uniquerecipients, $uniquerecipients));

                foreach ($records as $record) {
                    $ignored[] = $record->id;
                }
            }
        }

        return $ignored;
    }

    /**
     * Returns the backpack emails matching the user ids in $userids. If the
     * backpack email for a single user doesn't exist, the primary email
     * address is returned instead. 
     * 
     * @param int[] $userids The userids.
     * @return string[] The email addresses.
     */
    function get_backpack_emails(array $userids) {
        $userids = array_map('intval', $userids);
        $placeholders = implode(', ', array_fill(0, count($userids), '?'));

        $sql = <<<SQL
SELECT
    u.username, u.email, bp.email AS backpack_email
FROM
    {usr} u
LEFT JOIN
    {interaction_obf_usr_backpack} bp
ON
    u.id = bp.usr
WHERE
    u.id IN ($placeholders)
SQL;
        $records = get_records_sql_assoc($sql, $userids);
        $recipients = array();

        foreach ($records as $record) {
            $recipients[] = empty($record->backpack_email) ? $record->email : $record->backpack_email;
        }

        return $recipients;
    }

    /**
     * Authenticates the institution via OBF API.
     * 
     * @param string $institution The institution id.
     * @param string $token The certificate signing request token from OBF.
     * @return boolean Returns true if authentication was successful.
     * @throws Exception If the authentication fails.
     */
    public static function authenticate($institution, $token) {
        $curlopts = self::get_curl_opts($institution);
        $curlopts[CURLOPT_URL] = API_URL . 'client/OBF.rsa.pub';

        // We don't have these yet.
        unset($curlopts[CURLOPT_SSLCERT]);
        unset($curlopts[CURLOPT_SSLKEY]);

        $signature = trim($token);
        $token = base64_decode($signature);
        $pubkey = mahara_http_request($curlopts);

        if ($pubkey->data === false || $pubkey->info['http_code'] !== 200) {
            log_warn('Error while fetching public key: ' . var_export($pubkey,
                            true));
            throw new Exception(get_string('tokenerror', 'interaction.obf'));
        }

        $decrypted = '';

        // Get the public key.
        $key = openssl_pkey_get_public($pubkey->data);

        // Decrypt data with provided key.
        if (openssl_public_decrypt($token, $decrypted, $key,
                        OPENSSL_PKCS1_PADDING) === false) {
            log_warn('Error while decrypting data: ' . openssl_error_string());
            throw new Exception(get_string('tokenerror', 'interaction.obf'));
        }

        $json = json_decode($decrypted);
        $clientid = $json->id;

        set_config_plugin('interaction', 'obf',
                self::get_config_key_name($institution), $clientid);

        // Create a new private key.
        $config = array('private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA);
        $privkey = openssl_pkey_new($config);

        // Export the new private key to a file for later use.
        openssl_pkey_export_to_file($privkey,
                self::get_pkey_filename($institution));

        $csrout = '';
        $dn = array('commonName' => $clientid);

        // Create a new CSR with the private key we just created.
        $csr = openssl_csr_new($dn, $privkey);

        // Export the CSR into string.
        if (openssl_csr_export($csr, $csrout) === false) {
            log_warn('Couldn\'t export CSR into string: ' . openssl_error_string());
            throw new Exception(get_string('tokenerror', 'interaction.obf'));
        }

        $postdata = json_encode(array('signature' => $signature, 'request' => $csrout));
        $curlopts[CURLOPT_URL] = API_URL . 'client/' . $clientid . '/sign_request';
        $curlopts[CURLOPT_POST] = true;
        $curlopts[CURLOPT_POSTFIELDS] = $postdata;

        $cert = mahara_http_request($curlopts);

        // Fetching certificate failed
        if ($cert->data === false || $cert->info['http_code'] !== 200) {
            log_warn('Error while fetching certificate: ' . print_r($cert, true));
            throw new Exception(get_string('tokenerror', 'interaction.obf'));
        }

        // Store the certificate into a file for later use
        if (file_put_contents(self::get_cert_filename($institution), $cert->data)
                === false) {
            log_warn('Couldn\'t write to file: ' . self::get_cert_filename($institution));
            throw new Exception(get_string('certdirectorynotwritable',
                    'interaction.obf'));
        }

        return true;
    }

    /**
     * Removes authentication data from the system.
     * 
     * @param string $institution The institution id.
     */
    public static function deauthenticate($institution) {
        $certfile = self::get_cert_filename($institution);
        $pkifile = self::get_pkey_filename($institution);

        @unlink($certfile);
        @unlink($pkifile);

        self::remove_config_plugin(self::get_config_key_name($institution));
    }

    /**
     * Removes a configuration value from the database related to this plugin.
     * 
     * @param string $configname The name of the configuration value.
     */
    public static function remove_config_plugin($configname) {
        delete_records('interaction_config', 'plugin', 'obf', 'field',
                $configname);
    }

    /**
     * Get the Curl-options common to all requests.
     * 
     * @param string $institution The institution id.
     * @return array The curl options as an associative array.
     */
    public static function get_curl_opts($institution) {
        return array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSLCERT => self::get_cert_filename($institution),
            CURLOPT_SSLKEY => self::get_pkey_filename($institution),
            CURLOPT_SSL_VERIFYHOST => !TEST_MODE, // for testing
            CURLOPT_SSL_VERIFYPEER => !TEST_MODE // for testing
        );
    }

    /**
     * Creates and returns the HTML form used to change the issuing privileges
     * inside an institution.
     * 
     * @param string $institution The institution id.
     * @return string The HTML form
     */
    public static function get_privileges_form($institution) {
        // Get users who currently have issuer privileges.
        $issuers = get_column_sql('SELECT ioi.usr
        FROM {interaction_obf_issuer} ioi
        LEFT JOIN {usr} u ON ioi.usr = u.id
        WHERE ioi.institution = ?
        AND u.deleted = 0', array($institution));

        $userlistelement = array(
            'title' => get_string('institutionissuers', 'interaction.obf'),
            'lefttitle' => get_string('institutionmembers', 'interaction.obf'),
            'righttitle' => get_string('institutionissuermembers',
                    'interaction.obf'),
            'type' => 'userlist',
            'filter' => false,
            'searchscript' => 'admin/users/userinstitutionsearch.json.php',
            'defaultvalue' => $issuers,
            'searchparams' => array('member' => 1, 'limit' => 100, 'query' => '',
                'institution' => $institution)
        );

        $userlistform = array(
            'name' => 'institutionissuers',
            'elements' => array(
                'users' => $userlistelement,
                'submit' => array(
                    'type' => 'submit',
                    'value' => get_string('save', 'interaction.obf')
                )
            )
        );

        $content = pieform($userlistform);

        return $content;
    }

    /**
     * Returns the form used to issue a badge.
     * 
     * @param stdClass $badge The selected badge.
     * @param string $institution The institution id.
     * @return string The HTML form.
     */
    public static function get_issuance_form($badge, $institution) {
        $section = 'interaction.obf';
        $expiresdefault = empty($badge->expires) ? null : strtotime('+ ' . $badge->expires . ' months');
        $form = pieform(array(
            'name' => 'issuance',
            'renderer' => 'table',
            'method' => 'post',
            'elements' => array(
                'badge' => array(
                    'type' => 'hidden',
                    'value' => $badge->id,
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
                            'righttitle' => get_string('grouprecipients',
                                    $section),
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
                    'legend' => get_string('email', $section),
                    'collapsible' => true,
                    'collapsed' => true,
                    'elements' => self::get_email_fields($badge->id,
                            $institution)
                ),
                'submit' => array(
                    'type' => 'submit',
                    'value' => get_string('issuebadge', $section)
                )
            )
                )
        );

        return $form;
    }

    /**
     * Returns the email form elements (subject, body, footer) to be used
     * in any Pieform.
     * 
     * @param string $badgeid The id of the badge.
     * @param string $institution The institution id.
     * @return array The form elements.
     */
    public static function get_email_fields($badgeid = null, $institution = null) {
        $section = 'interaction.obf';
        $subject = '';
        $body = '';
        $footer = '';

        if (!is_null($badgeid)) {
            $email = self::get_badge_email($badgeid, $institution);

            if ($email !== false) {
                $subject = $email['subject'];
                $body = $email['body'];
                $footer = $email['footer'];
            }
        }

        return array(
            'subject' => array(
                'type' => 'text',
                'defaultvalue' => $subject,
                'title' => get_string('emailsubject', $section)
            ),
            'body' => array(
                'type' => 'textarea',
                'defaultvalue' => $body,
                'resizable' => false,
                'rows' => 10,
                'cols' => 60,
                'title' => get_string('emailbody', $section)
            ),
            'footer' => array(
                'type' => 'textarea',
                'defaultvalue' => $footer,
                'resizable' => false,
                'rows' => 5,
                'cols' => 60,
                'title' => get_string('emailfooter', $section)
            )
        );
    }

    /**
     * Checks whether the institution is currently authenticated with the
     * OBF API.
     * 
     * @param string $institution The institution id.
     * @return boolean True if already authenticated, false otherwise.
     * @throws RemoteServerException If something goes wrong while communicating
     *      with the OBF API.
     */
    public static function is_authenticated($institution) {
        $clientid = self::get_client_id($institution);

        if (empty($clientid)) {
            return false;
        }

        $url = API_URL . 'client/' . $clientid;
        $curlopts = self::get_curl_opts($institution);
        $curlopts[CURLOPT_URL] = $url;
        $response = mahara_http_request($curlopts);
        $httpcode = $response->info['http_code'];

        // Remote server error
        if ($httpcode >= 500) {
            throw new RemoteServerException(get_string('apierror',
                    'interaction.obf'));
        }

        return $response->info['http_code'] == 200;
    }

    /**
     * Returns the HTML used to show an error message.
     * 
     * @param string $message The error message to show.
     * @return string The HTML markup.
     */
    public static function get_error_template($message) {
        $sm = smarty_core();
        $sm->assign('error', $message);
        return $sm->fetch('interaction:obf:error.tpl');
    }

    /**
     * Creates and returns the HTML form used to authenticate the institution.
     * 
     * @param string $institution The institution id.
     * @return string The HTML form.
     */
    public static function get_settings_form($institution) {
        $content = '';
        $formdefs = array();

        try {
            $authenticated = self::is_authenticated($institution);
        } catch (RemoteServerException $exc) {
            $content = self::get_error_template($exc->getMessage());
            return $content;
        }

        // Institution is not yet authenticated, show the authentication form.
        if (!$authenticated) {
            $content .= '<div class="info">' .
                    get_string('authenticationhelp', 'interaction.obf') . '</div>';
            $formdefs = array(
                'name' => 'token',
                'renderer' => 'table',
                'elements' => array(
                    'token' => array(
                        'type' => 'textarea',
                        'title' => get_string('requesttoken', 'interaction.obf'),
                        'rows' => 5,
                        'cols' => 80,
                        'rules' => array('required' => true)
                    ),
                    'submit' => array(
                        'type' => 'submit',
                        'value' => get_string('authenticate', 'interaction.obf')
                    )
                )
            );
        }

        // Institution already authenticated, show a button to disconnect.
        else {
            $formdefs = array(
                'name' => 'disconnect',
                'renderer' => 'table',
                'jsform' => false,
                'presubmitcallback' => null,
                'elements' => array(
                    'submit' => array(
                        'type' => 'submit',
                        'value' => get_string('deauthenticate',
                                'interaction.obf')
                    )
                )
            );

            $sm = smarty_core();
            $content .= $sm->fetch('interaction:obf:alreadyauthenticated.tpl');
        }

        $content .= pieform($formdefs);

        return $content;
    }

    /**
     * Updates the issuer privileges to database.
     * 
     * @param string $institution The institution id.
     * @param int[] $users The userids with the issuer privilege.
     * @return boolean
     */
    public static function save_institution_issuers($institution, array $users) {
        $userids = array_map('intval', $users);
        $validusers = array();

        // Check that users actually belong to selected institution, just in case.
        // TODO: Get all users of the institution BEFORE the loop and just do the
        // check inside the loop to avoid multiple SQL-queries.
        foreach ($userids as $userid) {
            $user = new User();
            $user->find_by_id($userid);

            if (in_array($institution, array_keys($user->institutions))) {
                $validusers[] = $user;
            }
        }

        db_begin();
        delete_records('interaction_obf_issuer', 'institution', $institution);

        foreach ($validusers as $user) {
            insert_record('interaction_obf_issuer',
                    (object) array(
                        'usr' => $user->id,
                        'institution' => $institution
            ));
        }

        db_commit();

        return true;
    }

    /**
     * Verifies the assertion returned by Persona's authentication callback.
     * 
     * @param string $assertion The assertion from Persona.
     * @return string Returns the email address matching the assertion.
     * @throws Exception If the verification fails for some reason.
     */
    public static function verify_backpack_assertion($assertion) {
        $params = array('assertion' => $assertion, 'audience' => self::get_audience());
        $curlopts = array(
            CURLOPT_POST => 1,
            CURLOPT_URL => PERSONA_VERIFIER_URL,
            CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
            CURLOPT_POSTFIELDS => json_encode($params)
        );

        $resp = mahara_http_request($curlopts);

        if (empty($resp->data)) {
            throw new Exception(get_string('verificationfailed',
                    'interaction.obf'));
        }

        $data = json_decode($resp->data);

        if (empty($data)) {
            throw new Exception(get_string('verificationfailed',
                    'interaction.obf'));
        }

        if ($data->status != 'okay') {
            throw new Exception(get_string('invalidassertion', 'interaction.obf'));
        }

        $email = $data->email;

        return $email;
    }

    /**
     * Saves the user's backpack email to database.
     * 
     * @param stdClass $user The user object.
     * @param type $email
     */
    public static function save_backpack_email($user, $email) {
        $existingrecord = new stdClass();
        $existingrecord->usr = $user->id;

        $record = new stdClass();
        $record->usr = $user->id;
        $record->email = $email;

        ensure_record_exists('interaction_obf_usr_backpack', $existingrecord,
                $record);
    }

    /**
     * Returns the audience (site URL) used in Persona verification.
     * 
     * @return string The site URL.
     */
    public static function get_audience() {
        $urlparts = parse_url(get_config('wwwroot'));
        $port = isset($urlparts['port']) ? $urlparts['port'] : 80;
        $url = $urlparts['scheme'] . '://' . $urlparts['host'] . ':' . $port;

        return $url;
    }

    /**
     * Returns the absolute path of the institution's public key file.
     * 
     * @param string $institution The institution id.
     * @return string The absolute path of the file.
     */
    public static function get_pkey_filename($institution) {
        return __DIR__ . '/../pki/' . $institution . '.key';
    }

    /**
     * Returns the absolute path of the institution's certificate file.
     * 
     * @param string $institution The institution id.
     * @return string The absolute path of the file.
     */
    public static function get_cert_filename($institution) {
        return __DIR__ . '/../pki/' . $institution . '.pem';
    }

    /**
     * Checks every certificate in the system and notifies the institution
     * admins if the certificate is expiring.
     */
    public static function check_certificate_expiration_dates() {
        require_once('activity.php');
        require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/lib/institution.php');

        $certfiles = self::get_cert_files();

        foreach ($certfiles as $certfile) {
            $daysleft = self::get_certificate_days_left($certfile);
            $donotify = in_array($daysleft,
                    array(30, 25, 20, 15, 10, 5, 4, 3, 2, 1));
            
            // Notify only if there's certain amount of days left before the
            // certification expires.
            if ($donotify === false) {
                continue;
            }

            $institutionid = basename($certfile, '.pem');
            
            try {
                $institution = new Institution($institutionid);
            } catch (ParamOutOfRangeException $exc) {
                // Institution does not exist
                continue;
            }

            // Not a good habit to query in a loop, but this is done in a cron
            // job.
            $recipients = static::get_institution_admins($institution);
            
            // We need to send each notification separately, because users can
            // have different language settings.
            foreach ($recipients as $userid) {
                // Yes, yet another query to database. We'll refactor this
                // later.
                $lang = get_user_language($userid);
                $subject = get_string_from_language($lang,
                        'certificateisexpiring', 'interaction.obf');
                $message = get_string_from_language($lang,
                        'certificateisexpiringmessage', 'interaction.obf',
                        $institution->displayname, $daysleft);
                $notification = array('users' => array($userid), 'subject' => $subject,
                    'message' => $message);

                activity_occurred('maharamessage', $notification);
            }
        }
    }

    /**
     * Get the number of days left before the certificate expires.
     * 
     * @param string $certfile The absolute path of the certificate file.
     * @return int The number of days left before expiration.
     */
    protected static function get_certificate_days_left($certfile) {
        $expiresin = self::get_certificate_expiration_date($certfile);

        $diff = $expiresin - time();
        $days = floor($diff / (60 * 60 * 24));

        return $days;
    }

    /**
     * Returns all certification files in the system.
     * 
     * @return string[] A string of filenames with absolute paths.
     */
    protected static function get_cert_files() {
        return glob(__DIR__ . '/../pki/*.pem');
    }

    /**
     * Returns the expiration date of the certificate.
     * 
     * @param string $certfile The absolute path of the certificate file.
     * @return int The expiration date as Unix timestamp.
     */
    protected static function get_certificate_expiration_date($certfile) {
        $cert = file_get_contents($certfile);
        $ssl = openssl_x509_parse($cert);

        return $ssl['validTo_time_t'];
    }

    public static function instance_config_form($group, $instance = null) {
        
    }

    public static function instance_config_save($instance, $values) {
        
    }

}

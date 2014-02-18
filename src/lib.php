<?php

defined('INTERNAL') || die();
define('API_URL', 'https://elvis.discendum.com/obf/v1/');
define('API_CONSUMER_ID', 'mahara');

require_once(dirname(dirname(__FILE__)) . '/lib.php');

class PluginInteractionObf extends PluginInteraction {

    private static $badgecache = array();

    public static function instance_config_form($group, $instance = null) {
        
    }

    public static function instance_config_save($instance, $values) {
        
    }

    public static function menu_items() {
        global $USER, $HEADDATA;

        if (is_null($HEADDATA)) {
            $HEADDATA = array();
        }

        // Hack? Not at all!
        if (!isset($HEADDATA['interaction.obf'])) {
            $obfheaddata = '';
            $isgrouppage = defined('MENUITEM') && strpos(MENUITEM, 'groups/') === 0;
            $isprofilepage = defined('MENUITEM') && strpos(MENUITEM, 'profile/')
                    === 0;
            $groupid = defined('GROUP') ? (int) GROUP : null;
            $userid = $USER->id;

            if ($isgrouppage && !is_null($groupid) && self::user_can_issue_badges()) {
                $jsonopts = json_encode(array(
                    'lang' => array(
                        'issuetoall' => get_string('issuetoall',
                                'interaction.obf'),
                        'badges' => get_string('badges', 'interaction.obf')
                )));

                $obfheaddata .= self::get_assets('init_group',
                                array($groupid, $jsonopts));
            }
            else if ($isprofilepage) {
                $jsonopts = json_encode(array(
                    'lang' => array(
                        'backpacksettings' => get_string('backpacksettings',
                                'interaction.obf')
                    )
                ));
                $obfheaddata .= self::get_assets('init_profile',
                                array($userid, $jsonopts));
            }

            $HEADDATA['interaction.obf'] = $obfheaddata;
        }

        $items = array();

        if ($USER->is_institutional_admin()) {
            $items['manageinstitutions/obf'] = array(
                'path' => 'manageinstitutions/obf',
                'url' => 'interaction/obf/institution.php',
                'title' => get_string('openbadgefactory', 'interaction.obf'),
                'weight' => 10);
        }

        return $items;
    }

    public static function get_assets($initfunc, array $params) {
        global $THEME;

        $scripturl = get_config('wwwroot') . 'interaction/obf/js/obf.js';
        $obfcssurl = array_pop($THEME->get_url('style/style.css', true,
                        'interaction/obf'));
        $args = implode(', ', $params);
        $obfheaddata = <<<JS
<link rel="stylesheet" type="text/css" href="$obfcssurl" />
<script type="text/javascript" src="$scripturl"></script>
<script type="text/javascript">
jQuery(document).ready(function () {
    Obf.$initfunc($args);
});
</script>
JS;

        return $obfheaddata;
    }

    public static function user_can_issue_badges() {
        global $USER;
        return record_exists('interaction_obf_issuer', 'usr', $USER->id);
    }

    public static function get_client_id($institution) {
        // Yes, we should have our own table for clientid's, but what the heck,
        // It's friday.
        $key = self::get_config_key_name($institution);
        $clientid = get_config_plugin('interaction', 'obf', $key);

        return $clientid;
    }

    public static function get_config_key_name($institution) {
        return $institution . '.clientid';
    }

    public static function stream_to_json($str) {
        $json = '[' . implode(',', array_filter(explode("\r\n", $str))) . ']';
        return json_decode($json);
    }

    public static function get_badges($institution) {
        // Check cache first.
        if (isset(self::$badgecache[$institution])) {
            $badges = self::$badgecache[$institution];
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
                }

                self::$badgecache[$institution] = $badges;
            }
            else {
                log_warn('Error fetching badges (code ' . $ret->info['http_code'] . ')');
            }
        }

        return $badges;
    }

    public static function get_badgelist($institution, $group = null) {
        $categories = array();
        $badges = self::get_badges($institution);
        $sm = smarty();

        if ($badges !== false) {
            $clientid = self::get_client_id($institution);
            $categories = self::get_categories($institution, $clientid);
        }

        $sm->assign('institution', $institution);
        $sm->assign('badges', $badges);
        $sm->assign('categories', $categories);
        $sm->assign('group', $group);

        return $sm->fetch('interaction:obf:badgelist.tpl');
    }

    public static function get_categories($institution, $clientid) {
        $curlopts = self::get_curl_opts($institution);
        $curlopts[CURLOPT_URL] = API_URL . 'badge/' . $clientid . '/_/categorylist';

        $ret = mahara_http_request($curlopts);
        $categories = json_decode($ret->data);

        return $categories;
    }

    public static function get_badge($institution, $badgeid) {
        $curlopts = self::get_curl_opts($institution);
        $clientid = self::get_client_id($institution);

        if (empty($clientid)) {
            return false;
        }

        $curlopts[CURLOPT_URL] = API_URL . 'badge/' . $clientid . '/' . $badgeid;

        // TODO; check for errors in request
        $resp = mahara_http_request($curlopts);
        $badgejson = json_decode($resp->data);

        return $badgejson;
    }

    public static function get_group_events($groupid, $badgeid = null) {
        $institution = self::get_group_institution($groupid);
        $events = self::get_events($institution,
                        self::get_api_consumer_id($groupid), $badgeid);

        return $events;
    }

    public static function get_events($institution, $apiconsumerid = null,
                                      $badgeid = null) {
        $curlopts = self::get_curl_opts($institution);
        $clientid = self::get_client_id($institution);

        if (empty($clientid)) {
            return false;
        }

        $aci = empty($apiconsumerid) ? self::get_api_consumer_id() : $apiconsumerid;
        $curlopts[CURLOPT_URL] = API_URL . 'event/' . $clientid . '?api_consumer_id=' . $aci;

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

    public static function get_badge_image($badgeid, $institution) {
        $badges = self::get_badges($institution);

        foreach ($badges as $badge) {
            if ($badge->id == $badgeid) {
                return $badge->image;
            }
        }

        return null;
    }

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

    public static function get_recipient_names(array $userids) {
        require_once('user.php');

        $userdata = get_users_data($userids, false);
        $names = array();

        foreach ($userdata as $record) {
            $names[] = $record->display_name;
        }

        return $names;
    }

    public static function get_api_consumer_id($groupid = null) {
        return API_CONSUMER_ID . (is_null($groupid) ? '' : '_group_' . $groupid);
    }

    public static function issue_badge($groupid, $badgeid, $userids, $issuedat,
                                       $expiresat, $subject, $body, $footer) {
        global $USER;

        require_once('activity.php');

        $institution = self::get_group_institution($groupid);
        $emails = self::get_backpack_emails($userids);
        $names = self::get_recipient_names($userids);
        $badges = self::get_badges($institution);
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

        $badgename = '';

        // Find the name of the badge for the notification.
        foreach ($badges as $badge) {
            if ($badgeid == $badge->id) {
                $badgename = $badge->name;
                break;
            }
        }

        $message = get_string('youhaveissuedbadgesmessage', 'interaction.obf',
                $badgename, implode("\r\n", $names));
        $notification = array(
            'users' => array($USER->id),
            'subject' => get_string('youhaveissuedbadgessubject',
                    'interaction.obf'),
            'message' => $message
        );

        activity_occurred('maharamessage', $notification);

        return true;
    }

    public static function get_ignored_users($groupid, $badgeid) {
        $events = self::get_group_events($groupid, $badgeid);
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

            $recipients = array_unique($recipients);

            if (count($recipients) > 0) {
                $placeholders = implode(', ',
                        array_fill(0, count($recipients), '?'));
                $sql = <<<SQL
SELECT
    u.id, COALESCE(bp.email, u.email) AS backpack_email
FROM
    {usr} u
LEFT JOIN
    {interaction_obf_usr_backpack} bp ON u.id = bp.usr
HAVING
    backpack_email IN ($placeholders)
SQL;

                $records = get_records_sql_assoc($sql, $recipients);

                foreach ($records as $record) {
                    $ignored[] = $record->id;
                }
            }
        }

        return $ignored;
    }

    function get_backpack_emails(array $userids) {
        $userids = array_map('intval', $userids);
        $placeholders = implode(', ', array_fill(0, count($userids), '?'));

        $sql = "SELECT
                u.username, u.email, bp.email AS backpack_email
            FROM
                {usr} u
            LEFT JOIN
                {interaction_obf_usr_backpack} bp
            ON
                u.id = bp.usr
            WHERE
                u.id IN ($placeholders)";
        $records = get_records_sql_assoc($sql, $userids);
        $recipients = array();

        foreach ($records as $record) {
            $recipients[] = empty($record->backpack_email) ? $record->email : $record->backpack_email;
        }

        return $recipients;
    }

    public static function authenticate($institution, $token) {
        global $USER;

        if (!$USER->can_edit_institution($institution)) {
            throw new Exception(get_string('notadminforinstitution',
                    'interaction.obf'));
        }

        $curlopts = self::get_curl_opts($institution);
        $curlopts[CURLOPT_URL] = API_URL . 'client/OBF.rsa.pub';

        // We don't have these yet.
        unset($curlopts[CURLOPT_SSLCERT]);
        unset($curlopts[CURLOPT_SSLKEY]);

        $signature = trim($token);
        $token = base64_decode($signature);
        $pubkey = mahara_http_request($curlopts);

        if ($pubkey->data === false || $pubkey->info['http_code'] !== 200) {
            log_warn('Error while fetching public key: ' . print_r($pubkey, true));
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

    public static function deauthenticate($institution) {
        $certfile = self::get_cert_filename($institution);
        $pkifile = self::get_pkey_filename($institution);

        @unlink($certfile);
        @unlink($pkifile);
        
        self::remove_config_plugin(self::get_config_key_name($institution));
    }

    public static function remove_config_plugin($configname) {
        delete_records('interaction_config', 'plugin', 'obf', 'field', $configname);
    }
    
    public static function get_curl_opts($institution) {
        return array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSLCERT => self::get_cert_filename($institution),
            CURLOPT_SSLKEY => self::get_pkey_filename($institution),
            CURLOPT_SSL_VERIFYHOST => false, // for testing
            CURLOPT_SSL_VERIFYPEER => false // for testing
        );
    }

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

    public static function is_authenticated($institution) {
        $clientid = self::get_client_id($institution);

        if (empty($clientid)) {
            return false;
        }

        $url = API_URL . 'client/' . $clientid;
        $curlopts = self::get_curl_opts($institution);
        $curlopts[CURLOPT_URL] = $url;
        $response = mahara_http_request($curlopts);

        return $response->info['http_code'] == 200;
    }

    public static function get_settings_form($institution) {
        $authenticated = self::is_authenticated($institution);
        $content = '';

        $tokenform = pieform(array(
            'name' => 'token',
            'renderer' => 'table',
            'elements' => array(
                'token' => array(
                    'type' => 'textarea',
                    'title' => get_string('requesttoken', 'interaction.obf'),
                    'help' => true,
                    'rows' => 5,
                    'cols' => 80,
                    'rules' => array('required' => true),
                    'disabled' => $authenticated
                ),
                'submit' => array(
                    'type' => 'submit',
                    'disabled' => $authenticated,
                    'value' => get_string('authenticate', 'interaction.obf')
                )
            )
        ));

        if ($authenticated) {
            $sm = smarty();
            $content .= $sm->fetch('interaction:obf:alreadyauthenticated.tpl');
        }

        $content .= $tokenform;

        return $content;
    }

    public static function save_institution_issuers($institution, array $users) {
        global $USER;

        if (!$USER->can_edit_institution($institution)) {
            throw new Exception(get_string('notadminforinstitution',
                    'interaction.obf'));
        }

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

    public static function verify_backpack_assertion($assertion) {
        $params = array('assertion' => $assertion, 'audience' => self::get_audience());
        $curlopts = array(
            CURLOPT_POST => 1,
            CURLOPT_URL => 'https://verifier.login.persona.org/verify',
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

    public static function save_backpack_email($email) {
        global $USER;

        $existingrecord = new stdClass();
        $existingrecord->usr = $USER->id;

        $record = new stdClass();
        $record->usr = $USER->id;
        $record->email = $email;

        ensure_record_exists('interaction_obf_usr_backpack', $existingrecord,
                $record);
    }

    public static function get_audience() {
        $urlparts = parse_url(get_config('wwwroot'));
        $port = isset($urlparts['port']) ? $urlparts['port'] : 80;
        $url = $urlparts['scheme'] . '://' . $urlparts['host'] . ':' . $port;

        return $url;
    }

    public static function get_group_institution($groupid) {
        return get_field('group', 'institution', 'id', $groupid);
    }

    public static function get_pkey_filename($institution) {
        return __DIR__ . '/pki/' . $institution . '.key';
    }

    public static function get_cert_filename($institution) {
        return __DIR__ . '/pki/' . $institution . '.pem';
    }

}

class InteractionObfInstance extends InteractionInstance {

    public function interaction_remove_user($userid) {
        
    }

    public static function get_plugin() {
        return 'obf';
    }

}

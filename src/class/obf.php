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
            $institutionevents = self::get_events($institution,
                            self::get_api_consumer_id($groupid), $badgeid,
                            $offset, $limit);
            
            if ($institutionevents !== false) {
                $events = array_merge($events, $institutionevents);
            }
        }

        return $events;
    }

    public static function navigation_hook($user, &$items) {
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

    public static function get_head_data($menuitem, $menuexists, $userid, $theme) {
        return '';
    }

    public static function get_institution_admins(Institution $institution) {
        return $institution->admins();
    }

}

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

define('API_URL', '%%API_URL%%');
define('API_CONSUMER_ID', 'mahara');
define('EVENTS_PER_PAGE', 15);
define('TEST_MODE', false);
define('PERSONA_VERIFIER_URL', 'https://verifier.login.persona.org/verify');

require_once(dirname(dirname(__FILE__)) . '/lib.php');
require_once(dirname(__FILE__) . '/class/obf.php');

class InteractionObfInstance extends InteractionInstance {

    public function interaction_remove_user($userid) {
        
    }

    public static function get_plugin() {
        return 'obf';
    }

}

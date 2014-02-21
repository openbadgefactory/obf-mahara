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

function xmldb_interaction_obf_upgrade($oldversion = 0) {
    // There was no database prior to this version.
    if ($oldversion < 2014012401) {
        install_from_xmldb_file(get_config('docroot') . 'interaction/obf/db/install.xml');
    }

    // Create table for backpack emails.
    if ($oldversion < 2014013000) {
        $table = new XMLDBTable('interaction_obf_usr_backpack');

        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, '10', null,
                XMLDB_NOTNULL);
        $table->addFieldInfo('email', XMLDB_TYPE_CHAR, '255', null,
                XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('usr'));

        if (!create_table($table)) {
            throw new SQLException('Table "' . $table->name . '" could not be created, check log for errors.');
        }
    }

    // Create table for email templates.
    if ($oldversion < 2014020601) {
        $table = new XMLDBTable('interaction_obf_badge_email');

        $table->addFieldInfo('badgeid', XMLDB_TYPE_CHAR, '255', null, null);
        $table->addFieldInfo('subject', XMLDB_TYPE_CHAR, '255', null, null);
        $table->addFieldInfo('body', XMLDB_TYPE_TEXT, 'big', null, null);
        $table->addFieldInfo('footer', XMLDB_TYPE_TEXT, 'big', null, null);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('badgeid'));

        if (!create_table($table)) {
            throw new SQLException('Table "' . $table->name . '" could not be created, check log for errors.');
        }
    }

    return true;
}

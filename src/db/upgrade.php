<?php

defined('INTERNAL') || die();

function xmldb_interaction_obf_upgrade($oldversion = 0) {
    // There was no database prior to this version.
    if ($oldversion < 2014012401) {
        install_from_xmldb_file(get_config('docroot') . 'interaction/obf/db/install.xml');
    }

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

<?php

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'manageinstitutions/obf');
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'obf');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require(dirname(__FILE__) . '/lib.php');

$subpages = array('history', 'email');
$paramtype = param_alpha('type', 'history');
$type = !in_array($paramtype, $subpages) ? 'history' : $paramtype;
$badgeid = param_variable('badgeid');
$institution = param_alphanum('institution');
$content = '';
$badge = PluginInteractionObf::get_badge($institution, $badgeid);
$currentpath = '/interaction/obf/badge.php?type=' . $type . '&institution=' .
        $institution . '&badgeid=' . $badgeid;

define('TITLE', get_string('openbadgefactory', 'interaction.obf') . ' - ' . $badge->name);

switch ($type) {
    
    case 'history':
        $events = PluginInteractionObf::get_events($institution, null, $badgeid);
        $sm = smarty();
        $sm->assign('events', $events);
        $content = $sm->fetch('interaction:obf:events.tpl');
        break;
    
    case 'email':
        $elements = PluginInteractionObf::get_email_fields($badgeid, $institution);
        $elements['submit'] = array(
            'type' => 'submit',
            'value' => get_string('saveemail', 'interaction.obf')
        );

        $emailform = pieform(array(
            'name' => 'emailtemplate',
            'renderer' => 'table',
            'method' => 'post',
            'elements' => $elements
                )
        );
        
        $content = $emailform;
        
        break;
}

$cssfiles = $THEME->get_url('style/style.css', true,
        SECTION_PLUGINTYPE . '/' . SECTION_PLUGINNAME);
$cssfilesmodified = array();

// Let's add our own suffix to the theme name so that our styles don't override
// the admin styles using the same theme.
foreach ($cssfiles as $theme => $sheet) {
    $cssfilesmodified[$theme . '_obf'] = $sheet;
}

$smarty = smarty();

// A small hack here. The plugin stylesheet gets overridden by admin styles,
// so we need to add them manually.
$smarty->assign('STYLESHEETLIST',
        array_merge($smarty->get_template_vars('STYLESHEETLIST'),
                $cssfilesmodified));

$smarty->assign('badge', $badge);
$smarty->assign('type', $type);
$smarty->assign('institution', $institution);
$smarty->assign('subpages', $subpages);
$smarty->assign('content', $content);
$smarty->display('interaction:obf:badge.tpl');

function emailtemplate_submit(Pieform $form, $values) {
    global $SESSION, $currentpath, $badgeid;
    
    try {
        PluginInteractionObf::save_email_template($badgeid, $values['subject'],
                $values['body'], $values['footer']);
        $SESSION->add_ok_msg(get_string('emailtemplatesaved', 'interaction.obf'));        
    } catch (Exception $ex) {
        $SESSION->add_error_msg($ex->getMessage());
    }
    
    redirect($currentpath);
}

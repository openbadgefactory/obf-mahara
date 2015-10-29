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

$string['alreadyauthenticated'] = 'Connection to Open Badge Factory is up and working. The client certificate expires in <strong>%s</strong>.';
$string['apierror'] = 'Fetching data from Open Badge Factory failed. Please try'
        . ' again later.';
$string['authenticate'] = 'Authenticate';
$string['authenticationhelp'] = 'Certificate needs to be generated before'
        . ' Open Badge Factory can be used within the institution. Log in to'
        . ' Open Badge Factory to get the request token and paste it to the'
        . ' field below.';
$string['authenticationsuccessful'] = 'Authentication successful.';
$string['backpackconnectedhelp'] = 'Connection to Mozilla Backpack is '
        . 'established using your email address <strong>%s</strong>.';
$string['backpackdisconnected'] = 'Connection to Mozilla Backpack hasn\'t'
        . ' been made yet.';
$string['backpackhelp'] = 'To start earning Open Badges, you need to connect to '
        . 'Mozilla Backpack using your email address.';
$string['backpacksettings'] = 'Backpack settings';
$string['backtobadgelist'] = '< Back to list of badges';
$string['badgecreated'] = 'Created';
$string['badgecreatedn'] = 'Created %s';
$string['badgedescription'] = 'Description';
$string['badgedetails'] = 'Badge details';
$string['badgeemail'] = 'Email message';
$string['badgegrouphistory'] = 'Issuance history';
$string['badgehistory'] = 'Issuance history';
$string['badgename'] = 'Name';
$string['badges'] = 'Badges';
$string['badgesuccessfullyissued'] = 'Badge was successfully issued.';
$string['certdirectorynotwritable'] = 'Saving certificate failed. The system'
        . ' admin has to make sure that the directory for the certificate files'
        . ' is created and that it is writable by the server process.';
$string['certificateisexpiring'] = 'Open Badge Factory client certificate is expiring.';
$string['certificateisexpiringmessage'] = 'Open Badge Factory client certificate '
        . 'of your institution %s is expiring in %s days. To renew the certificate, '
        . 'log in to Open Badge Factory with your organization admin account, '
        . 'generate and copy the certificate signing request token and paste it '
        . 'to the textarea in the institution settings page. Read the plugin '
        . 'documentation for detailed instructions.';
$string['confirmdeauthentication'] = 'Are you sure you want to deauthenticate '
        . 'the selected institution?';
$string['deauthenticate'] = 'Deauthenticate';
$string['disconnectbackpack'] = 'Disconnect';
$string['email'] = 'Email message';
$string['emailbody'] = 'Email body';
$string['emailfooter'] = 'Email footer';
$string['emailsubject'] = 'Email subject';
$string['emailtemplatesaved'] = 'Email message was successfully saved.';
$string['errorfetchingbadges'] = 'There was an error while fetching the badges.';
$string['expiresat'] = 'Expires in';
$string['filterbadges'] = 'Filter badges:';
$string['groupmembers'] = 'Group members';
$string['grouprecipients'] = 'Recipients';
$string['history'] = 'Issuance history';
$string['institutionissuermembers'] = 'These members can issue badges:';
$string['institutionissuers'] = 'Issuance privileges';
$string['institutionissuershelp'] = 'Select users who can issue badges in the '
        . 'groups they are members of.';
$string['institutionissuersupdated'] = 'Issuance settings updated.';
$string['institutionmembers'] = 'Members of the institution';
$string['institutionselectordescription'] = '';
$string['invalidassertion'] = 'Couldn\'t verify email address. Reason: %s';
$string['issuancedetails'] = 'Issuance details';
$string['issuancefailed'] = 'Badge issuance failed. Please try again later.';
$string['issuebadge'] = 'Issue badge';
$string['issuedat'] = 'Issued at';
$string['issuetoall'] = 'Select all >';
$string['nobadges'] = 'No badges created yet.';
$string['noevents'] = 'No issuance events yet.';
$string['notadminforinstitution'] = 'Only the institution admin can perform'
        . ' administration tasks for this plugin.';
$string['numberofrecipients'] = '%s recipients';
$string['openbadgefactory']   = 'Open Badge Factory';
$string['privileges'] = 'Issuance settings';
$string['reauthenticate'] = 'Re-authenticate anyway';
$string['recipients'] = 'Recipients';
$string['requesttoken'] = 'Request token';
$string['save'] = 'Save';
$string['savebackpack'] = 'Connect to Mozilla Backpack';
$string['saveemail'] = 'Save message';
$string['selectissuancecategories'] = 'Available categories';
$string['selectissuancecategorieshelp'] = 'Select the badge categories of which '
        . 'badges can be used in issuance events of this institution. If none '
        . 'of the categories is selected, the badges from every category can be '
        . 'issued. Click the name of the category while holding the CTRL button to deselect it.';
$string['settings'] = 'Settings';
$string['showallbadges'] = 'Reset filter';
$string['tokenerror'] = 'Decrypting the request token failed. Be sure to paste the '
        . 'certificate signing request token fully.';
$string['youhaveissuedbadgesmessage'] = 'You have issued the badge "%s"'
        . ' to following group members:

%s';
$string['youhaveissuedbadgessubject'] = 'You have issued a badge';
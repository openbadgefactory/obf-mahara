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

$string['alreadyauthenticated'] = 'Yhteys Open Badge Factoryyn on jo muodostettu.';
$string['apierror'] = 'Tietojen haku Open Badge Factorysta epäonnistui. Yritä'
        . ' hetken kuluttua uudelleen.';
$string['authenticate'] = 'Suorita valtuutus';
$string['authenticationhelp'] = 'Open Badge Factoryn käyttö ei ole mahdollista'
        . ' ennen sertifikaatin luontia. Kirjaudu Open Badge Factoryyn luodaksesi'
        . ' valtuutusavaimen ja liitä se oheiseen tekstikenttään.';
$string['authenticationsuccessful'] = 'Valtuutus onnistui.';
$string['backpackconnectedhelp'] = 'Yhteys Mozilla Backpackiin on muodostettu'
        . ' käyttäen sähköpostiosoitettasi <strong>%s</strong>.';
$string['backpackdisconnected'] = 'Yhteys Mozilla Backpackiin katkaistu.';
$string['backpackhelp'] = 'Jotta voit vastaanottaa Open Badges -osaamismerkkejä,'
        . ' profiilisi tietoihin tulee tallentaa Mozilla Backpackissa käyttämäsi'
        . ' sähköpostiosoite.';
$string['backpacksettings'] = 'Backpack-asetukset';
$string['backtobadgelist'] = '< Takaisin osaamismerkkilistaan';
$string['badgecreated'] = 'Luotu';
$string['badgecreatedn'] = 'Luotu %s';
$string['badgedescription'] = 'Kuvaus';
$string['badgedetails'] = 'Merkin tiedot';
$string['badgeemail'] = 'Sähköpostiviesti';
$string['badgegrouphistory'] = 'Myöntämishistoria';
$string['badgehistory'] = 'Myöntämishistoria';
$string['badgename'] = 'Nimi';
$string['badges'] = 'Osaamismerkit';
$string['badgesuccessfullyissued'] = 'Osaamismerkin myöntäminen onnistui.';
$string['certdirectorynotwritable'] = 'Sertifikaatin tallennus epäonnistui.'
        . ' Järjestelmän ylläpitäjän tulee tarkistaa, että hakemisto sertifikaateille'
        . ' on luotu ja että palvelinprosessilla on siihen kirjoitusoikeudet.';
$string['certificateisexpiring'] = 'Open Badge Factoryn sertifikaatti on vanhentumassa.';
$string['certificateisexpiringmessage'] = 'Instituutiosi %s sertifikaatti '
        . 'Open Badge Factoryyn vanhenee %s päivän kuluttua. Uusiaksesi sertifikaatin '
        . 'kirjaudu Open Badge Factoryyn organisaatiosi ylläpitotunnuksella, luo ja kopioi '
        . 'valtuutusavain ja liitä se tekstikenttään instituutiosi asetussivulla. '
        . 'Tarkemmat ohjeet näet OBF-lisäosan manuaalista.';
$string['confirmdeauthentication'] = 'Oletko varma, että haluat katkaista valtuutuksen?';
$string['deauthenticate'] = 'Katkaise valtuutus';
$string['disconnectbackpack'] = 'Katkaise yhteys';
$string['email'] = 'Sähköpostiviesti';
$string['emailbody'] = 'Viestin alkuosa';
$string['emailfooter'] = 'Viestin loppuosa';
$string['emailsubject'] = 'Viestin otsikko';
$string['emailtemplatesaved'] = 'Sähköpostiviesti tallennettu.';
$string['errorfetchingbadges'] = 'Osaamismerkkien haku epäonnistui.';
$string['expiresat'] = 'Erääntyy';
$string['filterbadges'] = 'Suodata merkkejä:';
$string['groupmembers'] = 'Ryhmän jäsenet';
$string['grouprecipients'] = 'Merkin saajat';
$string['history'] = 'Myöntämishistoria';
$string['institutionissuermembers'] = 'Instituution jäsenet, joilla on oikeus'
        . ' myöntää osaamismerkkejä';
$string['institutionissuers'] = 'Anna myöntämisoikeudet';
$string['institutionissuersupdated'] = 'Myöntämisoikeudet päivitetty';
$string['institutionmembers'] = 'Instituution jäsenet';
$string['institutionselectordescription'] = '';
$string['issuancedetails'] = 'Myöntämisasetukset';
$string['issuancefailed'] = 'Merkin myöntäminen epäonnistui. Yritä myöhemmin uudelleen.';
$string['issuebadge'] = 'Myönnä osaamismerkki';
$string['issuedat'] = 'Myönnetty';
$string['issuetoall'] = 'Valitse kaikki >';
$string['nobadges'] = 'Osaamismerkkejä ei ole vielä luotu.';
$string['noevents'] = 'Ei vielä myöntämistapahtumia.';
$string['notadminforinstitution'] = 'Ainostaan instituution ylläpitäjä voi suorittaa ylläpitotoimia.';
$string['numberofrecipients'] = '%s vastaanottajaa';
$string['openbadgefactory']   = 'Open Badge Factory';
$string['privileges'] = 'Myöntämisoikeudet';
$string['reauthenticate'] = 'Tee valtuutus uudelleen siitä huolimatta.';
$string['recipients'] = 'Merkin saajat';
$string['requesttoken'] = 'Valtuutusavain';
$string['save'] = 'Tallenna';
$string['savebackpack'] = 'Yhdistä Mozilla Backpackiin';
$string['saveemail'] = 'Tallenna viesti';
$string['settings'] = 'Asetukset';
$string['showallbadges'] = 'Näytä kaikki';
$string['tokenerror'] = 'Valtuutus epäonnistui. Ole hyvä ja tarkista, että '
        . 'kirjoitit valtuutusavaimen kokonaisuudessaan.';
$string['youhaveissuedbadgesmessage'] = 'Olet myöntänyt osaamismerkin "%s"'
        . ' seuraaville jäsenille:
            
%s';
$string['youhaveissuedbadgessubject'] = 'Vahvistus osaamismerkin myöntämisestä';
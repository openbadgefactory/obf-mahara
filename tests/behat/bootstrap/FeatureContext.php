<?php

use Behat\Behat\Context\Step\Given;
use Behat\Behat\Context\Step\Then;
use Behat\Behat\Event\SuiteEvent;
use Behat\MinkExtension\Context\MinkContext;
use Behat\MinkExtension\Context\RawMinkContext;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//
define('TESTSRUNNING', 1);
define('INTERNAL', 1);
define('PUBLIC', 1);
define('BEHAT_ERROR_REPORTING', E_ALL ^ E_NOTICE);

$mahararoot = '/var/www/kyvytfi';

global $CFG, $db, $USER, $THEME, $SESSION;

require_once(__DIR__ . '/../../../vendor/behat/behat/src/Behat/Behat/Exception/ErrorException.php');
require_once($mahararoot . '/init.php');
require_once($mahararoot . '/lib/institution.php');
require_once($mahararoot . '/interaction/obf/lib.php');

/**
 * Features context.
 */
class FeatureContext extends RawMinkContext {

    private $institutionadmin = 'test_admin';
    private $password = 'test_password';

    /**
     * @BeforeSuite
     */
    public static function prepare(SuiteEvent $event) {
        set_config('dropdownmenu', 0);
        set_config('theme', 'default');
    }

    /**
     * Initializes context.
     * Every scenario gets it's own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters) {
        $this->useContext('mink', new MinkContext);

        // Initialize your context here
    }

    /**
     * @Given /^I log in as institution admin$/
     */
    public function iLogInAsInstitutionAdmin() {
        return array(
            new Given('I am on the homepage'),
            new Then('I fill in "login_username" with "' . $this->institutionadmin . '"'),
            new Then('I fill in "login_password" with "' . $this->password . '"'),
            new Then('I press "submit"')
        );
    }

    /**
     * @Given /^institutions "([^"]*)" and "([^"]*)" exist$/
     */
    public function institutionsAndExist($institutionname1, $institutionname2) {
        $this->ensureInstitutionExists($institutionname1);
        $this->ensureInstitutionExists($institutionname2);
    }

    private function ensureInstitutionExists($name) {
        $institution = new Institution();
        $sanitizedname = $this->sanitize($name);

        if ($institution->findByName($sanitizedname) === false) {
            $institution->name = $sanitizedname;
            $institution->displayname = $name;
            $institution->commit();
        }

        if ($institution->findByName($sanitizedname) === false) {
            throw new Exception('Institution "' . $name . ' doesn\'t exist');
        }
    }

    private function sanitize($name) {
        return strtolower(preg_replace("/[^a-zA-Z]/", "", $name));
    }

    /**
     * @Given /^I am the admin of "([^"]*)" and "([^"]*)"$/
     */
    public function iAmTheAdminOfAnd($institution1, $institution2) {
        $user = $this->ensureUserExists($this->institutionadmin);
        $this->ensureUserIsAdminOf($user, $institution1);
        $this->ensureUserIsAdminOf($user, $institution2);
    }

    private function ensureUserIsAdminOf($user, $institutionname) {
        $sanitized = $this->sanitize($institutionname);
        $user->join_institution($sanitized);

        execute_sql('UPDATE {usr_institution} SET admin = 1 WHERE usr = ' . (int) $user->id);

        $user->reset_institutions();

        if (!$user->is_institutional_admin($sanitized)) {
            throw new Exception('User "' . $user->username . '" isn\'t admin of "' . $institutionname . '"');
        }
    }

    private function ensureUserExists($name) {
        $user = new User();
        $userid = -1;

        try {
            $user->find_by_username($name);
            $userid = $user->id;
        } catch (AuthUnknownUseErxception $ex) {
            $user->username = $name;
            $user->password = $this->password;
            $user->firstname = 'Test';
            $user->lastname = 'Admin';
            $user->email = 'test@example.com';

            $userid = create_user($user->to_stdclass());
        }


        if (!$userid) {
            throw new Exception('User "' . $name . '" does not exist and couldn\'t be created');
        }

        return $user->find_by_id($userid);
    }

    /**
     * @Given /^I go to the institution admin page$/
     */
    public function iGoToTheInstitutionAdminPage() {
        $this->getSession()->visit($this->locatePath('/admin/users/institutions.php'));
    }

    /**
     * @When /^I go to Open Badge Factory management page$/
     */
    public function iGoToOpenBadgeFactoryManagementPage() {
        $this->getSession()->visit($this->locatePath('/interaction/obf/institution.php'));
    }

    /**
     * @Given /^I submit a wrong token$/
     */
    public function iSubmitAWrongToken() {
        return array(
            new Given('I fill in "token_token" with "foobar"'),
            new Then('I press "Suorita valtuutus"')
        );
    }

    /**
     * @When /^I submit a valid token via management page$/
     */
    public function iSubmitAValidTokenViaManagementPage()
    {
        return array(
            new Given('I fill in "token_token" with "VALID_TEST_TOKEN"'),
            new Then('I press "Suorita valtuutus"')
        );
    }

    /**
     * @Given /^"([^"]*)" is not authenticated$/
     */
    public function isNotAuthenticated($institutionname) {
        $institutionid = $this->sanitize($institutionname);
        PluginInteractionObf::deauthenticate($institutionid);
    }

}

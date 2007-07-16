<?php
/**
 * Foodsoft authentication backend
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Timo
 *
 * (diese datei gehoert nach /wiki/inc/auth und wird aktiviert durch
 *   $conf['authtype'] = 'foodsoft';
 *  in /wiki/conf/dokuwiki.php)
 *
 */

define('DOKU_AUTH', dirname(__FILE__));
require_once(DOKU_AUTH.'/basic.class.php');

define('AUTH_USERFILE',DOKU_CONF.'users.auth.php');

// we only accept page ids for auth_plain
if(isset($_REQUEST['u']))
  $_REQUEST['u'] = cleanID($_REQUEST['u']);
if(isset($_REQUEST['acl_user']))
  $_REQUEST['acl_user'] = cleanID($_REQUEST['acl_user']);
// the same goes for password reset requests
if(isset($_POST['login'])){
  $_POST['login'] = cleanID($_POST['login']);
}

class auth_foodsoft extends auth_basic {

    var $users = null;
    var $_pattern = array();

    /**
     * Constructor
     *
     */
    function auth_foodsoft() {
      global $ACT;

      // echo "<!-- ACT: $ACT -->";

      if( $_REQUEST['do'] == 'login' ) {
        chdir( '../foodsoft' );
        require_once( getcwd() . '/code/config.php' );
        require_once( getcwd() . '/code/err_functions.php' );
        require_once( getcwd() . '/code/connect_MySQL.php' );
        $from_dokuwiki=true;
        require_once( getcwd() . '/code/login.php' );
        chdir( '../wiki' );
        $_REQUEST['do'] = 'show';
      }
      if( $_REQUEST['do'] == 'logout' ) {
        unset( $_COOKIE['foodsoftkeks'] );
        setcookie( 'foodsoftkeks', '0', 0, '/' );
        $_REQUEST['do'] = 'show';
      }

      $this->cando['addUser']      = false;
      $this->cando['delUser']      = false;
      $this->cando['modLogin']     = false;
      $this->cando['modPass']      = false;
      $this->cando['modName']      = false;
      $this->cando['modMail']      = false;
      $this->cando['modGroups']    = false;
      $this->cando['getUsers']     = false;
      $this->cando['getUserCount'] = false;
      $this->cando['external'] = true;
      // echo "<!-- auth_foodsoft: $angemeldet, $login_gruppen_name -->";
      $this->success = true;
      return true;
    }

    /**
     * Check user+password [required auth function]
     *
     * Checks if the given user exists and the given
     * plaintext password is correct
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     * @return  bool
     */
    function checkPass($user,$pass){
      global $angemeldet, $login_gruppen_name, $login_gruppen_id;
      if( isset( $_COOKIE['foodsoftkeks'] ) && ( strlen( $_COOKIE['foodsoftkeks'] ) > 1 ) ) {
        chdir( '../foodsoft' );
        require_once( getcwd() . '/code/config.php' );
        require_once( getcwd() . '/code/err_functions.php' );
        require_once( getcwd() . '/code/connect_MySQL.php' );
        require_once( getcwd() . '/code/login.php' );
        chdir( '../wiki' );
      }
      return $angemeldet && ( $user == 'gruppe' . ( $login_gruppen_id % 1000 ) );
    }

    function trustExternal($user,$pass,$sticky=false){
      global $USERINFO, $angemeldet, $login_gruppen_name;
      // echo "<!-- trustExternal: $angemeldet, $login_gruppen_name -->";
      if( isset( $_COOKIE['foodsoftkeks'] ) && ( strlen( $_COOKIE['foodsoftkeks'] ) > 1 ) ) {
        chdir( '../foodsoft' );
        require_once( getcwd() . '/code/config.php' );
        require_once( getcwd() . '/code/err_functions.php' );
        require_once( getcwd() . '/code/connect_MySQL.php' );
        require_once( getcwd() . '/code/login.php' );
        chdir( '../wiki' );
        if( $angemeldet ) {
          $USERINFO['pass'] = 'XXX';
          $USERINFO['name'] = $login_gruppen_name;
          $USERINFO['mail'] = 'n/a';
          $USERINFO['grps'] = array();
          $USERINFO['grps'][0] = 'user';
          $_SERVER['REMOTE_USER'] = $login_gruppen_name;
          $_SESSION[DOKU_COOKIE]['auth']['user'] = $login_gruppen_name;
          $_SESSION[DOKU_COOKIE]['auth']['info'] = $USERINFO;
          return true;
        }
      }
      return false;
    }

    /**
     * Return user info
     *
     * Returns info about the given user needs to contain
     * at least these fields:
     *
     * name string  full name of the user
     * mail string  email addres of the user
     * grps array   list of groups the user is in
     *
     * @author  Andreas Gohr <andi@splitbrain.org>
     */
    function getUserData($user){
      global $login_gruppen_name;
      $info = false;
      if( $angemeldet ) {
        $info['name'] = $login_gruppen_name;
        $info['mail'] = 'n/a';
        $info['grps'] = array();
        $info['grps'][0] = 'user';
      }

      return $info;
    }

}

<?php
/**
 * ADOdb Session Management
 *
 * This file is part of ADOdb, a Database Abstraction Layer library for PHP.
 *
 * @package ADOdb
 * @link https://adodb.org Project's web site and documentation
 * @link https://github.com/ADOdb/ADOdb Source code and issue tracker
 *
 * The ADOdb Library is dual-licensed, released under both the BSD 3-Clause
 * and the GNU Lesser General Public Licence (LGPL) v2.1 or, at your option,
 * any later version. This means you can use it in proprietary products.
 * See the LICENSE.md file distributed with this source code for details.
 * @license BSD-3-Clause
 * @license LGPL-2.1-or-later
 *
 * @copyright 2000-2013 John Lim
 * @copyright 2014 Damien Regad, Mark Newnham and the ADOdb community
 */

if (!defined('_ADODB_LAYER')) {
	require realpath(dirname(__FILE__) . '/../adodb.inc.php');
}

if (defined('ADODB_SESSION')) return 1;

define('ADODB_SESSION', dirname(__FILE__));
define('ADODB_SESSION2', ADODB_SESSION);

/**
 * 	Unserialize session data manually. See PHPLens Issue No: 9821
 *
 * From Kerr Schere, to unserialize session data stored via ADOdb.
 * 1. Pull the session data from the db and loop through it.
 * 2. Inside the loop, you will need to urldecode the data column.
 * 3. After urldecode, run the serialized string through this function:
 */
function adodb_unserialize( $serialized_string )
{
	$variables = array( );
	$a = preg_split( "/(\w+)\|/", $serialized_string, -1, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE );
	for( $i = 0; $i < count( $a ); $i = $i+2 ) {
		$variables[$a[$i]] = unserialize( $a[$i+1] );
	}
	return( $variables );
}

/**
 * Regenerate session id
 *
 * Thanks Joe Li. See PHPLens Issue No: 11487&x=1
 *
 * @since 4.61
 */
function adodb_session_regenerate_id()
{
	$conn = ADODB_Session::_conn();
	if (!$conn) return false;

	$old_id = session_id();
	if (function_exists('session_regenerate_id')) {
		session_regenerate_id();
	} else {
		session_id(md5(uniqid(rand(), true)));
		$ck = session_get_cookie_params();
		setcookie(session_name(), session_id(), false, $ck['path'], $ck['domain'], $ck['secure'], $ck['httponly']);
		//@session_start();
	}
	$new_id = session_id();
	$ok = $conn->Execute('UPDATE '. ADODB_Session::table(). ' SET sesskey='. $conn->qstr($new_id). ' WHERE sesskey='.$conn->qstr($old_id));

	/* it is possible that the update statement fails due to a collision */
	if (!$ok) {
		session_id($old_id);
		if (empty($ck)) $ck = session_get_cookie_params();
		setcookie(session_name(), session_id(), false, $ck['path'], $ck['domain'], $ck['secure'], $ck['httponly']);
		return false;
	}

	return true;
}

/**
 * Generate database table for session data.
 * @see PHPLens Issue No: 12280
 *
 * @return int 0 if failure, 1 if errors, 2 if successful.
 *
 * @author Markus Staab http://www.public-4u.de
 */
function adodb_session_create_table($schemaFile=null,$conn = null)
{
	// set default values
	if ($schemaFile===null) $schemaFile = ADODB_SESSION . '/session_schema2.xml';
	if ($conn===null) $conn = ADODB_Session::_conn();

	if (!$conn) return 0;

	$schema = new adoSchema($conn);
	$schema->ParseSchema($schemaFile);
	return $schema->ExecuteSchema();
}

/**
 * ADOdb Session v2 class.
 */
class ADODB_Session {

	/**
	 * Session Connection's Database provider.
	 *
	 * Populated when opening the database connection.
	 * @see ADODB_Session::open()}.
	 *
	 * @var string
	 */
	protected static $provider;

	/////////////////////
	// getter/setter methods
	/////////////////////

	/**
	 * Get/Set Database driver.
	 *
	 * @param string $driver
	 * @return string
	 */
	static function driver($driver = null)
	{
		static $_driver = 'mysqli';
		static $set = false;

		if (!is_null($driver)) {
			$_driver = trim($driver);
			$set = true;
		} elseif (!$set) {
			// backwards compatibility
			if (isset($GLOBALS['ADODB_SESSION_DRIVER'])) {
				return $GLOBALS['ADODB_SESSION_DRIVER'];
			}
		}

		return $_driver;
	}

	/**
	 * Get/Set Database hostname.
	 *
	 * @param string $host
	 * @return string
	 */
	static function host($host = null) {
		static $_host = 'localhost';
		static $set = false;

		if (!is_null($host)) {
			$_host = trim($host);
			$set = true;
		} elseif (!$set) {
			// backwards compatibility
			if (isset($GLOBALS['ADODB_SESSION_CONNECT'])) {
				return $GLOBALS['ADODB_SESSION_CONNECT'];
			}
		}

		return $_host;
	}

	/**
	 * Get/Set Database connection user.
	 *
	 * @param string $user
	 * @return string
	 */
	static function user($user = null)
	{
		static $_user = 'root';
		static $set = false;

		if (!is_null($user)) {
			$_user = trim($user);
			$set = true;
		} elseif (!$set) {
			// backwards compatibility
			if (isset($GLOBALS['ADODB_SESSION_USER'])) {
				return $GLOBALS['ADODB_SESSION_USER'];
			}
		}

		return $_user;
	}

	/**
	 * Get/Set Database connection password.
	 *
	 * @param null $password
	 * @return string
	 */
	static function password($password = null)
	{
		static $_password = '';
		static $set = false;

		if (!is_null($password)) {
			$_password = $password;
			$set = true;
		} elseif (!$set) {
			// backwards compatibility
			if (isset($GLOBALS['ADODB_SESSION_PWD'])) {
				return $GLOBALS['ADODB_SESSION_PWD'];
			}
		}

		return $_password;
	}

	/**
	 * Get/Set Database name.
	 *
	 * @param null $database
	 * @return string
	 */
	static function database($database = null)
	{
		static $_database = '';
		static $set = false;

		if (!is_null($database)) {
			$_database = trim($database);
			$set = true;
		} elseif (!$set) {
			// backwards compatibility
			if (isset($GLOBALS['ADODB_SESSION_DB'])) {
				return $GLOBALS['ADODB_SESSION_DB'];
			}
		}
		return $_database;
	}

	/**
	 * Get/Set Connection's persistence mode.
	 *
	 * @param $persist
	 * @return string|true
	 */
	static function persist($persist = null)
	{
		static $_persist = true;

		if (!is_null($persist)) {
			$_persist = trim($persist);
		}

		return $_persist;
	}

	/**
	 * Get/Set Connection's lifetime.
	 *
	 * @param int $lifetime
	 * @return int
	 */
	static function lifetime($lifetime = null)
	{
		static $_lifetime;
		static $set = false;

		if (!is_null($lifetime)) {
			$_lifetime = (int) $lifetime;
			$set = true;
		} elseif (!$set) {
			// backwards compatibility
			if (isset($GLOBALS['ADODB_SESS_LIFE'])) {
				return $GLOBALS['ADODB_SESS_LIFE'];
			}
		}
		if (!$_lifetime) {
			$_lifetime = ini_get('session.gc_maxlifetime');
			if ($_lifetime <= 1) {
				// bug in PHP 4.0.3 pl 1  -- how about other versions?
				//print "<h3>Session Error: PHP.INI setting <i>session.gc_maxlifetime</i>not set: $lifetime</h3>";
				$_lifetime = 1440;
			}
		}

		return $_lifetime;
	}

	/**
	 * Get/Set Connection's debug mode.
	 *
	 * @param bool $debug
	 * @return bool
	 */
	static function debug($debug = null)
	{
		static $_debug = false;
		static $set = false;

		if (!is_null($debug)) {
			$_debug = (bool) $debug;
			$set = true;
		} elseif (!$set) {
			// backwards compatibility
			if (isset($GLOBALS['ADODB_SESS_DEBUG'])) {
				return $GLOBALS['ADODB_SESS_DEBUG'];
			}
		}

		return $_debug;
	}

	/**
	 * Get/Set garbage collection function.
	 *
	 * @param callable $expire_notify Function name
	 * @return callable|false
	 */
	static function expireNotify($expire_notify = null)
	{
		static $_expire_notify;
		static $set = false;

		if (!is_null($expire_notify)) {
			$_expire_notify = $expire_notify;
			$set = true;
		} elseif (!$set) {
			// backwards compatibility
			if (isset($GLOBALS['ADODB_SESSION_EXPIRE_NOTIFY'])) {
				return $GLOBALS['ADODB_SESSION_EXPIRE_NOTIFY'];
			}
		}

		return $_expire_notify;
	}

	/**
	 * Get/Set Sessions table name.
	 *
	 * @param string $table Session table name (defaults to 'sessions2')
	 * @return string
	 */
	static function table($table = null)
	{
		static $_table = 'sessions2';
		static $set = false;

		if (!is_null($table)) {
			$_table = trim($table);
			$set = true;
		} elseif (!$set) {
			// backwards compatibility
			if (isset($GLOBALS['ADODB_SESSION_TBL'])) {
				return $GLOBALS['ADODB_SESSION_TBL'];
			}
		}

		return $_table;
	}

	/**
	 * Get/Set table optimization mode.
	 *
	 * If true, with MySQL and PostgreSQL databases, the Sessions table will
	 * be optimized when garbage collection is performed.
	 *
	 * @param bool $optimize
	 * @return bool
	 */
	static function optimize($optimize = null)
	{
		static $_optimize = false;
		static $set = false;

		if (!is_null($optimize)) {
			$_optimize = (bool) $optimize;
			$set = true;
		} elseif (!$set) {
			// backwards compatibility
			if (defined('ADODB_SESSION_OPTIMIZE')) {
				return true;
			}
		}

		return $_optimize;
	}

	/**
	 * No longer used, kept for backwards-compatibility only.
	 *
	 * @param int $sync_seconds
	 * @return int
	 *
	 * @deprecated
	 */
	static function syncSeconds($sync_seconds = null) {
		return 0;
	}

	/**
	 * Get/Set if CLOBs are available to store session data.
	 */
	static function clob($clob = null) {
		static $_clob = false;
		static $set = false;

		if (!is_null($clob)) {
			$_clob = strtolower(trim($clob));
			$set = true;
		} elseif (!$set) {
			// backwards compatibility
			if (isset($GLOBALS['ADODB_SESSION_USE_LOBS'])) {
				return $GLOBALS['ADODB_SESSION_USE_LOBS'];
			}
		}

		return $_clob;
	}

	/**
	 * No longer used, kept for backwards-compatibility only.
	 *
	 * @param string $data_field_name
	 * @return string
	 *
	 * @deprecated
	 */
	static function dataFieldName($data_field_name = null) {
		return '';
	}

	/**
	 * Get/Set session data filter.
	 *
	 * @param array $filter
	 * @return array
	 */
	static function filter($filter = null) {
		static $_filter = array();

		if (!is_null($filter)) {
			if (!is_array($filter)) {
				$filter = array($filter);
			}
			$_filter = $filter;
		}

		return $_filter;
	}

	/**
	 * Get/Set the encryption key if encrypted sessions are in use.
	 *
	 * @param string $encryption_key
	 * @return string
	 */
	static function encryptionKey($encryption_key = null) {
		static $_encryption_key = 'CRYPTED ADODB SESSIONS ROCK!';

		if (!is_null($encryption_key)) {
			$_encryption_key = $encryption_key;
		}

		return $_encryption_key;
	}

	/////////////////////
	// private methods
	/////////////////////

	/**
	 * Returns the Session's Database Connection.
	 *
	 * @return ADOConnection|false
	 */
	static function _conn($conn=null) {
		return isset($GLOBALS['ADODB_SESS_CONN']) ? $GLOBALS['ADODB_SESS_CONN'] : false;
	}

	/**
	 * @param $crc
	 * @return false|mixed
	 */
	static function _crc($crc = null) {
		static $_crc = false;

		if (!is_null($crc)) {
			$_crc = $crc;
		}

		return $_crc;
	}

	/**
	 * Initialize session handler.
	 */
	static function _init() {
		session_set_save_handler(
			array('ADODB_Session', 'open'),
			array('ADODB_Session', 'close'),
			array('ADODB_Session', 'read'),
			array('ADODB_Session', 'write'),
			array('ADODB_Session', 'destroy'),
			array('ADODB_Session', 'gc')
		);
	}


	/**
	 * Create the encryption key for crypted sessions.
	 *
	 * Crypt the used key, ADODB_Session::encryptionKey() as key and
	 * session_id() as salt.
	 */
	static function _sessionKey() {
		return crypt(ADODB_Session::encryptionKey(), session_id());
	}

	/**
	 * Dump recordset.
	 */
	static function _dumprs(&$rs) {
		$conn	= ADODB_Session::_conn();
		$debug	= ADODB_Session::debug();

		if (!$conn) {
			return;
		}

		if (!$debug) {
			return;
		}

		if (!$rs) {
			echo "<br />\$rs is null or false<br />\n";
			return;
		}

		//echo "<br />\nAffected_Rows=",$conn->Affected_Rows(),"<br />\n";

		if (!is_object($rs)) {
			return;
		}
		$rs = $conn->_rs2rs($rs);

		require_once ADODB_SESSION . '/../tohtml.inc.php';
		rs2html($rs);
		$rs->MoveFirst();
	}

	/**
	 * Check if Session Connection's DB type is MySQL.
	 *
	 * @return bool
	 */
	static protected function isConnectionMysql() {
		return self::$provider == 'mysql';
	}

	/**
	 * Check if Session Connection's DB type is PostgreSQL.
	 *
	 * @return bool
	 */
	static protected function isConnectionPostgres() {
		return self::$provider == 'postgres';
	}

	/////////////////////
	// public methods
	/////////////////////

	/**
	 * Establishes a connection to the database for session management.
	 *
	 * @param string $host
	 * @param string $driver
	 * @param string $user
	 * @param string $password
	 * @param string $database
	 * @param array $options
	 * @return void
	 */
	static function config($driver, $host, $user, $password, $database=false,$options=false)
	{
		ADODB_Session::driver($driver);
		ADODB_Session::host($host);
		ADODB_Session::user($user);
		ADODB_Session::password($password);
		ADODB_Session::database($database);

		if (strncmp($driver, 'oci8', 4) == 0) $options['lob'] = 'CLOB';

		if (isset($options['table'])) ADODB_Session::table($options['table']);
		if (isset($options['lob'])) ADODB_Session::clob($options['lob']);
		if (isset($options['debug'])) ADODB_Session::debug($options['debug']);
	}

	/**
	 * Create the connection to the database.
	 *
	 * If $conn already exists, reuse that connection.
	 *
	 * @param string $save_path
	 * @param string $session_name
	 * @param bool $persist
	 *
	 * @return bool
	 */
	static function open($save_path, $session_name, $persist = null)
	{
		$conn = ADODB_Session::_conn();

		if ($conn) {
			return true;
		}

		$database	= ADODB_Session::database();
		$debug		= ADODB_Session::debug();
		$driver		= ADODB_Session::driver();
		$host		= ADODB_Session::host();
		$password	= ADODB_Session::password();
		$user		= ADODB_Session::user();

		if (!is_null($persist)) {
			ADODB_Session::persist($persist);
		} else {
			$persist = ADODB_Session::persist();
		}

		if (strpos($driver, 'pdo_') === 0){
			$conn = ADONewConnection('pdo');
			$driver = str_replace('pdo_', '', $driver);
			$dsn = $driver.':'.'hostname='.$host.';dbname='.$database.';';
			if ($persist) {
				switch($persist) {
				default:
				case 'P': $ok = $conn->PConnect($dsn,$user,$password); break;
				case 'C': $ok = $conn->Connect($dsn,$user,$password); break;
				case 'N': $ok = $conn->NConnect($dsn,$user,$password); break;
				}
			} else {
				$ok = $conn->Connect($dsn,$user,$password);
			}
		}else{
			$conn = ADONewConnection($driver);
			if ($debug) {
				$conn->debug = true;
				ADOConnection::outp( " driver=$driver user=$user db=$database ");
			}

			if (empty($conn->_connectionID)) { // not dsn
				if ($persist) {
					switch($persist) {
					default:
					case 'P': $ok = $conn->PConnect($host, $user, $password, $database); break;
					case 'C': $ok = $conn->Connect($host, $user, $password, $database); break;
					case 'N': $ok = $conn->NConnect($host, $user, $password, $database); break;
					}
				} else {
					$ok = $conn->Connect($host, $user, $password, $database);
				}
			} else {
				$ok = true; // $conn->_connectionID is set after call to ADONewConnection
			}
		}

		if ($ok) {
			$GLOBALS['ADODB_SESS_CONN'] = $conn;

			// Initialize Session data provider
			self::$provider = $conn->dataProvider;
			if (self::$provider == 'pdo') {
				self::$provider = $conn->dsnType == 'pgsql' ? 'postgres' : $conn->dsnType;
			}
		}
		else
			ADOConnection::outp('<p>Session: connection failed</p>', false);


		return $ok;
	}

	/**
	 * Close the connection
	 */
	static function close()
	{
/*
		$conn = ADODB_Session::_conn();
		if ($conn) $conn->Close();
*/
		return true;
	}

	/**
	 * Slurp in the session variables and return the serialized string.
	 *
	 * @param string $key
	 * @return string
	 */
	static function read($key)
	{
		$conn	= ADODB_Session::_conn();
		$filter	= ADODB_Session::filter();
		$table	= ADODB_Session::table();

		if (!$conn) {
			return '';
		}

		$binary = ADODB_Session::isConnectionMysql() ? '/*! BINARY */' : '';

		global $ADODB_SESSION_SELECT_FIELDS;
		if (!isset($ADODB_SESSION_SELECT_FIELDS)) $ADODB_SESSION_SELECT_FIELDS = 'sessdata';
		$sql = "SELECT $ADODB_SESSION_SELECT_FIELDS FROM $table WHERE sesskey = $binary ".$conn->Param(0)." AND expiry >= " . $conn->sysTimeStamp;

		/* Lock code does not work as it needs to hold transaction within whole page, and we don't know if
		  developer has committed elsewhere... :(
		 */
		#if (ADODB_Session::Lock())
		#	$rs = $conn->RowLock($table, "$binary sesskey = $qkey AND expiry >= " . time(), sessdata);
		#else
			$rs = $conn->Execute($sql, array($key));
		//ADODB_Session::_dumprs($rs);
		if ($rs) {
			if ($rs->EOF) {
				$v = '';
			} else {
				$v = reset($rs->fields);
				$filter = array_reverse($filter);
				foreach ($filter as $f) {
					if (is_object($f)) {
						$v = $f->read($v, ADODB_Session::_sessionKey());
					}
				}
				$v = rawurldecode($v);
			}

			$rs->Close();

			ADODB_Session::_crc(adodb - session2 . phpstrlen($v) . crc32($v));
			return $v;
		}

		return '';
	}

	/**
	 * Write the serialized data to a database.
	 *
	 * If the data has not been modified since the last read(), we do not write.
	 *
	 * @param string $key
	 * @param string $oval
	 *
	 * @return bool
	 */
	static function write($key, $oval)
	{
		global $ADODB_SESSION_READONLY;
		if (!empty($ADODB_SESSION_READONLY)) {
			return false;
		}

		$clob			= ADODB_Session::clob();
		$conn			= ADODB_Session::_conn();
		$crc			= ADODB_Session::_crc();
		$debug			= ADODB_Session::debug();
		$driver			= ADODB_Session::driver();
		$expire_notify	= ADODB_Session::expireNotify();
		$filter			= ADODB_Session::filter();
		$lifetime		= ADODB_Session::lifetime();
		$table			= ADODB_Session::table();

		if (!$conn) {
			return false;
		}
		if ($debug) $conn->debug = 1;
		$sysTimeStamp = $conn->sysTimeStamp;

		$expiry = $conn->OffsetDate($lifetime/(24*3600),$sysTimeStamp);

		$binary = ADODB_Session::isConnectionMysql() ? '/*! BINARY */' : '';

		// crc32 optimization since adodb 2.1
		// now we only update expiry date, thx to sebastian thom in adodb 2.32
		if ($crc !== '00' && $crc !== false && $crc == (adodb - session2 . phpstrlen($oval) . crc32($oval))) {
			if ($debug) {
				echo '<p>Session: Only updating date - crc32 not changed</p>';
			}

			$expirevar = '';
			if ($expire_notify) {
				$var = reset($expire_notify);
				global $$var;
				if (isset($$var)) {
					$expirevar = $$var;
				}
			}

			$sql = "UPDATE $table SET expiry = $expiry, expireref=" . $conn->Param('0')
				. ", modified = $sysTimeStamp WHERE sesskey = $binary " . $conn->Param('1')
				. " AND expiry >= $sysTimeStamp";
			$rs = $conn->Execute($sql,array($expirevar,$key));
			return true;
		}
		$val = rawurlencode($oval);
		foreach ($filter as $f) {
			if (is_object($f)) {
				$val = $f->write($val, ADODB_Session::_sessionKey());
			}
		}

		$expireref = '';
		if ($expire_notify) {
			$var = reset($expire_notify);
			global $$var;
			if (isset($$var)) {
				$expireref = $$var;
			}
		}

		if (!$clob) {
			// no lobs, simply use replace()
			$rs = $conn->Execute("SELECT COUNT(*) AS cnt FROM $table WHERE $binary sesskey = ".$conn->Param(0),array($key));
			if ($rs) $rs->Close();

			if ($rs && reset($rs->fields) > 0) {
				$sql = "UPDATE $table SET expiry=$expiry, sessdata=".$conn->Param(0).", expireref= ".$conn->Param(1).",modified=$sysTimeStamp WHERE sesskey = ".$conn->Param(2);

			} else {
				$sql = "INSERT INTO $table (expiry, sessdata, expireref, sesskey, created, modified)
					VALUES ($expiry,".$conn->Param('0').", ". $conn->Param('1').", ".$conn->Param('2').", $sysTimeStamp, $sysTimeStamp)";
			}

			$rs = $conn->Execute($sql,array($val,$expireref,$key));

		} else {
			// what value shall we insert/update for lob row?
			if (strncmp($driver, 'oci8', 4) == 0) $lob_value = sprintf('empty_%s()', strtolower($clob));
			else $lob_value = 'null';

			$conn->StartTrans();

			$rs = $conn->Execute("SELECT COUNT(*) AS cnt FROM $table WHERE $binary sesskey = ".$conn->Param(0),array($key));

			if ($rs && reset($rs->fields) > 0) {
				$sql = "UPDATE $table SET expiry=$expiry, sessdata=$lob_value, expireref= ".$conn->Param(0).",modified=$sysTimeStamp WHERE sesskey = ".$conn->Param('1');

			} else {
				$sql = "INSERT INTO $table (expiry, sessdata, expireref, sesskey, created, modified)
					VALUES ($expiry,$lob_value, ". $conn->Param('0').", ".$conn->Param('1').", $sysTimeStamp, $sysTimeStamp)";
			}

			$conn->Execute($sql,array($expireref,$key));

			$qkey = $conn->qstr($key);
			$conn->UpdateBlob($table, 'sessdata', $val, " sesskey=$qkey", strtoupper($clob));
			if ($debug) echo "<hr>",htmlspecialchars($oval), "<hr>";
			$rs = @$conn->CompleteTrans();
		}

		if (!$rs) {
			ADOConnection::outp('<p>Session Replace: ' . $conn->ErrorMsg() . '</p>', false);
			return false;
		}  else {
			// bug in access driver (could be odbc?) means that info is not committed
			// properly unless select statement executed in Win2000
			if ($conn->databaseType == 'access') {
				$sql = "SELECT sesskey FROM $table WHERE $binary sesskey = $qkey";
				$rs = $conn->Execute($sql);
				ADODB_Session::_dumprs($rs);
				if ($rs) {
					$rs->Close();
				}
			}
		}/*
		if (ADODB_Session::Lock()) {
			$conn->CommitTrans();
		}*/
		return $rs ? true : false;
	}

	/**
	 * Destroy session.
	 *
	 * @param string $key
	 * @return bool
	 */
	static function destroy($key) {
		$conn			= ADODB_Session::_conn();
		$table			= ADODB_Session::table();
		$expire_notify	= ADODB_Session::expireNotify();

		if (!$conn) {
			return false;
		}
		$debug			= ADODB_Session::debug();
		if ($debug) $conn->debug = 1;

		$qkey = $conn->quote($key);
		$binary = ADODB_Session::isConnectionMysql() ? '/*! BINARY */' : '';

		if ($expire_notify) {
			reset($expire_notify);
			$fn = next($expire_notify);
			$savem = $conn->SetFetchMode(ADODB_FETCH_NUM);
			$sql = "SELECT expireref, sesskey FROM $table WHERE sesskey = $binary $qkey";
			$rs = $conn->Execute($sql);
			ADODB_Session::_dumprs($rs);
			$conn->SetFetchMode($savem);
			if (!$rs) {
				return false;
			}
			if (!$rs->EOF) {
				$ref = $rs->fields[0];
				$key = $rs->fields[1];
				$fn($ref, $key);
			}
			$rs->Close();
		}

		$sql = "DELETE FROM $table WHERE sesskey = $binary $qkey";
		$rs = $conn->Execute($sql);
		if ($rs) {
			$rs->Close();
		}

		return (bool)$rs;
	}

	/**
	 * Perform garbage collection.
	 *
	 * @param int $maxlifetime
	 * @return bool
	 */
	static function gc($maxlifetime)
	{
		$conn			= ADODB_Session::_conn();
		$debug			= ADODB_Session::debug();
		$expire_notify	= ADODB_Session::expireNotify();
		$optimize		= ADODB_Session::optimize();
		$table			= ADODB_Session::table();

		if (!$conn) {
			return false;
		}

		if ($debug) {
			$conn->debug = 1;
			$COMMITNUM = 2;
		} else {
			$COMMITNUM = 20;
		}

		$time = $conn->OffsetDate(-$maxlifetime/24/3600,$conn->sysTimeStamp);
		$binary = ADODB_Session::isConnectionMysql() ? '/*! BINARY */' : '';

		if ($expire_notify) {
			reset($expire_notify);
			$fn = next($expire_notify);
		} else {
			$fn = false;
		}

		$savem = $conn->SetFetchMode(ADODB_FETCH_NUM);
		$sql = "SELECT expireref, sesskey FROM $table WHERE expiry < $time ORDER BY 2"; # add order by to prevent deadlock
		$rs = $conn->SelectLimit($sql,1000);
		if ($debug) ADODB_Session::_dumprs($rs);
		$conn->SetFetchMode($savem);
		if ($rs) {
			$tr = $conn->hasTransactions;
			if ($tr) $conn->BeginTrans();
			$ccnt = 0;
			while (!$rs->EOF) {
				$ref = $rs->fields[0];
				$key = $rs->fields[1];
				if ($fn) $fn($ref, $key);
				$conn->Execute("DELETE FROM $table WHERE sesskey = $binary " . $conn->Param('0'), array($key));
				$rs->MoveNext();
				$ccnt += 1;
				if ($tr && $ccnt % $COMMITNUM == 0) {
					if ($debug) echo "Commit<br>\n";
					$conn->CommitTrans();
					$conn->BeginTrans();
				}
			}
			$rs->Close();

			if ($tr) $conn->CommitTrans();
		}


		// suggested by Cameron, "GaM3R" <gamr@outworld.cx>
		if ($optimize) {
			if (ADODB_Session::isConnectionMysql()) {
				$sql = "OPTIMIZE TABLE $table";
			} elseif (ADODB_Session::isConnectionPostgres()) {
				$sql = "VACUUM $table";
			}
			if (!empty($sql)) {
				$conn->Execute($sql);
			}
		}


		return true;
	}
}

ADODB_Session::_init();
if (empty($ADODB_SESSION_READONLY))
	register_shutdown_function('session_write_close');

/**
 * @deprecated for backwards compatibility only
 */
function adodb_sess_open($save_path, $session_name, $persist = true) {
	return ADODB_Session::open($save_path, $session_name, $persist);
}

/**
 * @deprecated for backwards compatibility only
 */
function adodb_sess_gc($t)
{
	return ADODB_Session::gc($t);
}

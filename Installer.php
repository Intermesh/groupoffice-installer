<?php

class Installer {

	private $path;

	public $fatal = false;

	public $admin_user;
	public $admin_pass;
	public $data_folder;
	public $db_user;
	public $db_pass;
	public $db_name;
	public $db_port;
	public $db_host = 'localhost';

	private $app;

	public function __construct(){

		$this->path = dirname(__DIR__).'/groupoffice-server/';
		$this->data_folder = $this->path.'data/';
		if(isset($_POST['admin_user'])) {
			$this->admin_user = $_POST['admin_user'];
			$this->admin_pass = $_POST['admin_pass'];
			$this->data_folder = $_POST['data_folder'];
			$this->db_user = $_POST['db_user'];
			$this->db_pass = $_POST['db_pass'];
			$this->db_name = $_POST['db_name'];
			$this->db_host = $_POST['db_host'];
		}
	}

	/**
	 *
	 * @return \GO\Core\Web\App
	 */
	public function getApp() {
		if(empty($this->app)) {
			$classLoader = require($this->path."vendor/autoload.php");
			$this->app = new \GO\Core\Web\App($classLoader, require($this->path.'config.php'));
		}
		return $this->app;

	}

	public function isInstalled() {
		if(file_exists($this->path.'config.php') && !empty(file_get_contents($this->path.'config.php'))) {
			$app = $this->getApp();
			return \GO\Core\Install\Model\System::isDatabaseInstalled();
		}
		return false;
	}

	private function returnBytes($val) {
		$val = trim($val);
		$last = strtolower($val[strlen($val)-1]);
		$val = substr($val, 0, -1);
		switch($last) {
			 case 'g':
				  $val *= 1024;
			 case 'm':
				  $val *= 1024;
			 case 'k':
				  $val *= 1024;
		}

		return $val;
  }

	public function systemTests() {

		$memory_limit = $this->returnBytes(ini_get('memory_limit'));
		$ze1compat=ini_get('zend.ze1_compatibility_mode');

		return [
			[
				'name' => 'Operating System',
				'pass' => (strtolower(PHP_OS) === 'linux'),
				'feedback' => 'Your OS is "'.PHP_OS.'" The recommended OS is Linux. Other systems may work but are not officially supported',
				'fatal' => false
			],[
				'name' => 'Web server',
				'pass' => (stripos($_SERVER["SERVER_SOFTWARE"], 'apache') !== false),
				'feedback' => 'Your web server ".$_SERVER["SERVER_SOFTWARE"]." is not officially supported',
				'fatal' => false
			],[
				'name' => 'PHP SAPI mode',
				'pass' => (php_sapi_name() != 'apache'),
				'feedback' => 'PHP running in "'.php_sapi_name().'" mode. This works fine but you need some additional rewrite rules for setting up ActiveSync and DAV.',
				'fatal' => false
			],[
				'name' => 'Expose PHP',
				'pass' => ini_get('expose_php')<1,
				'feedback' => 'You should set expose php to off to prevent version information to be public',
				'fatal' => false
			],[
				'name' => 'PHP version',
				'pass' => function_exists('version_compare') && version_compare( phpversion(), "5.6", ">="),
				'feedback' => 'Your PHP version is too old PHP 5.6 or higher is required',
				'fatal' => true
			],[
				'name' => 'Output buffering',
				'pass' => ini_get('output_buffering')<1,
				'feedback' => 'output_buffering is enabled. This will increase memory usage might cause memory errors',
				'fatal' => false
			],[
				'name' => 'mbstring function overloading',
				'pass' => ini_get('mbstring.func_overload')<1,
				'feedback' => 'mbstring.func_overload is enabled in php.ini. Encrypting e-mail passwords will be disabled with this feature enabled. Disabling this feature is recommended',
				'fatal' => false
			],[
				'name' => 'Magic quotes setting',
				'pass' => !get_magic_quotes_gpc(),
				'feedback' => 'magic_quotes_gpc is enabled. You will get better performance if you disable this setting.',
				'fatal' => false
			],[
				'name' => 'PDO support',
				'pass' => extension_loaded('PDO') && extension_loaded('pdo_mysql'),
				'feedback' => 'The PHP PDO extension with MySQL support is required.',
				'fatal' => true
			],[
				'name' => 'Mcrypt support',
				'pass' => extension_loaded('mcrypt'),
				'feedback' => 'No Mcrypt extension for PHP found. Without mcrypt Group-Office has to save e-mail passwords in plain text.',
				'fatal' => true
			],[
				'name' => 'GD support',
				'pass' => extension_loaded('gd'),
				'feedback' => 'No GD extension for PHP found. Without GD Group-Office can\'t create thumbnails.',
				'fatal' => true
			],[
				'name' => 'XML support',
				'pass' => extension_loaded('xml'),
				'feedback' => 'No XML extension for PHP found. Cannot parse XML.',
				'fatal' => true
			],[
				'name' => 'MBstring support',
				'pass' => extension_loaded('mbstring'),
				'feedback' => 'No GD extension for PHP found. Without GD Group-Office can\'t create thumbnails.',
				'fatal' => true
			],[
				'name' => 'Zip support',
				'pass' => extension_loaded('zip'),
				'feedback' => 'No Zip extension for PHP found. Without GD Group-Office can\'t (un)zip files.',
				'fatal' => false
			],[
				'name' => 'PCRE support',
				'pass' => extension_loaded('pcre'),
				'feedback' => 'No PCRE extension for PHP found.Required for regular expressions',
				'fatal' => false
			],[
				'name' => 'Date support',
				'pass' => extension_loaded('date'),
				'feedback' => 'No Date extension for PHP found. Without it Group-Office can\'t function',
				'fatal' => true
			],[
				'name' => 'IConv support',
				'pass' => extension_loaded('iconv'),
				'feedback' => 'No IConv extension for PHP found.',
				'fatal' => true
			],[
				'name' => 'CType support',
				'pass' => extension_loaded('ctype'),
				'feedback' => 'No GD extension for PHP found. Required for type checking',
				'fatal' => true
			],[
				'name' => 'OpenSSL support',
				'pass' => extension_loaded('openssl'),
				'feedback' => 'No OpenSSL extension for PHP found. Can not connect to Encrypted servers',
				'fatal' => false
			],[
				'name' => 'File upload support',
				'pass' => ini_get('file_uploads')>0,
				'feedback' => 'File uploads are disabled. Please set file_uploads=On in php.ini.',
				'fatal' => false
			],[
				'name' => 'Safe mode',
				'pass' => ini_get('safe_mode')<1,
				'feedback' => 'safe_mode is enabled in php.ini. This may cause trouble with the filesystem module and Synchronization. If you can please set safe_mode=Off in php.ini',
				'fatal' => false
			],[
				'name' => 'Open base_dir',
				'pass' => ini_get('open_basedir')=='',
				'feedback' => 'open_basedir is enabled. This may cause trouble with the filesystem module and Synchronization.',
				'fatal' => false
			],[
				'name' => 'Register globals',
				'pass' => ini_get('register_globals')<1,
				'feedback' => 'register_globals is enabled in php.ini. This causes a problem in the spell checker and probably in some other parts. It\'s recommended to disable this.',
				'fatal' => false
			],[
				'name' => 'zlib compression',
				'pass' => extension_loaded('zlib'),
				'feedback' => 'No zlib output compression support. You can increase the initial load time by installing this php extension.',
				'fatal' => false
			],[
				'name' => 'Memory limit',
				'pass' => ($memory_limit<=0 || $memory_limit>=64*1024*1024),
				'feedback' => 'Your memory limit setting ('.$memory_limit.') is less than 64MB. It\'s recommended to allow at least 64 MB.',
				'fatal' => false
			],[
				'name' => 'Error logging',
				'pass' => ini_get('log_errors')>0,
				'feedback' => 'PHP error logging is disabled in php.ini. It\'s recommended that this feature is enabled in a production environment.',
				'fatal' => false
			],[
				'name' => 'MultiByte string functions',
				'pass' => function_exists('mb_detect_encoding'),
				'feedback' => 'php-mbstring is not installed. Problems with non-ascii characters in e-mails and filenames might occur.',
				'fatal' => true
			],[

//				'name' => 'Ioncube enabled',
//				'pass' => $ioncubeWorks = ioncube_tester(),
//				'feedback' => 'Ioncube is not installed. The professional modules will not be enabled.',
//				'fatal' => false
//			],[
//				'name' => 'Ioncube version check',
//				'pass' => ($ioncube_version['status'] == 'OK'),
//				'feedback' => $ioncube_version['problem'].(!empty($ioncube_version['problem']) ? ' - ' : '').$ioncube_version['solution'],
//				'fatal' => false
//			],[
//				'name' => 'Shared Memory Functions',
//				'pass' => function_exists('sem_get') && function_exists('shm_attach') && function_exists('sem_acquire') && function_exists('shm_get_var'),
//				'feedback' => 'InterProcessData::InitSharedMem(): PHP libraries for the use shared memory are not available. Z-push will work unreliably!',
//				'fatal' => false
//			],[
//				'name' => 'Process Control Extensions',
//				'pass' => function_exists('posix_getuid'),
//				'feedback' => 'Process Control Extensions PHP library not avaialble. Z-push will work unreliably!',
//				'fatal' => false
//			],[
				'name' => 'JSON functions',
				'pass' => function_exists('json_encode'),
				'feedback' => 'json_encode and json_decode functions are not available. Try apt-get install php5-json on Debian or Ubuntu.',
				'fatal' => true
			],[
				'name' => 'zend.ze1_compatibility_mode',
				'pass' => empty($ze1compat),
				'feedback' => 'zend.ze1_compatibility_mode is enabled. can\'t run with this setting enabled',
				'fatal' => true
			],[
				'name' => 'Configuration file permissions',
				'pass' => is_writeable($this->path.'config.php'),
				'feedback' => "Your 'config.php' file is not writeable by the webserver. Try changing the permissions: ".
						"<code>touch ".$this->path."config.php\nchmod 666 ".$this->path."config.php</code>",
				'fatal' => true
			],[
				'name' => 'Data folder permissions',
				'pass' => is_writeable($this->data_folder),
				'feedback' => 'The data folder "'.$this->data_folder.'" is not writable by the webserver. Change the permissions or pick a different folder.'.
					"<code>mkdir ".$this->data_folder."\nchown www-data:www-data ".$this->data_folder."</code>",
				'fatal' => true
			],[
				'name' => 'Database connection',
				'pass' => $this->dbConnect(),
				'feedback' => 'Could not connect to the database. Please check the connection parameters.',					
				'fatal' => true
			]
		];
	}
	
	private function dbConnect() {
		try {
			$dsn = "mysql:host=".$this->db_host.";dbname=".$this->db_name.";port=".$this->db_port;
			
			$pdo = new \PDO($dsn, $this->db_user, $this->db_pass);
			
			return true;
		} catch (\PDOException $e) {
			return false;
		}
	}

	private function writeConfig() {
		$data = <<<EOF
<?php
return [
	'IFW\Config' => [
		'dataFolder'=>'$this->data_folder'
	],
	'IFW\Db\Connection' => [
		'user' => '$this->db_user',
		'port' => 3306,
		'pass' => '$this->db_pass',
		'database' => '$this->db_name',
		'host' => '$this->db_host',
	]
];
EOF;
		return file_put_contents($this->path.'config.php', $data);
	}

	public function start() {
	
		try {
		if($this->writeConfig()){
			$app = $this->getApp();
			$system = new \GO\Core\Install\Model\System();
			if($system->install()) {
				return $app->getAuth()->sudo(function(){
					$user = GO\Core\Users\Model\User::findByPk(1);
					$user->username = $this->admin_user;
					$user->setPassword($this->admin_pass);
					return $user->save();
				});
			}
		}
		} catch (\Exception $e) {			
			return $e->getMessage();
		}
		
		return true;
	}
}

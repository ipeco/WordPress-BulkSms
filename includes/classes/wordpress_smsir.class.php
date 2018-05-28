<?php
abstract class WORDPRESS_SMSIR {

	/**
	 * Webservice username
	 *
	 * @var string
	 */
	public $username;
	
	/**
	 * Webservice password
	 *
	 * @var string
	 */
	public $password;
	
	/**
	 * Webservice API/Key
	 *
	 * @var string
	 */
	public $has_key = false;
	
	/**
	 * SMsS send from number
	 *
	 * @var string
	 */
	public $from;
	
	/**
	 * Send SMS to number
	 *
	 * @var string
	 */
	public $to;
	
	/**
	 * SMS text
	 *
	 * @var string
	 */
	public $msg;
	
	/**
	 * Wordpress Database
	 *
	 * @var string
	 */
	protected $db;
	
	/**
	 * Wordpress Table prefix
	 *
	 * @var string
	 */
	protected $tb_prefix;
	
	/**
	 * Constructors
	 */
	public function __construct() {
		
		global $wpdb, $table_prefix;
		
		$this->db = $wpdb;
		$this->tb_prefix = $table_prefix;
		
	}
	
	public function Hook($tag, $arg) {
		do_action($tag, $arg);
	}
	
	public function InsertToDB($sender, $message, $recipient) {
		
		return $this->db->insert(
			$this->tb_prefix . "smsir_send",
			array(
				'date'		=>	date('Y-m-d H:i:s' ,current_time('timestamp', 0)),
				'sender'	=>	$sender,
				'message'	=>	$message,
				'recipient'	=>	implode(',', $recipient)
			)
		);

	}
	
	public function InsertToDBclub($sender, $message) {
		
		return $this->db->insert(
			$this->tb_prefix . "smsir_send",
			array(
				'date'		=>	date('Y-m-d H:i:s' ,current_time('timestamp', 0)),
				'sender'	=>	$sender,
				'message'	=>	$message,
				'recipient'	=>	"Club Users"
			)
		);

	}
}
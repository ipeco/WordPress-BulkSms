<?php
	class smsir extends WORDPRESS_SMSIR {
		
		public $tariff = "http://sms.ir/";
		public $panel = "sms.ir";
		public $unitrial = false;
		public $unit;
		public $flash = "disable";
		public $isflash = false;
		
		/**
		* gets API Customer Club Send To Categories Url.
		*
		* @return string Indicates the Url
		*/
		protected function getAPICustomerClubSendToCategoriesUrl() {
			return "http://RestfulSms.com/api/CustomerClub/SendToCategories";
		}

		/**
		* gets API Message Send Url.
		*
		* @return string Indicates the Url
		*/
		protected function getAPIMessageSendUrl() {
			return "http://RestfulSms.com/api/MessageSend";
		}

		/**
		* gets API Customer Club Add Contact And Send Url.
		*
		* @return string Indicates the Url
		*/
		protected function getAPICustomerClubAddAndSendUrl() {
			return "http://RestfulSms.com/api/CustomerClub/AddContactAndSend";
		}

		/**
		* gets API Customer Club Contact Url.
		*
		* @return string Indicates the Url
		*/
		protected function getAPICustomerClubContactUrl() {
			return "http://RestfulSms.com/api/CustomerClubContact";
		}

		/**
		* gets API credit Url.
		*
		* @return string Indicates the Url
		*/
		protected function getAPIcreditUrl() {
			return "http://RestfulSms.com/api/credit";
		}

		/**
		* gets API Verification Code Url.
		*
		* @return string Indicates the Url
		*/
		protected function getAPIVerificationCodeUrl() {
			return "http://RestfulSms.com/api/VerificationCode";
		}

		/**
		* gets Api Token Url.
		*
		* @return string Indicates the Url
		*/
		protected function getApiTokenUrl(){
			return "http://RestfulSms.com/api/Token";
		}

		/**
		*
		* @return void
		*/
		public function __construct() {
			parent::__construct();
			// ini_set("soap.wsdl_cache_enabled", "0");
		}

		/**
		* Send SMS.
		*
		* @return boolean
		*/
		public function SendSMS() {
			if($this->to){
				foreach($this->to as $key=>$value){
					if(($this->is_mobile($value)) || ($this->is_mobile_withz($value))){
						$number[] = doubleval($value);
					}
				}
				@$numbers = array_unique($number);
				
				if(is_array($numbers) && $numbers){
					foreach($numbers as $key => $value){
						$Messages[] = $this->msg;
					}
				}
				
				$SendDateTime = date("Y-m-d")."T".date("H:i:s");

				date_default_timezone_set('Asia/Tehran');
				
				if(get_option('wordpress_smsir_stcc_number')){

					foreach($numbers as $num_keys => $num_vals){
						$contacts[] = array(
							"Prefix" => "",
							"FirstName" => "" ,
							"LastName" => "",
							"Mobile" => $num_vals,
							"BirthDay" => "",
							"CategoryId" => "",
							"MessageText" => $this->msg
						);
					}

					$CustomerClubInsertAndSendMessage = $this->CustomerClubInsertAndSendMessage($contacts);

					if($CustomerClubInsertAndSendMessage == true){
						$this->InsertToDB($this->from, $this->msg, $this->to);
						$this->Hook('wordpress_smsir_send', $result);
						return true;
					} else {
						return false;
					}
				} else {
					
					$SendMessage = $this->SendMessage($numbers,$Messages,$SendDateTime);
					
					if($SendMessage == true){
						$this->InsertToDB($this->from, $this->msg, $this->to);
						$this->Hook('wordpress_smsir_send', $result);
						return true;
					} else {
						return false;
					}
				}
			}
		}

		/**
		* Customer Club Send To Categories.
		*
		* @param Messages[] $Messages array structure of messages
		* @param contactsCustomerClubCategoryIds[] $contactsCustomerClubCategoryIds array structure of contacts Customer Club Category Ids
		* @param string $SendDateTime Send Date Time
		* @return string Indicates the sent sms result
		*/
		public function SendSMStoCustomerclubContacts($Messages) {
			
			$contactsCustomerClubCategoryIds = array();
			$token = $this->GetToken($this->username, $this->password);
			if($token != false){
				$postData = array(
					'Messages' => $Messages,
					'contactsCustomerClubCategoryIds' => $contactsCustomerClubCategoryIds,
					'SendDateTime' => '',
					'CanContinueInCaseOfError' => 'false'
				);
				
				$url = $this->getAPICustomerClubSendToCategoriesUrl();
				$CustomerClubSendToCategories = $this->execute($postData, $url, $token);
				$object = json_decode($CustomerClubSendToCategories);

				if(is_object($object)){
					$array = get_object_vars($object);
					if(is_array($array)){
						if($array['IsSuccessful'] == true){
							$this->InsertToDBclub($this->from, $message);
							$this->Hook('wordpress_smsir_send', $result);
							return true;
						} else {
							return false;
						}
					} else {
						return false;
					}
				} else {
					return false;
				}
				
			} else {
				return false;
			}
		}
			
		/**
		* Verification Code.
		*
		* @param string $Code Code
		* @param string $MobileNumber Mobile Number
		* @return string Indicates the sent sms result
		*/
		public function SendSMSforVerification($Code, $MobileNumber) {
			
			$token = $this->GetToken($this->username, $this->password);
			if($token != false){
				$postData = array(
					'Code' => $Code,
					'MobileNumber' => $MobileNumber,
				);
				
				$url = $this->getAPIVerificationCodeUrl();
				$VerificationCode = $this->execute($postData, $url, $token);
				$object = json_decode($VerificationCode);

				if(is_object($object)){
					$array = get_object_vars($object);
					if(is_array($array)){
						if($array['IsSuccessful'] == true){
							$result = true;
						} else {
							$result = false;
						}
						$result = $array['IsSuccessful'];
					} else {
						$result = false;
					}
				} else {
					$result = false;
				}
				
			} else {
				$result = false;
			}
			return $result;
		}

		/**
		* Get Credit.
		*
		* @return string Indicates the sent sms result
		*/
		public function GetCredit() {
			
			$token = $this->GetToken($this->username, $this->password);
			if($token != false){

				$url = $this->getAPIcreditUrl();
				$GetCredit = $this->executeCredit($url, $token);
				
				$object = json_decode($GetCredit);

				if(is_object($object)){
					$array = get_object_vars($object);

					if(is_array($array)){
						if($array['IsSuccessful'] == true){
							$result = $array['Credit'];
						} else {
							$result = $array['Message'];
						}
					} else {
						$result = false;
					}
				} else {
					$result = false;
				}
				
			} else {
				$result = false;
			}
			return $result;
		}
			
		/**
		* send sms.
		*
		* @param MobileNumbers[] $MobileNumbers array structure of mobile numbers
		* @param Messages[] $Messages array structure of messages
		* @param string $SendDateTime Send Date Time
		* @return string Indicates the sent sms result
		*/
		public function SendMessage($MobileNumbers, $Messages, $SendDateTime = '') {
			
			$token = $this->GetToken($this->username, $this->password);

			if($token != false){
				$postData = array(
					'Messages' => $Messages,
					'MobileNumbers' => $MobileNumbers,
					'LineNumber' => $this->from,
					'SendDateTime' => $SendDateTime,
					'CanContinueInCaseOfError' => 'false'
				);
				
				$url = $this->getAPIMessageSendUrl();
				$SendMessage = $this->execute($postData, $url, $token);
				$object = json_decode($SendMessage);

				if(is_object($object)){
					$array = get_object_vars($object);
					if(is_array($array)){
						if($array['IsSuccessful'] == true){
							$result = true;
						} else {
							$result = false;
						}
					} else {
						$result = false;
					}
				} else {
					$result = false;
				}
				
			} else {
				$result = false;
			}
			return $result;
		}
			
		/**
		* Customer Club Insert And Send Message.
		*
		* @param data[] $data array structure of contacts data
		* @return string Indicates the sent sms result
		*/
		public function CustomerClubInsertAndSendMessage($data) {
			
			$token = $this->GetToken($this->username, $this->password);
			if($token != false){
				$postData = $data;
				
				$url = $this->getAPICustomerClubAddAndSendUrl();
				$CustomerClubInsertAndSendMessage = $this->execute($postData, $url, $token);
				$object = json_decode($CustomerClubInsertAndSendMessage);

				if(is_object($object)){
					$array = get_object_vars($object);
					if(is_array($array)){
						if($array['IsSuccessful'] == true){
							$result = true;
						} else {
							$result = false;
						}
					} else {
						$result = false;
					}
				} else {
					$result = false;
				}
				
			} else {
				$result = false;
			}
			return $result;
		}

		/**
		* Add Contact To Customer Club.
		*
		* @param string $Prefix Prefix
		* @param string $FirstName First Name
		* @param string $LastName Last Name
		* @param string $Mobile Mobile
		* @param string $BirthDay Birth Day
		* @param string $CategoryId Category Id
		* @return string Indicates the sent sms result
		*/
		public function inserttosmscustomerclub($user_name,$mobile) {
			
			$token = $this->GetToken($this->username, $this->password);
			if($token != false){
				$postData = array(
					'Prefix' => '',
					'FirstName' => '',
					'LastName' => $user_name,
					'Mobile' => $mobile,
					'BirthDay' => '',
					'CategoryId' => ''
				);
				
				$url = $this->getAPICustomerClubContactUrl();
				$AddContactToCustomerClub = $this->execute($postData, $url, $token);
				$object = json_decode($AddContactToCustomerClub);
				
				if(is_object($object)){
					$array = get_object_vars($object);
					if(is_array($array)){
						$result = $array['Message'];
					} else {
						$result = false;
					}
				} else {
					$result = false;
				}
				
			} else {
				$result = false;
			}
			return $result;
		}
	
		/**
		* gets token key for all web service requests.
		*
		* @return string Indicates the token key
		*/
		private function GetToken(){
			$postData = array(
				'UserApiKey' => $this->username,
				'SecretKey' => $this->password,
				'System' => 'wordpress_4_v_3_0'
			);
			$postString = json_encode($postData);

			$ch = curl_init($this->getApiTokenUrl());
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
												'Content-Type: application/json'
												));		
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_POST, count($postString));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
			
			$result = curl_exec($ch);
			curl_close($ch);
			
			$response = json_decode($result);
			
			if(is_object($response)){
				$resultVars = get_object_vars($response);
				if(is_array($resultVars)){
					@$IsSuccessful = $resultVars['IsSuccessful'];
					if($IsSuccessful == true){
						@$TokenKey = $resultVars['TokenKey'];
						$resp = $TokenKey;
					} else {
						$resp = false;
					}
				}
			}
			
			return $resp;
		}

		/**
		* executes the main method.
		*
		* @param postData[] $postData array of json data
		* @param string $url url
		* @param string $token token string
		* @return string Indicates the curl execute result
		*/
		private function execute($postData, $url, $token){
			
			$postString = json_encode($postData);

			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
												'Content-Type: application/json',
												'x-sms-ir-secure-token: '.$token
												));		
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ch, CURLOPT_POST, count($postString));
			curl_setopt($ch, CURLOPT_POSTFIELDS, $postString);
			
			$result = curl_exec($ch);
			curl_close($ch);
			
			return $result;
		}
		/**
		* executes the main method.
		*
		* @param string $url url
		* @param string $token token string
		* @return string Indicates the curl execute result
		*/
		private function executeCredit($url, $token){
			
			$ch = curl_init($url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
												'Content-Type: application/json',
												'x-sms-ir-secure-token: '.$token
												));		
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
			
			$result = curl_exec($ch);
			curl_close($ch);
			
			return $result;
		}
		
		/**
		* check if mobile number is valid.
		*
		* @param string $mobile mobile number
		* @return boolean Indicates the mobile validation
		*/
		public function is_mobile($mobile){
			if(preg_match('/^09(0[1-3]|1[0-9]|3[0-9]|2[0-2]|9[0])-?[0-9]{3}-?[0-9]{4}$/', $mobile)){
				return true;
			} else {
				return false;
			}
		}
		
		/**
		* check if mobile with zero number is valid.
		*
		* @param string $mobile mobile with zero number
		* @return boolean Indicates the mobile with zero validation
		*/
		public function is_mobile_withz($mobile){
			if(preg_match('/^9(0[1-3]|1[0-9]|3[0-9]|2[0-2]|9[0])-?[0-9]{3}-?[0-9]{4}$/', $mobile)){
				return true;
			} else {
				return false;
			}
		}
	
	}
?>
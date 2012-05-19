<?php
	/**
	 * User class
	 * 
	 * @extends Controller
	 */
	class User extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->load->library("session");
		}
		
		public function check_login() {
			
			$this->load->view("json", array("data"=>$this->_check_login()));
		}
		
		public function login() {
			$email=$this->input->post("email");
			$password=$this->input->post("password");
			$result=$this->_login($email, $password);
			$this->load->view("json", array("data"=>$result));
		}
		
		protected function _login($email, $password) {
			$this->load->model("model_user");
			$result=array(
				"success"=>false,
				"message"=>"",
			);
			if (empty($email)) {
				$result["message"]="Email required";
				return $result;
			}
			if (empty($password)) {
				$result["message"]="Password required";
				return $result;
			}
			
			$login=$this->model_user->login($email, $password);
			if (empty($login)) {
				$result["message"]="Email or password incorrect";
				return $result;
			}
			$result['user']=$this->_check_login();
			$result["success"]=true;
			return $result;
		}
		
		protected function _check_login() {
			$result=array("logged_in"=>false, "subscribed"=>false);
			$this->load->library("session");
			$user_id=$this->session->userdata("user_id");
			if (!empty($user_id)) {
				$result["logged_in"]=true;
			}
			return $result;
		}
	}

/* End of file user.php */
/* Location: ./system/application/controllers/ */
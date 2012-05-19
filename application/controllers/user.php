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
			$this->load->library("convene_security");
			$publication_id=$this->convene_security->publication_id();
			$this->load->view("json", array("data"=>$this->_check_login($publication_id)));
		}
		
		public function login() {
			$email=$this->input->get_post("email");
			$password=$this->input->get_post("password");
			$result=$this->_login($email, $password);
			$this->load->view("json", array("data"=>$result));
		}
		
		public function logout() {
			$this->session->sess_destroy();
			$result["success"]=true;
			$result["message"]="Successfully logged out";
			$this->load->view("json", array("data"=>$result));
		}
		
		public function retrieve_password() {
			$email=$this->input->get_post("email");
			$this->_retrieve_password($email);
			$result=array(
				"success"=>true,
				"message"=>"",
			);
			$this->load->view("json", array("data"=>$result));
		}
		
		public function emailtemplate() {
			$this->load->view("templates/email");
		}
		
		public function draw() {
			$this->load->view("user_signup");
		}
		
		public function register() {
			$this->load->model("model_user");
			$result=array(
				"success"=>false,
				"message"=>"",
			);
			$email=$this->input->get_post("email");
			$password=$this->input->get_post("password");
			$fname=$this->input->get_post("fname");
			$sname=$this->input->get_post("sname");
			$cel=$this->input->get_post("cel");
			$tel=$this->input->get_post("tel");
			$designation=$this->input->get_post("designation");
			$company=$this->input->get_post("company");
			$city=$this->input->get_post("city");
			$country=$this->input->get_post("country");
			
			//Sanity
			$this->load->helper("email");
			if (empty($email) || empty($password) || empty($fname) || empty($sname)) {
				$result["message"]="Required field missing";
				$this->load->view("json", array("data"=>$result));
				return false;
			}
			if (!valid_email($email)) {
				$result["message"]="Invalid email address";
				$this->load->view("json", array("data"=>$result));
				return false;
			}
			if (strlen($password)<4) {
				$result["message"]="Password must be at least four characters long";
				$this->load->view("json", array("data"=>$result));
				return false;
			}
			$checkuser=$this->model_user->get_by_email($email);
			if (!empty($checkuser->email)) {
				$result["message"]="The email address you supplied has already been registered";
				$this->load->view("json", array("data"=>$result));
				return false;
			}
			$randomcode=$this->model_user->get_randomcode($email);
			$this->model_user->create_user(
				array(
					"email"=>$email,
					"password"=>$password,
					"fname"=>$fname,
					"sname"=>$sname,
					"cel"=>$cel,
					"tel"=>$tel,
					"designation"=>$designation,
					"company"=>$company,
					"city"=>$city,
					"country"=>$country,
					"randomcode"=>$randomcode,
					"active"=>0,
					"moderated"=>0
				)
			);
			$this->load->library('email');
			$config["mailtype"]="html";
			$this->email->initialize($config);
			$this->email->from("registrations@dailymaverick.co.za", "Daily Maverick");
			$this->email->to($email);
			$this->email->subject("Daily Maverick registration confirmation");
			$text="Hi there\n\nTo confirm your subscription to Daily Maverick, please click on the following link or copy and paste it into a browser:\n".base_url()."user/confirm/".$randomcode."\n\nThanks for subscribing!\n\n";
			$this->email->set_alt_message($text);
			$template=file_get_contents(base_url()."user/emailtemplate");
			$html=str_replace("[CONTENT]", nl2br($text), $template);
			$this->email->message($html);
			$this->email->send();
			$result["success"]=true;
			$this->load->view("json", array("data"=>$result));
			return true;
		}
		
		public function confirm($randomcode) {
			$this->load->model("model_user");
			$user=$this->model_user->get_by_randomcode($randomcode);
			if (!empty($user->id)) {
				$this->model_user->confirm($user->id);
				redirect("http://dailymaverick.co.za/user/register/confirmed");
			} else {
				redirect("http://dailymaverick.co.za/user/register/unconfirmed");
			}
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
			$result=array("logged_in"=>false);
			$user_id=$this->session->userdata("user_id");
			if (!empty($user_id)) {
				$result["logged_in"]=true;
			}
			return $result;
		}
		
		protected function _retrieve_password($email) {
			$this->load->model("model_user");
			$user=$this->model_user->get_by_email($email);
			if (empty($user->id)) {
				return false;
			}
			if (!empty($user->password)) {
				$this->load->library('email');
				$config["mailtype"]="html";
				$this->email->initialize($config);
				$this->email->from("register@thedailymaverick.co.za", "The Daily Maverick");
				$template=file_get_contents(base_url()."user/emailtemplate");
				$this->email->to($email);
				$this->email->subject("Daily Maverick password");
				$text="Hi there\n\nYour Daily Maverick password is: {$user->password}\n\n";
				$this->email->set_alt_message($text);
				$html=str_replace("[CONTENT]", nl2br($text), $template);
				$this->email->message($html);
				$this->email->send();
			}
			return true;
		}
	}

/* End of file user.php */
/* Location: ./system/application/controllers/ */
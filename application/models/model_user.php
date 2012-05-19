<?php
	class Model_User extends CI_Model {
		public $table="users";
				
		public function login($email,$password) {
			$this->db->select("users.*")->from("users")->where("email",$email)->where("password",$password)->where("active",1);
			$query=$this->db->get();
			if ($query->num_rows()==0) {
				return false;
			}
			$data=array(
				"user_id"=>$query->row()->id,
				"user_email"=>$query->row()->email,
				"user_fname"=>$query->row()->fname,
				"user_sname"=>$query->row()->sname
			);
			$this->session->set_userdata($data);
			$id=$query->row()->id;
			$this->db->where("id",$id);
			$this->db->update("users",array("date_login"=>date("c")));
			return true;
		}
		
		public function checklogin() {
			$user_email=$this->session->userdata("user_email");
			if (empty($user_email)) {
				return false;
			} else {
				return true;
			}
		}
		
		public function get_by_email($email) {
			$query=$this->db->get_where("users",array("email"=>$email));
			return $query->row();
		}

		public function get_by_randomcode($randomcode) {
			$query=$this->db->get_where("users",array("randomcode"=>$randomcode));
			return $query->row();
		}
		
		public function get_randomcode($seed) {
			$found=true;
			while ($found) {
				$code=substr(md5(microtime().$seed),0,6);
				$found=$this->db->get_where("users",array("randomcode"=>$code))->num_rows();	
			}
			return $code;
		}
		
		public function confirm($user_id) {
			$this->db->where("id", $user_id)->update("users", array("active"=>1));
		}
		
		public function create_user($dbdata) {
			$this->db->insert("users", $dbdata);
		}
		
	}
?>
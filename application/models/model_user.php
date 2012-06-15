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
		
		public function count_all($publication_id) {
			return $this->db->where("publication_id", $publication_id)->get("users")->num_rows();
		}
		
		public function getall_admin($offset, $limit, $order_by, $order_dir, $publication_id, $searchstring=false) {
			if (empty($order_by)) {
				$order_by="date_created";
			}
			if ($searchstring!=false) {
				$this->db->like("email", $searchstring);
				$this->db->or_like("fname", $searchstring);
				$this->db->or_like("sname", $searchstring);
			}
			return $this->db->where("publication_id", $publication_id)->order_by($order_by, $order_dir)->limit($limit, $offset)->get("users")->result();
		}
		
		public function countall_admin($publication_id, $searchstring=false) {
			if ($searchstring!=false) {
				$this->db->like("email", $searchstring);
				$this->db->or_like("fname", $searchstring);
				$this->db->or_like("sname", $searchstring);
			}
			return $this->db->where("publication_id", $publication_id)->get("users")->num_rows();
		}

		public function get_by_id($id) {
			return $this->db->get_where("users", array("id"=>$id))->row();
		}
		
		public function change_active($id, $status) {
			$this->db->where("id",$id)->update("users", array("active"=>$status));
			return true;
		}
		
		public function change_moderated($id, $status) {
			$this->db->where("id",$id)->update("users", array("moderated"=>$status));
			return true;
		}
		
		public function update($id, $data) {
			$this->db->where('id', $id)->update('users', $data);
			return true;
		}
		
	}
?>
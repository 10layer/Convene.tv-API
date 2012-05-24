<?php
	/**
	 * Admin class
	 * 
	 * @extends Controller
	 */
	class Admin extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
			$this->load->library("convene_security");
			$this->convene_security->private_only();
			$this->load->model("model_comment");
		}
		
		public function draw() {
			$publication_id=$this->convene_security->publication_id();
			$data["private_key"]=$this->convene_security->private_key();
			$data["count"]=$this->model_comment->count_all($publication_id);
			$data["perpage"]=25;
			$this->load->view("admin_console", $data);	
		}
		
		public function users() {
			$this->load->model("model_user");
			$publication_id=$this->convene_security->publication_id();
			$data["private_key"]=$this->convene_security->private_key();
			$data["count"]=$this->model_user->count_all($publication_id);
			$data["perpage"]=100;
			$this->load->view("admin_users", $data);	
		}
		
		public function get_users($offset=0, $limit=100, $order_by=false, $order_dir=false) {
			$this->load->model("model_user");
			if ($order_by=="undefined") {
				$order_by=false;
			}
			if (($order_dir!="ASC") && ($order_dir!="DESC")) {
				$order_dir="ASC";
			}
			$publication_id=$this->convene_security->publication_id();
			$searchstring=$this->input->get_post("searchstring");
			$data["users"]=$this->model_user->getall_admin($offset, $limit, $order_by, $order_dir, $publication_id, $searchstring);
			$data["count"]=$this->model_user->countall_admin($publication_id, $searchstring);
			$data["order_by"]=$order_by;
			$data["order_dir"]=$order_dir;
			$this->load->view("json", array("data"=>$data));
		}
		
		public function user_toggle_active() {
			$this->load->model("model_user");
			$user_id=$this->input->get_post("user_id");
			$user=$this->model_user->get_by_id($user_id);
			$this->model_user->change_active($user_id, !$user->active);
			$this->load->view("json", array("data"=>array("active"=>!$user->active)));
		}
		
		public function user_toggle_moderated() {
			$this->load->model("model_user");
			$user_id=$this->input->get_post("user_id");
			$user=$this->model_user->get_by_id($user_id);
			$this->model_user->change_moderated($user_id, !$user->moderated);
			$this->load->view("json", array("data"=>array("active"=>!$user->moderated)));
		}
		
		public function get_comments($offset=0, $limit=25) {
			$publication_id=$this->convene_security->publication_id();
			//$data["count"]=$this->model_comment->count_all($publication_id);
			$data["comments"]=$this->model_comment->getall_admin($offset, $limit, $publication_id);
			$this->load->view("json", array("data"=>$data));
		}
		
		public function toggle_live() {
			$comment_id=$this->input->get_post("comment_id");
			$publication_id=$this->convene_security->publication_id();
			$result=array(
				"success"=>false,
				"message"=>"",
				"live"=>false,
			);
			$comment=$this->model_comment->get_comment($comment_id);
			if ($comment->live) {
				$this->model_comment->unlive($comment_id);
				$result["success"]=true;
				$result["live"]=false;
			} else {
				$this->model_comment->live($comment_id);
				$result["success"]=true;
				$result["live"]=true;
			}
			$this->load->view("json", array("data"=>$result));
		}
		
		public function unlive_comment($comment_id) {
			$publication_id=$this->convene_security->publication_id();
			$result=array(
				"success"=>false,
				"message"=>"",
			);
			$this->model_comment->unlive($comment_id);
			$this->load->view("json", array("data"=>$result));
		}
		
		public function edit_comment() {
			$publication_id=$this->convene_security->publication_id();
			$result=array(
				"success"=>false,
				"message"=>"",
			);
			$comment_id=$this->input->get_post("comment_id");
			$comment=$this->input->get_post("comment");
			if (empty($comment)) {
				$result["message"]="Can't have empty comment";
				$this->load->view("json", array("data"=>$result));
				return false;
			}
			if (empty($comment_id)) {
				$result["message"]="Can't find comment_id";
				$this->load->view("json", array("data"=>$result));
				return false;
			}
			$this->model_comment->update($comment_id, array("comment"=>$comment));
			$result["success"]=true;
			$this->load->view("json", array("data"=>$result));
		}
		
		public function edit_user($user_id) {
			$publication_id=$this->convene_security->publication_id();
			$result=array(
				"success"=>false,
				"message"=>"",
			);
			
			$this->load->view("json", array("data"=>$result));
		}
		
		public function search($offset=0, $limit=25) {
			$searchstring=$this->input->get_post("searchstring");
			$publication_id=$this->convene_security->publication_id();
			$data["comments"]=$this->model_comment->search_admin($searchstring, $offset, $limit, $publication_id);
			$data["count"]=$this->model_comment->search_admin_count($searchstring, $publication_id);
			$this->load->view("json", array("data"=>$data));
		}
		
		public function user_search($offset=0, $limit=25) {
			$searchstring=$this->input->get_post("searchstring");
			$publication_id=$this->convene_security->publication_id();
			$data["comments"]=$this->model_comment->usersearch_admin($searchstring, $offset, $limit, $publication_id);
			$data["count"]=$this->model_comment->usersearch_admin_count($searchstring, $publication_id);
			$this->load->view("json", array("data"=>$data));
		}
		
		public function urlid_search($offset=0, $limit=25) {
			$searchstring=$this->input->get_post("searchstring");
			$searchstring=str_replace(" ","-",strtolower($searchstring));
			$publication_id=$this->convene_security->publication_id();
			$data["comments"]=$this->model_comment->urlidsearch_admin($searchstring, $offset, $limit, $publication_id);
			$data["count"]=$this->model_comment->urlidsearch_admin_count($searchstring, $publication_id);
			$this->load->view("json", array("data"=>$data));
		}
	}

/* End of file .php */
/* Location: ./system/application/controllers/ */
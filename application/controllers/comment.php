<?php
	/**
	 * Comment class
	 * 
	 * @extends Controller
	 */
	class Comment extends CI_Controller {

		/**
		 * __construct function.
		 * 
		 * @access public
		 * @return void
		 */
		public function __construct() {
			parent::__construct();
		}
		
		public function draw() {
			$this->load->library("convene_security");
			$this->convene_security->private_only();
			$publication_id=$this->convene_security->publication_id();
			$this->load->library("comments");
			$this->load->library("session");
			$urlid=$this->uri->segment(3);
			if (empty($urlid)) {
				return false;
			}
			$data["urlid"]=$urlid;
			$data["public_key"]=$this->convene_security->public_key();
			$this->load->view("comments",$data);
		}
		
		public function get() {
			$this->load->library("convene_security");
			$publication_id=$this->convene_security->publication_id();
			$this->load->model("model_comment");
			$urlid=$this->uri->segment(3);
			if (empty($urlid)) {
				return false;
			}
			$comments=$this->model_comment->get_by_article($urlid, $publication_id);
			for($x=0; $x<sizeof($comments); $x++) { //Prep and add data
				$commentdate=strtotime($comments[$x]->date_created);
				$comments[$x]->commentdate=date("D, j M Y",$commentdate);
				$comments[$x]->commenttime=date("H:i",$commentdate);
				$comments[$x]->comment=nl2br($comments[$x]->comment);
			}
			$comments=$this->_sort_comments($comments);
			$data["comments"]=$comments;
			$data["comment_count"]=$this->model_comment->count_by_article($urlid, $publication_id);
			$data["commentalert"]=$this->model_comment->check_subscribe($urlid, $publication_id);
			$this->load->view("json", array("data"=>$data));
		}
		
		protected function _sort_comments($comments) {
			$result=array();
			$tmp=array();
			while(!empty($comments)) {
				$comment=array_shift($comments);
				if ($comment->parent_id == 0) {
					$result[] = $comment;
				} else {
					$tmp[] = $comment;
				}
			}
			$comments = $tmp;
			$comments=array_reverse($comments);
			while(!empty($comments)) {
				for($x=0; $x<sizeof($result); $x++) {
					for($y=0; $y<sizeof($comments); $y++) {
						if ($comments[$y]->parent_id==$result[$x]->id) {
							$comments[$y]->level=$result[$x]->level+1;
							if ($comments[$y]->level >=3) {
								$comments[$y]->level=2;
							}
							array_splice($result, $x+1, 0, array_splice($comments, $y, 1));
						}
					}
				}
			}
			return $result;
		}
		
		public function ajax_submit() {	
			$this->load->library("convene_security");
			$public_key=$this->convene_security->public_key();
			if (empty($public_key)) {
				$this->load->view("json", array("data"=>array("success"=>false, "message"=>"Public key error")));
				return true;
			}
			$comment=$this->input->get_post("comment");
			$article_id=$this->input->get_post("article_id");
			$parent=$this->input->get_post("parent_id");
			$result=$this->submit($comment, $article_id, $parent);
			$this->load->view("json", array("data"=>$result));
		}
		
		public function ajax_subscribe($urlid) {
			$this->load->library("convene_security");
			$publication_id=$this->convene_security->publication_id();
			if (empty($publication_id)) {
				$this->load->view("json", array("data"=>array("success"=>false, "message"=>"Public key error")));
				return true;
			}
			$this->load->model("model_comment");
			$this->load->library("session");
			$result=array(
				"success"=>false,
				"message"=>"",
			);
			$alert=$this->model_comment->check_subscribe($urlid, $publication_id);
			if ($alert) {
				$this->model_comment->unsubscribe($urlid, $publication_id);
				$result["success"]=true;
				$result["message"]="unsubscribed";
			} else {
				$this->model_comment->subscribe($urlid, $publication_id);
				$result["success"]=true;
				$result["message"]="subscribed";
			}
			$this->load->view("json", array("data"=>$result));
		}
		
		public function ajax_check_subscribe($urlid) {
			$this->load->library("convene_security");
			$publication_id=$this->convene_security->publication_id();
			if (empty($publication_id)) {
				$this->load->view("json", array("data"=>array("success"=>false, "message"=>"Public key error")));
				return true;
			}
			$this->load->model("model_comment");
			$result=array(
				"success"=>false,
				"message"=>"",
			);
			$alert=$this->model_comment->check_subscribe($urlid, $publication_id);
			if ($alert) {
				$result["success"]=true;
				$result["message"]="subscribed";
			} else {
				$result["success"]=true;
				$result["message"]="unsubscribed";
			}
			$this->load->view("json", array("data"=>$result));
		}
		
		protected function submit($comment, $article_id, $parent) {
			$result=array(
				"success"=>false,
				"message"=>"",
			);
			$this->load->library("session");
			$this->load->library("comments");
			$userid=$this->session->userdata("user_id");
			if (empty($userid)) {
				$result["message"]="Not logged in";
				return $result;
			}
			if (empty($comment) || empty($article_id)) {
				$result["message"]="Missing information";
				return $result;
			}
			$publication_id=$this->convene_security->publication_id();
			if ($this->model_comment->submit($comment, $article_id, $parent, $userid, $publication_id)) {
				$result["success"]=true;
			} else {
				$result["message"]="Comment already exists";
			}
			
			return $result;
		}
		
		public function email_contact() {
			$data=array("msg"=>"", "error"=>false);
			$this->load->helper("email");
			$email=$this->input->post("email");
			$body=$this->input->post("body");
			if (!valid_email($email)) {
				$data["msg"]="Not a valid email address";
				$data["error"]=true;
			}
			if (empty($email)) {
				$data["msg"]="The email was blank";
				$data["error"]=true;
			}
			if (empty($body)) {
				$data["msg"]="The message was blank";
				$data["error"]=true;
			}
			if (!$data["error"]) {
				send_email("jason@freespeechpub.co.za","Daily Maverick website message","Message received at ".date("c")." from $email\n\n$body");
			}
			$this->load->view("email_contact",$data);
		}
		
		public function logout($redirect=false) {
			$this->load->library("session");
			$this->session->sess_destroy();
			if (empty($redirect)) {
				redirect(base_url());
			}
			$tmp=$this->uri->rsegment_array();
			$redirect_array=array_slice($tmp, 2);
			$redirect=implode("/", $redirect_array);
			redirect($redirect);
		}
	}

/* End of file comment.php */
/* Location: ./system/application/controllers/ */
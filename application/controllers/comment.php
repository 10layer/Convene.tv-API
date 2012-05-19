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
			$this->load->library("comments");
			$this->load->library("session");
			$this->comments->draw_comments();
		}
		
		public function iframe() {
			$this->load->library("session");
			$this->load->view("iframe");
		}
		
		public function get() {
			$this->load->model("model_article");
			$this->load->model("model_comment");
			$urlid=$this->uri->segment(3);
			$article=$this->model_article->get($urlid);
			if (empty($article->id)) {
				return false;
			}
			$comments=$this->model_comment->get_by_article($article->id);
			for($x=0; $x<sizeof($comments); $x++) { //Prep and add data
				$commentdate=strtotime($comments[$x]->date_created);
				$comments[$x]->commentdate=date("D, j M Y",$commentdate);
				$comments[$x]->commenttime=date("H:i",$commentdate);
				$comments[$x]->comment=nl2br($comments[$x]->comment);
			}
			for($x=sizeof($comments)-1; $x>=0; $x--) {	//Put in our levels
				if ($comments[$x]->parent_id != 0) {
					for($y=sizeof($comments)-1; $y>=0; $y--) {
						if ($comments[$y]->id==$comments[$x]->parent_id) {
							$comments[$x]->level=$comments[$y]->level + 1;
							$tmp=array_splice($comments, $x, 1);
							//print_r($tmp);
							//print $x." ".$y;
							$comments=array_merge(
								array_slice($comments, 0, $y+1),
								$tmp,
								array_slice($comments, $y+1)
							);
							//print_r($comments);
							break;
						}
					}
				}
			}
			$data["comments"]=$comments;
			$data["comment_count"]=$this->model_comment->count_by_article($article->id);
			$this->load->view("json", array("data"=>$data));
		}
		
		public function ajax_submit() {
			$comment=$this->input->post("comment");
			$article_id=$this->input->post("article_id");
			$parent=$this->input->post("parent_id");
			print json_encode($this->submit($comment, $article_id, $parent));
			return true;
		}
		
		public function ajax_subscribe($article_id) {
			$this->load->model("model_comment");
			$this->load->library("session");
			$result=array(
				"success"=>false,
				"message"=>"",
			);
			$alert=$this->model_comment->check_subscribe($article_id);
			if ($alert) {
				$this->model_comment->unsubscribe($article_id);
				$result["success"]=true;
				$result["message"]="unsubscribed";
			} else {
				$this->model_comment->subscribe($article_id);
				$result["success"]=true;
				$result["message"]="subscribed";
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
			if ($this->model_comment->submit($comment, $article_id, $parent, $userid)) {
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
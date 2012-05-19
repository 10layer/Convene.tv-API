<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Comments {
	public $ci;
	
	public function __construct() {
		$this->ci=&get_instance();
		$this->ci->load->model("model_comment");
		$this->ci->load->model("model_article");
		$this->ci->load->model("model_user");
		$this->ci->load->library('akismet');
	}

	public function draw_comments() {
		$urlid=$this->ci->uri->segment(3);
		$article=$this->ci->model_article->get($urlid);
		if (empty($article->id)) {
			return false;
		}
		$data["comments"]=$this->ci->model_comment->get_by_article($article->id);
		$data["commentalert"]=$this->ci->model_comment->check_subscribe($article->id);
		$data["article"]=$article;
		$this->ci->load->view("comments",$data);
	}

	public function savecomment() {
		$urlid=$this->ci->uri->segment(2);
		$article=$this->ci->model_article->getbyurlid($urlid);
		if (empty($article->id)) {
			return false;
		}
		$comment=$this->ci->input->post("comment");
		if (!empty($comment)) {
			$double=$this->ci->model_comment->checkdouble($article->id, $comment);
			if (!$double) {
				$user=$this->ci->model_user->getbyid($this->ci->session->userdata("user_id"));
				$dbdata=array(
					"comment"=>strip_tags($comment),
					"user_id"=>$this->ci->session->userdata("user_id"),
					"article_id"=>$article->id,
					"parent_id"=>$this->ci->input->post("comment_parent_id")
				);
				$this->ci->model_comment->insert($dbdata);
				$this->email_alerts($article,$comment);
				return "Your comment has been submitted";
			} else {
				return "Your comment was already submitted";
			}
		}
		return "";
	}

	protected function email_alerts($article,$comment) {
		$this->ci->load->library('email');
		$config["mailtype"]="html";
		$this->ci->email->initialize($config);

		$template=file_get_contents(base_url()."user/emailtemplate");
		$emails=$this->ci->model_comment->emailalert_list($article->id);
		foreach($emails as $email) {
			if ($email->user_id!=$this->ci->session->userdata("user_id")) {
				$this->ci->email->clear(TRUE);
				$this->ci->email->from('comments@thedailymaverick.co.za', 'The Daily Maverick');
				$this->ci->email->to($email->email);

				$this->ci->email->subject('Comment on The Daily Maverick article');
				
				$msg="Hi {$email->fname},\n\nThe article '<a href='".base_url()."article/{$article->urlid}'>{$article->headline}</a>' has received the following comment:\n\n<div style='font-family: arial; font-weight: bold; border: 1px #CCC solid; padding: 5px; margin-left: 10px;'>$comment\n\n".base_url()."article/{$article->urlid}</div>\n\nYou can stop receiving alerts about comments on this article by unticking 'Subscribe to this article's comments' at the top of the article's comments section";
				$s=str_replace("[CONTENT]",nl2br($msg),$template);
				$this->ci->email->message($s);
				$this->ci->email->set_alt_message(strip_tags($msg)."\n\nKind regards,\nThe Daily Maverick Team\n\n".base_url());
				$this->ci->email->send();
			}
		}
	}

	public function checkspam($comment,$author,$email) {
		$commentarray=array("body"=>$comment, "author"=>$author, "email"=>$email, "website"=>"");
		$config = array(
			'blog_url' => 'http://www.thedailymaverick.co.za/',
			'api_key' => '307611c58c89',
			'comment' => $commentarray
		);
		$this->ci->akismet->init($config);
		if ( $this->ci->akismet->errors_exist() ) {
			if ( $this->ci->akismet->is_error('AKISMET_INVALID_KEY') ) {
				print 'AKISMET :: Theres a problem with the api key';
			} elseif ( $this->akismet->is_error('AKISMET_RESPONSE_FAILED') ) {
				print 'AKISMET :: Looks like the servers not responding';
			} elseif ( $this->akismet->is_error('AKISMET_SERVER_NOT_FOUND') ) {
				print 'AKISMET :: Wheres the server gone?';
			}
			// If the server is down, we have to post the comment :(
// 			$this->_post_comment($comment);
// 			$this->load->view('thankyou');
			return false;
		} else {
	if ( $this->ci->akismet->is_spam() ) {
		return true;
	} else {
// 		$this->_post_comment($comment);
		return false;
	}
}
	}
	
}
?>
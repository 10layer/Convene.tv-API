<?php
	class Model_Comment extends CI_Model {
		
		public function get_by_article($urlid, $publication_id) {
			$this->db->select("comments.*");
			$this->db->select("0 AS level", false);
			$this->db->select("users.fname, users.sname");
			$this->db->from("comments");
			$this->db->join("users","users.id=comments.user_id");
			$this->db->where("urlid",$urlid)->where("publication_id", $publication_id);
			//$this->db->where("parent_id",0);
			$this->db->order_by("comments.date_created");
			$query=$this->db->get();
			return $query->result();
		}
		
		public function count_by_article($urlid, $publication_id) {
			return $this->db->select("*")->from("comments")->where("urlid",$urlid)->where("publication_id",$publication_id)->get()->num_rows();
		}

		public function get_children($id) {
			$this->db->select("comments.id,comments.comment, comments.date_created");
			$this->db->select("users.fname, users.sname");
			$this->db->from("comments");
			$this->db->join("users","users.id=comments.user_id");
			$this->db->where("comments.parent_id",$id);
			$query=$this->db->get();
			return $query->result();
		}

		public function checkdouble($urlid,$publication_id,$comment) {
			$query=$this->db->get_where("comments",array("urlid"=>$urlid, "publication_id"=>$publication_id, "comment"=>$comment));
			if ($query->num_rows()>0) {
				return true;
			}
			return false;
		}

		public function getall_admin($start=0,$limit=5) {
			$this->db->select("comments.id,comments.comment, comments.date_created");
			$this->db->select("users.fname, users.sname, users.id AS user_id");
			$this->db->select("articles.headline, articles.urlid");
			$this->db->from("comments");
			$this->db->join("users","users.id=comments.user_id");
			$this->db->join("articles","articles.id=comments.article_id");
			$this->db->order_by("date_created DESC");
			$this->db->limit($limit,$start);
			$query=$this->db->get();
			return $query->result();
		}

		public function subscribe($urlid, $publication_id) {
			$userid=$this->session->userdata("user_id");
			$this->db->insert("article_alerts",array("urlid"=>$urlid,"user_id"=>$userid, "publication_id"=>$publication_id));
		}

		public function unsubscribe($urlid, $publication_id) {
			$userid=$this->session->userdata("user_id");
			$this->db->delete("article_alerts",array("urlid"=>$urlid,"user_id"=>$userid, "publication_id"=>$publication_id));
		}

		public function check_subscribe($urlid, $publication_id) {
			$userid=$this->session->userdata("user_id");
			if (empty($userid)) {
				return false;
			}
			$query=$this->db->get_where("article_alerts",array("urlid"=>$urlid,"user_id"=>$userid, "publication_id"=>$publication_id));
			$result=$query->row();
			return (!empty($result->id));
		}

		public function emailalert_list($articleid) {
			$this->db->select("users.email, users.fname, users.id AS user_id");
			$this->db->from("article_alerts");
			$this->db->join("users","users.id=article_alerts.user_id");
			$this->db->where("article_id",$articleid);
			$query=$this->db->get();
			return $query->result();
		}
		
		public function submit($comment, $urlid, $parent_id=0, $user_id, $publication_id) {
			if ($this->checkdouble($urlid, $publication_id, $comment)) {
				return false;
			}
			$dbdata=array(
				"comment"=>strip_tags($comment),
				"user_id"=>$user_id,
				"urlid"=>$urlid,
				"parent_id"=>$parent_id,
				"publication_id"=>$publication_id
			);
			$this->db->insert("comments",$dbdata);
			return true;
		}
	}
?>
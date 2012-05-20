<?php
	class Model_Comment extends CI_Model {
		
		public function get_comment($comment_id) {
			return $this->db->get_where("comments", array("id"=>$comment_id))->row();
		}
		
		public function get_by_article($urlid, $publication_id) {
			$this->db->select("comments.*");
			$this->db->select("0 AS level", false);
			$this->db->select("users.fname, users.sname");
			$this->db->from("comments");
			$this->db->join("users","users.id=comments.user_id");
			$this->db->where("urlid",$urlid)->where("publication_id", $publication_id)->where("live",true);
			//$this->db->where("parent_id",0);
			$this->db->order_by("comments.date_created");
			$query=$this->db->get();
			return $query->result();
		}
		
		public function count_by_article($urlid, $publication_id) {
			return $this->db->select("*")->from("comments")->where("urlid",$urlid)->where("publication_id",$publication_id)->where("live",true)->get()->num_rows();
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

		public function getall_admin($start=0,$limit=25, $publication_id) {
			$this->db->select("comments.id,comments.comment, comments.date_created, comments.urlid, comments.live");
			$this->db->select("users.fname, users.sname, users.id AS user_id");
			$this->db->from("comments");
			$this->db->join("users","users.id=comments.user_id");
			$this->db->where("publication_id", $publication_id);
			$this->db->order_by("date_created DESC");
			$this->db->limit($limit,$start);
			$query=$this->db->get();
			return $query->result();
		}
		
		public function search_admin($searchstring, $start=0,$limit=25, $publication_id) {
			$this->_search_admin($searchstring, $publication_id);
			$this->db->limit($limit,$start);
			$query=$this->db->get();
			return $query->result();
		}
		
		public function search_admin_count($searchstring, $publication_id) {
			$this->_search_admin($searchstring, $publication_id);
			return $this->db->get()->num_rows();
		}
		
		protected function _search_admin($searchstring, $publication_id) {
			$this->db->select("comments.id,comments.comment, comments.date_created, comments.urlid, comments.live");
			$this->db->select("users.fname, users.sname, users.id AS user_id");
			$this->db->from("comments");
			$this->db->join("users","users.id=comments.user_id");
			$this->db->like("comment", $searchstring);
			$this->db->where("publication_id", $publication_id);
			$this->db->order_by("date_created DESC");
		}
		
		public function usersearch_admin($searchstring, $start=0,$limit=25, $publication_id) {
			$this->_usersearch_admin($searchstring, $publication_id);
			$this->db->limit($limit,$start);
			$query=$this->db->get();
			return $query->result();
		}
		
		public function usersearch_admin_count($searchstring, $publication_id) {
			$this->_usersearch_admin($searchstring, $publication_id);
			return $this->db->get()->num_rows();
		}
		
		protected function _usersearch_admin($searchstring, $publication_id) {
			$this->db->select("comments.id,comments.comment, comments.date_created, comments.urlid, comments.live");
			$this->db->select("users.fname, users.sname, users.id AS user_id");
			$this->db->from("comments");
			$this->db->join("users","users.id=comments.user_id");
			$this->db->like("users.fname", $searchstring);
			$this->db->or_like("users.sname", $searchstring);
			$this->db->or_like("users.email", $searchstring);
			$this->db->or_like("CONCAT(TRIM(users.fname), ' ', TRIM(users.sname))", $searchstring);
			$this->db->where("publication_id", $publication_id);
			$this->db->order_by("date_created DESC");
		}
		
		public function urlidsearch_admin($searchstring, $start=0,$limit=25, $publication_id) {
			$this->_urlidsearch_admin($searchstring, $publication_id);
			$this->db->limit($limit,$start);
			$query=$this->db->get();
			return $query->result();
		}
		
		public function urlidsearch_admin_count($searchstring, $publication_id) {
			$this->_urlidsearch_admin($searchstring, $publication_id);
			return $this->db->get()->num_rows();
		}
		
		protected function _urlidsearch_admin($searchstring, $publication_id) {
			$this->db->select("comments.id,comments.comment, comments.date_created, comments.urlid, comments.live");
			$this->db->select("users.fname, users.sname, users.id AS user_id");
			$this->db->from("comments");
			$this->db->join("users","users.id=comments.user_id");
			$this->db->like("comments.urlid", $searchstring);
			$this->db->where("publication_id", $publication_id);
			$this->db->order_by("date_created DESC");
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
				"publication_id"=>$publication_id,
				"live"=>1,
			);
			$this->db->insert("comments",$dbdata);
			return true;
		}
		
		public function count_all($publication_id) {
			return $this->db->where("publication_id", $publication_id)->get("comments")->num_rows();
		}
		
		public function unlive($comment_id) {
			$this->db->where("id",$comment_id)->update("comments", array("live"=>false));
			return true;
		}
		
		public function live($comment_id) {
			$this->db->where("id",$comment_id)->update("comments", array("live"=>true));
			return true;
		}
		
		public function update($comment_id, $data) {
			$this->db->where("id",$comment_id)->update("comments", $data);
			return true;
		}
	}
?>
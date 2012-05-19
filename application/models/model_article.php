<?php
	
	/**
	 * Model_Article class.
	 * 
	 * @extends Model
	 */
	class Model_Article extends CI_Model {
		
		public function get($urlid) {
			return $this->db->get_where("articles", array("urlid"=>$urlid))->row();
		}
		
		public function get_article($urlid) {
			$this->db->select("articles.*");
			$this->db->select("photos.urlid AS photo_urlid, photos.title AS photo_title");
			$this->db->select("authors.name AS author_name, authors.email AS author_email, authors.twitter AS author_twitter, authors.photo_id AS author_photo_id, authors.urlid AS author_urlid");
			$this->db->select("sections.name AS section_name, sections.urlid AS section_urlid");
			$this->db->from("articles");
			$this->db->join("articles_photos_link","articles.id=articles_photos_link.article_id","left outer");
			$this->db->join("photos","articles_photos_link.photo_id=photos.id","left outer");
			$this->db->join("authors","articles.author_id=authors.id","left outer");
			
			$this->db->join("articles_sections_link", "articles.id=articles_sections_link.article_id", "left outer");
			$this->db->join("sections", "articles_sections_link.section_id=sections.id");
			$this->db->where("articles.urlid", $urlid);
			$this->db->order_by("sections.is_main DESC")->order_by("sections.order");
			$query=$this->db->get();
			return $query->row();
		}
		
		public function next_article($urlid) {
			$orig=$this->db->get_where("articles", array("urlid"=>$urlid))->row();
			$next=$this->db->where("start_date < ", $orig->start_date)->where("live",1)->where("type_id",$orig->type_id)->order_by("start_date DESC")->limit(1)->get("articles")->row();
			if ($next->type_id==1) {
				return $this->get_article($next->urlid);
			} else {
				return $this->get_opinionista($next->urlid);
			}
		}
		
		public function get_opinionista($urlid) {
			$this->db->select("articles.*");
			$this->db->select("photos.urlid AS photo_urlid, photos.title AS photo_title");
			$this->db->select("authors.name AS author_name, authors.email AS author_email, authors.twitter AS author_twitter, authors.photo_id AS author_photo_id, authors.urlid AS author_urlid, authors.about AS author_about");
			//$this->db->select("sections.name AS section_name, sections.urlid AS section_urlid");
			$this->db->from("articles");
			$this->db->join("authors","articles.author_id=authors.id","left outer");
			$this->db->join("photos","authors.photo_id=photos.id","left outer");
			$this->db->join("articles_sections_link", "articles.id=articles_sections_link.article_id", "left outer");
			//$this->db->join("sections", "articles_sections_link.section_id=sections.id");
			$this->db->where("articles.urlid", $urlid);
			$query=$this->db->get();
			return $query->row();
		}
		
		public function articles_by_author($id) {
			$this->db->select("articles.*");
			$this->db->from("articles");
			$this->db->where("author_id",$id);
			$this->db->order_by("dateline DESC");
			$query=$this->db->get();
			return $query->result();
		}
		
		public function get_related($articleid, $limit) {
			$sections=$this->get_sections($articleid);
			$this->db->select("articles.headline, articles.urlid")->from("articles")->join("articles_sections_link","articles_sections_link.article_id=articles.id")->where("articles.live",1);
			$this->db->select("photos.urlid AS photo_urlid")->join("articles_photos_link","articles_photos_link.article_id=articles.id")->join("photos","photos.id=articles_photos_link.photo_id");
			$this->db->select("authors.name AS author_name, authors.email AS author_email, authors.twitter AS author_twitter, authors.photo_id AS author_photo_id, authors.urlid AS author_urlid, authors.about AS author_about");
			$this->db->join("authors","articles.author_id=authors.id","left outer");
			$tmp=array();
			foreach($sections as $section) {
				$tmp[]="articles_sections_link.section_id=".$section->id;
			}
			$this->db->where("(".implode(" or ",$tmp).")", "",false);
			$this->db->where("articles.id !=", $articleid);
			$this->db->order_by("start_date","DESC");
			$this->db->group_by("urlid");
			$this->db->limit($limit);
			return $this->db->get()->result();
		}
		
		public function get_sections($articleid) {
			$this->db->select("sections.id");
			$this->db->from("sections");
			$this->db->join("articles_sections_link","articles_sections_link.section_id=sections.id");
			$this->db->where("articles_sections_link.article_id",$articleid);
			$query=$this->db->get();
			return $query->result();
		}
		
		public function get_top($limit=4, $exclude_ids=array()) {
			$this->db->select("articles.*");
			$this->db->select("photos.urlid AS photo_urlid, photos.title AS photo_title");
			$this->db->select("authors.name AS author_name, authors.email AS author_email, authors.twitter AS author_twitter, authors.photo_id AS author_photo_id, authors.urlid AS author_urlid");
			$this->db->select("sections.name AS section_name, sections.urlid AS section_urlid");
			$this->db->from("articles_home_link");
			$this->db->join("articles","articles.id=articles_home_link.article_id");
			$this->db->join("articles_photos_link","articles.id=articles_photos_link.article_id","left outer");
			$this->db->join("photos","articles_photos_link.photo_id=photos.id","left outer");
			$this->db->join("authors","articles.author_id=authors.id","left outer");
			
			$this->db->join("articles_sections_link", "articles.id=articles_sections_link.article_id", "left outer");
			$this->db->join("sections", "articles_sections_link.section_id=sections.id");
			$this->db->where("articles.start_date <=",date("Y-m-d H:i:s"));
			$this->db->where("articles.live",1);
			$this->db->where("articles.type_id",1);
			$this->db->group_by("articles.urlid");
			$this->db->order_by("articles_home_link.order","ASC");
			$this->db->order_by("start_date","DESC");
			if (is_array($exclude_ids) && !empty($exclude_ids)) {
				$this->db->where_not_in("articles.id", $exclude_ids);
			}
			$this->db->limit($limit);
			$query=$this->db->get();
			return $query->result();
		}
		
		public function get_rss($limit=60,$start=0) {
			$this->db->select("articles.*");
			$this->db->select("photos.urlid AS photo_urlid, photos.title AS photo_title");
			$this->db->select("authors.name AS author_name, authors.email AS author_email, authors.twitter AS author_twitter, authors.photo_id AS author_photo_id, authors.urlid AS author_urlid");
			$this->db->from("articles");
			$this->db->join("articles_photos_link","articles.id=articles_photos_link.article_id","left outer");
			$this->db->join("photos","articles_photos_link.photo_id=photos.id","left outer");
			$this->db->join("authors","articles.author_id=authors.id","left outer");
			$this->db->where("articles.type_id !=",5);
			$this->db->where("articles.live",1);
			$this->db->where("articles.start_date <=",date("c"));
			$this->db->group_by("articles.urlid");
			$this->db->order_by("start_date","DESC");
			$this->db->limit($limit,$start);
			$query=$this->db->get();
			return $query->result();
		}
		
	}

/* End of file .php */
/* Location: ./system/application/models/ */
<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Convene_Security {
	public $ci;
	public $private_key;
	public $public_key;
	public $publication;
	public $is_private=false;
	protected $encryption_key;
	
	public function __construct() {
		$this->ci=&get_instance();
		$this->encryption_key=$this->ci->config->item("encryption_key");
		$parts=$this->ci->uri->segment_array();
		$api_key=array_pop($parts);
		$publication=$this->ci->db->get_where("publications", array("api_key"=>$api_key))->row(); //First look for private key
		if (empty($publication->id)) {
			$this->publication=$this->ci->db->select("publications.*")->from("public_keys")->join("publications","publications.id=public_keys.publication_id")->where("public_key", $api_key)->get()->row(); //Search for a public key
			if (empty($this->publication->id)) {
				return false;
			}
			$this->public_key=$api_key;
		} else {
			$this->is_private=true;
			$this->private_key=$api_key;
			$this->publication=$publication;
		}
		return true;
	}
	
	public function public_key() {
		if (empty($this->public_key)) {
			if (!$this->is_private) {
				return false;
			}
			$key=md5($this->encryption_key.microtime());
			while ($this->ci->db->get_where("public_keys",array("public_key"=>$key))->num_rows() != 0) {
				$key=md5($this->encryption_key.microtime());
			}
			$this->ci->db->insert("public_keys", array("public_key"=>$key, "publication_id"=>$this->publication->id));
			$this->public_key=$key;
		}
		return $this->public_key;
	}
	
	public function private_key() {
		$this->private_only();
		return $this->private_key;
	}
	
	public function private_only() {
		if (!$this->is_private) {
			header('HTTP/1.1 403 Forbidden');
			print "<h1>Convene private key error</h1>";
			die();
		}
	}
	
	public function publication_id() {
		return $this->publication->id;
	}
	
}
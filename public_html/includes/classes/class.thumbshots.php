<?

class thumbshots {
	function thumbshots($link,$sites_id) {
		$this->set_dblink($link);
		$this->set_sites_id($sites_id);
		$this->success_msgs = array();
		$this->error_msgs = array();
		$this->default_width = 150;
		$this->default_height = 110;
		// do nothing
	}
	
	function set_sites_id($sites_id) {
		$this->sites_id = $sites_id;
	}
	
	function set_dblink($link) {
		$this->dblink = $link;
	}
	
	function set_key($key) {
		$this->key = $key;
	}
	
	function get_thumb($url, $width = null, $height = null) {
		if (!$width) { $width = $this->default_width; }
		if (!$height) { $height = $this->default_height; }
		if (!$url) { return false; }
		
		$url = mcrypt_encrypt(MCRYPT_3DES, $this->key, $url, MCRYPT_MODE_ECB, "00000000");
		$url = base64_encode($url);
		$url = 'http://simple.thumbshots.com/image.aspx?cid=1385&v=1&w='.$width.'&h='.$height.'&xurl=' . urlencode($url);
		
		if ($thumbnail = file_get_contents($url)) {
			return $thumbnail;
		}
		
		return false;
	}
	
}
?>
<?php

class pid {

    protected $filename;
    public $already_running = false;
   
    function __construct($directory=null) {
       
	   if (!$directory) { $directory = sys_get_temp_dir(); }
	   	
		foreach ((array)$_SERVER['argv'] as $key => $val) {
        	if (in_array($val, array('PID')) && $_SERVER['argv'][$key+1]) { $_REQUEST[$val] = $_SERVER['argv'][$key+1]; }
		}
		
		if ($_REQUEST['PID']) { $file = $_REQUEST['PID']; }
		elseif ($_SERVER['PHP_SELF']) { $file = $_SERVER['PHP_SELF']; }
		else { $file = __FILE__; }
		
        $this->filename = $directory . '/' . md5($file) . '.pid';
       
        if(is_writable($this->filename) || is_writable($directory)) {
           
            if(file_exists($this->filename)) {
                $pid = (int)trim(file_get_contents($this->filename));
//              if(posix_getsid($pid) !== false) {
				exec("ps -e -o pid= | awk '{print $1}'", $pids);
		        foreach ($pids as $key => $val) { $pids[$key]=trim($val); }
		        if (in_array($pid, $pids)) {
                    $this->already_running = true;
                }
            }
           
        }
        else {
            die("Cannot write to pid file '$this->filename'. Program execution halted.\n");
        }
       
        if(!$this->already_running) {
            $pid = getmypid();
            file_put_contents($this->filename, $pid);
        }
       
    }

    public function __destruct() {

        if(!$this->already_running && file_exists($this->filename) && is_writeable($this->filename)) {
            unlink($this->filename);
        }
   
    }
   
}

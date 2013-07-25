<?php
/***************************************************************************

pseudo-cron v1.3
(c) 2003,2004 Kai Blankenhorn
www.bitfolge.de/pseudocron
kaib@bitfolge.de


This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.

****************************************************************************


Usually regular tasks like backup up the site's database are run using cron
jobs. With cron jobs, you can exactly plan when a certain command is to be 
executed. But most homepage owners can't create cron jobs on their web 
server - providers demand some extra money for that.
The only thing that's certain to happen quite regularly on a web page are 
page requests. This is where pseudo-cron comes into play: With every page 
request it checks if any cron jobs should have been run since the previous 
request. If there are, they are run and logged.

Pseudo-cron uses a syntax very much like the Unix cron's one. For an 
overview of the syntax used, see a page of the UNIXGEEKS. The syntax 
pseudo-cron uses is different from the one described on that page in 
the following points:

  -  there is no user column
  -  the executed command has to be an include()able file (which may contain further PHP code) 


All job definitions are made in a text file on the server with a 
user-definable name. A valid command line in this file is, for example:

*	2	1,15	*	*	samplejob.inc.php

This runs samplejob.inc.php at 2am on the 1st and 15th of each month.


Features:
  -  runs any PHP script
  -  periodical or time-controlled script execution
  -  logs all executed jobs
  -  can be run from an IMG tag in an HTML page
  -  follow Unix cron syntax for crontabs


Usage:
  -  Modify the variables in the config section below to match your server.
  -  Write a PHP script that does the job you want to be run regularly. Be
     sure that any paths in it are relative to pseudo-cron.
  -  Set up your crontab file with your script
	-  put an include("pseudo-cron.inc.php"); statement somewhere in your most
	   accessed page or call pseudo-cron-image.php from an HTML img tag
  -  Wait for the next scheduled run :)


Note:
You can log messages to pseudo-cron's log file from cron jobs by calling
     logMessage("log a message");
		 
		 

Release notes for v1.2.2:

This release changed the way cron jobs are called. The file paths you specify in
the crontab file are now relative to the location of pseudo-cron.inc.php, instead
of to the calling script. Example: If /include/pseudo-cron.inc.php is included
in /index.php and your cronjobs are in /include/cronjobs, then your crontab file
looked like this:

10	1	*	*	*	include/cronjobs/dosomething.php	# do something

Now you have to change it to

10	1	*	*	*	cronjobs/dosomething.php	# do something

After you install the new version, each of your cronjobs will be run once,
and the .job files will have different names than before.



Changelog:

v1.3	06-15-04
	added:	the number of jobs run during one call of pseudocron
		can now be limited.
	added:	additional script to call pseudocron from an HTML img tag
	improved storage of job run times
	fixed a bug with jobs marked as run although they did not complete


v1.2.2	01-17-04
	added:	send an email for each completed job
	improved:	easier cron job configuration (relative to pseudo-cron, not
		to calling script. Please read the release notes on this)


v1.2.1	02-03-03
	fixed:	 jobs may be run too often under certain conditions
	added:	 global debug switch
	changed: typo in imagecron.php which prevented it from working


v1.2	01-31-03
	added:   more documentation
	changed: log file should now be easier to use
	changed: log file name


v1.1	01-29-03
	changed: renamed pseudo-cron.php to pseudo-cron.inc.php
	fixed:   comments at the end of a line don't work
	fixed:   empty lines in crontab file create nonsense jobs
	changed: log file grows big very quickly
	changed: included config file in main file to avoid directory confusion
	added:   day of week abbreviations may now be used (three letters, english)


v1.0	01-17-03
	inital release

***************************************************************************/

class pseudo_cron {

	function pseudo_cron($dblink,$sites_id) {
		$this->dblink = $dblink;
		$this->sites_id = $sites_id;
		$this->writeDir = false; // "/cronjobs/"; // The directory where the script can store information on completed jobs and its log file.
		$this->useLog = false; // Control logging, true=use log file, false=don't use log file
		$this->sendLogToEmail = ""; // Where to send cron results.
		$this->debug = false; // Turn on / off debugging output
		/*		don't change anything here		*/
		$this->PC_MINUTE = 1;
		$this->PC_HOUR = 2;
		$this->PC_DOM = 3;
		$this->PC_MONTH = 4;
		$this->PC_DOW = 5;
		$this->PC_CMD = 7;
		$this->PC_COMMENT = 8;
		$this->PC_CRONLINE = 20;
		$this->resultsSumary = '';
		
	}

	function logMessage($msg) {
		if ($msg[strlen($msg)-1]!="\n") {
			$msg.="\n";
		}
		if ($this->debug || $this->debug2) echo $msg;
		$this->resultsSummary.= $msg;
		if ($this->useLog && $this->writeDir !== FALSE) {
			$logfile = $this->writeDir."pseudo-cron.log";
			$file = fopen($logfile,"a");
			fputs($file,date("r",time())."  ".$msg);
			fclose($file);
		}
	}

	function lTrimZeros($number) {
		while ($number[0]=='0') {
			$number = substr($number,1);
		}
		return $number;
	}

	function multisort(&$array, $sortby, $order='asc') {
	   foreach($array as $val) {
	       $sortarray[] = $val[$sortby];
	   }
	   $c = $array;
	   $const = $order == 'asc' ? SORT_ASC : SORT_DESC;
	   $s = array_multisort($sortarray, $const, $c, $const);
	   $array = $c;
	   return $s;
	}

	function parseElement($element, &$targetArray, $numberOfElements) {
		$subelements = explode(",",$element);
		for ($i=0;$i<$numberOfElements;$i++) {
			$targetArray[$i] = $subelements[0]=="*";
		}
		
		for ($i=0;$i<count($subelements);$i++) {
			if (preg_match("~^(\\*|([0-9]{1,2})(-([0-9]{1,2}))?)(/([0-9]{1,2}))?$~",$subelements[$i],$matches)) {
				if ($matches[1]=="*") {
					$matches[2] = 0;		// from
					$matches[4] = $numberOfElements;		//to
				} elseif ($matches[4]=="") {
					$matches[4] = $matches[2];
				}
				if ($matches[5][0]!="/") {
					$matches[6] = 1;		// step
				}
				for ($j=$this->lTrimZeros($matches[2]);$j<=$this->lTrimZeros($matches[4]);$j+=$this->lTrimZeros($matches[6])) {
					$targetArray[$j] = TRUE;
				}
			}
		}
	}
	
	function incDate(&$dateArr, $amount, $unit) {
		
		if ($this->debug2) echo sprintf("Increasing from %02d.%02d. %02d:%02d by %d %6s ",$dateArr[mday],$dateArr[mon],$dateArr[hours],$dateArr[minutes],$amount,$unit);
		if ($unit=="mday") {
			$dateArr["hours"] = 0;
			$dateArr["minutes"] = 0;
			$dateArr["seconds"] = 0;
			$dateArr["mday"] += $amount;
			$dateArr["wday"] += $amount % 7;
			if ($dateArr["wday"]>6) {
				$dateArr["wday"]-=7;
			}
	
			$months28 = Array(2);
			$months30 = Array(4,6,9,11);
			$months31 = Array(1,3,5,7,8,10,12);
			
			if (
				(in_array($dateArr["mon"], $months28) && $dateArr["mday"]==29 && !date('L', strtotime($dateArr["year"]."-01-01"))) ||
				(in_array($dateArr["mon"], $months28) && $dateArr["mday"]==30 && date('L', strtotime($dateArr["year"]."-01-01"))) ||
				(in_array($dateArr["mon"], $months30) && $dateArr["mday"]==31) ||
				(in_array($dateArr["mon"], $months31) && $dateArr["mday"]==32)
			) {
				$dateArr["mon"]++;
				if ($dateArr["mon"] == 13) { 
					$dateArr["mon"] = 1;
					$dateArr["year"]++;
				}
				$dateArr["mday"] = 1;
			}
			
		} elseif ($unit=="hour") {
			if ($dateArr["hours"]==23) {
				$this->incDate($dateArr, 1, "mday");
			} else {
				$dateArr["minutes"] = 0;
				$dateArr["seconds"] = 0;
				$dateArr["hours"]++;
			}
		} elseif ($unit=="minute") {
			if ($dateArr["minutes"]==59) {
				$this->incDate($dateArr, 1, "hour");
			} else {
				$dateArr["seconds"] = 0;
				$dateArr["minutes"]++;
			}
		}
		if ($this->debug2) echo sprintf("to %02d.%02d. %02d:%02d\n",$dateArr[mday],$dateArr[mon],$dateArr[hours],$dateArr[minutes]);
	}
	
	function getLastScheduledRunTime($job, $lastActual=0) {
		$extjob = Array();
		$this->parseElement($job[$this->PC_MINUTE], $extjob[$this->PC_MINUTE], 60);
		$this->parseElement($job[$this->PC_HOUR], $extjob[$this->PC_HOUR], 24);
		$this->parseElement($job[$this->PC_DOM], $extjob[$this->PC_DOM], 31);
		$this->parseElement($job[$this->PC_MONTH], $extjob[$this->PC_MONTH], 12);
		$this->parseElement($job[$this->PC_DOW], $extjob[$this->PC_DOW], 7);
		
		if (!$lastActual) { $dateArr = getdate($job["lastActual"]); } else { $dateArr = getdate($lastActual); }
		$minutesAhead = 0;
		while (
			$minutesAhead<525600 AND 
			(!$extjob[$this->PC_MINUTE][$dateArr["minutes"]] OR 
			!$extjob[$this->PC_HOUR][$dateArr["hours"]] OR 
			(!$extjob[$this->PC_DOM][$dateArr["mday"]] OR !$extjob[$this->PC_DOW][$dateArr["wday"]]) OR
			!$extjob[$this->PC_MONTH][$dateArr["mon"]])
		) {
			if (!$extjob[$this->PC_DOM][$dateArr["mday"]] OR !$extjob[$this->PC_DOW][$dateArr["wday"]]) {
				$this->incDate($dateArr,1,"mday");
				$minutesAhead+=1440;
				continue;
			}
			if (!$extjob[$this->PC_HOUR][$dateArr["hours"]]) {
				$this->incDate($dateArr,1,"hour");
				$minutesAhead+=60;
				continue;
			}
			if (!$extjob[$this->PC_MINUTE][$dateArr["minutes"]]) {
				$this->incDate($dateArr,1,"minute");
				$minutesAhead++;
				continue;
			}
		}
		
		if (mktime($dateArr["hours"],$dateArr["minutes"],0,$dateArr["mon"],$dateArr["mday"],$dateArr["year"]) - $job["lastActual"] < 60) { $dateArr["minutes"]++; }
		
		if ($this->debug2) print_r($dateArr);
		
		return mktime($dateArr["hours"],$dateArr["minutes"],0,$dateArr["mon"],$dateArr["mday"],$dateArr["year"]);
	}
	
	function getJobFileName($jobname) {
		$jobfile = $this->writeDir.urlencode($jobname).".job";
		return $jobfile;
	}
	
	function getLastActualRunTime($jobname) {
		if ($this->writeDir !== FALSE) {
			$jobfile = $this->getJobFileName($jobname);
			if (file_exists($jobfile)) {
				return filemtime($jobfile);
			}
		}
		return 0;
	}
	
	function markLastRun($jobname, $lastRun) {
		if ($this->writeDir !== FALSE) {
			$jobfile = $this->getJobFileName($jobname);
			@touch($jobfile);
		}
	}
	
	function runJob(&$job) {
		$resultsSummary = "";
		
		$lastActual = $job["lastActual"];
		//$lastScheduled = $this->getLastScheduledRunTime($job);
		$lastScheduled = $job["last_scheduled"];
		if ($lastScheduled<time()) {
			$this->logMessage("Running 	".$job[$this->PC_CRONLINE]);
			$this->logMessage("  Last run:       ".date("r",$lastActual));
			$this->logMessage("  Last scheduled: ".date("r",$lastScheduled));
			if ($this->debug || $this->debug2) {
				$e = @error_reporting(-1);
				include($job[$this->PC_CMD]);		// display errors only when debugging
				@error_reporting($e);
			} else {
				$e = @error_reporting(0);
				@include($job[$this->PC_CMD]);		// any error messages are supressed
				@error_reporting($e);
			}
			$this->markLastRun($job[$this->PC_CMD], $lastScheduled);
			$job["lastActual"] = time();
			$job["lastScheduled"] = $this->getLastScheduledRunTime($job);
			$this->logMessage("Completed	".$job[$this->PC_CRONLINE]);
			if ($this->sendLogToEmail!="") {
				mail($this->sendLogToEmail, "[cron] ".$job[$this->PC_COMMENT], $this->resultsSummary);
			}
			return true;
		} else {
			if ($this->debug) {
				$this->logMessage("Skipping 	".$job[$this->PC_CRONLINE]);
				$this->logMessage("  Last run:       ".date("r",$lastActual));
				$this->logMessage("  Last scheduled: ".date("r",$lastScheduled));
				$this->logMessage("Completed	".$job[$this->PC_CRONLINE]);
			}
			return false;
		}
	}
	
	function parseCronTab($cronTab) {
		$file = explode("\n", $cronTab);
		$job = Array();
		$jobs = Array();
		for ($i=0;$i<count($file);$i++) {
			if ($file[$i][0]!='#') {
	//			old regex, without dow abbreviations:
	//			if (preg_match("~^([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-7,/*]+|Sun|Mon|Tue|Wen|Thu|Fri|Sat)\\s+([^#]*)(#.*)?$~i",$file[$i],$job)) {
				if (preg_match("~^([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-9,/*]+)\\s+([-0-7,/*]+|(-|/|Sun|Mon|Tue|Wed|Thu|Fri|Sat)+)\\s+([^#]*)\\s*(#.*)?$~i",$file[$i],$job)) {
					$jobNumber = count($jobs);
					$jobs[$jobNumber] = $job;
					if ($jobs[$jobNumber][$this->PC_DOW][0]!='*' AND !is_numeric($jobs[$jobNumber][$this->PC_DOW])) {
						$jobs[$jobNumber][$this->PC_DOW] = str_replace(
							Array("Sun","Mon","Tue","Wed","Thu","Fri","Sat"),
							Array(0,1,2,3,4,5,6),
							$jobs[$jobNumber][$this->PC_DOW]);
					}
					$jobs[$jobNumber][$this->PC_CMD] = trim($job[$this->PC_CMD]);
					$jobs[$jobNumber][$this->PC_COMMENT] = trim(substr($job[$this->PC_COMMENT],1));
					$jobs[$jobNumber][$this->PC_CRONLINE] = $file[$i];
				}
				$jobfile = $this->getJobFileName($jobs[$jobNumber][$this->PC_CMD]);
				
				$jobs[$jobNumber]["lastActual"] = $this->getLastActualRunTime($jobs[$jobNumber][$this->PC_CMD]);
				$jobs[$jobNumber]["lastScheduled"] = $this->getLastScheduledRunTime($jobs[$jobNumber]);
			}
		}
		
		//$this->multisort($jobs, "lastScheduled");
		
		if ($this->debug2) var_dump($jobs);
		return $jobs;
	}

}
/*
if ($debug) echo "<pre>";

$jobs = parseCronFile($cronTab);
$jobsRun = 0;
for ($i=0;$i<count($jobs);$i++) {
	if ($maxJobs==0 || $jobsRun<$maxJobs) {
		if (runJob($jobs[$i])) {
			$jobsRun++;
		}
	}
}
if ($debug) echo "</pre>";
*/
?>
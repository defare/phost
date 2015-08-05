#!/usr/bin/php5 
<?php
if(isset($argv[1])){
	$a = $argv[1];
	//switch hosts
	switch_host($a);
} else{
	//show all hosts
	show_host();
}
function data(){
	$data    = array();
	$host    = file_get_contents('/etc/hosts');
	$preg    = "%#====(.*?)#====%si";
	preg_match_all($preg, $host,$match);
	foreach ($match[0] as $key => $value) {
		$r = strstr($value, "\n",true);
		$r = trim($r,'#==== ');
		$data[$r] = $value;
	}
	return $data;
}


//tools
function show_host(){
	//host switch
	$data = data();
	$now  = which_host($data);
	$count   = count($data);
	echo "{$count} hosts can be switch\r\n";
	foreach ($data as $key => $value) {
		if($key == $now){
			echo $key."(*)\n";
		}else{
			echo $key."\n";
		}
	}
}
function switch_host($a){
	//is in hosts
	$data    = data();
	if(!array_key_exists($a, $data)){
		echo "make sure the host is right!\n";
		show_host();
		exit;
	}
	$before  = '';
	$after   = '';
	$host    = file_get_contents('/etc/hosts');
	//before #====
	$before  = strstr($host,'#====',true);
	foreach ($data as $key => $value) {
		$head   = strstr($value,"\n",true);
		$head   = $head."\n";
		$bottom = '#===='; 
		$s = strpos($value, "\n");
		//need to process
		$v = substr($value, $s);
		$v = trim($v, $bottom);
		$v = trim($v, "\n");
		if($a == $key){
			//remove #
			$r = substr_count($v, "#");
			if($r != 0){
				$v = str_replace("#", "", $v);
			}
		}else{
			//add # after \n
			$r = substr_count($v, "#");
			if($r == 0){
				$v = add($v);
			}
		}
		$d = $head.$v."\n".$bottom."\n\n";
		$after.=$d;
	}
	$fdata = $before.$after;
	file_put_contents('/etc/hosts', $fdata);
	echo "switch success!\n";
	show_host();
}
function which_host($data){
	$name = '';
	foreach ($data as $key => $value) {
		$r = substr_count($value, "#");
		if($r <= 3){
			$name = $key;
			break;
		}
	}
	return $name;
}
function add($str){
	$n = substr_count($str, "\n");
	$str = '#'.$str;
	//need $n #
	$j = 0;
	for ($i=0; $i < $n; $i++) {
		$j = strpos($str, "\n",$j+1);
		$b = substr($str, 0, $j);
		$a = substr($str, $j+1);
		$str = $b."\n#".$a;
	}
	return $str;
}
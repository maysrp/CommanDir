<?php
	session_start();
	$exec=new exec();
	$exec->get($_GET['command']);
	




	class exec{
		public $dir;
		public $notex;
		public $notdir;
		public $file;
		public $dirdir;
		public $all;
		function __construct(){
			$this->notex=array("php","js","tgz");//不允许显示的后缀名文件
			$this->notdir=array("a","phpmyadmin");//不允许显示的文件夹
		}
		function lsdir($data){			
			$session=isset($_SESSION['dir'])?$_SESSION['dir']:"./";
			if ($data) {
				$first=substr($data,0,1);
				if($first=="/"){
					$_SESSION['dir']="./";
					$session="./";

				}
				foreach ($this->notdir as $key => $value) {
					if(strtolower($data)==$value){
						$date=".";
					}
				}
				$tom=trim($data);
				$tam=str_replace("..", ".", $tom);
				$this->dir=$session."/".$tam;
			}else{
				$this->dir=$session;
			}
		}
		function get($info){
			$info=preg_replace('/\s+/', ' ',$info);
			$cma= explode(' ', $info);
			$this->jugg($cma);

		}
		function jugg($data){
			$type=strtolower($data[0]);
			switch ($type) {
				case 'cd':
					$this->cd($data);
					break;
				case 'cat':
					$this->cat($data);
					break;
				case 'ls':
					$this->ls($data);
					break;
				case 'md5':
					$this->md5($data);
					break;
				case 'rm':
					$this->rm($data);
					break;
				case 'rmdir':
					$this->rmdir($data);
					break;
				case 'wget':
					$this->wget($data);
					break;
				case 'time':
					$this->time($data);
					break;
				case 'rand':
					$this->rand($data);
					break;
				default:
					$this->error($data);
					break;
			}
		}
		function error(){

		}
		function md5($data){
			$string_1=isset($data[1])?(int)$data[1]:0;
			$string_2=isset($data[2])?(int)$data[2]:0;
			if($string_1){
				$re['con']=md5($string_1.$string_2);
				$re['status']=true;
				$re['type']="text";
			}else{
				$re['status']=false;
				$re['type']="text";
				$re['con']="未输入字符串";
			}
			$this->json($re);
		}
		function time($data){
			$string_1=isset($data[1])?$data[1]:0;
			$string_2=isset($data[2])?$data[2]:0;
			if($string_1){
				$time=(int)$string_1;
				$date=date('Y-m-d H:i:s',$time);
				$re['con']="当前时间戳:".$time." 对应时间:".$date;
				$re['status']=true;
				$re['type']="text";
			}else{
				$time=time();
				$date=date('Y-m-d H:i:s');
				$re['con']="当前时间戳:".$time." 对应时间:".$date;
				$re['status']=true;
				$re['type']="text";
			}
			$this->json($re);
		}
		function rand($data){
			$string_1=isset($data[1])?$data[1]:0;
			$string_2=isset($data[2])?$data[2]:0;
			$s=(int)$string_1;
			$e=(int)$string_2;
			if($e>$s){
				$num=mt_rand($s,$e);
				$re['con']="随机数:".$num;
				$re['status']=true;
				$re['type']="text";
			}elseif($s>$e){
				$num=mt_rand($e,$s);
				$re['con']="随机数:".$num;
				$re['status']=true;
				$re['type']="text";
			}else{
				$num=mt_rand(1,999);
				$re['con']="[1-999中]随机数:".$num;
				$re['status']=true;
				$re['type']="text";
			}
			$this->json($re);
		}
		function cd($data){
			$string_1=isset($data[1])?$data[1]:"";
			//$string_1=$this->gbk($string_1);//Windows汉简环境下直接转为GBK[不建议使用]
			$this->lsdir($string_1);
			$vt=$this->open_dir();
			if($vt){
				$re['con']=$this->all;
				$re['status']=true;
				$re['type']="list";
				$_SESSION['dir']=$this->dir;
			}else{
				$re['con']="错误,无该目录！";
				$re['status']=false;
				$re['type']="text";
			}
			$this->json($re);

		}
		function ls($data){
			$string_1=isset($data[1])?$data[1]:"";
			//$string_1=$this->gbk($string_1);//Windows汉简环境下直接转为GBK[不建议使用]
			$this->lsdir($string_1);
			$vt=$this->open_dir();
			if($vt){
				$re['con']=$this->all;
				$re['status']=true;
				$re['type']="list";
			}else{
				$re['con']="错误,无该目录！";
				$re['status']=false;
				$re['type']="text";
			}
			$this->json($re);
		}
		function open_dir(){
			if(is_dir($this->dir)){
				if($dh=opendir($this->dir)){
					while(($file=readdir($dh))!==false){
						$this->file_array($file);
					}
					@sort($this->file);
					@sort($this->dirdir);
					closedir($dh);
				}
				return true;
			}else{
				return false;
			}
		}
		function file_array($jugg){
			if($jugg!="."&&$jugg!=".."){
				if (is_dir($this->dir."/".$jugg)) {
					if(!in_array(strtolower($this->filename($jugg)), $this->notdir)){
						$this->dirdir[]=$this->dir."/".$jugg;
						$file['type']='dir';
						$file['name']=$this->str_encode($jugg);
						$file['size']=$this->size($this->dir."/".$jugg);
						$file['mtime']=$this->mtime($this->dir."/".$jugg);
						$file['ctime']=$this->ctime($this->dir."/".$jugg);
						$file['atime']=$this->atime($this->dir."/".$jugg);
						$file['dir']=$this->dir."/".$jugg;
						$this->all[]=$file;
					}	
				}else{
					$ex=$this->ex($jugg);
					if(!in_array($ex, $this->notex)){
						$this->file[]=$this->dir."/".$jugg;
						$file['name']=$this->str_encode($jugg);
						$file['type']=$this->type($this->dir."/".$jugg);
						$file['size']=$this->size($this->dir."/".$jugg);
						$file['mtime']=$this->mtime($this->dir."/".$jugg);
						$file['ctime']=$this->ctime($this->dir."/".$jugg);
						$file['atime']=$this->atime($this->dir."/".$jugg);
						$file['dir']=$this->dir."/".$jugg;
						$this->all[]=$file;
					}
				}
			}
		}
		function gbk($str){
			$ar=array("GBK","BIG5","Shift_JIS");
        	$encode=mb_detect_encoding($str,$ar);
        	$string=iconv($encode,"GBK",$str);//$encode为你之前文件编码格式
        	return $string;
		}
		function filename($file){
			$ar=explode("/", $file);
			return array_pop($ar);
		}
		function size($file){
			$fz=filesize($file);
			if ($fz>(1024*1024*1024)) {
				return sprintf("%.2f",$fz/(1024*1024*1024))."GB";
			}elseif ($fz>(1024*1024)) {
				return sprintf("%.2f",$fz/(1024*1024))."MB";
			}elseif($fz>1024){
				return sprintf("%.2f",$fz/1024)."KB";
			}else{
				return $fz."B";
			}
		}
		function mtime($file){
			return date("Y-m-d H:i:s",filemtime($file));
		}
		function atime($file){
			return date("Y-m-d H:i:s",fileatime($file));
		}
		function ctime($file){
			return date("Y-m-d H:i:s",filectime($file));
			
		}
		function ex($string){
			$ar=explode(".", $string);
			$ex=array_pop($ar);
			return strtolower($ex);
		}
		function json($data){
			$json=json_encode($data);
			echo $json;
		}
		function str_encode($str){
        	$ar=array("GBK","BIG5","Shift_JIS");
        	$encode=mb_detect_encoding($str,$ar);
        	$string=iconv($encode,"utf-8",$str);//$encode为你之前文件编码格式
        	return $string;
		}
		function cat($data){
			$da=isset($data[1])?$data[1]:"";
			$this->lsdir($da);
			$this->file_array($this->dir);
			if(count($this->all)!=1){
				$re['status']=false;
				$re['type']="text";
				$re['con']="非文件";
			}else{
				$re['status']=true;
				$re['type']=$this->all[0]['type'];
				$re['con']=$this->dir;
			}
			$this->json($re);
		}
		function type($file){
			$ex=$this->ex($file);
			switch ($ex) {
				case 'png':
				case 'jpg':
				case 'gif':
				case 'bmp':
				case 'jpeg':
					return "image";
					break;
				case 'torrent':
					return "torrent";
					break;
				case 'mp3':
					return "mp3";
					break;
				case 'mp4':
				case 'ogg':
				case 'webm':
					return "video";
					break;
				case 'mkv':
				case 'rmvb':
				case 'avi':
				case 'mov':
					return "other_video";
					break;
				case 'xls':
				case 'xlsx':
				case 'doc':
				case 'docx':
				case 'ppt':
				case 'pptx':
					return "other";
					break;
				case 'pdf':
					return "pdf";
					break;
				case 'txt':
				case 'json':
				case 'xml':
				case 'html':
				case 'md':
					return "text";
					break;
				default:
					return "other";
					break;
			}
		}
	}
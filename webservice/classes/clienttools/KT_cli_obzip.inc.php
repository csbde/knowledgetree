<?php
/**
 * n_obzip
 *
 * @comment $Id: n_obzip.php 541 2008-12-11 07:35:22Z nemesarial $
 * @package Travel2
 * @author Nemesarial
 * @copyright 2008
 * @version $Id: n_obzip.php 541 2008-12-11 07:35:22Z nemesarial $
 * @access public
 * @todo -Upgrade this class to an output buffer handler
 */
class n_obzip{
  /**
   * n_obzip::start()
   *
   * Start the output buffer zipping process
   *
   * @return void
   */
	function start(){
		if(n_obzip::gzipSupport()){
			ob_start();
			ob_implicit_flush(0);
		}
	}

  /**
   * n_obzip::discard()
   *
   * Discard all the information that is in the output buffer and start again
   *
   * @return void
   */
	function discard(){
		ob_end_clean();
		ob_start();
	}

  /**
   * n_obzip::discontinue()
   *
   * Flush the buffer without compression
   *
   * @param bool $flush
   * @return void
   */
	function discontinue($flush=false){
		if(!defined('_gzip_discontinued'))define('_gzip_discontinued',true);
		if($flush)ob_end_flush();
	}

  /**
   * n_obzip::end()
   *
   * End the output buffer storage, compress it, and send it out to the browser
   *
   * @param bool $continue
   * @param bool $reportOverride
   * @return void
   */
	function end($continue=false,$reportOverride=false){
		global $_gzip_n_benchmark;
		if($_gzip_n_benchmark)$elapsed=$_gzip_n_benchmark->measure('pagegen');
		$do=(defined('_gzip_zip_encode')?_gzip_zip_encode:true);
		if($do){
			if($enc=n_obzip::gzipSupport()){
				$level=(defined('_gzip_zip_level')?_gzip_zip_level:9);
				$speed=(defined('_gzip_linespeed')?_gzip_linespeed:14336);
				if(defined('_gzip_reporting')||$reportOverride)if(constant('_gzip_reporting')==true ||$reportOverride){
					$cnt=ob_get_contents();
					$psize=strlen($cnt);
					$_gzip_n_benchmark->start('compress');
					$asize=strlen(gzcompress($cnt,$level));
					$ctime=$_gzip_n_benchmark->format('micro','compress');
					$save=sprintf("%01.2f",100-(($asize/$psize)*100));
					$tsave=sprintf("%01.2f",100-(((($asize/$speed)+$elapsed)/(($psize/$speed)+$elapsed))*100));
					$pfsize=sprintf("%01.2f",$psize/1024)."KB";
					$afsize=sprintf("%01.2f",$asize/1024)."KB";
					$sfsize=sprintf("%01.2f",($psize-$asize)/1024)."KB";
					echo "<span style=\"margin: 10px; padding: 5px; border: 1px solid #000000; background-color:#FDFFB9;font-family: Arial; font-size: 12px; color: #A10000; text-align:center; font-weight: normal; display: block\"><strong>Level {$level} GZIP (Page Generation: ".n_benchmark::format('s',$elapsed)." Compression Time: {$ctime})</strong><br> Download Size From <strong>{$pfsize}</strong> To <strong>{$afsize}</strong> Saving <strong>{$sfsize} ({$save}%</strong>)<br>Download Speed: From <strong>".n_benchmark::format('s',($psize/$speed)+$elapsed)."</strong> To <strong>".n_benchmark::format('s',($asize/$speed)+$elapsed)."</strong> Saving <strong>".n_benchmark::format('s',(($psize/$speed)+$elapsed)-(($asize/$speed)+$elapsed))." ({$tsave}%)</strong> assuming download speed at <strong>".sprintf("%01.2f",$speed/1024)."KB/s</strong></style>";
				}
				$cnt=ob_get_contents();
				ob_end_clean();
				$size = strlen($cnt);
				$crc=crc32($cnt);
				$cnt = gzcompress($cnt, $level);
				$cnt = substr($cnt, 0, strlen($cnt)-4);
				$out="\x1f\x8b\x08\x00\x00\x00\x00\x00".$cnt.pack("V",$crc).pack("V",$size);
				header("Content-encoding: ".$enc);header("Content-length: ".strlen($out));echo $out;
				ob_end_flush();
			}
			if(!$continue)exit;
		}else{
			if(defined('_gzip_reporting')||$reportOverride)if(constant('_gzip_reporting')==true ||$reportOverride){
				echo "($enc) Gzip Not Supported";
			}
			ob_end_flush();
			if(!$continue)exit;
		}
	}

  /**
   * n_obzip::gzipSupport()
   *
   * Test for gZip Support
   *
   * @return
   */
	function gzipSupport() {
		global  $_gzip_n_benchmark;
		if(defined('_gzip_reporting'))if(!$_gzip_n_benchmark->exists('pagegen'))$_gzip_n_benchmark->start('pagegen');
		@ini_set("zlib.output_compression","0");
		if(ini_get("zlib.output_compression")>0)return false;
		if(defined('_gzip_discontinued'))return false;
		if(!function_exists("gzcompress")){echo 'function not found'; return false;}
		if(defined("_gzip"))if(_gzip==false)return false;
		if(strpos($_SERVER['HTTP_ACCEPT_ENCODING'], 'x-gzip') !== false) return "x-gzip";
		if(strpos($_SERVER['HTTP_ACCEPT_ENCODING'],'gzip') !== false) return "gzip";
		return false;
	}
}
/**
 * n_benchmark
 *
 * @comment $Id: n_obzip.php 541 2008-12-11 07:35:22Z nemesarial $
 * @package Travel2
 * @author Nemesarial
 * @copyright 2008
 * @version $Id: n_obzip.php 541 2008-12-11 07:35:22Z nemesarial $
 * @access public
 * @todo upgrade this class to php 5+
 */

$_gzip_n_benchmark=new n_benchmark();
?>
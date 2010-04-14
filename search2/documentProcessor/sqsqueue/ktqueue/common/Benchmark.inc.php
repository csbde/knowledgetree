<?php 
/**
 * Simple benchmarking.
 *
 *
 * @package    KTLive
 * @author     Prince Mbekwa
 */
final class Benchmark {

	/**
	 * Set a benchmark start point.
	 * If a previous benchmark with the same name has not stopped yet, it will automatically be stopped first.
	 *
	 * @param   string  benchmark name
	 * @return  void
	 */
	public static function start(&$container,$name)
	{
		if(!is_array($container))$container=array();
		if ( ! isset($container[$name])){$container[$name] = array();}
		$cur=$container[$name];

		$cur['start']=microtime(true);
		$cur['stop']=false;
		$cur['memory_start']=self::memory_usage();
		$cur['memory_stop']=false;
		
		$container[$name]=$cur;		
		
//		return $container;
	}

	/**
	 * Set a benchmark stop point.
	 *
	 * @param   string  benchmark name
	 * @return  void
	 */
	public static function stop(&$container,$name)
	{
		if(!is_array($container))$container=array();
		if ( ! isset($container[$name])){$container[$name] = array();}
		$cur=$container[$name];

		if($cur['start'] && ($cur['stop']==false) && $cur['memory_start'] && ($cur['memory_stop']==false)){
			$cur['stop']=microtime(true);
			$cur['memory_stop']=self::memory_usage();
			$cur['time']+=$cur['stop']-$cur['start'];
			$cur['memory']+=$cur['memory_stop']-$cur['memory_start'];
			$cur['count']++;
		}
		$container[$name]=$cur;
	}

	/**
	 * Get the elapsed time between a start and stop.
	 *
	 * @param   string   benchmark name, TRUE for all
	 * @param   integer  number of decimal places to count to
	 * @return  array
	 */
	public static function get($container,$name, $decimals = 4)
	{
		if(!is_array($container))$container=array();
		$ret=array();

		if ($name === TRUE){
			$names=array_keys($container);
			foreach($names as $curName){
				$ret[$curName]=self::get($container,$curName,$decimals);
			}
		}else{
			if ( ! isset($container[$name])){$container[$name] = array();}
			$cur=$container[$name];
			$ret['time']=number_format(($cur['time']/$cur['count']),$decimals);
			$ret['memory']=number_format(($cur['memory']/$cur['count']),$decimals);
			$ret['count']=$cur['count'];
		}
		return $ret;
	}

	/**
	 * Returns the current memory usage. This is only possible if the
	 * memory_get_usage function is supported in PHP.
	 *
	 * @return  integer
	 */
	private static function memory_usage()
	{
		return function_exists('memory_get_usage') ? memory_get_usage() : 0;
	}

} // End Benchmark

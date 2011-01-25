<?php
/**
 * This memcache helper assumes the old memcache library NOT memcached
 * @author mark
 *
 */

if(!class_exists('ktMemcachePool')) { require_once('ktMemcachePool.helper.php'); }

class MemCacheUtil extends ktMemcachePool{}

?>

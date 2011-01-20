<?php
/**
 * This memcache helper assumes the old memcache library NOT memcached
 * @author mark
 *
 */

require_once(KT_DIR . '/lib/memcache/ktMemcachePool.helper.php');

class MemCacheUtil extends ktMemcachePool{}

?>
<?php

require_once (HOME . '/common/DbObject.inc.php');
require_once (HOME . '/common/lib.static.php');

class PersistenceManager {
    
    private static $dbObject;
    
    static public function connect()
    {
        // set up simple db access object
        self::$dbObject = new dbObject();
    }

    static public function persistComplexEvent($eventObject)
    {
        $result = self::$dbObject->insert($eventObject->id, lib::sSerialize($eventObject));
    }

    static public function getPersistedEvent($eventId)
    {
        return self::$dbObject->select($eventId);
    }

    static public function deletePersistedEvent($eventId)
    {
        $result = self::$dbObject->delete($eventId);
    }
    
    static public function lockPersistenceDatabase()
    {
        self::$dbObject->lock();
    }
    
    static public function unlockPersistenceDatabase()
    {
        self::$dbObject->unlock();
    }
    
}

?>
<?php 
/**
 * Process queuing/execution class. Allows an unlimited number of callbacks
 * to be added to 'events'. Events can be run multiple times, and can also
 * process event-specific data. 
 * 
 * To add an event to the Handler
 * @example Usage EventHandler::add('sqsqueue.execute',array($this-pdflisterner,'convert'));
 *
 * To execute a complex event / a series of events
 * @example Usage EventHandler::run('user_settings_display')
 * 
 */
require_once(realpath(dirname(__FILE__)) . '/../config/config.inc.php');
require_once(HOME . '/common/ConfigManager.inc.php');

final class EventHandler {

    // Event callbacks
    private static $events = array();

    // Cache of events that have been run
    private static $has_run = array();

    // Data that can be processed during events
    public static $data;

    // Include paths
    private static $include_paths = null;

    /**
	 * Add a callback to an event queue.
	 *
	 * @param   string   event name
	 * @param   array    http://php.net/callback
	 * @return  boolean
	 */
    public static function add($name, $callback)
    {
        if ( ! isset(self::$events[$name]))
        {
            // Create an empty event if it is not yet defined
            self::$events[$name] = array();
        }
        elseif (in_array($callback, self::$events[$name], TRUE))
        {
            // The event already exists
            return FALSE;
        }

        // Add the event
        self::$events[$name][] = $callback;

        return TRUE;
    }
    
    /**
	 * Get all callbacks for an event.
	 *
	 * @param   string  event name
	 * @return  array
	 */
    public static function get($name)
    {
        return empty(self::$events[$name]) ? array() : self::$events[$name];
    }

    /**
	 * Clear some or all callbacks from an event.
	 *
	 * @param   string  event name
	 * @param   array   specific callback to remove, FALSE for all callbacks
	 * @return  void
	 */
    public static function clear($name, $callback = FALSE)
    {
        if ($callback === FALSE)
        {
            self::$events[$name] = array();
        }
        elseif (isset(self::$events[$name]))
        {
            // Loop through each of the event callbacks and compare it to the
            // callback requested for removal. The callback is removed if it
            // matches.
            foreach (self::$events[$name] as $i => $event_callback)
            {
                if ($callback === $event_callback)
                {
                    unset(self::$events[$name][$i]);
                }
            }
        }
    }

    /**
	 * Execute all of the callbacks attached to an event.
	 *
	 * @param   string   event name
	 * @param   array    data can be processed as Event::$data by the callbacks
	 * 
	 * @return  void
	 */
    public static function run($name, & $data = NULL,$event = NULL)
    {
        self::set_include_paths();
        if ( ! empty(self::$events[$name]) AND empty(self::$has_run))
        {
            // So callbacks can access Event::$data
            self::$data =& $data;
            $callbacks  =  self::get($name);
            try {
                foreach ($callbacks as $callback)
                {
                    $options = explode(".",$callback);
                    $class = $options[0];
                    print ("$class\n");
                    $function = $options[1];
                    $file = HOME . '/' . self::$include_paths[$class] . "/$class.inc.php";
                    require_once ("{$file}");
                    if (method_exists($class, $function)) {
                        try {
                            $result = call_user_func_array(array($class, $function), self::$data);
                            if (! empty($event)) {
                                $event->returnData = $result;
                                // Add the event
                                self::$events[$name][] = $event;

                            }
                        }catch (Exception $e){
                            if (! empty($event)) {
                                $event->error($e);
                                // Add the event
                                self::$events[$name][] = $event;
                            }

                        }

                    }
                    //call_user_func($callback);
                }

                // Do this to prevent data from getting 'stuck'
                $clear_data = '';
                self::$data =& $clear_data;

            }catch (Exception $e){
                throw new Exception($e->getMessage());
            }

            return $result;
        }

        // The event has been run!
        self::$has_run[$name] = $name;
    }

    /**
	 * Runs an event immediately without adding it to the event batch
	 * Updates the event which is returned through arg by reference
	 *
	 * @param string $name
	 * @param string $data
	 * @param object $event
	 */
    public static function run_event($name, $data, &$event)
    {
        self::set_include_paths();
        try {
            $options = explode(".",$name);
            $class = $options[0];
            $function = $options[1];
            $file = HOME . '/' . self::$include_paths[$class] . "/$class.inc.php";
            require_once ($file);
            if (method_exists($class, $function)) {
                $result = call_user_func_array(array($class, $function), $data);
                $event->resultData = $result;
            }
        }catch (Exception $e){
            $event->error($e);
        }
    }

    /**
	 * Check if a given event has been run.
	 *
	 * @param   string   event name
	 * @return  boolean
	 */
    public static function has_run($name)
    {
        return isset(self::$has_run[$name]);
    }

    /**
     * Checks whether the include paths are already set and sets them if not
     * The include path configuration file declares each class and its location
     * relative to the root directory
     * 
     * If the $refresh argument is set to true, then the config will be re-read
     * regardless of previous readings.  Default is to use the already loaded values
     *
     * @param boolean $refresh Whether to force a re-read of the configuration
     */
    public static function set_include_paths($refresh = false)
    {
        if (empty(self::$include_paths) || $refresh)
        {
            ConfigManager::load(HOME . '/config/processors.ini');
            if (ConfigManager::error()) {
                // log error and die...
                die (ConfigManager::getErrorMessage());
            }

            // load amazon authentication information
            self::$include_paths = ConfigManager::getSection('Processor Locations');
        }
    }

} // End EventHandler
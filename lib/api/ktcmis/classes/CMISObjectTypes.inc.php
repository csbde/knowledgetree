<?php

class CMISObjectTypes {

    /**
     *
     * @var array $objects The array of supported object types
     */
    private $objects;
    private $path;

    function __construct()
    {
        // TODO Set path dynamically instead of statically?  i.e. via config as with search
        $this->path = CMIS_DIR . '/objecttypes';
        $this->registerObjects();
    }

    function registerObjects()
    {
        // read object types directory to find supported object types
        $this->objects = array();

        // TODO check search code for correctPath function and see if similar is needed here
    	$dir = opendir($this->path);
		while (($file = readdir($dir)) !== false)
		{
			if (substr($file,-14) == 'Object.inc.php')
			{
                // TODO check what if anything here is useful and reinstate
//				require_once($this->path . '/' . $file);
//				$class = substr($file, 0, -8);
//
//				if (!class_exists($class))
//				{
//					continue;
//				}
//
//				$field = new $class;
//				if (is_null($field) || !($field instanceof FieldExpr))
//				{
//					continue;
//				}

				$this->objects[] = str_replace('CMIS', '', substr($file,0,-14));
			}
        }
        
        closedir($dir);
    }

    /**
     * return a list of all supported objects
     */
    function getObjectTypes()
    {
        return $this->objects;
    }
}

?>

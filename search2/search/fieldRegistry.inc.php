<?php

require_once('search/expr.inc.php');

class ResolutionException extends Exception {}


/**
 * This is the primary registry for fields.
 *
 */
class ExprFieldRegistry
{
	/**
	 * Stores all registered fields.
	 *
	 * @var array
	 */
    private $fields;

	/**
	 * Stores all registered aliases.
	 *
	 * @var array
	 */
    private $alias;

    /**
     * Path to location of class definitions
     *
     * @var string
     */
    private $path;

    private $metadata;

    private $display;

    /**
     * Initialise the registry.
     * This is private and class must be obtained via the get() method.
     *
     * @access private
     *
     */
    private function __construct()
    {
    	$this->fields = array();
    	$this->alias = array();
    	$this->metadata = array();
    	$this->display=array();

    	$config = KTConfig::getSingleton();

    	$this->path = $config->get('search/fieldsPath');
    }

    /**
     * Retuns a singleton to the class.
     *
     * @return ExprFieldRegistry
     */
    public static function getRegistry()
    {
    	static $singleton = null;

    	if (is_null($singleton))
    	{
			$singleton = new ExprFieldRegistry();
			$singleton->registerFields();
    	}

    	return $singleton;
    }

    /**
     * Add a field to the registry.
     *
     * @param FieldExpr $field
     */
    private function registerField($field)
    {
        assert(!is_null($field));
        $classname = strtolower(get_class($field));
        $alias = strtolower($field->getAlias());

        if (array_key_exists($classname, $this->fields) || array_key_exists($alias, $this->alias))
        {
			throw new ResolutionException("Class $classname with alias $alias already registered.");
        }

        $this->fields[$classname] = $field;
        $this->alias[$alias] = $field;

        if ($field instanceof MetadataField )
        {
        	$fieldsetn = $field->getFieldSet();
        	$fieldn= $field->getField();
        	$this->metadata[$fieldsetn][$fieldn] = $field;
        	$this->display[] = "[\"$fieldsetn\"][\"$fieldn\"]";
        }
        else
        {
			$this->display[] = $field->getAlias();
        }

    }

    public function resolveAlias($alias)
    {
    	return $this->getField($alias);
    }

    public function resolveMetadataField($fieldset, $field)
    {
    	if (!array_key_exists($fieldset,$this->metadata))
    	{
    		throw new ResolutionException("Metadata class for fieldset '$fieldset' and field '$field' not found.");
    	}
    	if (!array_key_exists($field,$this->metadata[$fieldset]))
    	{
    		throw new ResolutionException("Metadata class for fieldset '$fieldset' and field '$field' not found.");
    	}
    	return $this->metadata[$fieldset][$field];
    }


    /**
     * A static method to lookup a field by fieldname.
     *
     * @param string $fieldname
     * @return unknown
     */
    public static function lookupField($fieldname)
    {
    	$registry = ExprFieldRegistry::get();
    	return $registry->getField($fieldname);
    }

    /**
     * Returns a field from the registry.
     *
     * @param string $fieldname
     * @return ExprField
     */
    public function getField($fieldname)
    {
    	$fieldname = strtolower($fieldname);
    	if (array_key_exists($fieldname, $this->fields))
        {
        	return $this->fields[$fieldname];
        }
    	if (array_key_exists($fieldname, $this->alias))
        {
        	return $this->alias[$fieldname];
        }
         throw new ResolutionException('Field not found: ' . $fieldname);
    }

    public function getAliasNames()
    {
    	return $this->display;
    }

    /**
     * Load all fields into the registry
     *
     */
    public function registerFields()
    {
    	$this->fields = array();

    	$dir = opendir($this->path);
		while (($file = readdir($dir)) !== false)
		{
			if (substr($file,-13) == 'Field.inc.php')
			{
				require_once($this->path . '/' . $file);
				$class = substr($file, 0, -8);

				if (!class_exists($class))
				{
					continue;
				}

				$field = new $class;
				if (is_null($field) || !($field instanceof FieldExpr))
				{
					continue;
				}

				$this->registerField($field);
			}
        }
        closedir($dir);

        $this->registerMetdataFields();
    }

    /**
     * Registers metdata fields in system.
     *
     */
    private function registerMetdataFields()
    {
		$sql = "SELECT
					fs.name as fieldset, f.name as field, fs.id as fsid, f.id as fid
				FROM
					fieldsets fs
					INNER JOIN document_fields f ON f.parent_fieldset=fs.id
				WHERE
					fs.disabled=0";
		$result = DBUtil::getResultArray($sql);

		foreach($result as $record)
		{
			$fieldset = $record['fieldset'];
			$field = $record['field'];
			$fieldsetid = $record['fsid'];
			$fieldid = $record['fid'];
			$classname = "MetadataField$fieldid";

			$classdefn = "
				class $classname extends MetadataField
				{
					public function __construct()
					{
						parent::__construct('$fieldset','$field',$fieldsetid, $fieldid);
					}
				}";
			eval($classdefn);

			$field = new $classname;
			$this->registerField($field);
		}
    }

    public function getFields()
    {
    	$result = array();
    	foreach($this->fields as $field)
    	{
    		if ($field instanceof MetadataField)
    		{
    			continue;
    		}
    		$result[] = $field;
    	}
    	return $result;
    }

}

?>
<?php
	
	namespace Products;
	
	use Database\QueryException;
	use DatabaseObject\Generator;
	use DatabaseObject\Property\Property;
	use DatabaseObject\Property\LinkToProperty;
	use DatabaseObject\Property\LinkFromMultipleProperty;
	use DatabaseObject\Column;
	
	use DatabaseObject\FormElement\Text;
	use DatabaseObject\FormElement\GeneratorElement;
	use DatabaseObject\FormElement\Checkbox;
	
	/**
	 * A group of options
	 * @author	Sarah Bousfield <sarah@activatedesign.co.nz>
	 */
	class OptionGroup extends Generator
	{
		const TABLE = 'option_groups';
		const ID_FIELD = 'option_group_id';
		const SINGULAR = 'group';
		const PLURAL = 'groups';
		const HAS_ACTIVE = true;
		const HAS_POSITION = true;
		const LABEL_PROPERTY = 'name';
		const PARENT_PROPERTY = 'product';
		
		public $name = '';
		public $product = null;
		public $active = true;
		
		/** @var ProductOption[] */
		public $options = null;
		
		/** @var ProductOption[] */
		protected $_activeOptions = null;
		
		/**
		 * Gets the array of Properties that determine how this Object interacts with the database
		 */
		public static function properties()
		{
			parent::properties();
			
			static::addProperty(new Property('name', 'name', 'string'));
			static::addProperty(new LinkToProperty('product', 'product_id', Product::class));
			static::addProperty(new LinkFromMultipleProperty('options', ProductOption::class, 'group'));
		}
		
		/**
		 * Sets the array of Columns that are displayed to the user for this object type
		 */
		public static function columns()
		{
			static::addColumn(new Column('Name', 'name'));
			
			parent::columns();
		}
		
		/**
		 * Sets the Form Elements for this object
		 */
		public function formElements()
		{
			parent::formElements();
			
			$this->addFormElement(new Text('name', 'Name'));
			$this->addFormElement(new GeneratorElement('options', 'Option'));
		}
		
		/**
		 * Gets the active options for this group
		 * @return	ProductOption[]		The active options
		 */
		public function get_activeOptions()
		{
			if ($this->_activeOptions === null)
			{
				$this->_activeOptions = ProductOption::loadAllForMultiple(['active' => true, 'group' => $this]);
			}
			
			return $this->_activeOptions;
		}
		
		/**
		 * Deletes the Entity from the database
		 * @param	bool	$startTransaction	Whether this delete should start a new transaction
		 * @throws    QueryException    If the query fails
		 */
		public function delete($startTransaction = true)
		{
			foreach ($this->options as $option)
			{
				$option->delete($startTransaction);
			}
			
			parent::delete();
		}
	}

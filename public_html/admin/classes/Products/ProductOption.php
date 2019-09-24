<?php
	
	namespace Products;
	
	use DatabaseObject\Generator;
	use DatabaseObject\Property\Property;
	use DatabaseObject\Property\LinkToProperty;
	use DatabaseObject\Column;
	
	use DatabaseObject\FormElement\Text;
	use DatabaseObject\FormElement\Hidden;
	
	/**
	 * A single option in a group of options
	 * @author	Sarah Bousfield <sarah@activatedesign.co.nz>
	 */
	class ProductOption extends Generator
	{
		const TABLE = 'product_options';
		const ID_FIELD = 'product_option_id';
		const SINGULAR = 'option';
		const PLURAL = 'options';
		const HAS_ACTIVE = true;
		const HAS_POSITION = true;
		const LABEL_PROPERTY = 'name';
		const PARENT_PROPERTY = 'group';
		
		public $name = '';
		public $group = null;
		public $active = true;
		
		/**
		 * Gets the array of Properties that determine how this Object interacts with the database
		 */
		public static function properties()
		{
			parent::properties();
			
			static::addProperty(new Property('name', 'name', 'string'));
			static::addProperty(new LinkToProperty('group', 'group_id', OptionGroup::class));
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
			
			$this->addFormElement((new Text('name', 'Name'))->setClasses(''));
		}
	}

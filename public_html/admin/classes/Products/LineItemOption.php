<?php
	namespace Products;
	
	use DatabaseObject\Entity;
	use DatabaseObject\Property\LinkToProperty;
	use DatabaseObject\Property\Property;
	
	/**
	 * Contains options for a single line item
	 * @author	Callum Muir <callum@activatedesign.co.nz>
	 */
	class LineItemOption extends Entity
	{
		const TABLE = "line_item_options";
		const ID_FIELD = "line_item_option_id";
		const PARENT_PROPERTY = "lineItem";
		
		public $optionGroupName = null;
		public $optionName = null;
		
		/** @var ProductLineItem */
		public $lineItem = null;
		
		/** @var OptionGroup */
		public $optionGroup = null;
		
		/** @var ProductOption */
		public $option = null;
		
		/**
		 * Gets the array of Properties that determine how this Object interacts with the database
		 */
		protected static function properties()
		{
			parent::properties();
			
			static::addProperty(new Property("optionGroupName", "option_group_name", "string"));
			static::addProperty(new Property("optionName", "option_name", "string"));
			static::addProperty(new LinkToProperty("lineItem", "line_item_id", ProductLineItem::class));
			static::addProperty(new LinkToProperty("optionGroup", "option_group_id", OptionGroup::class));
			static::addProperty(new LinkToProperty("option", "option_id", ProductOption::class));
		}
		
		/**
		 * Sets the option group for this line item option
		 * @param	OptionGroup|int		$optionGroup	The option group to set
		 */
		public function set_optionGroup($optionGroup)
		{
			$this->setProperty("optionGroup", $optionGroup);
			$this->optionGroupName = $this->optionGroup->name;
		}
		
		/**
		 * Sets the option for this line item option
		 * @param	ProductOption|int	$option		The option to set
		 */
		public function set_option($option)
		{
			$this->setProperty("option", $option);
			$this->optionName = $this->option->name;
		}
	}
<?php
	namespace Products;
	
	use Database\QueryException;
	use DatabaseObject\Property\LinkFromMultipleProperty;
	use Orders\LineItem;
	
	/**
	 * A Product Line Item contains references to products
	 * @author	Callum Muir <callum@activatedesign.co.nz>
	 */
	class ProductLineItem extends LineItem
	{
		/** @var LineItemOption[] */
		public $options = null;
		
		/**
		 * Gets the array of Properties that determine how this Object interacts with the database
		 */
		protected static function properties()
		{
			parent::properties();
			
			static::addProperty(new LinkFromMultipleProperty("options", LineItemOption::class, "lineItem"));
		}
		
		/**
		 * Deletes the Entity from the database
		 * @param	bool	$startTransaction	Whether this delete should start a new transaction
		 * @throws    QueryException    If the query fails
		 */
		public function delete($startTransaction = true)
		{
			foreach($this->options as $option)
			{
				$option->delete($startTransaction);
			}
			
			parent::delete($startTransaction);
		}
		
		/**
		 * Specifies what data should be serialised when json_encode is called
		 * @return    array    Name/data pairs for the data in this object
		 */
		public function jsonSerialize()
		{
			$options = [];
			
			foreach($this->options as $option)
			{
				$options[] = $option->optionGroupName . ": " . $option->optionName;
			}
			
			$json = parent::jsonSerialize();
			$json["options"] = implode($options, "<br />\n");
			
			return $json;
		}
	}
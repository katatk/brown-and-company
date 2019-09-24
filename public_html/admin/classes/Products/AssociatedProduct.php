<?php
	namespace Products;

	use DatabaseObject\FormElement\Hidden;
	use DatabaseObject\FormElement\Select;
	use DatabaseObject\Generator;
	use DatabaseObject\Property\LinkToProperty;
	use DatabaseObject\Property\Property;

	/**
	 * Associates a Product with another Product
	 * @author	Callum Muir <callum@activatedesign.co.nz>
	 */
	class AssociatedProduct extends Generator
	{
		const TABLE = "associated_products";
		const ID_FIELD = "associated_product_id";
		const PARENT_PROPERTY = "from";
		const SINGULAR = "associated product";
		const PLURAL = "associated products";
		const LABEL_PROPERTY = "toName";
		const HAS_POSITION = true;

		public $from;
		
		/** @var ProductCategory */
		public $to;
		public $toName;

		/**
		 * @inheritdoc
		 */
		protected static function properties()
		{
			parent::properties();

			static::addProperty(new LinkToProperty("from", "from_id", Product::class));
			static::addProperty(new LinkToProperty("to", "to_id", ProductCategory::class));
			static::addProperty(new Property("toName"));
		}

		/**
		 * @inheritdoc
		 */
		protected function formElements()
		{
			parent::formElements();

			$this->addFormElement(new Hidden("from", ""));
			$this->addFormElement(new Hidden("toName", ""));
			$this->addFormElement(new Select("to", "Product", Category::getCategoryTree()));
		}
		
		/**
		 * Gets the name of the associated product
		 * @return	string	The name of the product
		 */
		public function get_toName()
		{
			return $this->to->product->name;
		}
	}

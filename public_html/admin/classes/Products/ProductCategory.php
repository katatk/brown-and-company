<?php
	namespace Products;

	use DatabaseObject\Generator;
	use DatabaseObject\Property\LinkToProperty;
	use DatabaseObject\Property\Property;
	use DatabaseObject\Column;

	/**
	 * Linking class between Products and Categories
	 * @author	Callum Muir
	 */
	class ProductCategory extends Generator
	{
		const ID_FIELD = "product_category_id";
		const TABLE = "product_categories";
		const SINGULAR = 'product';
		const PLURAL = 'products';
		const HAS_POSITION = true;

		const PARENT_PROPERTY = 'category';

		/** @var Product */
		public $product = null;

		/** @var Category */
		public $category = null;

		private $_product = null;

		/**
		 * Gets the array of Properties that determine how this Product Category interacts with the database
		 */
		protected static function properties()
		{
			parent::properties();

			static::addProperty(new Property("position", "position", "int"));
			static::addProperty(new LinkToProperty("product", "product_id", Product::class));
			static::addProperty(new LinkToProperty("category", "category_id", Category::class));
		}
		
		/**
		 * Sets the array of Columns that are displayed to the user for this object type
		 */
		protected static function columns()
		{
			static::addColumn(new Column('Name',function(ProductCategory $productCategory)
			{
				return $productCategory->product->getIdentifierLink();
			}));
			
			static::addColumn(new Column("Featured", function(ProductCategory $productCategory)
			{
				return $productCategory->product->getToggle("featured");
			}));
			
			static::removeColumn('Active');
			
			static::addColumn(new Column('Active', function(ProductCategory $productCategory)
			{
				return $productCategory->product->getActiveToggle();
			}));
			
			parent::columns();
		}
		
		/**
		 * Gets name => id pairs for all the categories inside of a category
		 * @param	Category	$root	The root category
		 * @return	Category[]			All the categories inside that category
		 */
		public static function getProductCategoryArray(Category $root)
		{
			$productCategories = [];

			foreach(ProductCategory::loadAllFor("category", $root) as $productCategory)
			{
				$productCategories[$productCategory->product->name] = $productCategory->id;
			}

			return $productCategories;
		}
		
		/**
		 * Creates a link to add a new item of this type
		 * @param   Generator $generator	Optionally, the Generator that this is a child of
		 * @return	string	The HTML for the link
		 */
		public static function addLink(Generator $generator = null)
		{
			return Product::addLink($generator);
		}

		/**
		 * get the path to edit this Generator in the admin panel
		 *
		 * @return string
		 */
		public function getEditLink()
		{
			return $this->product->getEditLink();
		}
		
		/**
		 * Creates a link to the delete page for this Generator
		 * @return	string	The HTML for the delete link
		 */
		public function generateDeleteLinkHtml()
		{
			return $this->product->generateDeleteLinkHtml();
		}

		/**
		 * Gets the Product belonging to this ProductCategory
		 * @return	Product		The Product
		 */
		public function get_product()
		{
			if($this->_product === null)
			{
				$this->_product = $this->getProperty("product");
				$this->_product->category = $this->category;
			}

			return $this->_product;
		}
	}

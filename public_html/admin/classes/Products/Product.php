<?php
	namespace Products;

	use Database\Database;
	use Database\QueryException;
	use DatabaseObject\Entity;
	use DatabaseObject\Generator;
	use DatabaseObject\Property\LinkFromMultipleProperty;
	use DatabaseObject\Property\Property;

	use DatabaseObject\FormElement\Editor;
	use DatabaseObject\FormElement\Text;
	use DatabaseObject\FormElement\Textarea;
	use DatabaseObject\FormElement\MultipleCheckbox;
	use DatabaseObject\FormElement\GridElement;
	use DatabaseObject\FormElement\GeneratorElement;
	use Exception;
	use Files\Image;
	use Orders\LineItem;
	use Orders\LineItemGenerator;
	use Pages\Page;

	/**
	 * Handles products using the Database Object system
	 * @author	Callum Muir
	 */
	class Product extends Generator implements LineItemGenerator
	{
		const TABLE = "products";
		const ID_FIELD = "product_id";
		const SINGULAR = 'product';
		const PLURAL = 'products';
		const HAS_ACTIVE = true;
		const USE_TABS = true;
		const LABEL_PROPERTY = "name";
		const PARENT_PROPERTY = "parent";
		const LINK_METHOD = "get_path";
		const SLUG_PROPERTY = 'name';
		const PATH_PARENT = 'categories';
		const SLUG_TAB = "Content";

		private static $productsPage = null;

		public $name = "";
		public $code = "";
		public $title = "";
		public $description = "";
		public $summary = "";
		public $price = 0;
		public $salePrice = 0;
		public $onSale = false;
		public $numberInStock = 0;
		public $active = false;
		public $featured = false;
		public $metaTitle = "";
		public $metaDescription = "";

		public $path = "";

		/** @var ProductCategory[] */
		public $productCategories = null;

		/** @var OptionGroup[] */
		public $optionGroups = null;

		public $categoryIds = null;
		private $_categoryIds = null;

		/** @var ProductImage[] */
		public $images = null;

		/** @var Category */
		public $category = null;

		/** @var AssociatedProduct[] */
		public $associatedProductCategories = null;
		public $parent = null;

		/** @var ProductImage[] */
		public $activeImages = [];

		public $_activeImages = null;

		/** @var Category[] */
		public $categories = null;

		/**
		 * Gets the array of Properties that determine how this Product interacts with the database
		 */
		protected static function properties()
		{
			parent::properties();

			static::addProperty(new Property("name", "name", "string"));
			static::addProperty(new Property("code", "code", "string"));
			static::addProperty(new Property('title', 'title', 'string'));
			static::addProperty(new Property("summary", "summary", "string"));
			static::addProperty(new Property("description", "description", "html"));
			static::addProperty(new Property("price", "price", "float"));
			static::addProperty(new Property("salePrice", "sale_price", "float"));
			static::addProperty(new Property("onSale", "on_sale", "bool"));
			static::addProperty(new Property("numberInStock", "number_in_stock", "int"));
			static::addProperty(new Property("featured", "featured", "bool"));
			static::addProperty(new Property("metaTitle", "meta_title", "string"));
			static::addProperty(new Property("metaDescription", "meta_description", "string"));
			static::addProperty(new Property("categoryIds"));
			static::addProperty(new LinkFromMultipleProperty("productCategories", ProductCategory::class, "product", ["position" => true]));
			static::addProperty(new LinkFromMultipleProperty("images", ProductImage::class, "product"));
			static::addProperty(new Property("parent"));
			static::addProperty(new LinkFromMultipleProperty("associatedProductCategories", AssociatedProduct::class, "from"));
			static::addProperty(new LinkFromMultipleProperty("optionGroups", OptionGroup::class, "product"));
			static::addProperty(new Property("activeImages"));
			static::addProperty(new Property("pathParents"));
			static::addProperty(new Property("categories"));
			static::addProperty(new Property("path"));
		}

		/**
		 * Gets all the Products that belong to a Category
		 * @param	Category	$category			The Category to retrieve Products for
		 * @param	bool		$includeInactive	Whether to include inactive Products
		 * @return				static[]			All the Products that belong to that Category
		 */
		public static function loadAllForCategory(Category $category, $includeInactive = false)
		{
			$query = "SELECT ~PROPERTIES "
				   . "FROM ~TABLE "
				   . "INNER JOIN ~ProductCategory "
				   . "ON ~ProductCategory.~product = ~id "
				   . "WHERE ~ProductCategory.~category = ? ";

			if(!$includeInactive)
			{
				$query .= "AND ~active = TRUE ";
			}

			$query .= "ORDER BY ~ProductCategory.~position";

			$products = static::makeMany($query, [$category->id]);

			foreach($products as $product)
			{
				$product->category = $category;
			}

			return $products;
		}

		/**
		 * Creates a link to return to the table page from the form pages
		 * @param    Generator $generator	A Review that the return link returns from
		 * @return	string	Path to the page
		 */
		public static function returnLink(Generator $generator = null)
		{
			return Category::returnLink($generator);
		}

		/**
		 * Gets all uncategorised Products
		 * @return	static[]	Said Products
		 */
		public static function loadAllUncategorised()
		{
			$query = "SELECT ~PROPERTIES "
				   . "FROM ~TABLE "
				   . "WHERE ~id NOT IN "
				   . "("
				   .	"SELECT ~ProductCategory.~product "
				   .	"FROM ~ProductCategory"
				   . ")";

			return static::makeMany($query);
		}

		/**
		 * Loads an object that matches a slug (case insensitive)
		 * @param	string	$slug			The slug to match against
		 * @param	Entity	$parent			The parent of the object matching that slug
		 * @param 	bool 	$checkActive	Do we care about active or not
		 * @return	static					The matching object
		 */
		public static function loadForSlug($slug, Entity $parent = null, $checkActive = true)
		{
			if($parent === null)
			{
				// All products must belong to a category
				return static::makeNull();
			}

			$query = "SELECT ~PROPERTIES "
				   . "FROM ~TABLE "
				   . "WHERE ~slug = ? "
				   . ($checkActive ? "AND ~active = TRUE " : "")
				   . "AND ~id IN "
				   . "("
				   .	"SELECT ~ProductCategory.~product "
				   .	"FROM ~ProductCategory "
				   .	"WHERE ~ProductCategory.~category = ? "
				   . ") "
				   . "LIMIT 1";

			return static::makeOne($query, [$slug, $parent->id]);
		}

		/**
		 * Gets all the featured products
		 * @param	int			$limit	The limit to place on the number of returned products
		 * @return	static[]			All the featured products, ordered randomly
		 */
		public static function getFeatured($limit = PHP_INT_MAX)
		{
			$query = "SELECT ~PROPERTIES "
				. "FROM ~TABLE "
				. "WHERE ~featured = TRUE "
				. "AND ~active = TRUE "
				. "ORDER BY RAND() "
				. "LIMIT ?";

			return static::makeMany($query, [$limit]);
		}

		/**
		 * Sets the Form Elements for this object
		 */
		protected function formElements()
		{
			parent::formElements();

			$this->removeFormElement("name");
			$this->addFormElement((new Text('name', 'Name'))->setClasses('half first'), 'Content', 'slug');
			$this->addFormElement((new Text('title', 'Title <span>(Optional)</span>'))->setClasses('half'), 'Content', 'slug');
			$this->addFormElement((new Text('price', 'Price'))->setClasses('currency'), 'Content');
			$this->addFormElement(new Editor("description", "Description"), "Content");

			$this->addFormElement(new Text('metaTitle', 'Meta Title'), 'Metadata');
			$this->addFormElement(new Textarea('metaDescription', 'Meta Description'), 'Metadata');

			$this->addFormElement(new MultipleCheckbox('categoryIds', 'Category', Category::getCategoryOptionsForProduct(), $this->getCategories()), 'Categories');

			/** @noinspection PhpParamsInspection */
			$this->addFormElement(new GridElement('images', 'Images'), "Images");

			if (MODULE_PRODUCTS_ASSOCIATED)
			{
				$this->addFormElement(new GeneratorElement("associatedProductCategories", "Associated Product"), "Associated Product");
			}
		}

		/**
		 * Gets the categories this product belongs to
		 * @return	Category[]	The categories
		 */
		public function getCategories()
		{
			$categories = [];

			foreach($this->productCategories as $productCategory)
			{
				$categories[] = $productCategory->category->id;
			}

			return $categories;
		}

		/**
		* Gets the text to use in a heading for this product
		*
		* @return String
		*/
		public function getTitle()
		{
			return $this->title !== '' ? $this->title : $this->name;
		}

		/**
		 * Gets the active images for this product
		 * @return	ProductImage[]	The active images
		 */
		public function get_activeImages()
		{
			if ($this->_activeImages === null)
			{
				$this->_activeImages = ProductImage::loadAllForMultiple(
				[
					'product' => $this->id,
					'active' => true
				]);
			}

			return $this->_activeImages;
		}

		/**
		 * take multiple uploaded images and assign them to this Page
		 * @param  \Files\Image[][] $images
		 */
		public function set_imageUpload($images = [])
		{
			if(!$this->id && !empty($images))
			{
				// this page hasn't been saved yet but we need an id for the gallery items
				$this->save();
			}
			//This caches the productImages so that when we iterate over the productImages with the form elements, the items we're adding now will not be included. Magic.
			$this->images;
			$count = count($images);
			foreach($images as $imagePair)
			{
				$productImage = new ProductImage;
				$productImage->image = $imagePair[0];
				if(isset($imagePair[1]))
				{
					$productImage->thumbnail = $imagePair[1];
				}
				else
				{
					$productImage->thumbnail = $imagePair[0];
				}
				$productImage->product = $this;
				$productImage->save();
			}
			if($count > 0)
			{
				addMessage($count . ' images were added.');
			}
		}

		/**
		 * Sets the categories this product belongs to
		 * @param	int[]	$categoryIds	The identifiers for all the categories
		 */
		public function set_categoryIds($categoryIds)
		{
			$ids = [];

			foreach($categoryIds as $categoryId)
			{
				$ids[] = (int) $categoryId;
			}

			$this->_categoryIds = $ids;
		}

		/**
		 * Saves this Generator
		 * @param	bool	$startTransaction	Whether this save should start a new mySQL transaction
		 */
		public function save($startTransaction = true)
		{
			parent::save($startTransaction);

			if($this->_categoryIds === null)
			{
				return;
			}

			//Update categories

			$oldIds = [];
			$categoryIds = $this->_categoryIds;
			$this->_categoryIds = null;
			$productCategories = $this->productCategories;

			foreach($productCategories as $currentObject)
			{
				$oldIds[] = $currentObject->category->id;

				if(!in_array($currentObject->category->id, $categoryIds))
				{
					$currentObject->delete();
				}
			}

			foreach($categoryIds as $newId)
			{
				if(!in_array($newId, $oldIds))
				{
					$category = Category::load($newId);

					$productCategory = new ProductCategory();
					$productCategory->category = $category;
					$productCategory->product = $this;
					$productCategory->save($startTransaction);
					$productCategories[] = $productCategory;
				}
			}

			$this->productCategories = $productCategories;

			//update slug now we have categories, and save to database without calling save
			$this->setSlug($this->slug);
			$query = "UPDATE ~TABLE SET ~slug = ? WHERE ~id = ? ";
			Database::query(static::processQuery($query), [$this->slug, $this->id]);
		}

		/**
		 * Gets a single parent category for this product
		 * @return	Category	The single parent category
		 */
		public function get_parent()
		{
			foreach($this->productCategories as $productCategory)
			{
				return $productCategory->category;
			}

			return Category::makeNull();
		}

		/**
		 * Sets the parents for this product to be a single category
		 * @param	int|Category	$category	The category to set
		 */
		public function set_parent($category)
		{
			$this->productCategories = $this->getProperty("productCategories");

			if($category instanceof Entity)
			{
				$category = $category->id;
			}

			$productCategory = new ProductCategory;
			$productCategory->product = $this;
			$productCategory->category = $category;

			$this->productCategories = [$productCategory];
		}

		/**
		 * Deletes the Entity from the database
		 * @param	bool	$startTransaction	Whether this delete should start a new transaction
		 * @throws    QueryException    If the query fails
		 */
		public function delete($startTransaction = true)
		{
			foreach ($this->productCategories as $productCategory)
			{
				$productCategory->delete($startTransaction);
			}

			foreach($this->images as $productImage)
			{
				$productImage->delete();
			}

			parent::delete($startTransaction);
		}

		/**
		 * Gets a possible path to this product
		 * @return	string	A possible path
		 */
		public function get_path()
		{
			$category = null;

			if($this->category !== null)
			{
				$category = $this->category;
			}
			else
			{
				foreach($this->productCategories as $productCategory)
				{
					if ($productCategory->category->active)
					{
						$category = $productCategory->category;
						break;
					}
				}
			}

			if ($category !== null && !$category->isNull())
			{
				$path = $category->path . $this->slug . "/";

				return $path;
			}
			else
			{
				return '';
			}
		}

		public function getCanonicalLink()
		{
			$oldest = null;
			foreach ($this->productCategories as $productCategory)
			{
				if ($oldest === null || $productCategory->id < $oldest->id)
				{
					$oldest = $productCategory;
				}
			}
			return $oldest->category->path . $this->slug . "/";
		}

		/**
		 * Gets the categories this product belongs to
		 * @return	Category[]	The categories
		 */
		public function get_categories()
		{
			$categories = [];
			foreach ($this->productCategories as $productCategory)
			{
				$categories[] = $productCategory->category;
			}
			return $categories;
		}

		//region LineItemGenerator

		/**
		 * Gets a string that will uniquely identify this class from other Line Item Generators
		 * @return    string    The identifier
		 */
		public static function getClassLineItemGeneratorIdentifier(): string
		{
			return "Product";
		}

		/**
		 * Loads an object for this class, given an identifier
		 * @param    string $identifier The identifier that will identify a Line Item Generator
		 * @return    LineItemGenerator                    The original object that generated this Line Item, or null if such cannot be found
		 */
		public static function loadForLineItemGeneratorIdentifier($identifier): ?LineItemGenerator
		{
			return static::load($identifier);
		}

		/**
		 * Updates, replaces or deletes an existing Line Item
		 * Basically this is entended to check if the line item generator is still current (active, still exists etc)
		 * And update to whatever the current line item generator is like (price)
		 *
		 * @param    string   $identifier The identifier that will identify the Line Item Generator
		 * @param    LineItem $current    The line item to update
		 * @return    LineItem                    The updated line item, or null if it's been removed
		 */
		public static function updateLineItem($identifier, LineItem $current): ?LineItem
		{
			$product = static::load($identifier);

			if(!$product->active || $product->isNull())
			{
				return null;
			}

			if($current instanceof ProductLineItem)
			{
				foreach($current->options as $option)
				{
					if(!$option->optionGroup->active || $option->optionGroup->isNull() || !$option->option->active || $option->optionGroup->isNull())
					{
						return null;
					}
				}

				// New options must have been added to the product,
				if(count($current->options) !== count($product->optionGroups))
				{
					return null;
				}
			}

			$current->price = $product->price;
			$current->title = $product->name;

			return $current;
		}

		/**
		 * Gets a unique identifier for this object
		 * @return    string    An identifier that uniquely identifies this object
		 */
		public function getLineItemGeneratorIdentifier(): string
		{
			return $this->id;
		}

		/**
		 * Gets a Line Item from this object. The quantity, parentClassIdentifier and parentIdentifier will be filled in after you return the line item
		 * @return    LineItem    The generated line item
		 */
		public function getLineItem(): LineItem
		{
			$lineItem = new ProductLineItem();
			$lineItem->title = $this->name;
			$lineItem->price = $this->price;

			$lineItemOptions = [];

			foreach($this->optionGroups as $optionGroup)
			{
				if(!isset($_POST["options"][$optionGroup->id]))
				{
					throw new Exception("Please select a " . $optionGroup->name);
				}

				$option = ProductOption::load($_POST["options"][$optionGroup->id]);

				if(!$option->active || $option->group !== $optionGroup)
				{
					throw new Exception("That " . $optionGroup->name . " is not valid, please select another");
				}

				$lineItemOption = new LineItemOption();
				$lineItemOption->optionGroup = $optionGroup;
				$lineItemOption->option = $option;
				$lineItemOptions[] = $lineItemOption;
			}

			$lineItem->options = $lineItemOptions;

			return $lineItem;
		}

		/**
		 * Gets a representative thumbnail image for this Line Item Generator, may return null
		 * @return    Image    The representative image
		 */
		public function getLineItemImage(): ?Image
		{
			return ($this->activeImages[0] ?? new ProductImage())->thumbnail;
		}

		/**
		 * Gets a link to this Line Item Generator on the site, may return null
		 * @return    string    A link to view this item on the site
		 */
		public function getLineItemLink(): ?string
		{
			$path = $this->path;

			if($path === "")
			{
				return null;
			}

			return $path;
		}

		/**
		 * Gets a link to edit this Line Item Generator in the admin, may return null
		 * @return    string    The link to edit this generator in the admin panel
		 */
		public function getLineItemEditLink(): ?string
		{
			return $this->getEditLink();
		}

		//endregion
	}

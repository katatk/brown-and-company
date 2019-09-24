<?php
	namespace Products;

	use Configuration\AdminNavItem;
	use Configuration\AdminNavItemGenerator;
	use Configuration\Registry;
	use DatabaseObject\Category as GeneralCategory;
	use DatabaseObject\Column;
	use DatabaseObject\Entity;
	use DatabaseObject\Property\LinkToProperty;
	use DatabaseObject\Property\LinkFromMultipleProperty;
	use DatabaseObject\Property\Property;
	use DatabaseObject\Property\ImageProperty;
	use Files\Image;
	use Pages\Page;
	use \NavItem;

	use DatabaseObject\FormElement\ImageElement;
	use DatabaseObject\FormElement\Text;
	use DatabaseObject\FormElement\Textarea;
	use DatabaseObject\FormElement\Select;

	/**
	 * Handles categories using the Database Object system
	 * @author	Callum Muir
	 */
	class Category extends GeneralCategory implements NavItem, AdminNavItemGenerator
	{
		const TABLE = "categories";
		const ID_FIELD = "category_id";
		const SINGULAR = 'category';
		const PLURAL = 'categories';
		const HAS_POSITION = true;
		const HAS_ACTIVE = true;
		const LINK_METHOD = "get_path";
		const SLUG_PROPERTY = 'name';
		const PATH_PARENT = 'pathParent';
		const USE_TABS = true;
		const SLUG_TAB = "Content";

		const PARENT_PROPERTY = "parent";
		const SUBITEM_PROPERTY = 'children';

		const THUMBNAIL_LOCATION = DOC_ROOT . "/resources/images/category/";
		const THUMBNAIL_WIDTH = CATEGORY_THUMBNAIL_WIDTH;
		const THUMBNAIL_HEIGHT = CATEGORY_THUMBNAIL_HEIGHT;
		const THUMBNAIL_RESIZE_TYPE = ImageProperty::CROP;

		public $name = "";
		public $title = "";
		public $description = "";
		public $content = "";
		public $active = false;
		public $onNav = false;
		public $metaDescription = "";
		public $metaTitle = "";

		/** @var Category */
		public $parent = null;
		public $productCategories = null;
		public $products = null;
		public $children = null;

		/** @var Category[] */
		public $subcategories = null;

		/** @var Image null */
		public $image = null;

		public $path = "";

		/** @var Product[] */
		private $_products = null;

		/** @var Product[] */
		private $_allProducts = null;
		private $_children = null;
		private $_pathParent = null;

		/** @var string */
		private $_path = null;

		/**
		 * Gets the array of Properties that determine how this Category interacts with the database
		 */
		protected static function properties()
		{
			parent::properties();

			static::addProperty(new Property("name", "name", "string"));
			static::addProperty(new Property("title", "title", "string"));
			static::addProperty(new Property("description", "description", "string"));
			static::addProperty(new Property("content", "content", "html"));
			static::addProperty(new Property("active", "active", "bool"));
			static::addProperty(new Property("onNav", "on_nav", "bool"));
			static::addProperty(new Property("position", "position", "int"));
			static::addProperty(new LinkToProperty("parent", "parent_id", Category::class));
			static::addProperty(new LinkFromMultipleProperty("subcategories", Category::class, "parent", ["position" => true]));
			static::addProperty(new Property("products"));
			static::addProperty(new Property("allProducts"));
			static::addProperty(new LinkFromMultipleProperty("children", CategoryChild::class, 'parent'));
			static::addProperty(new ImageProperty("image", "image", static::THUMBNAIL_LOCATION, static::THUMBNAIL_WIDTH, static::THUMBNAIL_HEIGHT, static::THUMBNAIL_RESIZE_TYPE));
			static::addProperty(new Property("pathParent"));
			static::addProperty(new Property("path"));
			static::addProperty(new Property('metaDescription', 'meta_description', 'string'));
			static::addProperty(new Property('metaTitle', 'meta_title', 'string'));
			static::addProperty(new LinkFromMultipleProperty("productCategories", ProductCategory::class, "category"));
		}

		/**
		 * Gets the array of Columns that belong to this Database Object Generatory Category
		 */
		protected static function columns()
		{
			parent::columns();

			static::removeColumn("Name");

			static::addColumn(new Column("Name", function($category)
			{
				/** @var Category $category */
				if (count($category->getContents()) > 0)
				{
					$subitemName = count($category->getContents()) > 1 ? "Items" : " Item";
					return static::nameColumn($category) . ' (' . count($category->getContents()) . ' ' . $subitemName . ')';
				}
				else
				{
					return static::nameColumn($category);
				}
			}), 'Edit');

			static::removeColumn("Active");

			static::addColumn(new Column("Active", function($object)
			{
				if($object->id === null)
				{
					return "";
				}

				/** @var static $object */
				return $object->getActiveToggle();
			}), 'Edit');
		}

		/**
		 * Gets the tree of values to insert into a select box
		 * @param	Category	$root	The root Category to get child Categories for
		 * @param	string[][]	$tree	The current tree to insert Categories and Products into
		 * @param	int			$level	The number of dashes to insert before names
		 * @return	string[][]			The requested tree
		 */
		public static function getCategoryTree(Category $root = null, array $tree = [], $level = 0)
		{
			$categories = Category::loadAllFor("parent",$root);

			foreach($categories as $category)
			{
				$tree = static::getCategoryTree($category, $tree, $level + 1);

				$before = str_repeat("-", $level);
				$tree[$before . " " . $category->name] = ProductCategory::getProductCategoryArray($category);
			}

			return $tree;
		}

		/**
		 * Loads all the Generators to be displayed in the table
		 * If there's a parent property of the same class as this, only load top level categories
		 * @param	array	$orderBy	The properties to order by
		 * @return    static[]    The array of Generators
		 */
		public static function loadAllForTable(array $orderBy = [])
		{
			$categories = static::loadAllFor("parent", null);
			$products = Product::loadAllUncategorised();

			if(count($products) > 0)
			{
				$category = new Category;
				$category->name = "Uncategorised";

				$productCategories = [];

				foreach($products as $product)
				{
					$productCategory = new ProductCategory;
					$productCategory->product = $product;
					$productCategory->category = $category;
					$productCategories[] = $productCategory;
				}

				$category->children = $productCategories;
				$categories[] = $category;
			}

			return $categories;
		}

		/**
		 * Gets the first Category
		 * @return	Category	The first Category
		 */
		public static function loadFirst()
		{
			$query = "SELECT ~PROPERTIES "
				. "FROM ~TABLE "
				. "WHERE ~active = TRUE "
				 . "AND ~parent IS NULL "
				. "ORDER BY ~position "
				. "LIMIT 1";

			return static::makeOne($query);
		}

		/**
		 * Gets the top level categories
		 * @return	static[]	The top level categories
		 */
		public static function getTopLevelCats()
		{
			return static::loadAllForMultiple(['parent' => null, 'active' => true]);
		}

		/**
		 * Gets an array of name => id pairs of all categories
		 * @return	int[]	The pairs
		 */
		public static function getCategoryOptionsForProduct()
		{
			$options = [];
			$topCats = Category::loadAllFor('parent', null);
			foreach($topCats as $cat)
			{
				$options[$cat->getFullName()] = $cat->id;
				$children = $cat->getChildOptionsForProduct();
				if (count($children) > 0)
				{
					$options = array_merge($options, $children);
				}
			}
			return $options;
		}

		/**
		 * Sets the Form Elements for this Category
		 */
		protected function formElements()
		{
			$this->addFormElement(new Select('parent', 'Parent Category', static::getParentOptions($this)), "Content");
			$this->addFormElement((new Text('name', 'Name'))->setClasses('half first'), "Content" );
			parent::formElements();
			$this->addFormElement((new Text('title', 'Title <span>(Optional)</span>'))->setClasses('half'), "Content", 'slug');
			$this->addFormElement(new Textarea('description', 'Description'), "Content");
			$this->addFormElement(new ImageElement("image", "Image"), "Content");

			$this->addFormElement(new Text('metaTitle', 'Meta Title'), 'Metadata');
			$this->addFormElement(new Textarea('metaDescription', 'Meta Description'), 'Metadata');
		}

		/**
		 * Gets the text to use in a heading for this category
		 *
		 * @return String
		 */
		public function getTitle()
		{
			return $this->title !== '' ? $this->title : $this->name;
		}

		/**
		 * Get name => id pairs of possible parents for a Select form element
		 * @return	int[]	The possible parents
		 */
		 static public function getParentOptions($category, $parent = null, $level = 0)
 		{
 			$options = ['None (top level)' => null];
 			if ($parent === null)
 			{
 				$categories = static::loadAllFor('parent', null);
 			}
 			else
 			{
 				$categories = $parent->subcategories;
 			}

 			foreach ($categories as $c)
 			{
 				if (!$c->isDecendantOf($category) && $c->id !== $category->id)
 				{
 					$options[str_repeat('-', $level) . ' ' . $c->getFullName()] = $c->id;

 					if (count($c->subcategories) > 0)
 					{
 						$options = array_merge($options, static::getParentOptions($category, $c, $level + 1));
 					}
 				}
 			}

 			return $options;
 		}

		/**
		 * Gets the full name of this category, which includes parent names
		 * for the purposes of being used in select boxes in the admin, where if you have multiple things with the same name, you only end up with one of them
		 */
		public function getFullName()
		{
			return  $this->getParentNames() . ' ' . $this->name;
		}

		/**
		* Helper for getFullName()
		*/
		public function getParentNames()
		{
			$name = '';
			if (!$this->parent->isNull())
			{
				$name .= $this->parent->getParentNames() . '[' . $this->parent->name . ']';
			}
			return $name;
		}

		/**
		 * Check if this is a decendant category
		 *
		 * @param Category $possibleParent
		 * @return bool 	if this category is a decendant of $possibleParent
		 */
		public function isDecendantOf($possibleParent)
		{
			if ($this->parent->isNull())
			{
				return false;
			}
			else if ($this->parent->id === $possibleParent->id)
			{
				return true;
			}
			else
			{
				if ($this->parent->isDecendantOf($possibleParent))
				{
					return true;
				}
				else
				{
					return false;
				}
			}
		}

		/**
		 *	Gets an array of name => id of any children
		 * @param	int	$level	The level to get
		 * @return	array	An array of options
		 */
		public function getChildOptionsForProduct($level = 1)
		{
			$options = [];
			foreach($this->subcategories as $child)
			{
				$indent = '-';
				for ($i = 1; $i < $level; $i++)
				{
					$indent .= '-';
				}
				$options[$indent . $child->getFullName()] = $child->id;
				if (count($child->children) > 0)
				{
					$options = array_merge($options, $child->getChildOptionsForProduct($level + 1));
				}
			}
			return $options;
		}

		/**
		 * Loads an object that matches a slug (case insensitive)
		 * @param    string $slug        The slug to match against
		 * @param    Entity $parent      The parent of the object matching that slug
		 * @param    bool   $checkActive For use in Generator
		 * @return    static                    The matching object
		 */
		public static function loadForSlug($slug, Entity $parent = null, $checkActive = true)
		{
			if(static::SLUG_PROPERTY === "")
			{
				return static::makeNull();
			}

			/** @noinspection PhpUndefinedClassConstantInspection */
			$query = "SELECT ~PROPERTIES "
				. "FROM ~TABLE "
				. "WHERE LOWER(~slug) = ? ";

			$parameters = [strtolower($slug)];

			if ($parent instanceof Page)
			{
				$query .= "AND ~parent IS NULL ";
			}
			else if ($parent !== null)
			{
				$query .= "AND ~parent = ? ";
				$parameters[] = $parent->id;
			}

			$query .= "LIMIT 1";

			return static::makeOne($query, $parameters);
		}

		/**
		 * Gets the children of this category
		 * @return	Product[]|Category[]	The children of this category
		 */
		public function get_children()
		{
			if($this->_children !== null)
			{
				return $this->_children;
			}

			/** @var Product[]|Category[] $children */
			$children = CategoryChild::loadAllFor('parent', $this);

			return $children;
		}

		/**
		 * Sets the children of this category
		 * @param	Product[]|Category[]	$children	The children to set
		 */
		public function set_children($children)
		{
			$this->_children = $children;
		}

		/**
		 * Gets the active Products that belong to this Category
		 * @return	Product[]	The Products
		 */
		public function get_products()
		{
			if($this->_products === null)
			{
				$products = Product::loadAllForCategory($this);
				if (count($this->subcategories) > 0)
				{
					foreach ($this->subcategories as $subcat)
					{
						if ($subcat->active)
						{
							$products = array_merge($products, $subcat->products);
						}
					}
				}
				$this->_products = $products;
			}

			return $this->_products;
		}

		/**
		 * Gets all the Products that belong to this Category
		 * @return	Product[]	The Products
		 */
		public function get_allProducts()
		{
			if($this->_allProducts === null)
			{
				$this->_allProducts = Product::loadAllForCategory($this, true);
			}

			return $this->_allProducts;
		}

		/**
		 * Gets the path parent of this category, either a page or another category
		 * @return	Page|Category	$parent		The path parent of this category
		 */
		public function get_pathParent()
		{
			if ($this->_pathParent === null)
			{
				if ($this->parent->isNull())
				{
					$this->_pathParent =  Page::loadFor('module', 'Products');
				}
				else
				{
					$this->_pathParent = $this->parent;
				}
			}
			return $this->_pathParent;
		}

		/**
		 * Gets the path to this category
		 * @return	string	The path
		 */
		public function get_path()
		{
			if($this->_path === null)
			{
				$indexedPath = $this->getIndexedPaths();
				$keys = array_keys($indexedPath);
				$this->_path = count($keys) > 0 ? $indexedPath[$keys[0]] : '';
			}

			return $this->_path;
		}

		/**
		 * Gets the label for this item
		 * @return	string	The label for this item
		 */
		public function getNavLabel()
		{
			return $this->name;
		}

		/**
		 * Gets the path to this item
		 * @return	string	The path to this item
		 */
		public function getNavPath()
		{
			return $this->get_path();
		}

		/**
		 * Gets whether this item is currently selected
		 * @param	NavItem $currentNavItem The current nav item
		 * @return	bool	Whether this item is currently selected
		 */
		public function isNavSelected(NavItem $currentNavItem = null)
		{
			$className = get_called_class();
			if($currentNavItem instanceof $className && $currentNavItem->id === $this->id)
			{
				return true;
			}
			else
			{
				// @todo the amount of times this runs for any given nav could be excessive
				// cache on first pass?
				/** @var self $child */
				foreach($this->getChildNavItems() as $child)
				{
					if($child->isNavSelected($currentNavItem))
					{
						return true;
					}
				}
			}

			return false;
		}

		/**
		 * Gets whether this item opens in a new window
		 * @return	bool	Whether this item opens in a new window
		 */
		public function isOpenedInNewWindow()
		{
			return false;
		}

		/**
		 * Gets whether this item is the homepage
		 * @return	bool	Whether this item is the homepage
		 */
		public function isHomepage()
		{
			return false;
		}

		/**
		 * Gets the complete chain of Nav Items from parent to child, including the current Nav Item
		 * @return	NavItem[]	The chain of Nav Items
		 */
		public function getNavItemChain()
		{
			if ($this->parent->isNull())
			{
				$parentChain = Page::loadFor("module", "products")->getNavItemChain();
			}
			else
			{
				$parentChain = $this->parent->getNavItemChain();
			}
			return array_merge($parentChain, [$this]);
		}

		/**
		 * Gets any children this item has
		 * @return	static[]	The children this item has
		 */
		public function getChildNavItems()
		{
			return Category::loadAllForMultiple(['parent' => $this, 'active' => true]);
		}

		// region AdminNavItemGenerator

		/**
		 * Gets the nav item for this class
		 * @return    AdminNavItem        The admin nav item for this class
		 */
		public static function getAdminNavItem()
		{
			return new AdminNavItem(static::getAdminNavLink(), "Products", [static::class, Product::class], Registry::isEnabled("Products"));
		}

		//endregion
	}

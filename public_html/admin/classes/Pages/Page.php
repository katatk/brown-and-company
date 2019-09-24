<?php
	/*****
	 * The class file for the Page class
	 *
	 * @copyright	2016 Activate Design
	 * @author	Robert Urquhart <programmer@activatedesign.co.nz>
	 * @version 	3.0
	 **/
	namespace Pages;

	use Configuration\AdminNavItem;
	use Configuration\AdminNavItemGenerator;
	use Configuration\Registry;
	use Database\Database;
	use Database\QueryException;
	use DatabaseObject\Column;
	use DatabaseObject\Entity;
	use DatabaseObject\Category as GeneratorCategory;
	use DatabaseObject\FormElement\Element;
	use DatabaseObject\FormElement\GridElement;
	use DatabaseObject\Generator;
	use DatabaseObject\Property\LinkFromMultipleProperty;
	use DatabaseObject\Property\LinkToProperty;
	use DatabaseObject\Property\Property;
	use DatabaseObject\Property\ImageProperty;

	use DatabaseObject\FormElement\Checkbox;
	use DatabaseObject\FormElement\Editor;
	use DatabaseObject\FormElement\ImageElement;
	use DatabaseObject\FormElement\Radio;
	use DatabaseObject\FormElement\SearchingText;
	use DatabaseObject\FormElement\Select;
	use DatabaseObject\FormElement\Text;
	use DatabaseObject\FormElement\Textarea;

	use Exception;
	use Files\Image;
	use NavItem;
	use Publishable;
	use Searchable;
	use SearchResult;
	use ShortCode;
	use ShortCodeSupport;
	use SitemapItem;
	use Products\Category;

	/**
	 * Basic Page module
	 */
	class Page extends GeneratorCategory implements NavItem, Publishable, Searchable, ShortCodeSupport, SitemapItem, AdminNavItemGenerator
	{
		//Object
		const TABLE = 'pages';
		const ID_FIELD = 'page_id';
		const PATH_PARENT = 'parent';

		//Generator
		const NICE_NAME = 'Page';
		const SINGULAR = 'page';
		const PLURAL = 'pages';
		const SUBITEM_NAME_SINGULAR = 'subpage';
		const SUBITEM_NAME_PLURAL = 'subpages';
		const LABEL_PROPERTY = "name";
		const HAS_POSITION = true;
		const HAS_ACTIVE = true;
		const PARENT_PROPERTY = 'parent';
		const USE_TABS = true;
		const SLUG_PROPERTY = 'name';
		const SLUG_TAB = "Content";

		//Category
		const SUBITEM_PROPERTY = 'children';

		const SLIDE_CLASS = PageSlide::class;

		const IMAGE_LOCATION = DOC_ROOT . "/resources/images/page/";
		const IMAGE_WIDTH = PAGE_AUX_WIDTH;
		const IMAGE_HEIGHT = PAGE_AUX_HEIGHT;
		const IMAGE_RESIZE_TYPE = ImageProperty::SCALE;
		const BANNER_WIDTH = PAGE_BANNER_WIDTH;
		const BANNER_HEIGHT = PAGE_BANNER_HEIGHT;


		// Page
		public $name = '';
		public $module = 'Page';
		public $path = '';
		public $title = '';
		public $contentTitle = '';
		public $keywords = '';
		public $description = '';
		public $content = '';
		public $onNav = false;
		public $onSecondaryNav = false;
		public $isHomepage = false;
		public $isErrorPage = false;
		public $redirect = '';
		public $isExternalRedirect = false;
		public $isInternalRedirect = false;
		public $isDuplicate = false;
		public $useNewWindow = false;

		/** @var Image */
		public $image = null;
		public $useSlideshow = false;
		public $isRedirect = false;
		public $showSubpageLinks = false;

		// public $bannerImage = null;
		// public $bannerTitle = '';
		// public $bannerText = '';
		// public $bannerButton = '';


		/** @var Page $parent */
		public $parent = null;

		/** @var Page $original */
		public $original = null;

		/** @var PageSlide[] */
		public $slides = null;

		/** @var Page[] */
		public $children = null;

		/// internal flags
		// @var bool $validated can be changed by setter functions and is checked by save()
		// 				lets us do validation in setter functions instead of also having to refer to
		// 				properties in a separate validate() function
		protected $validated = true;

		// for flags where there can only be one of a given type in the site
		// protected bool $wasHomepage
		// protected bool $wasErrorPage
		protected $wasHomepage = false;
		protected $wasErrorPage = false;

		// for detecting if we are saving a child of the actual edited item
		static $startedChain = null;

		/**
		 * Sets the array of Properties that determine how this Object interacts with the database
		 */
		protected static function properties()
		{
			parent::properties();

			static::addProperty(new Property('name','nav_text','string'));
			static::addProperty(new Property('title','title','string'));
			static::addProperty(new Property('contentTitle', 'content_title', 'string'));
			static::addProperty(new Property('content','content','html'));
			static::addProperty(new Property('useSlideshow','has_slideshow','bool'));
			// static::addProperty(new Property('bannerText','banner_text','string'));
			static::addProperty((new LinkFromMultipleProperty("slides", static::SLIDE_CLASS, "page"))->setAutoDelete(true));
			static::addProperty(new Property('keywords','keywords','string'));
			static::addProperty(new Property('description','description','string'));
			static::addProperty(new Property('module','page_type','string'));
			static::addProperty(new LinkToProperty("parent", "parent_id", Page::class));
			static::addProperty(new Property("isRedirect"));
			static::addProperty(new Property('isExternalRedirect','external_redirect','bool'));
			static::addProperty(new Property('redirect','redirect_path','string'));
			static::addProperty(new Property('isInternalRedirect','internal_redirect','bool'));
			static::addProperty(new Property('isDuplicate','duplicate','bool'));
			static::addProperty(new LinkToProperty('original','original_id', Page::class));
			static::addProperty(new Property('onNav','display_on_nav','bool'));
			static::addProperty(new Property('onSecondaryNav','display_on_secondary_nav','bool'));
			static::addProperty(new Property('useNewWindow','new_window','bool'));
			static::addProperty(new Property('isHomepage','is_homepage','bool'));
			static::addProperty(new Property('isErrorPage','is_error_page','bool'));
			static::addProperty(new Property('path'));
			static::addProperty(new ImageProperty('image', 'auxilary_image', static::IMAGE_LOCATION, static::IMAGE_WIDTH, static::IMAGE_HEIGHT, static::IMAGE_RESIZE_TYPE));
			// static::addProperty(new ImageProperty('bannerImage', 'banner', static::IMAGE_LOCATION, static::BANNER_WIDTH, static::BANNER_HEIGHT, static::IMAGE_RESIZE_TYPE));
			// static::addProperty(new Property("bannerTitle", "banner_title", "string"));
			// static::addProperty(new Property("bannerText", "banner_text", "html"));
			// static::addProperty(new Property("bannerButton", "banner_button", "string"));
			static::addProperty((new LinkFromMultipleProperty("children", Page::class, "parent"))->setAutoDelete(true));
		}

		/**
		 * Sets the array of Columns that are displayed to the user for this object type
		 */
		protected static function columns()
		{
			parent::columns();

			static::addColumn(new Column('On Menu',function($generator)
			{
				/** @var Page $generator */
				return $generator->getToggle('onNav');
			}), 'Active');

			if(SITE_SECONDARY_NAV)
			{
				static::addColumn(new Column('In Quicklinks',function($generator)
				{
					/** @var Page $generator */
					return $generator->getToggle('onSecondaryNav');
				}), 'Active');
			}
		}

		/**
		 * Gets the html to display in the Name column
		 *
		 * @param  Category 	$category 	Cateogory to get the name for
		 * @return string 	html for the column
		 */
		public static function nameColumn($category)
		{
			/** @var Page $category */
			return $category->generateAdminLink();
		}

		/**
		 * Sets the Form Elements for this object
		 */
		protected function formElements()
		{
			$this->addFormElement((new Text("name", 'Menu text*'))->setClasses('half inline-label first')->addValidation(Element::REQUIRED), "Content");
			//$this->addFormElement((new Text("contentTitle", 'Content Title'))->setClasses('inline-label half'), "Content");

			parent::formElements();
			// if(PAGE_BANNER)
			// {
			// 	$this->addFormElement(new ImageElement('bannerImage', 'Banner', $this->bannerImage), "Content");
			// }

			$this->addFormElement(new Text("contentTitle", 'Content Title'), "Content");

			$this->addFormElement(new Editor("content", 'Content'), "Content");



			// if(PAGE_BANNER_TEXT)
			// {
			// 	$this->addFormElement((new Text("bannerText", 'Banner Text')), "Content");
			// }

			// if(PAGE_AUX_IMAGE)
			// {
			// 	$this->addFormElement(new ImageElement('image', 'Image', $this->image), "Content");
			// }

			if(PAGE_HAS_SLIDESHOW)
			{
				$this->addFormElement(new Checkbox("useSlideshow", 'Display banner/slideshow'), "Banner/Slideshow");
				//
				// /** @var ImageProperty $imageProperty */
				$this->addFormElement(new GridElement('slides', 'Slides'), "Banner/Slideshow");
			}

			$this->addFormElement(new Text("title", 'Page Title'), "Metadata");
			$this->addFormElement(new Textarea("keywords", 'Keywords'), "Metadata");
			$this->addFormElement(new Textarea("description", 'Description'), "Metadata");
			$this->addFormElement(new Select("module", 'Page type', $this->getPageTypeOptions()), "Setup");
			$this->addFormElement(new SearchingText("parent", 'Parent ("Select none" for top level pages)', $this->getParentSearchValues()), "Setup");

			$pageType = "normalPage";

			if($this->isExternalRedirect)
			{
				$pageType = "externalLink";
			}
			else if($this->isInternalRedirect)
			{
				$pageType = "internalLink";
			}
			else if($this->isDuplicate)
			{
				$pageType = "duplicate";
			}

			$this->addFormElement(new Radio("actsAs", "Acts as",
			[
				"Normal Page" => "normalPage",
				"External Link (enter URL below)" => "externalLink",
				"Internal Link (select page below)" => "internalLink",
				"Duplicate of (select page below)" => "duplicate"
			], $pageType), "Setup");

			$this->addFormElement(new Text("redirect", "Redirect URL", $this->redirect), "Setup");
			$this->addFormElement(new SearchingText("original", "Link to / Duplicate of", $this->getOriginalSearchValues()), "Setup");
			$this->addFormElement(new Checkbox("onNav", 'Display in main menu'), "Setup");

			if(SITE_SECONDARY_NAV)
			{
				$this->addFormElement(new Checkbox("onSecondaryNav", 'Display in footer menu (Quicklinks)'), "Setup");
			}

			$this->addFormElement(new Checkbox('useNewWindow', 'Open in new browser tab'), "Setup");
			$this->addFormElement(new Checkbox('isHomepage', 'Set as Home page'), "Setup");
			$this->addFormElement(new Checkbox("isErrorPage", 'Set as Error page'), "Setup");
		}

		/**
		 * Returns an array of identifier/identifier key pairs.
		 * @param	int			$parent		The parent id for the page
		 * @param	int			$level		The page level, indicating the number of dashes required
		 * @return	string[]				Identifier property/identifier key pairs
		 */
		public static function loadNames($parent = null, $level = 0)
		{
			$items = [];

			foreach(static::loadAllFor("parent", $parent, ["position" => true]) as $page)
			{
				$items[str_repeat("-", $level) . " " . $page->name] = $page->id;
				$items += static::loadNames($page, $level + 1);
			}

			return $items;
		}


		/*****
		 * getters (non-html returns)
		 **/
		/**
		 * Gets the controller for this page
		 * @return	PageController	The requested controller
		 */
		public function getController()
		{
			$pageTypes = PageType::get();

			if(isset($pageTypes[$this->module]))
			{
				$controllerClass = $pageTypes[$this->module]->controller;

				return new $controllerClass($this);
			}

			return new PageController($this);
		}

		/**
		 * pseudo property isRedirect
		 * 		does this page redirect to another
		 *
		 * @return bool
		 */
		public function get_isRedirect()
		{
			return ($this->getProperty('isExternalRedirect') || $this->getProperty('isInternalRedirect'));
		}

		/**
		 * pseudo property isNormalPage
		 * 		does this page display it's own content
		 *
		 * @return bool
		 */
		public function get_isNormalPage()
		{
			return !($this->getProperty('isExternalRedirect') || $this->getProperty('isInternalRedirect') || $this->getProperty('isDuplicate'));
		}

		/**
		 * return the appropriate target attribute to be incuded in a link tag
		 * @todo should be generateTarget()
		 *
		 * @return string
		 */
		public function get_target()
		{
			return $this->getProperty('useNewWindow') ? 'target="_blank"' : '';
		}

		/**
		 * data for the 'parent' property SearchingText input
		 *
		 * @return mixed[]
		 */
		public function getParentSearchValues()
		{
			$parent = $this->parent;
			$id = $parent->id;
			return [
				'class' => get_called_class()
				, 'search' => 'name'
				, 'real' => ($id) ? $id : 0
				, 'display' => ($id) ? '"'.$parent->getNavChainAsText().'"' : 'No Parent (Top Level '.ucfirst(static::SINGULAR).')'
			];
		}

		/**
		 * current data for the 'parent' property SearchingText input
		 *
		 * @return mixed
		 */
		public function getOriginalSearchValues()
		{
			$original = $this->original;
			$id = $original->id;
			return [
				'class' => get_called_class()
				, 'search' => 'name'
				, 'real' => ($id) ? $id : 0
				, 'display' => ($id) ? '"'.$original->getNavChainAsText().'"' : 'No Source'
			];
		}

		/**
		 * build an array of page (module) type => module name for feeding to 'module' property Select
		 *
		 * @return string[]
		 */
		public function getPageTypeOptions()
		{
			$items = [];

			foreach(array_keys(PageType::get()) as $pageType)
			{
				$items[$pageType] = $pageType;
			}

			return $items;
		}

		/**
		 * The path for an arbitrary page (unique page_type)
		 *		usually for output in a template file
		 *
		 * @param string $type of the page ('module' property)
		 *
		 * @return string
		 */
		public static function getPathForType($type = '')
		{
			$page = static::loadFor('module', $type);
			return $page->isNull() ? '' : $page->getNavPath();
		}

		/**
		 * The path for the current site homepage
		 *
		 * @return string
		 */
		public static function getHomepagePath()
		{
			$page = static::loadFor('isHomepage',true);

			//fallback assume page id 1 is homepage
			if($page->isNull() || !$page->active)
			{
				$page = static::load(1);
			}

			// fallback go to root
			if($page->isNull() || !$page->active)
			{
					return '/';
			}
			//else
			return $page->getNavPath();
		}

		/**
		 * return paths to active pages for inclusion in sitemap
		 * @return string[]
		 */
		public static function getSitemapUrls()
		{
			$paths = [];

			$pages = static::loadAllFor('active',true, []);

			foreach($pages as $obj)
			{
				if($obj->isHomepage)
				{
					$paths[] = '/';
				}
				elseif($obj->isExternalRedirect)
				{
					// do not include in sitemap
					continue;
				}
				else
				{
					$paths[] = $obj->path;
				}
			}

			return $paths;
		}

		/*****
		 * setters
		 **/
		/**
		 * validate and set nav text ('name' property)
		 *
		 * @param string $str
		 */
		public function set_name($str = '')
		{
			$str = trim($str);
			if($str === '')
			{
				addMessage('Menu text can not be blank.');
				$this->validated = false;
				return;
			}

			$this->setProperty('name',$str);
		}

		/**
		 * by default use the page name as the title
		 *
		 * @param string $str
		 */
		public function set_title($str = '')
		{
			if($str === '')
			{
				$str = $this->getProperty(static::LABEL_PROPERTY);
			}

			$this->setProperty('title', $str);
		}

		/**
		 * validate and set parent
		 * this needs to be done after set_name() so the path can be updated if necessary
		 *
		 * @param int|static $intOrObj
		 */
		public function set_parent($intOrObj = 0)
		{
			$id = ($intOrObj instanceOf Generator) ? $intOrObj->id : (int) $intOrObj;
			if($id === 0 && !is_null($this->parent))
			{
				$this->setProperty('parent',null);
			}
			else
			{
				$newParent = Page::load($id);

				if($newParent->id === $this->parent->id)
				{
					// don't bother with other validation calls
					return;
				}
				// else
				// validate
				if($id && !$newParent->id)
				{
					addMessage('The selected parent '.static::SINGULAR.' no longer exists.');
					$this->validated = false;
					return;
				}
				//else
				if($this->id && in_array($this->id,$newParent->getNavItemChain()))
				{
					addMessage('The '.static::SINGULAR.' can not be moved below itself.');
					$this->validated = false;
					return;
				}
				//else
				if($this->id && $newParent->id === $this->id)
				{
					addMessage('The '.static::SINGULAR.' can not be it\'s own parent.');
					$this->validated = false;
					return;
				}
				// good
				$this->setProperty('parent',$newParent);
			}
		}

		/**
		 * validate and set the page this one copies or redirects to
		 * 		this needs to be done after set_name() so the path can be updated if necessary
		 *
		 * @param int|static $int
		 */
		public function set_original($int = 0)
		{
			// if we have an object get the id / fix type
			$id = ($int instanceOf Generator) ? $int->id : (int) $int;

			if($id === 0 && !is_null($this->original))
			{
				$this->setProperty('original', null);
			}
			else
			{
				$newParent = Page::load($id);

				if($newParent->id === $this->original->id)
				{
					// don't bother with other validation calls
					return;
				}
				//else
				// validate
				if($id && !$newParent->id)
				{
					addMessage('The selected page no longer exists.');
					$this->setProperty('original', null);
					return;
				}

				//else
				if($this->id && $newParent->id === $this->id)
				{
					addMessage('The page can not be it\'s own copy or redirect.');
					$this->setProperty('original', null);
					return;
				}
				// good
				$this->setProperty('original', $newParent);
			}
		}

		/**
		 * set property and flag to determine if homepage has changed
		 * 		only one page can be set as the homepage at a time
		 *
		 * @param bool $bool
		 */
		public function set_isHomepage($bool)
		{
			// in save() we check wasHomepage so we can unset any other instances
			$this->wasHomepage = $this->getProperty('isHomepage');
			$this->setProperty('isHomepage', $bool);
		}

		/**
		 * set property and flag to determine if error page has changed
		 * 		only one page can be set as the error page at a time
		 *
		 * @param bool $bool
		 */
		public function set_isErrorPage($bool)
		{
			// in save() we check wasErrorPpage so we can unset any other instances
			$this->wasErrorPage = $this->getProperty('isErrorPage');
			$this->setProperty('isErrorPage', $bool);
		}

		/**
		 * Handles setting the functionality for this page
		 * @param	string	$value	Specific value provided from the actsAs form element
		 */
		public function set_actsAs($value)
		{
			$this->isExternalRedirect = false;
			$this->isInternalRedirect = false;
			$this->isDuplicate = false;

			switch($value)
			{
				case "externalLink":
					$this->isExternalRedirect = true;
				break;

				case "internalLink":
					$this->isInternalRedirect = true;
				break;

				case "duplicate":
					$this->isDuplicate = true;
				break;
			}
		}

		/**
		 * Gets the class a database row should be cast to
		 * @param    array $row The database row as an associative array
		 * @return    string|Entity            The class to cast to
		 */
		protected static function getClassNameForRow(array $row)
		{
			$pageType = $row[static::getProperties()['module']->getDatabaseName()];
			$pageTypes = PageType::get();

			if(isset($pageTypes[$pageType]))
			{
				return $pageTypes[$pageType]->class;
			}

			return static::class;
		}

		/*****
		 * loaders
		 **/

		/**
		 * Loads the top level pages on a particular nav
		 * @param	int $navId The identifier for the nav the pages belong to
		 * @return	static[]	The top level pages
		 * included because Callum has in it old page class, may no longer be necessary
		 */
		public static function loadAllTopLevelNavItems($navId = 1)
		{
			$query = "SELECT ~PROPERTIES "
				. "FROM ~TABLE "
				. "WHERE "
				. "~active = TRUE AND ~parent IS NULL "
				. "AND " . (($navId == 1) ? '  ~onNav' : '~onSecondaryNav') ." = TRUE "
				. "ORDER BY ~position";

			return static::makeMany($query, []);
		}

		/**
		 * Gets the active pages that append to this page
		 * @return	static[]	Said pages
		 *
		 * included because Callum has in it old page class, may no longer be necessary
		 */
		function getActiveAppendedPages()
		{
			$query = "SELECT ~PROPERTIES "
				. "FROM ~TABLE "
				. "WHERE ~append = ? "
				. "AND ~active = TRUE "
				. "ORDER BY position";

			return Page::makeMany($query, [$this->id]);
		}

		///
		/// record manipulation
		///

		/**
		 * Deletes the Entity from the database
		 * @param	bool	$startTransaction	Whether this delete should start a new transaction
		 * @throws    QueryException    If the query fails
		 */
		public function delete($startTransaction = true)
		{
			if(is_null(static::$startedChain))
			{
				static::$startedChain = $this->id;
			}

			// subpages get deleted by Category
			parent::delete();

			// we only want to do this once, after all children have been deleted
			if(static::$startedChain === $this->id)
			{
				createXmlSitemap();
				static::$startedChain = null;
			}
		}

		/**
		 * Either updates or inserts the Object into the database
		 * checks validation status before updating or inserting the object
		 * informative validation messages to be added in setters (where validation occurs)
		 *
		 * @todo  should return $this for chaining
		 * @param    bool $startTransaction Whether this save should start a new transaction
		 * @throws Exception
		 * @throw Exception if validation fails or file is unable to be created
		 */
		public function save($startTransaction = true)
		{
			if(!$this->validated)
			{
				throw new Exception(get_called_class(). ' failed validation.');
			}
			// else
			parent::save($startTransaction);

			if(!$this->id && !$this->isNull())
			{
				throw new Exception(get_called_class(). ' generator failed to save.');
			}
			//else

			if($this->isHomepage && !$this->wasHomepage)
			{
				Database::query(static::processQuery("UPDATE ~TABLE SET ~isHomepage = FALSE WHERE ~id != ?"), [$this->id]);
			}

			if($this->isErrorPage && !$this->wasErrorPage)
			{
				Database::query(static::processQuery("UPDATE ~TABLE SET ~isErrorPage = FALSE WHERE ~id != ?"), [$this->id]);
			}

			if($startTransaction)
			{
				createXmlSitemap();
			}
		}

		//This function is probably not needed anymore.
		/**
		 * Updates this Generator from the form
		 * @param	string[]			 $prefix	List of prefixes
		 * @param 	GeneratorElement     $origin 	Comes from a grouped GeneratorElement
		 * @throws	Exception						When validation fails
		 */
		/*
		public function updateFromForm(array $prefix = [], $origin = null)
		{
			if(is_null(static::$startedChain))
			{
				static::$startedChain = $this->id;
			}

			parent::updateFromForm($prefix, $origin);
		}
		*/

		/*****
		 * html generators
		 **/

		/**
		 * Generates the heading text of the creation form for this type of Generator
		 *
		 * @return string
		 */
		public static function generateCreateFormHeader()
		{
			$html = 'Add ' . ucfirst(static::SINGULAR);
			if(isset($_GET['parent']))
			{
				$labelProp = static::LABEL_PROPERTY;
				$parent = static::load($_GET['parent']);
				/** @noinspection PhpVariableVariableInspection */
				$html .= ' to ' . $parent->$labelProp;
			}

			$html .= "\n";

			return $html;
		}

		/**
		 * return html to be used in the admin panel table - not a link if page is not active
		 *
		 * @return string;
		 */
		public function generateAdminLink()
		{
			$navText = $this->getNavLabel();
			$html =  ($this->getProperty('active')) ? '<a href="' . $this->path . '" target="_blank">'.$navText .'</a>'
				: $navText;

			$module = $this->getProperty('module');
			$html .= (strtolower($module) !== 'page') ? ' [' . $module .']' : '';
			$html .= $this->getProperty('isHomepage') ? ' [Homepage]' : '';
			$html .= $this->getProperty('isErrorPage') ? ' [404 error page]' : '';
			$html .= $this->isRedirect ? ' [Redirect]' : '';
			$html .= $this->isDuplicate ? ' [Duplicate]' : '';

			return $html;
		}

		/**
		 * Gets the path to this page
		 * @return	string	The path to this page
		 */
		public function get_path()
		{
			// current() returns the item at the pointer which by default is the first item in the array of paths
			return current($this->getIndexedPaths());
		}

		/**
		 * utility for Page in SearchingText fields
		 * @return string
		 */
		public function getNavChainAsText()
		{
			$return = [];
			$chain = $this->getNavItemChain();
			foreach($chain as $obj)
			{
				$return[] = $obj->getNavLabel();
			}

			return implode(' &gt; ' , $return);
		}

		/*****
		 * NavItem interface methods
		 */
		/**
		 * Gets the label for this item
		 * @return	string	The label for this item
		 */
		public function getNavLabel()
		{
			return cleanHtmlData($this->getProperty('name'));
		}

		/**
		 * Gets the path to this item
		 * @return	string	The path to this item
		 */
		public function getNavPath()
		{
			$path = $this->path;

			if($this->getProperty('isHomepage'))
			{
				$path = "/";
			}

			if($this->get_isRedirect())
			{
				if($this->isExternalRedirect)
				{
					$path = $this->getProperty('redirect');
				}
				else
				{
					$path = $this->original->getNavPath();
				}
			}

			return $path;
		}

		/**
		 * Gets the parent of this page
		 * @return	Page	The parent item
		 */
		public function getParentNavItem()
		{
			return ($this->parent->isNull()) ? null : $this->parent;
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
			return $this->getProperty('useNewWindow');
		}

		/**
		 * Gets whether this item is the homepage
		 * @return	bool	Whether this item is the homepage
		 */
		public function isHomepage()
		{
			return $this->getProperty('isHomepage');
		}

		/**
		 * Gets the complete chain of Nav Items from parent to child, including the current Nav Item
		 * @return	NavItem[]	The chain of Nav Items
		 */
		public function getNavItemChain()
		{
			$parent = $this->getParentNavItem();
			$return = [$this];
			return ($parent !== null) ? array_merge($parent->getNavItemChain(), $return) : $return;
		}

		/**
		 * Gets any (active) children this item has
		 * @param int $navId Which nav this page is on, the primary one or the secondary one (TODO, we should look at this again and make it less weird.)
		 * @return static[] The children this item has
		 */
		public function getChildNavItems($navId = 1)
		{
			$query = 'SELECT ~PROPERTIES FROM ~TABLE WHERE ~parent = ? AND ~active = TRUE AND '
				. (($navId == 1) ? '~onNav' : '~onSecondaryNav') . " = TRUE " . 'ORDER BY ~position ASC';

			return Page::makeMany($query, [$this->id]);
		}

		/*****
		 * Publishes interface methods
		 **/
		/**
		 * Format and output properties as a coherent string of HTML
		 *
		 * @return string
		 */
		public function output()
		{
			$html = '';

			if($this->content !== '')
			{
				$html = ShortCode::expandHtml($this->content);
			}

			return $html;
		}

		/*****
		 * Searchable interface
		 */
		/**
		 * Performs a search defined by $search  using the supplied string
		 *
		 * @var string $search context of the particular search
		 *		(usually used within the method to determine what fields to search on)
		 * @var string $term the search term/s
		 * @var string[] $classParams any other parameters used by the class eg fields from a form
		 *
		 * @return	string[]	containing human-readable text labels and object ids (value) of results
		 *		for json_encoding
		 */
		public static function ajaxSearch($search = 'name', $term = '', array $classParams = [])
		{
			/////
			// @var array[] $return init empty
			// @var string $dataClassName we are searching in the data object table not this table
			///
			$return = [];

			// whitelist searchable properties
			switch($search)
			{
				case 'name':
					$query = "SELECT ~id as id, ~name as $search FROM ~TABLE "
						. " WHERE ~$search LIKE ?";
					$query = static::processQuery($query);

					foreach(Database::query(static::processQuery($query), ["%$term%"]) as $row)
					{

						$return[] = [
							'label' => $row[$search]
							, 'value' => $row['id']
						];
					}

					$return[] = [
						'label' => '[Select none]'
						, 'value' => 0
					];
					break;
				default:
					//$return = parent::ajaxSearch($search, $term);
			}

			return $return;
		}

		/**
		 * create a SearchResult containing relevant information for the object
		 *
		 * @return SearchResult usually for output to a page alongside other SearchResults
		 */
		public function makeSearchResult()
		{
			return new SearchResult([
						'module' => get_called_class()
						, 'object' => $this
						, 'path' => $this->getNavPath()
						, 'title' => $this->getNavLabel()
						, 'description' => $this->description
						, 'image' => (PAGE_AUX_IMAGE ? $this->getProperty('image') : null)
					]);
		}

		/**
		 * search function
		 *
		 * @param string $term //TODO What does this do?
		 * @param array  $classParams //TODO What does this do?
		 * @return SearchResult[]
		 */
		public static function search($term = '', array $classParams = [])
		{
			$return = [];

			if($term !== '')
			{
				//can't use static::makeMany() because we lose Relevance calculation

				$query = "SELECT ~PROPERTIES, MATCH(~content, ~title, ~keywords, ~description, ~name) AGAINST (?) as Relevance FROM ~TABLE WHERE ~active = TRUE AND MATCH(~content, ~title, ~keywords, ~description, ~name) AGAINST (?) > 0.6";
				$results = Database::query(static::processQuery($query),[$term, $term]);

				foreach($results as $row)
				{
					$obj = static::makeFromRow($row);
					$searchResult = $obj->makeSearchResult();
					$searchResult->relevance = $row['Relevance'];
					$return[] = $searchResult;
				}
			}

			return $return;
		}

		/**
		 * Runs on clone
		 */
		public function __clone()
		{
			parent::__clone();

			$this->active = false;
		}

		/**
		* Get the page that should be used in the template, ie, if this page is the duplicate of another
		* @return  Page 	$page 	the page to use as content
		*/
		public function getContentPage()
		{
			if ($this->isDuplicate)
			{
				return $this->original;
			}
			return $this;
		}

		//region AdminNavItemGenerator

		/**
		 * Gets the nav item for this class
		 * @return    AdminNavItem        The admin nav item for this class
		 */
		public static function getAdminNavItem()
		{
			$identifiers = [];

			foreach(PageType::get() as $pageType)
			{
				$identifiers[] = $pageType->class;
			}

			return new AdminNavItem(static::getAdminNavLink(), "Pages", $identifiers, Registry::isEnabled("Pages"));
		}

		//endregion

		//region ShortCodeSupport

		/**
		 * Gets a unique identifier for this class
		 * @return    string    An identifier that uniquely identifies this class
		 */
		public static function getClassShortcodeIdentifier()
		{
			return "Page";
		}

		/**
		 * Loads an object for this class, given an identifier
		 * @param    string $identifier The identifier to load from
		 * @return    Publishable                    An object that can be outputted to the page, or null if the correct one cannot be found
		 */
		public static function loadForShortcodeIdentifier($identifier)
		{
			$page = static::loadForMultiple(
			[
				"id" => $identifier,
				"active" => true
			]);

			return $page->isNull() ? null : $page;
		}

		/**
		 * Gets a unique identifier for this object
		 * @return    string    An identifier that uniquely identifies this object
		 */
		public function getShortcodeIdentifier()
		{
			return $this->id;
		}

		//endregion
	}

<?php
	/*****
	 * The class file for the BlogArticle class
	 *
	 * @copyright    2017 Activate Design
	 **/

	namespace Blog;

	use Configuration\AdminNavItem;
	use Configuration\AdminNavItemGenerator;
	use DatabaseObject\Generator;
	use DatabaseObject\Property\Property;
	use DatabaseObject\Property\ImageProperty;
	use DatabaseObject\Column;

	use DatabaseObject\FormElement\Text;
	use DatabaseObject\FormElement\Textarea;
	use DatabaseObject\FormElement\Date;
	use DatabaseObject\FormElement\Editor;
	use DatabaseObject\FormElement\ImageElement;

	use DateTime;
	use Files\Image;
	use Pages\Page;
	use Database\Database;
	use SitemapItem;
	use Configuration\Registry;

	/**
	 * The class for a single blog article
	 */
	class BlogArticle extends Generator implements SitemapItem, AdminNavItemGenerator
	{
		const TABLE = 'blog_articles';
		const ID_FIELD = 'blog_article_id';
		const SINGULAR = 'article';
		const PLURAL = 'articles';
		const HAS_ACTIVE = true;
		const LABEL_PROPERTY = 'title';
		const SLUG_PROPERTY = "title";
		const PATH_PARENT = "page";
		
		const IMAGES_LOCATION = DOC_ROOT . "/resources/images/blog/";
		const IMAGE_WIDTH = BLOG_IMAGE_WIDTH;
		const IMAGE_HEIGHT = BLOG_IMAGE_HEIGHT;
		const IMAGE_RESIZE_TYPE = ImageProperty::SCALE;
		const THUMBNAIL_WIDTH = BLOG_THUMBNAIL_WIDTH;
		const THUMBNAIL_HEIGHT = BLOG_THUMBNAIL_HEIGHT;
		const THUMBNAIL_RESIZE_TYPE = ImageProperty::CROP;

		// matches the type of page as selected in the Page Setup tab
		// @see get_path()
		const PAGE_TYPE = 'Blog';
		public $title = ''
		, $author = ''
		, $summary = ''
		, $content = '';

		public $path = "";

		/** @var DateTime */
		public $date = null;

		public $image = null;
		public $thumbnail = null;

		public $updateSitemap = false;

		/**
		 * Gets the array of Properties that determine how this Object interacts with the database
		 */
		protected static function properties()
		{
			parent::properties();
			static::addProperty(new Property('title', 'title', 'string'));
			static::addProperty(new Property("author", "author", "string"));
			static::addProperty(new Property('date', 'date', 'date'));
			static::addProperty(new Property('title', 'title', 'string'));
			static::addProperty(new Property('summary', 'summary', 'string'));
			static::addProperty(new Property('content', 'content', 'html'));
			static::addProperty(new ImageProperty('image', 'image', static::IMAGES_LOCATION, static::IMAGE_WIDTH, static::IMAGE_HEIGHT, static::IMAGE_RESIZE_TYPE));
			static::addProperty(new ImageProperty('thumbnail', 'thumbnail', static::IMAGES_LOCATION, static::THUMBNAIL_WIDTH, static::THUMBNAIL_HEIGHT, static::THUMBNAIL_RESIZE_TYPE));
			static::addProperty(new Property("path"));
		}

		/**
		 * Sets the array of Columns that are displayed to the user for this object type
		 */
		protected static function columns()
		{
			static::addColumn(new Column('Article', 'title'));
			static::addColumn(new Column('Date', function(Generator $generator) {
				/** @noinspection PhpUndefinedFieldInspection */
				return $generator->date->format('Y-m-d');
			}));
			parent::columns();
		}

		/**
		 * Loads all the Generators to be displayed in the table
		 * @return    static[]    The array of Generators
		 */
		public static function loadAllForTable()
		{
			return static::loadAll(['date' => false, 'id' => false]);
		}

		/**
		 * Gets an array of the most recent blog articles
		 * @param $limit 	The number of recent articles to get
		 * @return static[] Recent blog articles
		 */
		public static function getRecent($limit = RECENT_ARTICLES)
		{
			$query = "SELECT * "
				. "FROM ~TABLE "
				. "WHERE ~active = true "
				. "ORDER BY ~date DESC, ~id DESC  "
				. "LIMIT ?";

			return static::makeMany($query, [$limit]);
		}

		/**
		 * Generates a user friendly message about how the images will be cropped, and to what dimensions
		 * ImageElement currently isn't clever enough to handle generating a scaling message for when two images are created, so we need to do that here instead
		 * @return string 	User message with image dimensions
		 */
		public static function getScalingMessage()
		{
			$scalingMessage = 'This image will be ' . (static::IMAGE_RESIZE_TYPE === ImageProperty::CROP ? 'cropped' : 'scaled') . ' down to a maximum width of '.static::IMAGE_WIDTH.' pixels and a maximum height of '.static::IMAGE_HEIGHT.' pixels. <br />
								A ' . (static::THUMBNAIL_RESIZE_TYPE === ImageProperty::CROP ? 'cropped' : 'scaled') . ' thumbnail will be created with a maximum width of '.static::THUMBNAIL_WIDTH.' pixels and a maximum height of '.static::THUMBNAIL_HEIGHT.' pixels.';

			return $scalingMessage;
		}

		/**
		 * Gets the mav item for this class
		 * @return    AdminNavItem        The admin nav item for this class
		 */
		public static function getAdminNavItem()
		{
			return new AdminNavItem(static::getAdminNavLink(), "Blog", [static::class], Registry::isEnabled("Blog"));
		}

		/**
		 * Sets the Form Elements for this object
		 */
		protected function formElements()
		{
			parent::formElements();

			$this->getFormElements()['slug']->setClasses('asymmetric');
			$this->getFormElements()['updateSlugFromProperty']->setClasses('asymmetric');

			$this->addFormElement((new Text('title', 'Title'))->setClasses('asymmetric'), '', 'slug');
			//$this->addFormElement((new Text('author', 'Author'))->setClasses('asymmetric'));
			$this->addFormElement((new Date('date', 'Date'))->setClasses('asymmetric'));
			$this->addFormElement((new Textarea('summary', 'Summary'))->setClasses('asymmetric'));

			$imageElement = (new ImageElement('imageUpload', 'Image', [$this->image, $this->thumbnail], static::THUMBNAIL_RESIZE_TYPE, static::THUMBNAIL_WIDTH, static::THUMBNAIL_HEIGHT, false))
				->setClasses('asymmetric image-element')
				->setScalingMessage(static::getScalingMessage())
				->setKeepOriginal(true);
			$this->addFormElement($imageElement);
			$this->addFormElement(new Editor('content', 'Content'));
		}

		/**
		 * Toggles active for this blog article, and updates the sitemap accordingly
		 * @param bool $bool If this article should be active or not
		 */
		public function set_active($bool = false)
		{
			// force type
			$bool = (bool) $bool;
			if($this->id && $this->getProperty('active') !== $bool)
			{
				$this->updateSitemap = true;
			}
			$this->setProperty('active', $bool);
		}

		/**
		 *    to save the client having to generate and upload two different sized images for some designs
		 *    (eg square thumbnail/preview, rectangular full article image) we just have them upload and
		 *    crop for the thumbnail property and keep the original to assign to the image property here
		 * @param Image[] $obj
		 */
		public function set_imageUpload($obj = [])
		{
			$this->image = $obj[0];
			$this->thumbnail = $obj[1];
		}

		/**
		 * Gets the path where this article can be found on the site
		 * @return string the path of this article
		 */
		public function get_path()
		{
			// current() returns the item at the pointer which by default is the first item in the array of paths
			return current($this->getIndexedPaths());
		}

		/**
		 * Gets the blog module page
		 * @return Page The blog module page
		 */
		public function get_page()
		{
			return Page::loadFor('module', static::PAGE_TYPE);
		}

		/**
		 * Gets the article immediately before this one
		 *
		 * @return BlogArticle
		 */
		public function getPrevious()
		{
			$formatedDate = $this->date->format('Y-m-d H:i:s');
			$query = "SELECT * "
				. "FROM ~TABLE "
				. "WHERE ~active = true "
				. "AND (~date > ? OR (~date = ? AND ~id > ?))"
				. "ORDER BY ~date ASC, ~id ASC "
				. "LIMIT 1";

			return static::makeOne($query, [$formatedDate, $formatedDate, $this->id]);
		}

		/**
		 * Gets the article immediately after this one
		 *
		 * @return BlogArticle
		 */
		public function getNext()
		{
			$formatedDate = $this->date->format('Y-m-d H:i:s');
			$query = "SELECT * "
				. "FROM ~TABLE "
				. "WHERE ~active = true "
				. "AND (~date < ? OR (~date = ? AND ~id < ?))"
				. "ORDER BY ~date DESC, ~id DESC "
				. "LIMIT 1";

			return static::makeOne($query, [$formatedDate, $formatedDate, $this->id]);
		}

		/**
		 * Gets the page number to append to a "back" link or breadcrumb
		 * sql based on http://www.tech-recipes.com/rx/17470/mysql-how-to-get-row-number-order-5/
		 *
		 * @todo this really needs to be made generic and go with the loadPages() methods in Entity
		 * @todo change get_path() so it can take an article as a parameter and append the appropriate ?page=n
		 *
		 * @return int
		 */
		public function getPageNumber()
		{
			// san check
			if(!$this->active)
			{
				// go to first page
				return 1;
			}
			// else
			// set up a temporary user defined variable
			Database::query('set @row_num = 0');
			// set up a temporary table containing the indexes of the active articles if they
			// were all on one page
			Database::query('CREATE TEMPORARY TABLE `blog_row_numbers` ' . static::processQuery(
					'SELECT @row_num := @row_num + 1 AS `row_number`, ~id AS `id` from ~TABLE '
					. 'WHERE ~active = true '
					. 'ORDER BY ~date DESC, ~id DESC'
				));

			// query the temporary table using a litle math to get the page number from the row number
			// for this article
			$result = Database::query(static::processQuery('SELECT '
				. 'CEIL(`row_number` / ' . ARTICLES_PER_PAGE . ') as page_number '
				. 'FROM `blog_row_numbers` WHERE `id` = ? LIMIT 1'), [$this->id]
			);

			// return page number
			return $result[0]['page_number'];
		}

		/**
		 * return paths to active pages for inclusion in sitemap
		 * @return string[]
		 */
		static function getSitemapUrls()
		{
			$paths = [];

			$items = static::loadAllFor('active', true, ['date' => false]);

			/** @var BlogArticle $obj */
			foreach($items as $obj)
			{
				$paths[] = $obj->path;
			}

			return $paths;
		}
	}

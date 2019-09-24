<?php
		namespace Projects;

	use Configuration\AdminNavItem;
	use Configuration\AdminNavItemGenerator;
	use DatabaseObject\Generator;
	use DatabaseObject\Property\Property;
	use DatabaseObject\Property\ImageProperty;
	use DatabaseObject\Column;
	use DatabaseObject\Property\LinkFromMultipleProperty;
	use DatabaseObject\FormElement\GridElement;

	use DatabaseObject\FormElement\Text;
	use DatabaseObject\FormElement\Textarea;
	use DatabaseObject\FormElement\Editor;
	use DatabaseObject\FormElement\ImageElement;

	use DateTime;
	use Files\Image;
	use Pages\Page;
	use Database\Database;
	use SitemapItem;
	use Configuration\Registry;
	use Slide;


	class Project extends Generator implements SitemapItem, AdminNavItemGenerator, Slide
	{
		const TABLE = 'projects';
		const ID_FIELD = 'project_id';
		const SINGULAR = 'project';
		const PLURAL = 'projects';
		const HAS_ACTIVE = true;
		const HAS_POSITION = true;
		const LABEL_PROPERTY = 'title';
		const SLUG_PROPERTY = "title";
		const PATH_PARENT = "page";
		const CONTAINS_MULTIPLE = CONTAINS_MULTIPLE;

		const IMAGES_LOCATION = DOC_ROOT . "/resources/images/projects/";

		const IMAGE_WIDTH = PAGE_BANNER_WIDTH;
		const IMAGE_HEIGHT = PAGE_BANNER_HEIGHT;
		const IMAGE_RESIZE_TYPE = ImageProperty::CROP;

		const RESPONSIVE_IMAGE_WIDTH = PAGE_SLIDESHOW_RESPONSIVE_WIDTH;
		const RESPONSIVE_IMAGE_HEIGHT = PAGE_SLIDESHOW_RESPONSIVE_HEIGHT;
		const RESPONSIVE_IMAGE_RESIZE_TYPE = ImageProperty::CROP;

		// matches the type of page as selected in the Page Setup tab
		// @see get_path()
		const PAGE_TYPE = 'Projects';
		public $title = ''
		, $summary = ''
		, $content = ''
		, $path = ''
		, $subtitle = ''
		, $button = '';

		public $attribution = null;

		public $date = null;

		public $image = null;
		public $responsiveImage = null;

		public $thumbnail = null;

		public $updateSitemap = false;

		/** @var ProjectPhoto[] */
		public $photos = null;
		public $activePhotos = [];

		public $_activePhotos = null;


		/**
		 * Gets the array of Properties that determine how this Object interacts with the database
		 */
		protected static function properties()
		{
			parent::properties();

			static::addProperty(new Property('title', 'title', 'string'));
			static::addProperty(new Property('date', 'date', 'date'));
			static::addProperty(new Property('attribution', 'attribution', 'string'));
			static::addProperty(new Property('summary', 'summary', 'string'));
			static::addProperty(new Property('content', 'content', 'html'));
			static::addProperty(new ImageProperty('image', 'banner', static::IMAGES_LOCATION, static::IMAGE_WIDTH, static::IMAGE_HEIGHT, static::IMAGE_RESIZE_TYPE));
			// static::addProperty(new ImageProperty('photo', 'photo', static::IMAGES_LOCATION, static::IMAGE_WIDTH, static::IMAGE_HEIGHT, static::IMAGE_RESIZE_TYPE));
			static::addProperty(new Property("path"));
			static::addProperty(new LinkFromMultipleProperty("photos", ProjectPhoto::class, "project"));
			static::addProperty(new LinkFromMultipleProperty("thumbnail", ProjectPhoto::class, "project"));
			static::addProperty(new Property("activePhotos"));
		}

		/**
		 * Sets the array of Columns that are displayed to the user for this object type
		 */
		protected static function columns()
		{
			static::addColumn(new Column('Project', 'title'));
			parent::columns();
		}


		/**
		 * Loads all the Generators to be displayed in the table
		 * @return    static[]    The array of Generators
		 */
		public static function loadAllForTable()
		{
			return static::loadAll(['position' => true]);
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
				. "ORDER BY ~id DESC  "
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
			$scalingMessage = 'This image will be ' . (static::IMAGE_RESIZE_TYPE === ImageProperty::CROP ? 'cropped' : 'scaled') . ' down to a maximum width of '.static::IMAGE_WIDTH.' pixels and a maximum height of '.static::IMAGE_HEIGHT.' pixels. <br />';

			return $scalingMessage;
		}

		/**
		 * Gets the mav item for this class
		 * @return    AdminNavItem        The admin nav item for this class
		 */
		public static function getAdminNavItem()
		{
			return new AdminNavItem(static::getAdminNavLink(), "Projects", [static::class], Registry::isEnabled("Projects"));
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
			$this->addFormElement((new Textarea('summary', 'Summary'))->setClasses('asymmetric'));

			$imageElement = (new ImageElement('imageUpload', 'Main Photo', [$this->image], static::IMAGE_RESIZE_TYPE, static::IMAGE_WIDTH, static::IMAGE_HEIGHT, false))
				->setClasses('asymmetric image-element')
				->setScalingMessage(static::getScalingMessage())
				->setKeepOriginal(true);
			$this->addFormElement($imageElement);

			// $this->addFormElement(new Text("metaTitle", 'Page Title'), "Metadata");
			// $this->addFormElement(new Textarea("metaDescription", 'Page Description'), "Metadata");

			$this->addFormElement(new GridElement('photos', 'Project Photos'), "Content");

			$this->addFormElement(new Editor('content', 'Content'));

			$this->addFormElement(new Text('attribution', 'Photo Attribution'));


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



		public function get_activePhotos()
		{
			if ($this->_activePhotos === null)
			{
				$this->_activePhotos = ProjectPhoto::loadAllForMultiple(
				[
					'project' => $this->id,
					'active' => true
				]);
			}

			return $this->_activePhotos;
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
			// $this->thumbnail = $obj[1];
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
		// public function getPrevious()
		// {
		// 	$formatedDate = $this->date->format('Y-m-d H:i:s');
		// 	$query = "SELECT * "
		// 		. "FROM ~TABLE "
		// 		. "WHERE ~active = true "
		// 		. "AND (~date > ? OR (~date = ? AND ~id > ?))"
		// 		. "ORDER BY ~date ASC, ~id ASC "
		// 		. "LIMIT 1";
		//
		// 	return static::makeOne($query, [$formatedDate, $formatedDate, $this->id]);
		// }

		/**
		 * Gets the article immediately after this one
		 *
		 * @return BlogArticle
		 */
		// public function getNext()
		// {
		// 	$formatedDate = $this->date->format('Y-m-d H:i:s');
		// 	$query = "SELECT * "
		// 		. "FROM ~TABLE "
		// 		. "WHERE ~active = true "
		// 		. "AND (~date < ? OR (~date = ? AND ~id < ?))"
		// 		. "ORDER BY ~date DESC, ~id DESC "
		// 		. "LIMIT 1";
		//
		// 	return static::makeOne($query, [$formatedDate, $formatedDate, $this->id]);
		// }

		/**
		 * Gets the page number to append to a "back" link or breadcrumb
		 * sql based on http://www.tech-recipes.com/rx/17470/mysql-how-to-get-row-number-order-5/
		 *
		 * @todo this really needs to be made generic and go with the loadPages() methods in Entity
		 * @todo change get_path() so it can take an article as a parameter and append the appropriate ?page=n
		 *
		 * @return int
		 */
		// public function getPageNumber()
		// {
		// 	// san check
		// 	if(!$this->active)
		// 	{
		// 		// go to first page
		// 		return 1;
		// 	}
		// 	// else
		// 	// set up a temporary user defined variable
		// 	Database::query('set @row_num = 0');
		// 	// set up a temporary table containing the indexes of the active articles if they
		// 	// were all on one page
		// 	Database::query('CREATE TEMPORARY TABLE `blog_row_numbers` ' . static::processQuery(
		// 			'SELECT @row_num := @row_num + 1 AS `row_number`, ~id AS `id` from ~TABLE '
		// 			. 'WHERE ~active = true '
		// 			. 'ORDER BY ~date DESC, ~id DESC'
		// 		));
		//
		// 	// query the temporary table using a litle math to get the page number from the row number
		// 	// for this article
		// 	$result = Database::query(static::processQuery('SELECT '
		// 		. 'CEIL(`row_number` / ' . ARTICLES_PER_PAGE . ') as page_number '
		// 		. 'FROM `blog_row_numbers` WHERE `id` = ? LIMIT 1'), [$this->id]
		// 	);
		//
		// 	// return page number
		// 	return $result[0]['page_number'];
		// }

		/**
		 * return paths to active pages for inclusion in sitemap
		 * @return string[]
		 */
		static function getSitemapUrls()
		{
			$paths = [];

			$items = static::loadAllFor('active', true, ['date' => false]);

			foreach($items as $obj)
			{
				$paths[] = $obj->path;
			}

			return $paths;
		}

		// Slide methods

		/**
		 * Gets the image for this slide
		 * @return    Image    The image for this slide
		 */
		public function getSlideImage(): ?Image
		{
			return $this->image;
		}

		/**
		 * Gets the responsive image for this slide
		 *
		 * Can always return null. Template uses this to just output non-responsive code without caring
		 * why there is only one image (not enabled or not uploaded)
		 *
		 * @return    Image    The smaller image for this slide
		 */
		public function getSmallScreenImage(): ?Image
		{
			return static::CONTAINS_MULTIPLE ? $this->responsiveImage : null;
		}

		/**
		 * Gets the caption for this slide
		 * @return    string    The caption for this slide
		 */
		public function getSlideText(): string
		{
			$html = "";

			// if($this->title !== '')
			// {
			// 	$html .= "<h2>\n";
			// 	$html .= nl2br($this->title) . "\n";
			// 	$html .= "</h2>\n";
			// }
			//
			// if($this->subtitle !== '')
			// {
			// 	$html .= "<p>\n";
			// 	$html .= nl2br($this->subtitle) . "\n";
			// 	$html .= "</p>\n";
			// }
			//
			// // if button field not empty, create a button
			// if($this->button !== "")
			// {
			// 	$buttonText = static::DEFAULT_BUTTON_TEXT;
			//
			// 	if($this->button !== "")
			// 	{
			// 		$buttonText = $this->button;
			// 	}
			//
			// 	// if no link set, default to contact page
			// 	if($this->link !== "")
			// 	{
			// 		$buttonLink = $this->link;
			// 	}
			// 	else
			// 	{
			// 		$buttonLink = "Contact/";
			// 	}
			//
			// 	$html .= "<a href='/" . $buttonLink . "' class='button light'>" . $buttonText . "</a>\n";
			// }

			return $html;
		}

	}

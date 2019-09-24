<?php
	/*****
	 * The class file for the Gallery class
	 *		A basic collection of images which can be presented as a gallery or slideshow
	 *
	 * @copyright	2016 Activate Design
	 * @author	Robert Urquhart <programmer@activatedesign.co.nz>
	 * @version 	3.0
	 **/
	namespace Galleries;

	use Configuration\AdminNavItem;
	use Configuration\AdminNavItemGenerator;
	use Configuration\Registry;
	use Database\QueryException;
	use DatabaseObject\Column;
	use DatabaseObject\FormElement\Element;
	use DatabaseObject\FormElement\GridElement;
	use DatabaseObject\Generator;
	use DatabaseObject\Property\LinkFromMultipleProperty;
	use DatabaseObject\Property\Property;

	use DatabaseObject\FormElement\Checkbox;
	use DatabaseObject\FormElement\BasicEditor;
	use DatabaseObject\FormElement\Text;
	use Publishable;
	use ShortCode;
	use ShortCodeSupport;
	use Controller\Twig;

	/**
	 * Basic Gallery module
	 */
	class Gallery extends Generator implements Publishable, ShortCodeSupport, AdminNavItemGenerator
	{
		//Object
		const TABLE = 'galleries';
		const ID_FIELD = 'gallery_id';

		//Generator
		const SINGULAR = 'gallery';
		const PLURAL = 'galleries';
		const LABEL_PROPERTY = "title";

		const HOME_GALLERY_ID = 1;

		// Gallery
		public $title = '';

		/// collections of objects
		/** @var GalleryItem[] */
		public $galleryItems = null;

		/** @var  GalleryItem[] */
		private $_activeImages;

		/**
		 * Sets the array of Properties that determine how this Object interacts with the database
		 */
		protected static function properties()
		{
			parent::properties();

			static::addProperty(new LinkFromMultipleProperty("galleryItems", GalleryItem::class, "gallery"));
			static::addProperty(new Property('title','title','string'));
		}

		/**
		 * Sets the array of Columns that are displayed to the user for this object type
		 */
		protected static function columns()
		{
			static::addColumn(new Column('Gallery', 'title'));

			static::addColumn(new Column('Include code',function(Gallery $gallery)
			{
				return ShortCode::generate($gallery);
			}));

			parent::columns();
			static::removeColumn('Name');
		}

		/**
		 * Sets the Form Elements for this object
		 */
		protected function formElements()
		{
			parent::formElements();

			$this->removeFormElement("active");
			$this->removeFormElement("name");

			$this->addFormElement((new Text('title', 'Gallery Title'))->addValidation(Element::REQUIRED));
			$this->addFormElement(new GridElement('galleryItems', 'Images'));
		}

		/**
		 * lazy-loading only active images
		 *
		 * @return GalleryItem[]
		 */
		public function get_activeImages()
		{
			if(!isset($this->_activeImages))
			{
				$this->_activeImages = GalleryItem::loadActiveImagesForGallery($this);
			}

			return $this->_activeImages;
		}

		/**
		 * turn the Introduction into a short description suitable for gallery list page
		 *
		 * @return string
		 */
		public function get_description()
		{
			$str = strip_tags($this->getProperty('content'));
			return substr($str,0,100)
				. ((strlen($str) > 100) ?  '&hellip;' : '')
				;
		}

		/**
		 * Deletes the Entity from the database
		 * @param	bool	$startTransaction	Whether this delete should start a new transaction
		 * @throws    QueryException    If the query fails
		 */
		public function delete($startTransaction = true)
		{
			foreach($this->galleryItems as $galleryItem)
			{
				// this will also delete the images
				$galleryItem->delete(false);
			}

			parent::delete();
		}

		/*****
		 * html generators
		 **/

		/*****
		 * Publishes interface methods
		 **/
		/**
		 * is this public? Method intended to be queried by other classes which may want to
		 * 	pull in this content before calling output()
		 *
		 * @return bool
		 */
		public function canShow()
		{
			return $this->active;
		}

		/**
		 * Format and output properties as a coherent string of HTML
		 *
		 * @return string
		 */
		public function output()
		{
			return Twig::render("galleries/gallery.twig",
			[
				"gallery" => $this
			]);
		}

		//region AdminNavItemGenerator

		/**
		 * Gets the nav item for this class
		 * @return    AdminNavItem        The admin nav item for this class
		 */
		public static function getAdminNavItem()
		{
			return new AdminNavItem(static::getAdminNavLink(), "Galleries", [static::class], Registry::isEnabled("Galleries"));
		}

		//endregion

		//region ShortCodeSupport

		/**
		 * Gets a unique identifier for this class
		 * @return    string    An identifier that uniquely identifies this class
		 */
		public static function getClassShortcodeIdentifier()
		{
			return "Gallery";
		}

		/**
		 * Loads an object for this class, given an identifier
		 * @param    string $identifier The identifier to load from
		 * @return    Publishable                    An object that can be outputted to the page, or null if the correct one cannot be found
		 */
		public static function loadForShortcodeIdentifier($identifier)
		{
			$gallery = static::loadForMultiple(
			[
				"id" => $identifier
			]);

			return $gallery->isNull() ? null : $gallery;
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

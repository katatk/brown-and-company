<?php
	/**
	 * The class file for the Testimonial X class
	 * @copyright	2015 Activate Design
	 */
	
	use Configuration\AdminNavItem;
	use Configuration\AdminNavItemGenerator;
	use Configuration\Registry;
	use DatabaseObject\FormElement\ImageElement;
	use DatabaseObject\Generator;
	use DatabaseObject\Property\Property;
	use DatabaseObject\Property\ImageProperty;
	use DatabaseObject\Column;

	use DatabaseObject\FormElement\Text;
	use DatabaseObject\FormElement\Textarea;


	/**
	 * Basic class for accessing Testimonial records
	 * @author	Callum Muir
	 */
	class Testimonial extends Generator implements AdminNavItemGenerator
	{
		/** @var	string	The name of the table that stores the testimonial data */
		const TABLE = "testimonials";

		/** @var	string	The database name for the primary key of the database data */
		const ID_FIELD = "testimonial_id";

		const SINGULAR = 'testimonial';
		const PLURAL = 'testimonials';

		/** @var	bool	Whether Testimonials use positioning */
		const HAS_POSITION = true;
		const HAS_ACTIVE = true;
		const LABEL_PROPERTY = 'witness';
		
		const IMAGE_LOCATION = DOC_ROOT . "/resources/images/testimonial/";
		const IMAGE_WIDTH = TESTIMONIAL_IMAGE_WIDTH;
		const IMAGE_HEIGHT = TESTIMONIAL_IMAGE_HEIGHT;
		const IMAGE_SCALE_TYPE = ImageProperty::SCALE;

		public $witness = "";
		public $testimony = "";
		public $image = null;

		/**
		 * Sets the properties for a Testimonial
		 */
		protected static function properties()
		{
			parent::properties();

			static::addProperty(new Property("witness", "witness", "string"));
			static::addProperty(new Property("testimony", "testimony", "string"));
			static::addProperty(new ImageProperty('image', 'image', static::IMAGE_LOCATION, static::IMAGE_WIDTH, static::IMAGE_HEIGHT, static::IMAGE_SCALE_TYPE));
		}
		
		/**
		 * Sets the array of Columns that are displayed to the user for this object type
		 */
		 protected static function columns()
		{
			static::addColumn(new Column('Witness', 'witness'));

			parent::columns();
		}

		/**
		 * Gets all the active testimonials
		 * @return	static[]	Said testimonials
		 */
		public static function loadAllActive()
		{
			return static::loadAllFor("active", true, ["position" => true]);
		}
		
		/**
		 * Gets a random Testimonial
		 * @return	Testimonial		A random Testimonial
		 */
		public static function loadRandom()
		{
			$query = "SELECT ~PROPERTIES "
				   . "FROM ~TABLE "
				   . "ORDER BY RAND() "
				   . "LIMIT 1";

			return static::makeOne($query);
		}
		
		/**
		 * Sets the Form Elements for this object
		 */
		 protected function formElements()

		{
			parent::formElements();

			$this->addFormElement(new Text('witness', 'Customer name'));
			$this->addFormElement(new Textarea('testimony', 'Testimonial'));

			if (TESTIMONIAL_IMAGE)
			{
				$this->addFormElement(new ImageElement("image", 'Image', $this->image));
			}
		}
		
		//region AdminNavItemGenerator
		
		/**
		 * Gets the nav item for this class
		 * @return    AdminNavItem        The admin nav item for this class
		 */
		public static function getAdminNavItem()
		{
			return new AdminNavItem(static::getAdminNavLink(), "Testimonials", [static::class], Registry::isEnabled("Testimonials"));
		}
		
		//endregion
	}

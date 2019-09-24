<?php
	namespace Projects;

	use DatabaseObject\Generator;
	use DatabaseObject\Property\ImageProperty;
	use DatabaseObject\Property\Property;
	use DatabaseObject\Property\LinkToProperty;

	use DatabaseObject\FormElement\Text;
	use DatabaseObject\FormElement\ImageElement;
	use DatabaseObject\FormElement\Hidden;
	use Files\Image;


	class ProjectPhoto extends Generator
	{
		const TABLE = "project_photos";
		const ID_FIELD = "project_photo_id";
		const PARENT_PROPERTY = "project";
		const SINGULAR = 'photo';
		const PLURAL = 'photos';
		const HAS_ACTIVE = true;
		const HAS_POSITION = true;

		const IMAGES_LOCATION = DOC_ROOT . "/resources/images/projects/";
		const IMAGE_WIDTH = PROJECT_SLIDE_WIDTH;
		const IMAGE_HEIGHT = PROJECT_SLIDE_HEIGHT;
		const IMAGE_RESIZE_TYPE = ImageProperty::SCALE;

		const THUMBNAIL_WIDTH = PROJECT_WIDTH;
		const THUMBNAIL_HEIGHT = PROJECT_HEIGHT;
		const THUMBNAIL_RESIZE_TYPE = ImageProperty::CROP;

		/** @var Image */
		public $photo = null;
		// public $attribution = null;

		/** @var Image */
		public $thumbnail = null;

		/** @var Project */
		public $project = null;
		public $active = true;



		/**
		 * Gets the array of Properties that determine how this Object interacts with the database
		 */
		protected static function properties()
		{
			parent::properties();
			static::addProperty(new LinkToProperty("project", "project_id", Project::class));
			// static::addProperty(new Property('attribution', 'attribution', 'string'));
			static::addProperty(new ImageProperty('photo', 'photo', static::IMAGES_LOCATION, static::IMAGE_WIDTH, static::IMAGE_HEIGHT, static::IMAGE_RESIZE_TYPE));
			static::addProperty(new ImageProperty('thumbnail', 'thumbnail', static::IMAGES_LOCATION, static::THUMBNAIL_WIDTH, static::THUMBNAIL_HEIGHT, static::THUMBNAIL_RESIZE_TYPE));
		}

		/**
		 * Gets a scaling message to display to the user
		 * @return	string	The scaling message
		 */
		public static function getScalingMessage()
		{
			// assuming a maximum width is always defined for full-size images and both dimensions
			// are aways defined for thumbnail images
			$scalingMessage = 'This image will be '
				. (static::IMAGE_RESIZE_TYPE === ImageProperty::SCALE ? 'scaled' : 'cropped')
				. ' down to a maximum width of '
				. static::IMAGE_WIDTH
				. ' pixels'
				. (static::IMAGE_HEIGHT > 0 ? ' and a maximum height of '
				. static::IMAGE_HEIGHT
				. ' pixels' : '')
				;
			return $scalingMessage;
		}

		/**
		 * Sets the Form Elements for this object
		 */
		protected function formElements()
		{
			parent::formElements();

			$imageElement = new ImageElement("imageUpload", 'Photo', [$this->photo, $this->thumbnail], static::IMAGE_RESIZE_TYPE, static::IMAGE_WIDTH, static::IMAGE_HEIGHT, false);
			$imageElement->setKeepOriginal(true);
			$imageElement->setScalingMessage(static::getScalingMessage());
			$imageElement->setIsRepresentative(true);
			$this->addFormElement($imageElement);

			// $this->addFormElement(new Text('attribution', 'Attribution'));

			if($this->id === null)
			{
				$this->addFormElement(new Hidden("project", '', $this->project->id));
			}
		}

		/**
		 * take multiple uploaded images and assign them to this Page
		 * @param \Files\Image[] $obj
		 */
		public function set_imageUpload($obj = [])
		{
			$this->photo = $obj[0];

			if(isset($obj[1]))
			{
				$this->thumbnail = $obj[1];
			}
			else
			{
				$this->thumbnail = $obj[0];
			}
		}
	}

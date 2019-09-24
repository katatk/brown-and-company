<?php
	namespace Products;

	use DatabaseObject\Generator;
	use DatabaseObject\Property\ImageProperty;
	use DatabaseObject\Property\LinkToProperty;

	use DatabaseObject\FormElement\ImageElement;
	use DatabaseObject\FormElement\Hidden;
	use Files\Image;
	
	/**
	 * A single image for a product
	 * @author	Callum Muir <callum@activatedesign.co.nz>
	 */
	class ProductImage extends Generator
	{
		const TABLE = "product_images";
		const ID_FIELD = "product_image_id";
		const PARENT_PROPERTY = "product";
		const SINGULAR = 'image';
		const PLURAL = 'images';
		const HAS_ACTIVE = true;
		const HAS_POSITION = true;
		
		const IMAGES_LOCATION = DOC_ROOT . "/resources/images/product/";
		const IMAGE_WIDTH = PRODUCT_IMAGE_WIDTH;
		const IMAGE_HEIGHT = PRODUCT_IMAGE_HEIGHT;
		const IMAGE_RESIZE_TYPE = ImageProperty::SCALE;
		const THUMBNAIL_WIDTH = PRODUCT_THUMBNAIL_WIDTH;
		const THUMBNAIL_HEIGHT = PRODUCT_THUMBNAIL_HEIGHT;
		const THUMBNAIL_RESIZE_TYPE = ImageProperty::CROP;

		/** @var Image */
		public $image = null;
		
		/** @var Image */
		public $thumbnail = null;

		/** @var Product */
		public $product = null;
		public $active = true;
		
		/**
		 * Gets the array of Properties that determine how this Object interacts with the database
		 */
		protected static function properties()
		{
			parent::properties();
			static::addProperty(new LinkToProperty("product", "product_id", Product::class));
			static::addProperty(new ImageProperty('image', 'image', static::IMAGES_LOCATION, static::IMAGE_WIDTH, static::IMAGE_HEIGHT, static::IMAGE_RESIZE_TYPE));
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
				. '.<br /> A '
				. (static::THUMBNAIL_RESIZE_TYPE === ImageProperty::SCALE ? 'scaled' : 'cropped')
				. ' thumbnail will be created with a maximum width of '
				. static::THUMBNAIL_WIDTH
				. ' pixels and a maximum height of '
				. static::THUMBNAIL_HEIGHT
				. ' pixels.'
				;
			return $scalingMessage;
		}
		
		/**
		 * Sets the Form Elements for this object
		 */
		protected function formElements()
		{
			parent::formElements();

			$imageElement = new ImageElement("imageUpload", 'Image', [$this->image, $this->thumbnail], static::THUMBNAIL_RESIZE_TYPE, static::THUMBNAIL_WIDTH, static::THUMBNAIL_HEIGHT, false);
			$imageElement->setKeepOriginal(true);
			$imageElement->setScalingMessage(static::getScalingMessage());
			$imageElement->setIsRepresentative(true);
			$this->addFormElement($imageElement);

			if($this->id === null)
			{
				$this->addFormElement(new Hidden("product", '', $this->product->id));
			}
		}

		/**
		 * take multiple uploaded images and assign them to this Page
		 * @param \Files\Image[] $obj
		 */
		public function set_imageUpload($obj = [])
		{
			$this->image = $obj[0];

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

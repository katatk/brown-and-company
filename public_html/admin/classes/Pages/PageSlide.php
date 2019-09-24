<?php
	/**
	 * The class file for the PageSlide class
	 *
	 * @copyright	2016 Activate Design
	 * @author		Robert Urquhart <programmer@activatedesign.co.nz>
	 * @version 	3.0
	 */
	namespace Pages;

	use Database\QueryException;
	use DatabaseObject\Entity;
	use DatabaseObject\FormElement\ImageElement;
	use DatabaseObject\Generator;
	use DatabaseObject\Property\LinkToProperty;
	use DatabaseObject\Property\Property;
	use DatabaseObject\Property\ImageProperty;
	use DatabaseObject\FormElement\Hidden;
	use DatabaseObject\FormElement\Text;
	use Files\Image;
	use Slide;

	/**
	 * Class for managing Page -> Slideshow Image relationships
	 */
	class PageSlide extends Generator implements Slide
	{
		// Object
		const TABLE = "page_slides";
		const ID_FIELD = "page_slide_id";

		// Generator
		const SINGULAR = "slide";
		const PLURAL = "slides";
		const LABEL_PROPERTY = 'title';

		const HAS_ACTIVE = true;
		const HAS_POSITION = true;

		const PARENT_PROPERTY = 'page';
		const HAS_AUTOCAST = true;

		const DEFAULT_BUTTON_TEXT = "View";

		const CONTAINS_MULTIPLE = PAGE_SLIDE_HAS_MULTIPLE_IMAGES;
		const IMAGES_LOCATION = DOC_ROOT . "/resources/images/page/";

		const DESKTOP_IMAGE_WIDTH = PAGE_SLIDESHOW_WIDTH;
		const DESKTOP_IMAGE_HEIGHT = PAGE_SLIDESHOW_HEIGHT;
		const DESKTOP_IMAGE_RESIZE_TYPE = ImageProperty::CROP;

		const RESPONSIVE_IMAGE_WIDTH = PAGE_SLIDESHOW_RESPONSIVE_WIDTH;
		const RESPONSIVE_IMAGE_HEIGHT = PAGE_SLIDESHOW_RESPONSIVE_HEIGHT;
		const RESPONSIVE_IMAGE_RESIZE_TYPE = ImageProperty::CROP;

		public $active = true;

		// PageSlide
		/** @var Page */
		public $page = null;

		/** @var Image */
		public $image = null;
		public $responsiveImage = null;
		public $title = '';
		public $subtitle = '';
		public $description = '';
		public $link = '';
		public $button = "";

		/**
		 * Gets the array of Database Object Properties that determine how an Image interacts with the database
		 */
		protected static function properties()
		{
			parent::properties();

			static::addProperty(new LinkToProperty("page", "page_id", Page::class));
			static::addProperty(new Property('title', 'title', 'string'));
			static::addProperty(new Property('subtitle', 'subtitle', 'string'));
			static::addProperty(new Property("link", "link", "string"));
			static::addProperty(new Property("button", "button", "string"));
			static::addProperty(new ImageProperty('image', 'image', static::IMAGES_LOCATION, static::DESKTOP_IMAGE_WIDTH, static::DESKTOP_IMAGE_HEIGHT, static::DESKTOP_IMAGE_RESIZE_TYPE));
			// don't need to make the property conditional, can just always be null.
			static::addProperty(new ImageProperty('responsiveImage', 'responsive_image', static::IMAGES_LOCATION, static::RESPONSIVE_IMAGE_WIDTH, static::RESPONSIVE_IMAGE_HEIGHT, static::RESPONSIVE_IMAGE_RESIZE_TYPE));
		}

		public static function getScalingMessage()
		{
			$scalingMessage = 'For a banner: simply add one image. This image will be ' . (static::DESKTOP_IMAGE_RESIZE_TYPE === ImageProperty::CROP ? 'cropped' : 'scaled') . ' down to a maximum width of '.static::DESKTOP_IMAGE_WIDTH.' pixels and a maximum height of '.static::DESKTOP_IMAGE_HEIGHT.' pixels. <br />';

			return $scalingMessage;
		}

		/**
		 * Sets the Form Elements for this object
		 */
		protected function formElements()
		{
			parent::formElements();

			if(static::CONTAINS_MULTIPLE)
			{
				$this->addFormElement((new ImageElement('image', 'Image'))->setIsRepresentative(true)->setClasses('half first'));
				$this->addFormElement((new ImageElement('responsiveImage', 'Responsive Image (phones, tablets) <span>Optional</span>'))->setClasses('half'));
			}
			else
			{
				$imageElement = (new ImageElement('image', 'Image'))
				->setIsRepresentative(true)
				->setScalingMessage(static::getScalingMessage());
				$this->addFormElement($imageElement);
			}

			if (PAGE_SLIDESHOW_CAPTION)
			{
				$this->addFormElement(new Text('title', 'Title <span>(optional)</span>'));
				$this->addFormElement(new Text('subtitle', 'Sub Title <span>(optional)</span>'));
			}

			if(PAGE_SLIDESHOW_LINK)
			{
				$this->addFormElement(new Text("link", "Link <span>(optional)</span>"));

				if(PAGE_SLIDESHOW_BUTTON)
				{
					$this->getFormElements()['link']->setClasses('half first');
					$this->addFormElement((new Text("button", "Button text <span>(optional, if no link set, button will link to contact page)</span>"))->setClasses('half'));
				}
			}
		}

		//region Slide

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

			if($this->title !== '')
			{
				$html .= "<h2>\n";
				$html .= nl2br($this->title) . "\n";
				$html .= "</h2>\n";
			}

			if($this->subtitle !== '')
			{
				$html .= "<p>\n";
				$html .= nl2br($this->subtitle) . "\n";
				$html .= "</p>\n";
			}

			// if button field not empty, create a button
			if($this->button !== "")
			{
				$buttonText = static::DEFAULT_BUTTON_TEXT;

				if($this->button !== "")
				{
					$buttonText = $this->button;
				}

				// if no link set, default to contact page
				if($this->link !== "")
				{
					$buttonLink = $this->link;
				}
				else
				{
					$buttonLink = "Contact/";
				}

				$html .= "<a href='/" . $buttonLink . "' class='button light'>" . $buttonText . "</a>\n";
			}

			return $html;
		}

		//endregion


	}

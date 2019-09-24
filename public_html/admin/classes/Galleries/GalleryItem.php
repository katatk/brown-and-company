<?php

namespace Galleries;

use Database\QueryException;
use DatabaseObject\Entity;
use DatabaseObject\FormElement\ImageElement;
use DatabaseObject\Generator;
use DatabaseObject\Property\Property;
use DatabaseObject\Property\ImageProperty;
use DatabaseObject\Property\LinkToProperty;

use DatabaseObject\FormElement\Text;
use DatabaseObject\FormElement\Hidden;
use Files\Image;

/**
 * A single item in a gallery
 * @author	Sarah Bousfield <sarah@activatedesign.co.nz>
 */
class GalleryItem extends Generator
{
	const TABLE = 'gallery_items';
	const ID_FIELD = 'gallery_item_id';
	const SINGULAR = 'Image';
	const PLURAL = 'Images';
	const LABEL_PROPERTY = 'title';
	const HAS_ACTIVE = true;
	const HAS_POSITION = true;

	const PARENT_PROPERTY = 'gallery';

	public $active = true;

	/** @var Gallery */
	public $gallery = null;

	/** @var Image */
	public $image = null;
	public $title = '';

	/** @var Image */
	public $thumbnail = null;

	const IMAGES_LOCATION = DOC_ROOT . "/resources/images/gallery/";
	const IMAGE_WIDTH = GALLERY_IMAGE_WIDTH;
	const IMAGE_HEIGHT = GALLERY_IMAGE_HEIGHT;
	const IMAGE_RESIZE_TYPE = ImageProperty::SCALE;
	const THUMBNAIL_WIDTH = GALLERY_THUMBNAIL_WIDTH;
	const THUMBNAIL_HEIGHT = GALLERY_THUMBNAIL_HEIGHT;
	const THUMBNAIL_RESIZE_TYPE = ImageProperty::CROP;

	/**
	 * Gets the array of Properties that determine how this Object interacts with the database
	 */
	protected static function properties()
	{
		parent::properties();

		static::addProperty(new LinkToProperty("gallery", "gallery_id", Gallery::class));
		static::addProperty((new Property('title', 'title', 'string'))->setIsSearchable(true));
		static::addProperty(new ImageProperty('image', 'image', static::IMAGES_LOCATION, static::IMAGE_WIDTH, static::IMAGE_HEIGHT, static::IMAGE_RESIZE_TYPE));
		static::addProperty(new ImageProperty('thumbnail', 'thumbnail', static::IMAGES_LOCATION, static::THUMBNAIL_WIDTH, static::THUMBNAIL_HEIGHT, static::THUMBNAIL_RESIZE_TYPE));
	}

	/**
	 * load all the images in one Gallery
	 *
	 * @param Gallery $parent
	 *
	 * @return Image[]
	 */
	public static function loadImagesForGallery(Gallery $parent)
	{
		$return = [];
		foreach(static::loadAllForMultiple(['gallery' => $parent], []) as $obj)
		{
			$return[] = $obj;
		}

		return $return;
	}

	/**
	 * load all the public images in one Gallery
	 *
	 * @param Gallery $parent
	 *
	 * @return Image[]
	 */
	public static function loadActiveImagesForGallery(Gallery $parent)
	{
		$return = [];
		foreach(static::loadAllForMultiple(['gallery' => $parent->id,'active' => true], []) as $obj)
		{
			$return[] = $obj;
		}
		return $return;
	}

	/**
	 * Gets a scaling message for this item
	 * @return	string	The scaling message for this item
	 */
	public static function getScalingMessage()
	{
			$scalingMessage = 'This image will be '.(static::IMAGE_RESIZE_TYPE === ImageProperty::SCALE ? 'scaled' : 'cropped').' down to a maximum width of '.static::IMAGE_WIDTH.' pixels and a maximum height of '.static::IMAGE_HEIGHT.' pixels. <br />
							A '.(static::THUMBNAIL_RESIZE_TYPE === ImageProperty::SCALE ? 'scaled' : 'cropped').' thumbnail will be created with a maximum width of '.static::THUMBNAIL_WIDTH.' pixels and a maximum height of '.static::THUMBNAIL_HEIGHT.' pixels.';
		return $scalingMessage;
	}

	/**
	 * Sets the Form Elements for this object
	 */
	protected function formElements()
	{
		parent::formElements();
		$this->addFormElement(new Text('title', 'Title'));

		$imageElement = new ImageElement("imageUpload", 'Image', [$this->image, $this->thumbnail], static::THUMBNAIL_RESIZE_TYPE, static::THUMBNAIL_WIDTH, static::THUMBNAIL_HEIGHT, false);
		$imageElement->setKeepOriginal(true);
		$imageElement->setScalingMessage(static::getScalingMessage());
		$imageElement->setIsRepresentative(true);
		$this->addFormElement($imageElement);

		if($this->id === null)
		{
			$this->addFormElement(new Hidden("gallery", '', $this->gallery->id));
		}
	}

	/**
	 * Gets the width of the item
	 * @return	int		The width
	 */
	public function get_width()
	{
		return ($this->image !== null) ? $this->image->width : 0;
	}

	/**
	 * Gets the height of the item
	 * @return	int		The height
	 */
	public function get_height()
	{
		return ($this->image !== null) ? $this->image->height : 0;
	}


	/*****
	 * setters
	 **/

	/**
	 * take multiple uploaded images and assign them to this Page
	 * @param Image[] $obj
	 */
	public function set_imageUpload($obj = [])
	{
		$this->image = $obj[0];
		$this->thumbnail = $obj[1];
	}
}

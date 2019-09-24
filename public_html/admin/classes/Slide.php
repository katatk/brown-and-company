<?php
	use Files\Image;

	/**
	 * A Slide is something that can be displayed in the slideshow
	 * @author	Callum Muir <callum@activatedesign.co.nz>
	 */
	interface Slide
	{
		/**
		 * Gets the image for this slide
		 * @return	Image	The image for this slide
		 */
		public function getSlideImage(): ?Image;

		/**
		 * Gets the responsive image for this slide
		 *
		 * Can always return null. Template uses this to just output non-responsive code without caring
		 * why there is only one image (not enabled or not uploaded)
		 *
		 * @return    Image    The smaller image for this slide
		 */
		public function getSmallScreenImage(): ?Image;

		/**
		 * Gets the caption for this slide
		 * @return	string	The caption for this slide
		 */
		public function getSlideText(): string;
	}

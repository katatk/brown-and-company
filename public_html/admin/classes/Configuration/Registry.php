<?php
	namespace Configuration;

	use Blog\BlogArticle;
	use Forms\Form;
	use Galleries\Gallery;
	use GoogleMaps\GoogleMap;
	use Orders\LineItemGenerator;
	use Orders\Order;
	use Pages\Faqs\Faq;
	use Pages\Page;
	use Products\Category;
	use Products\Product;
	use Redirect;
	use Menus\Menu;
	use Testimonial;
	use Users\Administrator;
	use Users\User;

	/**
	 * Handles global module configuration
	 * Will eventually be an ORM object pulling settings from the database
	 *
	 * @author	Robert Urquhart <robert@activatedesign.co.nz>
	 * @copyright 2017 Activate Design
	 */
	class Registry
	{
		protected const ENABLED_MODULES =
		[
			"Administrators" => true,
			"Blog" => false,
			"Cart" => false,
			"Configuration" => true,
			"Discounts" => false,
			"FAQs" => false,
			"Forms" => true,
			"Galleries" => true,
			"HoverCart" => false,
			'Menus' => false,
			"Orders" => false,
			"Pages" => true,
			"Payments" => false,
			"Products" => false,
			"Shipping" => false,
			"Testimonials" => false,
			"Users" => false
		];

		protected const ALIASES =
		[
			"BlogArticle" => BlogArticle::class,
			"Faq" => Faq::class,
			"Form" => Form::class,
			"Gallery" => Gallery::class,
			"Map" => GoogleMap::class,
			"Page" => Page::class,
			"Product" => Product::class,
			"User" => User::class
		];

		const SEARCHABLE_CLASSES = [Page::class, Category::class, Product::class];

		/** @var string[]|ShortCodeSupport[] */
		const SHORTCODE_CLASSES = [Form::class, Gallery::class, GoogleMap::class, Page::class];
		const SITEMAP_CLASSES = [Page::class, Menu::class];
		const SLUGGED_CLASSES = [Page::class, Category::class, Product::class, Menu::class];

		/** @var string[]|LineItemGenerator[] */
		const LINE_ITEM_GENERATING_CLASSES = [Product::class];

		/**
		 * The page that shows up first in the admin
		 * @return	string	The path to the admin page
		 */
		public static function getDefaultView()
		{
			return Page::getAdminNavLink();
		}

		/**
		 * Gets the list of links for admin panel display
		 * @return	AdminNavItem[]		The nav items
		 */
		public static function getAdminNavItems()
		{
			/** @var AdminNavItem[] $items */
			$items =
			[
				Order::getAdminNavItem(),
				Category::getAdminNavItem(),
				Menu::getAdminNavItem(),
				Page::getAdminNavItem(),
				BlogArticle::getAdminNavItem(),
				Form::getAdminNavItem(),
				Gallery::getAdminNavItem(),
				Testimonial::getAdminNavItem(),
				Administrator::getAdminNavItem(),
				Configuration::getAdminNavItem(),
				Redirect::getAdminNavItem(),
				new AdminNavItem("https://www.activehost.co.nz/Help-Guides/CMS-Help", "Help", [], true, [], true)
			];

			return $items;
		}

		/**
		 * Check if a module is enabled
		 * @param	string	$moduleName		The name of the module
		 * @return	bool					Whether the module is enabled
		 */
		public static function isEnabled($moduleName)
		{
			assert(isset(static::ENABLED_MODULES[$moduleName]), "Module " . $moduleName . " is in list of modules");

			return static::ENABLED_MODULES[$moduleName];
		}

		/**
		 * Get the fully qualified class name from an alias
		 * @param	string	$alias	The alias to check
		 * @return	string			The class name for that alias, or the original string, if that alias doesn't exist
		 */
		public static function qualifyClassName($alias)
		{
			if(isset(static::ALIASES[$alias]))
			{
				return static::ALIASES[$alias];
			}

			return $alias;
		}
	}

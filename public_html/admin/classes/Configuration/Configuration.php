<?php
	namespace Configuration;

	use DatabaseObject\Generator;
	use DatabaseObject\Property\Property;
	use DatabaseObject\FormElement\Element;
	use DatabaseObject\FormElement\Text;
	use DatabaseObject\FormElement\Textarea;

	/**
	 * Handles global configuration
	 * @author	Callum Muir <callum@activatedesign.co.nz>
	 */
	class Configuration extends Generator implements AdminNavItemGenerator
	{
		const TABLE = "configuration";
		const ID_FIELD = "configuration_id";
		const SINGULAR = "configuration";
		const PLURAL = "configuration";
		const LABEL_PROPERTY = "siteName";
		const USE_TABS = true;

		/**
		 *	Default admin email address, used for dev sites
	   	 * @var string DEV_EMAIL send site error alerts etc to this address, usually leave this
	   	 *		as the address of the responsible Activate Design developer or programmer/support@ad
		*/
		const DEV_EMAIL = 'programmer@activatedesign.co.nz';

		/** @var Configuration $singleton */
		private static $singleton = null;

		public $siteName = '';
		public $phoneOne = '';
		public $phoneTwo = '';
		public $email = '';
		public $addressOneTitle = '';
		public $addressOne = '';
		public $addressTwoTitle = '';
		public $addressTwo = '';

		/**
		 * Sets the properties for this class
		 */
		protected static function properties()
		{
			parent::properties();

			static::addProperty(new Property('siteName', 'site_name', 'string'));
			static::addProperty(new Property('phoneOne', 'phone_one', 'string'));
			static::addProperty(new Property('phoneTwo', 'phone_two', 'string'));
			static::addProperty(new Property('email', 'email', 'string'));
			static::addProperty(new Property('addressOneTitle', 'address_one', 'string'));
			static::addProperty(new Property('addressOne', 'address_one', 'string'));
			static::addProperty(new Property('addressTwoTitle', 'address_two', 'string'));
			static::addProperty(new Property('addressTwo', 'address_two', 'string'));

		}

		/**
		 * Gets the Configuration, or, if it doesn't exist, creates it
		 * @return	Configuration	The configuration object
		 */
		public static function acquire()
		{
			if(static::$singleton === null)
			{
				$query = "SELECT ~PROPERTIES "
					   . "FROM ~TABLE "
					   . "LIMIT 1";

				static::$singleton = static::makeOne($query, []);

				if(static::$singleton->isNull())
				{
					static::$singleton = new static();
					static::$singleton->save();
				}
			}

			return static::$singleton;
		}

		/**
		 * Any attempt to get this object should return the singleton
		 * @param	array			$values		Unused
		 * @return	Configuration				The Configuration object
		 */
		public static function loadForMultiple(array $values)
		{
			return static::acquire();
		}

		/**
		 * Any attempt to get multiple of this object should return the singleton in an array
		 * @param	array			$values		Unused
		 * @param	bool[]			$orderBy	Unused
		 * @return	Configuration[]				An array containing the single Configuration object
		 */
		public static function loadAllForMultiple(array $values, array $orderBy = [])
		{
			return [static::acquire()];
		}

		/**
		 * Hides the return link
		 * @param   Generator	$generator	Unused
		 * @return	string					Empty string
		 */
		public static function returnLink(Generator $generator = null)
		{
			return "/admin";
		}

		/**
		 * Hides the add link
		 * @param   Generator	$generator	Unused
		 * @return	string					Empty string
		 */
		public static function addLink(Generator $generator = null)
		{
			return "";
		}

		/**
		 * Gets the appropriate site email address depending on if the site is a dev site or not
		 *
		 *	@return string 	email address
	   	 * 		- send emails for site visitors (eg confirmations) from this address
	   	 *		- send email for the site owner (eg from contact forms) to this address unless otherwise
	   	 *			defined
		 */
		public static function getAdminEmail()
		{
			return (IS_DEV_SITE) ? static::DEV_EMAIL : static::acquire()->email;
		}

		/**
		 * Gets the site name so we can just call this menthod instead of acquire()ing everywhere we need it
		 *
		 * @return string the site name
		 */
		public static function getSiteName()
		{
			return static::acquire()->siteName;
		}

		/**
		 * Preserving USER_NOTIFICATIONS_ADDRESS until the new users stuff gets here
		 *
		 * @return string USER_NOTIFICATIONS_ADDRESS email address to send notifications of details changes
	   	 *		(not addresses at present) when a user updates their own details. Set to '' to disable these
	   	 *		notifications
		*/
		public static function getUserNotificationEmail()
		{
			return static::getAdminEmail();
		}

		/**
		 * Sets the Form Elements for this object
		 */
		protected function formElements()
		{
			$this->addFormElement((new Text('siteName', 'Site Name <span>(appears in template locations and emails)</span>'))->addValidation(Element::REQUIRED), 'Contact Details');
			$this->addFormElement((new Text('email', 'Email'))->addValidation(Element::REQUIRED), 'Contact Details');

			$this->addFormElement(new Text('addressOneTitle', 'Address One Label'), 'Contact Details');
			$this->addFormElement(new Textarea('addressOne', 'Address One'), 'Contact Details');
			$this->addFormElement(new Text('phoneOne', 'Phone One'), 'Contact Details');

			$this->addFormElement(new Text('addressTwoTitle', 'Address Two Label'), 'Contact Details');
			$this->addFormElement(new Textarea('addressTwo', 'Address Two'), 'Contact Details');
			$this->addFormElement(new Text('phoneTwo', 'Phone Two'), 'Contact Details');

		}

		/**
		 * Same as in Generator, but without a return link
		 *
		 * @return	string	The HTML for the html above the form
		 */
		 public function generateEditFormHeading()
 		{
 			$labelProperty = static::LABEL_PROPERTY;

 			$html = "";
 			$html .= "<h1>\n";
 				$html .= "Edit " . ($labelProperty === "id" ? ucfirst(static::SINGULAR) : $this->$labelProperty) . "\n";
 			$html .= "</h1>\n";

 			return $html;
 		}

		/**
		 * Creates a nav at the top of the create and edit form pages
		 * 		by default includes links to
		 *			- add another item without having to click back to the table page
		 *			- breadcrumbs
		 *
		 * @param   Generator $generator	A Review that the return link returns from
		 * @return	string	html to be included in the nav
		 */
		public static function generateItemNav(Generator $generator = null)
		{
			$html = '';
			if($generator !== null)
			{
				$html .= static::addLink();
			}

			return $html;
		}

		/**
		 * Returns only digits of a phone number
		 * @param	string	$phone	The non processed phone number
		 * @return	string			The digits of the phone number
		 */
		public static function getDigits($phone)
		{
			return preg_replace("/[^0-9]/", "", $phone);
		}

		/**
		 * Converts a phone number to use the international format (for New Zealand)
		 * @param	$str	the phone number to format
		 * @return	string	the phone number in international format
		 */
		public static function formatAsInternationalPhone($str)
		{
			if(strpos($str,'+') === 0)
			{
				// already in international format
				$return = '+'.preg_replace('/[^\d]/','',$str);
			}
			else
			{
				$return = '+64'.ltrim(preg_replace('/[^\d]/','',$str),0);
			}

			return $return;
		}

		/**
		 * For update messages
		 *
		 * @deprecated with addition of siteName
		 *
		 * @return	string	The word "Configuration"
		 */
		public function get_label()
		{
			return "Configuration";
		}

		/**
		 * Gets the phone number digits
		 * @return	string	The phone number with all non-digit characters stripped out
		 */
		public function getPhoneDigits()
		{
			return static::getDigits($this->phoneOne);
		}

		public function getSecondaryPhoneDigits()
		{
			return static::getDigits($this->phoneTwo);
		}

		/**
		 * Retrieves the link to this Generator's table
		 * @return	string	The link to the table
		 */
		public static function getAdminNavLink()
		{
			return static::acquire()->getEditLink();
		}

		/**
		 * Get a google map for the configuration address
		 *
		 * @param Bool	$useBusinessName	If true the site name is prepended to the address,
		 * 		 and is then used as the label for the map pin.
		 *
		 * @return String 	html for an embeded map
		 */
		public function getMap($useBusinessName = false)
		{
			return static::makeMap($this->address, $useBusinessName);
		}

		/**
		 * Get a google map for an address
		 * @param	string	$address			Address to get a map for
		 * @param	bool	$useBusinessName	Whether to add the site name to the start of the address. This is unreliable and mostly requires them to have a business listing on Google.
		 * @return	string						HTML for the embedded map
		 */
		public static function makeMap($address = '', $useBusinessName = false)
		{
			if($address !== "")
			{
				$fullAddress = "";

				if($useBusinessName)
				{
					$fullAddress .= static::getSiteName() . ", ";
				}

				// Remove carriage returns, and replace new lines with commas, which are typically preferred by Google
				$fullAddress .= str_replace(["\r", "\n"],["", ", "],$address);

				$url = 'https://maps.google.com/maps?hl=en&amp;q=' . rawurlencode($fullAddress) . "&amp;output=embed";
				return '<iframe frameborder="0" style="border:0" src="' . $url . '" allowfullscreen="allowfullscreen"></iframe>';
			}
			else
			{
				return "";
			}
		}

		//region AdminNavItemGenerator

		/**
		 * Gets the nav item for this class
		 * @return    AdminNavItem        The admin nav item for this class
		 */
		public static function getAdminNavItem()
		{
			return new AdminNavItem(static::getAdminNavLink(), "Configuration", [static::class], Registry::isEnabled("Configuration"));
		}

		//endregion
	}

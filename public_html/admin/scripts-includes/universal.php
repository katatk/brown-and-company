<?php
	use Configuration\Registry;

	/*****
	 * Global definitions and includes
	 *
	 * @copyright 2016 Activate Design
	 **/
	/**
	 * @var bool IS_DEV_SITE flag so that we can conditionally change email addresses and so forth
	 *		for development without risking forgetting to change it back when the code goes live
     * @var bool IS_DEBUG_IP flag so we can conditionally change code on the live site while we are
	 *		working without potentially reducing the functionality for regular visitors e.g. genuine
	 *		enquiries through forms ending up in our mailbox, incomplete replacement code or styles
	 *		displaying
	 */
	// Checks if the domain is either *.activatedev.co.nz or *.adev.co.nzz
	define('IS_DEV_SITE', (preg_match("/.*\\.(activatedev|adev)\\.co\\.nz/", $_SERVER['HTTP_HOST'] ?? "") !== 0));
	define('IS_DEBUG_IP', ($_SERVER['REMOTE_ADDR'] ?? "" === '222.154.229.223'));

	/*****
	 * Global template settings
	 **/
	/**
	 * @var string SITE_FROM_NAME send email notifications from this name unless otherwise defined
	 */
	define('SITE_FROM_NAME', "Brown &amp; Company Planning Group" ?? "Unit Tests");

	/**
	 * Google API credentials
	 * generate site-specific API codes at
	 * https://console.developers.google.com/apis/credentials?project=activatedesign.co.nz:api-project-681176468897
	 *  API documentation at https://developers.google.com/maps/documentation/javascript/tutorial
	 * @var string ANALYTICS_IDENTIFIER Google Analytics Identifier
	 * 		Google Analytics is incuded in header.php if this is set
	 *
	 * @var string GOOGLE_MAPS_API if we are using a custom map
	 * 	 	map code in google-maps.js which is included in header.php if this is set
	 *
	 * @var string GOOGLE_MAPS_EMBED_API if we are using google embed map
	 * 	 	map code in Configuration
	 */
	define('ANALYTICS_IDENTIFIER', '');
	define('GOOGLE_MAPS_API', '');

	/**
	 * Google reCAPTCHA credentials
	 * generate site specific credentials at https://www.google.com/recaptcha/admin#list
	 *
	 * @var string RECAPTCHA_SITE_KEY code in header.php, passed_captcha() and the Form class checks this
	 *	@var string RECAPTCHA_SECRET the other part of reCAPTCHA implementation, used in passed_captcha()
	 *
	 * note this implementation requires the visitor have a javascript enabled browser
	 * @todo move to Configuration
	 */
	define('RECAPTCHA_SITE_KEY', '');
	define('RECAPTCHA_SECRET', '');

	/**
	 * pages module
	 *    - doesn't have it's own flag as is base for everything else
	 * @var bool PAGE_HAS_SLIDESHOW do pages have a slideshow or slideshow-acting-as-banner
	 * @var bool PAGE_SLIDESHOW_HAS_MULTIPLE_IMAGES do the slides potentially  show different images at different screen sizes
	 * @var int PAGE_SLIDESHOW_WIDTH (max) px referenced by new/replace slideshow image scripts
	 * @var int PAGE_SLIDESHOW_HEIGHT (max) px referenced by new/replace slideshow image scripts
	 * @var int PAGE_SLIDESHOW_RESPONSIVE_WIDTH (max) px referenced by new/replace slideshow image scripts (used with ...MULTIPLE_IMAGES)
	 * @var int PAGE_SLIDESHOW_RESPONSIVE_HEIGHT (max) px referenced by new/replace slideshow image scripts (used with ...MULTIPLE_IMAGES)
	 * @var int PAGE_HOMEPAGE_SLIDESHOW_WIDTH (max) px referenced by new/replace slideshow image scripts on the homepage
	 * @var int PAGE_HOMEPAGE_SLIDESHOW_HEIGHT (max) px referenced by new/replace slideshow image scripts on the homepage
	 * @var int PAGE_SLIDESHOW_CAPTION show the caption input field
	 * @var int PAGE_SLIDESHOW_LINK show the link input field
	 * @var bool PAGE_AUX_IMAGE has a customisable image on each page (may be part of template or
	 *		adjacent to content)
	 * @var int PAGE_AUX_WIDTH (max) px referenced by new/replace auxilary image scripts
	 * @var int PAGE_AUX_HEIGHT (max) px referenced by new/replace auxilary image scripts
	 * @var bool SITE_SECONDARY_NAV does the template have a partial duplication of the main menu
	 *		anywhere (usually in the footer); triggers a checkbox to include pages on this menu
	 */
	define('PAGE_HAS_SLIDESHOW', false);
	define('PAGE_SLIDE_HAS_MULTIPLE_IMAGES', false);//If this is true, you should really set slideAsBackground in _slideshow.twig to true as well
	define('PAGE_SLIDESHOW_WIDTH', 2560);
	define('PAGE_SLIDESHOW_HEIGHT', 387);
	define('PAGE_SLIDESHOW_RESPONSIVE_WIDTH', 600);
	define('PAGE_SLIDESHOW_RESPONSIVE_HEIGHT', 340);
	define('PAGE_HOMEPAGE_SLIDESHOW_WIDTH', 2560);
	define('PAGE_HOMEPAGE_SLIDESHOW_HEIGHT', 823);
	define('PAGE_SLIDESHOW_CAPTION', false);
	define('PAGE_SLIDESHOW_LINK', false);
	define("PAGE_SLIDESHOW_BUTTON", false);
	define('PAGE_BANNER', true);
	define('PAGE_BANNER_TEXT', true);
	define('PAGE_BANNER_HEIGHT', 585);
	define('PAGE_BANNER_WIDTH', 1900);
	define('PAGE_AUX_IMAGE', true);
	define('PAGE_AUX_WIDTH', 950);
	define('PAGE_AUX_HEIGHT', 650);
	define('SITE_SECONDARY_NAV', false);

	/**
	 * galleries
	 *    - doesn't have a flag as also present in every site
	 * @var int GALLERY_IMAGE_WIDTH (max) px referenced by new/replace image scripts
	 * @var int GALLERY_IMAGE_HEIGHT (max) px referenced by new/replace image scripts
	 * @var int GALLERY_THUMBNAIL_WIDTH (max) px referenced by new/replace image scripts
	 * @var int GALLERY_THUMBNAIL_HEIGHT (max) px referenced by new/replace image scripts
	 */
	define('GALLERY_IMAGE_WIDTH', 1000);
	define('GALLERY_IMAGE_HEIGHT', 800);
	define('GALLERY_THUMBNAIL_WIDTH', 500);
	define('GALLERY_THUMBNAIL_HEIGHT', 500);

	/**
	 * Products image dimensions
	 * @var int CATEGORY_THUMBNAIL_WIDTH (max) px referenced by new/replace image scripts
	 * @var int CATEGORY_THUMBNAIL_HEIGHT (max) px referenced by new/replace image scripts
	 * @var int PRODUCT_IMAGE_WIDTH (max) px referenced by new/replace image scripts
	 * @var int PRODUCT_IMAGE_HEIGHT (max) px referenced by new/replaceimage scripts
	 * @var int PRODUCT_THUMBNAIL_WIDTH (max) px referenced by new/replace image scripts
	 * @var int PRODUCT_THUMBNAIL_HEIGHT (max) px referenced by new/replace image scripts
	 */
	define('CATEGORY_THUMBNAIL_WIDTH', 150);
	define('CATEGORY_THUMBNAIL_HEIGHT', 150);
	define('PRODUCT_IMAGE_WIDTH', 680);
	define('PRODUCT_IMAGE_HEIGHT', 0);
	define('PRODUCT_THUMBNAIL_WIDTH', 150);
	define('PRODUCT_THUMBNAIL_HEIGHT', 150);

	/**
	 * @var int NUM_ASSOCIATED_PRODUCTS number of products to display on each product page
	 *    - for SQL query
	 *    - selected at random from those defined
	 */
	define('MODULE_PRODUCTS_ASSOCIATED', false);

	/*******
	 * payment options
	 **/
	define("PAYMENT_GATEWAY_TESTING", true);

	/**
	 * @var string BANK_DETAILS instructions for paying by bank deposit
	 */
	define('BANK_DETAILS', '');

	/***
	 * Credit card gateways
	 *
	 * @var string CREDIT_CARD_GATEWAY used in switch() in process_place_order.php
	 */
	define('CREDIT_CARD_GATEWAY', 'DPS');
	/**
	 * DPS (Payment Express)
	 *		currently the default
	 *
	 * @var string PXPAY_URL (DPS)
 	 * @var string DPS_TRANSACTION_METHOD (valid values 'Purchase' or 'Auth')
	 */
	//*
	//* Testing account details: do not overwrite. Live details go just below. Comment in/out relevant section
	define('DPS_ID', 'ActivateDesignPXP2_Dev'); // Activate Design developer account
	define('DPS_ENCRYPT', '9ab6069948263952afc303d0c0a5ce0a589db63e1713136d5002719dbdc99b3e'); // AD developer account
	// */
	/*
	/* Client details.
	define('DPS_ID',''); //live
	define('DPS_ENCRYPT',''); //live
	// */
	define('PXPAY_URL', 'https://sec.paymentexpress.com/pxpay/pxaccess.aspx');
	define('DPS_TRANSACTION_METHOD','Purchase');
	//define('DPS_TRANSACTION_METHOD','Auth');

	/**
	 * Stripe
	 */
	define('STRIPE_API_KEY', 'sk_test_lZqHSFdzUCvzhZPH8afJcL6N');//secret
	define('STRIPE_PUBLIC_KEY', 'pk_test_1PocB3nEQbfasrFvzDiUuQjE');

	/**
	 * PayStation
	 * @var bool PAYSTATION_ID client's paystation id
	 * @var bool PAYSTATION_HMAC_ID client key issued by PayStation required for dynamic url return
	 * @var bool PAYSTATION_GATEWAY_ID determines which gateway Paystation presents
	 * @var bool PAYSTATION_TEST_MODE determines which mode paystation uses
	 */
	define('PAYSTATION_ID', '615252'); // Activate Design test account
	define('PAYSTATION_HMAC_ID', 'JUVvqq6P8LGUE7xA'); // Activate Design test account
	define('PAYSTATION_GATEWAY_ID', 'paystation');
	define('PAYSTATION_TEST_MODE', true);

	/***
	 * Paypal
	 *
	 * @todo let credit card gateway be paypal rather than having it separate
	 *
	 */
	define('PAYPAL_TEST_MODE', true);
	if(PAYPAL_TEST_MODE)
	{
		// Activate Design test account
		define('PAYPAL_ACCOUNT_EMAIL', 'programmer@activatedesign.co.nz');
		//define('PAYPAL_ACCOUNT_EMAIL', 'progra_1193784050_biz@activatedesign.co.nz'); // Test account retailer
		define('PAYPAL_API_ID','AQWR-nGF5JDDHYeE8OX59rSyNFngl6kpKY0OYGxNu637QlnEFnWstRQBCxMCZcG_xC39tmGxaVyxoJDt');
		define('PAYPAL_API_SECRET','EF3Q5Ksd9u5SoDAdlm_zdLsqdAoFZz2cLGq36VxJjGewPaKeEBHzq5E7-v7R7eOkCbCfXiJ5m5LcJajy');
	}
	else
	{
		// Client REST API Application details from https://developer.paypal.com/developer/applications/
		define('PAYPAL_ACCOUNT_EMAIL', 'programmer@activatedesign.co.nz');
		define('PAYPAL_API_ID','AQWR-nGF5JDDHYeE8OX59rSyNFngl6kpKY0OYGxNu637QlnEFnWstRQBCxMCZcG_xC39tmGxaVyxoJDt');
		define('PAYPAL_API_SECRET','EF3Q5Ksd9u5SoDAdlm_zdLsqdAoFZz2cLGq36VxJjGewPaKeEBHzq5E7-v7R7eOkCbCfXiJ5m5LcJajy');
	}

	define('EWAY_API_KEY', '');
	define('EWAY_API_PASSWORD', '');

	/***
	 * POLi
	 *
	 * @var bool POLI_MERCHANT_CODE
	 * @var bool POLI_AUTHENTICATION_CODE
		 */
	//define('POLI_MERCHANT_CODE', 'SS64000565'); // Activate Design test account
	//define('POLI_AUTHENTICATION_CODE', 'L9O!q2aHV9z@H');
	define('POLI_MERCHANT_CODE', '');
	define('POLI_AUTHENTICATION_CODE', '');

	/**
	 * blog module
	 * @var INT ARTICLES_PER_PAGE default number of posts to show in a topic page or sidebar
	 * @var bool RECENT_ARTICLES default number of posts to show in a "Recent News" section
	 * @var bool BLOG_POST_IMAGE upload a Featured Image for each blog post
	 * @var int BLOG_IMAGE_WIDTH (max) px referenced by new/replace image scripts
	 * @var int BLOG_IMAGE_HEIGHT (max) px referenced by new/replaceimage scripts
	 * @var int BLOG_IMAGE_WIDTH (max) px referenced by new/replace image scripts
	 * @var int BLOG_IMAGE_HEIGHT (max) px referenced by new/replaceimage scripts
	 */
 	define('ARTICLES_PER_PAGE', 9);
 	define('RECENT_ARTICLES', 3);
 	define('BLOG_IMAGE_WIDTH', PAGE_AUX_WIDTH);
 	define('BLOG_IMAGE_HEIGHT', PAGE_AUX_HEIGHT);
	// thumbnail dimensions sometimes different from full image eg square thumbnail; landscape image
	define('BLOG_THUMBNAIL_WIDTH', 710);
 	define('BLOG_THUMBNAIL_HEIGHT', 428);


	/**
	 * Testimonials module
	 * @var bool MODULE_TESTIMONIALS (display lists in admin panel menu)
	 * @var bool TESTIMONIAL_IMAGE has a customisable image with each testimonial (may be part of template or adjacent to content)
	 * @var int TESTIMONIAL_IMAGE_WIDTH (max) px referenced by new/replace image scripts
	 * @var int TESTIMONIAL_IMAGE_HEIGHT (max) px referenced by new/replace image scripts
	 */
	define('TESTIMONIAL_IMAGE', false);
	define('TESTIMONIAL_IMAGE_WIDTH', 120);
	define('TESTIMONIAL_IMAGE_HEIGHT', 0);

	/*****
	 * Internals
	 **/
	/**
	 * php >=5.4 enforces not relying on server
	 */
	date_default_timezone_set('Pacific/Auckland');

	require $_SERVER["DOCUMENT_ROOT"] . "/admin/vendor/autoload.php";

	/**
	 * Automatically loads called classes. Means classes do not need to be explicitly included, nor
	 *		will they be loaded into memory until they are actually needed.
	 *
	 * @param string $class The name of the class to load
	 */
	function autoInclude($class)
	{
		if($class==='Configuration\\Registry')
		{
			include_once __DIR__ . '/../classes/Configuration/Registry.php';
			return;
		}
		//else
		$class = Registry::qualifyClassName($class);
		//echo $class . ' | ';
		$bits = explode("\\", $class);
		$bits[count($bits) - 1] = $bits[count($bits) - 1] . ".php";

		$path = __DIR__ . "/../classes/" . implode("/", $bits);
		//echo $path . '<br />';
		/** @noinspection PhpIncludeInspection */

		if(!file_exists($path))
		{
			// die($path . ' not found in ' . __FUNCTION__); //debugging
			// fall through to any other autoloaders
		}
		/** @noinspection PhpIncludeInspection */
		elseif(!include_once $path)
		{
			debug_print_backtrace();
		}
	}

	spl_autoload_register("autoInclude");

	define('PROTOCOL', 'http' . ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']) ? 's' : '') . '://');

	/**
	 * Includes
	 * legacy include site-data could be brought in with these definitions, but it would be better to combine all define()s into one file
	 */
	require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/scripts-includes/site-data.php';
	require_once $_SERVER['DOCUMENT_ROOT'] . '/admin/scripts-includes/functions.php';

	if(!file_exists("/tmp/wep/"))
	{
		mkdir("/tmp/wep/", 0755, true);
	}

	// Make sure that sessions don't expire too quickly. This value is equal to twelve hours.
	ini_set('session.gc_maxlifetime', 12 * 60 * 60);
	ini_set("session.save_path", "/tmp/wep/");

	/**
	 * start session
	 */
	session_start();

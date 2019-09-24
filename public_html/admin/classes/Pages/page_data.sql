-- phpMyAdmin SQL Dump
-- version 4.0.9
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 08, 2016 at 04:05 PM
-- Server version: 5.5.49-cll
-- PHP Version: 5.6.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `mooracti_wepcms`
--

-- --------------------------------------------------------

--
-- Table structure for table `pages`
--

CREATE TABLE IF NOT EXISTS `pages` (
  `page_id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int(4) unsigned DEFAULT '0' COMMENT 'foreign recursive key',
  `position` int(10) unsigned NOT NULL DEFAULT '10',
  `page_type` varchar(20) NOT NULL DEFAULT 'page' COMMENT 'page; [hook]',
  `path` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `title` varchar(72) NOT NULL DEFAULT '',
  `content_title` varchar(72) NOT NULL DEFAULT '',
  `secondary_heading` varchar(255) NOT NULL DEFAULT '',
  `banner_text` varchar(72) NOT NULL DEFAULT '',
  `keywords` text NOT NULL,
  `description` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '0',
  `display_on_menu` tinyint(1) NOT NULL,
  `display_on_secondary_menu` tinyint(1) NOT NULL,
  `menu_text` varchar(50) NOT NULL,
  `is_homepage` tinyint(1) NOT NULL DEFAULT '0',
  `is_error_page` tinyint(1) NOT NULL DEFAULT '0',
  `redirect_path` varchar(255) NOT NULL COMMENT 'link out to somewhere else',
  `internal_redirect` tinyint(1) NOT NULL,
  `external_redirect` tinyint(1) NOT NULL,
  `new_window` tinyint(1) NOT NULL DEFAULT '0',
  `duplicate` tinyint(1) NOT NULL,
  `original_id` int(11) DEFAULT NULL COMMENT 'foreign recursive key ',
  `has_slideshow` tinyint(1) NOT NULL DEFAULT '0',
  `auxilary_image` varchar(250) DEFAULT NULL,
  `banner` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`page_id`),
  KEY `parent_id` (`parent_id`),
  KEY `display_on_menu` (`display_on_menu`),
  KEY `active` (`active`),
  KEY `display_on_secondary_menu` (`display_on_secondary_menu`),
  FULLTEXT KEY `orm_search_method` (`content`,`title`,`keywords`,`description`,`menu_text`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

-- alter page table --
-- ALTER TABLE `pages` ADD `banner_text` VARCHAR(72) NOT NULL AFTER `content_title`; --
-- ALTER TABLE `pages` ADD `secondary_heading` VARCHAR(255) NOT NULL AFTER `content_title`; --
-- ALTER TABLE `pages` ADD `banner` VARCHAR(255) DEFAULT NULL AFTER `auxilary_image`; --

--
-- Table structure for table `slideshow_image_relationships`
--

CREATE TABLE `page_slides` (
  `page_slide_id` int(10) UNSIGNED NOT NULL,
  `page_id` smallint(5) UNSIGNED DEFAULT NULL COMMENT 'foreign key pages',
  `image` varchar(250) DEFAULT NULL,
  `link` varchar(250) NOT NULL,
  `title` varchar(250) NOT NULL,
  `position` int(4) UNSIGNED NOT NULL DEFAULT '10',
  `active` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='Page module';

--
-- Table structure for table `two_col` page
--

CREATE TABLE IF NOT EXISTS `two_col` (
  `page_id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `second_content` text NOT NULL,
  `third_content` text NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `rows_page` (
  `page_id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

CREATE TABLE IF NOT EXISTS `page_rows` (
  `page_rows_id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `page_id` int(4) unsigned NOT NULL,
  `image` varchar(250) DEFAULT NULL,
  `text` text NOT NULL,
  `position` int(4) UNSIGNED NOT NULL DEFAULT '10',
  PRIMARY KEY (`page_rows_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `services_page` (
  `page_id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `image_one` varchar(250) DEFAULT NULL,
  `text_one` text NOT NULL,
  `image_two` varchar(250) DEFAULT NULL,
  `text_two` text NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;



CREATE TABLE IF NOT EXISTS `servicing_page` (
  `page_id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `intro_text` text NOT NULL,
  `list_one` text NOT NULL,
  `list_two` text NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `content_page` (
  `page_id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `image` varchar(250) DEFAULT NULL,
  `button_text` text NOT NULL,
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `home_page` (
  `page_id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `banner_title` varchar(72) NOT NULL DEFAULT '',
  `banner_text` text NOT NULL DEFAULT '',
  `banner_button` varchar(72) NOT NULL DEFAULT '',
  `content_button` varchar(72) NOT NULL DEFAULT '',
  `image_1` varchar(250) DEFAULT NULL,
  `second_section_title` varchar(72) NOT NULL DEFAULT '',
  `second_section_text` text NOT NULL DEFAULT '',
  `second_section_button` varchar(72) NOT NULL DEFAULT '',
  `third_section_title` varchar(72) NOT NULL DEFAULT '',
  `third_section_text` text NOT NULL DEFAULT '',
  `third_section_button` varchar(72) NOT NULL DEFAULT '',
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `leadership_and_management_training_page` (
  `page_id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `box_1_title` varchar(72) NOT NULL DEFAULT '',
  `box_1_text` text NOT NULL DEFAULT '',
  `box_2_title` varchar(72) NOT NULL DEFAULT '',
  `box_2_text` text NOT NULL DEFAULT '',
  `box_3_title` varchar(72) NOT NULL DEFAULT '',
  `box_3_text` text NOT NULL DEFAULT '',
  `box_4_title` varchar(72) NOT NULL DEFAULT '',
  `box_4_text` text NOT NULL DEFAULT '',
  `content_2` text NOT NULL DEFAULT '',
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


CREATE TABLE IF NOT EXISTS `executive_support_page` (
  `page_id` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `box_1_title` varchar(72) NOT NULL DEFAULT '',
  `box_1_text` text NOT NULL DEFAULT '',
  `box_2_title` varchar(72) NOT NULL DEFAULT '',
  `box_2_text` text NOT NULL DEFAULT '',
  `box_3_title` varchar(72) NOT NULL DEFAULT '',
  `box_3_text` text NOT NULL DEFAULT '',
  `section_1_content` text NOT NULL DEFAULT '',
  `section_1_button` varchar(72) NOT NULL DEFAULT '',
  `section_2_content` text NOT NULL DEFAULT '',
  `section_2_button` varchar(72) NOT NULL DEFAULT '',
  `section_3_content` text NOT NULL DEFAULT '',
  `section_3_button` varchar(72) NOT NULL DEFAULT '',
  PRIMARY KEY (`page_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

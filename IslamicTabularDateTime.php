<?php
/*
 * Islamic Tabular Calendar implemented as php's DateTime Class
 *   Copyright (C) 2012  Hossein Amin
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
require_once('RYMDateTime.php');

class IslamicTabularDateTime extends RYMDateTime {

  /* Constants */
  protected $UNIX_YEAR_SHIFT =  1389;
  // (m9)266 * 24 * 3600 +  21 * 24 * 3600;
  protected $UNIX_YREM_SHIFT = 24796800;
  protected $DAY_OF_WEEK_SHIFT = 2;
  protected $ISO_START_DOW = 0;

  const _30years = 10631; // 30 * _1year + 11
  protected $_1leapYear = 355;
  protected $_1year = 354;

  /*
   * textual values are in utf8 character set
   */
  protected static $CONST_DAY_OF_WEEK_NAMES = 
    array("السبت", "الأحد", "الاثنين", "الثلاثاء", "الأربعاء",
	  "الخميس", "الجمعة");
  // 4 characters day of week
  protected static $CONST_DAY_OF_WEEK_SHORT_NAMES = 
    array("السبت", "الأحد", "الاثنين", "الثلاثاء", "الأربعاء",
	  "الخميس", "الجمعة");
  protected static $CONST_MONTH_NAMES = 
    array("المحرّم", "صفر", "ربيع الأوّل", "ربيع الثاني", "جمادى الأولى",
	  "جمادى الثانية", "رجب", "شعبان", "رمضان",
	  "شوّال", "ذو القعدة", "ذو الحجّة");
  protected static $CONST_MONTH_SHORT_NAMES = 
    array("المحرم", "صفر", "ربيع الأول", "ربيع الثاني", "جمادى الأولى",
	  "جمادى الثانية", "رجب", "شعبان", "رمضان",
	  "شوال", "ذو القعدة", "ذو الحجة");

  protected static $CONST_MERIDIEM_STATUS_L = array('ص', 'م');
  protected static $CONST_MERIDIEM_STATUS_U = array('AM', 'PM');

  protected $CENTURY = 15;
  /*
   * variables are year number, month number and the reminder of 
   * month in sec or in miliseconds
   */
  protected $_year = 0;
  protected $_month = 0;
  protected $_mrem = 0;
  
  function __construct($time = "now", $timezone = NULL)
  {
    $this->DAY_OF_WEEK_NAMES =& self::$CONST_DAY_OF_WEEK_NAMES;
    $this->DAY_OF_WEEK_SHORT_NAMES =& self::$CONST_DAY_OF_WEEK_SHORT_NAMES;
    $this->MONTH_NAMES =& self::$CONST_MONTH_NAMES;
    $this->MONTH_SHORT_NAMES =& self::$CONST_MONTH_SHORT_NAMES;
    $this->MERIDIEM_STATUS_L =& self::$CONST_MERIDIEM_STATUS_L;
    $this->MERIDIEM_STATUS_U =& self::$CONST_MERIDIEM_STATUS_L;

    parent::__construct($time, $timezone);
  }
  public static function getCalendarName()
  {
    return 'islamic tabular';
  }
  public function isLeapYear($y = NULL)
  {
    if($y === NULL)
      $y = $this->_year;
    switch($y % 30)
      {
      case 1:
      case 4:
      case 6:
      case 9:
      case 12:
      case 15:
      case 17:
      case 20:
      case 23:
      case 25:
      case 28:
	return true;
      }
    return false;
  }
  protected function getMonthsArray($isleap = NULL)
  {
    if($isleap)
      return array(30, 29, 30, 29, 30, 29, 30, 29,
		   30, 29, 30, 30);
    return array(30, 29, 30, 29, 30, 29, 30, 29,
		 30, 29, 30, 29);
  }
}
?>
<?php
/*
 * Gregorian calendar implemented as php's DateTime Class
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
require_once('BaseDateTime.php');

class GregorianDateTime extends RYMDateTime {

  /* Constants */
  protected $UNIX_YEAR_SHIFT = 1970;
  protected $UNIX_YREM_SHIFT = 0;
  protected $DAY_OF_WEEK_SHIFT = 6;
  protected $ISO_START_DOW = 1;

  const _400years = 146097;
  protected $_1leapYear = 366;
  protected $_1year = 365;

  /*
   * textual values in ascii charset
   */
  protected static $CONST_DAY_OF_WEEK_NAMES = 
    array("Sunday", "Monday", "Tuesday", "Wednesday", 
	  "Thursday", "Friday", "Saturday");
  protected static $CONST_DAY_OF_WEEK_SHORT_NAMES = 
    array("Sun", "Mon", "Tue", "Wed", 
	  "Thu", "Fri", "Sat");
  protected static $CONST_MONTH_NAMES = 
    array("January", "February", "March", "April", "May", "June",
	  "July", "August", "September", "October", "November", "December");
  protected static $CONST_MONTH_SHORT_NAMES = 
    array("Jan", "Feb", "Mar", "Apr", "May", "Jun",
	  "Jul", "Aug", "Sep", "Oct", "Nov", "Dec");

  protected static $CONST_MERIDIEM_STATUS_L = array('am', 'pm');
  protected static $CONST_MERIDIEM_STATUS_U = array('AM', 'PM');

  protected $CENTURY = 21;
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
    $this->MERIDIEM_STATUS_U =& self::$CONST_MERIDIEM_STATUS_U;
    
    parent::__construct($time, $timezone);
  }
  public static function getCalendarName()
  {
    return 'gregorian';
  }
  public function isLeapYear($y = NULL)
  {
    if($y === NULL)
      $y = $this->_year;
    if(($y % 4 == 0 && $y % 100 != 0) || $y % 400 == 0)
      return true;
    return false;
  }
  protected function getMonthsArray($isleap = NULL)
  {
    if($isleap === NULL)
      $isleap = $this->isLeapYear();
    if($isleap)
      return array(31, 29, 31, 30, 31, 30,
		   31, 31, 30, 31, 30, 31);
    return array(31, 28, 31, 30, 31, 30,
		 31, 31, 30, 31, 30, 31);
  }
  protected function yearsRangeToDays($y, $ey)
  {
    if($ey < $y)
      return null;
    $delta = $ey - $y;
    $d = floor($delta / 400) * self::_400years;
    $y += floor($delta / 400) * 400;
    while($y < $ey)
      {
	if($this->isLeapYear($y))
	  {
	    $d += $this->_1leapYear;
	  }else
	  {
	    $d += $this->_1year;
	  }
	++$y;
      }
    return $d;
  }
}
?>
<?php
/*
 * DateTime.php DateTime implementation for several calendars
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
require_once('PersianDateTime.php');
require_once('IslamicTabularDateTime.php');
require_once('GregorianDateTime.php');

header("content-type: text/plain;charset=utf8");
$tz = new DateTimeZone("Asia/Tehran");
$calendar = '';
if(isset($argv))
  {
    if(sizeof($argv) < 2)
      die("usage: date [calendar]\n");
    $date_str = $argv[1];
    if(sizeof($argv) >= 3)
      $calendar = $argv[2];
  }
elseif(isset($_GET))
  {
    if(!isset($_GET['date']))
      die("usage: GET date=<date>&calendar=<cal>\n");
    $date_str = $_GET['date'];
    if(isset($_GET['calendar']))
      $calendar = $_GET['calendar'];
    elseif(isset($_GET['cal']))
      $calendar = $_GET['cal'];
  }
switch(strtolower($calendar))
  {
  case 'persian':
    $bdate = new PersianDateTime($date_str, $tz);
    $cdate = new PersianDateTime("now", $tz);
    break;
  case 'islamic':
    $bdate = new IslamicTabularDateTime($date_str, $tz);
    $cdate = new IslamicTabularDateTime("now", $tz);
    break;
  case 'gregorian':
  default:
    $bdate = new GregorianDateTime($date_str, $tz);
    $cdate = new GregorianDateTime("now", $tz);
    break;
  }
$diff = $bdate->diff($cdate);
echo $diff->format("%R %Y years, %M months, %D days").", ";
echo $diff->format("%H Hours, %I Minutes, %S Seconds")."\n";
?>
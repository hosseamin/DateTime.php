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

/*
 * DateTime test tools
 */

require_once(__DIR__.'/PersianDateTime.php');
require_once(__DIR__.'/IslamicTabularDateTime.php');
require_once(__DIR__.'/GregorianDateTime.php');
header("content-type: text/plain;charset=utf8");
$tz = new DateTimeZone("Asia/Tehran");
//$pd = new PersianDateTime("12-05-20 20:10:5", $tz);
//$pd = new IslamicTabularDateTime("now", $tz);
$pd = new GregorianDateTime("now", $tz);
//$tinterval = new DateInterval("P3Y6M22DT6H");
//$tinterval = new DateInterval("P202142DT688H");
//sub_add_interval($tinterval, $pd);
//$pd->setDate(1391, 5, 19);
//$pd->setTime(11, 12, 10);
//$pd->add($tinterval);
//$pd->sub($tinterval);
$t = time();
$t = -3.5 * 3600 - 325 * 24 * 3600;
$pd->setTimestamp($t);
$pdt = $pd->getTimestamp();
echo $t." ".($t - $pdt)."\n".$pdt."\n";
echo $pd->format('Y-m-d H:i:s l');
echo "\n";

function add_sub_interval($interval, $dt)
{
  $dt = clone $dt;
  $t = $dt->getTimestamp();
  $sdate = $dt->format('Y-m-d H:i:s');
  $dt->add($interval);
  $afteradd = $dt->format('Y-m-d H:i:s');
  $dt->sub($interval);
  $aftersub = $dt->format('Y-m-d H:i:s');
  $nt = $dt->getTimestamp();
  if($t !== $nt)
    {
      throw new Exception("Addition and Subtraction with same ".
			  "interval applied but different results\n".
			  "timestamp ".$t." - ".$nt." = ".($t - $nt)."\n".
			  "Date ".$sdate." +> ".$afteradd." -> ".$aftersub);
      
    }
}
function sub_add_interval($interval, $dt)
{
  $dt = clone $dt;
  $t = $dt->getTimestamp();
  $sdate = $dt->format('Y-m-d H:i:s');
  $dt->add($interval);
  $aftersub = $dt->format('Y-m-d H:i:s');
  $dt->sub($interval);
  $afteradd = $dt->format('Y-m-d H:i:s');
  $nt = $dt->getTimestamp();
  if($t !== $nt)
    {
      throw new Exception("Subtraction and Addition with same ".
			  "interval applied but different results\n".
			  "timestamp ".$t." - ".$nt." = ".($t - $nt)."\n".
			  "Date ".$sdate." -> ".$aftersub." +> ".$afteradd);
      
    }
}
?>
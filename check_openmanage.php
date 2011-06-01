<?php
#
# PNP4Nagios template for check_openmanage 
# Author: 	Trond Hasle Amundsen
# Contact: 	t.h.amundsen@usit.uio.no
# Website:      http://folk.uio.no/trondham/software/check_openmanage.html
# Date: 	2011-06-01
#
# $Id$
#
# Copyright (C) 2008-2011 Trond H. Amundsen
#
# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# This program is distributed in the hope that it will be useful, but
# WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
# General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>.

# Array with different colors
$colors = array("0022ff", "22ff22", "ff0000", "00aaaa", "ff00ff",
		"ffa500", "cc0000", "0000cc", "0080C0", "8080C0",
		"FF0080", "800080", "688e23", "408080", "808000",
		"000000", "00FF00", "0080FF", "FF8000", "800000",
		"FB31FB");

# Counters
$f = 0;      # fan probe counter
$t = 0;      # temp probe counter
$a = 0;      # amp probe counter
$v = 0;      # volt probe counter
$e = 0;      # enclosure counter

# Flags
$visited_amp  = 0;

# IDs
$id_temp1 = 1;
$id_temp2 = 2;
$id_watt  = 3;
$id_amp   = 4;
$id_volt  = 5;
$id_fan   = 6;
$id_enc   = 7;

# Enclosure id
$enclosure_id = '';

# Default title
$def_title = 'Dell OpenManage';

# Temperature unit
function tempunit($arg) 
{
    $unit   = 'unknown';
    $vlabel = 'unknown';
    
    switch ($arg) {
    default:
	$vlabel = "Celsius";
	$unit = "°C";
	break;
    case "F":
	$vlabel = "Fahrenheit";
	$unit = "F";
	break;
    case "K":
	$vlabel = "Kelvin";
	$unit = "K";
	break;
    case "R":
	$vlabel = "Rankine";
	$unit = "R";
	break;
    }
    return array($unit, $vlabel);
}

# Loop through the performance data
foreach ($this->DS as $KEY=>$VAL) {
	
    $label = $VAL['LABEL'];

    # TEMPERATURES (AMBIENT)
    if (preg_match('/^T/', $label) && preg_match('/Ambient/', $label)) {

	# Temperature unit and vertical label
	list ($unit, $vlabel) = tempunit($VAL['UNIT']);

	# Long label
	$label = preg_replace('/^T(\d+)_(.+)/', '$2', $label);
	$label = preg_replace('/_/', ' ', $label);

	# Short label
	$label = preg_replace('/^T(\d+)$/', 'Probe $1', $label);

	$ds_name[$id_temp1] = "Temperatures";

	$opt[$id_temp1] = "--slope-mode --vertical-label \"$vlabel\" --title \"$def_title: Ambient Temperature\" ";
	if(isset($def[$id_temp1])){
	    $def[$id_temp1] .= "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	else {
	    $def[$id_temp1] = "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}

	# fancy graphing
	$def[$id_temp1] .= "CDEF:shade00_$KEY=var$KEY,1,* ";
	$def[$id_temp1] .= "CDEF:shade01_$KEY=var$KEY,0.99,* ";
	$def[$id_temp1] .= "CDEF:shade02_$KEY=var$KEY,0.92,* ";
	$def[$id_temp1] .= "CDEF:shade03_$KEY=var$KEY,0.97,* ";
	$def[$id_temp1] .= "CDEF:shade06_$KEY=var$KEY,0.94,* ";
	$def[$id_temp1] .= "CDEF:shade09_$KEY=var$KEY,0.91,* ";
	$def[$id_temp1] .= "CDEF:shade12_$KEY=var$KEY,0.88,* ";
	$def[$id_temp1] .= "CDEF:shade15_$KEY=var$KEY,0.85,* ";
	$def[$id_temp1] .= "CDEF:shade18_$KEY=var$KEY,0.82,* ";
	$def[$id_temp1] .= "CDEF:shade21_$KEY=var$KEY,0.79,* ";
	$def[$id_temp1] .= "CDEF:shade24_$KEY=var$KEY,0.76,* ";
	$def[$id_temp1] .= "CDEF:shade27_$KEY=var$KEY,0.73,* ";
	$def[$id_temp1] .= "CDEF:shade30_$KEY=var$KEY,0.70,* ";
        $def[$id_temp1] .= "AREA:shade00_$KEY#114480 ";
        $def[$id_temp1] .= "AREA:shade01_$KEY#114490 ";
        $def[$id_temp1] .= "AREA:shade02_$KEY#1144a0 ";
        $def[$id_temp1] .= "AREA:shade03_$KEY#1144a9 ";
        $def[$id_temp1] .= "AREA:shade06_$KEY#1144b3 ";
        $def[$id_temp1] .= "AREA:shade09_$KEY#1144bb ";
        $def[$id_temp1] .= "AREA:shade12_$KEY#1144c2 ";
        $def[$id_temp1] .= "AREA:shade15_$KEY#1144c8 ";
        $def[$id_temp1] .= "AREA:shade18_$KEY#1144cd ";
        $def[$id_temp1] .= "AREA:shade21_$KEY#1144d2 ";
        $def[$id_temp1] .= "AREA:shade24_$KEY#1144d6 ";
        $def[$id_temp1] .= "AREA:shade27_$KEY#1144d9 ";
        $def[$id_temp1] .= "AREA:shade30_$KEY#1144dc:\"$label\": ";

	$def[$id_temp1] .= "GPRINT:var$KEY:LAST:\"%6.0lf $unit last \" ";
	$def[$id_temp1] .= "GPRINT:var$KEY:MAX:\"%6.0lf $unit max \" ";
	$def[$id_temp1] .= "GPRINT:var$KEY:AVERAGE:\"%6.2lf $unit avg \\n\" ";

	# add an extra linebreak if we have thresholds
	if ($VAL['WARN'] != "" || $VAL['CRIT'] != "") {
	    $def[$id_temp1] .= "COMMENT:\" \l\" ";
	}

        # warning threshold
	if ($VAL['WARN'] != "") {
	    $warnThresh = $VAL['WARN'];
	    $def[$id_temp1] .= "CDEF:warn$KEY=var$KEY,$warnThresh,GT,var$KEY,0,IF ";
	    $def[$id_temp1] .= "CDEF:wshade00_$KEY=warn$KEY,1,* ";
	    $def[$id_temp1] .= "CDEF:wshade01_$KEY=warn$KEY,0.99,* ";
	    $def[$id_temp1] .= "CDEF:wshade02_$KEY=warn$KEY,0.92,* ";
	    $def[$id_temp1] .= "CDEF:wshade03_$KEY=warn$KEY,0.97,* ";
	    $def[$id_temp1] .= "CDEF:wshade06_$KEY=warn$KEY,0.94,* ";
	    $def[$id_temp1] .= "CDEF:wshade09_$KEY=warn$KEY,0.91,* ";
	    $def[$id_temp1] .= "CDEF:wshade12_$KEY=warn$KEY,0.88,* ";
	    $def[$id_temp1] .= "CDEF:wshade15_$KEY=warn$KEY,0.85,* ";
	    $def[$id_temp1] .= "CDEF:wshade18_$KEY=warn$KEY,0.82,* ";
	    $def[$id_temp1] .= "CDEF:wshade21_$KEY=warn$KEY,0.79,* ";
	    $def[$id_temp1] .= "CDEF:wshade24_$KEY=warn$KEY,0.76,* ";
	    $def[$id_temp1] .= "CDEF:wshade27_$KEY=warn$KEY,0.73,* ";
	    $def[$id_temp1] .= "CDEF:wshade30_$KEY=warn$KEY,0.70,* ";
	    $def[$id_temp1] .= "AREA:wshade00_$KEY#c4c400 ";
	    $def[$id_temp1] .= "AREA:wshade01_$KEY#c8c800 ";
	    $def[$id_temp1] .= "AREA:wshade02_$KEY#cbcb00 ";
	    $def[$id_temp1] .= "AREA:wshade03_$KEY#e0e000 ";
	    $def[$id_temp1] .= "AREA:wshade06_$KEY#e4e400 ";
	    $def[$id_temp1] .= "AREA:wshade09_$KEY#e8e800 ";
	    $def[$id_temp1] .= "AREA:wshade12_$KEY#ebeb00 ";
	    $def[$id_temp1] .= "AREA:wshade15_$KEY#f0f000 ";
	    $def[$id_temp1] .= "AREA:wshade18_$KEY#f4f400 ";
	    $def[$id_temp1] .= "AREA:wshade21_$KEY#f8f800 ";
	    $def[$id_temp1] .= "AREA:wshade24_$KEY#fbfb00 ";
	    $def[$id_temp1] .= "AREA:wshade27_$KEY#fdfd00 ";
	    $def[$id_temp1] .= "AREA:wshade30_$KEY#ffff00:\"Above Upper Warning Threshold\:  $warnThresh $unit\\n\": ";
	}
	
        # critical threshold
	if ($VAL['CRIT'] != "") {
	    $critThresh = $VAL['CRIT'];
	    $def[$id_temp1] .= "CDEF:crit$KEY=var$KEY,$critThresh,GT,var$KEY,0,IF ";
	    $def[$id_temp1] .= "CDEF:cshade00_$KEY=crit$KEY,1,* ";
	    $def[$id_temp1] .= "CDEF:cshade01_$KEY=crit$KEY,0.99,* ";
	    $def[$id_temp1] .= "CDEF:cshade02_$KEY=crit$KEY,0.92,* ";
	    $def[$id_temp1] .= "CDEF:cshade03_$KEY=crit$KEY,0.97,* ";
	    $def[$id_temp1] .= "CDEF:cshade06_$KEY=crit$KEY,0.94,* ";
	    $def[$id_temp1] .= "CDEF:cshade09_$KEY=crit$KEY,0.91,* ";
	    $def[$id_temp1] .= "CDEF:cshade12_$KEY=crit$KEY,0.88,* ";
	    $def[$id_temp1] .= "CDEF:cshade15_$KEY=crit$KEY,0.85,* ";
	    $def[$id_temp1] .= "CDEF:cshade18_$KEY=crit$KEY,0.82,* ";
	    $def[$id_temp1] .= "CDEF:cshade21_$KEY=crit$KEY,0.79,* ";
	    $def[$id_temp1] .= "CDEF:cshade24_$KEY=crit$KEY,0.76,* ";
	    $def[$id_temp1] .= "CDEF:cshade27_$KEY=crit$KEY,0.73,* ";
	    $def[$id_temp1] .= "CDEF:cshade30_$KEY=crit$KEY,0.70,* ";
	    $def[$id_temp1] .= "AREA:cshade00_$KEY#800000 ";
	    $def[$id_temp1] .= "AREA:cshade01_$KEY#900000 ";
	    $def[$id_temp1] .= "AREA:cshade02_$KEY#a00000 ";
	    $def[$id_temp1] .= "AREA:cshade03_$KEY#a90000 ";
	    $def[$id_temp1] .= "AREA:cshade06_$KEY#b30000 ";
	    $def[$id_temp1] .= "AREA:cshade09_$KEY#bb0000 ";
	    $def[$id_temp1] .= "AREA:cshade12_$KEY#c20000 ";
	    $def[$id_temp1] .= "AREA:cshade15_$KEY#c80000 ";
	    $def[$id_temp1] .= "AREA:cshade18_$KEY#cd0000 ";
	    $def[$id_temp1] .= "AREA:cshade21_$KEY#d20000 ";
	    $def[$id_temp1] .= "AREA:cshade24_$KEY#d60000 ";
	    $def[$id_temp1] .= "AREA:cshade27_$KEY#d90000 ";
	    $def[$id_temp1] .= "AREA:cshade30_$KEY#dc0000:\"Above Upper Critical Threshold\: $critThresh $unit\\n\": ";
	}
    }

    # TEMPERATURES
    if (preg_match('/^T/', $label) && !preg_match('/Ambient/', $label)) {

	# Temperature unit and vertical label
	list ($unit, $vlabel) = tempunit($VAL['UNIT']);

	# Long label
	$label = preg_replace('/^T(\d+)_(.+)/', '$2', $label);
	$label = preg_replace('/_/', ' ', $label);

	# Short label
	$label = preg_replace('/^T(\d+)$/', 'Probe $1', $label);

	$ds_name[$id_temp2] = "Temperatures";

	$opt[$id_temp2] = "--slope-mode --vertical-label \"$vlabel\" --title \"$def_title: Chassis Temperatures\" ";
	if (isset($def[$id_temp2])) {
	    $def[$id_temp2] .= "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	else {
	    $def[$id_temp2] = "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	$def[$id_temp2] .= "LINE:var$KEY#".$colors[$t++].":\"$label\" " ;
	$def[$id_temp2] .= "GPRINT:var$KEY:LAST:\"%6.0lf $unit last \" ";
	$def[$id_temp2] .= "GPRINT:var$KEY:MAX:\"%6.0lf $unit max \" ";
	$def[$id_temp2] .= "GPRINT:var$KEY:AVERAGE:\"%6.2lf $unit avg \\n\" ";
    }

    # WATTAGE PROBE
    if (preg_match('/^W/', $label)) {

	# Long label
	$label = preg_replace('/^W(\d+)_(.+)/', '$2', $label);
	$label = preg_replace('/_/', ' ', $label);

	# Short label
	$label = preg_replace('/^W(\d+)$/', 'Probe $1', $label);

	$ds_name[$id_watt] = "Power Consumption";
	$vlabel = "Watt";

	$title = $ds_name[$id_watt];

	$opt[$id_watt] = "--slope-mode --vertical-label \"$vlabel\" --title \"$def_title: $title\" ";

	if(isset($def[$id_watt])){
	    $def[$id_watt] .= "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	else {
	    $def[$id_watt] = "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}

	# calculate kWh and BTU
	$def[$id_watt] .= "VDEF:tot$KEY=var$KEY,TOTAL ";
	$def[$id_watt] .= "CDEF:kwh$KEY=var$KEY,POP,tot$KEY,1000,/,60,/,60,/ ";
        $def[$id_watt] .= "CDEF:btu$KEY=kwh$KEY,3412.3,* ";

	# fancy graphing
	$def[$id_watt] .= "CDEF:shade00_$KEY=var$KEY,1,* ";
	$def[$id_watt] .= "CDEF:shade01_$KEY=var$KEY,0.99,* ";
	$def[$id_watt] .= "CDEF:shade02_$KEY=var$KEY,0.92,* ";
	$def[$id_watt] .= "CDEF:shade03_$KEY=var$KEY,0.97,* ";
	$def[$id_watt] .= "CDEF:shade06_$KEY=var$KEY,0.94,* ";
	$def[$id_watt] .= "CDEF:shade09_$KEY=var$KEY,0.91,* ";
	$def[$id_watt] .= "CDEF:shade12_$KEY=var$KEY,0.88,* ";
	$def[$id_watt] .= "CDEF:shade15_$KEY=var$KEY,0.85,* ";
	$def[$id_watt] .= "CDEF:shade18_$KEY=var$KEY,0.82,* ";
	$def[$id_watt] .= "CDEF:shade21_$KEY=var$KEY,0.79,* ";
	$def[$id_watt] .= "CDEF:shade24_$KEY=var$KEY,0.76,* ";
	$def[$id_watt] .= "CDEF:shade27_$KEY=var$KEY,0.73,* ";
	$def[$id_watt] .= "CDEF:shade30_$KEY=var$KEY,0.70,* ";

        $def[$id_watt] .= "AREA:shade00_$KEY#800000 ";
        $def[$id_watt] .= "AREA:shade01_$KEY#900000 ";
        $def[$id_watt] .= "AREA:shade02_$KEY#a00000 ";
        $def[$id_watt] .= "AREA:shade03_$KEY#a90000 ";
        $def[$id_watt] .= "AREA:shade06_$KEY#b30000 ";
        $def[$id_watt] .= "AREA:shade09_$KEY#bb0000 ";
        $def[$id_watt] .= "AREA:shade12_$KEY#c20000 ";
        $def[$id_watt] .= "AREA:shade15_$KEY#c80000 ";
        $def[$id_watt] .= "AREA:shade18_$KEY#cd0000 ";
        $def[$id_watt] .= "AREA:shade21_$KEY#d20000 ";
        $def[$id_watt] .= "AREA:shade24_$KEY#d60000 ";
        $def[$id_watt] .= "AREA:shade27_$KEY#d90000 ";
        $def[$id_watt] .= "AREA:shade30_$KEY#dc0000:\"$label\": ";

	# print avg, max and min
	$def[$id_watt] .= "GPRINT:var$KEY:LAST:\"%6.0lf W last \" ";
	$def[$id_watt] .= "GPRINT:var$KEY:MAX:\"%6.0lf W max \" ";
	$def[$id_watt] .= "GPRINT:var$KEY:AVERAGE:\"%6.2lf W avg \l\" ";

	# print kWh and BTU for time period
        $def[$id_watt] .= "COMMENT:\" \l\" ";
        $def[$id_watt] .= "COMMENT:\"    Total power used in time period\:\" ";
	$def[$id_watt] .= "GPRINT:kwh$KEY:AVERAGE:\"%10.2lf kWh\l\" ";
        $def[$id_watt] .= "COMMENT:\"                                    \" ";
	$def[$id_watt] .= "GPRINT:btu$KEY:AVERAGE:\"%10.2lf BTU\l\" ";
    }

    # AMPERAGE PROBE
    if (preg_match('/^A/', $label)) {

	$first = 0;
	if ($visited_amp == 0) {
	    $first = 1;
	    $visited_amp = 1;
	}

	# Long label
	$label = preg_replace('/^A(\d+)_(.+)/', '$2', $label);
	$label = preg_replace('/_/', ' ', $label);

	# Short label
	$label = preg_replace('/^A(\d+)$/', 'Probe $1', $label);

	$ds_name[$id_amp] = "Amperage Probes";
	$vlabel = "Ampere";

	$title = $ds_name[$id_amp];

	$opt[$id_amp] = "-X0 --lower-limit 0 --slope-mode --vertical-label \"$vlabel\" --title \"$def_title: $title\" ";
	if(isset($def[$id_amp])){
	    $def[$id_amp] .= "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE ";
	}
	else {
	    $def[$id_amp] = "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE ";
	}

        $def[$id_amp] .= "CDEF:tier$KEY=var$KEY,10,/ ";
        $def[$id_amp] .= "AREA:tier$KEY#".$colors[$a]."b7::STACK ";
        $def[$id_amp] .= "AREA:tier$KEY#".$colors[$a]."bf::STACK ";
        $def[$id_amp] .= "AREA:tier$KEY#".$colors[$a]."c7::STACK ";
        $def[$id_amp] .= "AREA:tier$KEY#".$colors[$a]."cf::STACK ";
        $def[$id_amp] .= "AREA:tier$KEY#".$colors[$a]."d7::STACK ";
        $def[$id_amp] .= "AREA:tier$KEY#".$colors[$a]."df::STACK ";
        $def[$id_amp] .= "AREA:tier$KEY#".$colors[$a]."e7::STACK ";
        $def[$id_amp] .= "AREA:tier$KEY#".$colors[$a]."ef::STACK ";
        $def[$id_amp] .= "AREA:tier$KEY#".$colors[$a]."f7::STACK ";
        $def[$id_amp] .= "AREA:tier$KEY#".$colors[$a]."ff:\"$label\":STACK ";
	$a++;

	if ($first) {
	    $def[$id_amp] .= "CDEF:sum$KEY=var$KEY,0,+ ";
	}
	else {
	    $def[$id_amp] .= "CDEF:sum$KEY=sum".($KEY-1).",var$KEY,+ ";
	}

	$def[$id_amp] .= "LINE1:sum$KEY#555555 ";
	$def[$id_amp] .= "GPRINT:var$KEY:LAST:\"%4.1lf A last \" ";
	$def[$id_amp] .= "GPRINT:var$KEY:MAX:\"%4.1lf A max \" ";
	$def[$id_amp] .= "GPRINT:var$KEY:AVERAGE:\"%4.3lf A avg \\n\" ";
    }
    

    # VOLTAGE PROBE
    if (preg_match('/^V/', $label)) {

	# Long label
	$label = preg_replace('/^V(\d+)_(.+)/', '$2', $label);
	$label = preg_replace('/_/', ' ', $label);

	# Short label
	$label = preg_replace('/^V(\d+)$/', 'Probe $1', $label);
		
	$ds_name[$id_volt] = "Voltage Probes";
	$vlabel = "Volts";

	$title = $ds_name[$id_volt];

	$opt[$id_volt] = "--slope-mode --vertical-label \"$vlabel\" --title \"$def_title: $title\" ";
	if(isset($def[$id_volt])){
	    $def[$id_volt] .= "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	else {
	    $def[$id_volt] = "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	$def[$id_volt] .= "LINE:var$KEY#".$colors[$v++].":\"$label\" " ;
	$def[$id_volt] .= "GPRINT:var$KEY:LAST:\"%4.2lf A last \" ";
	$def[$id_volt] .= "GPRINT:var$KEY:MAX:\"%4.2lf A max \" ";
	$def[$id_volt] .= "GPRINT:var$KEY:AVERAGE:\"%4.4lf A avg \\n\" ";
    }

    # FANS (RPMs)
    if (preg_match('/^F/', $label)) {

	# Long label
	$label = preg_replace('/^F(\d+)_(.+)/', '$2', $label);
	$label = preg_replace('/_/', ' ', $label);

	# Short label
	$label = preg_replace('/^F(\d+)$/', 'Probe $1', $label);

	$ds_name[$id_fan] = "Fan Probes";

	$opt[$id_fan] = "-X0 --slope-mode --vertical-label \"RPMs\" --title \"$def_title: Fan Speeds\" ";
	if(isset($def[$id_fan])){
	    $def[$id_fan] .= "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	else {
	    $def[$id_fan] = "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	$def[$id_fan] .= "LINE:var$KEY#".$colors[$f++].":\"$label\" " ;
	$def[$id_fan] .= "GPRINT:var$KEY:LAST:\"%6.0lf RPM last \" ";
	$def[$id_fan] .= "GPRINT:var$KEY:MAX:\"%6.0lf RPM max \" ";
	$def[$id_fan] .= "GPRINT:var$KEY:AVERAGE:\"%6.2lf RPM avg \\n\" ";
    }
	
    # ENCLOSURE TEMPERATURES (Celsius)
    if (preg_match('/^E(?P<encl>.+?)_t(emp_)?(?P<probe>\d+)/', $label, $matches)) {

	$this_id     = $matches['encl'];
	$probe_index = $matches['probe'];

	if ($enclosure_id != $this_id) {
	    $e = 0;
	    $id_enc++;
	    $enclosure_id = $this_id;
	}

	# Label
	$label = "Probe $probe_index";

	$ds_name[$id_enc] = "Enclosure $enclosure_id Temperatures";

	$opt[$id_enc] = "--slope-mode --vertical-label \"Celsius\" --title \"$def_title: Enclosure $enclosure_id Temperatures\" ";

	if(isset($def[$id_enc])){
	    $def[$id_enc] .= "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	else {
	    $def[$id_enc] = "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	$def[$id_enc] .= "LINE:var$KEY#".$colors[$e++].":\"$label\" " ;
	$def[$id_enc] .= "GPRINT:var$KEY:LAST:\"%6.0lf °C last \" ";
	$def[$id_enc] .= "GPRINT:var$KEY:MAX:\"%6.0lf °C max \" ";
	$def[$id_enc] .= "GPRINT:var$KEY:AVERAGE:\"%6.2lf °C avg \\n\" ";
    }
}
?>

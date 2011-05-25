<?php
#
# PNP4Nagios template for check_openmanage 
# Author: 	Trond Hasle Amundsen
# Contact: 	t.h.amundsen@usit.uio.no
# Website:      http://folk.uio.no/trondham/software/check_openmanage.html
# Date: 	2010-03-16
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

# Color for power usage in watts
$PWRcolor = "dd0000";

# Counters
$count = 0;  # general counter
$f = 0;      # fan probe counter
$t = 0;      # temp probe counter
$a = 0;      # amp probe counter
$v = 0;      # volt probe counter
$e = 0;      # enclosure counter

# Flags
$visited_fan  = 0;
$visited_temp = 0;
$visited_amp  = 0;
$visited_volt = 0;

# Enclosure id
$enclosure_id = '';

# Default title
$def_title = 'Dell OpenManage';

# Loop through the performance data
foreach ($this->DS as $KEY=>$VAL) {
	
    $label = $VAL['LABEL'];

    # TEMPERATURES
    if (preg_match('/^T/', $label)) {
	if ($visited_temp == 0) {
	    ++$count;
	    $visited_temp = 1;
	}

	# Temperature unit
	switch ($VAL['UNIT']) {
	    default:
		$unit_long = "Celsius";
		$unit_short = "°C";
	    case "F":
		$unit_long = "Fahrenheit";
		$unit_short = "F";
		break;
	    case "K":
		$unit_long = "Kelvin";
		$unit_short = "K";
		break;
	    case "R":
		$unit_long = "Rankine";
		$unit_short = "R";
		break;
	}

	# Long label
	$label = preg_replace('/^T(\d+)_(.+)/', '$2', $label);
	$label = preg_replace('/_/', ' ', $label);

	# Short label
	$label = preg_replace('/^T(\d+)$/', 'Probe $1', $label);

	$ds_name[$count] = "Chassis Temperatures";

	$warnThresh = "INF";
	$critThresh = "INF";

	if ($VAL['WARN'] != "") {
	    $warnThresh = $VAL['WARN'];
	}
	if ($VAL['CRIT'] != "") {
	    $critThresh = $VAL['CRIT'];
	}

	$opt[$count] = "--slope-mode --vertical-label \"$unit_long\" --title \"$def_title: Chassis Temperatures\" ";
	if(isset($def[$count])){
	    $def[$count] .= "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	else {
	    $def[$count] = "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	$def[$count] .= "LINE:var$KEY#".$colors[$t++].":\"$label\" " ;
	$def[$count] .= "GPRINT:var$KEY:LAST:\"%6.0lf $unit_short last \" ";
	$def[$count] .= "GPRINT:var$KEY:MAX:\"%6.0lf $unit_short max \" ";
	$def[$count] .= "GPRINT:var$KEY:AVERAGE:\"%6.2lf $unit_short avg \\n\" ";
    }

    # WATTAGE PROBE
    if (preg_match('/^W/', $label)) {

	# Long label
	$label = preg_replace('/^W(\d+)_(.+)/', '$2', $label);
	$label = preg_replace('/_/', ' ', $label);

	# Short label
	$label = preg_replace('/^W(\d+)$/', 'Probe $1', $label);

	++$count;
	$ds_name[$count] = "Power Consumption";
	$vlabel = "Watt";

	$title = $ds_name[$count];

	$opt[$count] = "--slope-mode --vertical-label \"$vlabel\" --title \"$def_title: $title\" ";

	if(isset($def[$count])){
	    $def[$count] .= "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	else {
	    $def[$count] = "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}

	$def[$count] .= "VDEF:tot$KEY=var$KEY,TOTAL ";
	$def[$count] .= "CDEF:kwh$KEY=var$KEY,POP,tot$KEY,1000,/,60,/,60,/ ";
        $def[$count] .= "CDEF:btu$KEY=kwh$KEY,3412.3,* ";

	$def[$count] .= "AREA:var$KEY#$PWRcolor:\"$label\" " ;
	$def[$count] .= "GPRINT:var$KEY:LAST:\"%6.0lf W last \" ";
	$def[$count] .= "GPRINT:var$KEY:MAX:\"%6.0lf W max \" ";
	$def[$count] .= "GPRINT:var$KEY:AVERAGE:\"%6.2lf W avg \l\" ";

        $def[$count] .= "COMMENT:\" \l\" ";

        $def[$count] .= "COMMENT:\"    Total power used in time period\:\" ";
	$def[$count] .= "GPRINT:kwh$KEY:AVERAGE:\"%10.2lf kWh\l\" ";

        $def[$count] .= "COMMENT:\"                                    \" ";
	$def[$count] .= "GPRINT:btu$KEY:AVERAGE:\"%10.2lf BTU\l\" ";
    }

    # AMPERAGE PROBE
    if (preg_match('/^A/', $label)) {

	# Long label
	$label = preg_replace('/^A(\d+)_(.+)/', '$2', $label);
	$label = preg_replace('/_/', ' ', $label);

	# Short label
	$label = preg_replace('/^A(\d+)$/', 'Probe $1', $label);
		
	if ($visited_amp == 0) {
	    ++$count;
	    $visited_amp = 1;
	}
	$ds_name[$count] = "Amperage Probes";
	$vlabel = "Ampere";

	$title = $ds_name[$count];

	$opt[$count] = "-X0 --lower-limit 0 --slope-mode --vertical-label \"$vlabel\" --title \"$def_title: $title\" ";
	if(isset($def[$count])){
	    $def[$count] .= "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE ";
	}
	else {
	    $def[$count] = "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE ";
	}
	$def[$count] .= "AREA:var$KEY#".$colors[$a++].":\"$label\":STACK ";
	$def[$count] .= "GPRINT:var$KEY:LAST:\"%4.1lf A last \" ";
	$def[$count] .= "GPRINT:var$KEY:MAX:\"%4.1lf A max \" ";
	$def[$count] .= "GPRINT:var$KEY:AVERAGE:\"%4.3lf A avg \\n\" ";
    }
    

    # VOLTAGE PROBE
    if (preg_match('/^V/', $label)) {

	# Long label
	$label = preg_replace('/^V(\d+)_(.+)/', '$2', $label);
	$label = preg_replace('/_/', ' ', $label);

	# Short label
	$label = preg_replace('/^V(\d+)$/', 'Probe $1', $label);
		
	if ($visited_volt == 0) {
	    ++$count;
	    $visited_volt = 1;
	}
	$ds_name[$count] = "Voltage Probes";
	$vlabel = "Volts";

	$title = $ds_name[$count];

	$opt[$count] = "--slope-mode --vertical-label \"$vlabel\" --title \"$def_title: $title\" ";
	if(isset($def[$count])){
	    $def[$count] .= "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	else {
	    $def[$count] = "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	$def[$count] .= "LINE:var$KEY#".$colors[$v++].":\"$label\" " ;
	$def[$count] .= "GPRINT:var$KEY:LAST:\"%4.2lf A last \" ";
	$def[$count] .= "GPRINT:var$KEY:MAX:\"%4.2lf A max \" ";
	$def[$count] .= "GPRINT:var$KEY:AVERAGE:\"%4.4lf A avg \\n\" ";
    }

    # FANS (RPMs)
    if (preg_match('/^F/', $label)) {
	if ($visited_fan == 0) {
	    ++$count;
	    $visited_fan = 1;
	}

	# Long label
	$label = preg_replace('/^F(\d+)_(.+)/', '$2', $label);
	$label = preg_replace('/_/', ' ', $label);

	# Short label
	$label = preg_replace('/^F(\d+)$/', 'Probe $1', $label);

	$ds_name[$count] = "Fan Probes";

	$opt[$count] = "-X0 --slope-mode --vertical-label \"RPMs\" --title \"$def_title: Fan Speeds\" ";
	if(isset($def[$count])){
	    $def[$count] .= "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	else {
	    $def[$count] = "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	$def[$count] .= "LINE:var$KEY#".$colors[$f++].":\"$label\" " ;
	$def[$count] .= "GPRINT:var$KEY:LAST:\"%6.0lf RPM last \" ";
	$def[$count] .= "GPRINT:var$KEY:MAX:\"%6.0lf RPM max \" ";
	$def[$count] .= "GPRINT:var$KEY:AVERAGE:\"%6.2lf RPM avg \\n\" ";
    }
	
    # ENCLOSURE TEMPERATURES (Celsius)
    if (preg_match('/^E(?P<encl>.+?)_t(emp_)?(?P<probe>\d+)/', $label, $matches)) {

	$this_id     = $matches['encl'];
	$probe_index = $matches['probe'];

	if ($enclosure_id != $this_id) {
	    $e = 0;
	    ++$count;
	    $enclosure_id = $this_id;
	}

	# Label
	$label = "Probe $probe_index";

	$ds_name[$count] = "Enclosure $enclosure_id Temperatures";

	$warnThresh = "INF";
	$critThresh = "INF";

	if ($VAL['WARN'] != "") {
	    $warnThresh = $VAL['WARN'];
	}
	if ($VAL['CRIT'] != "") {
	    $critThresh = $VAL['CRIT'];
	}

	$opt[$count] = "--slope-mode --vertical-label \"Celsius\" --title \"$def_title: Enclosure $enclosure_id Temperatures\" ";

	if(isset($def[$count])){
	    $def[$count] .= "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	else {
	    $def[$count] = "DEF:var$KEY=$rrdfile:$VAL[DS]:AVERAGE " ;
	}
	$def[$count] .= "LINE:var$KEY#".$colors[$e++].":\"$label\" " ;
	$def[$count] .= "GPRINT:var$KEY:LAST:\"%6.0lf °C last \" ";
	$def[$count] .= "GPRINT:var$KEY:MAX:\"%6.0lf °C max \" ";
	$def[$count] .= "GPRINT:var$KEY:AVERAGE:\"%6.2lf °C avg \\n\" ";
    }
}
?>

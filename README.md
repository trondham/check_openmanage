# check_openmanage

**Dell™ Server Hardware Monitoring with OpenManage™ and Nagios®**

## Main features

* **Advanced hardware discovery**

  The plugin will search the monitored server for hardware components
  and monitor them. No need to tune the plugin to match different
  server models etc.

* **Blade detection**

  The plugin will identify blade servers as such and will not report
  fans and power supplies to be "missing" on blade systems.

* **Remote or local check**

  The plugin can check the system remotely via SNMP, or locally by
  using omreport commands.

* **Performance data**

  The plugin can give performance data with the ``-p`` or
  ``--perfdata`` switch. Performance data collected include
  temperatures, fan speeds and power usage (on servers that support
  it).

* **Highly customizable**

  A multitude of options lets the user tailor the plugin to meet his
  or her specific needs.



## Basic Overview

check_openmanage is a plugin for [Nagios](http://www.nagios.org/)
which checks the hardware health of Dell
[PowerEdge](http://www.dell.com/poweredge) (and some
[PowerVault](http://www.dell.com/powervault)) servers. It uses the
Dell [OpenManage Server
Administrator](http://www.delltechcenter.com/page/OpenManage+Systems+Management)
(OMSA) software, which must be running on the monitored
system. check_openmanage can be used remotely with SNMP or locally
with [NRPE](http://nagios.sourceforge.net/docs/3_0/addons.html#nrpe),
[check_by_ssh](http://nagiosplugins.org/man/check_by_ssh) or similar,
whichever suits your needs and particular taste. The plugin checks the
health of the storage subsystem, power supplies, memory modules,
temperature probes etc., and gives an alert if any of the components
are faulty or operate outside normal parameters.

![Nagios and check_openmanage](http://folk.uio.no/trondham/software/check_openmanage01.png)

**Storage components checked:**

* Controllers
* Physical drives
* Logical drives
* Cache batteries
* Connectors (channels)
* Enclosures
* Enclosure fans
* Enclosure power supplies
* Enclosure temperature probes
* Enclosure management modules (EMMs)

**Chassis components checked:**

* Processors
* Memory modules
* Cooling fans
* Temperature probes
* Power supplies
* Batteries
* Voltage probes
* Power usage
* Chassis intrusion
* Removable flash media (SD cards)

**Other:**

* ESM Log health
* ESM Log content (default disabled)
* Alert Log content (default disabled, not SNMP)

![Screenshot](http://folk.uio.no/trondham/software/screenshot-check_openmanage03.png)

check_openmanage will identify blades and will not report "missing"
power supplies, cooling fans etc. on blades. It will also accept that
other components are "missing", unless for components that should be
present in all servers. For example, all servers should have at least
one temperature probe, but not all servers have logical drives
(depends on the type and configuration of the controller).

![Screenshot](http://folk.uio.no/trondham/software/screenshot-check_openmanage01.jpg)

![Screenshot](http://folk.uio.no/trondham/software/check_openmanage-example1.png)

This nagios plugin is designed to be used by either [NRPE](http://nagios.sourceforge.net/docs/3_0/addons.html#nrpe) or with
SNMP. It is written in perl. In NRPE mode, it uses omreport to display
status on various hardware components. In SNMP mode, it checks the
same components as with omreport. Output is parsed and reported in a
Nagios friendly way.

check_openmanage provides handy options that gives you the possibility
to blacklist one or more hardware components that you won't fix, and
to fine-tune which components are checked in the first
place. Blacklisting_ and `check control`_ are described later in this
document.

![Screenshot](http://folk.uio.no/trondham/software/screenshot-check_openmanage02.jpg)

check_openmanage has been testet on a variety of Dell servers running
RHEL3, RHEL4, RHEL5, RHEL6, VMware ESX and various Windows releases
(with SNMP), with recent OpenManage versions. It has been tested and
runs successfully on the following Dell PowerEdge models at our site:
1750, 1800, 1850, 1950, 1955, 2600, 2650, 2800, 2850, 2900, 2950,
6650, 6950, 750, 850, M600, M610, M910, R510, R610, R710, T710, R805,
R815, R900, R910, R620, R720.


## Prerequisites

### Perl interpreter

check_openmanage needs a normal perl interpreter, version 5.6.0 or
later. The plugin assumes that perl is available as ``/usr/bin/perl``,
but you can easily change this as you wish by editing the first line
in the script.

For SNMP, you'll also need the perl module ``Net::SNMP`` on the Nagios
server (or the server running the queries). This module is not part of
perl itself, but is available in all modern Linux
distributions. Installing ``Net::SNMP`` is quite easy:

* For RHEL6 and CentOS 6 the best way is to use Fedora EPEL:

  ```
  yum --nogpgcheck install http://download.fedoraproject.org/pub/epel/6/i386/epel-release-6-5.noarch.rpm
  yum install perl-Net-SNMP
  ```

* For RHEL5 and CentOS 5 the best way is to use Fedora EPEL:

  ```
  yum --nogpgcheck install http://download.fedora.redhat.com/pub/epel/5/i386/epel-release-5-4.noarch.rpm
  yum install perl-Net-SNMP
  ```

* For Fedora:

  ```
  yum install perl-Net-SNMP
  ```

* For SuSE:

  ```
  rug install perl-Net-SNMP
  ```

* For Debian and Ubuntu:

  ```
  aptitude install libnet-snmp-perl
  ```

To use the `configuration file`_, you'll also need the perl module
``Config::Tiny``. This perl module is available for most Linux
distributions:

* For RHEL, CentOS and Fedora:

  ```
  yum install perl-Config-Tiny
  ```

* For SuSE:

  ```
  rug install perl-Config-Tiny
  ```

* For Debian and Ubuntu:

  ```
  aptitude install libconfig-tiny-perl
  ```


### Dell Openmanage Server Administrator

check_openmanage relies heavily on Dell Openmanage Server
Administrator (OMSA) and will not function if this software is not
installed on the monitored server. To obtain OMSA, go to
http://support.dell.com/, select "Drivers and downloads", select your
server model, and download the package called "Openmanage Server
Administrator Managed Node" under "Systems Management".

check_openmanage is tested with OMSA version 5.3 or later. Older
versions **may** work, but no guarantees. YMMV.

There are also official Dell repositories for Red Hat and SuSE. For
information about these, look here: http://linux.dell.com/repo/hardware/


## Intelligent plugin

check_openmanage is an intelligent plugin. It will by itself discover
which hardware components are present in the server and monitor
them. It does this because it assumes that most systems administrators
are lazy, and are not interested in configuring the plugin to match
different server models, blade vs. standalone etc. It should just
work. Missing hardware components are ignored by the plugin, unless
they should be present in all servers, such as CPUs.

This comes with a price, however. For hardware components that are
allowed to be missing, if OMSA doesn't display them the component will
be ignored by the plugin.

The following components are allowed to be missing on all servers:

* Amperage probes
* Power supplies
* Batteries
* Intrusion detection sensor
* Removable flash media (SD cards)

In addition, the following components are allowed to be missing if the
server is identified as a blade system:

* Cooling (fans)

If you're using the plugin via SNMP, without blacklisting and without
removing checks with the ``--check`` option, check_openmanage will
also check the global health status for added security.


## Multiline output

Since check_openmanage monitors several things, the plugin's output
will sometime contain multiple lines. These lines will be separated by
HTML linebreaks (``<br/>``) if run as a command within Nagios, via
NRPE etc. If run from a console which has a TTY, i.e. if you log in
via SSH or similar and run check_openmanage manually, the linebreaks
will be regular linebreaks.

Nagios 3.x allows the following option in ``cgi.cfg``:

```
# ESCAPE HTML TAGS
# This option determines whether HTML tags in host and service
# status output is escaped in the web interface.  If enabled,
# your plugin output will not be able to contain clickable links.

escape_html_tags=1
```

The default, as seen above in the sample ``cgi.cfg`` from the
distribution, is that HTML tags are escaped. My advice is to turn this
off. If not, you will see literal HTML linebreaks in the Nagios
console, i.e. like this:

```
Power Supply 0 [AC]: Presence Detected, Failure Detected, AC Lost<br/>Controller 0 [PERC 6/i Integrated]: Driver '00.00.03.15-RH1' is out of date
```

instead of this:

```
Power Supply 0 [AC]: Presence Detected, Failure Detected, AC Lost
Controller 0 [PERC 6/i Integrated]: Driver '00.00.03.15-RH1' is out of date
```

With Nagios 3.x, plugins are allowed to output multiple lines with
regular linebreaks, but only the first line is shown in the web
interface (status.cgi).


## Getting started

This is a short HOWTO that describes how to get started with using
check_openmanage. This HOWTO assumes that the prerequisites are met,
and that you have a Nagios server up and running. Nagios version 3.x
is assumed.

The examples below are simple examples with very basic usage of
check_openmanage. There are many more or less advanced options that
you might consider useful. Se the Usage section for info.

Also note that the examples below are just that: *examples*. They
describe one way of doing things, that is simple and
straightforward. There are many other ways of configuring Nagios, this
is ultimately up to you.

The first thing to consider is by which mechanism you want
check_openmanage to check your Dell servers. You can run the script
remotely on the Nagios server, probing the Dell servers via SNMP, or
you can use NRPE, check_by_ssh etc. and run the plugin locally on the
Dell servers. This should be an informed choice, but you can always
change it later. If you have mostly Linux boxes, you may want to use
[NRPE](http://nagios.sourceforge.net/docs/3_0/addons.html#nrpe) and run the plugin locally. If you have mostly Windows boxes,
you may want to check via SNMP. You can easily do both, e.g. NRPE for
Linux and SNMP for Windows, but you'll have to define different
commands in your Nagios config for each mechanism.


### Creating a hostgroup

The first thing you want to do is create a
[hostgroup](http://nagios.sourceforge.net/docs/3_0/objectdefinitions.html#hostgroup)
that contains your Dell servers, if you haven't already done so. If
you have very few Dell servers you can skip this step and use
[hosts](http://nagios.sourceforge.net/docs/3_0/objectdefinitions.html#host)
in the service definition instead, but I think hostgroups are always
better:

```
# hostgroup for Dell servers
define hostgroup {
    hostgroup_name  dell-servers
    alias           Dell Servers
}
```

### Defining the hosts

You'll need a host definition for each of the servers. You probably
already have this in place, but for the sake of completeness it is
included in this mini-howto:

```
define host {
    host_name       my-server1.foo.org
    alias           my-server1
    address         192.168.10.12
    use             generic-host
    hostgroups      dell-servers
    contact_groups  example@foo.org
}
```

### Creating a servicegroup

Next you want to create a
[servicegroup](http://nagios.sourceforge.net/docs/3_0/objectdefinitions.html#servicegroup)
for this service. This is not required, but it makes things easier
when you want to inspect your Dell servers via Nagios' web
interface. Creating a servicegroup is simple:

```
# Servicegroup for Dell OpenManage
define servicegroup {
    servicegroup_name         dell-openmanage
    alias                     Dell server health status
}
```

The servicegroup is used later in the service definition.


### Remote check via SNMP

#### Defining a command

The next step is to define a [command](http://nagios.sourceforge.net/docs/3_0/objectdefinitions.html#command) for check_openmanage:

```
# Openmanage check via SNMP
define command {
    command_name    check_openmanage
    command_line    /path/to/check_openmanage -H $HOSTADDRESS$
}
```

(Replace ``/path/to`` with the actual path leading up to the plugin).

Note that is is a very basic example of check_openmanage usage. Refer
to the **usage** section for info about the different options that alters
the behaviour of check_openmanage.


#### Defining the service

Finally, you define the service:

```
# Dell OMSA status
define service {
    use                       generic-service
    hostgroup_name            dell-servers
    servicegroups             dell-openmanage
    service_description       Dell OMSA
    check_command             check_openmanage
    notes_url                 https://github.com/trondham/check_openmanage
}
```

The ``notes_url`` statement is optional.


### Local check via NRPE

If you want to use [NRPE](http://nagios.sourceforge.net/docs/3_0/addons.html#nrpe), I assume that you have defined a check_nrpe
command elsewhere in your config and are ready to use it. Usually, we
don't define a
[command](http://nagios.sourceforge.net/docs/3_0/objectdefinitions.html#command)
for check_openmanage when using [NRPE](http://nagios.sourceforge.net/docs/3_0/addons.html#nrpe), so we go right to the
[service](http://nagios.sourceforge.net/docs/3_0/objectdefinitions.html#service)
definition:

```
# Dell OMSA status
define service {
    use                       generic-service
    hostgroup_name            dell-servers,!evil-dell-servers
    servicegroups             dell-openmanage
    service_description       Dell OMSA
    check_command             check_nrpe!check_openmanage
    notes_url                 https://github.com/trondham/check_openmanage
}
```

In this example we have opted to check the hostgroup "dell-servers",
while ignoring the hostgroup "evil-dell-servers" (which contains Dell
servers that can't run OMSA). Refer to the [Nagios
documentation](http://nagios.sourceforge.net/docs/3_0/toc.html) to
read up on hostgroups.

The [NRPE](http://nagios.sourceforge.net/docs/3_0/addons.html#nrpe) config has the following:

```
command[check_openmanage]=/path/to/check_openmanage
```

(Replace ``/path/to`` with the actual path leading up to the plugin,
or a correct macro such as ``$USER1$``.)

Note that is is a very basic example of check_openmanage usage. Refer
to the Usage section for info about the different options that alters
the behaviour of check_openmanage.


## Usage

### Help output

The option ``-h`` or ``--help`` will give a short usage information,
that includes the most commonly used options. For more information,
see the manual page.

```
$ check_openmanage -h
Usage: check_openmanage [OPTION]...

GENERAL OPTIONS:

   -f, --config         Specify configuration file
   -p, --perfdata       Output performance data [default=no]
   -t, --timeout        Plugin timeout in seconds [default=30]
   -c, --critical       Custom temperature critical limits
   -w, --warning        Custom temperature warning limits
   -F, --fahrenheit     Use Fahrenheit as temperature unit
   -d, --debug          Debug output, reports everything
   -h, --help           Display this help text
   -V, --version        Display version info

SNMP OPTIONS:

   -H, --hostname       Hostname or IP (required for SNMP)
   -C, --community      SNMP community string [default=public]
   -P, --protocol       SNMP protocol version [default=2]
   --port               SNMP port number [default=161]
   -6, --ipv6           Use IPv6 instead of IPv4 [default=no]
   --tcp                Use TCP instead of UDP [default=no]

OUTPUT OPTIONS:

   -i, --info           Prefix any alerts with the service tag
   -e, --extinfo        Append system info to alerts
   -s, --state          Prefix alerts with alert state
   -S, --short-state    Prefix alerts with alert state abbreviated
   -o, --okinfo         Verbosity when check result is OK
   -B, --show-blacklist Show blacklistings in OK output
   -I, --htmlinfo       HTML output with clickable links

CHECK CONTROL AND BLACKLISTING:

   -a, --all            Check everything, even log content
   -b, --blacklist      Blacklist missing and/or failed components
   --only               Only check a certain component or alert type
   --check              Fine-tune which components are checked
   --no-storage         Don't check storage

For more information and advanced options, see the manual page or URL:
  https://github.com/trondham/check_openmanage
```


### Local check

Run locally or via NRPE, check_openmanage will use omreport to display
info on hardware components, and report the result:

```
$ check_openmanage
OK - System: 'PowerEdge R710', SN: 'XXXXXX', 24 GB ram (6 dimms), 1 logical drives, 2 physical drives
```

Any user is allowed to run omreport, so you don't need any sudo
mechanisms or similar.

#### Local check on Windows

If SNMP just isn't your cup of tea, you can use check_openmanage
natively on Windows by either

* Install a Windows Perl interpreter, e.g. [Strawberry Perl](http://strawberryperl.com/) and use
  the plugin as a normal perl script, or

* Use the file ``check_openmanage.exe``, which is included in the ZIP
  achive and gzipped tarball.

The file ``check_openmanage.exe`` is a Win32 executable binary
produced with Microsoft Visual Studio 2010, Strawberry Perl and the
perl module [PAR::Packer](http://search.cpan.org/dist/PAR-Packer/). See also [this
howto](http://sam-pointer.com/2009/03/06/compiling-windows-executables-with-par).

The Win32 executable can be utilized via the **[External Scripts]**
tag, but before you do that you also have to
add **CheckExternalScripts.dll** under **[modules]** at the top of
the **NSC.ini** file.  Like so:

```
[modules]
CheckExternalScripts.dll

[External Scripts]
check_openmanage="C:\Program Files\NSClient++\plugins\check_openmanage.exe"
```

Then restart the nsc service. Call the plugin from Nagios as:

```
check_command check_nrpe!check_openmanage
```


### Remote check

> **NOTE:**
>
> If the ``-H`` or ``--hostname`` option is present, the plugin will
> automatically use SNMP to communicate with the monitored system.
>
> The perl module Net::SNMP must be installed for check_openmanage to
> work in SNMP mode. See prerequisites.
>
> check_openmanage is fully compatible with Nagios' embedded perl
> interpreter (ePN).

The plugin can query the monitored host remotely via
SNMP. Prerequisites for this are that the monitored host is running an
SNMP agent, that OMSA is installed and running with SNMP support, and
that the Nagios server is allowed to communicate with the host over
SNMP. The ``-H`` or ``--hostname`` option is needed for the
hostname/IP you want to check.

```
$ check_openmanage -H myhost
OK - System: 'PowerEdge R710', SN: 'XXXXXXX', 24 GB ram (6 dimms), 1 logical drives, 2 physical drives
```

You can specify the SNMP community string (for SNMP version 1 and 2c)
with the ``-C`` or ``--community`` option. Default community is set to
"public" if the option is not present:

```
$ check_openmanage -H myhost -C mycommunity
OK - System: 'PowerEdge R710', SN: 'XXXXXXX', 24 GB ram (6 dimms), 1 logical drives, 2 physical drives
```

You can also specify SNMP protocol version with the ``-P`` or
``--protocol`` option. Default is ``2`` (i.e. SNMP version 2c) if the
option is not present. Changing the SNMP protocol version is usually
not needed. Note that SNMP protocol version 2c (default) is
significantly faster than version 1. SNMP version 3 requires
additional authentication options to be specified. Also note that
Windows does not support SNMPv3 natively.

For other SNMP options, refer to the usage_ section and the manual
page.

For details on how to enable SNMP on Windows 2003 server, refer to

* HOW TO Configure the Simple Network Management Protocol (SNMP) Service in Windows Server 2003: http://support.microsoft.com/kb/324263
* How to install and configure Windows SNMP agent (2000 -XP): http://www.loriotpro.com/ServiceAndSupport/How_to/InstallWXPAgent_EN.php


> **CAUTION: SNMP daemon on Windows is unstable with some OMSA versions**
>
> Many users have reported that the SNMP daemon on Windows dies
> occasionally. It seems that OMSA versions prior to 5.5.0 have this
> problem, while 5.5.0 (or better yet: 5.5.0.1) and later versions do
> not. If you're using check_openmanage with SNMP to monitor Windows
> hosts, make sure that you have a recent OMSA version running.


### Output control

The default behaviour of the plugin is to print all alerts on separate
lines with no extra fuzz:

```
$ check_openmanage
Power Supply 0 [AC]: Presence Detected, Failure Detected, AC Lost
Controller 0 [PERC 6/i Integrated]: Driver '00.00.03.15-RH1' is out of date
```

There are several options that allows you to alter this, as listed below.


#### Prefix alerts with the service state

The ``-s`` or ``--state`` option will prefix each alert with the full
service state:

```
$ check_openmanage -s
CRITICAL: Power Supply 0 [AC]: Presence Detected, Failure Detected, AC Lost
WARNING: Controller 0 [PERC 6/i Integrated]: Driver '00.00.03.15-RH1' is out of date
```

#### Prefix alerts with the service state (abbreviated)

Example output with the ``-S`` or ``--short-state`` option, which does
the same, except that the service state is abbreviated to only one
letter, i.e. ``C`` for ``CRITICAL``, ``W`` for ``WARNING`` etc.:

```
$ check_openmanage -S
C: Power Supply 0 [AC]: Presence Detected, Failure Detected, AC Lost
W: Controller 0 [PERC 6/i Integrated]: Driver '00.00.03.15-RH1' is out of date
```

#### Prefix alerts with the service tag

The option ``-i`` or ``--info`` will prefix all alerts with the
service tag:

```
$ check_openmanage -i
[JV8KH0J] Power Supply 0 [AC]: Presence Detected, Failure Detected, AC Lost
[JV8KH0J] Controller 0 [PERC 6/i Integrated]: Driver '00.00.03.15-RH1' is out of date
```

#### System info after the alert(s)

The option ``-e`` or ``--extinfo`` will print the server model and
service tag on a separate line at the end of the alert:

```
$ check_openmanage -e
Power Supply 0 [AC]: Presence Detected, Failure Detected, AC Lost
Controller 0 [PERC 6/i Integrated]: Driver '00.00.03.15-RH1' is out of date
------ SYSTEM: PowerEdge 1950, SN: JV8KH0J
```

#### Custom line after the alert(s)

If this isn't exactly what you want, you can also specify your own
string to be shown on a separate line at the end of the alert, with
the ``--postmsg`` option:

```
$ check_openmanage --postmsg 'NOTE: Service tag: %s - Dell support: 555-1234-5678'
Power Supply 0 [AC]: Presence Detected, Failure Detected, AC Lost
Controller 0 [PERC 6/i Integrated]: Driver '00.00.03.15-RH1' is out of date
NOTE: Service tag: JV8KH0J - Dell support: 555-1234-5678
```

The argument is either a string with the message, or a file containing
that string. You can control the format with the following interpreted
sequences:

Code | Replaced with
-----|--------------
``%m`` | System model
``%s`` | Service tag
``%b`` | BIOS version
``%d`` | BIOS release date
``%o`` | Operating system name
``%r`` | Operating system release
``%p`` | Number of physical drives
``%l`` | Number of logical drives
``%n`` | Line break
``%%`` | A literal ``%``

The full range of the control format for ``--postmsg`` is also
available in the manual page.

#### Combination of output options

You can combine any of these options. A simple example:

```
$ check_openmanage -s -e
CRITICAL: Power Supply 0 [AC]: Presence Detected, Failure Detected, AC Lost
WARNING: Controller 0 [PERC 6/i Integrated]: Driver '00.00.03.15-RH1' is out of date
------ SYSTEM: PowerEdge 1950, SN: JV8KH0J
```

A more advanced example:

```
$ check_openmanage -s -i --postmsg 'NOTE: Handled in RT ticket #123456'
CRITICAL: [JV8KH0J] Power Supply 0 [AC]: Presence Detected, Failure Detected, AC Lost
WARNING: [JV8KH0J] Controller 0 [PERC 6/i Integrated]: Driver '00.00.03.15-RH1' is out of date
NOTE: Handled in RT ticket #123456
```

Which (combination) of these options you choose to use, if any,
depends on how you use Nagios and your personal preference.

#### Clickable links in the alerts

Using the ``-I`` or ``--htmlinfo`` option will make the servicetag and model
name into clickable HTML links in the output. The model name link will
point to the official Dell documentation for that model, while the
servicetag link will point to a website containing support info for
that particular server.

![The --htmlinfo option](http://folk.uio.no/trondham/software/check_openmanage-htmlinfo.png)

This option takes an optional argument, which should be your country
code (e.g. ``nl``, ``us``, ``fr``) or ``me`` for the middle east. If
the country code is omitted the servicetag link will still work, but
it will not be speficic for your country or area. Example for Germany:

```
$ check_openmanage -H <hostname> -e --htmlinfo de
Logical drive 0 '/dev/sda' [RAID-1, 136.13 GB] needs attention: Degraded
------ SYSTEM: **PowerEdge 1950**, SN: **XXXXXXX**
```

If this option is used together with either the ``-e|--extinfo`` (as
in the example above) or ``-i|--info`` options, it is particularly
useful. Whenever an alert occurs, you'll get clickable links to the
server's documentation and warranty information from Dell.

Only the most common country codes is supported at this time. If
your country code is not supported, send me an email and I'll add
it in the next release.


#### Verbosity when everything is ok

The default behaviour of the plugin is to output a single line when
there are no alerts:

```
$ check_openmanage
OK - System: 'PowerEdge M600', SN: 'XXXXXXX', 24 GB ram (6 dimms), 1 logical drives, 2 physical drives
```

The option ``-o`` or ``--ok-info`` takes an integer as argument, and
lets you control the amount of output given by the plugin in an "OK"
state. The higher the integer, the more output. The argument is
cumulative. A value of ``1`` outputs BIOS and firmware info on a
separate line:

```
$ check_openmanage -H myhost -o 1
OK - System: 'PowerEdge 2950', SN: 'XXXXXXX', 4 GB ram (4 dimms), 4 logical drives, 47 physical drives
----- BIOS='2.5.0 09/12/2008', DRAC5='1.45', BMC='2.37'
```

A value of ``2`` also outputs firmware, driver etc. for storage
controllers and enclosures (including backplane):

```
$ check_openmanage -o 2
OK - System: 'PowerEdge 2950', SN: 'XXXXXXX', 16 GB ram (8 dimms), 4 logical drives, 47 physical drives
----- BIOS='2.5.0 09/12/2008', DRAC5='1.45', BMC='2.37'
----- Ctrl 0 [PERC 6/i Integrated]: Fw='6.2.0-0013', Dr='00.00.04.01-RH1'
----- Ctrl 1 [PERC 6/E Adapter]: Fw='6.2.0-0013', Dr='00.00.04.01-RH1'
----- Ctrl 2 [PERC 6/E Adapter]: Fw='6.2.0-0013', Dr='00.00.04.01-RH1'
----- Encl 0:0:0 [Backplane]: Fw='1.05'
----- Encl 1:0:0 [MD1000]: Fw='A.04'
----- Encl 2:0:0 [MD1000]: Fw='A.04'
----- Encl 2:1:0 [MD1000]: Fw='A.04'
```

A value of ``3`` (or above) will also include the OMSA version:

```
$ check_openmanage -o 3
OK - System: 'PowerEdge 2950', SN: 'XXXXXXX', 16 GB ram (8 dimms), 4 logical drives, 47 physical drives
----- BIOS='2.5.0 09/12/2008', DRAC5='1.45', BMC='2.37'
----- Ctrl 0 [PERC 6/i Integrated]: Fw='6.2.0-0013', Dr='00.00.04.01-RH1'
----- Ctrl 1 [PERC 6/E Adapter]: Fw='6.2.0-0013', Dr='00.00.04.01-RH1'
----- Ctrl 2 [PERC 6/E Adapter]: Fw='6.2.0-0013', Dr='00.00.04.01-RH1'
----- Encl 0:0:0 [Backplane]: Fw='1.05'
----- Encl 1:0:0 [MD1000]: Fw='A.04'
----- Encl 2:0:0 [MD1000]: Fw='A.04'
----- Encl 2:1:0 [MD1000]: Fw='A.04'
----- OpenManage Server Administrator (OMSA) version: '5.5.0'
```

Most would go for the default (value of ``0``), i.e. a single line.

> **NOTE**
> When the plugin is run locally, i.e. via NRPE, the only way for
> check_openmanage to determine the OMSA version is to run the command
> "omreport about". This command is very slow, and will effectively
> double the amount of time check_openmanage takes to run. When used via
> SNMP, this is not an issue.


#### Show blacklistings in OK output

When blacklistings are used to mask faulty components, it is easy to
forget which components and on which servers. If the ``-B`` or
``--show-blacklist`` option is used, any blacklistings will be
displayed on a separate line in the OK output:

```
$ check_openmanage -H dell-server01 -b ctrl_driver=all -b pdisk=1:0:0:1 -B
OK - System: 'PowerEdge R610 II', SN: 'G7YCX4J', 16 GB ram (4 dimms), 1 logical drives, 2 physical drives
----- BLACKLISTED: pdisk=1:0:0:1/ctrl_driver=all
```

If no blacklisting is used, this option has no effect.


#### Hide the servicetag (serial number)

If you for some reason wish to hide the servicetag in the output,
e.g. if your customers have Nagios access but you don't want them to
view the complete hardware history, you can use the option
``--hide-servicetag``:

```
$ check_openmanage -H dell-server02 --hide-servicetag
OK - System: 'PowerEdge R710 II', SN: 'XXXXXXX', 96 GB ram (12 dimms), 2 logical drives, 26 physical drives
```

### Debug output

If given option ``-d`` or ``--debug``, check_openmanage will
output messages about all the checked components, along with their
respectible alert states and a unique identifier, if applicable. The
identifier is used whenever you want to blacklist a component,
i.e. prevent it from being checked. Blacklisting is discussed
later. An example debug output is given below.

```
   System:      PowerEdge R710           OMSA version:    6.5.0
   ServiceTag:  XXXXXXX                  Plugin version:  3.7.4
   BIOS/date:   3.0.0 01/31/2011         Checking mode:   SNMPv2c UDP/IPv4
-----------------------------------------------------------------------------
   Storage Components                                                        
=============================================================================
  STATE  |    ID    |  MESSAGE TEXT                                          
---------+----------+--------------------------------------------------------
      OK |        0 | Controller 0 [PERC 6/i Integrated] is Ready
      OK |  0:0:0:0 | Physical Disk 0:0:0 [SAS-HDD 73GB] on ctrl 0 is Online
      OK |  0:0:0:1 | Physical Disk 0:0:1 [SAS-HDD 73GB] on ctrl 0 is Online
      OK |  0:0:0:2 | Physical Disk 0:0:2 [SAS-HDD 300GB] on ctrl 0 is Online
      OK |  0:0:0:3 | Physical Disk 0:0:3 [SAS-HDD 300GB] on ctrl 0 is Online
      OK |  0:1:0:4 | Physical Disk 1:0:4 [SAS-HDD 300GB] on ctrl 0 is Online
      OK |      0:0 | Logical Drive '/dev/sda' [RAID-1, 67.75 GB] is Ready
      OK |      0:1 | Logical Drive '/dev/sdb' [RAID-5, 557.75 GB] is Ready
      OK |      0:0 | Cache Battery 0 in controller 0 is Ready
      OK |      0:0 | Connector 0 [SAS] on controller 0 is Ready
      OK |      0:1 | Connector 1 [SAS] on controller 0 is Ready
      OK |    0:0:0 | Enclosure 0:0:0 [Backplane] on controller 0 is Ready
      OK |    0:1:0 | Enclosure 0:1:0 [Backplane] on controller 0 is Ready
-----------------------------------------------------------------------------
   Chassis Components                                                        
=============================================================================
  STATE  |  ID  |  MESSAGE TEXT                                              
---------+------+------------------------------------------------------------
      OK |    0 | Memory module 0 [DIMM_A1, 4096 MB] is Ok
      OK |    1 | Memory module 1 [DIMM_A2, 4096 MB] is Ok
      OK |    2 | Memory module 2 [DIMM_A3, 4096 MB] is Ok
      OK |    3 | Memory module 3 [DIMM_A4, 4096 MB] is Ok
      OK |    4 | Memory module 4 [DIMM_A5, 4096 MB] is Ok
      OK |    5 | Memory module 5 [DIMM_A6, 4096 MB] is Ok
      OK |    6 | Memory module 6 [DIMM_A7, 4096 MB] is Ok
      OK |    7 | Memory module 7 [DIMM_A8, 4096 MB] is Ok
      OK |    8 | Memory module 8 [DIMM_A9, 4096 MB] is Ok
      OK |    9 | Memory module 9 [DIMM_B1, 4096 MB] is Ok
      OK |   10 | Memory module 10 [DIMM_B2, 4096 MB] is Ok
      OK |   11 | Memory module 11 [DIMM_B3, 4096 MB] is Ok
      OK |   12 | Memory module 12 [DIMM_B4, 4096 MB] is Ok
      OK |   13 | Memory module 13 [DIMM_B5, 4096 MB] is Ok
      OK |   14 | Memory module 14 [DIMM_B6, 4096 MB] is Ok
      OK |   15 | Memory module 15 [DIMM_B7, 4096 MB] is Ok
      OK |   16 | Memory module 16 [DIMM_B8, 4096 MB] is Ok
      OK |   17 | Memory module 17 [DIMM_B9, 4096 MB] is Ok
      OK |    0 | Chassis fan 0 [System Board FAN 1 RPM] reading: 3600 RPM
      OK |    1 | Chassis fan 1 [System Board FAN 2 RPM] reading: 3600 RPM
      OK |    2 | Chassis fan 2 [System Board FAN 3 RPM] reading: 3600 RPM
      OK |    3 | Chassis fan 3 [System Board FAN 4 RPM] reading: 3600 RPM
      OK |    4 | Chassis fan 4 [System Board FAN 5 RPM] reading: 3600 RPM
      OK |    0 | Power Supply 0 [AC]: Presence detected
      OK |    1 | Power Supply 1 [AC]: Presence detected
      OK |    0 | Temperature Probe 0 [System Board Ambient Temp] reads 17 C (min=8/3, max=42/47)
      OK |    0 | Processor 0 [Intel Xeon L5520 2.27GHz] is Present
      OK |    1 | Processor 1 [Intel Xeon L5520 2.27GHz] is Present
      OK |    0 | Voltage sensor 0 [CPU1 VCORE PG] is Good
      OK |    1 | Voltage sensor 1 [CPU2 VCORE PG] is Good
      OK |    2 | Voltage sensor 2 [CPU2 0.75 VTT CPU2 PG] is Good
      OK |    3 | Voltage sensor 3 [CPU1 0.75 VTT CPU1 PG] is Good
      OK |    4 | Voltage sensor 4 [System Board 1.5V PG] is Good
      OK |    5 | Voltage sensor 5 [System Board 1.8V PG] is Good
      OK |    6 | Voltage sensor 6 [System Board 3.3V PG] is Good
      OK |    7 | Voltage sensor 7 [System Board 5V PG] is Good
      OK |    8 | Voltage sensor 8 [CPU2 MEM PG] is Good
      OK |    9 | Voltage sensor 9 [CPU1 MEM PG] is Good
      OK |   10 | Voltage sensor 10 [CPU2 VTT PG] is Good
      OK |   11 | Voltage sensor 11 [CPU1 VTT PG] is Good
      OK |   12 | Voltage sensor 12 [System Board 0.9V PG] is Good
      OK |   13 | Voltage sensor 13 [CPU2 1.8 PLL  PG] is Good
      OK |   14 | Voltage sensor 14 [CPU1 1.8 PLL PG] is Good
      OK |   15 | Voltage sensor 15 [System Board 8.0 V PG] is Good
      OK |   16 | Voltage sensor 16 [System Board 1.1 V PG] is Good
      OK |   17 | Voltage sensor 17 [System Board 1.0 LOM PG] is Good
      OK |   18 | Voltage sensor 18 [System Board 1.0 AUX PG] is Good
      OK |   19 | Voltage sensor 19 [System Board 1.05 V PG] is Good
      OK |   20 | Voltage sensor 20 [PS 1 Voltage] is 228.000 V
      OK |   21 | Voltage sensor 21 [PS 2 Voltage] is 240.000 V
      OK |    0 | Battery probe 0 [System Board CMOS Battery] is Presence Detected
      OK |    0 | Amperage probe 0 [PS 1 Current] reads 0.4 A
      OK |    1 | Amperage probe 1 [PS 2 Current] reads 0.4 A
      OK |    2 | Amperage probe 2 [System Board System Level] reads 175 W
      OK |    0 | Chassis intrusion 0 detection: Ok (Not Breached)
      OK |    0 | SD Card 0 [vFlash] is Absent
-----------------------------------------------------------------------------
   Other messages                                                            
=============================================================================
  STATE  |  MESSAGE TEXT                                                     
---------+-------------------------------------------------------------------
      OK | ESM log health is Ok (less than 80% full)
      OK | Chassis Service Tag is sane
```

The debug output will vary from model to model and server to
server. Among other things, it depends on what components exist in the
server in the first place. The above example shows a PowerEdge R710
server with an attached MD1220 shelf.

> **WARNING:** The option ``-d`` or ``--debug`` is intended for
> diagnostics and debugging purposes only. Do not use this option from
> within Nagios, i.e. in your Nagios config.


### Custom temperature thresholds

![OMSA temperature thresholds](http://folk.uio.no/trondham/software/temp_omsa.png)

Openmanage (OMSA) has its own temperature thresholds. You can easily
view these with ``omreport``:

```
# omreport chassis temps
Temperature Probes Information

------------------------------------
Main System Chassis Temperatures: Ok
------------------------------------

Index                     : 0
Status                    : Ok
Probe Name                : System Board Ambient Temp
Reading                   : 17.0 C
Minimum Warning Threshold : 8.0 C
Maximum Warning Threshold : 42.0 C
Minimum Failure Threshold : 3.0 C
Maximum Failure Threshold : 47.0 C
```

Check_openmanage will also output the OMSA thresholds when run in
debug mode, example:

```
$ check_openmanage -H myhost --only temp -d
   System:      PowerEdge R710 II        OMSA version:    6.4.0
   ServiceTag:  XXXXXXX                  Plugin version:  3.6.5
   BIOS/date:   2.1.15 09/02/2010        Checking mode:   SNMPv2 UDP/IPv4
-----------------------------------------------------------------------------
   Chassis Components                                                        
=============================================================================
  STATE  |  ID  |  MESSAGE TEXT                                              
---------+------+------------------------------------------------------------
      OK |    0 | Temperature Probe 0 [System Board Ambient Temp] reads 20 C (min=8/3, max=42/47)
```

As you can see the thresholds are given inside the parentheses at the
end of the line. Check_openmanage will treat these values as absolute,
i.e. if the temperature is beyond these limits, the plugin will issue
an alert.


#### Narrowing the field

![OMSA and check_openmanage temperature thresholds](http://folk.uio.no/trondham/software/temp_plugin.png)

If you wish to *narrow* the field of OK temperatures (e.g. setting the
warning limit for the maximum temperature lower than the OMSA
threshold), you can override the OMSA temperature warning and critical
thresholds with the ``-w|--warning`` and ``-c|--critical`` options:

```
$ check_openmanage -H myhost -w 0=30 -c 0=40
Temperature Probe 0 [System Board Ambient Temp] reads 31 C (custom max=30)
```

The option takes either a string or a file containing the string with
the limits. Syntax is ``id1=max[/min],id2=max[/min],...``. Each of
these options can be specified multiple times if needed.

You can also specify a custom minimum temperature:

```
$ check_openmanage -H myhost -w 0=30/15 -c 0=40/10
Temperature Probe 0 [System Board Ambient Temp] reads 14 C (custom min=15)
```


#### Expanding the field

![OMSA expanded temperature thresholds](http://folk.uio.no/trondham/software/temp_omsa_custom.png)

If you wish to *expand* the field, the check_openmanage options
mentioned above won't help you. In this case, use ``omconfig`` to adjust
the warning thresholds of OMSA itself. If, for the machine above, we
wanted the warning threshold to be 45 degrees instead of 43 degrees
(the default), we would say:

```
# omconfig chassis temps index=0 maxwarnthresh=45
Temperature probe warning threshold(s) set successfully.
```

If you want to reset the thresholds to the system default, use this
command:

```
# omconfig chassis temps index=0 warnthresh=default
Temperature probe warning threshold(s) set successfully.
```

Note that only the OMSA warning thresholds can be adjusted like
this. The failure/critical thresholds are absolute and can't be set
manually.


### Blacklisting

You can blacklist failed/missing components that you won't
fix. Blacklisting means that the particular component is never
checked. The option ``-b|--blacklist`` is used for blacklisting, takes
either a string or file as input, and can be specified multiple times:

```
$ check_openmanage -s -H myhost
WARNING: Controller 0 [PERC 6/i Integrated]: Driver '00.00.03.15-RH1' is out of date
WARNING: Controller 1 [PERC 6/E Adapter]: Driver '00.00.03.15-RH1' is out of date
  
$ check_openmanage -s -H myhost -b ctrl_driver=0,1
OK - System: 'PowerEdge 1950', SN: 'XXXXXXX', 4 GB ram (4 dimms), 2 logical drives, 8 physical drives
```

Syntax for blacklisting is:

```
component1=<all|id1,id2,...>/component2=<all|id1,id2,..>/...
```

I.e. a single line separated by slashes. The component names are
listed in the manual page. Here is the list:

Component     | Comment
--------------|--------
ctrl          | Controller
ctrl_fw       | Suppress the "special" warning message about old controller firmware. Use this if you can't or won't upgrade the firmware.
ctrl_driver   | Suppress the "special" warning message about old controller driver.  Particularly useful on systems where you can't upgrade the driver.
ctrl_stdr     | Suppress the "special" warning message about old Windows storport driver.
pdisk         | Physical disk.
pdisk_cert    | Ignore warnings for non-certified physical drives
pdisk_foreign | Ignore warnings for foreign physical drives
vdisk         | Logical drive (virtual disk)
bat           | Controller cache battery
bat_charge    | Ignore warnings related to the controller cache battery charging cycle, which happens approximately every 40 days on Dell servers. Note that using this blacklist keyword makes check_openmanage ignore non-critical cache battery errors.
conn          | Connector (channel)
encl          | Enclosure
encl_fan      | Enclosure fan
encl_ps       | Enclosure power supply
encl_temp     | Enclosure temperature probe
encl_emm      | Enclosure management module (EMM)
dimm          | Memory module
fan           | Fan (Cooling device)
ps            | Powersupply
temp          | Temperature sensor
cpu           | Processor (CPU)
volt          | Voltage probe
bp            | System battery
amp           | Amperage probe (power consumption monitoring)
intr          | Intrusion sensor
sd            | Removable flash media (SD card)


The component IDs are listed in the debug output:

```
$ check_openmanage -H myhost -d
   System:      poweredge 2850
   ServiceTag:  XXXXXXX                  OMSA version:    6.1.0
   BIOS/date:   A06 10/03/2006           Plugin version:  3.5.4
-----------------------------------------------------------------------------
   Storage Components                                                        
=============================================================================
   LVL   |    ID    |  STATE                                                 
---------+----------+--------------------------------------------------------
      OK |        0 | Controller 0 [PERC 4e/Di] is Ready
CRITICAL |    0:0:0 | Physical disk 0:0 [Maxtor ATLAS15K2_146SCA, 146GB] on controller 0 needs attention: Failed
      OK |    0:0:1 | Physical disk 0:1 [146GB] on controller 0 is Online
[...etc...]
```

If we in the above example wished to blacklist the failed disk, we
would use the following as input to the ``-b|--blacklist`` option:

```
pdisk=0:0:0
```

Now the failed disk is not checked at all, and Nagios is happy.

You can also use ``all`` instead of the component IDs. For example, if
you want to ignore old drivers for all controllers:

```
$ check_openmanage -b ctrl_driver=all
```


### Check control

check_openmanage lets you fine-tune which components you want to check
via the ``--check`` option. By default almost everything is checked (as
listed in the basic overview).

The syntax for the ``--check`` option is as follows:

```
component1=<0|1>,component2=<0|1>,...
```

A value of ``0`` will turn checking off for the specified component,
while a value of ``1`` will turn checking on. Example:

```
$ check_openmanage --check storage=0,esmlog=1
```

In the above example, we turn off checking of the storage subsystem,
and adds checking of the ESM log content. You can specify the
``--check`` option multiple times. The following example will have the
same effect as the one above:

```
$ check_openmanage --check storage=0 --check esmlog=1
```

The argument to the ``--check`` option can also be a file containing
the actual arguments. If we for example make a file
``/etc/check_openmanage.check`` that contains the following:

```
storage=0,esmlog=1
```

The following example will then have the same effect as the other
examples in this paragraph:

```
$ check_openmanage --check /etc/check_openmanage.check
```

If the specified file (here ``/etc/check_openmanage.check``) doesn't
exist, it is simply ignored. You can also mix "check files" and actual
arguments:

```
$ check_openmanage  --check /etc/check_openmanage.check --check power=0
```

This option is versatile for a reason. If, for example, you're running
check_openmanage locally via NRPE, and want to have the same command
and NRPE config for all servers, you can specify a "check file" and
still be able to control the checks performed individually on each
server.

The list of legal parameters to the ``--check`` option is the same as
for the ``--only`` option described below, except for "critical" and
"warning". The full list is also given in the `manual page`_.


### Only check one component type or alert type

You can use the option ``--only`` to specify what type of component is
desired. If this option is used, only that type of component is
checked. Example:

```
$ check_openmanage --only voltage
VOLTAGE OK - 14 voltage probes checked
```

The following keywords are accepted by the ``--only`` option:

Keyword    | Effect
-----------|-------
critical   | Only output critical alerts. It is possible to use the ``--check`` option together with this option to adjust checks.
warning    | Only output warning alerts. It is possible to use the ``--check`` option together with this option to adjust checks.
chassis    | Only check chassis components, i.e. everything but storage and log content.
storage    | Only check storage components
memory     | Only check memory modules
fans       | Only check fans
power      | Only check power supplies
temp       | Only check temperatures
cpu        | Only check processors
voltage    | Only check voltage probes
batteries  | Only check batteries
amperage   | Only check power usage
intrusion  | Only check chassis intrusion
sdcard     | Only check removable flash media
servicetag | Only check for sane service tag
esmhealth  | Only check ESM log health
esmlog     | Only check ESM log content
alertlog   | Only check alertlog content

A couple of other examples:

```
$ check_openmanage --only storage -H myhost
STORAGE OK - 4 physical drives, 2 logical drives

$ check_openmanage --only fans -H myhost
FANS OK - 6 fan probes checked

$ check_openmanage --only memory -H myhost
MEMORY OK - 6 memory modules, 24576 MB total memory
```


### Check everything

Use the option ``-a`` or ``--all`` to turn on checking of everything,
even log content:

```
$ check_openmanage -a
ESM log content: 3 critical, 0 non-critical, 4 ok
```

## SELinux considerations

If you plan on using the plugin on a system with SELinux in enforcing
mode, you need to set a proper file context (label) on the
plugin. Which label to choose depends on how you plan to use the
plugin, via SNMP or locally via NRPE or similar.

### SNMP check

With SNMP, the following label should suffice:

```
nagios_services_plugin_exec_t
```

To set this file context permanently, execute the following commands:

```
semanage fcontext -a -t nagios_services_plugin_exec_t '/usr/lib(64)?/nagios/plugins/check_openmanage'
restorecon -v /usr/lib*/nagios/plugins/check_openmanage
```

### Local check

If using a local check via NRPE or similar, the plugin
executes **omreport** which is part of Dell OMSA, which in turn is a
unconfined service. Because of this, the plugin also needs to run
unconfined, i.e. using the following label:

```
nagios_unconfined_plugin_exec_t
```

To set this file context permanently, execute the following commands:

```
semanage fcontext -a -t nagios_unconfined_plugin_exec_t '/usr/lib(64)?/nagios/plugins/check_openmanage'
restorecon -v /usr/lib*/nagios/plugins/check_openmanage
```


## Configuration file

> IMPORTANT: This section describes a feature that is present in
> version 3.7.0 and later versions.

The plugin takes an optional configuration file. To specify a
configuration file, use the ``-f`` or ``--config`` option:

```
check_openmanage -f /etc/check_openmanage.conf
```

If the ``-f`` or ``--config`` option is specified, the plugin requires
the perl module ``Config::Tiny`` and will output an error if that
module is not found.

While a configuration file may be used in either local or SNMP mode,
its strengths are mostly present when using SNMP. It allows setting
different options, such as blacklisting, on single hosts or groups of
hosts using glob patterns.

### File format

The file has an ini-style syntax and consists of sections and
parameters. A section begins with the name of the section in square
brackets and continues until the next section begins. An example of
section with two keywords and parameters:

```
[section]
    key1 = boolean
    key2 = string
```

The data types used are string (no quotes needed) and bool (with
values of "TRUE/FALSE"). For boolean values, "1", "on" and "true"
are equivalent, likewise for "0", "off" and "false". They are also
case insensitive.

The root section or global section has no section name in brackets,
example:

```
key1 = false
key2 = foo

[section]
    key1 = true
    key2 = bar
```

The values set in a bracket section will override those set in the
root section, in case of conflicts.

Lines starting with "#" or ";" are considered comments and ignored, as
are blank lines.

The configuration file must be a regular file. Owner and group does
not matter, but the Nagios user must have read access.

### Sections and ordering

The section name should correspond to the hostname, i.e. the value
passed to the ``-H`` or ``--hostname`` parameter. The section name
itself can be either an exact match to the hostname, or a glob
pattern, as this example shows:

```
key1 = true
key2 = foo

[192.168.1.2]
    key1 = true

[192.168.*]
    key1 = false
    key2 = bar
```

The sections are read in order of significance. The root section is
read first. Then any sections with glob patterns that match the
hostname are read (alphabetically). Any section whose name is an exact
match to the hostname is read last.

For boolean values, any conflicts are resolved by letting the section
with the most significance (closest match to the hostname) override
any previous definitions. For string values, they are simply added
together.

In the example above, for the host "192.168.1.2" the value of **key1**
will be **true** and **key2** will be **bar**. Any other host that
matches "192.168.\*" will have **key1 = false** and **key2 = bar**. All
other hosts will have **key1 = true** and **key2 = foo**.

Normal shell globbing may be used for the section names. This is
limited to ``*``, ``?`` and ``[]``. Some examples:

```
[192.168.*]
    # matches e.g. 192.168.10.20

[192.168.10[34].1]
    # matches 192.168.103.1 and 192.168.104.1

[login?.example.com]
    # mathces e.g. login1.example.com
```

Be careful not to have more than one glob pattern section match any
single host. This may lead to unpredictable results.


### Configuration

#### General

##### Check control

Any keyword to the ``--check`` parameter are accepted in the
configuration file, as "check_KEYWORD". These options take boolean
values ("true" or "false"). The following keywords are accepted for
check control, listed here with their default values:

* check_storage = true
* check_memory = true
* check_fans = true
* check_power = true
* check_temp = true
* check_cpu = true
* check_voltage = true
* check_batteries = true
* check_amperage = true
* check_intrusion = true
* check_sdcard = true
* check_esmhealth = true
* check_servicetag = true
* check_esmlog = false
* check_alertlog = false
* check_everything = false

If used together with the ``--check`` command line option, the
command line option will override the configuration file, if there
is a conflict.

The option **check_everything** is special and turns on all
checks. Setting this option to "true" will effectively negate any
other check options. This option corresponds to the ``-a`` or
``--all`` command line option.

##### Blacklisting

For blacklisting the keyword **blacklist** is used, and the value is
a string that uses the same syntax as the ``-b`` or ``--blacklist``
parameter. Example:

```
blacklist = ctrl_fw=all/pdisk=0:0:1
```

If used together with the ``-b`` or ``--blacklist`` command line
option, the two blacklists from the config file and command line are
merged together.

##### Timeout

The plugin timeout can be configured with the **timeout**
keyword. The argument is number of seconds and should be a positive
integer. Example:

```
timeout = 60
```

The corresponding command line option is ``-t`` or ``--timeout``.

##### Performance data

Performance data can be turned on in the configuration file with
``performance_data``. Accepted values are boolean (TRUE/FALSE) or
either of the keywords "minimal" and "multiline". Example:

```
performance_data = true
```

The corresponding command line option is ``-p`` or ``--perfdata``.

##### Legacy Performance data

With version 3.7.0, performance data output changed. The new format
is not compatible with the old format. Users who wish to postpone
switching to the new performance data API may use this option. This
option takes a boolean value. Example:

```
legacy_performance_data = true
```

The corresponding command line option is ``--legacy-perfdata``.

##### Temperature unit

The temperature unit used for reporting, performance data etc. can
be set with the **temperature_unit** option. Allowed values are ``F``
for Fahrenheit, ``C`` for Celsius, ``K`` for Kelvin and ``R`` for
Rankine. Example:

```
temperature_unit = F
```

The corresponding command line option is ``--tempunit``. Note that
the command line option ``-F`` or ``--fahrenheit`` will override
both the command line option and the configuration file option.

##### Temperature limits

Custom temperature limits may be configured with the
options **temp_threshold_warning**
and **temp_threshold_critical**. These options corresponds to the
command line options ``-w`` or ``--warning`` and ``-c`` or
``--critical``, respectively. They take the same arguments as the
command line options. Examples:

```
temp_threshold_warning = 0=30/10
temp_threshold_critical = 0=35/8
```


#### SNMP

Several SNMP related options may be set in the configuration file. The
configuration file may contain the following SNMP options:

##### SNMP community string

The SNMP community string can be set with **snmp_community**. Example:

```
snmp_community = mycommunity
```

Corresponding command line option: ``-C`` or ``--community``

##### SNMP protocol version

The SNMP protocol version can be set with **snmp_version**. Example:

```
snmp_version = 2
```

Corresponding command line option: ``-P`` or ``--protocol``

##### SNMP port number

The remote port number used with SNMP can be set with
**snmp_port**. Example:

```
snmp_port = 161
```

Corresponding command line option: ``--port``

##### Use IPv6 instead of IPv4

The option **snmp_use_ipv6** instructs the plugin to use IPv6 instead
of IPv4. This option takes a boolean value. Example:

```
snmp_use_ipv6 = true
```

Corresponding command line option: ``-6`` or ``--ipv6``

##### Use TCP instead of UDP

The option **snmp_use_tcp** instructs the plugin to use TCP instead
of UDP. This option takes a boolean value. Example:

```
snmp_use_tcp = true
```

Corresponding command line option: ``--tcp``


#### Output control

These options gives some control over the output given by the plugin.

##### Include servicetag in alerts

The option **output_servicetag** will make the plugin include the
servers servicetag (serial number) in every alert. This option takes
a boolean value. Example:

```
output_servicetag = true
```

Corresponding command line option: ``-i`` or ``--info``

##### Include service state in alerts

The option **output_servicestate** will make the plugin include the
service state in any alerts. This option takes a boolean
value. Example:

```
output_servicestate = true
```

Corresponding command line option: ``-s`` or ``--state``

##### Include abbreviated service state in alerts

The option **output_servicestate_abbr** will make the plugin include
the abbreviated service state in any alerts. This option takes a
boolean value. Example:

```
output_servicestate_abbr = true
```

Corresponding command line option: ``-S`` or ``--short-state``

##### Show system info with alerts

The option **output_sysinfo** will make the plugin output some system
information with alerts. This option takes a boolean value. Example:

```
output_sysinfo = true
```

Corresponding command line option: ``-e`` or ``--extinfo``

##### Show blacklistings in OK output

The option **output_blacklist** will make the plugin show any
blacklistings in the OK output. This option takes a boolean
value. Example:

```
output_blacklist = true
```

Corresponding command line option: ``-B`` or ``--show-blacklist``

##### Verbosity of OK output

The option **output_ok_verbosity** lets you adjust how much
information is shown in the OK output. This option takes a positive
integer as parameter. Example:

```
output_ok_verbosity = 3
```

Corresponding command line option: ``-o`` or ``--ok-info``

##### HTML output

The output **output_html** makes the plugin produce HTML output. This
option takes either a boolean value, or a country or area
code. Example:

```
output_html = de
```

Corresponding command line option: ``-I`` or ``--htmlinfo``

##### Custom line after alerts

The option **output_post_message** lets you specify one line of
information to be shown after any alerts. This option takes a string
as parameter. Examples:

```
output_post_message = OS: %o %r
output_post_message = NOTE: Handled in ticket 123456
```

For more information about codes and formatting, see `Custom line
after the alert(s)`_.

##### Hide service tag (serial number)

The option **output_hide_servicetag** will hide the serial number in
any output from the plugin. This option takes a boolean
value. Example:

```
output_hide_servicetag = true
```

Corresponding command line option: ``--hide-servicetag``


## Alternative to configuration file: Custom object variables

*Contributed by Rudolf Kleijwegt*

If using a configuration file for the plugin does not appeal to you,
you may want to look into the custom object variables feature of
Nagios, to achieve a per-host configuration of the plugin. Consider
the following example host definition:

```
define host {
    use                   generic-host
    host_name             myhostname
    alias                 My Alias
    address               1.2.3.4
    max_check_attempts    3
    _openmanage_options   --no-storage
}
```

In this example, we are using the ``--no-storage`` option for this
host only. This is achieved by the following command definition for
check_openmanage:

```
define command {
    command_name    check_openmanage
    command_line    $USER1$/check_openmanage -H $HOSTADDRESS$ '$_HOSTOPENMANAGE_OPTIONS$'
}
```

Note the ``$_HOSTOPENMANAGE_OPTIONS$`` macro in the command
definition. The variable ``_openmanage_options`` in the host
definition is converted to uppercase characters.

For more information about custom object variables, refer to the
Nagios documentation:

* http://nagios.sourceforge.net/docs/3_0/customobjectvars.html


## A note about charging cache batteries

The Dell RAID controllers usually have a cache battery, which is
useful in case of a sudden power outage. This battery will on occasion
drain out, and needs recharging. The hardware takes care of this, but
gives a warning. What happens is this (assuming that the storage
subsystem is checked, which is the default):

1. The hardware senses that the battery has low power, reports it and
   as a result check_openmanage outputs the following:

   ```
   Cache battery 0 in controller 0 is Power Low [probably harmless]
   ```

2. After a while, the battery enters "learning state", where the
   battery learns its capacity, and check_openmanage will report this
   as:
   ```
   Cache battery 0 in controller 0 is Learning (Active) [probably harmless]
   ```

3. The battery then enters a recharge state, which check_openmanage
   will also report:
   ```
   Cache battery 0 in controller 0 is Charging [probably harmless]
   ```

One could argue that check_openmanage should simply ignore these
warnings, as they occur regularly on all controllers with a cache
battery. They could be viewed as informational. However, for the odd
case where the charge cycle never finishes, the warnings are reported
by check_openmanage, but with an included note that says that the
warning is probably harmless.

> NOTE: You can use the blacklist keyword ``bat_charge`` to disable
> messages about the battery charge cycle.

If the state never changes, e.g. if the warning persists for days, you
should act on it. Otherwise, the warnings can be ignored.


## Performance data

check_openmanage will output performance data if the ``--perfdata`` or
``-p`` option is used. The performance data gathered will vary
depending on the type and model of the monitored server. An example
graph using [PNP4Nagios](http://www.pnp4nagios.org/) is given below.

![pnp4nagios](http://folk.uio.no/trondham/software/pnp_check_openmanage-3.7.1.png)

The template used to generate these graphs are available as
``check_openmanage.php`` in the downloadable ZIP archive and tarball.


## Frequently Asked Questions (FAQ)

### General

#### Why did you make check_openmanage?

I wanted a monitoring tool for our Dell servers that was as good as
Gerhard Lausser's check_hpasm plugin is for HP servers. None of the
existing Dell plugins offered the features and detailed output that I
needed, so I made my own plugin. After a while, I decided to share it
with the community, and I've never regretted this
decision. Constructive feedback from users have improved this plugin
immensely.

#### How does check_openmanage compare to other Dell plugins?

Like most Nagios plugins that check the hardware health of Dell
servers, check_openmanage uses OpenManage Server Administrator (OMSA)
from Dell. The difference is in the level of detail in the
output. Most other plugins simply check the status level of the
subsystems and return the status code, while check_openmanage also
tries to figure out exactly what is wrong and report it in a detailed
and concise manner.

#### Why both 32bit and 64bit RPM packages?

This is a perl script, which is architecture independent by itself, so
one would think that the RPM arch would be "noarch". However, since
the official Nagios plugins package (and others, e.g. Fedora) put
Nagios plugins under ``/usr/lib`` for 32bit and ``/usr/lib64`` for
64bit platforms, I wanted to apply this to check_openmanage as
well. This is the *only* difference between the two RPM packages.

#### I don't like check_openmanage, are there other Dell plugins that you would recommend?

There are many plugins out there. The ones I would recommend are Jason
Ellison's excellent
[check_dell_openmanage](http://exchange.nagios.org/directory/Plugins/Hardware/Server-Hardware/Dell/check_dell_openmanage/details)
plugin and [HPC Community's "official"
plugin](http://exchange.nagios.org/directory/Plugins/Hardware/Server-Hardware/Dell/Dell-OpenManage-Nagios-Plugin/details). They
are both SNMP only, and are simpler then check_openmanage in the sense
that they don't check as many OIDs and don't use as many features of
OMSA. That way, they are less dependent on recent OMSA versions, have
faster execution time, and are probably also less error prone.

#### Is there a compiled executable for Windows that can be used with NSClient++?

Yes. See the paragraph about local check on windows.

#### Is VMware ESXi supported?

Unfortunately, no. OMSA for ESXi has serious limitations that impacts
monitoring with check_openmanage. The following quote is from the [OMSA
6.4 documentation intro](http://support.dell.com/support/edocs/software/svradmin/6.4/en/UG/HTML/intro.htm):

> NOTE: While ESXi supports SNMP traps, it does not support hardware
> inventory through SNMP.

For ESXi you can't use check_openmanage. Options for monitoring ESXi
include setting up an SNMP trap receiver on the Nagios server, and
configuring it as the trap destination on the ESXi hosts.

#### Is check_openmanage ePN compatible?

("ePN" is the Nagios' embedded perl interpreter. Many people turn this
off by default, as it often does more damage than good.)

Yes, check_openmanage if fully ePN compatible, and should work fine
with or without ePN. If your Nagios server has ePN turned on, and for
some reason you want to disable ePN for this plugin, add the following
line among the first 10 lines of the script:

```
# nagios: -epn
```

If you are running Nagios 2.x, you should specify ``perl <script>`` in
your Nagios config if you have ePN enabled and want to disable it for
this plugin.


#### How can I contact you if I have problems, bug reports, feature requests etc.?

You can contact me via email (see the manual page). Depending on the
time of day etc., you can also reach me on the IRC, as ``trondham`` on
the ``#nagios`` channel on Freenode.


### OpenManage (OMSA)

#### Which version of OMSA is OK?

Check_openmanage is tested with OMSA versions 5.3 and later. All these
should be OK to use. Note that some versions don't play well with
Windows and SNMP. For the best results, use the newest release
available.

#### Why do I get weird results from check_openmanage on my old server and/or old OMSA version?

Upgrade your Openmanage (OMSA) version. Check_openmanage is developed
with OMSA versions 5.3 and later. You should always try to use
the latest version. Older versions of OMSA lack some of the features
that check_openmanage needs to give accurate and detailed output.

Some really old servers (5.generation and older, e.g. 2550, 2450)
can't run newer OMSA than 4.5. Hence, these old servers are not
supported by check_openmanage.

#### How can I find out which version of OMSA my server is running?

This can be done in many ways. Log in to your server and type the
command ``omreport about`` (or ``omreport.exe about`` on Windows):

```
$ omreport about

Product name : Server Administrator
Version      : 6.1.0
Copyright    : Copyright (C) Dell Inc. 1995-2009. All rights reserved.
Company      : Dell Inc.
```  

You can also use check_openmanage to display the OMSA version, with
the ``-d`` or ``--debug`` option:

```
$ check_openmanage -H myhost -d | head -n 3
 System:      PowerEdge M600
 ServiceTag:  88CBS3J                  OMSA version:    6.1.0
 BIOS/date:   2.1.4 08/15/2008         Plugin version:  3.5.5
```


#### My boss won't let me upgrade OMSA

Your boss don't get it. Also, that wasn't technically a question.

#### Our security policy doesn't allow OMSA upgrades

Your security policy is broken. That wasn't a question either.


### Common Errors

#### ERROR: Dell OpenManage Server Administrator (OMSA) is not installed

This error normally indicates that OpenManage is not installed on the
host. However, it may be that OpenManage is installed in a location
other than the default. In this case, the omreport binary is not in
the search path of check_openmanage. You can work around this by using
the ``--omreport`` option, like this:

```
check_openmanage --omreport /usr/local/bin/omreport
```

The above should be configured in ``nrpe.cfg`` if you're using
NRPE. If you're using the .exe file for Windows with NSClient++, you
should configure this in the file ``nsc.ini`` on the host. Example:

```
check_openmanage.exe --omreport P:\dellopenmanage\oma\bin\omreport.exe
```

If the omreport or omreport.exe binary is installed in a place where
you think that check_openmanage should look by default, send me a
note.

#### ERROR: You need perl module Net::SNMP to run check_openmanage in SNMP mode

The perl module ``Net::SNMP`` is not installed, or not available to
the perl interpreter. This module is required for SNMP. See the
prerequisites_ section.

#### SNMP CRITICAL: No response from remote host '10.1.2.3'

This error indicates that the SNMP daemon is not running or not
responding. There is no reply from the monitored server on port 161
(or the port specified by the ``--port`` option). Check the SNMP
service.

You will also get this error if the SNMP community name doesn't
match. If this is the case, inspect the SNMP settings on the server
and verify that the community name matches the ``-C`` option given to
the plugin.

#### ERROR: (SNMP) OpenManage is not installed or is not working correctly

This error indicates that the SNMP service is responding, but
OpenManage OIDs are not present. To be specific, check_openmanage
checks the OID ``1.3.6.1.4.1.674.10892.1.300.10.1.9.1`` for the
chassis model name, to determine if OpenManage is running or not.

The error could be caused by different problems. OpenManage could
simply not be running. The SNMP daemon may not be configured with
OpenManage OIDs. The SNMP part of OpenManage may not be installed or
running. For Linux, the ``snmpd.conf`` file should have the
following:

```
smuxpeer .1.3.6.1.4.1.674.10892.1
```

This should be added by OpenManage at install time. The simple
solution may be to reinstall OpenManage and look for errors during
installation.

#### SNMP CRITICAL: Received genError(5) error-status at error-index 1

Some OpenManage versions perform poorly on Windows, especially
versions 5.4.0 and earlier. If you get this error on a Windows host,
check the OpenManage version and consider upgrading.

This error is from the Net::SNMP perl module.


#### PLUGIN TIMEOUT: check_openmanage timed out after 30 seconds

If your server is under very heavy load, it may take some time for
check_openmanage to finish, especially if you run the plugin locally
via NRPE or similar. The default plugin timeout is 30 seconds, but you
can set a different timeout via the ``-t|--timeout`` option. Under
normal load, the plugin should finish in about 2 seconds when run
locally. Checking via SNMP is even faster.

Be aware that NRPE has it's own timeout, also adjustable.


#### INTERNAL ERROR: blah blah

The plugin will output any perl warnings that occur during execution
as internal errors with unknown state. If you get one or more internal
errors like this, you may have hit a bug in the plugin. Please contact
me if you get internal errors.


#### UNKNOWN: Problem running 'omreport foo bar'

```
UNKNOWN: Problem running 'omreport chassis fans': Error! No fan probes found on this system.
UNKNOWN: Problem running 'omreport chassis temps': Error! No temperature probes found on this system.
UNKNOWN: Problem running 'omreport chassis volts': Error! No voltage probes found on this system.
[...etc...]
```

These are general errors that can have different causes. OMSA,
especially old versions, are known to have bugs that cause the
occasional hiccup. A simple restart of OMSA (Linux: "srvadmin-services.sh
restart") may prove to be the solution and should be
attempted first.

Other known causes include the following:

* Old or incompatible BIOS version. For example, later versions of
  OMSA may expect the BIOS version to be relatively up-to-date. Make
  sure that the BIOS version is new enough.

* Exhausted semaphore pool on Linux. OMSA requires a few semaphores in
  order to run commands. Check the semaphore resource pool
  with **ipcs** and consider increasing the number of semaphores
  available.

In case of exhausted semaphore pool, you should investigate if there
are times when the plugin times out during execution. In case of a
timeout, any omreport command that the plugin is running is abruptly
aborted. Any semaphores allocated by omreport is not freed, and if
this happens frequently the system will eventually run out of
semaphores. There are three workarounds for this problem:

1. Switch to checking the system via SNMP, which does not require
   running omreport, and thus does not use semaphores. In general SNMP
   checking is faster and requires less resources on the monitored
   host, and is therefore usually the best option for monitoring
   heavily loaded systems.

2. Increase the timeout of the plugin. The default timeout is 30
   seconds, you may change this with the ``-t`` or ``--timeout``
   parameter. Note that the timeout of check_nrpe (or whichever
   mechanism you have chosen to run remote plugins) should be
   increased accordingly.

3. Periodically remove allocated semaphores which belong to the user
   running the plugin. This can be achieved like this (example for the
   user "nrpe"):

   ```
   ipcrm $(ipcs -s | awk '/nrpe/ {print "-s ",$2}')
   ```

   This approach is a last resort. The commands above can be run
   manually whenever the issue appears, or periodically by crond.

#### Storage Error! No controllers found

From version 3.6.0 of the plugin, storage is no longer allowed to be
absent. The reason for this is that there have been several issues
with OMSA that prevents storage from being displayed. Check_openmanage
will no longer silently ignore this when it happens. Instead, it will
give this alert.

There may be cases where this alert is a false positive:

* Diskless systems
* Servers without a Dell storage controller, which OMSA does not
  recognize.

If you get this alert and your system falls into one of the above
categories, you should specify either **--no-storage** or **--check
storage=0** to prevent check_openmanage to check the storage system in
the first place:

```
check_openmanage --no-storage [other options...]
check_openmanage --check storage=0 [other options...]
```

If the server in question has storage that should be monitored, you'll
have to check OMSA, particularly which components of it are installed.


## Reporting bugs, proposing new features etc.

.. _University of Oslo: http://www.uio.no/english/

Please let me know if you are experiencing bugs, have feature
requests, or suggestions on how to improve check_openmanage. We use
this plugin in production at the [University of Oslo](http://www.uio.no/english/), on many Dell
servers of different models, but we don't use all the different
features of the plugin. While the plugin is bug-free for us, it might
not be for you, so let me know if you have problems.

Please send bug reports or feature requests to me directly, as the old
nagios-user mailing list is dead.

Depending on the time of day etc., you can also reach me on the
IRC, on the ``#nagios`` channel on Freenode.


## Disclaimer

This is free software. Use at your own risk.

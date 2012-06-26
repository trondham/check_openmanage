#!/bin/sh

# Find correct lib dir
if [ "`uname -m`" = "x86_64" ]; then
    libdir=/usr/lib64
else
    libdir=/usr/lib
fi

# Default install locations
def_plugindir=${libdir}/nagios/plugins
def_mandir=/usr/share/man

# Find install locations
if [ "$1" = "-q" ]; then
    plugindir=$def_plugindir
    mandir=$def_mandir
else
    echo -n "Plugin dir [$def_plugindir]: "
    read plugindir
    if [ "$plugindir" = "" ]; then
	plugindir=$def_plugindir
    fi
    echo -n "Man page dir [$def_mandir]: "
    read mandir
    if [ "$mandir" = "" ]; then
	mandir=$def_mandir
    fi
fi

man5dir=$mandir/man5
man8dir=$mandir/man8

# Error if plugin dir doesn't exist
if [ -d $plugindir ]; then
    :
else
    echo "ERROR: Plugin directory $plugindir doesn't exist,"
    echo "ERROR: or is not a directory"
    exit 1
fi

# Error if man dir doesn't exist
if [ -d $mandir ]; then
    :
else
    echo "ERROR: Man page directory $mandir doesn't exist,"
    echo "ERROR: or is not a directory"
    exit 1
fi

# Install
install -p -m 0755 check_openmanage $plugindir
install -m 0644 man/check_openmanage.8 $man8dir
install -m 0644 man/check_openmanage.conf.5 $man5dir

# Done
echo "done."
exit 0

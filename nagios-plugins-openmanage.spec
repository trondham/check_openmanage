# Name of the plugin
%global plugin check_openmanage

# No binaries here, do not build a debuginfo package
%global debug_package %{nil}

# SUSE installs Nagios plugins under /usr/lib, even on 64-bit
# It also uses noarch for non-binary Nagios plugins and has different
# package names for docbook.
%if %{defined suse_version}
%global nagiospluginsdir /usr/lib/nagios/plugins
%global docbookpkg docbook-xsl-stylesheets
BuildArch:     noarch
%else
%global nagiospluginsdir %{_libdir}/nagios/plugins
%global docbookpkg docbook-style-xsl
%endif

Name:          nagios-plugins-openmanage
Version:       3.7.12
Release:       1%{?dist}
Summary:       Nagios plugin to monitor hardware health on Dell servers

Group:         Applications/System
License:       GPLv3+
URL:           http://folk.uio.no/trondham/software/%{plugin}.html
Source0:       http://folk.uio.no/trondham/software/files/%{plugin}-%{version}.tar.gz

BuildRoot:     %{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)

# Building requires Docbook XML
BuildRequires: libxslt
BuildRequires: libxml2
BuildRequires: %{docbookpkg}

# Rpmbuild doesn't find these perl dependencies
Requires:      perl(Config::Tiny)
Requires:      perl(Net::SNMP)

# Owns the nagios plugins directory
%if 0%{?rhel} > 5 || 0%{?fedora} > 18
Requires: nagios-common
%else
Requires: nagios-plugins
%endif

# Make the transition to Fedora/EPEL packages easier for existing
# users of the non-Fedora/EPEL RPM packages
Provides:      nagios-plugins-check-openmanage = %{version}-%{release}
Obsoletes:     nagios-plugins-check-openmanage < 3.7.2-3

%description
check_openmanage is a plugin for Nagios which checks the hardware
health of Dell servers running OpenManage Server Administrator
(OMSA). The plugin can be used remotely with SNMP or locally with
NRPE, check_by_ssh or similar, whichever suits your needs and
particular taste. The plugin checks the health of the storage
subsystem, power supplies, memory modules, temperature probes etc.,
and gives an alert if any of the components are faulty or operate
outside normal parameters.

%prep
%setup -q -n %{plugin}-%{version}
rm -f %{plugin}.exe

%build
%if 0%{?rhel} > 5 || 0%{?fedora} > 18
pushd man
make clean && make
popd
%else
: # use pre-built man-pages on old systems
%endif

%install
rm -rf %{buildroot}
install -Dp -m 0755 %{plugin} %{buildroot}%{nagiospluginsdir}/%{plugin}
install -Dp -m 0644 man/%{plugin}.8 %{buildroot}%{_mandir}/man8/%{plugin}.8
install -Dp -m 0644 man/%{plugin}.conf.5 %{buildroot}%{_mandir}/man5/%{plugin}.conf.5

%clean
rm -rf %{buildroot}

%files
%defattr(-, root, root, -)
%doc README COPYING CHANGES example.conf
%{nagiospluginsdir}/%{plugin}
%{_mandir}/man8/%{plugin}.8*
%{_mandir}/man5/%{plugin}.conf.5*


%changelog
* Mon Jul 28 2014 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.7.12-1
- Release 3.7.12
- Conditionalize building man pages for rhel6+ and fedora19+ (others
  will use pre-built man pages)
- Conditionalize require nagios-common (rhel6+/fedora19+) or
  nagios-plugins (others) for owner of the plugins directory
- Drop perl(Crypt::Rijndael) requirement, as it provides optional and
  very rarely used functionality

* Tue Aug  6 2013 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.7.11-1
- Version 3.7.11

* Fri Jul 19 2013 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.7.10-1
- Version 3.7.10

* Mon Jan  7 2013 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.7.9-1
- Version 3.7.9

* Wed Dec 12 2012 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.7.8-1
- Version 3.7.8

* Thu Dec  6 2012 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.7.7-1
- Version 3.7.7

* Thu Jun 28 2012 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.7.6-1
- Version 3.7.6
- Added BuildRequires for Docbook XML (manual pages)
- Changed building of manual pages

* Fri Apr 13 2012 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.7.5-1
- Version 3.7.5

* Thu Mar 29 2012 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.7.4-1
- Version 3.7.4

* Mon Dec 12 2011 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.7.3-4
- Added some SUSE spec file compatibility

* Mon Nov 28 2011 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.7.3-3
- Provide example config file as documentation rather than installing
  it under /etc/nagios
- Remove win32 binary in prep section

* Tue Nov 15 2011 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.7.3-2
- Spec file changes which address issues raised in rhbz#743615

* Wed Oct  5 2011 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.7.3-1
- Version 3.7.3
- RPM name changed to nagios-plugins-openmanage
- Added obsoletes for old name

* Tue Sep 27 2011 Xavier Bachelot <xavier@bachelot.org> - 3.7.2-2
- Add a commented configuration file.
- Add some Requires to have all features out of the box.
- Add Requires on nagios-plugins for {_libdir}/nagios/plugins directory.
- Remove some useless command macros.
- Fix Obsoletes/Provides.

* Mon Sep 19 2011 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.7.2-1
- Version 3.7.2

* Mon Aug 22 2011 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.7.1-1
- Version 3.7.1

* Mon Aug 15 2011 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.7.0-1
- Version 3.7.0

* Mon Jun 06 2011 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.6.8-1
- Version 3.6.8

* Thu May 12 2011 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.6.7-1
- Version 3.6.7

* Thu Apr 28 2011 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.6.6-1
- Version 3.6.6

* Wed Feb  9 2011 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.6.5-1
- Version 3.6.5

* Tue Jan  4 2011 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.6.4-2
- Don't compress the man page, rpmbuild takes care of that. Thanks to
  Jose Pedro Oliveira for a patch that fixes this.

* Tue Jan  4 2011 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.6.4-1
- Version 3.6.4
- Initial build with new spec file
- Spec file adapted to Fedora/EPEL standards

* Mon Dec 13 2010 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.6.3-1
- Version 3.6.3

* Thu Nov 25 2010 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.6.2-1
- Version 3.6.2

* Tue Nov  2 2010 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.6.1-1
- Version 3.6.1

* Mon Aug 30 2010 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.6.0-1
- Version 3.6.0

* Wed Jul 14 2010 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.5.10-1
- Version 3.5.10

* Tue Jun 29 2010 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.5.9-1
- Version 3.5.9

* Thu Jun 17 2010 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.5.8-1
- Version 3.5.8

* Fri Mar 19 2010 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.5.7-1
- Version 3.5.7

* Tue Feb 23 2010 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.5.6-1
- Version 3.5.6

* Fri Jan 22 2010 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.5.5-1
- Version 3.5.5

* Wed Jan 13 2010 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.5.4-1
- Version 3.5.4

* Thu Dec 17 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.5.3-1
- Version 3.5.3

* Tue Nov 17 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.5.2-1
- Version 3.5.2

* Thu Oct 22 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.5.1-1
- Version 3.5.1

* Tue Oct 13 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.5.0-1
- Version 3.5.0
- New location for the manual page (section 3 -> 8)

* Fri Aug  7 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.4.9-1
- Version 3.4.9

* Fri Jul 31 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.4.8-1
- Version 3.4.8

* Fri Jul 24 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.4.7-1
- Version 3.4.7

* Tue Jul  7 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.4.6-1
- Version 3.4.6

* Mon Jun 22 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.4.5-1
- Version 3.4.5

* Mon Jun 22 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.4.4-1
- Version 3.4.4

* Thu Jun 11 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.4.3-1
- Version 3.4.3

* Wed Jun  3 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.4.2-1
- Version 3.4.2

* Wed May 27 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.4.1-1
- Version 3.4.1

* Mon May 25 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.4.0-1
- Version 3.4.0

* Tue May  5 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.3.2-1
- Version 3.3.2

* Tue Apr 28 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.3.1-1
- Version 3.3.1

* Tue Apr  7 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.3.0-1
- Version 3.3.0

* Sun Mar 29 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.2.7-1
- Version 3.2.7

* Thu Mar  5 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.2.6-1
- Version 3.2.6

* Tue Feb 24 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.2.5-1
- Version 3.2.5
- take 64bit (other libdir) into consideration

* Tue Feb 17 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.2.4-1
- Version 3.2.4

* Mon Feb  9 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.2.3-1
- Version 3.2.3

* Tue Feb  3 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.2.2-1
- Version 3.2.2

* Tue Feb  3 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.2.1-1
- Version 3.2.1

* Tue Jan 27 2009 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.2.0-1
- Version 3.2.0

* Sat Dec 20 2008 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.0.2-1
- Version 3.0.2

* Thu Dec  4 2008 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 3.0.0-1
- Version 3.0.0

* Wed Nov 19 2008 Trond Hasle Amundsen <t.h.amundsen@usit.uio.no> - 2.1.0-0
- first RPM release

# Definitions used throughout the spec file
%global plugin check_openmanage
%global nagiospluginsdir %{_libdir}/nagios/plugins

# No binaries here, do not build a debuginfo package
%global debug_package %{nil}

Name:          nagios-plugins-check-openmanage
Version:       3.7.1
Release:       1%{?dist}
Summary:       Nagios plugin to monitor hardware health on Dell servers

Group:         Applications/System
License:       GPLv3+
URL:           http://folk.uio.no/trondham/software/%{plugin}.html
Source0:       http://folk.uio.no/trondham/software/files/%{plugin}-%{version}.tar.gz

BuildRoot:     %{_tmppath}/%{name}-%{version}-%{release}-root-%(%{__id_u} -n)

BuildRequires: perl

Obsoletes:     check_openmanage <= 3.6.3-1

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

%build
pod2man -s 8 -r "%{plugin} %{version}" -c "Nagios plugin" %{plugin}.pod %{plugin}.8
pod2man -s 5 -r "%{plugin} %{version}" -c "Nagios plugin" %{plugin}.conf.pod %{plugin}.5

%install
%{__rm} -rf %{buildroot}
%{__install} -d -m 0755 %{buildroot}%{nagiospluginsdir}
%{__install} -d -m 0755 %{buildroot}%{_mandir}/man8
%{__install} -d -m 0755 %{buildroot}%{_mandir}/man5
%{__install} -pD -m 0755 %{plugin} %{buildroot}%{nagiospluginsdir}
%{__install} -pD -m 0644 %{plugin}.8 %{buildroot}%{_mandir}/man8
%{__install} -pD -m 0644 %{plugin}.conf.5 %{buildroot}%{_mandir}/man5

%clean
%{__rm} -rf %{buildroot}

%files
%defattr(-, root, root, -)
%doc README COPYING CHANGES
%{nagiospluginsdir}/*
%{_mandir}/man8/*.8*
%{_mandir}/man5/*.5*


%changelog
* Mon Aug 22 2011 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.7.1-1
- Version 3.7.1

* Mon Aug 15 2011 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.7.0-1
- Version 3.7.0

* Tue Jun 06 2011 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.6.8-1
- Version 3.6.8

* Thu May 12 2011 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.6.7-1
- Version 3.6.7

* Thu Apr 28 2011 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.6.6-1
- Version 3.6.6

* Wed Feb  9 2011 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.6.5-1
- Version 3.6.5

* Tue Jan  4 2011 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.6.4-2
- Don't compress the man page, rpmbuild takes care of that. Thanks to
  Jose Pedro Oliveira for a patch that fixes this.

* Tue Jan  4 2011 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.6.4-1
- Version 3.6.4
- Initial build with new spec file
- Spec file adapted to Fedora/EPEL standards

* Mon Dec 13 2010 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.6.3-1
- Version 3.6.3

* Thu Nov 25 2010 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.6.2-1
- Version 3.6.2

* Tue Nov  2 2010 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.6.1-1
- Version 3.6.1

* Mon Aug 30 2010 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.6.0-1
- Version 3.6.0

* Wed Jul 14 2010 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.5.10-1
- Version 3.5.10

* Tue Jun 29 2010 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.5.9-1
- Version 3.5.9

* Thu Jun 17 2010 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.5.8-1
- Version 3.5.8

* Wed Mar 19 2010 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.5.7-1
- Version 3.5.7

* Tue Feb 23 2010 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.5.6-1
- Version 3.5.6

* Fri Jan 22 2010 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.5.5-1
- Version 3.5.5

* Wed Jan 13 2010 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.5.4-1
- Version 3.5.4

* Thu Dec 17 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.5.3-1
- Version 3.5.3

* Tue Nov 17 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.5.2-1
- Version 3.5.2

* Thu Oct 22 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.5.1-1
- Version 3.5.1

* Tue Oct 13 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.5.0-1
- Version 3.5.0
- New location for the manual page (section 3 -> 8)

* Fri Aug  7 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.4.9-1
- Version 3.4.9

* Fri Jul 31 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.4.8-1
- Version 3.4.8

* Fri Jul 24 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.4.7-1
- Version 3.4.7

* Tue Jul  7 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.4.6-1
- Version 3.4.6

* Mon Jun 22 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.4.5-1
- Version 3.4.5

* Mon Jun 22 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.4.4-1
- Version 3.4.4

* Thu Jun 11 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.4.3-1
- Version 3.4.3

* Wed Jun  3 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.4.2-1
- Version 3.4.2

* Mon May 27 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.4.1-1
- Version 3.4.1

* Mon May 25 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.4.0-1
- Version 3.4.0

* Tue May  5 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.3.2-1
- Version 3.3.2

* Tue Apr 28 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.3.1-1
- Version 3.3.1

* Tue Apr  7 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.3.0-1
- Version 3.3.0

* Sun Mar 29 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.2.7-1
- Version 3.2.7

* Thu Mar  5 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.2.6-1
- Version 3.2.6

* Tue Feb 24 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.2.5-1
- Version 3.2.5
- take 64bit (other libdir) into consideration

* Tue Feb 17 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.2.4-1
- Version 3.2.4

* Mon Feb  9 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.2.3-1
- Version 3.2.3

* Tue Feb  3 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.2.2-1
- Version 3.2.2

* Tue Feb  3 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.2.1-1
- Version 3.2.1

* Tue Jan 27 2009 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.2.0-1
- Version 3.2.0

* Sat Dec 20 2008 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.0.2-1
- Version 3.0.2

* Thu Dec  4 2008 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 3.0.0-1
- Version 3.0.0

* Wed Nov 19 2008 Trond H. Amundsen <t.h.amundsen@usit.uio.no> - 2.1.0-0
- first RPM release

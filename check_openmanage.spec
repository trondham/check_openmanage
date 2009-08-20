Summary:   Nagios plugin to check hardware health on Dell servers
Name:      check_openmanage
Version:   3.4.9
Release:   1%{?dist}
License:   GPL
Packager:  Trond Hasle Amundsen <t.h.amundsen@usit.uio.no>
Group:     Applications/System
BuildRoot: %{_tmppath}/%{name}-%{version}-root
URL:       http://folk.uio.no/trondham/software/%{name}.html
Source0:   http://folk.uio.no/trondham/software/files/%{name}-%{version}.tar.gz
Requires: perl

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
%setup -q

%build
gzip %{name}.3pm
mkdir -p %{buildroot}/%{_libdir}/nagios/plugins/contrib
mkdir -p %{buildroot}/%{_mandir}/man3

%install
install -d -m 0755 %{buildroot}/%{_libdir}/nagios/plugins/contrib
install -m 0755 %{name} %{buildroot}/%{_libdir}/nagios/plugins/contrib
install -m 0755 %{name}.3pm.gz %{buildroot}/%{_mandir}/man3
pushd %{buildroot}/%{_libdir}/nagios/plugins/contrib
ln -s %{name} %{name}_alertlog
ln -s %{name} %{name}_batteries
ln -s %{name} %{name}_cpu
ln -s %{name} %{name}_esmlog
ln -s %{name} %{name}_fans
ln -s %{name} %{name}_intrusion
ln -s %{name} %{name}_memory
ln -s %{name} %{name}_power
ln -s %{name} %{name}_pwrmonitor
ln -s %{name} %{name}_storage
ln -s %{name} %{name}_temperature
ln -s %{name} %{name}_esmhealth
popd

%clean
rm -rf $RPM_BUILD_ROOT

%files
%defattr(-, root, root, -)
%{_libdir}/nagios/plugins/contrib/%{name}*
%attr(0755, root, root) %{_mandir}/man3/%{name}.3pm.gz

%changelog
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

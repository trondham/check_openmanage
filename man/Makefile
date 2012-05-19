# Special variables
DOCBOOK_XSLT   = http://docbook.sourceforge.net/release/xsl/current/manpages/profile-docbook.xsl
XSLTPROC       = /usr/bin/xsltproc
XSLTPROC_FLAGS = --catalogs --xinclude --nonet --stringparam profile.condition "with_false"
XMLLINT        = /usr/bin/xmllint
XMLLINT_FLAGS  = --catalogs --postvalid --nonet --xinclude --noout
SGML_CATALOG_FILES = /etc/xml/catalog

export SGML_CATALOG_FILES

MANS =  check_openmanage.8 check_openmanage.conf.5

default: man

man: $(MANS)

clean:
	rm $(MANS)

check_openmanage.8: check_openmanage.8.xml
	$(XMLLINT) $(XMLLINT_FLAGS) $<
	$(XSLTPROC) -o $@  $(XSLTPROC_FLAGS) $(DOCBOOK_XSLT) $<

check_openmanage.conf.5: check_openmanage.conf.5.xml
	$(XMLLINT) $(XMLLINT_FLAGS) $<
	$(XSLTPROC) -o $@  $(XSLTPROC_FLAGS) $(DOCBOOK_XSLT) $<


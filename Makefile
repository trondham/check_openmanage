NAME = check_openmanage
VER  = $(shell ./$(NAME) -V | head -n 1 | awk '{print $$NF}')

default: dist

man:
	pod2man -s 8 -r "`./$(NAME) -V | head -n 1`" -c 'Nagios plugin' $(NAME).pod $(NAME).8
	pod2man -s 5 -r "`./$(NAME) -V | head -n 1`" -c 'Nagios plugin' $(NAME).conf.pod $(NAME).conf.5

dist: man
	mkdir $(NAME)-$(VER)
	mv $(NAME).8 $(NAME).conf.5 $(NAME)-$(VER)
	cp $(NAME){,.pod,.conf.pod,.php,_legacy.php} $(NAME)-$(VER)
	cp nagios-plugins-check-openmanage.spec $(NAME)-$(VER)
	cp CHANGES COPYING INSTALL install.bat install.sh README $(NAME)-$(VER)
	cp -r debian $(NAME)-$(VER)
	rm -rf $(NAME)-$(VER)/debian/.svn

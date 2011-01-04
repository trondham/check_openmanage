NAME = check_openmanage
VER  = $(shell ./$(NAME) -V | head -n 1 | awk '{print $$NF}')

default: dist

man:
	pod2man -s 8 -r "`./$(NAME) -V | head -n 1`" -c 'Nagios plugin' $(NAME).pod $(NAME).8

dist: man
	mkdir $(NAME)-$(VER)
	mv $(NAME).8 $(NAME)-$(VER)
	cp $(NAME){,.pod,.php} $(NAME)-$(VER)
	cp nagios-plugins-check-openmanage.spec $(NAME)-$(VER)
	cp CHANGES COPYING INSTALL install.bat install.sh README $(NAME)-$(VER)
	cp -r debian $(NAME)-$(VER)

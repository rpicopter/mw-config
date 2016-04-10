bindatadir=$(bindir)
bindata_DATA = scripts/* 

bin_PROGRAMS = mw-config-task

mw_config_task_SOURCES = src/mw-config-task.c
mw_config_task_CFLAGS = -Wall
mw_config_task_LDFLAGS =  
mw_config_task_LDADD = -lrt -lpthread

mwconfdir=$(sysconfdir)/mw

install-exec-hook:
	chmod 755 $(DESTDIR)$(bindir)/mw-config-start.sh
	chmod 755 $(DESTDIR)$(bindir)/mw-config-wrapper.sh
	chmod 4755 $(DESTDIR)$(bindir)/mw-config-task
	mkdir -p $(mwconfdir)
	chmod 666 $(mwconfdir)

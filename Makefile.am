bin_PROGRAMS = mw-config-task

mw_config_task_SOURCES = src/mw-config-task.c
mw_config_task_CFLAGS = -Wall
mw_config_task_LDFLAGS =  
mw_config_task_LDADD = -lrt -lpthread

mw_config_task_bindir = $(bindir)
mw_config_task_bin_SCRIPTS = scripts/mw-config-start.sh scripts/mw-config-wrapper.sh

mwconfdir=$(sysconfdir)/mw

install-exec-hook:
	chmod 4755 $(DESTDIR)$(bindir)/mw-config-task
	mkdir -p $(mwconfdir)
	chmod 777 $(mwconfdir)

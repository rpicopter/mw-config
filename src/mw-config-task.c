#include <stdlib.h>
#include <sys/types.h>
#include <unistd.h>
#include <getopt.h>
#include <stdio.h>
#include <signal.h>

char *command;
int task = 0;
char arg[32];
int arg_int;

enum {
	NONE,
	REBOOT,
	KILL
};

void print_usage() {
    printf("Usage: -r | -k [pid]\n");
}

int set_defaults(int c, char **a) {
    int option;

    while ((option = getopt(c, a,"rk:")) != -1) {
        switch (option)  {
            case 'r': task=REBOOT; break;
            case 'k': task=KILL; arg_int = atoi(optarg); break;
            default: 
            	print_usage(); 
            	return -1;
        }
    }

	if (task==NONE) print_usage(); 
	return -1;
}

int main(int argc, char **argv) {
   setuid(0); // for uid to be 0, root

   set_defaults(argc,argv);

   switch (task) {
   		case REBOOT: 
   			command = "/sbin/reboot"; 
   			execl(command, command, NULL);
   			break;
   		case KILL:
   			kill(arg_int,SIGTERM);
   			break;   			
   } 

   return 0; // just to avoid the warning (since never returns)
}

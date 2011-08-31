#!/bin/sh -e
# Apiary Project startup scrupt
# sets environment variables needed by Tomcat, Fedora, Djatoka and OCRopus
# http://www.apiaryproject.org
# created by Sean Murphy

export FEDORA_HOME=/usr/local/fedora
export JAVA_HOME=/usr/lib/jvm/java-home
export PATH=$JAVA_HOME/bin:$PATH
export PATH=$FEDORA_HOME/server/bin:$PATH
export PATH=$FEDORA_HOME/client/bin:$PATH
export CATALINA_HOME=$FEDORA_HOME/tomcat
export LD_LIBRARY_PATH=/usr/local/lib
export PATH=/usr/local/bin:$PATH

djatoka_dir=/usr/local/adore-djatoka

case $1 in
	start)
		if [ -d "$djatoka_dir" ] ; then
			echo "Found Djatoka directory for a tomcat start"
			cd /usr/local/adore-djatoka/bin
			./tomcat.sh start
		else
			echo "Did not find Djatoka directory for a tomcat start"		
			if [ $HAVE_FEDORA==1 ] ; then
				echo "Found Fedora directory for a tomcat start"
				$FEDORA_HOME/tomcat/bin/startup.sh
			else
				echo "Did not find a fedora directory for a tomcat start"
				exit 0;
			fi
                fi
	;;
	stop)
		if [ -d "$djatoka_dir" ] ; then
			echo "Found Djatoka directory for a tomcat stop"
			cd /usr/local/adore-djatoka/bin
			./tomcat.sh stop
		else
			echo "Did not find Djatoka directory for a tomcat stop"
			if [ $HAVE_FEDORA==1 ] ; then
				echo "Found Fedora directory for a tomcat stop"
				$FEDORA_HOME/tomcat/bin/shutdown.sh
			else
				echo "Did not find a fedora directory for a tomcat stop"
				exit 0;
			fi
                fi
	;;
	restart)
		if [ -d "$djatoka_dir" ] ; then
			echo "Found Djatoka directory for restart"
			cd /usr/local/adore-djatoka/bin
			./tomcat.sh stop
			echo "Pausing before restart"
			wait
			echo "Restarting tomcat via djatoka"
			./tomcat.sh start
		else
			echo "Did not find Djatoka directory for restart"
			if [ $HAVE_FEDORA==1 ] ; then
				echo "Found Fedora directory for a tomcat restart"
				$FEDORA_HOME/tomcat/bin/shutdown.sh
				echo "Pausing before restart"
				wait
				echo "Restarting tomcat via fedora"
				$FEDORA_HOME/tomcat/bin/startup.sh
			else
				echo "Did not find a fedora directory for a tomcat stop"
				exit 0;
			fi
                fi
	;;
esac

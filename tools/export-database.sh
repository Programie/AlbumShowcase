#! /bin/bash

set -e

TOOLSPATH=$(dirname $0)

function require()
{
	which $1 > /dev/null 2>&1 || (echo "Command '$1' not found!"; exit 1)
}

require cat
require grep
require mysqldump
require php
require sed
require tee
require tr

echo "Reading configuration..."

DSN=$(php ${TOOLSPATH}/getconfigvalue.php DATABASE_DSN | sed 's/mysql:\(.*\)/\1/g')
USERNAME="`php ${TOOLSPATH}/getconfigvalue.php DATABASE_USERNAME`"
PASSWORD="`php ${TOOLSPATH}/getconfigvalue.php DATABASE_PASSWORD`"

echo "Parsing DSN '${DSN}'..."

PARAMETERS=()

for FULLVAR in $(echo ${DSN} | tr ";" "${IFS}"); do
	VAR=$(echo ${FULLVAR} | tr "=" "${IFS}")
	VAR=(${VAR})
	NAME=${VAR[0]}
	VALUE=${VAR[1]}

	case "${NAME}" in
		dbname)
			echo "Found database name in DSN: ${VALUE}"
			DATABASE="${VALUE}"
		;;
		host)
			echo "Found host name in DSN: ${VALUE}"
			PARAMETERS+=("-h ${VALUE}")
		;;
		port)
			echo "Found port in DSN: ${VALUE}"
			PARAMETERS+=("-P ${VALUE}")
		;;
	esac
done

echo "Using username '${USERNAME}'"

echo "Getting dump..."

mysqldump ${PARAMETERS[@]} -u ${USERNAME} -p${PASSWORD} --no-data --skip-lock-tables --skip-add-drop-table ${DATABASE} | grep -ve '^\/\*' | grep -ve '^--' | sed -E 's/AUTO_INCREMENT=[0-9]+([ ]+)?//' | cat -s | sed ':a;N;$!ba;s/^\n\+//g' | sed ':a;N;$!ba;s/\n\+$//g' | tee ${TOOLSPATH}/database.sql | grep "CREATE TABLE" -c | sed -E 's/([0-9]+)/Exported \1 tables/g'
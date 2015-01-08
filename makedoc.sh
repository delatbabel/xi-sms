#!/bin/sh

mkdir -p ./reports
mkdir -p ./documents/apigen

if [ -z "$1" ]; then
    apigen \
    --title 'Xi SMS Module API documentation' \
    --source ./library \
    --exclude './library/Xi/Sms/Gateway/Legacy/*' \
    --source ./vendor/messagebird \
    --destination ./documents/apigen \
    --report ./reports/apigen.xml

elif [ "$1" = "gateway" ]; then
    apigen \
    --title 'Xi SMS Gateway API documentation' \
    --source ./library/Xi/Sms/Gateway \
    --exclude './library/Xi/Sms/Gateway/Legacy/*' \
    --destination ./documents/apigen \
    --report ./reports/apigen.xml

fi

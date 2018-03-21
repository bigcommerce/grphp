#!/bin/bash
set -e

if [ "$TEST_GROUP" == "prereq" ]
then
  ./vendor/bin/phpcs --standard=PSR2 --ignore=src/Grphp/grpc.stubs.php src

elif [ "$TEST_GROUP"  == "1" ]
then
  phpunit --configuration phpunit.xml.dist
fi

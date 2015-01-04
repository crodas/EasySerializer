#!/bin/bash

FILE=$(pwd)/lib/EasySerializer/Templates.php
php vendor/crodas/simple-view-engine/cli.php compile -N EasySerializer $(pwd)/lib/EasySerializer/Template $FILE

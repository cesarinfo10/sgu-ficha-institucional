<?php

$servidor = "pop.gmail.com";
$usuario  = "jeugenio@umc.cl";
$passwd   = "08121979";

$mailbox  = "{".$servidor.":995/pop3/ssl/novalidate-cert}INBOX";

echo $mailbox;

echo imap_open($mailbox,$usuario,$passwd);
echo imap_last_error();
?>

{literal}
RewriteEngine on
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^(.*)$   /index.php?uri=$1 [L,QSA]
{/literal}
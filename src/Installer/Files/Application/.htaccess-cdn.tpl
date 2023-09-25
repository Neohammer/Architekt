##generated
{foreach from=$CORS_VALUES item=cors}
SetEnvIf Origin ^({$cors}{literal}(?::\d{1,5})?)$   CORS_ALLOW_ORIGIN=$1{/literal}
{/foreach}
{literal}
Header append Access-Control-Allow-Origin  %{CORS_ALLOW_ORIGIN}e   env=CORS_ALLOW_ORIGIN
Header merge  Vary "Origin"
{/literal}
##custom
{literal}
<title>{if isset($TITLE)}{$TITLE}{/if}</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="icon" type="image/png" href="/medias/favicon.png" />
{foreach from=$MEDIAS.CSS item=css}
<link href="{$css}" rel="stylesheet" />
{/foreach}
{foreach from=$MEDIAS.JAVASCRIPTS.external.top item=js}
<script src="{$js}" crossorigin="anonymous"></script>
{/foreach}
<script>
    var URL_APP = '{URL_{/literal}{$APPLICATION_UPPER}{literal}}';
    var URL_CDN = '{/literal}{if $APPLICATION_CDN}{literal}{{/literal}URL_{$APPLICATION_CDN_CODE_UPPER}{literal}}{/literal}{else}/medias/{/if}{literal}';
</script>
{/literal}
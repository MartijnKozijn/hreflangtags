{foreach from=$hreflangs item=hreflang}
    <link rel="alternate" href="{$hreflang.url|escape:'html':'UTF-8'}" hreflang="{$hreflang.locale|escape:'html':'UTF-8'}" />
{/foreach}

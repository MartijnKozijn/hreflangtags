{foreach from=$hreflangs item=hreflang}
    {if $hreflang.is_default}
        <link rel="alternate" href="{$hreflang.url|escape:'html':'UTF-8'}" hreflang="x-default" />
    {else}
        <link rel="alternate" href="{$hreflang.url|escape:'html':'UTF-8'}" hreflang="{$hreflang.locale|escape:'html':'UTF-8'}" />
    {/if}
{/foreach}

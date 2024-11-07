{foreach from=$hreflangs item=hreflang}
    <link rel="alternate" href="{$hreflang.url}" hreflang="{$hreflang.locale}" />
{/foreach}

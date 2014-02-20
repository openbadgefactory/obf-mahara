{include file="header.tpl"}

<h1>{str tag="openbadgefactory" section="interaction.obf"}</h1>

<p>{str tag="institutionselectordescription" section="interaction.obf"}</p>
{$institutionselector|safe}

{include file="tabs.tpl" tabs=$subpages selected=$page page="institution.php?institution=$institution"}

<div class="subpage">
    {$content|safe}
</div>

{include file="footer.tpl"}
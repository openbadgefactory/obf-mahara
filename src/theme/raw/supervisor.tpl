{include file="header.tpl"}
<div id="institution-selector"></div>

<p>{str tag="institutionselectordescription" section="interaction.obf"}</p>

{include file="tabs.tpl" tabs=$subpages selected=$page page="supervisor.php?institution=$institution"}

<div class="subpage">
    {$content|safe}
</div>

{include file="footer.tpl"}
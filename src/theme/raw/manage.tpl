{include file="header.tpl"}

<h1>{str tag="openbadgefactory" section="interaction.obf"}</h1>

{include file="interaction:obf:tabs.tpl" tabs=$subpages icons=$icons selected=$page page="institution.php?institution=$institution"}
<div class="with-heading">&nbsp;</div>

<div class="panel panel-default panel-body">
    {$content|safe}
</div>

{include file="footer.tpl"}
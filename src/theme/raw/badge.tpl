{include file="header.tpl"}

<h1>{$badge->name}</h1>
{include file="interaction:obf:tabs.tpl" tabs=$subpages selected=$page page="badge.php?institution=$institution&badgeid=$badge->id"}

<div class="with-heading">
    <a href="{$WWWROOT}interaction/obf/institution.php?institution={$institution}&page=badges">
    {str tag="backtobadgelist" section="interaction.obf"}</a>
</div>

{include file="interaction:obf:badgedetails.tpl" badge=$badge}

<div class="panel panel-body">
    {$content|safe}
</div>

{include file="footer.tpl"}
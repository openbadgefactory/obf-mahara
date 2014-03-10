{include file="header.tpl"}

<p>
    <a href="{$WWWROOT}interaction/obf/institution.php?institution={$institution}&page=badges">
    {str tag="backtobadgelist" section="interaction.obf"}</a>
</p>

<h1>{$badge->name}</h1>

{include file="badgedetails.tpl" badge=$badge}
{include file="tabs.tpl" tabs=$subpages selected=$page page="badge.php?institution=$institution&badgeid=$badge->id"}

<div class="subpage">
    {$content|safe}
</div>

{include file="footer.tpl"}
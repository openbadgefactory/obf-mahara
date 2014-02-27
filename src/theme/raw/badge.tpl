{include file="header.tpl"}

<p>
    {if $context eq "supervisor"}
        <a href="{$WWWROOT}interaction/obf/supervisor.php?institution={$institution}&page=badges">
    {else}
        <a href="{$WWWROOT}interaction/obf/institution.php?institution={$institution}&page=badges">
    {/if}
    
    {str tag="backtobadgelist" section="interaction.obf"}</a>
</p>

<h1>{$badge->name}</h1>

{include file="badgedetails.tpl" badge=$badge}
{include file="tabs.tpl" tabs=$subpages selected=$page page="badge.php?institution=$institution&badgeid=$badge->id&context=$context"}

<div class="subpage">
    {$content|safe}
</div>

{include file="footer.tpl"}
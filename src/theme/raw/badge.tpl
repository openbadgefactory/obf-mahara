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

<div class="tabswrap">
    <ul class="in-page-tabs">
        {foreach from=$subpages item=subpage}
            <li{if $subpage == $type} class="current-tab"{/if}>
                <a{if $subpage == $type} class="current-tab"{/if} href="{$WWWROOT}interaction/obf/badge.php?type={$subpage}&institution={$institution}&badgeid={$badge->id}&context={$context}">{str tag="badge$subpage" section="interaction.obf"}</a>
            </li>
        {/foreach}
    </ul>
</div>

<div class="subpage">
    {$content|safe}
</div>

{include file="footer.tpl"}
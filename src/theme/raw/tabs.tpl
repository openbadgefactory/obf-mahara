<div class="btn-group btn-toolbar btn-group-top">
    {foreach from=$tabs key=id item=tab}
        <a class="btn btn-default{if $id == $selected} active{/if}" href="{$WWWROOT}interaction/obf/{$page}&page={$id}">
            <span class="icon icon-lg icon-{$tab.icon}"></span>
            <span class="btn-title">{str tag=$id section="interaction.obf"}</span>
        </a>
    {/foreach}
</div>
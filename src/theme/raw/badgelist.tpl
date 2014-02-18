{if $badges eq false}
    <div class="error">{str tag="errorfetchingbadges" section="interaction.obf"}</div>
{else}
    {include file="categories.tpl" categories=$categories}

    <ul id="badges">
        {foreach from=$badges item=badge}
            <li class="openbadge" title="{$badge->name}" data-id="{$badge->id}" data-expires="{$badge->expires}" data-groups="{$badge->categoryjson}" data-categories="{$badge->categoryjson}">
                <div class="badge-wrapper">
                    <div class="name">
                        {if $group neq null}
                            <a href="{$WWWROOT}interaction/obf/issue.php?badgeid={$badge->id}&group={$group}">
                        {else}
                            <a href="{$WWWROOT}interaction/obf/badge.php?{if $group eq null}institution={$institution}&{/if}badgeid={$badge->id}{if $group}&group={$group}{/if}">
                        {/if}
                        
                        <img src="{$badge->image}" alt="{$badge->name}" />
                        <p>{$badge->name}</p>
                        </a>
                    </div>
                    <div class="description">
                        <p>{$badge->description}</p>
                    </div>
                </div>
            </li>
        {/foreach}
    </ul>
{/if}
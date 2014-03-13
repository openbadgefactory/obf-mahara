{if $badges === false}
    <div class="error">{str tag="errorfetchingbadges" section="interaction.obf"}</div>
{else}
    {if count($badges) eq 0}
        <p>{str tag="nobadges" section="interaction.obf"}</p>
    {else}

        {include file="categories.tpl" categories=$categories}

        <ul id="badges">
            {foreach from=$badges item=badge}
                {if $badge->hide neq true}
                    <li class="openbadge" title="{$badge->name}" data-id="{$badge->id}" data-categories="{$badge->categoryjson}">
                        <div class="badge-wrapper">
                            <div class="name">
                                {if $group neq null}
                                    <a href="{$WWWROOT}interaction/obf/issue.php?institution={$badge->institution}&badgeid={$badge->id}&group={$group}">
                                    {else}
                                        <a href="{$WWWROOT}interaction/obf/badge.php?institution={$badge->institution}&badgeid={$badge->id}">
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
                {/if}
            {/foreach}
        </ul>
    {/if}
{/if}
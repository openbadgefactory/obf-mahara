<div class="tabswrap">
    <ul class="in-page-tabs">
        {foreach from=$tabs item=tab}
            <li{if $tab == $selected} class="current-tab"{/if}>
                <a {if $tab == $selected}class="current-tab" {/if} href="{$WWWROOT}interaction/obf/{$page}&page={$tab}">{str tag=$tab section="interaction.obf"}</a>
            </li>
        {/foreach}
    </ul>
</div>

<div class="cb"></div>
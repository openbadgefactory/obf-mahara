{include file="header.tpl"}
{include file="tabs.tpl" tabs=$subpages selected=$page page="group.php?id=$group"}

<div class="subpage">
    {if $content eq false}
        <div class="error">{str tag="apierror" section="interaction.obf"}</div>
    {else}            
        {$content|safe}
    {/if}
</div>

<script type="text/javascript">

    {literal}
        $j(document).ready(function() {
            $j('ul.in-page-tabs li.badges').addClass('current-tab');
            $j('ul.in-page-tabs li.badges a').addClass('current-tab');
        });
    {/literal}
</script>

{include file="footer.tpl"}
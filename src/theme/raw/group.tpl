{include file="header.tpl"}

{*
<h2>{str tag="badges" section="interaction.obf"}</h2>

<div id="badge-wrapper">

<div id="badge-selector">
{$content|safe}
<p class="show-badges" style="display: none">
<a href="#">{str tag="changebadge" section="interaction.obf"}</a>
</p>
</div>

<div id="user-selector" style="display: none">        
{$form|safe}
</div>

</div>
*}

{include file="tabs.tpl" tabs=$subpages selected=$type page="group.php?id=$group"}
<div class="subpage">
    {$content|safe}
</div>

<script type="text/javascript">
    {*    Obf.init_issuance_page();*}

    {if $badge neq null}
        {*        Obf.select_badge('{$badge}');*}
    {/if}

    {literal}
        $j(document).ready(function() {
            $j('ul.in-page-tabs li.badges').addClass('current-tab');
            $j('ul.in-page-tabs li.badges a').addClass('current-tab');
        });
    {/literal}
</script>

{include file="footer.tpl"}
{include file="header.tpl"}

<h2>{str tag="openbadgessettings" section="interaction.obf"}</h2>

<p>{$helptext|safe}</p>
<div class="error" id="assertion-error" style="display: none"></div>
{$form|safe}

<script type="text/javascript">
    {literal}
        $j(document).ready(Obf.select_backpack_tab);
    {/literal}
</script>

{include file="footer.tpl"}
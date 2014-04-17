{include file="header.tpl"}

<h2>{str tag="backpacksettings" section="interaction.obf"}</h2>

<p>{$helptext|safe}</p>
<div class="error" id="assertion-error" style="display: none"></div>
{$form|safe}

<script type="text/javascript" src="https://login.persona.org/include.js"></script>
<script type="text/javascript">
    {literal}
        $j('#backpack_submit').click(Obf.connect_to_backpack);
        $j(document).ready(Obf.select_backpack_tab);
    {/literal}
</script>

{include file="footer.tpl"}
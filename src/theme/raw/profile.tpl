{include file="header.tpl"}

<h2>{str tag="backpacksettings" section="interaction.obf"}</h2>

<p>{$helptext|safe}</p>
{$form|safe}

<script type="text/javascript" src="https://login.persona.org/include.js"></script>
<script type="text/javascript">
    {literal}
        $j('#backpack_submit').click(Obf.connect_to_backpack);

        $j(document).ready(function() {
            $j('#sub-nav li.backpack').addClass('selected');
        });
    {/literal}
</script>

{include file="footer.tpl"}
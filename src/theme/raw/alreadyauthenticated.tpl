<div class="ok">
    {str tag="alreadyauthenticated" section="interaction.obf"}
    <a href="#" id="obf-reauthenticate">{str tag="reauthenticate" section="interaction.obf"}</a>
</div>

<script type="text/javascript">
$j('#obf-reauthenticate').click(function (evt) {
    evt.preventDefault();
    $j('#token_token').prop('disabled', false);
    $j('#token_submit').prop('disabled', false);
});
</script>
<div class="ok">
    {str tag="alreadyauthenticated" section="interaction.obf"}
</div>

<script type="text/javascript">
    $j(document).ready(function() {
        $j('#disconnect_submit').click(function(evt) {
            if (!window.confirm('{str tag="confirmdeauthentication" section="interaction.obf"}')) {
                evt.preventDefault();
            }
        });
    });
</script>
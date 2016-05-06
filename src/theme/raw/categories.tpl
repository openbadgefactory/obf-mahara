{if count($categories) gt 0}
    <div class="badge-category-wrapper">

        <div class="badge-category-reset-wrapper">
            <p>{str tag="filterbadges" section="interaction.obf"}</p>
            <button class="badge-reset-filter btn btn-primary">{str tag="showallbadges" section="interaction.obf"}</button>
        </div>

        <ul class="badge-categories">
            {foreach from=$categories item=category}
                <li>
                    <button class="btn">{$category}</button>
                </li>
            {/foreach}
        </ul>

        <script type="text/javascript">
            Obf.init_categories();
        </script>

    </div>
{/if}
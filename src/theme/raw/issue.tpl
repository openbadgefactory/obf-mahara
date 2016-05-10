{include file="header.tpl"}

<p><a href="{$WWWROOT}interaction/obf/group.php?id={$group}">{str tag="backtobadgelist" section="interaction.obf"}</a></p>

{if $badge eq false}
    <div class="error">{str tag="errorfetchingbadges" section="interaction.obf"}</div>
{else}

    <div id="badge-issue-wrapper">

        <div id="badge-issue-details">
            <h2>{$badge->name}</h2>

            <div id="badge-issue-image">
                <img src="{$badge->image}" alt="{$badge->name}" />
            </div>

            <div id="badge-issue-data">
                <div><strong>{str tag="badgename" section="interaction.obf"}:</strong> {$badge->name}</div>
                <div><strong>{str tag="badgecreated" section="interaction.obf"}:</strong> {$badge->ctime|date_format}</div>
                <div><strong>{str tag="badgedescription" section="interaction.obf"}:</strong> {$badge->description}</div>
            </div>
        </div>

        <div id="badge-issue-form-wrapper">
            <div class="tabswrap">
                <ul class="in-page-tabs">
                    <li class="current-tab" data-tab="issue"><a class="current-tab" href="#">{str tag="issuebadge" section="interaction.obf"}</a></li>
                    <li id="history-tab" data-tab="history"><a href="#">{str tag="badgegrouphistory" section="interaction.obf"}</a></li>
                </ul>
            </div>
            <div class="subpage">
                <div id="badge-issue-form" data-tab-content="issue">
                    {$form|safe}
                </div>
                <div id="badge-group-history" style="display: none" data-tab-content="history">
                    {include file="interaction:obf:events.tpl" events=$events badge=$badge}
                </div>
            </div>
        </div>

        <div class="cb"></div>

    </div>

    <script type="text/javascript">
        {literal}
            $j(document).ready(function() {
                $j('ul.in-page-tabs li.badges').addClass('current-tab');
                $j('ul.in-page-tabs li.badges a').addClass('current-tab');

                Obf.init_issuance_page();
            });
        {/literal}
    </script>

{/if}

{include file="footer.tpl"}
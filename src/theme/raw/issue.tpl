{include file="header.tpl"}

<div class="with-heading">
    <a href="{$WWWROOT}interaction/obf/group.php?id={$group}">{str tag="backtobadgelist" section="interaction.obf"}</a>
</div>

{if $badge eq false}
    <div class="error">{str tag="errorfetchingbadges" section="interaction.obf"}</div>
{else}

    <div class="row">

        <div class="col-sm-3">
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

        <div class="col-sm-9">

            <ul class="nav nav-tabs" role="tablist">
                <li class="active">
                    <a href="#badge-issuance" data-toggle="tab">{str tag="issuebadge" section="interaction.obf"}</a>
                </li>
                <li>
                    <a href="#badge-group-history" data-toggle="tab">{str tag="badgegrouphistory" section="interaction.obf"}</a>
                </li>
            </ul>

            <div class="tab-content">
                <div id="badge-issuance" class="tab-pane active" role="tabpanel">
                    {$form|safe}
                </div>
                <div id="badge-group-history" class="tab-pane" role="tabpanel">
                    {include file="interaction:obf:events.tpl" events=$events badge=$badge}
                </div>
            </div>
        </div>

    </div>

    <script type="text/javascript">
        {literal}
            $j(document).ready(function() {
                Obf.init_issuance_page();
            });
        {/literal}
    </script>

{/if}

{include file="footer.tpl"}
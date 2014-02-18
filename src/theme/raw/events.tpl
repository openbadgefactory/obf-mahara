{if count($events) eq 0}
    <p>{str tag="noevents" section="interaction.obf"}</p>

{else}    
    <table class="events">

        <thead>
            <tr>
                {if !$badge}
                    <th colspan="2">
                        {str tag="badgename" section="interaction.obf"}
                    </th>
                {/if}
                <th>{str tag="issuedat" section="interaction.obf"}</th>
                <th>{str tag="recipients" section="interaction.obf"}</th>
                <th>{str tag="expiresat" section="interaction.obf"}</th>
            </tr>
        </thead>

        <tbody>
            {foreach from=$events item=event}
                <tr class="{cycle values=array("r0", "r1")}">
                    {if !$badge}
                        <td class="image"><img src="{$event->image}" alt="{$event->name}" />
                        <td>{$event->name}</td>
                    {/if}
                    <td>{$event->issued_on|date_format}</td>

                    {if $event->recipientcount eq 1}
                        <td class="recipients">{$event->recipient[0]}</td>
                    {else}
                        <td class="recipients">
                            <a href="#">{str tag="numberofrecipients" section="interaction.obf" arg1=$event->recipientcount}</a>
                            <ul class="recipientlist" style="display: none">
                                {foreach from=$event->recipient item=recipient}
                                    <li>{$recipient}</li>
                                {/foreach}
                            </ul>
                        </td>
                            {/if}
                    <td>{$event->expires|date_format}</td>
                </tr>
            {/foreach}
        </tbody>

    </table>

    <script type="text/javascript">
        {literal}
            $j('table.events').on('click', 'td.recipients a', function (evt) {
               $j(this).siblings('.recipientlist').toggle();
            });
        {/literal}
    </script>
{/if}


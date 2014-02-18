<div id="badge-header">
    <div id="badge-image-wrapper">
        <img src="{$badge->image}" alt="{$badge->name}" />
    </div>

    <div id="badge-detail-wrapper">
        <h3>{str tag="badgedetails" section="interaction.obf"}</h3>

        <dl id="badge-details">
            <dt>{str tag="badgename" section="interaction.obf"}</dt>
            <dd>{$badge->name}</dd>
            <dt>{str tag="badgedescription" section="interaction.obf"}</dt>
            <dd>{$badge->description}</dd>
            <dt>{str tag="badgecreated" section="interaction.obf"}</dt>
            <dd>{$badge->ctime|date_format}</dd>
        </dl>
    </div>
</div>